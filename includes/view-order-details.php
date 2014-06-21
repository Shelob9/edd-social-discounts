<?php
/**
 * Admin
 */

/**
 * Downloads that were shared before this order was completed
 * 
 * @param  int $payment_id	the payment ID
 * @return void
 * @since  2.0
 */
function edd_social_discounts_view_order_details( $payment_id ) {
	// return if nothing was shared
	if ( ! get_post_meta( $payment_id, '_edd_social_discount', true ) )
		return;
?>
<div id="edd-purchased-files" class="postbox">
	<h3 class="hndle"><?php printf( __( '%s/Posts/Pages that were shared before payment', 'edd-social-discounts' ), edd_get_label_plural() ); ?></h3>
	<div class="inside">
		<table class="wp-list-table widefat fixed" cellspacing="0">
			<tbody id="the-list">
			<?php
				$downloads = get_post_meta( $payment_id, '_edd_social_discount_shared_ids', true );

				if ( $downloads ) :
					$i = 0;
					foreach ( $downloads as $download_id ) :
					?>
						<tr class="<?php if ( $i % 2 == 0 ) { echo 'alternate'; } ?>">
							<td class="name column-name">
								<?php echo '<a href="' . admin_url( 'post.php?post=' . $download_id . '&action=edit' ) . '">' . get_the_title( $download_id ) . '</a>'; ?>
							</td>
						</tr>
						<?php
						$i++;
					endforeach;
				endif;
			?>
			</tbody>
		</table>
	</div>
</div>
<?php }
add_action( 'edd_view_order_details_main_after', 'edd_social_discounts_view_order_details' );