<?php
/**
 * Admin UI: custom columns, metaboxes, and CPT details.
 *
 * @package AyudaWP_EU_Withdrawal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom columns to the withdrawal listing.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function ayudawp_euw_admin_columns( $columns ) {

	$new = array(
		'cb'                       => $columns['cb'] ?? '',
		'title'                    => __( 'Reference', 'eu-withdrawal-compliance' ),
		'ayudawp_euw_customer'     => __( 'Customer', 'eu-withdrawal-compliance' ),
		'ayudawp_euw_email'        => __( 'Email', 'eu-withdrawal-compliance' ),
		'ayudawp_euw_order'        => __( 'Order', 'eu-withdrawal-compliance' ),
		'ayudawp_euw_scope'        => __( 'Scope', 'eu-withdrawal-compliance' ),
		'ayudawp_euw_status'       => __( 'Status', 'eu-withdrawal-compliance' ),
		'date'                     => __( 'Date', 'eu-withdrawal-compliance' ),
	);

	return $new;
}
add_filter( 'manage_ayudawp_withdrawal_posts_columns', 'ayudawp_euw_admin_columns' );

/**
 * Render values for each custom column.
 *
 * @param string $column Column key.
 * @param int    $post_id Post ID.
 */
function ayudawp_euw_admin_column_content( $column, $post_id ) {

	switch ( $column ) {
		case 'ayudawp_euw_customer':
			echo esc_html( get_post_meta( $post_id, '_ayudawp_euw_name', true ) );
			break;

		case 'ayudawp_euw_email':
			$email = get_post_meta( $post_id, '_ayudawp_euw_email', true );
			if ( $email ) {
				printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $email ) );
			}
			break;

		case 'ayudawp_euw_order':
			$order   = get_post_meta( $post_id, '_ayudawp_euw_order', true );
			$wc_id   = absint( get_post_meta( $post_id, '_ayudawp_euw_wc_order_id', true ) );

			if ( $wc_id && function_exists( 'wc_get_order' ) ) {
				$wc_order = wc_get_order( $wc_id );
				if ( $wc_order ) {
					$edit_url = method_exists( $wc_order, 'get_edit_order_url' )
						? $wc_order->get_edit_order_url()
						: admin_url( 'post.php?post=' . $wc_id . '&action=edit' );
					printf(
						'<a href="%1$s">%2$s</a>',
						esc_url( $edit_url ),
						esc_html( $order )
					);
					break;
				}
			}

			echo esc_html( $order );
			break;

		case 'ayudawp_euw_scope':
			$scope = get_post_meta( $post_id, '_ayudawp_euw_scope', true );
			echo esc_html(
				'partial' === $scope
					? __( 'Partial', 'eu-withdrawal-compliance' )
					: __( 'Full', 'eu-withdrawal-compliance' )
			);
			break;

		case 'ayudawp_euw_status':
			$status = get_post_meta( $post_id, '_ayudawp_euw_status', true );
			$labels = array(
				'pending'   => __( 'Pending', 'eu-withdrawal-compliance' ),
				'accepted'  => __( 'Accepted', 'eu-withdrawal-compliance' ),
				'rejected'  => __( 'Rejected', 'eu-withdrawal-compliance' ),
				'completed' => __( 'Completed', 'eu-withdrawal-compliance' ),
			);

			$label = isset( $labels[ $status ] ) ? $labels[ $status ] : __( 'Pending', 'eu-withdrawal-compliance' );
			$class = 'ayudawp-euw-status-' . sanitize_html_class( $status ? $status : 'pending' );

			printf(
				'<span class="ayudawp-euw-status %1$s">%2$s</span>',
				esc_attr( $class ),
				esc_html( $label )
			);
			break;
	}
}
add_action( 'manage_ayudawp_withdrawal_posts_custom_column', 'ayudawp_euw_admin_column_content', 10, 2 );

/**
 * Add metabox with full request details.
 */
