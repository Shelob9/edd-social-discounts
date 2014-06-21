<?php

/**
 * Load discount
 *
 * @since 2.0
*/
function edd_social_discounts_share_product() {
	global $post;
	//global $edd_options;

	if ( ! isset( $_POST['product_id'] ) )
		return;

	$post_id = isset( $_POST['product_id'] ) ? $_POST['product_id'] : '';

	// check nonce
	check_ajax_referer( 'edd_sd_nonce', 'nonce' );


	// if download is locked, replace it with button
	// 

		

	// global discount
	// will put this back in after
	// get discount code's ID from plugin settings
//		$discount = edd_get_option( 'edd_sd_discount_code', '' );

	// single download discount
	$discount = get_post_meta( $_POST['product_id'], 'edd_social_discount', true );

	// get discount code by ID
	$discount = edd_get_discount_code( $discount );

	// set cart discount. Discount will only be applied if discount exists.
	// can only be set after sharing, Will not work from cart
	edd_set_cart_discount( $discount );

	// purchase was shared
	EDD()->session->set( 'edd_shared', true );

	// store the download ID temporarily
	EDD()->session->set( 'edd_shared_id', $_POST['product_id'] );
	
	$success_title 		= edd_social_discounts_is_locked( $post_id ) ? edd_social_discounts_share_to_unlock_success_title( $post_id ) : edd_social_discounts_success_title( $post_id );
	$success_message 	= edd_social_discounts_is_locked( $post_id ) ? edd_social_discounts_share_to_unlock_success_message( $post_id ) : edd_social_discounts_success_message( $post_id );

	$return = apply_filters( 'edd_social_discounts_ajax_return', array(
		'msg'				=> 'valid',
		'success_title'		=> $success_title,
		'success_message'	=> $success_message,
		'product_id'		=> $_POST['product_id'],
	//	'button'			=> html_entity_decode( edd_get_purchase_link( array( 'download_id' => 5 ) ), ENT_COMPAT, 'UTF-8' )
	) );

	if ( edd_social_discounts_is_locked( $post_id ) ) {

	//	if ( $_POST['product_id'] ) {
			$button = edd_get_purchase_link( array( 'download_id' => $_POST['product_id'], 'ref' => 'ajax' ) );
		//	$button = edd_social_discounts_edd_get_purchase_link( $_POST['product_id'] );
			$return['button'] = html_entity_decode( trim( $button ), ENT_COMPAT, 'UTF-8' );
		// } else {
		// 	$button = '';
		// 	$return['button'] = '';
		// }

		

	}



	// if ( isset( $post_id ) ) {
	// 	$button = edd_get_purchase_link( array( 'download_id' => $post_id ) );

	// 	$return['button'] = html_entity_decode( $button, ENT_COMPAT, 'UTF-8' );
	// 	// if ( $download_id )
	// 	// 	$button = isset ( $post->ID ) ? edd_get_purchase_link( array( 'download_id' => $download_id ) ) : null;
	// }

	echo json_encode( $return );

	edd_die();
}

// share product + apply discount using ajax
add_action( 'wp_ajax_share_product', 'edd_social_discounts_share_product', 9999 );
add_action( 'wp_ajax_nopriv_share_product', 'edd_social_discounts_share_product', 9999 );

function edd_social_discounts_edd_get_purchase_link( $download_id ) {
	if ( $download_id )
		return edd_get_purchase_link( array( 'download_id' => $download_id  ) );
	else {
		return 'shit';
	}
}