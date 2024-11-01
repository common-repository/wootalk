<?php
/**
 * Plugin Name: WooTalk 
 * Plugin URI: https://www.wpproduct.in/product/wootalk-chat-plugins/
 * Description: An WooTalk is a woocommerce chatting system with the admin and customer.
 * Version: 1.0.1
 * WC requires at least: 3.0.0
 * WC tested up to: 3.7.0
 * Author: wpproduct team
 * Author URI: https://www.wpproduct.in
 * Text Domain: wootalk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
// Define WT_PLUGIN_FILE.
if ( ! defined( 'WT_PLUGIN_FILE' ) ) {
	define( 'WT_PLUGIN_FILE', __FILE__ );
}
// Include the main WooTalk class.
if ( ! class_exists( 'wootalk' ) ) {
	include_once dirname( __FILE__ ) . '/inc/wootalk.php';
}
/**
 * Main instance of WooTalk.
 */
function wootalk() {
	return wootalk::instance();
}
$GLOBALS['wootalk'] = wootalk();

add_filter( 'plugin_action_links', 'ttt_wpmdr_add_action_plugin', 10, 5 );
function ttt_wpmdr_add_action_plugin( $actions, $plugin_file ) 
{
	static $plugin;
	if (!isset($plugin))
	$plugin = plugin_basename(__FILE__);
	if ($plugin == $plugin_file) {
		$site_link = array('updgrate' => '<a class="wootalk_updgrate" href="https://wpproduct.in" target="_blank">Updgrate To Pro</a>');
		$settings = array('settings' => '<a href="admin.php?page=wootalk">' . __('Settings', 'General') . '</a>');
		$actions = array_merge($site_link, $actions);
		$actions = array_merge($settings, $actions);
		
	}
	return $actions;
}
