<?php
/**
 * Conditional asset loading: only enqueues CSS where it is needed.
 *
 * @package AyudaWP_EU_Withdrawal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detect whether the current request shows the withdrawal form,
 * either via shortcode, the configured page, or the WC My Account endpoint.
 *
 * @return bool
 */
function ayudawp_euw_is_form_visible() {

	// Configured withdrawal page.
	$page_id = (int) get_option( 'ayudawp_euw_page_id', 0 );

	if ( $page_id && is_page( $page_id ) ) {
		return true;
	}

	// Any post containing the shortcode.
	if ( is_singular() ) {

		$post = get_post();

		if ( $post && has_shortcode( $post->post_content, 'ayudawp_withdrawal_form' ) ) {
			return true;
		}
	}

	// WooCommerce My Account "withdrawal" endpoint.
	if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'withdrawal' ) ) {
		return true;
	}

	// Fallback: WC account page where the withdrawal query var is set.
	// is_wc_endpoint_url() can return false during early enqueue depending on
	// WC's query parsing order, so we double-check the global query vars.
	if ( function_exists( 'is_account_page' ) && is_account_page() ) {

		global $wp;

		if ( isset( $wp->query_vars['withdrawal'] ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Enqueue frontend CSS only on the form page.
 */
function ayudawp_euw_enqueue_frontend() {

	if ( ! ayudawp_euw_is_form_visible() ) {
		return;
	}

	wp_enqueue_style(
		'ayudawp-euw-frontend',
		AYUDAWP_EUW_URL . 'assets/css/frontend.css',
		array(),
		AYUDAWP_EUW_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'ayudawp_euw_enqueue_frontend' );

/**
 * Enqueue admin CSS on the withdrawal CPT screens and on the settings page.
 *
 * Also loads Thickbox on the settings page so the promo banner can open the
 * "plugin information" modals when the user clicks an Install button.
 *
 * @param string $hook Current admin page hook.
 */
function ayudawp_euw_enqueue_admin( $hook ) {

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen ) {
		return;
	}

	$is_cpt_screen      = ( 'ayudawp_withdrawal' === $screen->post_type );
	$is_settings_screen = ( false !== strpos( (string) $screen->id, 'ayudawp-euw-settings' ) );

	if ( ! $is_cpt_screen && ! $is_settings_screen ) {
		return;
	}

	wp_enqueue_style(
		'ayudawp-euw-admin',
		AYUDAWP_EUW_URL . 'assets/css/admin.css',
		array(),
		AYUDAWP_EUW_VERSION
	);

	// Thickbox is only needed on the settings page where the promo banner lives.
	if ( $is_settings_screen ) {
		add_thickbox();
	}
}
add_action( 'admin_enqueue_scripts', 'ayudawp_euw_enqueue_admin' );
