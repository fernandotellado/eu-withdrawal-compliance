<?php
/**
 * Plugin Name:       EU Withdrawal Compliance
 * Plugin URI:        https://servicios.ayudawp.com
 * Description:       Adds the EU online withdrawal function required by Directive (EU) 2023/2673 from June 19, 2026. Includes a shortcode, a Gutenberg-friendly form, a WooCommerce "My account" endpoint and a full admin log.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Tested up to:      7.0
 * Requires PHP:      7.4
 * Author:            Fernando Tellado
 * Author URI:        https://ayudawp.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       eu-withdrawal-compliance
 * Domain Path:       /languages
 * WC requires at least: 7.0
 * WC tested up to:   10.7
 *
 * @package AyudaWP_EU_Withdrawal
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'AYUDAWP_EUW_VERSION', '1.0.0' );
define( 'AYUDAWP_EUW_FILE', __FILE__ );
define( 'AYUDAWP_EUW_DIR', plugin_dir_path( __FILE__ ) );
define( 'AYUDAWP_EUW_URL', plugin_dir_url( __FILE__ ) );
define( 'AYUDAWP_EUW_BASENAME', plugin_basename( __FILE__ ) );

// Load core files.
require_once AYUDAWP_EUW_DIR . 'includes/functions-cpt.php';
require_once AYUDAWP_EUW_DIR . 'includes/functions-form.php';
require_once AYUDAWP_EUW_DIR . 'includes/functions-shortcode.php';
require_once AYUDAWP_EUW_DIR . 'includes/functions-handler.php';
require_once AYUDAWP_EUW_DIR . 'includes/functions-emails.php';
require_once AYUDAWP_EUW_DIR . 'includes/functions-admin.php';
require_once AYUDAWP_EUW_DIR . 'includes/functions-assets.php';
require_once AYUDAWP_EUW_DIR . 'includes/functions-settings.php';
require_once AYUDAWP_EUW_DIR . 'includes/functions-woocommerce.php';
require_once AYUDAWP_EUW_DIR . 'includes/functions-emails-wc.php';
require_once AYUDAWP_EUW_DIR . 'includes/class-aeuw-promo-banner.php';

/**
 * Load plugin translations.
 *
 * Needed for self-hosted distribution (GitHub). On WordPress.org installs,
 * GlotPress handles this automatically and the call is a no-op.
 */
function ayudawp_euw_load_textdomain() {
	load_plugin_textdomain(
		'eu-withdrawal-compliance',
		false,
		dirname( AYUDAWP_EUW_BASENAME ) . '/languages'
	);
}
add_action( 'init', 'ayudawp_euw_load_textdomain' );

/**
 * Activation hook: schedule cleanup task and create default page.
 */
function ayudawp_euw_activate() {

	// Register CPT before flushing rewrite rules.
	ayudawp_euw_register_cpt();
	flush_rewrite_rules();

	// Load textdomain now so the sample template text below is translated
	// into the site language (the `init` hook fires after activation).
	ayudawp_euw_load_textdomain();

	// Trigger the welcome notice on the next admin page load.
	set_transient( 'ayudawp_euw_just_activated', 1, MINUTE_IN_SECONDS );

	// Create the withdrawal page if it does not exist yet.
	$page_id = (int) get_option( 'ayudawp_euw_page_id', 0 );

	if ( ! $page_id || ! get_post( $page_id ) ) {

		$intro       = __( '[Sample template — review with a legal advisor before publishing.]', 'eu-withdrawal-compliance' );
		$paragraph_1 = __( 'You have a 14-day withdrawal period from the moment you receive your order to cancel your purchase, without giving any reason and without penalty, as established by EU Directive 2023/2673 and the consumer protection laws applicable in your country.', 'eu-withdrawal-compliance' );
		$paragraph_2 = __( 'To exercise this right, fill in the form below. You will receive an automatic confirmation by email once your request is registered. The refund will be processed within the legal deadline that applies to your purchase.', 'eu-withdrawal-compliance' );
		$paragraph_3 = __( 'Some products and services are excluded from the right of withdrawal (custom-made goods, perishables, sealed digital content downloaded with your express consent, hygiene-sealed items, etc.). Please review our terms and conditions for the full list of exceptions.', 'eu-withdrawal-compliance' );

		$content = sprintf(
			"<!-- wp:paragraph -->\n<p><strong>%1\$s</strong></p>\n<!-- /wp:paragraph -->\n\n" .
			"<!-- wp:paragraph -->\n<p>%2\$s</p>\n<!-- /wp:paragraph -->\n\n" .
			"<!-- wp:paragraph -->\n<p>%3\$s</p>\n<!-- /wp:paragraph -->\n\n" .
			"<!-- wp:paragraph -->\n<p>%4\$s</p>\n<!-- /wp:paragraph -->\n\n" .
			'<!-- wp:shortcode -->[ayudawp_withdrawal_form]<!-- /wp:shortcode -->',
			esc_html( $intro ),
			esc_html( $paragraph_1 ),
			esc_html( $paragraph_2 ),
			esc_html( $paragraph_3 )
		);

		$new_page_id = wp_insert_post(
			array(
				'post_title'   => __( 'Right of withdrawal', 'eu-withdrawal-compliance' ),
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_author'  => get_current_user_id(),
			),
			true
		);

		if ( ! is_wp_error( $new_page_id ) ) {
			update_option( 'ayudawp_euw_page_id', $new_page_id );
		}
	}
}
register_activation_hook( __FILE__, 'ayudawp_euw_activate' );

