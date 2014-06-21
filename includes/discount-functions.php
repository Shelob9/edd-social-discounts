<?php

/**
 * Retrieve the products the social discounts
 *
 * @since 2.1
 * @param int $code_id Discount ID
 * @return array $shared_products IDs of the required products
 */
function edd_social_discounts_get_social_discount_products( $code_id = null ) {
	$products = get_post_meta( $code_id, '_edd_discount_social_discount_products', true );

	if ( empty( $products ) || ! is_array( $products ) ) {
		$products = array();
	}

	return (array) apply_filters( 'edd_social_discounts_get_social_discount_products', $products, $code_id );
}





// $amount = ( $price - apply_filters( 'edd_get_cart_item_discount_amount', $discounted_price, $discounts, $item, $price ) );






/**
 * All download IDs that have a discount applied against them
 * 
 * @since 2.1
 * @return  $share_ids download IDs that have discounts applied to them.
 */
function edd_social_discounts_get_shared_product_ids() {
	// get all discount IDs
	$discounts = edd_get_discounts();

	$discount_ids = array();
	$share_ids = array();
	
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




function edd_social_discounts_edd_discounted_amount( $discounted_price, $code, $base_price, $item ) {
	$discount_id = edd_get_discount_id_by_code( $code );
	$rate        = edd_get_discount_amount( $discount_id );
//	var_dump('hello');
//	return;
	//$discounted_price = 50;

	$products_to_discount =  edd_social_discounts_get_social_discount_products( $discount_id );
	//var_dump( edd_social_discounts_get_social_discount_products( $discount_id ) );

	$discounted_price = 0;

	foreach ( $products_to_discount as $id ) {
		
		if ( ( $id == $item['id'] ) ) {
			$discounted_price = $base_price - ( $base_price * ( $rate / 100 ) );
			return $discounted_price;
		//	return $discounted_price;
		//	return $discounted_price;
		//	echo 'yes';
		//	$discounted_price = $base_price - ( $base_price * ( $rate / 100 ) );
		//	continue;
		}
		
	}
	//var_dump( $discounted_price );

	return $discounted_price;
}
//add_filter( 'edd_discounted_amount', 'edd_social_discounts_edd_discounted_amount', 10, 4 );






























/**
 * Remove discount code label from checkout
 *
 * @since  2.1
 * @todo include new filter in EDD core so it's easier to change
 */
function edd_social_discounts_checkout_html( $html, $discounts, $rate, $remove_url ) {

	if ( ! $discounts ) {
		$discounts = edd_get_cart_discounts();
	}

	if ( ! $discounts ) {
		return;
	}

	$html = '';

	foreach ( $discounts as $discount ) {
		$discount_id  = edd_get_discount_id_by_code( $discount );

		$social_products  = edd_social_discounts_get_social_discount_products( $discount_id );

		// not sharing discount, return
		if ( empty( $social_products ) ) {
			return $html;
		}
	
		$rate = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );

		$remove_url   = add_query_arg(
			array(
				'edd_action'    => 'remove_cart_discount',
				'discount_id'   => $discount_id,
				'discount_code' => $discount
			),
			edd_get_checkout_uri()
		);

		$discount_text = __( 'Share Discount:', 'edd-social-discounts' );

		$html .= "<span class=\"edd_discount\">\n";
			$html .= "<span class=\"edd_discount_rate\">$discount_text &nbsp;&ndash;&nbsp;$rate</span>\n";
			$html .= "<a href=\"$remove_url\" data-code=\"$discount\" class=\"edd_discount_remove\"></a>\n";
		$html .= "</span>\n";

	}

	return $html;
}
add_filter( 'edd_get_cart_discounts_html','edd_social_discounts_checkout_html', 10, 4 );



















/**
 * Prevents the discount from working at checkout if the discount is a sharing discount
 *
 * @since  2.1
 * @todo  only apply discount to shared product
 * @uses  edd_social_discounts_product_reqs_met()
 * @todo  check that the download ID is in EDD()->session->get( 'edd_shared_ids' ), meaning the download has actually been shared, else return
 */
