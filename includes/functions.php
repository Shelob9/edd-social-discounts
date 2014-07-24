<?php
/**
 * Functions
 */

/**
 * Success Title when download has been shared
 *
 * @since 2.0
*/
function edd_social_discounts_success_title( $product_id ) {
	$title = edd_get_option( 'edd_sd_success_title', __( 'Thanks for sharing!', 'edd-social-discounts' ) );

	return apply_filters( 'edd_social_discounts_success_title', $title, $product_id );
}

/**
 * Success Message when download has been shared
 *
 * @since 2.0
*/
function edd_social_discounts_success_message( $product_id ) {
	$message = edd_get_option( 'edd_sd_success_message', __( 'Add this product to your cart and the discount will be applied.', 'edd-social-discounts' ) );

	return apply_filters( 'edd_social_discounts_success_message', $message, $product_id );
}

/**
 * Share to unlock success title
 *
 * @since 2.1
*/
function edd_social_discounts_share_to_unlock_success_title() {
	$title = edd_get_option( 'edd_sd_share_to_unlock_success_title', __( 'Thanks for sharing!', 'edd-social-discounts' ) );

	return apply_filters( 'edd_social_discounts_share_to_unlock_success_title', $title );
}

/**
 * Share to unlock success message
 *
 * @since 2.1
*/
function edd_social_discounts_share_to_unlock_success_message( $product_id ) {
	$message = edd_get_option( 'edd_sd_share_to_unlock_success_message', __( 'This product is now unlocked and you can add it to your cart.', 'edd-social-discounts' ) );

	return apply_filters( 'edd_social_discounts_share_to_unlock_success_message', $message, $product_id );
}

/**
 * Check for existance of shortcode
 * 
 * @param  string  $shortcode
 * @return boolean
 * @since  2.0
 */
function edd_social_discounts_has_shortcode( $shortcode = '' ) {
	global $post;

	// false because we have to search through the post content first
	$found = false;

	// if no short code was provided, return false
	if ( !$shortcode ) {
		return $found;
	}

	if (  is_object( $post ) && stripos( $post->post_content, '[' . $shortcode ) !== false ) {
		// we have found the short code
		$found = true;
	}

	// return our final results
	return $found;
}

/**
 * Check that each social network is enabled
 * @param  string  $network
 * @return boolean
 * @since  2.0
 */
function edd_social_discounts_is_enabled( $network = '' ) {
	global $edd_options;

	$networks = edd_get_option( 'edd_sd_services', '' );

	// if network is passed as parameter
	if ( $network ) {
		switch ( $network ) {

			case 'twitter':
				return isset( $networks[$network] );
				break;

			case 'facebook':
				return isset( $networks[$network] );
				break;
				
			case 'googleplus':
				return isset( $networks[$network] );
				break;
				
			case 'linkedin':
				return isset( $networks[$network] );
				break;			
			
		}
	}
	elseif ( $networks ) {
		return true;
	}

}

/**
 * Checks to see if the download is locked or not
 * 
 * @return boolean true if locked, false otherwise
 * @since  2.1
 */
function edd_social_discounts_is_locked( $download_id = '' ) {

	if ( ! $download_id )
		return;

	$locked = get_post_meta( $download_id, 'edd_social_discounts_locked', true );

	if ( $locked )
		return (bool) true;

	return (bool) false;
}

/**
 * Is discount active within extension's setttngs
 * 
 * @return boolean true if discount, false otherwise
 * @since  2.0
 */
function edd_social_discounts_is_discount_active() {
	global $edd_options;

	$discount_active = isset( $edd_options['edd_sd_discount_code'] ) && '0' != $edd_options['edd_sd_discount_code'] ? true : false;
	
	return $discount_active;
}