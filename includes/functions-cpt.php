<?php
/**
 * Custom Post Type to log withdrawal requests.
 *
 * @package AyudaWP_EU_Withdrawal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the withdrawal CPT used to store every request received.
 *
 * It is non-public, only visible in the admin area, so logs cannot be
 * crawled or accessed from the frontend.
 */
function ayudawp_euw_register_cpt() {

	$labels = array(
		'name'               => _x( 'Withdrawals', 'post type general name', 'eu-withdrawal-compliance' ),
		'singular_name'      => _x( 'Withdrawal', 'post type singular name', 'eu-withdrawal-compliance' ),
		'menu_name'          => _x( 'Withdrawals', 'admin menu', 'eu-withdrawal-compliance' ),
		'name_admin_bar'     => _x( 'Withdrawal', 'add new on admin bar', 'eu-withdrawal-compliance' ),
		'all_items'          => __( 'Withdrawals', 'eu-withdrawal-compliance' ),
		'view_item'          => __( 'View withdrawal', 'eu-withdrawal-compliance' ),
		'search_items'       => __( 'Search withdrawals', 'eu-withdrawal-compliance' ),
		'not_found'          => __( 'No withdrawals found.', 'eu-withdrawal-compliance' ),
		'not_found_in_trash' => __( 'No withdrawals in Trash.', 'eu-withdrawal-compliance' ),
	);

	// When WooCommerce is active, nest the CPT under the WooCommerce menu so
	// withdrawal requests live next to orders. Otherwise expose a top-level menu.
	$show_in_menu = class_exists( 'WooCommerce' ) ? 'woocommerce' : true;

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => $show_in_menu,
		'show_in_rest'       => false,
		'query_var'          => false,
		'rewrite'            => false,
		'capability_type'    => 'post',
		'capabilities'       => array(
			'create_posts' => 'do_not_allow', // Only created via frontend form.
		),
		'map_meta_cap'       => true,
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 25,
		'menu_icon'          => 'dashicons-undo',
		'supports'           => array( 'title' ),
	);

	register_post_type( 'ayudawp_withdrawal', $args );
}
add_action( 'init', 'ayudawp_euw_register_cpt' );

/**
 * Register a custom status for processed withdrawals.
 */
function ayudawp_euw_register_status() {

	register_post_status(
		'ayudawp_processed',
		array(
			'label'                     => _x( 'Processed', 'withdrawal status', 'eu-withdrawal-compliance' ),
			'public'                    => false,
			'internal'                  => false,
			'protected'                 => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			/* translators: %s: number of items. */
			'label_count'               => _n_noop(
				'Processed <span class="count">(%s)</span>',
				'Processed <span class="count">(%s)</span>',
				'eu-withdrawal-compliance'
			),
		)
	);
}
add_action( 'init', 'ayudawp_euw_register_status' );
