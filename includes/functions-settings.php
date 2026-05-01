<?php
/**
 * Settings page using the Settings API.
 *
 * @package AyudaWP_EU_Withdrawal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add the settings page.
 *
 * When WooCommerce is active the page is nested under the WooCommerce menu
 * with the label "EU Withdrawal". Otherwise it sits under the CPT menu.
 */
function ayudawp_euw_register_settings_page() {

	$has_wc = class_exists( 'WooCommerce' );

	$parent_slug = $has_wc ? 'woocommerce' : 'edit.php?post_type=ayudawp_withdrawal';
	$menu_label  = $has_wc
		? __( 'EU Withdrawal', 'eu-withdrawal-compliance' )
		: __( 'Settings', 'eu-withdrawal-compliance' );

	add_submenu_page(
		$parent_slug,
		__( 'EU Withdrawal settings', 'eu-withdrawal-compliance' ),
		$menu_label,
		'manage_options',
		'ayudawp-euw-settings',
		'ayudawp_euw_settings_page_html'
	);
}
add_action( 'admin_menu', 'ayudawp_euw_register_settings_page' );

/**
 * Register settings, sections and fields.
 */
function ayudawp_euw_register_settings() {

	register_setting(
		'ayudawp_euw_settings_group',
		'ayudawp_euw_notify_email',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'default'           => get_option( 'admin_email' ),
		)
	);

	register_setting(
		'ayudawp_euw_settings_group',
		'ayudawp_euw_page_id',
		array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 0,
		)
	);

	add_settings_section(
		'ayudawp_euw_main_section',
		__( 'General', 'eu-withdrawal-compliance' ),
		'ayudawp_euw_settings_section_callback',
		'ayudawp-euw-settings'
	);

	add_settings_field(
		'ayudawp_euw_notify_email',
		__( 'Notification email', 'eu-withdrawal-compliance' ),
		'ayudawp_euw_field_email_callback',
		'ayudawp-euw-settings',
		'ayudawp_euw_main_section'
	);

	add_settings_field(
		'ayudawp_euw_page_id',
		__( 'Withdrawal page', 'eu-withdrawal-compliance' ),
		'ayudawp_euw_field_page_callback',
		'ayudawp-euw-settings',
		'ayudawp_euw_main_section'
	);
}
add_action( 'admin_init', 'ayudawp_euw_register_settings' );

/**
 * Section description.
 */
function ayudawp_euw_settings_section_callback() {

	echo '<p>' . esc_html__( 'Configure where notifications are sent and which page hosts the withdrawal form.', 'eu-withdrawal-compliance' ) . '</p>';
}

/**
 * Email field callback.
 */
function ayudawp_euw_field_email_callback() {

	$value = get_option( 'ayudawp_euw_notify_email', get_option( 'admin_email' ) );

	printf(
		'<input type="email" name="ayudawp_euw_notify_email" value="%s" class="regular-text">',
		esc_attr( $value )
	);

	echo '<p class="description">' . esc_html__( 'Address that receives a notification each time a customer submits a withdrawal request.', 'eu-withdrawal-compliance' ) . '</p>';
}

/**
 * Page selector field callback.
 */
function ayudawp_euw_field_page_callback() {

	$selected = (int) get_option( 'ayudawp_euw_page_id', 0 );

	wp_dropdown_pages(
		array(
			'name'             => 'ayudawp_euw_page_id',
			'show_option_none' => __( '— Select a page —', 'eu-withdrawal-compliance' ),
			'option_none_value' => '0',
			'selected'         => $selected,
		)
	);

	echo '<p class="description">' . esc_html__( 'Page where the withdrawal form is published. Make sure it includes the [ayudawp_withdrawal_form] shortcode.', 'eu-withdrawal-compliance' ) . '</p>';
}

/**
 * Render the settings page.
 */
function ayudawp_euw_settings_page_html() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<?php
		$page_id = (int) get_option( 'ayudawp_euw_page_id', 0 );

		if ( $page_id && get_post( $page_id ) ) {
			$page_url = get_permalink( $page_id );
			printf(
				'<p>%1$s <a href="%2$s" target="_blank" rel="noopener">%3$s</a></p>',
				esc_html__( 'Withdrawal form public URL:', 'eu-withdrawal-compliance' ),
				esc_url( $page_url ),
				esc_html( $page_url )
			);
		}
		?>

		<form action="options.php" method="post">
			<?php
			settings_fields( 'ayudawp_euw_settings_group' );
			do_settings_sections( 'ayudawp-euw-settings' );
			submit_button( __( 'Save changes', 'eu-withdrawal-compliance' ) );
			?>
		</form>

		<hr>

		<h2><?php esc_html_e( 'How to use this plugin', 'eu-withdrawal-compliance' ); ?></h2>

		<p><?php esc_html_e( 'You can place the withdrawal form anywhere on your site using the following shortcode:', 'eu-withdrawal-compliance' ); ?></p>
		<p><code>[ayudawp_withdrawal_form]</code></p>

		<?php if ( function_exists( 'wc_get_account_endpoint_url' ) ) : ?>
			<p>
				<?php
				$endpoint_url = wc_get_account_endpoint_url( 'withdrawal' );
				printf(
					/* translators: %s: WooCommerce My Account withdrawal endpoint URL. */
					esc_html__( 'WooCommerce customers can also access the form from their account at: %s', 'eu-withdrawal-compliance' ),
					'<a href="' . esc_url( $endpoint_url ) . '" target="_blank" rel="noopener"><code>' . esc_html( $endpoint_url ) . '</code></a>'
				);
				?>
			</p>
		<?php endif; ?>

		<p>
			<?php
			esc_html_e(
				'For maximum visibility, link the withdrawal page from your footer next to your privacy policy and terms of service. Customers must be able to find it within a couple of clicks from any page on the site.',
				'eu-withdrawal-compliance'
			);
			?>
		</p>

		<hr>

		<h2><?php esc_html_e( 'About the EU withdrawal function', 'eu-withdrawal-compliance' ); ?></h2>
		<p>
			<?php
			echo wp_kses(
				__( 'This plugin implements the digital withdrawal function required by <a href="https://eur-lex.europa.eu/eli/dir/2023/2673/oj" target="_blank" rel="noopener">EU Directive 2023/2673</a>, applicable to all EU online retailers from June 19, 2026.', 'eu-withdrawal-compliance' ),
				array(
					'a' => array(
						'href'   => array(),
						'target' => array(),
						'rel'    => array(),
					),
				)
			);
			?>
		</p>

		<?php
		// Promotional banner with rotating AyudaWP plugins and services.
		if ( class_exists( 'Aeuw_Promo_Banner' ) ) {
			$promo_banner = new Aeuw_Promo_Banner( 'eu-withdrawal-compliance', 'aeuw' );
			$promo_banner->render( 'horizontal' );
		}
		?>
	</div>
	<?php
}