function edd_sd_edd_is_discount_valid( $return, $discount_id, $code, $user ) {

	//$social_products  = edd_social_discounts_get_social_discount_products( $discount_id );

	//$return      = false;

	// get discount id
	$discount_id = edd_get_discount_id_by_code( $code );
	
	// get user. We'll use this for seeing if it's already been used before
	$user        = trim( $user );

	// get array of products this discount is for
	$product_reqs = edd_social_discounts_get_social_discount_products( $discount_id );

	//wp_die( $product_reqs );

	// return if current discount isn't a social discount
	if ( ! $product_reqs )
		return $return;

	// get array of current items in cart
	$cart_items   = edd_get_cart_contents();

	// pluck the cart ids from the array
	$cart_ids     = $cart_items ? wp_list_pluck( $cart_items, 'id' ) : null;

	// the array of download IDs that have been shared within the purchase session
	$edd_shared_ids = EDD()->session->get( 'edd_shared_ids' );
	$edd_shared_ids = ! empty( $edd_shared_ids ) ? $edd_shared_ids : array();

	if ( $discount_id !== false ) {
		
		// if cart ids exist
		if ( $cart_ids ) {

			foreach ( $cart_ids as $id ) {
				// product must be in sharing discount array 
				// product must have been shared (prevents customer just typing discount code in at checkout)
				if ( 
					in_array( $id, $product_reqs ) && 
				//	in_array( $id, $edd_shared_ids ) &&						// test mode. Allows discounts to be entered at checkout
					edd_is_discount_active( $discount_id ) &&				// discount must be active and not expired - works
					edd_discount_is_min_met( $discount_id ) &&				// discount must meet minimum amount - works
					edd_is_discount_started( $discount_id ) &&				// check the date that the discount starts
					! edd_is_discount_maxed_out( $discount_id ) &&			// check that the discount doesn't exceed it's max uses
					! edd_is_discount_used( $code, $user, $discount_id )	// discount limit per user - works
					) {
					// the discount is valid
					$return = true;
					// break out of foreach when first instance is found
					break;
				}
				else {
					$return = false;
				}
			}

		}
			

	}

	return $return;
}
add_filter( 'edd_is_discount_valid', 'edd_sd_edd_is_discount_valid', 10, 4 );









































// return false if ANY of the downloads assigned a sharing discount isn't in the cart


/**
 * Checks to see if the required products are in the cart
 *
 * @since 1.5
 * @param int $code_id Discount ID
 * @return bool $ret Are required products in the cart?
 */
function edd_social_discounts_product_reqs_met( $code_id = null ) {
	
	
	
	$ret          = false;

	// if ( empty( $product_reqs ) ) {
	// 	$ret = true;
	// }

	// // Ensure we have requirements before proceeding
	// if ( ! $ret ) {
	// 	foreach ( $product_reqs as $download_id ) {
	// 		if ( ! edd_item_in_cart( $download_id ) ) {
	// 			$ret = false;
	// 			break;
	// 		}
	// 	}
	// }

//	return (bool) apply_filters( 'edd_social_discounts_product_reqs_met', $ret, $code_id );
	return false;

}








// filter function that determines the discount to be used for EACH download

