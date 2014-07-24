<?php
/**
 * Template functions
 */


/**
 * Display the share button
 * Automatically adds the sharing buttons to product pages. Can be overridden with shortcode on per product basis
 *
 * @since 2.0
*/
function edd_social_discounts_display_share_buttons() {
	$display = edd_get_option( 'edd_sd_display_services', 'after' );

	// don't automatically output the sharing services
	if ( 'none' != $display ) {
		// load sharing box by default after download content 
		if ( 'after' == $display ) {
			add_action( 'edd_after_download_content', 'edd_social_discounts_load_share_box' );
		}
		// load before content
		elseif ( 'before' == $display ) {
			add_action( 'edd_before_download_content', 'edd_social_discounts_load_share_box' );
		}
	}
}
add_action( 'template_redirect', 'edd_social_discounts_display_share_buttons' );

/**
 * Load sharebox
 *
 * @since 2.0
*/
function edd_social_discounts_load_share_box() {
	// if shortcode is detected on page already, then return
	if ( edd_social_discounts_has_shortcode( 'edd_social_discount' ) )
		return;

	echo edd_social_discounts_share_box();
}

/**
 * Main share box that is displayed on the page
 * 
 * @param  string $id 		post/page/download ID
 * @param  string $title 	custom title
 * @param  string $message 	custom message
 * @param  string $tweet 	custom tweet message
 * @return void
 * @since  2.0
 * @todo  this needs to accept a discount parameter now that downloads can have per-download discounts
 */
