<?php

/**
 * Register all the meta boxes for the Download custom post type
 *
 * @since 1.0
 * @return void
 */
function edd_social_discounts_meta_box() {

	$post_types = apply_filters( 'edd_sd_metabox_post_types' , array( 'download' ) );

	foreach ( $post_types as $post_type ) {

		/** Social Discounts **/
		add_meta_box( 'edd_sd_social_discounts', sprintf( __( 'Social Discounts', 'edd-social-discounts' ), edd_get_label_singular(), edd_get_label_plural() ),  'edd_social_discounts_render_meta_box', $post_type, 'side', 'default' );
	}
}
add_action( 'add_meta_boxes', 'edd_social_discounts_meta_box' );




/**
 * Render the metabox
 * @return [type] [description]
 */
function edd_social_discounts_render_meta_box() {
	$discounts = edd_get_discounts( array( 'posts_per_page' => -1, 'post_status' => 'active' ) );
	?>
	<label for="download-social-discount">
		<p><strong><?php printf( __( 'Select the discount that will be applied when this %s is shared', 'edd-social-discounts' ), edd_get_label_singular( true ) ); ?></strong></p>
	</label>

	<p>
	<select name="edd_social_discount" id="download-social-discount" data-placeholder="<?php printf( __( 'Select a discount', 'edd-social-discounts' ), edd_get_label_plural() ); ?>" class="edd-select">
		<option><?php printf( __( 'Select a discount', 'edd-social-discounts' ) ); ?></option>
		<?php if ( $discounts ) : 
		
		foreach ( $discounts as $discount ) { 
			$selected = get_post_meta( get_the_ID(), 'edd_social_discount', true );

		?>
		<option value="<?php echo absint( $discount->ID ); ?>"<?php echo selected( $selected, $discount->ID, false ); ?>><?php echo $discount->post_title; ?></option>

		<?php } ?>

		<?php endif; ?>

	</select>
	</p>

	<?php
	/**
	 * Per-download sharing title
	 */
	$title = get_post_meta( get_the_ID(), 'edd_social_discount_title', true );

	?>
	
	<p>
		<strong><label for="edd-social-discount-title"><?php _e( 'Social Discount Title', 'edd-social-discounts' ); ?></label></strong>
	</p>
	
	<p>
		<input type="text" id="edd-social-discount-title" name="edd_social_discount_title" class="large-text" value="<?php echo esc_attr( $title ); ?>" />
	</p>

	<?php
	/**
	 * Per-download sharing message
	 */
	$message = get_post_meta( get_the_ID(), 'edd_social_discount_message', true );

	?>
	
	<p><strong><label for="edd-social-discount-message"><?php _e( 'Social Discount Message', 'edd-social-discounts' ); ?></label></strong></p>
	
	<p>
		<textarea id="edd-social-discount-message" name="edd_social_discount_message" class="large-text" cols="40" rows="4"><?php echo esc_attr( $message ); ?></textarea>
	</p>
	
	<?php
		$locked = get_post_meta( get_the_ID(), 'edd_social_discounts_locked', true );
	?>
	<p>
		<input id="lock-download" type="checkbox" value="1" name="edd_social_discounts_locked" <?php checked( $locked, true ); ?>/> <label for="lock-download"><?php printf( __( 'Enable share to unlock', 'edd-social-discounts' ), edd_get_label_singular( true ) ); ?></label>
	</p>
	<p class="description"><?php printf( __( 'Useful for free %s, where you want the customer to share first before being able to add to the cart and download.', 'edd-social-discounts' ), edd_get_label_plural( true ) ); ?></p>
<?php }


/**
 * Find a download_id within discounts
 * 
 * @param  [type] $download_id [description]
 * @return [type]              [description]
 */
