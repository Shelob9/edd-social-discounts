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

= 2.1 =
* New: Per-product social discounts!
* New: "Social Discount Downloads" select field on discount page to select the products for the sharing discount
* New: "Social Sharing Discount" metabox on each download's edit/publish screen to select the discount the download should use when shared
* Tweak: Removed redundant EDD licensing files as they are now included in the core EDD plugin
* 

= 2.0.1 =
* New: edd_social_discounts_share_url filter hook for modifying the URL
* New: edd_social_discounts_success_title filter hook
* New: edd_social_discounts_success_message filter hook
* New: edd_social_discounts_ajax_return filter hook
* New: edd_social_discounts_before_share_box action hook
* New: edd_social_discounts_after_share_box action hook
* New: added CSS class names for each of the networks on their wrapping div

= 2.0 =
* Initial release