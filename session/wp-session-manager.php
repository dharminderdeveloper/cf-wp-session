<?php
if (!defined('WP_SESSION_COOKIE')) {
	define('WP_SESSION_COOKIE', '_wp_session');
}
if (!class_exists('WP_Session') && !class_exists('Recursive_ArrayAccess')) {
	require_once('class-wp-session.php');
}
?>