function edd_sd_edd_get_cart_item_discount_amount( $default, $item ) {

	// stops discount from working
	//return false;

	//$social_products   = edd_social_discounts_get_social_discount_products( $code_id );


	

	// Retrieve all discounts applied to the cart
	$discounts = edd_get_cart_discounts();
	
	if( $discounts ) {

		foreach ( $discounts as $discount ) {

			// at the moment it's test
			
			$code_id           = edd_get_discount_id_by_code( $discount );

			// get array of downloads based on this discount code
			$social_products   = edd_social_discounts_get_social_discount_products( $code_id );

		//	var_dump( $social_products );

			// this discount applies to the item in the cart
			if ( ! empty( $social_products ) ) {
				return edd_get_cart_item_discount_amount2( $item );
			}
			// this discount does not apply to the item in cart, return
			else {
				return $default;
			}
		}
	}

}
add_filter( 'edd_get_cart_content_details_item_discount_amount', 'edd_sd_edd_get_cart_item_discount_amount', 10, 2 );
add_filter( 'edd_get_cart_item_tax_item_discount_amount', 'edd_sd_edd_get_cart_item_discount_amount', 10, 2 );


/**
 * New discount function that runs when an item in the cart has the currently applied discount recorded against it
 * @todo  doesn't need the foreach, see above
 */
function edd_get_cart_item_discount_amount2( $item = array() ) {

	$amount           = 0;
	$price            = edd_get_cart_item_price( $item['id'], $item['options'], edd_prices_include_tax() );
	$discounted_price = $price;

	// Retrieve all discounts applied to the cart
	$discounts = edd_get_cart_discounts();

	if( $discounts ) {

		foreach ( $discounts as $discount ) {

			$code_id           = edd_get_discount_id_by_code( $discount );
			$social_products   = edd_social_discounts_get_social_discount_products( $code_id );

			if ( ! empty( $social_products ) ) {

				foreach ( $social_products as $download_id ) {
					// flat
					if ( 'flat' === edd_get_discount_type( $code_id ) ) {
							$discounted_amount = edd_get_discount_amount( $code_id );
							$discounted_amount = ( $discounted_amount / edd_get_cart_quantity() );
							$discounted_price -= $discounted_amount;

					}
					// percentage
					elseif ( 'percent' === edd_get_discount_type( $code_id ) ) {
						if ( $download_id == $item['id'] ) {
							$discounted_price = edd_get_discounted_amount( $discount, $price );
						}
					}
				}
			}

		} // end foreach

		$amount = ( $price - $discounted_price );
	}

	return $amount;
}








/**
 * Filter
 */
function edd_social_discounts_edd_get_cart_item_discounted_price( $discounted_price, $discounts, $item, $price ) {

	foreach ( $discounts as $discount ) {

		$code_id           = edd_get_discount_id_by_code( $discount );

		$social_discount_ids = edd_social_discounts_get_social_discount_products( $code_id );

		if ( ! $social_discount_ids )
			return $discounted_price;

		// percentage
		//if ( ! empty( $social_discount_ids ) && ! edd_social_discounts_is_discount_global( $code_id ) ) {

			// This is a product(s) specific discount
			foreach ( $social_discount_ids as $download_id ) {
				echo 'yes';
				if ( $download_id == $item['id'] ) {
					$discounted_price = edd_get_discounted_amount( $discount, $price, $item );
					echo 'yes';
				}
			}
		//}
		
		

	}

	return $discounted_price;
}
//add_filter( 'edd_get_cart_item_discounted_amount', 'edd_social_discounts_edd_get_cart_item_discounted_price', 10, 4 );




































function edd_social_discounts_edd_get_cart_item_discount_amount( $discounted_price, $discounts, $item, $price ) {

	foreach ( $discounts as $discount ) {

		$code_id           = edd_get_discount_id_by_code( $discount );
	//	$reqs              = edd_get_discount_product_reqs( $code_id );
	//	$excluded_products = edd_get_discount_excluded_products( $code_id );

		$social_discount_ids = edd_social_discounts_get_social_discount_products( $code_id );

		if ( ! empty( $social_discount_ids ) && ! edd_social_discounts_is_discount_global( $code_id ) ) {

			// This is a product(s) specific discount

			foreach ( $social_discount_ids as $download_id ) {
				
				if ( $download_id == $item['id'] ) {
				//	echo 'hello';
					$discounted_price = edd_get_discounted_amount( $discount, $price );
				//	var_dump( $discounted_price );
				}
				
			}

		}

	}
		
	//var_dump( $discounted_price );
	//$discounted_price = 10;
	return $discounted_price;
}
//add_filter( 'edd_get_cart_item_discount_amount', 'edd_social_discounts_edd_get_cart_item_discount_amount', 9, 4 );







