=== WC_Limit_Product_View_By_User ===
Contributors: mathildemar,rhaudry
Donate link: none
Plugin URI : https://www.mminformatique.fr/plugins-et-themes-wordpress/wc-limit-product-view-by-user/
Tags: admin, limit, view, woocommerce, list, user, admin panel
Requires at least: 4.7
Tested up to: 5.7.1
Stable tag: 4.3
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to restrict the visibility of a product to a predefined list of users

== Description ==

With this plugin, you will be able to limit the view of a product to certain users.
 Simply in the settings of your WooCommerce products, you just have to check the users you want to authorize


== Frequently Asked Questions ==

= How does it work =

After checking and validating the users, the user IDs are stored in the meta table of wordpress posts, each time a product page is displayed, the plugin checks that the user has the right to see

To configure the authorized users, you must go to the modification page of woocommerce products, then in the correct section, check the desired users.
For the changes to work, just click on the Update button in the Post.

Namely that if no user is checked, the plugin will consider the product as public and will display it for everyone.

If a previously checked user is unchecked, he will be removed from the list at the time of the update.


== Screenshots ==

1. Global view of the config section : assets/Screen_global_view_1.png

== Changelog ==

= 1.0 =
This is the first version

== Upgrade Notice ==

= 1.0 =
No update today
