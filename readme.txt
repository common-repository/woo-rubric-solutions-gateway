=== WooCommerce RS Payment Gateway ===
Contributors: financeplatform4u
Tags: woocommerce, payment gateway, credit card, ecommerce, e-commerce, cart, checkout
Requires at least: 4.2
Tested up to: 5.0.3
Stable tag: 1.0.10
Requires PHP: 5.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A WooCommerce Payment Gateway for taking credit card payments via the RS platform.

== Description ==

The WooCommerce RS Payment Gateway plugin enables you to use the RS payment gateway with the popular WordPress ecommerce platform WooCommerce.

This plugin connects via API to the RS Payment Gateway where the customer's payment data is securely transmitted and processed.

== Installation ==

= Minimum Requirements =

* WooCommerce 3.0.0 or later

= Automatic Installation =

1. Navigate to "Plugins" in the WordPress Dashboard and click on "Install New".
2. Search for "RS Payment Gateway", then click "Install", which completed, click "Activate".
3. Follow the instructions below to configure your initial settings.

= Manual Installation =

1. Upload `woocommerce-rubric-gateway.zip` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Follow the instructions that follow to configure you intital settings.

= Initial Settings =

1. Navigate to WooCommerce > Settings from the left-hand side of your WordPress dashboard menu. Click the "Payments" tab from the top menu.
2. Once you have navigated to "Payments", locate the "RS" link and click there to navigate to the settings page.
3. Enable the gateway by checking the "Enable" checkbox.
4. Enter your Merchant ID and Merchant Password in the appropriate boxes and Save changes.
5. You are now ready to accept payments with the RS gateway.

== Changelog ==
= 1.0.10 =
* Verify compatiblity with WooCommerce 3.5.4 and WordPress 5.0.3.
* Increase gateway timeout limit.
= 1.0.9 =
* Allow international orders.
* Add country fields to api payment calls.
= 1.0.8 =
* Verify compatiblity with WooCommerce 3.5.3 and WordPress 5.0.2.
= 1.0.7 =
* Verify compatiblity with WooCommerce 3.5.0.
= 1.0.6 =
* Allow 2-digit year for card expiration date.
* Verify compatiblity with the WooCommerce 3.4.6.
= 1.0.5 =
* Update the settings configuration instructions for latest version of WooCommerce.
* Verify compatiblity with the lastest WooCommerce version.
= 1.0.4 =
* Send shipping information, if applicable, with payment request.
= 1.0.3 =
* Update API settings to new api version.
= 1.0.2 =
* Update API data fields to new api version.
= 1.0.1 =
* Show specific errors from payment gateway if payment fails.
