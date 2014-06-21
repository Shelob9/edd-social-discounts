<?php
/**
 * Admin settings
 */

/**
 * Settings
 *
 * @since 2.0
*/
function edd_social_admin_settings( $settings ) {
	
	// make sure we only show active discounts
	$args = array(
		'post_status' => 'active'
	);

	$discounts = edd_get_discounts( $args );

	if ( $discounts ) {
		$discount_options = array( 0 => __( 'Select discount', 'edd-social-discounts' ) );

		foreach ( $discounts as $discount ) {
			$discount_options[ $discount->ID ] = $discount->post_title;
		}
	}
	else {
		$discount_options = array( 0 => __( 'No discounts found', 'edd-social-discounts' ) );
	}

	$plugin_settings = array(
		array(
			'id' => 'edd_sd_header',
			'name' => '<strong>' . __( 'Social Discounts', 'edd-social-discounts' ) . '</strong>',
			'type' => 'header'
		),
		array(
			'id' => 'edd_sd_services',
			'name' => __( 'Social Services To Enable', 'edd-social-discounts' ),
			'desc' => __( '', 'edd-social-discounts' ),
			'type' => 'multicheck',
			'options' => apply_filters( 'edd_social_discounts_settings_services', array(
					'twitter' =>  __( 'Twitter', 'edd-social-discounts' ),
					'facebook' =>  __( 'Facebook', 'edd-social-discounts' ),
					'googleplus' =>  __( 'Google+', 'edd-social-discounts' ),
					'linkedin' =>  __( 'LinkedIn', 'edd-social-discounts' ),
				)
			)
		),
		array(
			'id' => 'edd_sd_display_services',
			'name' => __( 'Display Sharing Services', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Sharing services can be positioned on a per download basis by using the [edd_social_discount] shortcode.', 'edd-social-discounts' ) . '</p>',
			'type' => 'select',
			'options' => apply_filters( 'edd_social_discounts_settings_display_services', array(
					'before' =>  __( 'Before content', 'edd-social-discounts' ),
					'after' =>  __( 'After content', 'edd-social-discounts' ),
					'none' =>  __( 'Disable automatic display (use shortcode instead)', 'edd-social-discounts' ),
				)
			),
			'std' => 'after'
		),
		array(
			'id' => 'edd_sd_discount_code',
			'name' => __( 'Discount Code', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Select the EDD discount that will be applied to the checkout. Leave as default to use plugin as simple sharing service.', 'edd-social-discounts' ) . '</p>',
			'type' => 'select',
			'options' => $discount_options
		),
		array(
			'id' => 'edd_sd_share_to_unlock_title',
			'name' => __( 'Share To Unlock Title', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Enter the title that will appear above the sharing services.', 'edd-social-discounts' ) . '</p>',
			'type' => 'text',
			'std' =>  __( 'Share to unlock!', 'edd-social-discounts' ),
		),
		array(
			'id' => 'edd_sd_share_to_unlock_message',
			'name' => __( 'Share To Unlock Message', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Enter the message that will appear underneath the Social Discount Title.', 'edd-social-discounts' ) . '</p>',
			'type' => 'textarea',
			'std' =>  __( 'Simply share this product and it will be unlocked.', 'edd-social-discounts' ),
		),
		array(
			'id' => 'edd_sd_share_to_unlock_success_title',
			'name' => __( 'Share To Unlock Success Title', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Enter the title that will appear above the sharing services when the product has been shared.', 'edd-social-discounts' ) . '</p>',
			'type' => 'text',
			'std' =>  __( 'Thanks for sharing!', 'edd-social-discounts' ),
		),
		array(
			'id' => 'edd_sd_share_to_unlock_success_message',
			'name' => __( 'Share To Unlock Success Message', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Enter the message that will appear underneath the Social Discount Title when the product has been shared.', 'edd-social-discounts' ) . '</p>',
			'type' => 'textarea',
			'std' =>  __( 'Add this product to your cart and the discount will be applied.', 'edd-social-discounts' ),
		),
		array(
			'id' => 'edd_sd_title',
			'name' => __( 'Social Discount Title', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Enter the title that will appear above the sharing services.', 'edd-social-discounts' ) . '</p>',
			'type' => 'text',
			'std' =>  __( 'Share for a discount', 'edd-social-discounts' ),
		),
		array(
			'id' => 'edd_sd_message',
			'name' => __( 'Social Discount Message', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Enter the message that will appear underneath the Social Discount Title.', 'edd-social-discounts' ) . '</p>',
			'type' => 'textarea',
			'std' =>  __( 'Simply share this and a discount will be applied to your purchase at checkout.', 'edd-social-discounts' ),
		),
		array(
			'id' => 'edd_sd_success_title',
			'name' => __( 'Social Discount Success Title', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Enter the title that will appear above the sharing services when the product has been shared.', 'edd-social-discounts' ) . '</p>',
			'type' => 'text',
			'std' =>  __( 'Thanks for sharing!', 'edd-social-discounts' ),
		),
		array(
			'id' => 'edd_sd_success_message',
			'name' => __( 'Social Discount Success Message', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Enter the message that will appear underneath the Social Discount Title when the product has been shared.', 'edd-social-discounts' ) . '</p>',
			'type' => 'textarea',
			'std' =>  __( 'Add this product to your cart and the discount will be applied.', 'edd-social-discounts' ),
		),
		array(
			'id' => 'edd_sd_twitter_username',
			'name' => __( 'Twitter Username', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Enter the Twitter username you want the Follow button to use. Leave blank to disable.', 'edd-social-discounts' ) . '</p>',
			'type' => 'text',
			'std' => ''
		),
		array(
			'id' => 'edd_sd_twitter_count_box',
			'name' => __( 'Twitter Count Box Position', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Displays how the count box is positioned with the button.', 'edd-social-discounts' ) . '</p>	',
			'type' => 'select',
			'options' => apply_filters( 'edd_social_discounts_settings_twitter_count_box', array(
					'horizontal' =>  __( 'Horizontal', 'edd-social-discounts' ),
					'vertical' =>  __( 'Vertical', 'edd-social-discounts' ),
					'none' =>  __( 'None', 'edd-social-discounts' ),
				)
			),
			'std' => 'vertical'
		),
		array(
			'id' => 'edd_sd_twitter_button_size',
			'name' => __( 'Twitter Button Size', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Note: the count box cannot show when large is selected.', 'edd-social-discounts' ) . '</p>',
			'type' => 'select',
			'options' => apply_filters( 'edd_sd_twitter_button_size', array(
					'medium' =>  __( 'Medium', 'edd-social-discounts' ),
					'large' =>  __( 'Large', 'edd-social-discounts' ),
				)
			),
			'std' => 'medium'
		),
		array(
			'id' => 'edd_sd_twitter_locale',
			'name' => __( 'Twitter Locale', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Enter the language code, eg en.', 'edd-social-discounts' ) . '</p>',
			'type' => 'text',
			'std' => 'en'
		),
		array(
			'id' => 'edd_sd_facebook_button_layout',
			'name' => __( 'Facebook Button Layout', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Layout of the button and count.', 'edd-social-discounts' ) . '</p>',
			'type' => 'select',
			'options' => apply_filters( 'edd_social_discounts_settings_facebook_button_layout', array(
					'button_count' =>  __( 'Button Count', 'edd-social-discounts' ),
					'box_count' =>  __( 'Box Count', 'edd-social-discounts' ),
				)
			),
			'std' => 'box_count'
		),
		array(
			'id' => 'edd_sd_facebook_locale',
			'name' => __( 'Facebook Locale', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Enter the language code, eg en_US. Facebook uses ISO country codes.', 'edd-social-discounts' ) . '</p>',
			'type' => 'text',
			'std' => 'en_US'
		),
		array(
			'id' => 'edd_sd_googleplus_button_annotation',
			'name' => __( 'Google+ Button Annotation', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'The style of the annotation that displays next to the button.', 'edd-social-discounts' ) . '</p>',
			'type' => 'select',
			'options' => apply_filters( 'edd_social_discounts_settings_googleplus_button_annotation', array(
					'bubble' =>  __( 'Bubble', 'edd-social-discounts' ),
					'inline' =>  __( 'Inline', 'edd-social-discounts' ),
					'none' =>  __( 'None', 'edd-social-discounts' ),
				)
			),
			'std' => 'bubble'
		),
		array(
			'id' => 'edd_sd_googleplus_button_size',
			'name' => __( 'Google+ Button Size', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'The size of the button', 'edd-social-discounts' ) . '</p>',
			'type' => 'select',
			'options' => apply_filters( 'edd_social_discounts_settings_googleplus_button_size', array(
					'small' =>  __( 'Small', 'edd-social-discounts' ),
					'medium' =>  __( 'Medium', 'edd-social-discounts' ),
					'standard' =>  __( 'Standard', 'edd-social-discounts' ),
					'tall' =>  __( 'Tall', 'edd-social-discounts' ),
				)
			),
			'std' => 'tall'
		),
		array(
			'id' => 'edd_sd_googleplus_button_recommendations',
			'name' => __( 'Google+ Button Recommendations', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Show recommendations within the +1 hover bubble', 'edd-social-discounts' ) . '</p>',
			'type' => 'select',
			'options' => apply_filters( 'edd_social_discounts_settings_googleplus_button_recommendations', array(
					'true' =>  __( 'Yes', 'edd-social-discounts' ),
					'false' =>  __( 'No', 'edd-social-discounts' ),
				)
			),
			'std' => 'true'
		),
		array(
			'id' => 'edd_sd_googleplus_locale',
			'name' => __( 'Google+ Locale', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Enter the language code, eg en-US. ', 'edd-social-discounts' ) . sprintf( '<a title="%s" href="%s" target="_blank">' . __( 'List of supported languages', 'edd-social-discounts' ) . '</a>.', __( 'List of supported languages', 'edd-social-discounts' ), 'https://developers.google.com/+/web/api/supported-languages' ) . '</p>',
			'type' => 'text',
			'std' => 'en-US'
		),
		array(
			'id' => 'edd_sd_linkedin_counter',
			'name' => __( 'LinkedIn Counter', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Whether or not to show the the share count and where it gets displayed', 'edd-social-discounts' ) . '</p>',
			'type' => 'select',
			'options' => apply_filters( 'edd_social_discounts_settings_linkedin_counter', array(
					'top' =>  __( 'Top', 'edd-social-discounts' ),
					'right' =>  __( 'Right', 'edd-social-discounts' ),
					'' =>  __( 'None', 'edd-social-discounts' ),
				)
			),
			'std' => 'top'
		),
		array(
			'id' => 'edd_sd_linkedin_locale',
			'name' => __( 'LinkedIn Locale', 'edd-social-discounts' ),
			'desc' => '<p class="description">' . __( 'Enter the language code, eg en_US. ', 'edd-social-discounts' ) . '</p>',
			'type' => 'text',
			'std' => 'en_US'
		),
	);

	return array_merge( $settings, $plugin_settings );
}
add_filter( 'edd_settings_extensions', 'edd_social_admin_settings' );