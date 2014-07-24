<?php
/**
 * EDD Social Discounts - Discounts class
 *
 */
class EDD_Social_Discounts_Discounts {

	public function __construct() {
		
	//	add_filter( 'edd_is_discount_valid', 							 array( $this, 'is_discount_valid' ), 10, 4 );

		add_filter( 'edd_get_cart_content_details_item_discount_amount', array( $this, 'get_cart_item_discount_amount' ), 10, 2 );
	//	add_filter( 'edd_get_cart_item_tax_item_discount_amount',        array( $this, 'get_cart_item_discount_amount' ), 10, 2 );
	//	add_filter( 'edd_get_cart_discounts_html',                       array( $this, 'show_each_social_discount' ), 10, 4 );
		
	}

		
	/**
	 * Determines the discount to be used for EACH download
	 * Something wrong with this function
	 *
	 * If I enter 1, it takes 100% of the correct download, but subsequent discounts do not work
	 * If I enter 2 as the discount, then 1, it takes 100% off the entire order
	 *
	 * @todo
	 * @uses  edd_get_cart_item_discount_amount2()
	 * @since  2.1
	 */

	// $discount   = apply_filters( 'edd_get_cart_content_details_item_discount_amount', edd_get_cart_item_discount_amount( $item ), $item );

	public function get_cart_item_discount_amount( $discount_amount, $download ) {

		// need to check if THIS download is a social download, WITH a social discount applied
		
		$download_id = $download['id'];
		
		// Retrieve all discounts applied to the cart
		$discounts = edd_get_cart_discounts();
		
		if ( $discounts ) {

			foreach ( $discounts as $discount ) {

				$discount_id = edd_get_discount_id_by_code( $discount );

//				var_dump( $download_id );

			//	if ( $this->is_social_discount( $discount_id ) ) {
					// find all products using this discount ID. If this is true, then this particular discount is a social discount, not a standard one as the array holds IDs of downloads
					$social_discount_products = $this->get_social_discount_products( $discount_id );

			//		var_dump( $download_id );

					// this discount is a social discount
					if ( $social_discount_products ) {

						continue; // i di this

						// loop through each download ID. We're going to match it against the downloads in the cart
						foreach ( $social_discount_products as $id ) {
							
						//	var_dump( $download_id );
						//	var_dump( $id );

							if ( $download_id == $id ) {
							//	$discount_amount = $this->edd_get_cart_item_discount_amount2( $download );
							//	return $this->edd_get_cart_item_discount_amount2( $download );
								return 10;
							//	continue;
							//	break 2;
							}

							//if ( $this->is_social_discount( $discount_id ) ) {
								
							//}

							// this discount applies to the item in the cart
							// if ( ! empty( $social_products ) ) {
							// 	return $this->edd_get_cart_item_discount_amount2( $download );
							// }
							// // this discount does not apply to the item in cart, return
							// else {
							// 	return $discount_amount;
							// }
						
						}
					}
			
				else {
				//	continue;
					return $discount_amount;

				}
			}	
		}

		//return $discount_amount;
		
	}






