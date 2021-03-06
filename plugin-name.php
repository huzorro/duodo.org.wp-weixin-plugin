<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 *
 * @wordpress-plugin
 * Plugin Name:       duodo.org.wap-weixin-plugin
 * Plugin URI:        @TODO
 * Description:       @TODO
 * Version:           1.0.0
 * Author:            @TODO
 * Author URI:        @TODO
 * Text Domain:       plugin-name-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/huzorro/duodo.org.wp-weixin-plugin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-plugin-name.php` with the name of the plugin's class file
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-plugin-name.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 * @TODO:
 *
 * - replace Plugin_Name with the name of the class defined in
 *   `class-plugin-name.php`
 */
register_activation_hook( __FILE__, array( 'Plugin_Name', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Plugin_Name', 'deactivate' ) );

/*
 * @TODO:
 *
 * - replace Plugin_Name with the name of the class defined in
 *   `class-plugin-name.php`
 */
add_action( 'plugins_loaded', array( 'Plugin_Name', 'get_instance' ) );


/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-plugin-admin.php` with the name of the plugin's admin file
 * - replace Plugin_Name_Admin with the name of the class defined in
 *   `class-plugin-name-admin.php`
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-general-settings-admin.php' );
    require_once( plugin_dir_path(__FILE__) . 'admin/class-wechat-custom-replys-main.php');
    require_once( plugin_dir_path(__FILE__) . 'admin/class-wechat-latest-news-main.php');
    require_once( plugin_dir_path(__FILE__) . 'admin/class-wechat-reply-content-main.php');
    require_once( plugin_dir_path(__FILE__) . 'admin/class-wechat-users-main.php');
    require_once( plugin_dir_path(__FILE__) . 'admin/class-wechat-msg-mutual-main.php');

	add_action( 'plugins_loaded', array( 'General_settings_admin', 'get_instance' ) );
    add_action('plugins_loaded', array('Wechat_custom_replys_main', 'get_instance'));
    add_action('plugins_loaded', array('Wechat_latest_news_main', 'get_instance'));
    add_action('plugins_loaded', array('Wechat_reply_content_main', 'get_instance'));
    add_action('plugins_loaded', array('Wechat_users_main', 'get_instance'));
    add_action('plugins_loaded', array('Wechat_msg_mutual_main', 'get_instance'));


}