function edd_social_discounts_share_box( $id = '', $title = '', $message = '', $tweet = '', $locked = '' ) {
	global $edd_options;

	// use ID passed into function, otherwise get the post ID
	$id = $id ? $id : get_the_ID();
//	$id = get_the_ID();

//	var_dump( $id );


	//	$test = the_title_attribute( 'echo=0' );
	//	$test = get_the_title( $id );

	//	var_dump( $test );

//	var_dump( $locked );

	//$edd_social_discounts = edd_social_discounts();

	// return if our share box has been turned off
	// if ( ! $edd_social_discounts::$share_box_enabled )
	// 	return;

	// load required scripts if template tag or shortcode has been used
	//$edd_social_discounts::$add_script = true;


	$per_download_title = get_post_meta( $id, 'edd_social_discount_title', true );
	$per_download_message = get_post_meta( $id, 'edd_social_discount_message', true );

	// get custom title, else default title
	// show the success message if product has been shared, else default title
	if ( EDD()->session->get( 'edd_shared_ids' ) && in_array( $id, EDD()->session->get( 'edd_shared_ids' ) ) ) {

		if (  edd_social_discounts_is_locked( $id ) ) {
			$share_title = edd_social_discounts_share_to_unlock_success_title( $id );
			$share_message = edd_social_discounts_share_to_unlock_success_message( $id );
		} else {
			$share_title = edd_social_discounts_success_title( $id );
			$share_message = edd_social_discounts_success_message( $id );
		}

	}
	else {
		// custom title passed into function 
		if ( $title ) {
			$share_title = esc_attr( $title );
		}
		// locked title
		elseif ( edd_social_discounts_is_locked( $id ) ) {
			$share_title = isset( $edd_options['edd_sd_share_to_unlock_title'] ) && ! empty( $edd_options['edd_sd_share_to_unlock_title'] ) ? esc_attr( $edd_options['edd_sd_share_to_unlock_title'] ) : '';
		}
		elseif ( $per_download_title ) {
			$share_title = esc_attr( $per_download_title );
		}
		// title from plugin settings
		else {
			$share_title = isset( $edd_options['edd_sd_title'] ) && ! empty( $edd_options['edd_sd_title'] ) ? esc_attr( $edd_options['edd_sd_title'] ) : '';
		}

		// custom message
		if ( $message ) {
			$share_message = esc_attr( $message );
		}
		elseif ( edd_social_discounts_is_locked( $id ) ) {
			$share_message = isset( $edd_options['edd_sd_share_to_unlock_message'] ) && ! empty( $edd_options['edd_sd_share_to_unlock_message'] ) ? esc_attr( $edd_options['edd_sd_share_to_unlock_message'] ) : '';
		}
		// per download message
		elseif ( $per_download_message ) {
			$share_message = esc_attr( $per_download_message );
		}
		// message from plugin settings
		else {
			$share_message = isset( $edd_options['edd_sd_message'] ) && ! empty( $edd_options['edd_sd_message'] ) ? esc_attr( $edd_options['edd_sd_message'] ) : '';
		}

	}
	
	// custom tweet message
	if ( $tweet ) {
		$twitter_default_text = esc_attr( $tweet );
	}
	// else if we're on a single download page
	elseif ( is_singular( 'download' ) ) {
		$twitter_default_text = the_title_attribute( 'echo=0' );
	//	$twitter_default_text = get_the_title( $id );
	}
	// default twitter message that is shown when shared. 
	// if an ID was passed
	elseif ( $id ) {
		$twitter_default_text = get_the_title( $id );
	}
	
	else {
		$twitter_default_text = '';
	}

	// URL to share
	$share_url = apply_filters( 'edd_social_discounts_share_url', post_permalink( $id ) );

	// get services
	$services = edd_get_option( 'edd_sd_services', '' );

	// return if there are no services
	if ( empty( $services ) )
		return;

	ob_start();


?>


	<div class="<?php echo apply_filters( 'edd_social_discounts_classes', 'edd-sd-share' ); ?>" data-id="<?php echo $id; ?>">

		<?php 
			// show the title and message, but if the product has been shared, show the success message
			echo apply_filters( 'edd_social_discounts_share_title', '<h3 class="edd-sd-title">' . $share_title . '</h3>' );
			echo apply_filters( 'edd_social_discounts_share_message', '<p class="edd-sd-message">' . $share_message . '</p>' );
		?>

		<?php do_action( 'edd_social_discounts_before_share_box' ); ?>

		<?php if ( edd_social_discounts_is_enabled( 'twitter' ) ) : 
			$twitter_username = isset( $edd_options['edd_sd_twitter_username'] ) ? esc_attr( $edd_options['edd_sd_twitter_username'] ) : '';
			// defaults to en_US if left blank
			$locale = isset( $edd_options['edd_sd_twitter_locale'] ) && ! empty( $edd_options['edd_sd_twitter_locale'] ) ? $edd_options['edd_sd_twitter_locale'] : 'en';
			$twitter_count_box = edd_get_option( 'edd_sd_twitter_count_box', 'vertical' );
			$twitter_button_size = edd_get_option( 'edd_sd_twitter_button_size', 'medium' );
		?>
		<div class="edd-sd-service twitter">
			<a href="https://twitter.com/share" data-lang="<?php echo $locale; ?>" class="twitter-share-button" data-id="<?php echo 'hello';?>" data-count="<?php echo $twitter_count_box; ?>" data-size="<?php echo $twitter_button_size; ?>" data-counturl="<?php echo post_permalink( $id ); ?>" data-url="<?php echo $share_url; ?>" data-text="<?php echo $twitter_default_text; ?>" data-via="<?php echo $twitter_username; ?>" data-related=""><?php _e( 'Share', 'edd-social-discounts' ); ?></a>
		</div>
		<?php endif; ?>

		<?php if ( edd_social_discounts_is_enabled( 'facebook' ) ) :
			// filter for enabling share button although won't trigger discount
			$data_share = apply_filters( 'edd_social_discounts_facebook_share_button', 'false' );
			$facebook_button_layout = edd_get_option( 'edd_sd_facebook_button_layout', 'box_count' );
		?>
		
		<div class="edd-sd-service facebook">
			<div class="fb-like" data-href="<?php echo $share_url; ?>" data-send="true" data-action="like" data-layout="<?php echo $facebook_button_layout; ?>" data-share="<?php echo $data_share; ?>" data-width="" data-show-faces="false"></div>
		</div>
		<?php endif; ?>

		<?php if ( edd_social_discounts_is_enabled( 'googleplus' ) ) : 
			$googleplus_button_size = isset( $edd_options['edd_sd_googleplus_button_size'] ) ? $edd_options['edd_sd_googleplus_button_size'] : 'tall';
			$google_button_annotation = edd_get_option( 'edd_sd_googleplus_button_annotation', 'bubble' );
			$google_button_recommendations = edd_get_option( 'edd_sd_googleplus_button_recommendations', 'false' );
		?>
		<div class="edd-sd-service googleplus">
			<div class="g-plusone" data-recommendations="<?php echo $google_button_recommendations; ?>" data-annotation="<?php echo $google_button_annotation;?>" data-callback="plusOned" data-size="<?php echo $googleplus_button_size; ?>" data-href="<?php echo $share_url; ?>"></div>
		</div>
		<?php endif; ?>

		<?php if ( edd_social_discounts_is_enabled( 'linkedin' ) ) :
			$locale = isset( $edd_options['edd_sd_linkedin_locale'] ) && ! empty( $edd_options['edd_sd_linkedin_locale'] ) ? $edd_options['edd_sd_linkedin_locale'] : 'en_US';
			$linkedin_counter = edd_get_option( 'edd_sd_linkedin_counter', 'top' );
		?>
		<div class="edd-sd-service linkedin">
		<script src="http://platform.linkedin.com/in.js" type="text/javascript">lang: <?php echo $locale; ?></script>
		<script type="IN/Share" data-counter="<?php echo $linkedin_counter; ?>" data-onSuccess="share" data-url="<?php echo $share_url; ?>"></script>
		</div>
		<?php endif; ?>

		<?php 
		/**
		 * When the download is unlocked, the purchase button is loaded onto this hook
		 */
		do_action( 'edd_social_discounts_after_share_box', $id ); 
		?>

		<?php if ( 'yes' == $locked ) { ?>
			<div class="edd-sd-locked"></div>
		<?php } ?>

	</div>
	<input type="hidden" name="edd_sd_download_id" value="<?php echo $id; ?>" />
<?php 
	$share_box = ob_get_clean();
	return apply_filters( 'edd_social_discounts_share_box', $share_box );
}

