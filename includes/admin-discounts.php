<?php
/**
 * Admin
 */


/**
 * Adds an option to the edit discounts page to select downloads for use with this particular discount
 * @return [type] [description]
 */
function edd_social_discounts_edit_discount_form( $discount_id, $discount = '' ) {
	$selected_products = edd_social_discounts()->discounts->get_social_discount_products( $discount_id );

	var_dump( $selected_products );

	$already_shared = edd_social_discounts_get_shared_product_ids();

	?>

	<table class="form-table">
		<tbody>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="social-discount-products"><?php printf( __( 'Social Discount %s', 'edd' ), edd_get_label_plural() ); ?></label>
				</th>
				<td>

				<?php
					$products = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1, 'nopaging' => true, 'orderby' => 'title', 'order' => 'ASC' ) );
				?>
				<select name="social_discount_products[]" id="social-discount-products" data-placeholder="<?php printf( __( 'Choose one or more %s', 'edd-social-discounts' ), edd_get_label_plural() ); ?>" multiple class="edd-select edd-select-chosen">
					<?php if ( $products ) : 
					
					foreach ( $products as $product ) { 
						$selected = in_array( $product->ID, $selected_products ) ? $product->ID : '';
						$disabled = in_array( $product->ID, $already_shared ) && ! $selected ? ' disabled' : '';
						$value    = ! $disabled ? 'value="' . absint( $product->ID ) . '"' : '';
					?>
					<option<?php echo $disabled; ?> <?php echo $value; ?> <?php echo selected( $selected, $product->ID, false ); ?>><?php echo esc_html( get_the_title( $product->ID ) ); ?></option>

					<?php } ?>

					<?php else : ?>

						<option><?php printf( __( 'No %s found', 'edd-social-discounts' ), edd_get_label_plural() ); ?></option>

					<?php endif; ?>

				</select>

				</td>
			</tr>
		</tbody>
	</table>

<?php	
}
add_action( 'edd_edit_discount_form_bottom', 'edd_social_discounts_edit_discount_form', 10, 2 );


/**
 * Adds an option to the add discount page to select downloads for use with this particular discount
 * @return [type] [description]
 */
function edd_social_discounts_add_to_discount_form() {
	$already_shared = edd_social_discounts_get_shared_product_ids();
	//$selected_products = edd_social_discounts()->discounts->get_social_discount_products( $discount_id );
?>

	<table class="form-table">
		<tbody>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="social-discount-products"><?php printf( __( 'Social Discount %s', 'edd' ), edd_get_label_plural() ); ?></label>
				</th>
				<td>

				<?php
					$products = get_posts( array( 'post_type' => 'download', 'nopaging' => true, 'orderby' => 'title', 'order' => 'ASC' ) );
				?>
				<select name="social_discount_products[]" id="social-discount-products" data-placeholder="<?php printf( __( 'Choose one or more %s', 'edd-social-discounts' ), edd_get_label_plural() ); ?>" multiple class="edd-select edd-select-chosen">
					<?php if ( $products ) : 
					
					
					foreach ( $products as $product ) { 
					//	$selected = in_array( $product->ID, $selected_products ) ? $product->ID : '';
						$disabled = in_array( $product->ID, $already_shared ) ? ' disabled' : '';
					?>
					<option<?php echo $disabled; ?> value="<?php echo absint( $product->ID ); ?>"><?php echo esc_html( get_the_title( $product->ID ) ); ?></option>

					<?php } ?>

					<?php else : ?>

						<option><?php printf( __( 'No %s found', 'edd-social-discounts' ), edd_get_label_plural() ); ?></option>

					<?php endif; ?>

				</select>

					<p class="description"><?php printf( __( '%s that this discount code will be applied to when shared.', 'edd' ), edd_get_label_plural() ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>

<?php	
}
add_action( 'edd_add_discount_form_bottom', 'edd_social_discounts_add_to_discount_form' );



/**
 * Update discount
 */
function edd_social_discounts_update_discount( $details, $discount_id ) {

	$details['social_discount_products'] = isset( $details['social-discount-products'] )? $details['social-discount-products'] : array();

	//var_dump( $details ); wp_die();
	
	return $details;
}
//add_filter( 'edd_update_discount', 'edd_social_discounts_update_discount', 10, 2 );


/**
 * Add download meta to discount
 */
function edd_social_discounts_add_or_update_discount( $details, $discount_id ) {

	// ids from database
	$ids_in_db = get_post_meta( $discount_id, '_edd_discount_social_discount_products', true );

	// ids in select field. If nothing is entered it will be a blank array
	$ids = isset( $details['social_discount_products'] ) ? $details['social_discount_products'] : array();
	
	// work out which ids need to be deleted and store to array
	$to_remove = array();

	if ( $ids_in_db ) {
		foreach( $ids_in_db as $value ) {
		     if ( ! in_array( $value, $ids ) ) {
		        $to_remove[] = $value;
		     }
		}
	}
	
	// delete post meta store against each download
	if ( $to_remove ) {
		foreach ( $to_remove as $id ) {
			delete_post_meta( $id, 'edd_social_discount', $discount_id );
		}
	}
	
	// update post meta
	foreach ( $ids as $id ) {
		update_post_meta( $id, 'edd_social_discount', $discount_id );
	}

	// update the array of downloads stored in the db with our new ids
	update_post_meta( $discount_id, '_edd_discount_social_discount_products', $ids );

}
add_action( 'edd_post_insert_discount', 'edd_social_discounts_add_or_update_discount', 10, 2 );
add_action( 'edd_post_update_discount', 'edd_social_discounts_add_or_update_discount', 10, 2 );