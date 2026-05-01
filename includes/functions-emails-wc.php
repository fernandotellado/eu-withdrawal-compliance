<?php
/**
 * Inject the withdrawal notice into WooCommerce transactional emails.
 *
 * The directive obliges traders to inform consumers about the existence
 * and placement of the withdrawal function. We add a short notice with
 * a link to the form in the customer-facing emails covering the period
 * during which the right of withdrawal is exercisable.
 *
 * @package AyudaWP_EU_Withdrawal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print the withdrawal notice inside WooCommerce emails.
 *
 * Hooked to woocommerce_email_after_order_table for selected email types.
 *
 * @param object $order         WC_Order instance.
 * @param bool   $sent_to_admin Whether the email is going to the admin.
 * @param bool   $plain_text    Whether the email is plain text.
 * @param object $email         WC_Email instance.
 */
function ayudawp_euw_inject_email_notice( $order, $sent_to_admin, $plain_text, $email ) {

	// Never include the notice in admin emails.
	if ( $sent_to_admin ) {
		return;
	}

	// Only on customer-facing emails relevant to the withdrawal period.
	$allowed_emails = array(
		'customer_processing_order',
		'customer_completed_order',
		'customer_on_hold_order',
	);

	$email_id = isset( $email->id ) ? $email->id : '';

	/**
	 * Filter the list of WooCommerce email IDs where the notice is included.
	 *
	 * @param array $allowed_emails Default list of email IDs.
	 */
	$allowed_emails = apply_filters( 'ayudawp_euw_email_ids', $allowed_emails );

	if ( ! in_array( $email_id, $allowed_emails, true ) ) {
		return;
	}

	// Resolve the URL of the withdrawal page.
	$page_id = (int) get_option( 'ayudawp_euw_page_id', 0 );

	if ( ! $page_id || ! get_post( $page_id ) ) {
		return;
	}

	$page_url = get_permalink( $page_id );

	// Append order_id so the form can be pre-filled.
	if ( $order && method_exists( $order, 'get_id' ) ) {
		$page_url = add_query_arg( 'order_id', $order->get_id(), $page_url );
	}

	if ( $plain_text ) {
		echo "\n\n----------\n";
		echo esc_html__( 'Right of withdrawal', 'eu-withdrawal-compliance' ) . "\n";
		echo esc_html__( 'You have 14 days from receipt to exercise your withdrawal right without giving any reason. To do so, use our online withdrawal function:', 'eu-withdrawal-compliance' ) . "\n";
		echo esc_url( $page_url ) . "\n";
		return;
	}

	?>
	<div style="margin-top: 30px; padding: 16px 20px; background-color: #f5f7fa; border-left: 4px solid #2271b1; font-size: 14px;">
		<p style="margin: 0 0 8px 0; font-weight: 600;">
			<?php esc_html_e( 'Right of withdrawal', 'eu-withdrawal-compliance' ); ?>
		</p>
		<p style="margin: 0 0 12px 0;">
			<?php esc_html_e( 'You have 14 days from receipt to exercise your withdrawal right without giving any reason.', 'eu-withdrawal-compliance' ); ?>
		</p>
		<p style="margin: 0;">
			<a href="<?php echo esc_url( $page_url ); ?>" style="display: inline-block; padding: 10px 18px; background-color: #2271b1; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: 600;">
				<?php esc_html_e( 'Exercise withdrawal right here', 'eu-withdrawal-compliance' ); ?>
			</a>
		</p>
	</div>
	<?php
}
add_action( 'woocommerce_email_after_order_table', 'ayudawp_euw_inject_email_notice', 20, 4 );
