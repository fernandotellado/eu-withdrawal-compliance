<?php
/**
 * Uninstall handler.
 *
 * Removes plugin options. Withdrawal request log entries (CPT) are preserved
 * by default for legal record-keeping. Set the AYUDAWP_EUW_DELETE_DATA constant
 * to true in wp-config.php to wipe everything, including stored requests.
 *
 * @package AyudaWP_EU_Withdrawal
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove plugin options.
delete_option( 'ayudawp_euw_notify_email' );
delete_option( 'ayudawp_euw_page_id' );

// Optionally delete all withdrawal records.
if ( defined( 'AYUDAWP_EUW_DELETE_DATA' ) && AYUDAWP_EUW_DELETE_DATA ) {

	$posts = get_posts(
		array(
			'post_type'      => 'ayudawp_withdrawal',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	foreach ( $posts as $post_id ) {
		wp_delete_post( $post_id, true );
	}
}
