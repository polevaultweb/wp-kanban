=== WP Trello ===
Contributors: polevaultweb
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=R6BY3QARRQP2Q
Plugin URI: http://www.polevaultweb.co.uk/plugins/wp-trello/
Author URI: http://www.polevaultweb.com/
Tags: trello, cards, boards, lists, widget, shortcode, api, integration
Requires at least: 3.0
Tested up to: 3.8
Stable tag: 1.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin to display data from Trello in your WordPress site.

== Description ==

This plugin displays organizations, boards, lists and cards from Trello the awesome collaboration tool. Display the data via shortcodes or widgets. Connect with your Trello account securely using oAuth.

You need to specify the ID of the Trello object you want to list the data for. For example, if you want to lists the cards in a certain list you would set the type as **cards** and specify the ID of the list.

Shortcode usage:

Display the lists from the Welcome board:

`[wp-trello type="lists" id="507570c53db6337074ca4c88" link="no"]`

Display the cards from the Welcome board's Intermediate List with links:

`[wp-trello type="cards" id="507570c53db6337074ca4c92" link="yes"]`

Different types - organizations, boards, lists, cards, card.

The plugin comes with a handy API helper to find the IDs of all your Trello data.
	
If you have any issues or feature requests please visit and use the [Support Forum](http://www.polevaultweb.com/support/forum/wp-trello-plugin/)

[Plugin Page](http://www.polevaultweb.com/plugins/wp-trello/) | [@polevaultweb](http://www.twitter.com/polevaultweb/)

== Installation ==

This section describes how to install the plugin and get it working.

You can use the built in installer and upgrader, or you can install the plugin manually.

1. Delete any existing `wp-trello` folder from the `/wp-content/plugins/` directory
2. Upload `wp-trello` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the options page under the 'Settings' menu and set the configuration you want.

If you have to upgrade manually simply repeat the installation steps and re-enable the plugin.

== Changelog ==

= 1.0.6 =

* Improvement - Links in cards now made clickable

= 1.0.5 =

* Fix - Links now don't appear if you pass link="no" to shortcode

= 1.0.4 =

* Fix - Opening PHP tags shorthand removed for compatibility, thanks Paul!

= 1.0.3 =

* Fix - Cannot redeclare class OAuthSignatureMethod_HMAC_SHA1 errors

= 1.0.2 =

* Fix - End of file parse errors

= 1.0.1 =

* Fix - Settings issues

= 1.0 =

* First release, please report any issue.

== Frequently Asked Questions ==

= I have an issue with the plugin =

Please visit the [Support Forum](http://www.polevaultweb.com/support/forum/wp-trello-plugin/) and see what has been raised before, if not raise a new topic.

== Screenshots ==

1. Screenshot of the general settings.
2. Screenshot of the API helper.
3. Screenshot of the widget.
4. Screenshot of the plugin output.

== Disclaimer ==

This plugin uses the Trello API and is not endorsed or certified by Trello. All Trello logoes and trademarks displayed on this website are property of Trello.