<?php
/*
Plugin Name: Easy Digital Downloads - Social Discounts
Plugin URI: https://easydigitaldownloads.com/extensions/edd-social-discounts/
Description: Offer customers a discount for sharing your products.
Version: 2.1
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
			$this->version 		= '2.1';
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
			if ( ! class_exists( 'EDD_License' ) ) {
				include( dirname( $this->file ) . '/includes/EDD_License_Handler.php' );
			}

			require_once( dirname( $this->file ) . '/includes/discount-functions.php' );
			require_once( dirname( $this->file ) . '/includes/ajax-functions.php' );
			require_once( dirname( $this->file ) . '/includes/functions.php' );
			require_once( dirname( $this->file ) . '/includes/template-functions.php' );
			require_once( dirname( $this->file ) . '/includes/scripts.php' );
			require_once( dirname( $this->file ) . '/includes/admin-settings.php' );
			require_once( dirname( $this->file ) . '/includes/shortcode.php' );


		//	require_once( dirname( $this->file ) . '/includes/twitter.php' );
			

			if ( ! is_admin() )
				return;

			require_once( dirname( $this->file ) . '/includes/admin-discounts.php' );
			require_once( dirname( $this->file ) . '/includes/metabox.php' );
			require_once( dirname( $this->file ) . '/includes/view-order-details.php' );
			
			
		}

		/**
		 * Setup the default hooks and actions
		 *
		 * @since 2.0
		 *
		 * @return void
		 */
		private function hooks() {
			// check for EDD when plugin is activated
			add_action( 'admin_init', array( $this, 'activation' ), 1 );
			
			// plugin meta
			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta' ), null, 2 );

			// settings link on plugin page
			add_filter( 'plugin_action_links_' . $this->basename, array( $this, 'settings_link' ), 10, 2 );
			
			// insert actions
			do_action( 'edd_sd_setup_actions' );
		}

		/**
		 * Licensing
		 *
		 * @since 2.0
		*/
		private function licensing() {
			//check EDD_License class is exist
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
		 * Activation function fires when the plugin is activated.
		 *
		 * This function is fired when the activation hook is called by WordPress,
		 * it flushes the rewrite rules and disables the plugin if EDD isn't active
		 * and throws an error.
		 *
		 * @since 2.0
		 * @access public
		 *
		 * @return void
		 */
		public function activation() {
			if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
				// is this plugin active?
				if ( is_plugin_active( $this->basename ) ) {
					// deactivate the plugin
			 		deactivate_plugins( $this->basename );
			 		// unset activation notice
			 		unset( $_GET[ 'activate' ] );
			 		// display notice
			 		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
				}

			}
		}

		/**
		 * Admin notices
		 *
		 * @since 2.0
		*/
		public function admin_notices() {
			$edd_plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/easy-digital-downloads/easy-digital-downloads.php', false, false );

			if ( ! is_plugin_active('easy-digital-downloads/easy-digital-downloads.php') ) {
				echo '<div class="error"><p>' . sprintf( __( 'You must install %sEasy Digital Downloads%s to use the EDD Social Discounts Add-On.', 'edd-social-discounts' ), '<a href="http://easydigitaldownloads.com" title="Easy Digital Downloads" target="_blank">', '</a>' ) . '</p></div>';
			}

			if ( $edd_plugin_data['Version'] < '1.8.4' ) {
				echo '<div class="error"><p>' . __( 'The EDD Social Discounts requires at least Easy Digital Downloads Version 1.8.4. Please update Easy Digital Downloads.', 'edd-social-discounts' ) . '</p></div>';
			}
		}

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
		 * Modify plugin metalinks
		 *
		 * @access      public
		 * @since       1.0.0
		 * @param       array $links The current links array
		 * @param       string $file A specific plugin table entry
		 * @return      array $links The modified links array
		 */
		public function plugin_meta( $links, $file ) {
		    if ( $file == plugin_basename( __FILE__ ) ) {
		        $plugins_link = array(
		            '<a title="View more plugins for Easy Digital Downloads by Sumobi" href="https://easydigitaldownloads.com/blog/author/andrewmunro/?ref=166" target="_blank">' . __( 'Author\'s EDD plugins', 'edd-wish-lists' ) . '</a>'
		        );

		        $links = array_merge( $links, $plugins_link );
		    }

		    return $links;
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
	return EDD_Social_Discounts::get_instance();
}

/**
 * Loads plugin after all the others have loaded and have registered their hooks and filters
 *
 * @since 2.0
*/
add_action( 'plugins_loaded', 'edd_social_discounts', apply_filters( 'edd_social_discounts_action_priority', 10 ) );

endif;