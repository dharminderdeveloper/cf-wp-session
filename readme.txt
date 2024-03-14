=== CF WP Session ===
Contributors: Dharminder Singh
Donate link: #
Tags: session
Requires at least: 3.4.2
Tested up to: 4.7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enhanced session management for WordPress, inspired by WP Session Manager.

== Description ==

CF WP Session provides advanced session management capabilities for WordPress, drawing inspiration from the WP Session Manager plugin. It adds `$_SESSION`-like functionality to WordPress, allowing every visitor, whether logged in or not, to be issued a unique instance of `WP_Session`. These sessions are identified by an ID stored in the `_wp_session` cookie. By default, session data is stored in a WordPress transient, with the option for alternative storage solutions like memory caching systems (e.g., memcached) if available.

This plugin empowers plugin and theme developers to utilize WordPress-managed session variables without relying on the standard PHP `$_SESSION` superglobal.

== Installation ==

= Manual Installation =

1. Upload the entire `/cf-wp-session` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Utilize `WP_Session::get_instance()` in your code to access session variables.

== Frequently Asked Questions ==

= How do I add session variables? =

To add session variables, reference the `WP_Session` instance and use it like an associative array similar to `$_SESSION`:

$wp_session = WP_Session::get_instance();
$wp_session['user_name'] = 'User Name';                            // A string
$wp_session['user_contact'] = array( 'email' => 'user@name.com' ); // An array
$wp_session['user_obj'] = new WP_User( 1 );                        // An object

== Changelog ==

= 1.0.0 =
* Update: Releasing the first version of the plugin