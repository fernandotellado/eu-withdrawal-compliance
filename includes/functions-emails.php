<?php
/**
 * Email notifications.
 *
 * @package AyudaWP_EU_Withdrawal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Send confirmation email to the customer.
 *
 * @param string $email Customer email.
 * @param string $name  Customer name.
 * @param string $order Order reference.
 * @param string $scope Withdrawal scope.
 */
function ayudawp_euw_send_customer_email( $email, $name, $order, $scope ) {

	$site_name = get_bloginfo( 'name' );

	$subject = sprintf(
		/* translators: %s: site name. */
		__( '[%s] We received your withdrawal request', 'eu-withdrawal-compliance' ),
		$site_name
	);

	$scope_label = ( 'partial' === $scope )
		? __( 'Partial withdrawal (specific products only)', 'eu-withdrawal-compliance' )
		: __( 'Full withdrawal', 'eu-withdrawal-compliance' );

	$lines = array(
		sprintf(
			/* translators: %s: customer name. */
			__( 'Hi %s,', 'eu-withdrawal-compliance' ),
			$name
		),
		'',
		__( 'We have received your withdrawal request with the following details:', 'eu-withdrawal-compliance' ),
		'',
		sprintf( '%s: %s', __( 'Order', 'eu-withdrawal-compliance' ), $order ),
		sprintf( '%s: %s', __( 'Scope', 'eu-withdrawal-compliance' ), $scope_label ),
		'',
		__( 'We will review the request and confirm next steps within 24 hours. If you do not hear from us, please reply to this email.', 'eu-withdrawal-compliance' ),
		'',
		sprintf(
			/* translators: %s: site name. */
			__( 'Thanks, the %s team', 'eu-withdrawal-compliance' ),
			$site_name
		),
	);

	$message = implode( "\r\n", $lines );

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	wp_mail( $email, $subject, $message, $headers );
}

/**
 * Send notification to the shop admin.
 *
 * @param int    $post_id Withdrawal CPT ID.
 * @param string $name    Customer name.
 * @param string $email   Customer email.
 * @param string $order   Order reference.
 * @param string $scope   Withdrawal scope.
 * @param string $details Free-text details.
 */
function ayudawp_euw_send_admin_email( $post_id, $name, $email, $order, $scope, $details ) {

	$admin_email = get_option( 'ayudawp_euw_notify_email', get_option( 'admin_email' ) );

	if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
		$admin_email = get_option( 'admin_email' );
	}

	$site_name = get_bloginfo( 'name' );

	$subject = sprintf(
		/* translators: 1: site name, 2: order number. */
		__( '[%1$s] New withdrawal request — order %2$s', 'eu-withdrawal-compliance' ),
		$site_name,
		$order
	);

	$scope_label = ( 'partial' === $scope )
		? __( 'Partial withdrawal', 'eu-withdrawal-compliance' )
		: __( 'Full withdrawal', 'eu-withdrawal-compliance' );

	$edit_link = get_edit_post_link( $post_id, '' );

	$lines = array(
		__( 'A new withdrawal request has just been submitted.', 'eu-withdrawal-compliance' ),
		'',
		sprintf( '%s: %s', __( 'Customer', 'eu-withdrawal-compliance' ), $name ),
		sprintf( '%s: %s', __( 'Email', 'eu-withdrawal-compliance' ), $email ),
		sprintf( '%s: %s', __( 'Order', 'eu-withdrawal-compliance' ), $order ),
		sprintf( '%s: %s', __( 'Scope', 'eu-withdrawal-compliance' ), $scope_label ),
		'',
		__( 'Details:', 'eu-withdrawal-compliance' ),
		( ! empty( $details ) ? $details : __( '(empty)', 'eu-withdrawal-compliance' ) ),
		'',
		__( 'View in admin:', 'eu-withdrawal-compliance' ),
		$edit_link,
	);

	$message = implode( "\r\n", $lines );

	// Sanitize the Reply-To header values to prevent CRLF header injection.
	$clean_name  = sanitize_text_field( $name );
	$clean_email = sanitize_email( $email );

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	if ( $clean_email ) {
		$headers[] = sprintf( 'Reply-To: %s <%s>', $clean_name, $clean_email );
	}

	wp_mail( $admin_email, $subject, $message, $headers );
}
