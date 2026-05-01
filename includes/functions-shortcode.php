<?php
/**
 * Shortcode and block registration.
 *
 * @package AyudaWP_EU_Withdrawal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the [ayudawp_withdrawal_form] shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string Form HTML.
 */
function ayudawp_euw_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'order_id' => '',
			'email'    => '',
		),
		$atts,
		'ayudawp_withdrawal_form'
	);

	return ayudawp_euw_render_form( $atts );
}
add_shortcode( 'ayudawp_withdrawal_form', 'ayudawp_euw_shortcode' );