	/**
	 * New discount function that runs when an item in the cart has the currently applied discount recorded against it
	 * @todo  doesn't need the foreach, see above
	 *
	 * @param array $download the download array
	 */
	public function edd_get_cart_item_discount_amount2( $download = array() ) {
		
		// needs to happen ONLY for the social discount
		

		$amount           = 0;
		$price            = edd_get_cart_item_price( $download['id'], $download['options'], edd_prices_include_tax() );
		$discounted_price = $price;

		// Retrieve all discounts applied to the cart
		$discounts = edd_get_cart_discounts();

		if ( $discounts ) {

			foreach ( $discounts as $discount ) {

				$code_id           = edd_get_discount_id_by_code( $discount );
				$social_products   = $this->get_social_discount_products( $code_id );

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
							if ( $download_id == $download['id'] ) {
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
	 * Get products assigned to a specific social discount code
	 *
	 * @since 2.1
	 * @param int $code_id Discount ID
	 * @return array $shared_products IDs of the products assigned to the code
	 */
	public function get_social_discount_products( $code_id = null ) {
		// get array
		$products = get_post_meta( $code_id, '_edd_discount_social_discount_products', true );

		// return blank array if no products
		// if ( empty( $products ) || ! is_array( $products ) ) {
		// 	$products = array();
		// }

		if ( $products ) {
			return (array) apply_filters( 'edd_sd_get_social_discount_products', $products, $code_id );
		}

		// else return blank array
		return array();
	}

	/**
	 * Check to see if a specific discount code is a social discount
	 *
	 * @since 2.1
	 * @param int $code_id Discount ID
	 * @return bool 
	 */
	public function is_social_discount( $code_id = null ) {
		// get array
		$products = get_post_meta( $code_id, '_edd_discount_social_discount_products', true );

		// if array exists, it's a social discount
		if ( $products ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if discount code is valid
	 * Prevents the discount from working at checkout if the discount is a sharing discount and no products were shared
	 * Makes sure the discount ONLY APPLIES to social downloads that were shared, and cannot be used on other downloads within the cart.
	 *
	 * @since  2.1
	 * @todo  only apply discount to shared product
	 * @todo  check that the download ID is in EDD()->session->get( 'edd_shared_ids' ), meaning the download has actually been shared, else return
	 */
	public function is_discount_valid( $return, $discount_id, $code, $user ) {

		// return starts off false




		return $return;
	}

	function edd_is_discount_valid2( $code = '', $user = '' ) {


		$return      = false;
		$discount_id = edd_get_discount_id_by_code( $code );
		$user        = trim( $user );

		if( edd_get_cart_contents() ) {

			if ( $discount_id ) {
				if (
					edd_is_discount_active( $discount_id ) &&
					edd_is_discount_started( $discount_id ) &&
					!edd_is_discount_maxed_out( $discount_id ) &&
					!edd_is_discount_used( $code, $user, $discount_id ) &&
					edd_discount_is_min_met( $discount_id ) &&
					edd_discount_product_reqs_met( $discount_id )
				) {
					$return = true;
				}
			} else {
				edd_set_error( 'edd-discount-error', __( 'This discount is invalid.', 'edd' ) );
			}

		}

		return apply_filters( 'edd_is_discount_valid', $return, $discount_id, $code, $user );
	}


	/**
	 * Append the social discounts to the end of EDD's standard discounts
	 * Shows each download title, with the discount that is applied to it
	 *
	 * @since  2.1
	 * @return mixed|void
	 */
	public function show_each_social_discount( $html, $discounts, $rate, $remove_url ) {

	//	$discounts = $this->get_download_details();

		

		// we already have discounts, so loop through them
		foreach ( $discounts as $discount ) {
		//	var_dump( $discount );

			// get discount ID from it's code
			$discount_id  = edd_get_discount_id_by_code( $discount );
			
			// see if this discount is a social discount
			$is_social_discount = $this->is_social_discount( $discount_id );

			//var_dump( $is_social_discount );



			if ( $is_social_discount ) {

				ob_start();

				echo 'There should be one';

				$html = ob_get_clean();
			}


			// $download_name  = isset( $discount['name'] ) ? $discount['name'] : '';
			// $discount_name 	= isset( $discount['discount_name'] ) ? $discount['discount_name'] : '';
			// $rate           = isset( $discount['rate'] ) ? $discount['rate'] : '';
			// $discount_id    = isset( $discount['discount_id'] ) ? $discount['discount_id'] : '';

			// $remove_url = add_query_arg(
			// 	array(
			// 		'edd_action'    => 'remove_cart_discount',
			// 		'discount_id'   => $discount_id,
			// 		'discount_code' => $discount_name
			// 	),
			// 	edd_get_checkout_uri()
			// );



			?>
			


			<?php 
		}
		
		

	//	var_dump( $this->show_download_rates() );

		// append the social discounts to the end of EDD's standard discounts
		return $html;	
	}
	
	function edd_get_cart_discounts_html2( $discounts = false ) {
		if ( ! $discounts ) {
			$discounts = edd_get_cart_discounts();
		}

		if ( ! $discounts ) {
			return;
		}

		$html = '';

		foreach ( $discounts as $discount ) {
			$discount_id  = edd_get_discount_id_by_code( $discount );
			$rate         = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );

			$remove_url   = add_query_arg(
				array(
					'edd_action'    => 'remove_cart_discount',
					'discount_id'   => $discount_id,
					'discount_code' => $discount
				),
				edd_get_checkout_uri()
			);

			$html .= "<span class=\"edd_discount\">\n";
				$html .= "<span class=\"edd_discount_rate\">$discount&nbsp;&ndash;&nbsp;$rate</span>\n";
				$html .= "<a href=\"$remove_url\" data-code=\"$discount\" class=\"edd_discount_remove\"></a>\n";
			$html .= "</span>\n";
		}

		return apply_filters( 'edd_get_cart_discounts_html', $html, $discounts, $rate, $remove_url );
	}



	/**
	 * Get an array of each download's details that exist in the cart
	 *
	 * Contains:
	 * name
	 * download_id
	 * discount_name
	 * discount_id
	 * rate
	 * 
	 * @param  array  $item The download array, containing download's ID, options, quantity
	 * @since  2.1
	 * @return array $details an array containing the above
	 */
	
	public function get_download_details() {

		// Retrieve an array of discount codes applied to the cart
		$discounts = edd_get_cart_discounts();

		// get array of current items in cart
		$cart_items = edd_get_cart_contents();

		// pluck the download ids from the cart
		$cart_download_ids  = $cart_items ? wp_list_pluck( $cart_items, 'id' ) : null;

		// echo 'this: ';
		// var_dump( $cart_download_ids );

		if ( $discounts ) {
			$details = array();

			// echo 'this: ';
			// var_dump( $discounts );

			// loop through each discount
			foreach ( $discounts as $discount_code ) {
				
				// get excluded downloads
				$excluded_downloads = edd_get_discount_excluded_products( $discount_code );

				// echo 'excluded:';
				// var_dump( $excluded_downloads );

				// get the discount ID from the discount code
				$discount_id = edd_get_discount_id_by_code( $discount_code );

			
			//	var_dump( $discount_id );

				// find all products using this discount ID. If this is true, then this particular discount is a social discount, not a standard one as the array holds IDs of downloads
				$social_discount_products = $this->get_social_discount_products( $discount_id );

				// this discount is a social discount
				if ( $social_discount_products ) {	
					// loop through each download ID. We're going to match it against the downloads in the cart
					foreach ( $social_discount_products as $download_id ) {
						
						// download exists in cart download IDs
						// download cannot be in excluded downloads array
						if ( in_array( $download_id, $cart_download_ids ) && ! in_array( $download_id, $excluded_downloads ) ) {
						

							$amount = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );	

							// build $details array			
							$details['download_name']   = get_the_title( $download_id );
							$details['download_id']     = $download_id;
							$details['discount_code']   = $discount_code; // ala discount name
							$details['discount_id']     = $discount_id;
							$details['discount_amount'] = $amount;
						}
					}
				}
			}

			return (array) $details;
		}

		
	}


	
	








	





} // end class