/**
 * Deactivation hook.
 */
function ayudawp_euw_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'ayudawp_euw_deactivate' );

/**
 * Declare WooCommerce HPOS compatibility.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				__FILE__,
				true
			);
		}
	}
);

/**
 * Resolve the URL of the settings page (different parent depending on WC).
 *
 * @return string
 */
function ayudawp_euw_get_settings_url() {

	if ( class_exists( 'WooCommerce' ) ) {
		return admin_url( 'admin.php?page=ayudawp-euw-settings' );
	}

	return admin_url( 'edit.php?post_type=ayudawp_withdrawal&page=ayudawp-euw-settings' );
}

/**
 * Add quick links (Settings, Withdrawals) to the plugin row in Plugins screen.
 *
 * @param array $links Existing action links.
 * @return array
 */
function ayudawp_euw_plugin_action_links( $links ) {

	$custom = array(
		'settings'    => sprintf(
			'<a href="%s">%s</a>',
			esc_url( ayudawp_euw_get_settings_url() ),
			esc_html__( 'Settings', 'eu-withdrawal-compliance' )
		),
		'withdrawals' => sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'edit.php?post_type=ayudawp_withdrawal' ) ),
			esc_html__( 'Withdrawals', 'eu-withdrawal-compliance' )
		),
	);

	return array_merge( $custom, $links );
}
add_filter( 'plugin_action_links_' . AYUDAWP_EUW_BASENAME, 'ayudawp_euw_plugin_action_links' );

/**
 * Show a one-time admin notice right after the plugin is activated.
 */
function ayudawp_euw_activation_notice() {

	if ( ! get_transient( 'ayudawp_euw_just_activated' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	delete_transient( 'ayudawp_euw_just_activated' );

	$page_id   = (int) get_option( 'ayudawp_euw_page_id', 0 );
	$page_edit = ( $page_id && get_post( $page_id ) ) ? get_edit_post_link( $page_id ) : '';
	?>
	<div class="notice notice-success is-dismissible">
		<p>
			<strong><?php esc_html_e( 'EU Withdrawal Compliance is active.', 'eu-withdrawal-compliance' ); ?></strong>
		</p>
		<p>
			<?php
			esc_html_e( 'A "Right of withdrawal" page with a sample legal template was created automatically. Review the text, link the page from your footer and configure the notification email.', 'eu-withdrawal-compliance' );
			?>
		</p>
		<p>
			<a href="<?php echo esc_url( ayudawp_euw_get_settings_url() ); ?>" class="button button-primary">
				<?php esc_html_e( 'Open settings', 'eu-withdrawal-compliance' ); ?>
			</a>
			<?php if ( $page_edit ) : ?>
				<a href="<?php echo esc_url( $page_edit ); ?>" class="button">
					<?php esc_html_e( 'Edit withdrawal page', 'eu-withdrawal-compliance' ); ?>
				</a>
			<?php endif; ?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'ayudawp_euw_activation_notice' );
