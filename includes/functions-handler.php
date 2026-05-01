<?php
/**
 * Form submission handler.
 *
 * @package AyudaWP_EU_Withdrawal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Process the withdrawal form submission.
 *
 * Hooked to admin-post.php for both logged-in and guest users.
 */
function ayudawp_euw_handle_submission() {

	// 1. Verify nonce.
	if ( ! isset( $_POST['ayudawp_euw_nonce'] )
		|| ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['ayudawp_euw_nonce'] ) ),
			'ayudawp_euw_submit_action'
		)
	) {
		ayudawp_euw_redirect_with_error( 'nonce' );
	}

	// 2. Honeypot: if filled, treat as spam.
	if ( ! empty( $_POST['ayudawp_euw_website'] ) ) {
		ayudawp_euw_redirect_with_error( 'spam' );
	}

	// 3. Sanitize input.
	$name    = isset( $_POST['ayudawp_euw_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ayudawp_euw_name'] ) ) : '';
	$email   = isset( $_POST['ayudawp_euw_email'] ) ? sanitize_email( wp_unslash( $_POST['ayudawp_euw_email'] ) ) : '';
	$order   = isset( $_POST['ayudawp_euw_order'] ) ? sanitize_text_field( wp_unslash( $_POST['ayudawp_euw_order'] ) ) : '';
	$date    = isset( $_POST['ayudawp_euw_date'] ) ? sanitize_text_field( wp_unslash( $_POST['ayudawp_euw_date'] ) ) : '';
	$scope   = isset( $_POST['ayudawp_euw_scope'] ) ? sanitize_key( wp_unslash( $_POST['ayudawp_euw_scope'] ) ) : 'full';
	$details = isset( $_POST['ayudawp_euw_details'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ayudawp_euw_details'] ) ) : '';
	$privacy = isset( $_POST['ayudawp_euw_privacy'] ) ? '1' : '';

	// 4. Validate required fields.
	if ( empty( $name ) || empty( $email ) || empty( $order ) || empty( $privacy ) ) {
		ayudawp_euw_redirect_with_error( 'fields' );
	}

	if ( ! is_email( $email ) ) {
		ayudawp_euw_redirect_with_error( 'email' );
	}

	$allowed_scopes = array( 'full', 'partial' );
	if ( ! in_array( $scope, $allowed_scopes, true ) ) {
		$scope = 'full';
	}

	// 5. Validate WooCommerce order if WC is active.
	$wc_validation = ayudawp_euw_validate_wc_order( $order, $email );

	if ( false === $wc_validation['valid'] ) {
		ayudawp_euw_redirect_with_error( $wc_validation['error'] );
	}

	// 6. Store the withdrawal request as a CPT entry.
	$post_id = wp_insert_post(
		array(
			'post_type'    => 'ayudawp_withdrawal',
			'post_status'  => 'publish',
			'post_title'   => sprintf(
				/* translators: 1: order number, 2: customer name. */
				__( 'Order %1$s — %2$s', 'eu-withdrawal-compliance' ),
				$order,
				$name
			),
			'post_content' => $details,
		),
		true
	);

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		ayudawp_euw_redirect_with_error( 'general' );
	}

	// 7. Save metadata.
	update_post_meta( $post_id, '_ayudawp_euw_name', $name );
	update_post_meta( $post_id, '_ayudawp_euw_email', $email );
	update_post_meta( $post_id, '_ayudawp_euw_order', $order );
	update_post_meta( $post_id, '_ayudawp_euw_order_date', $date );
	update_post_meta( $post_id, '_ayudawp_euw_scope', $scope );
	update_post_meta( $post_id, '_ayudawp_euw_ip', ayudawp_euw_get_user_ip() );
	update_post_meta( $post_id, '_ayudawp_euw_user_agent', isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' );
	update_post_meta( $post_id, '_ayudawp_euw_status', 'pending' );

	if ( $wc_validation['order_id'] ) {
		update_post_meta( $post_id, '_ayudawp_euw_wc_order_id', $wc_validation['order_id'] );
		ayudawp_euw_add_wc_order_note( $wc_validation['order_id'], $post_id, $scope, $details );
	}

	// 8. Send notifications.
	ayudawp_euw_send_customer_email( $email, $name, $order, $scope );
	ayudawp_euw_send_admin_email( $post_id, $name, $email, $order, $scope, $details );

	/**
	 * Fires after a withdrawal request has been processed.
	 *
	 * @param int   $post_id Withdrawal CPT ID.
	 * @param array $data    Submission data.
	 */
	do_action(
		'ayudawp_euw_after_submission',
		$post_id,
		array(
			'name'     => $name,
			'email'    => $email,
			'order'    => $order,
			'scope'    => $scope,
			'details'  => $details,
		)
	);

	// 9. Redirect back to the form page with success flag.
	ayudawp_euw_redirect_with_success();
}
add_action( 'admin_post_ayudawp_euw_submit', 'ayudawp_euw_handle_submission' );
add_action( 'admin_post_nopriv_ayudawp_euw_submit', 'ayudawp_euw_handle_submission' );

/**
 * Get the visitor IP address respecting common proxies.
 *
 * @return string IP address or empty string.
 */
function ayudawp_euw_get_user_ip() {

	$keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );

	foreach ( $keys as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );

			// X-Forwarded-For can be a comma-separated list.
			if ( false !== strpos( $ip, ',' ) ) {
				$parts = explode( ',', $ip );
				$ip    = trim( $parts[0] );
			}

			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}

	return '';
}

/**
 * Redirect back to the previous page with an error code.
 *
 * @param string $code Error code.
 */
function ayudawp_euw_redirect_with_error( $code ) {

	$referer = wp_get_referer();
	$url     = $referer ? $referer : home_url();

	$url = add_query_arg( 'ayudawp_euw_error', $code, $url );

	wp_safe_redirect( $url );
	exit;
}

/**
 * Redirect back to the previous page with success flag.
 */
function ayudawp_euw_redirect_with_success() {

	$referer = wp_get_referer();
	$url     = $referer ? $referer : home_url();

	$url = remove_query_arg( 'ayudawp_euw_error', $url );
	$url = add_query_arg( 'ayudawp_euw_sent', '1', $url );

	wp_safe_redirect( $url );
	exit;
}
