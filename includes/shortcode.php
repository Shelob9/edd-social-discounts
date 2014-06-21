<?php
/**
 * Shortcode
 * 
 * @param  array $atts
 * @param  $content
 * @return object
 * @since  2.0
 */
function edd_social_discounts_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array(
			'id' 		=> '',
			'title' 	=> '',
			'message' 	=> '',
			'tweet' 	=> '',
			'locked'	=> ''
		), $atts, 'edd_social_discount' )
	);

	$content = edd_social_discounts_share_box( $id, $title, $message, $tweet, $locked );

	return $content;
}
add_shortcode( 'edd_social_discount', 'edd_social_discounts_shortcode' );