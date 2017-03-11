=== Plugin Name ===
Contributors: cxThemes
Tags: woocommerce
Stable tag: 1.26
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Save time and simplify your life by having the ability to create a new Customer directly on the WooCommerce Order screen.

== Description ==

What it Does
This plugin is a must-have for any WooCommerce store; we too are Shop Managers and developed this to greatly simplify our workflow.

The Current Situation
Currently, to create a new Order manually for a new Customer, a Shop Manager needs to go to the User section, create the new User, choose a random username, add a temporary password manually, email that password in the clear (security risk). Once the User is created the Manager can navigate BACK to the Order screen to create a new Order for that Customer. (Hassle)

Our Plugin
Now, with this plugin, all you need to do is add a new Order and click the Create Customer button. Fill in their email address (and First & Last Name if you have them) - and that’s it. The Order can then be completed for that new User without leaving the screen.

Create User on Order then takes care of the previously time consuming work by immediately and automatically sending the customer an email detailing how to securely set a password and log into their new account. 

AND the Shop Manager can then save the billing and shipping addresses BACK to the Customer profile directly from the new Order. Which by itself is an incredibly useful piece of functionality. 

Once the new Order is created, the Manager can email the invoice for payment directly to that new customer in the standard way.

Great For:
* Creating orders manually for new customers, over-the-phone or email
* Saving time and effort for Managers.
* Generating sales and engagement by minimising the hassle for customers
* Assisting customers with difficulty ordering for the first time
* Empowering customer service managers with better, more efficient tools
* Any WooCommerce store!

Happy Conversions from the CX Team!


== Documentation ==

Please see the included PDF for full instructions on how to use this plugin.
 

== Changelog ==

= 1.26 =
* Fix the 'Change Password' link in the email so that it works for all versions of WooCommerce again.

= 1.25 =
* Create Customer now works with our other useful plugin - Shop as Customer - which means you can Create a New Customer and instantly switch to, and shop as, that New Customer.
* We've moved the Create New Customer form into a new looking modal which means you will be able to add new customers from more places.
* Create Customer can now be activated anywhere that there's a `wc-customer-search` input. We now support WooCommerce Subscriptions and WooCommerce Bookings, as well as on the WooCommerce order page.
* Developers can add more Customer Search inputs - to add the Create Customer button to - by filtering `cxccoo_customer_search_inputs` (see the code for more).

= 1.24 =
* Make sure we only change the user select2 on the order-page user-form, not anywhere else. Fixes bug that would switch to the new user when using Create Customer plugin with out Shop as Customer plugin.

= 1.23 =
* Fix notice when new user returns to set their password, introduced in new version of WooCommerce.
* Tweak CSS of the Create Customer form on WC Order page.

= 1.22 =
* Fixed Create Customer form appearing un-styled on some installations.

= 1.20 =
* We've changed the way we do plugin auto-updates so we can better manage the demand for our plugins and updates. You will now be notified - as usual - about new available plugin updates. Then we'll require you to save your CodeCanyon purchase-code for our plugin - first time only - which will enable this and any future auto-updates. If you're not sure where to get your purchase code - don't worry, it will be explained in the plugin.
* We've added compatibility with WooCommerce Booking plugin - you can now Create New Customers on the Create Bookings page too.
* Added a settings icon/button on the Create Customer form for quick access to the Settings Page.
* The Create Customer form now submits when you push return/enter.
* Fixed the 'Save to Customer' checkboxes not displaying correctly with new version of WC.
* Only enqueue our scripts/css where needed.

= 1.19 =
* Fixed issue where Create Customer form will incorrectly be automatically opened on the Order page.
* Changed the user-role input on the Create Customer form to be a hidden field when you've chosen not to customize the user role.