/**
 * Collect IDs of download's shared before purchasing
 *
 * @return 	void 
 * @since 	2.0 
*/
function edd_social_discounts_shared_downloads() {
	$ids = array();

	// get shared IDs from session
	$store_this = EDD()->session->get( 'edd_shared_id' );

	// if there's an ID
	if ( $store_this ) {

		$ids[] = $store_this;

		$current = EDD()->session->get( 'edd_shared_ids' );

		// no session, create one
		if ( false === $current ) {
			EDD()->session->set( 'edd_shared_ids', $ids );
		}
		// else update existing 
		else {
			// first we get the existing IDs
			$existing = EDD()->session->get( 'edd_shared_ids' );
			// only store the ID if it's not already in the array
			if ( ! in_array( $store_this, $existing) ) {
				$existing[] = $store_this;
				//set with our new IDs
				EDD()->session->set( 'edd_shared_ids', $existing );
			}
		}	
	}

}
add_action( 'template_redirect', 'edd_social_discounts_shared_downloads' );

/**
 * Store metakeys with purchase
 * 
 * @param  int $payment_id
 * @return void
 * @since  2.0
 */
function edd_social_discounts_update_post_meta( $payment_id ) {
	// store metakey if a social discount was used
	if ( EDD()->session->get( 'edd_shared' ) ) {
		update_post_meta( $payment_id, '_edd_social_discount', true );
	}

	// get IDs of all downloads that were shared
	$download_ids = EDD()->session->get( 'edd_shared_ids' );

	// store array of download IDs
	if ( $download_ids ) {
		update_post_meta( $payment_id, '_edd_social_discount_shared_ids', $download_ids );
	}
	
	// clear session variables
	EDD()->session->set( 'edd_shared_id', NULL );
	EDD()->session->set( 'edd_shared_ids', NULL );
}
add_filter( 'edd_complete_purchase', 'edd_social_discounts_update_post_meta' );


/**
 * Override purchase button if download has been locked
 *
 * @todo link button to sharing div 
 * @param  [type] $purchase_form [description]
 * @param  [type] $args          [description]
 * @return [type]                [description]
 */
function edd_social_discounts_purchase_download_form( $purchase_form, $args ) {
 //  global $post;

	//var_dump( $args );

    // if ajax is fired, return the normal purchase form
	if ( isset( $args['ref'] ) && $args['ref'] == 'ajax' ) {
		return $purchase_form;
	}

 //   $post_id = isset( $post->ID ) ? $post->ID : '';
    
    $download_id 		= $args['download_id'];
    $variable_pricing 	= edd_has_variable_prices( $download_id );
	$shared_ids 		= EDD()->session->get( 'edd_shared_ids' ) ? EDD()->session->get( 'edd_shared_ids' ) : array();

//	$form_id = ! empty( $args['form_id'] ) ? $args['form_id'] : 'edd_purchase_' . $download_id;

//	var_dump( $download_id );

	// return if we're not dealing with a locked download
	if ( 
		! edd_social_discounts_is_locked( $download_id ) || 
		in_array( $download_id, $shared_ids ) || 
		( edd_item_in_cart( $download_id ) && ! $variable_pricing ) ) 
	{
		return $purchase_form;
	}

    ob_start();

    $label = __( 'Share to unlock', 'edd-social-discounts' );

    ?>

    <div class="edd-sd-locked" data-id="<?php echo $download_id; ?>"></div>


<?php 
    return apply_filters( 'edd_social_discounts_purchase_download_form', ob_get_clean() );
}
add_filter( 'edd_purchase_download_form', 'edd_social_discounts_purchase_download_form', 10, 2 );



/**
 * If download has been unlocked, and the page is refreshed, show the purchase button
 * 
 * @since 2.1
 * @param $id ID of download/post/page
 */
function edd_social_discounts_already_unlocked( $id ) {

	// only locked downloads need the button to remain
	if ( ! edd_social_discounts_is_locked( $id ) )
		return;

	// has been shared
	if ( EDD()->session->get( 'edd_shared_ids' ) && in_array( $id, EDD()->session->get( 'edd_shared_ids' ) ) ) {
		// attach purchase button

		echo edd_append_purchase_link( $id );
	}

}
add_action( 'edd_social_discounts_after_share_box', 'edd_social_discounts_already_unlocked' );



// function edd_append_purchase_link2( $download_id ) {
// 	if ( ! get_post_meta( $download_id, '_edd_hide_purchase_link', true ) ) {
// 		echo edd_get_purchase_link( array( 'download_id' => $download_id ) );
// 	}
// }









/**
 * Prevent user from adding the download to the cart from URL
 */

