<?php
/**
 * Withdrawal form rendering.
 *
 * @package AyudaWP_EU_Withdrawal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the withdrawal form HTML.
 *
 * @param array $atts Optional atts. Currently supports:
 *                    - order_id: pre-fill order ID (used when called from My Account).
 *                    - email: pre-fill email.
 * @return string HTML markup of the form.
 */
function ayudawp_euw_render_form( $atts = array() ) {

	$atts = wp_parse_args(
		$atts,
		array(
			'order_id' => '',
			'email'    => '',
		)
	);

	// Show success message if the form was just submitted.
	$success = isset( $_GET['ayudawp_euw_sent'] )
		&& '1' === sanitize_text_field( wp_unslash( $_GET['ayudawp_euw_sent'] ) );
	$error   = isset( $_GET['ayudawp_euw_error'] ) ? sanitize_key( wp_unslash( $_GET['ayudawp_euw_error'] ) ) : '';

	if ( $success ) {
		return '<div class="ayudawp-euw-wrapper"><div class="ayudawp-euw-notice ayudawp-euw-notice--success" role="status"><p>' .
			esc_html__( 'We have received your withdrawal request. You will get a confirmation email shortly.', 'eu-withdrawal-compliance' ) .
			'</p></div></div>';
	}

	ob_start();
	?>
	<div class="ayudawp-euw-wrapper">

		<?php if ( $error ) : ?>
			<div class="ayudawp-euw-notice ayudawp-euw-notice--error" role="alert">
				<p><?php echo esc_html( ayudawp_euw_get_error_message( $error ) ); ?></p>
			</div>
		<?php endif; ?>

		<p class="ayudawp-euw-intro">
			<?php
			esc_html_e(
				'Use this form to exercise your withdrawal right under applicable EU consumer protection law. We will process your request and confirm by email within 24 hours.',
				'eu-withdrawal-compliance'
			);
			?>
		</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ayudawp-euw-form" novalidate>

			<input type="hidden" name="action" value="ayudawp_euw_submit">

			<?php
			wp_nonce_field( 'ayudawp_euw_submit_action', 'ayudawp_euw_nonce' );

			// Honeypot field for basic spam protection.
			?>
			<div class="ayudawp-euw-hp" aria-hidden="true">
				<label for="ayudawp_euw_website"><?php esc_html_e( 'Leave this field empty', 'eu-withdrawal-compliance' ); ?></label>
				<input type="text" id="ayudawp_euw_website" name="ayudawp_euw_website" tabindex="-1" autocomplete="off">
			</div>

			<div class="ayudawp-euw-field">
				<label for="ayudawp_euw_name"><?php esc_html_e( 'Full name', 'eu-withdrawal-compliance' ); ?> <span class="ayudawp-euw-required">*</span></label>
				<input type="text" id="ayudawp_euw_name" name="ayudawp_euw_name" required>
			</div>

			<div class="ayudawp-euw-field">
				<label for="ayudawp_euw_email"><?php esc_html_e( 'Email used in the order', 'eu-withdrawal-compliance' ); ?> <span class="ayudawp-euw-required">*</span></label>
				<input type="email" id="ayudawp_euw_email" name="ayudawp_euw_email" value="<?php echo esc_attr( $atts['email'] ); ?>" required>
			</div>

			<div class="ayudawp-euw-field">
				<label for="ayudawp_euw_order"><?php esc_html_e( 'Order number', 'eu-withdrawal-compliance' ); ?> <span class="ayudawp-euw-required">*</span></label>
				<input type="text" id="ayudawp_euw_order" name="ayudawp_euw_order" value="<?php echo esc_attr( $atts['order_id'] ); ?>" required>
				<small class="ayudawp-euw-help"><?php esc_html_e( 'You can find it in the confirmation email we sent you.', 'eu-withdrawal-compliance' ); ?></small>
			</div>

			<div class="ayudawp-euw-field">
				<label for="ayudawp_euw_date"><?php esc_html_e( 'Order date', 'eu-withdrawal-compliance' ); ?></label>
				<input type="date" id="ayudawp_euw_date" name="ayudawp_euw_date" max="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>">
			</div>

			<div class="ayudawp-euw-field">
				<label for="ayudawp_euw_scope"><?php esc_html_e( 'Scope of the withdrawal', 'eu-withdrawal-compliance' ); ?> <span class="ayudawp-euw-required">*</span></label>
				<select id="ayudawp_euw_scope" name="ayudawp_euw_scope" required>
					<option value="full"><?php esc_html_e( 'Full order', 'eu-withdrawal-compliance' ); ?></option>
					<option value="partial"><?php esc_html_e( 'Specific products only', 'eu-withdrawal-compliance' ); ?></option>
				</select>
			</div>

			<div class="ayudawp-euw-field">
				<label for="ayudawp_euw_details"><?php esc_html_e( 'Affected products / additional information', 'eu-withdrawal-compliance' ); ?></label>
				<textarea id="ayudawp_euw_details" name="ayudawp_euw_details" rows="5"></textarea>
				<small class="ayudawp-euw-help"><?php esc_html_e( 'If you have selected partial withdrawal, list the products affected here.', 'eu-withdrawal-compliance' ); ?></small>
			</div>

			<?php
			$privacy_policy = get_option( 'wp_page_for_privacy_policy' );
			$privacy_url    = $privacy_policy ? get_permalink( $privacy_policy ) : '';
			?>
			<div class="ayudawp-euw-field ayudawp-euw-field--checkbox">
				<label for="ayudawp_euw_privacy">
					<input type="checkbox" id="ayudawp_euw_privacy" name="ayudawp_euw_privacy" value="1" required>
					<span>
						<?php
						if ( $privacy_url ) {
							printf(
								/* translators: %s: privacy policy URL. */
								wp_kses(
									__( 'I have read and accept the <a href="%s" target="_blank" rel="noopener">privacy policy</a>.', 'eu-withdrawal-compliance' ),
									array(
										'a' => array(
											'href'   => array(),
											'target' => array(),
											'rel'    => array(),
										),
									)
								),
								esc_url( $privacy_url )
							);
						} else {
							esc_html_e( 'I confirm that the information provided is correct.', 'eu-withdrawal-compliance' );
						}
						?>
					</span>
				</label>
			</div>

			<div class="ayudawp-euw-submit">
				<button type="submit" class="ayudawp-euw-button"><?php esc_html_e( 'Exercise withdrawal right', 'eu-withdrawal-compliance' ); ?></button>
			</div>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Translate an error code into a human-readable message.
 *
 * @param string $code Error code.
 * @return string Translated message.
 */
function ayudawp_euw_get_error_message( $code ) {

	$messages = array(
		'nonce'   => __( 'Security check failed. Please refresh the page and try again.', 'eu-withdrawal-compliance' ),
		'spam'    => __( 'Your request looks like spam and was rejected.', 'eu-withdrawal-compliance' ),
		'fields'  => __( 'Some required fields are missing. Please fill them in.', 'eu-withdrawal-compliance' ),
		'email'   => __( 'The email address is not valid.', 'eu-withdrawal-compliance' ),
		'order'   => __( 'We could not match this email with the order number provided.', 'eu-withdrawal-compliance' ),
		'expired' => __( 'The 14-day withdrawal period has already expired for this order.', 'eu-withdrawal-compliance' ),
		'general' => __( 'An error occurred. Please try again later.', 'eu-withdrawal-compliance' ),
	);

	return isset( $messages[ $code ] ) ? $messages[ $code ] : $messages['general'];
}
