<?php
/*
	Plugin Name: CF WP Session
	Plugin URI: #
	Description: CF WP Session facilitates session management and seamless access to sessions within WordPress. Inspired by the WP Session Manager Plugin, credit for certain functionalities and code snippets goes to the original plugin creators.
	Version: 1.0.0
	Tested up to: 4.7.2
	Author: Dharminder Singh
	Author URI: #
	Text Domain: cf
	Domain Path: /lang/
	Network: true
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
if (!defined('ABSPATH')) {
	exit;
}
/*-------------------------------------------------*/
/* INCLUDE SESSION CLASS INSPIRE FROM CODEIGNITER */
/*-----------------------------------------------*/
/* WHY USE: DEFAULT PHP SESSION NOT WORK IN WP  */
/*---------------------------------------------*/

// Include utilities class
// Include WP_CLI routines early
// Only include the functionality if it's not pre-defined.
if (!defined('WP_SESSION_COOKIE')) {
	define('WP_SESSION_COOKIE', '_wp_session');
}
if (!class_exists('Recursive_ArrayAccess')) {
	include 'includes/class-recursive-arrayaccess.php';
}
if (!class_exists('WP_Session_Utils')) {
	include 'includes/class-wp-session-utils.php';
}
if (defined('WP_CLI') && WP_CLI) {
	include 'includes/wp-cli.php';
}
if (!class_exists('WP_Session')) {
	include 'includes/class-wp-session.php';
	include 'includes/wp-session.php';
}
class CF_Session
{
	private static $instance = null;
	private $plugin_path;
	private $plugin_url;
	private $text_domain = 'cf';
	/**
	 * Creates or returns an instance of this class.
	 */
	public static function get_instance()
	{
		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if (null == self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	/**
	 * Initializes the plugin by defining the properties.
	 *
	 * @since 0.6.0
	 */
	function init()
	{
		$class = __CLASS__;
		new $class;
	}
	function __construct()
	{
		$this->plugin_path = plugin_dir_path(__FILE__);
		$this->plugin_url = plugin_dir_url(__FILE__);
		load_plugin_textdomain($this->text_domain, false, $this->plugin_path . '\lang');
		$this->run_plugin();
	}
	public function get_plugin_url()
	{
		return $this->plugin_url;
	}
	public function get_plugin_path()
	{
		return $this->plugin_path;
	}
	/**
	 * Place code that runs at plugin activation here.
	 */
	public function activation()
	{
	}
	/**
	 * Place code that runs at plugin deactivation here.
	 */
	public function deactivation()
	{
	}
	/**
	 * Enqueue and register JavaScript files here.
	 */
	public function register_scripts()
	{
		wp_register_script('cfsa', $this->plugin_url . 'sa.min.js', array('jquery'), '0.7.0', false);
		wp_enqueue_script('cfsa');
	}
	/**
	 * Enqueue and register CSS files here.
	 */
	public function register_styles()
	{
		wp_register_style('cfsa', $this->plugin_url . 'sa.min.css', false, '0.7.0');
		wp_enqueue_style('cfsa');
	}
	private function run_plugin()
	{
	}
}

CF_Session::get_instance();