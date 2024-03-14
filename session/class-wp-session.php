<?php
class Recursive_ArrayAccess implements ArrayAccess
{
	protected $container = array();
	protected $dirty = false;
	protected function __construct($data = array())
	{
		foreach ($data as $key => $value) {
			$this[$key] = $value;
		}
	}
	public function __clone()
	{
		foreach ($this->container as $key => $value) {
			if ($value instanceof self) {
				$this[$key] = clone $value;
			}
		}
	}
	public function toArray()
	{
		$data = $this->container;
		foreach ($data as $key => $value) {
			if ($value instanceof self) {
				$data[$key] = $value->toArray();
			}
		}
		return $data;
	}
	public function offsetExists($offset)
	{
		return isset($this->container[$offset]);
	}
	public function offsetGet($offset)
	{
		return isset($this->container[$offset]) ? $this->container[$offset] : null;
	}
	public function offsetSet($offset, $data)
	{
		if (is_array($data)) {
			$data = new self($data);
		}
		if ($offset === null) {
			$this->container[] = $data;
		} else {
			$this->container[$offset] = $data;
		}
		$this->dirty = true;
	}
	public function offsetUnset($offset)
	{
		unset($this->container[$offset]);
		$this->dirty = true;
	}
}
final class WP_Session extends Recursive_ArrayAccess implements Iterator, Countable
{
	protected $session_id;
	protected $expires;
	protected $exp_variant;
	private static $instance = false;
	public static function get_instance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	protected function __construct()
	{
		if (isset($_COOKIE[WP_SESSION_COOKIE])) {
			$cookie = stripslashes($_COOKIE[WP_SESSION_COOKIE]);
			$cookie_crumbs = explode('||', $cookie);
			$this->session_id = $cookie_crumbs[0];
			$this->expires = $cookie_crumbs[1];
			$this->exp_variant = $cookie_crumbs[2];
			if (time() > $this->exp_variant) {
				$this->set_expiration();
				delete_option("_wp_session_expires_{$this->session_id}");
				add_option("_wp_session_expires_{$this->session_id}", $this->expires, '', 'no');
			}
		} else {
			$this->session_id = $this->generate_id();
			$this->set_expiration();
		}
		$this->read_data();
		$this->set_cookie();
	}
	protected function set_expiration()
	{
		$this->exp_variant = time() + (int) apply_filters('wp_session_expiration_variant', 24 * 60);
		$this->expires = time() + (int) apply_filters('wp_session_expiration', 30 * 60);
	}
	protected function set_cookie()
	{
		setcookie(WP_SESSION_COOKIE, $this->session_id . '||' . $this->expires . '||' . $this->exp_variant, $this->expires, COOKIEPATH, COOKIE_DOMAIN);
	}
	protected function generate_id()
	{
		require_once(ABSPATH . 'wp-includes/class-phpass.php');
		$hasher = new PasswordHash(8, false);
		return md5($hasher->get_random_bytes(32));
	}
	protected function read_data()
	{
		$this->container = get_option("_wp_session_{$this->session_id}", array());
		return $this->container;
	}
	public function write_data()
	{
		$option_key = "_wp_session_{$this->session_id}";
		if ($this->dirty) {
			if (false === get_option($option_key)) {
				add_option("_wp_session_{$this->session_id}", $this->container, '', 'no');
				add_option("_wp_session_expires_{$this->session_id}", $this->expires, '', 'no');
			} else {
				delete_option("_wp_session_{$this->session_id}");
				add_option("_wp_session_{$this->session_id}", $this->container, '', 'no');
			}
		}
	}
	public function json_out()
	{
		return json_encode($this->container);
	}
	public function json_in($data)
	{
		$array = json_decode($data);
		if (is_array($array)) {
			$this->container = $array;
			return true;
		}
		return false;
	}
	public function regenerate_id($delete_old = false)
	{
		if ($delete_old) {
			delete_option("_wp_session_{$this->session_id}");
		}
		$this->session_id = $this->generate_id();
		$this->set_cookie();
	}
	public function session_started()
	{
		return !!self::$instance;
	}
	public function cache_expiration()
	{
		return $this->expires;
	}
	public function reset()
	{
		$this->container = array();
	}
	public function current()
	{
		return current($this->container);
	}
	public function key()
	{
		return key($this->container);
	}
	public function next()
	{
		next($this->container);
	}
	public function rewind()
	{
		reset($this->container);
	}
	public function valid()
	{
		return $this->offsetExists($this->key());
	}
	public function count()
	{
		return count($this->container);
	}
}
function wp_session_cache_expire()
{
	$wp_session = WP_Session::get_instance();
	return $wp_session->cache_expiration();
}
function wp_session_commit()
{
	wp_session_write_close();
}
function wp_session_decode($data)
{
	$wp_session = WP_Session::get_instance();
	return $wp_session->json_in($data);
}
function wp_session_encode()
{
	$wp_session = WP_Session::get_instance();
	return $wp_session->json_out();
}
function wp_session_regenerate_id($delete_old_session = false)
{
	$wp_session = WP_Session::get_instance();
	$wp_session->regenerate_id($delete_old_session);
	return true;
}
function wp_session_start()
{
	$wp_session = WP_Session::get_instance();
	do_action('wp_session_start');
	return $wp_session->session_started();
}
add_action('plugins_loaded', 'wp_session_start');
function wp_session_status()
{
	$wp_session = WP_Session::get_instance();
	if ($wp_session->session_started()) {
		return PHP_SESSION_ACTIVE;
	}
	return PHP_SESSION_NONE;
}
function wp_session_unset()
{
	$wp_session = WP_Session::get_instance();
	$wp_session->reset();
}
function wp_session_write_close()
{
	$wp_session = WP_Session::get_instance();
	$wp_session->write_data();
	do_action('wp_session_commit');
}
add_action('shutdown', 'wp_session_write_close');
function wp_session_cleanup()
{
	global $wpdb;
	if (defined('WP_SETUP_CONFIG')) {
		return;
	}
	if (!defined('WP_INSTALLING')) {
		$expiration_keys = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '_wp_session_expires_%'");
		$now = time();
		$expired_sessions = array();
		foreach ($expiration_keys as $expiration) {
			if ($now > intval($expiration->option_value)) {
				$session_id = substr($expiration->option_name, 20);
				$expired_sessions[] = $expiration->option_name;
				$expired_sessions[] = "_wp_session_$session_id";
			}
		}
		if (!empty($expired_sessions)) {
			$option_names = implode("','", $expired_sessions);
			$wpdb->query("DELETE FROM $wpdb->options WHERE option_name IN ('$option_names')");
		}
	}
	do_action('wp_session_cleanup');
}
add_action('wp_session_garbage_collection', 'wp_session_cleanup');
function wp_session_register_garbage_collection()
{
	if (!wp_next_scheduled('wp_session_garbage_collection')) {
		wp_schedule_event(time(), 'hourly', 'wp_session_garbage_collection');
	}
}
add_action('wp', 'wp_session_register_garbage_collection');
?>