function ayudawp_euw_register_metabox() {

	add_meta_box(
		'ayudawp_euw_details',
		__( 'Withdrawal request details', 'eu-withdrawal-compliance' ),
		'ayudawp_euw_metabox_content',
		'ayudawp_withdrawal',
		'normal',
		'high'
	);

	add_meta_box(
		'ayudawp_euw_status',
		__( 'Status', 'eu-withdrawal-compliance' ),
		'ayudawp_euw_metabox_status',
		'ayudawp_withdrawal',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'ayudawp_euw_register_metabox' );

/**
 * Render the details metabox.
 *
 * @param WP_Post $post Current post.
 */
function ayudawp_euw_metabox_content( $post ) {

	$fields = array(
		'name'       => array( 'label' => __( 'Customer name', 'eu-withdrawal-compliance' ), 'meta' => '_ayudawp_euw_name' ),
		'email'      => array( 'label' => __( 'Email', 'eu-withdrawal-compliance' ), 'meta' => '_ayudawp_euw_email' ),
		'order'      => array( 'label' => __( 'Order number', 'eu-withdrawal-compliance' ), 'meta' => '_ayudawp_euw_order' ),
		'order_date' => array( 'label' => __( 'Order date', 'eu-withdrawal-compliance' ), 'meta' => '_ayudawp_euw_order_date' ),
		'scope'      => array( 'label' => __( 'Scope', 'eu-withdrawal-compliance' ), 'meta' => '_ayudawp_euw_scope' ),
		'ip'         => array( 'label' => __( 'IP address', 'eu-withdrawal-compliance' ), 'meta' => '_ayudawp_euw_ip' ),
		'user_agent' => array( 'label' => __( 'User agent', 'eu-withdrawal-compliance' ), 'meta' => '_ayudawp_euw_user_agent' ),
	);

	echo '<table class="ayudawp-euw-meta-table"><tbody>';

	foreach ( $fields as $field ) {
		$value = get_post_meta( $post->ID, $field['meta'], true );

		printf(
			'<tr><th scope="row">%1$s</th><td>%2$s</td></tr>',
			esc_html( $field['label'] ),
			esc_html( $value )
		);
	}

	echo '</tbody></table>';

	echo '<h4>' . esc_html__( 'Additional information', 'eu-withdrawal-compliance' ) . '</h4>';
	echo '<div class="ayudawp-euw-details">';
	echo wp_kses_post( wpautop( $post->post_content ) );
	echo '</div>';
}

/**
 * Render the status metabox with a save button.
 *
 * @param WP_Post $post Current post.
 */
function ayudawp_euw_metabox_status( $post ) {

	wp_nonce_field( 'ayudawp_euw_save_status', 'ayudawp_euw_status_nonce' );

	$status   = get_post_meta( $post->ID, '_ayudawp_euw_status', true );
	$status   = $status ? $status : 'pending';

	$statuses = array(
		'pending'   => __( 'Pending', 'eu-withdrawal-compliance' ),
		'accepted'  => __( 'Accepted', 'eu-withdrawal-compliance' ),
		'rejected'  => __( 'Rejected', 'eu-withdrawal-compliance' ),
		'completed' => __( 'Completed', 'eu-withdrawal-compliance' ),
	);

	echo '<p>';
	echo '<label for="ayudawp_euw_status_field"><strong>' . esc_html__( 'Set status', 'eu-withdrawal-compliance' ) . '</strong></label><br>';
	echo '<select name="ayudawp_euw_status_field" id="ayudawp_euw_status_field" style="width:100%">';

	foreach ( $statuses as $key => $label ) {
		printf(
			'<option value="%1$s" %2$s>%3$s</option>',
			esc_attr( $key ),
			selected( $status, $key, false ),
			esc_html( $label )
		);
	}

	echo '</select>';
	echo '</p>';
	echo '<p class="description">' . esc_html__( 'Track the internal handling state of this request.', 'eu-withdrawal-compliance' ) . '</p>';
}

/**
 * Save status changes from the metabox.
 *
 * @param int $post_id Post ID.
 */
function ayudawp_euw_save_status( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! isset( $_POST['ayudawp_euw_status_nonce'] )
		|| ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['ayudawp_euw_status_nonce'] ) ),
			'ayudawp_euw_save_status'
		)
	) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( get_post_type( $post_id ) !== 'ayudawp_withdrawal' ) {
		return;
	}

	if ( isset( $_POST['ayudawp_euw_status_field'] ) ) {
		$status  = sanitize_key( wp_unslash( $_POST['ayudawp_euw_status_field'] ) );
		$allowed = array( 'pending', 'accepted', 'rejected', 'completed' );

		if ( in_array( $status, $allowed, true ) ) {
			update_post_meta( $post_id, '_ayudawp_euw_status', $status );
		}
	}
}
add_action( 'save_post_ayudawp_withdrawal', 'ayudawp_euw_save_status' );
