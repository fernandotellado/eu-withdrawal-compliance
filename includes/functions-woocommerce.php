<?php
/**
 * WooCommerce integration: order validation, My Account endpoint and order notes.
 *
 * @package AyudaWP_EU_Withdrawal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate that a given order/email pair belongs to a real WC order
 * and that the 14-day withdrawal window is still open.
 *
 * If WooCommerce is not active, the function returns valid by default
 * because we cannot check anything against an order database.
 *
 * @param string $order_ref Order number or ID provided by the user.
 * @param string $email     Customer email.
 * @return array {
 *     @type bool   $valid     Whether the order is valid.
 *     @type string $error     Error code if invalid.
 *     @type int    $order_id  WooCommerce order ID if matched.
 * }
 */
function ayudawp_euw_validate_wc_order( $order_ref, $email ) {

	$default = array(
		'valid'    => true,
		'error'    => '',
		'order_id' => 0,
	);

	// Skip validation if WooCommerce is not active.
	if ( ! function_exists( 'wc_get_order' ) ) {
		return $default;
	}

	$order_id = absint( $order_ref );
	$order    = $order_id ? wc_get_order( $order_id ) : false;

	// If we cannot match the order, we still let the request through
	// because the consumer might be sending a request for a non-WC purchase
	// (manual invoice, marketplace, etc.). Admin will review manually.
	if ( ! $order ) {
		return $default;
	}

	// If we have a WC order, verify the email matches.
	$order_email = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : '';

	if ( ! empty( $order_email ) && strtolower( trim( $order_email ) ) !== strtolower( trim( $email ) ) ) {
		return array(
			'valid'    => false,
			'error'    => 'order',
			'order_id' => 0,
		);
	}

	// Check the 14-day window from order date.
	$date_created = $order->get_date_created();

	if ( $date_created ) {
		$created_ts = $date_created->getTimestamp();
		$deadline   = $created_ts + ( 14 * DAY_IN_SECONDS );

		// Allow a 7-day grace period for cases where the customer has not been
		// properly informed about their withdrawal right (worst-case extends to 12 months).
		// This is configurable via filter.
		$grace_days = (int) apply_filters( 'ayudawp_euw_grace_days', 0 );
		$deadline  += $grace_days * DAY_IN_SECONDS;

		if ( time() > $deadline && ! apply_filters( 'ayudawp_euw_skip_deadline_check', false, $order ) ) {
			return array(
				'valid'    => false,
				'error'    => 'expired',
				'order_id' => 0,
			);
		}
	}

	return array(
		'valid'    => true,
		'error'    => '',
		'order_id' => $order->get_id(),
	);
}

/**
 * Add a private note to the WooCommerce order linking to the withdrawal log.
 *
 * @param int    $wc_order_id WC order ID.
 * @param int    $cpt_id      Withdrawal CPT ID.
 * @param string $scope       Withdrawal scope.
 * @param string $details     Customer-provided details.
 */
function ayudawp_euw_add_wc_order_note( $wc_order_id, $cpt_id, $scope, $details ) {

	if ( ! function_exists( 'wc_get_order' ) ) {
		return;
	}

	$order = wc_get_order( $wc_order_id );

	if ( ! $order ) {
		return;
	}

	$scope_label = ( 'partial' === $scope )
		? __( 'partial', 'eu-withdrawal-compliance' )
		: __( 'full', 'eu-withdrawal-compliance' );

	$note = sprintf(
		/* translators: 1: scope label, 2: details, 3: log ID. */
		__( 'EU withdrawal request received (%1$s). Details: %2$s. Log ID: #%3$d', 'eu-withdrawal-compliance' ),
		$scope_label,
		( ! empty( $details ) ? $details : '—' ),
		$cpt_id
	);

	$order->add_order_note( $note, 0, false );
	$order->update_meta_data( '_ayudawp_euw_request_id', $cpt_id );
	$order->save();
}

/**
 * Register the My Account endpoint for WooCommerce.
 */
function ayudawp_euw_register_wc_endpoint() {

	add_rewrite_endpoint( 'withdrawal', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'ayudawp_euw_register_wc_endpoint' );

/**
 * Add the endpoint to WooCommerce's My Account query vars.
 *
 * @param array $vars Query vars.
 * @return array
 */
function ayudawp_euw_add_query_var( $vars ) {

	$vars[] = 'withdrawal';
	return $vars;
}
add_filter( 'query_vars', 'ayudawp_euw_add_query_var', 0 );

/**
 * Add the menu item to WooCommerce My Account navigation.
 *
 * @param array $items Navigation items.
 * @return array
 */
function ayudawp_euw_add_account_menu_item( $items ) {

	if ( ! function_exists( 'wc_get_endpoint_url' ) ) {
		return $items;
	}

	// Insert the new item before the logout link.
	$logout = isset( $items['customer-logout'] ) ? array( 'customer-logout' => $items['customer-logout'] ) : array();

	if ( $logout ) {
		unset( $items['customer-logout'] );
	}

	$items['withdrawal'] = __( 'Right of withdrawal', 'eu-withdrawal-compliance' );

	return array_merge( $items, $logout );
}
add_filter( 'woocommerce_account_menu_items', 'ayudawp_euw_add_account_menu_item' );

/**
 * Render the form inside the My Account endpoint.
 *
 * The form HTML is already built with esc_attr / esc_html / esc_url inside
 * ayudawp_euw_render_form(). wp_kses_post() must NOT be applied here because
 * it strips <form>, <input>, <select>, <textarea> and <button> from the markup.
 */
function ayudawp_euw_account_endpoint_content() {

	$user  = wp_get_current_user();
	$email = $user && ! empty( $user->user_email ) ? $user->user_email : '';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is escaped internally.
	echo ayudawp_euw_render_form(
		array(
			'email' => $email,
		)
	);
}
add_action( 'woocommerce_account_withdrawal_endpoint', 'ayudawp_euw_account_endpoint_content' );

/**
 * Add a "Withdraw" action button to each order in My Account orders list.
 *
 * @param array  $actions Order actions.
 * @param object $order   WC_Order.
 * @return array
 */
function ayudawp_euw_add_order_action( $actions, $order ) {

	if ( ! function_exists( 'wc_get_account_endpoint_url' ) ) {
		return $actions;
	}

	// Only show within 14 days from creation.
	$date_created = $order->get_date_created();

	if ( ! $date_created ) {
		return $actions;
	}

	$created_ts = $date_created->getTimestamp();
	$deadline   = $created_ts + ( 14 * DAY_IN_SECONDS );

	if ( time() > $deadline ) {
		return $actions;
	}

	$endpoint_url = wc_get_account_endpoint_url( 'withdrawal' );
	$endpoint_url = add_query_arg( 'order_id', $order->get_id(), $endpoint_url );

	$actions['ayudawp_euw'] = array(
		'url'  => $endpoint_url,
		'name' => __( 'Withdraw', 'eu-withdrawal-compliance' ),
	);

	return $actions;
}
add_filter( 'woocommerce_my_account_my_orders_actions', 'ayudawp_euw_add_order_action', 10, 2 );

/**
 * Pre-fill the order ID when the user clicks "Withdraw" on My Orders.
 *
 * @param array $atts Shortcode atts.
 * @return array
 */
function ayudawp_euw_prefill_from_query( $atts ) {

	if ( isset( $_GET['order_id'] ) ) {
		$atts['order_id'] = absint( wp_unslash( $_GET['order_id'] ) );
	}

	return $atts;
}
add_filter( 'shortcode_atts_ayudawp_withdrawal_form', 'ayudawp_euw_prefill_from_query' );