= 1.18 =
* Updated Setting Page layout - WooCommerce > Create Customer on Order.
* Added setting 'User Role Selection' - User role selection already exists, but now it's a setting so you can toggle it on/off. Once activated you're presented with a second setting 'User Role Hierarchy' that you can use to define the hierarchy of any custom user roles.
* Added setting 'Edit Username' - Toggles the input on/off.
* Added setting 'Default User Role' - The default user role for all new users created.
* Updated role checking to handle multisite super_admin. Now, in multisite environment, a site 'admin' cannot create a new 'super_admin' user.
* Update the language localization function across all our plugins so they are all the same behaviour.

= 1.17 =
* Added improved check to prevent a user with lesser capabilities adding a user with higher capabilities - eg Shop Manager adding an Admin user.
* Change the way we generate the link to the lost-password page - to improve compatibility with plugins that change it's location.
* Improved the way we apply WooCommerce email styling to our Email.
* Remove possible conflict with the plugin WP Email Template (A3 Revolution).

= 1.16 =
* Fixed so is_woocommerce_active() check also works for multisite installations.

= 1.15 =
* Added optional Username field to Create Customer form.
* Changes to the text in the Customer Email and the Set Password page - Please re-translate if you need to - thanks.
* Changed email validation to use WP core function `is_email()` so more email address's are accepted.
* Added security check so for instance shop_manager cant create a higher privileged user like admin.
* Fixed the address Save-to-Customer checkbox not clickable on some WC versions.

= 1.14 =
* Fixed pre-population of the firstname, lastname, email into the new customers billing and shipping address.
* Changed to not auto-check the Save-to-Customer checkbox so admin can explicitly choose to do this.
* Fix missing image notification.

= 1.13 =
* Fix Save-to-User checkboxes not showing on WC2.4
* Only show Save-to-User checkboxes after customer chooses to Edit-Address.

= 1.12 =
* Add checkbox to disable the registration email being sent to the user.
* Added filter to auto tick this box - add this code to your functions.php: add_filter( 'woocommerce_create_customer_disable_email', '__return_true' );
* Better formatting of the email sent to new customer.
* Fixed undefined index notices.

= 1.11 =
* Added Internationalization how-to to the the docs.
* Updated the language files.
* Changes to the order and priority of the loaded language files. Will not effect anyone who is already using internationalization.
* Changed where in the code the WooCommerce and version number checking is done.
* Made more strings translatable.
* Escaped all add_query_args and remove_query_args for security.
* Updated PluginUpdateChecker class.

= 1.10 =
* Add support for Select2 (WC2.3) and Chozen for backward compatibility.
* Fixed special case if roles are not set.

= 1.09 =
* replace $woocommerce->add_inline_js with wc_enqueue_js

= 1.08 =
* rawurlencode the reset password url email address so it is not stripped in case of a redirect.

= 1.07 =
* Changed our WooCommerce version support - you can read all about it here https://helpcx.zendesk.com/hc/en-us/articles/202241041/
* Fixed set password link not working in some versions of WC2.2 and above.

= 1.06 =
* Fixed set password link not working in some versions of WC.

= 1.05 =
* Added header and footer to email.
* Added filter to email title.
* Changed email From details to WooCommerce email settings.
* Regenerated Language files.
* Prepopulate billing and shipping first and last name and billing email address after creating a user.
* Fixed the Create Your Password title relabeling all titles on the page.

= 1.04 =
* Fixed compatibility issue with WC2.1 to use the new endpoints instead of page ids.

= 1.03 =
* Added compatibility with WooCommerce version 2.1.
* Updated UpdateChecker class.
* Various small bug fixes.

= 1.02 =
* Added en_US.mo and en_US.mo files to use for language conversion.
* Updated language support for previously disabled text areas.
* Fixed bug notice when applying filters to email message.

= 1.01 =
* Added ability to choose the role of the new user.
* Updated so nickname and displayname is dynamically set using a combo of firstname lastname and if they are left blank will use the first part of their email up to the @.

= 1.00 =
* Initial release.
