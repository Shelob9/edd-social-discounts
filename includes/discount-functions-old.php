<?php




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

	// we don't need to set this because it's carried into the filter
	//$html = '';

	foreach ( $discounts as $discount ) {
		$discount_id  = edd_get_discount_id_by_code( $discount );
		$rate = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );

		$social_products  = edd_social_discounts_get_social_discount_products( $discount_id );

		// not sharing discount, return
		if ( empty( $social_products ) ) {
			return $html;
		}
	
		

		$remove_url   = add_query_arg(
			array(
				'edd_action'    => 'remove_cart_discount',
				'discount_id'   => $discount_id,
				'discount_code' => $discount
			),
			edd_get_checkout_uri()
		);

	//	$discount_text = __( 'Share Discount:', 'edd-social-discounts' );
		$discount_text = __( 'Share Discount:', 'edd-social-discounts' );

		$html .= "<span class=\"edd_discount\">\n";
			$html .= "<span class=\"edd_discount_rate\">$discount_text &nbsp;&ndash;&nbsp;$rate</span>\n";
			$html .= "<a href=\"$remove_url\" data-code=\"$discount\" class=\"edd_discount_remove\"></a>\n";
		$html .= "</span>\n";

	}

	return $html;
}
//add_filter( 'edd_get_cart_discounts_html', 'edd_social_discounts_checkout_html', 10, 4 );


