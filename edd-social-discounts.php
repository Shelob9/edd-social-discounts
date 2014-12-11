<?php
/*
Plugin Name: Easy Digital Downloads - Social Discounts
Plugin URI: https://easydigitaldownloads.com/extensions/edd-social-discounts/
Description: Offer customers a discount for sharing your products.
Version: 2.0.3
Author: Andrew Munro, Sumobi
Author URI: http://sumobi.com/
License: GPL-2.0+
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'EDD_Social_Discounts' ) ) :

	final class EDD_Social_Discounts {

		/**
		 * Holds the instance
		 *
		 * Ensures that only one instance of EDD Social Discounts exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property that holds the singleton instance.
		 *
		 * @var object
		 * @since 2.0
		 */
		private static $instance;

		/**
		 * Holds the required scripts for the plugin
		 *
		 * @since 2.0
		*/
		private static $add_script;

		/**
		 * Enable our share box
		 * 
		 * @var boolean
		 * @since 2.0.1
		 */
		public static $share_box_enabled = true;

		/**
		 * Main Instance
		 *
		 * Ensures that only one instance exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 2.0
		 *
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Social_Discounts ) ) {
				self::$instance = new EDD_Social_Discounts;
				self::$instance->setup_globals();
				self::$instance->includes();
				self::$instance->hooks();
				self::$instance->licensing();
			}

			return self::$instance;
		}

		/**
		 * Constructor Function
		 *
		 * @since 2.0
		 * @access private
		 * @see EDD_Social_Discounts::init()
		 * @see EDD_Social_Discounts::activation()
		 */
		private function __construct() {
			self::$instance = $this;

			add_action( 'init', array( $this, 'init' ) );
		}

		/**
		 * Reset the instance of the class
		 *
		 * @since 2.0
		 * @access public
		 * @static
		 */
		public static function reset() {
			self::$instance = null;
		}

		/**
		 * Globals
		 *
		 * @since 2.0
		 * @return void
		 */
		private function setup_globals() {
			$this->version 		= '2.0.3';
			$this->title 		= 'EDD Social Discounts';

			// paths
			$this->file         = __FILE__;
			$this->basename     = apply_filters( 'edd_social_discounts_plugin_basenname', plugin_basename( $this->file ) );
			$this->plugin_dir   = apply_filters( 'edd_social_discounts_plugin_dir_path',  plugin_dir_path( $this->file ) );
			$this->plugin_url   = apply_filters( 'edd_social_discounts_plugin_dir_url',   plugin_dir_url ( $this->file ) );
		}

		/**
		 * Function fired on init
		 *
		 * This function is called on WordPress 'init'. It's triggered from the
		 * constructor function.
		 *
		 * @since 2.0
		 * @access public
		 *
		 * @uses EDD_Social_Discounts::load_textdomain()
		 *
		 * @return void
		 */
		public function init() {
			do_action( 'edd_sd_before_init' );

			$this->load_textdomain();

			do_action( 'edd_sd_after_init' );
		}

		/**
		 * Includes
		 *
		 * @since 2.0
		 * @access private
		 * @return void
		 */
		private function includes() {
			
		}

		/**
		 * Setup the default hooks and actions
		 *
		 * @since 2.0
		 *
		 * @return void
		 */
		private function hooks() {

			// display sharing buttons on product pages automatically
			add_action( 'template_redirect', array( $this, 'display_share_buttons' ) );

			// collect IDs of shared downloads
			add_action( 'template_redirect', array( $this, 'shared_downloads' ) );

			// share product + apply discount using ajax
			add_action( 'wp_ajax_share_product',  array( $this, 'share_product' ) );
			add_action( 'wp_ajax_nopriv_share_product',  array( $this, 'share_product' ) );

			// print scripts
			add_action( 'wp_footer', array( $this, 'print_script' ) );

			// view order details
			add_action( 'edd_view_order_details_main_after', array( $this, 'view_order_details' ) );

			// load CSS
			add_action( 'wp_head',  array( $this, 'load_css' ) );

			// add settings
			add_filter( 'edd_settings_extensions', array( $this, 'settings' ) );

			// update post meta on successful purchase
			add_filter( 'edd_complete_purchase', array( $this, 'update_post_meta' ) );
			
			// settings link on plugin page
			add_filter( 'plugin_action_links_' . $this->basename, array( $this, 'settings_link' ), 10, 2 );
			
			// shortcode
			add_shortcode( 'edd_social_discount', array( $this, 'shortcode' ) );

			// insert actions
			do_action( 'edd_sd_setup_actions' );
		}

		/**
		 * Licensing
		 *
		 * @since 2.0
		*/
		private function licensing() {
			// check if EDD_License class exists
			if ( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( $this->file, $this->title, $this->version, 'Andrew Munro' );
			}
		}

		/**
		 * Loads the plugin language files
		 *
		 * @access public
		 * @since 2.0
		 * @return void
		 */

		public function load_textdomain() {
			// Set filter for plugin's languages directory
			$lang_dir = dirname( plugin_basename( $this->file ) ) . '/languages/';
			$lang_dir = apply_filters( 'edd_social_discounts_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale        = apply_filters( 'plugin_locale',  get_locale(), 'edd-social-discounts' );
			$mofile        = sprintf( '%1$s-%2$s.mo', 'edd-social-discounts', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/edd-social-discounts/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-auto-register folder
				load_textdomain( 'edd-social-discounts', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-auto-register/languages/ folder
				load_textdomain( 'edd-social-discounts', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-social-discounts', false, $lang_dir );
			}
		}

		/**
		 * Display the share button
		 * Automatically adds the sharing buttons to product pages. Can be overridden with shortcode on per product basis
		 *
		 * @since 2.0
		*/
		public function display_share_buttons() {
			$display = function_exists( 'edd_get_option' ) ? edd_get_option( 'edd_sd_display_services', 'after' ) : '';

			// don't automatically output the sharing services
			if ( 'none' != $display ) {
				// load sharing box by default after download content 
				if ( 'after' == $display ) {
					add_action( 'edd_after_download_content', array( $this, 'load_share_box' ) );
				}
				// load before content
				elseif ( 'before' == $display ) {
					add_action( 'edd_before_download_content', array( $this, 'load_share_box' ) );
				}
			}
		}

		/**
		 * Load sharebox
		 *
		 * @since 2.0
		*/
		public function load_share_box() {
			// if shortcode is detected on page already, then return
			if ( $this->has_shortcode( 'edd_social_discount' ) )
				return;

			echo $this->share_box();
		}

		/**
		 * Is discount active within extension's setttngs
		 * @return boolean true if discount, false otherwise
		 * @since  2.0
		 */
		public function is_discount_active() {
			global $edd_options;

			$discount_active = isset( $edd_options['edd_sd_discount_code'] ) && '0' != $edd_options['edd_sd_discount_code'] ? true : false;
			
			return $discount_active;
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
		 */
		public function share_box( $id = '', $title = '', $message = '', $tweet = '' ) {

			if ( ! function_exists( 'EDD' ) ) {
				return;
			}

			global $edd_options;

			// return if our share box has been turned off
			if ( ! self::$share_box_enabled )
				return;

			// load required scripts if template tag or shortcode has been used
			self::$add_script = true;

			// get custom title, else default title
			// show the success message if product has been shared, else default title
			if ( EDD()->session->get( 'edd_shared_ids' ) && in_array( get_the_ID(), EDD()->session->get( 'edd_shared_ids' ) ) ) {
				$share_title = $this->success_title( $id );
				$share_message = $this->success_message( $id );
			}
			else {
				// custom title passed into function 
				if ( $title ) {
					$share_title = esc_attr( $title );
				}
				// title from plugin settings
				else {
					$share_title = isset( $edd_options['edd_sd_title'] ) && ! empty( $edd_options['edd_sd_title'] ) ? esc_attr( $edd_options['edd_sd_title'] ) : '';
				}

				// custom message
				if ( $message ) {
					$share_message = esc_attr( $message );
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
			// default twitter message that is shown when shared. 
			// if an ID was passed
			elseif ( $id ) {
				$twitter_default_text = get_the_title( $id );
			}
			// else if we're on a single download page
			elseif ( is_singular( 'download' ) ) {
				$twitter_default_text = the_title_attribute( 'echo=0' );
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
			<div class="<?php echo apply_filters( 'edd_social_discounts_classes', 'edd-sd-share' ); ?>">

				<?php 
					// show the title and message, but if the product has been shared, show the success message
					echo apply_filters( 'edd_social_discounts_share_title', '<h3 class="edd-sd-title">' . $share_title . '</h3>' );
					echo apply_filters( 'edd_social_discounts_share_message', '<p class="edd-sd-message">' . $share_message . '</p>' );
				?>
				
				<?php do_action( 'edd_social_discounts_before_share_box' ); ?>

				<?php if ( $this->is_enabled( 'twitter' ) ) : 
					$twitter_username = isset( $edd_options['edd_sd_twitter_username'] ) ? esc_attr( $edd_options['edd_sd_twitter_username'] ) : '';
					// defaults to en_US if left blank
					$locale = isset( $edd_options['edd_sd_twitter_locale'] ) && ! empty( $edd_options['edd_sd_twitter_locale'] ) ? $edd_options['edd_sd_twitter_locale'] : 'en';
					$twitter_count_box = edd_get_option( 'edd_sd_twitter_count_box', 'vertical' );
					$twitter_button_size = edd_get_option( 'edd_sd_twitter_button_size', 'medium' );
				?>
				<div class="edd-sd-service twitter">
					<a href="https://twitter.com/share" data-lang="<?php echo $locale; ?>" class="twitter-share-button" data-count="<?php echo $twitter_count_box; ?>" data-size="<?php echo $twitter_button_size; ?>" data-counturl="<?php echo post_permalink( $id ); ?>" data-url="<?php echo $share_url; ?>" data-text="<?php echo $twitter_default_text; ?>" data-via="<?php echo $twitter_username; ?>" data-related=""><?php _e( 'Share', 'edd-social-discounts' ); ?></a>
				</div>
				<?php endif; ?>

				<?php if ( $this->is_enabled( 'facebook' ) ) :
					// filter for enabling share button although won't trigger discount
					$data_share = apply_filters( 'edd_social_discounts_facebook_share_button', 'false' );
					$facebook_button_layout = edd_get_option( 'edd_sd_facebook_button_layout', 'box_count' );
				?>
				
				<div class="edd-sd-service facebook">
					<div class="fb-like" data-href="<?php echo $share_url; ?>" data-send="true" data-action="like" data-layout="<?php echo $facebook_button_layout; ?>" data-share="<?php echo $data_share; ?>" data-width="" data-show-faces="false"></div>
				</div>
				<?php endif; ?>

				<?php if ( $this->is_enabled( 'googleplus' ) ) : 
					$googleplus_button_size = isset( $edd_options['edd_sd_googleplus_button_size'] ) ? $edd_options['edd_sd_googleplus_button_size'] : 'tall';
					$google_button_annotation = edd_get_option( 'edd_sd_googleplus_button_annotation', 'bubble' );
					$google_button_recommendations = edd_get_option( 'edd_sd_googleplus_button_recommendations', 'false' );
				?>
				<div class="edd-sd-service googleplus">
					<div class="g-plusone" data-recommendations="<?php echo $google_button_recommendations; ?>" data-annotation="<?php echo $google_button_annotation;?>" data-callback="plusOned" data-size="<?php echo $googleplus_button_size; ?>" data-href="<?php echo $share_url; ?>"></div>
				</div>
				<?php endif; ?>

				<?php if ( $this->is_enabled( 'linkedin' ) ) :
					$locale = isset( $edd_options['edd_sd_linkedin_locale'] ) && ! empty( $edd_options['edd_sd_linkedin_locale'] ) ? $edd_options['edd_sd_linkedin_locale'] : 'en_US';
					$linkedin_counter = edd_get_option( 'edd_sd_linkedin_counter', 'top' );
				?>
				<div class="edd-sd-service linkedin">
				<script src="//platform.linkedin.com/in.js" type="text/javascript">lang: <?php echo $locale; ?></script>
				<script type="IN/Share" data-counter="<?php echo $linkedin_counter; ?>" data-onSuccess="share" data-url="<?php echo $share_url; ?>"></script>
				</div>
				<?php endif; ?>

				<?php do_action( 'edd_social_discounts_after_share_box' ); ?>

			</div>

		<?php 
			$share_box = ob_get_clean();
			return apply_filters( 'edd_social_discounts_share_box', $share_box );
		}


		/**
		 * Print scripts
		 *
		 * @since 2.0
		*/
		public function print_script() {
			global $edd_options;

			if ( ! self::$add_script )
				return;
			?>
			<script type="text/javascript">

			<?php 
			/**
			 * Twitter
			 *
			 * @since 2.0
			*/
			if ( $this->is_enabled( 'twitter' ) ) : 
				?>
			  	window.twttr = (function (d,s,id) {
				  var t, js, fjs = d.getElementsByTagName(s)[0];
				  if (d.getElementById(id)) return; js=d.createElement(s); js.id=id;
				  js.src="https://platform.twitter.com/widgets.js"; fjs.parentNode.insertBefore(js, fjs);
				  return window.twttr || (t = { _e: [], ready: function(f){ t._e.push(f) } });
				}(document, "script", "twitter-wjs"));

				twttr.ready(function (twttr) {
				    twttr.events.bind('tweet', function (event) {
				        jQuery.event.trigger({
				            type: "productShared",
				            url: event.target.baseURI
				        });
				    });
				});

				<?php endif; ?>

				<?php 
				/**
				 * Google +
				 *
				 * @since 2.0
				*/
				if ( $this->is_enabled( 'googleplus' ) ) : 
					// defaults to en_US if left blank
					$locale = isset( $edd_options['edd_sd_googleplus_locale'] ) && ! empty( $edd_options['edd_sd_googleplus_locale'] ) ? $edd_options['edd_sd_googleplus_locale'] : 'en-US';
				?>
					window.___gcfg = {
					  lang: '<?php echo $locale; ?>',
					  parsetags: 'onload'
					};

					(function() {
					    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
					    po.src = 'https://apis.google.com/js/plusone.js';
					    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
					  })();

					function plusOned(obj) {
						console.log(obj);
						jQuery.event.trigger({
						    type: "productShared",
						    url: obj.href
						});
					}
				<?php endif; ?>

				<?php 
				/**
				 * LinkedIn
				 *
				 * @since 2.0
				*/
				if ( $this->is_enabled( 'linkedin' ) ) : ?>
					function share(url) {
						console.log(url);
					 	jQuery.event.trigger({
				            type: "productShared",
				            url: url
				        });
					}
				
				<?php endif; ?>

				<?php
				/**
				 * Facebook
				 *
				 * @since 2.0
				*/
				if ( $this->is_enabled( 'facebook' ) ) : 
					// defaults to en_US if left blank
					$locale = isset( $edd_options['edd_sd_facebook_locale'] ) && ! empty( $edd_options['edd_sd_facebook_locale'] ) ? $edd_options['edd_sd_facebook_locale'] : 'en_US';
					?>

					(function(d, s, id) {
					     var js, fjs = d.getElementsByTagName(s)[0];
					     if (d.getElementById(id)) {return;}
					     js = d.createElement(s); js.id = id;
					     js.src = "//connect.facebook.net/<?php echo $locale; ?>/all.js";
					     fjs.parentNode.insertBefore(js, fjs);
					 }(document, 'script', 'facebook-jssdk'));

					window.fbAsyncInit = function() {
					    // init the FB JS SDK
					    FB.init({
					      status	: true,
					      cookie	: true,                               
					      xfbml		: true                              
					    });

					    FB.Event.subscribe('edge.create', function(href, widget) {
					        jQuery.event.trigger({
					            type: "productShared",
					            url: href
					        });     
					    });
					};
				<?php endif; ?>

				<?php 
				/**
				 * Listen for the productShared event
				 *
				 * @since 2.0
				*/
				if ( $this->is_enabled() ) : ?>

				/* <![CDATA[ */
				var edd_social_discount_vars = {
					"ajaxurl": "<?php echo edd_get_ajax_url(); ?>",
					"edd_sd_nonce": "<?php echo wp_create_nonce( 'edd_sd_nonce' ); ?>"
				};
				/* ]]> */

				jQuery(document).ready(function ($) {

					jQuery(document).on( 'productShared', function(e) {

					    	var postData = {
					            action: 'share_product',
					            product_id: <?php echo get_the_ID(); ?>, // post the download's ID
					            nonce: edd_social_discount_vars.edd_sd_nonce
					        };

					    	$.ajax({
				            type: "POST",
				            data: postData,
				            dataType: "json",
				            url: edd_social_discount_vars.ajaxurl,
				            success: function ( share_response ) {

				                if( share_response ) {

				                    if ( share_response.msg == 'valid' ) {
										console.log('successfully shared');
										console.log( share_response );

										$('.edd_cart_discount').html( share_response.html );
                        				$('.edd_cart_discount_row').show();

                        				// update cart amounts with new total
										$('.edd_cart_amount').each(function() {
											$(this).text(share_response.total);
										});

				                       jQuery('.edd-sd-share .edd-sd-title').html( share_response.success_title );
				                       jQuery('.edd-sd-share .edd-sd-message').html( share_response.success_message );

				                       // add CSS class so the box can be styled
				                       jQuery('.edd-sd-share').addClass('shared');
				                    } 
				                    else {
				                        console.log('failed to share');
				                        console.log( share_response );
				                    }
				                } 
				                else {
				                    console.log( share_response );
				                }
				            }
				        }).fail(function (data) {
				            console.log( data );
				        });

					});
				});
			<?php endif; ?>
			</script>
			<?php
		}

		/**
		 * Load CSS inline to avoid extra http request. There's very minimal CSS.
		 *
		 * @since 2.0
		*/
		public function load_css() {
		if ( ! $this->is_enabled() )
			return;
		?>
			<style>.edd-sd-service { display: inline-block; margin: 0 1em 1em 0; vertical-align: top; } .edd-sd-service iframe { max-width: none; }</style>
			<?php
		}

		/**
		 * Success Title when download has been shared
		 *
		 * @since 2.0
		*/
		public function success_title() {
			global $edd_options;

			$title = edd_get_option( 'edd_sd_success_title', __( 'Thanks for sharing!', 'edd-social-discounts' ) );

			return apply_filters( 'edd_social_discounts_success_title', $title );
		}

		/**
		 * Success Message when download has been shared
		 *
		 * @since 2.0
		*/
		public function success_message( $product_id ) {
			global $edd_options;
			
			$message = edd_get_option( 'edd_sd_success_message', __( 'Add this product to your cart and the discount will be applied.', 'edd-social-discounts' ) );

			return apply_filters( 'edd_social_discounts_success_message', $message, $product_id );
		}

		/**
		 * Load discount
		 *
		 * @since 2.0
		*/
		public function share_product() {
			if ( ! isset( $_POST['product_id'] ) )
				return;

			// check nonce
			check_ajax_referer( 'edd_sd_nonce', 'nonce' );

			global $edd_options;

			// get discount code's ID from plugin settings
			$discount  = edd_get_option( 'edd_sd_discount_code', '' );

			// get discount code by ID
			$discount  = edd_get_discount_code( $discount );

			// set cart discount. Discount will only be applied if discount exists.
			$discounts = edd_set_cart_discount( $discount );
			$total     = edd_get_cart_total( $discounts );

			// purchase was shared
			EDD()->session->set( 'edd_shared', true );

			// store the download ID temporarily
			EDD()->session->set( 'edd_shared_id', $_POST['product_id'] );
			
			$return = apply_filters( 'edd_social_discounts_ajax_return', array(
				'msg'             => 'valid',
				'success_title'	  => $this->success_title(),
				'success_message' => $this->success_message( $_POST['product_id'] ),
				'product_id'      => $_POST['product_id'],
				'total'           => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
				'html'            => edd_get_cart_discounts_html( $discounts )
			) );

			echo json_encode( $return );

			edd_die();
		}
		
		/**
		 * Collect IDs of download's shared before purchasing
		 *
		 * @return 	void 
		 * @since 	2.0
		*/
		public function shared_downloads() {
			$ids = array();

			if ( ! function_exists( 'EDD' ) ) {
				return;
			}

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

		/**
		 * Store metakeys with purchase
		 * 
		 * @param  int $payment_id
		 * @return void
		 * @since  2.0
		 */
		public function update_post_meta( $payment_id ) {
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
	
		/**
		 * Shortcode
		 * 
		 * @param  array $atts
		 * @param  $content
		 * @return object
		 * @since  2.0
		 */
		public function shortcode( $atts, $content = null ) {

			extract( shortcode_atts( array(
					'id' => '',
					'title' => '',
					'message' => '',
					'tweet' => ''
				), $atts, 'edd_social_discount' )
			);

			$content = $this->share_box( $id, $title, $message, $tweet );

			return $content;
		
		}

		/**
		 * Check for existance of shortcode
		 * 
		 * @param  string  $shortcode
		 * @return boolean
		 * @since  2.0
		 */
		public function has_shortcode( $shortcode = '' ) {
			global $post;

			// false because we have to search through the post content first
			$found = false;

			// if no short code was provided, return false
			if ( !$shortcode ) {
				return $found;
			}

			if (  is_object( $post ) && stripos( $post->post_content, '[' . $shortcode ) !== false ) {
				// we have found the short code
				$found = true;
			}

			// return our final results
			return $found;
		}

		/**
		 * Check that each social network is enabled
		 * @param  string  $network
		 * @return boolean
		 * @since  2.0
		 */
		public function is_enabled( $network = '' ) {

			$networks = function_exists( 'edd_get_option' ) ? edd_get_option( 'edd_sd_services', '' ) : '';

			// if network is passed as parameter
			if ( $network ) {
				switch ( $network ) {

					case 'twitter':
						return isset( $networks[$network] );
						break;

					case 'facebook':
						return isset( $networks[$network] );
						break;
						
					case 'googleplus':
						return isset( $networks[$network] );
						break;
						
					case 'linkedin':
						return isset( $networks[$network] );
						break;			
					
				}
			}
			elseif ( $networks ) {
				return true;
			}

		}

		/**
		 * Downloads that were shared before this order was completed
		 * 
		 * @param  int $payment_id	the payment ID
		 * @return void
		 * @since  2.0
		 */
		public function view_order_details( $payment_id ) {
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

		/**
		 * Plugin settings link
		 *
		 * @since 2.0
		*/
		public function settings_link( $links ) {
			$plugin_links = array(
				'<a href="' . admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions' ) . '">' . __( 'Settings', 'edd-social-discounts' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Settings
		 *
		 * @since 2.0
		*/
		public function settings( $settings ) {
			
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

	}
	
	/**
	 * Loads a single instance of EDD Social Discounts
	 *
	 * This follows the PHP singleton design pattern.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * @example <?php $edd_social_discounts = edd_social_discounts(); ?>
	 * @since 2.0
	 * @see EDD_Social_Discounts::get_instance()
	 * @return object Returns an instance of the EDD_Social_Discounts class
	 */
	function edd_social_discounts() {

	    if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {

	        if ( ! class_exists( 'EDD_Extension_Activation' ) ) {
	            require_once 'includes/class-activation.php';
	        }

	        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
	        $activation = $activation->run();
	        return EDD_Social_Discounts::get_instance();
	        
	    } else {
	        return EDD_Social_Discounts::get_instance();
	    }
	}
	add_action( 'plugins_loaded', 'edd_social_discounts', apply_filters( 'edd_social_discounts_action_priority', 10 ) );



endif;