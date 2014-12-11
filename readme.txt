=== EDD Social Discounts ===

Plugin URI: https://easydigitaldownloads.com/extensions/edd-social-discounts/
Author: Andrew Munro, Sumobi
Author URI: http://sumobi.com/

Requires Easy Digital Downloads 1.8.4 or greater

== Demo ==

http://edd-social-discounts.sumobithemes.com/

== Documentation ==

http://sumobi.com/docs/edd-social-discounts/

== Installation ==

1. Unpack the entire contents of this plugin zip file into your `wp-content/plugins/` folder locally
1. Upload to your site
1. Navigate to `wp-admin/plugins.php` on your site (your WP Admin plugin page)
1. Activate this plugin

After activation, configure the plugin from downloads -> settings -> extensions

== Changelog ==

v2.0.3
December 10th, 2014

* New: Activation class to check for the existence of EDD
* Fix: Plugin deactivating when EDD was updated

v2.0.2
July 24th, 2014

New:   When using shortcode on the checkout, the cart total and discount will updated via ajax after share
Tweak: Removed old EDD licensing files that are no longer required

v2.0.1
December 22, 2013

New: edd_social_discounts_share_url filter hook for modifying the URL
New: edd_social_discounts_success_title filter hook
New: edd_social_discounts_success_message filter hook
New: edd_social_discounts_ajax_return filter hook
New: edd_social_discounts_before_share_box action hook
New: edd_social_discounts_after_share_box action hook
New: added CSS class names for each of the networks on their wrapping div

v2.0
December 18, 2013

Initial release