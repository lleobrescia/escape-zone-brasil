=== URL Coupons For WooCommerce ===
Contributors: jkohlbach, RymeraWebCo
Donate link:
Tags: woocommerce url coupons, url coupons, url coupons for woocommerce, coupons, woocommerce
Requires at least: 3.4
Tested up to: 4.7.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a unique URL for each coupon code on your WooCommerce store that applies the coupon when visited.

== DESCRIPTION ==

URL Coupons For WooCommerce gives store owners a unique link for each coupon on their site. Once visited this link will apply the coupon.

1. Easy for customers, just visit the link for the coupon
1. Hide coupon fields to increase conversions, but still allow them via coupon URLs
1. Apply links to graphics or text to let customers to apply with a click

Some features at a glance:

** Unique URL for each coupon code **

Adds a new URL Coupons tab to your coupon edit screen which shows you the link for the coupon as well as other options.

** Disable coupon fields on the front end **

You can optionall disable coupon code fields on the front end of the site. Studies have shown that coupon boxes are a real conversion killer because customers go back to Google to look for coupons.

Turn off the fields and only allow users to apply with a coupon URL and enjoy higher conversions!

** Flexible shortcode to make clickable links **

Create clickable text easily:

[url_coupon code="yourcouponcode"]The text you want to link[/url_coupon]

Wrap it around text or images and you're all set! Works in any content area!

Or even more simply, you can just print the code already pre-linked:

[url_coupon code="yourcouponcode"]

** Restrict coupons to user roles **

Want to only let the coupon be applied for certain roles?

Eg:
- Allow the coupon only for registered customers
- Allow the coupon only for guests
- Allow the coupon only for wholesale

Just tweak the restrictions on the coupon and you'll see a new Restrict To User Role field.

** Highly Compatible **

Works with just about any theme or plugin combination out there!

** WHO MADE THIS AMAZING PLUGIN? **

The URL Coupons For WooCommerce plugin was brought to you by the handsome folks over at Marketing Suite. The makers of the worlds foremost suite of marketing automation extensions for WooCommerce.

Click here to visit us and read about our other plugins:
https://marketingsuiteplugin.com

== Installation ==

1. Upload the `url-coupons-for-woocommerce/` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Setup a coupon as normal and visit the new URL Coupons tab. You'll also find settings under WooCommerce->Settings, URL Coupons tab.
1. ... Profit!

== Frequently asked questions ==

= My coupon URL links aren’t working. They show as 404 when visiting them, why is that? =

There are two main causes for this:
1. The permalinks are not flushed properly. This can be due to a conflict with another plugin or other code modifying the permalinks and not flushing them properly. To solve this, visit the Settings->Permalinks page and hit save. Then try visiting your coupon URL again.
2. The permalink structure selected is incompatible. URL Coupons for WooCommerce requires that you are NOT using the “Default” permalink structure. Try switching to the “Post Name” permalink structure in Settings->Permalink and hit save. Then try visiting your coupon URL again.

== Screenshots ==

1. New tab in the coupon edit screen

2. Option to restrict to certain roles 

3. Settings

4. Help area

== Changelog ==

= 1.0.2 = 
* Improvement: Add MS promo banner
* Bug Fix: Include necessary files on plugin uninstallation

= 1.0.1 =
* Feature: Add feature to let admins customize or set the coupon url
* Feature: Add option to disable coupon url functionality per coupon
* Improvement: Add a link to the settings in the plugin listings
* Bug Fix: When updating "URL Prefix" under General Options, the update only works on new created coupons and not the existing ones
* Bug Fix: When applying a link coupon, coupon codes with spaces print an error saying it does not exist
* Bug Fix: Permalinks not flushing properly on new installs
* Bug Fix: Create cart session if there is no session yet
* Bug Fix: Do not allow saving of empty or invalie url prefix.
* Bug Fix: Coupon not applied after coupon link is loaded

= 1.0.0 =
* Initial version

== Upgrade notice ==

There is a new version of URL Coupons For WooCommerce available.