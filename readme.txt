=== Nusagate ===
Contributors: slmnabd
Tags: woocommerce, nusagate, payment, payment gateway, commerce
Requires at least: 4.7
Tested up to: 6.0
Stable tag: 1.0.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Nusagate for WooCommerce, easy crypto-Fiat Payment Gateway

== Description ==

Nusagate-WooCommerce is official plugin from [Nusagate](https://nusagate.com).

Nusagate-WooCommerce is a plugin payment gateway for WooCommerce, which can allow secure online payment using cryptocurrency to fiat for your WooCommerce store. This enables you to accept various cryptocurrency payments via Nusagate with just a few clicks.

* accept many popular cryptocurrencies

Visit https://nusagate.com for more information.

How to use:

* Register to our [Dashboard](https://dashboard.nusagate.com/).
* Go to Settings > Developers > API Keys to generate API key and secret key.
* Go to Settings > Developers > Callbacks to generate callback token and fill field with :
    - redirect success: [your-site-url]
    - redirect failed: [your-site-url]
    - callback url: [your-site-url]/wp-json/nusagate/v1/complete-payment or [your-site-url]/?rest_route=/nusagate/v1/complete-payment
* Go to WP Admin and Activate Nusagate WooCommerce.
* Enable from WooCommerce > Settings > Payments > Nusagate WooCommerce. 
* Enter API key, secret key, callback token from Manage.
* It will automatically appear to customer on checkout.

Contact: developer@nusagate.com

== Screenshots == 

1. step1.png
2. step2.png

== Frequently Asked Questions ==

= Where can I report bugs and request feature? =

the best way is send us email to developer@nusagate.com. You can also create an issue in our [repo](https://github.com/nusagate/nusagate-woocommerce/issues) or you can also use WordPress plugin support to report bugs and errors.


== Changelog ==

= 1.0.1 =
* Initial release.