function edd_social_discounts_find_download_in_discount( $download_id ) {

	$discounts = edd_get_discounts( array( 'posts_per_page' => -1, 'post_status' => 'active', 'meta_key' => '_edd_discount_social_discount_products' ) );

	if ( $discounts ) {
		foreach ( $discounts as $discount ) {
			// get discount ID
			$discount_id = $discount->ID;

			// get each discount array
			$discount_array = get_post_meta( $discount_id, '_edd_discount_social_discount_products', true );

			if ( in_array( $download_id, $discount_array ) ) {
				// return the ID of the discount it was found in
				return $discount_id;
			}
		}
	}
	
	return null;
}


/**
 * Saves the discount field
 */
function edd_social_discounts_edd_save_download( $post_id, $post ) {
	
	$social_discount 			= 'edd_social_discount';
	$social_discount_products 	= '_edd_discount_social_discount_products';





	/**************************************************************************************/
	/* save the edd_social_discount_title meta_key
	/**************************************************************************************/

	$social_discount_title = $_POST['edd_social_discount_title'] ? sanitize_text_field( $_POST['edd_social_discount_title'] ) : '';

	// only saves discount IDs, not the default value
	if ( $social_discount_title ) {
		update_post_meta( $post_id, 'edd_social_discount_title', $social_discount_title );
	} else {
		delete_post_meta( $post_id, 'edd_social_discount_title' );
	}


	/**************************************************************************************/
	/* save the edd_social_discount_message meta_key
	/**************************************************************************************/

	$social_discount_message = $_POST['edd_social_discount_message'] ? esc_textarea( $_POST['edd_social_discount_message'] ) : '';

	// only saves discount IDs, not the default value
	if ( $social_discount_message ) {
		update_post_meta( $post_id, 'edd_social_discount_message', $social_discount_message );
	} else {
		delete_post_meta( $post_id, 'edd_social_discount_message' );
	}

	/**************************************************************************************/
	/* save the edd_social_discounts_locked checkbox
	/**************************************************************************************/

	$social_discount_locked = isset( $_POST[ 'edd_social_discounts_locked' ] );

	// only saves discount IDs, not the default value
	if ( $social_discount_locked ) {
		update_post_meta( $post_id, 'edd_social_discounts_locked', true );
	} else {
		delete_post_meta( $post_id, 'edd_social_discounts_locked' );
	}

	/**************************************************************************************/
	/* save the edd_social_discount meta_key
	/**************************************************************************************/

	$value = $_POST[ $social_discount ];

	// only saves discount IDs, not the default value
	if ( isset( $_POST[ $social_discount ] ) && is_numeric( $_POST[ $social_discount ] ) ) {
		update_post_meta( $post_id, $social_discount, $value );
	} else {
		delete_post_meta( $post_id, $social_discount );
	}


	// get discount code assigned to this download
	$discount = get_post_meta( $post_id, $social_discount, true );

	// get discount array
	$discount_array = get_post_meta( $discount, $social_discount_products, true );
	$discount_array = $discount_array ? $discount_array : array();

	/**************************************************************************************/
	/* search through the discount sharing arrays and unset it from the old array
	/* downloads can only be assigned to one discount at a time
	/**************************************************************************************/

	// get the old discount_id the download is added to
	$old_discount_id = edd_social_discounts_find_download_in_discount( $post_id );

	// get old discount array
	$old_discount_array = get_post_meta( $old_discount_id, $social_discount_products, true );

	// find post ID in discount and remove
	if ( $old_discount_id ) {
		if ( ( $key = array_search( $post_id, $old_discount_array ) ) !== false ) {
		    unset( $old_discount_array[$key] );
		}
	}
	
	// update array with new values (minus the ID)
	update_post_meta( $old_discount_id, $social_discount_products, $old_discount_array );

	/**************************************************************************************/
	/* add the download ID to the discount array only if it's not there already
	/**************************************************************************************/

	if ( ! in_array( $post_id, $discount_array ) ) {
		// add download ID to the discount array
		$discount_array[] = $post_id;

		// update the discount array
		update_post_meta( $discount, '_edd_discount_social_discount_products', $discount_array );
	}
	
}
add_action( 'edd_save_download', 'edd_social_discounts_edd_save_download', 10, 2 );