function edd_social_discounts_this_should_work( $item, $code_id, $price, $discount ) {
	//$code_id           = edd_get_discount_id_by_code( $discount );
	// get IDs 

	//var_dump( $discount );

			
			
		//	var_dump( $social_discount_ids );

			//var_dump( edd_social_discounts_get_social_discount_products( $discount_id ) );

			//if ( ! empty( $social_discount_ids ) && ! edd_social_discounts_is_discount_global( $code_id ) ) {

				// This is a product(s) specific discount

	//$rate        = edd_get_discount_amount( $discount );

	$products_to_discount = edd_social_discounts_get_social_discount_products( $code_id );

	foreach ( $products_to_discount as $download_id ) {

		if ( $download_id == $item['id'] ) {
		//	echo 'hello';
		//	$discounted_price = edd_get_discounted_amount( $discount, $price, $item );
		//	$discounted_price = $price - ( $price * ( $rate / 100 ) );
			$discounted_price = 5;

		//	return $discounted_price;
		}
	}

			//}
}
//add_action( 'this_should_work', 'edd_social_discounts_this_should_work', 9999, 4 );






















// function edd_get_discounted_amount1232( $code, $base_price ) {
// 	$discount_id = edd_get_discount_id_by_code( $code );
// 	$type        = edd_get_discount_type( $discount_id );
// 	$rate        = edd_get_discount_amount( $discount_id );

// 	if ( $type == 'flat' ) {
// 		// Set amount
// 		$discounted_price = $base_price - $rate;
// 		if ( $discounted_price < 0 ) {
// 			$discounted_price = 0;
// 		}

// 	} else {
// 		// Percentage discount
// 		$discounted_price = $base_price - ( $base_price * ( $rate / 100 ) );
// 	}

// 	return apply_filters( 'edd_discounted_amount', $discounted_price );
//}




















/**
 * Check if a discount is not global
 *
 * By default discounts are applied to all products in the cart. Non global discounts are
 * applied only to the products selected as requirements
 *
 * @since 1.5
 * @param int $code_id Discount ID
 * @return array $product_reqs IDs of the required products
 * @return bool Whether or not discount code is global
 */
function edd_social_discounts_is_discount_global( $code_id = 0 ) {
	return (bool) get_post_meta( $code_id, '_edd_discount_social_discount_global', true );
}



function edd_social_discounts_edd_is_social_discount( $code_id = 0 ) {
	return (bool) get_post_meta( $code_id, '_edd_discount_is_social_discount', true );
}









/**
 * Get the discount IDs by download ID
 */

function edd_social_discounts_get_discount_id() {

	
	// $args = array(
	// 	'post_type' 		=> 'edd_discount',
	// 	'posts_per_page' 	=> -1,
	// 	'post_status' 		=> 'active',
	// //	'meta_compare'		=> 'LIKE',
	// 	'meta_query' => array(
	// 	      array(
	// 	          'key' => '_edd_discount_social_discount_products',
	// 	          'value' => array( 67, 4 ),
	// 	          'compare' => 'IN',
	// 	      )
	// 	  )
	// );

	$ids = array(67, 4);
	//$ids = '67, 4';

	 $args = array(
	   'post_type' => 'edd_discount',
	   'meta_key' => '_edd_discount_social_discount_products',
	   'post_status'       => 'active',
	   'posts_per_page'    => -1,
	   'meta_query' => array(
	       array(
	           'key' => '_edd_discount_social_discount_products',
	           'value' => $ids, //array
	           'compare' => 'IN',
	       )
	   )
	 );

	

	$discount = new WP_Query( $args );

	return $discount;
}