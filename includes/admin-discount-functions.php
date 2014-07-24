<?php

/**
 * Get array of all download IDs that have a discount applied to them
 * Used for edd_social_discounts_edit_discount_form() and edd_social_discounts_add_to_discount_form()
 * 
 * @since  2.1
 * @return $share_ids download IDs that have discounts applied to them.
 */
function edd_social_discounts_get_shared_product_ids() {
	// get all discount IDs
	$discounts    = edd_get_discounts();
	$discount_ids = array();
	$share_ids    = array();
	
	if ( $discounts ) {
		foreach ( $discounts as $discount ) {
			$discount_ids[] = $discount->ID;
		}
	}

	foreach ( $discount_ids as $id ) {
		$products = get_post_meta( $id, '_edd_discount_social_discount_products', true );

		if ( $products ) {
			foreach ($products as $product) {
				array_push( $share_ids, $product );
			}
		}
	}

	return (array) apply_filters( 'edd_social_discounts_get_share_array', $share_ids );
}
