<?php
/**
 * @package Viddyoze
 */
/*
Plugin Name: Viddyoze
Plugin URI: https://viddyoze.com/
Description: Create Client-Grabbing Videos In Just 3 Clicks With The World’s Most Powerful Video Animation Platform
Version: 1.0.9
Author: Viddyoze
Author URI: https://viddyoze.com/
License: GPLv2 or later
Text Domain: Viddyoze
*/

/**
 * @package Viddyoze - Viddyoze
 * @version 1.0.9
 *
**/


// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if(!defined('ABSPATH')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////// Define the base path of plugins  ////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////

define( 'VIDDYOZE_TOKEN', '' );

// Plugin version
define( 'VIDDYOZE_VERSION', '1.0.9' );
// Text Domain
define( 'VIDDYOZE_TEXT_DOMAIN', 'VIDDYOZE' );
// Plugin Root File
define( 'VIDDYOZE_PLUGIN_FILE', __FILE__ );
// Plugin Folder Path
define( 'VIDDYOZE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
// Plugin Folder URL
define( 'VIDDYOZE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
// Plugin Addons Folder Path
define( 'VIDDYOZE_ADMIN_DIR', plugin_dir_path( __FILE__ ) . 'admin/' );
// Plugin Folder URL
define( 'VIDDYOZE_ADMIN_URL', plugin_dir_url( __FILE__ ) . 'admin/' );
// Plugin Folder URL
define( 'VIDDYOZE_INCLUDES_DIR', plugin_dir_path( __FILE__ ) . 'includes/' );
// URL Login Status
define( 'VIDDYOZE_URL_LOGIN_STATUS', '/user/login_status?_format=json' );
// URL Login
define( 'VIDDYOZE_URL_LOGIN', '/user/login?_format=json' );

require_once( VIDDYOZE_INCLUDES_DIR . 'class.viddyoze.php' );

// init the plugin
add_action('init', array('Viddyoze','viddyozeInit'));

add_action( 'admin_menu', array( 'Viddyoze', 'viddyozeOptionsPage' ) );
if(get_option('viddyoze_api_key')) {
    add_action('admin_menu', array( 'Viddyoze', 'viddyozeTemplatesPage' ) );
    add_action('admin_menu', array( 'Viddyoze', 'viddyozeTemplateSingle' ) );
    add_action('admin_menu', array( 'Viddyoze', 'viddyozeVideosPage' ) );
    add_action('admin_menu', array( 'Viddyoze', 'viddyozeSupportPage' ) );
}

/**
 * Welcome screen
 */
function viddyoze_welcome_screen_activate() {
    set_transient( '_viddyoze_welcome_screen_activation_redirect', true, 30 );
}
register_activation_hook( __FILE__, 'viddyoze_welcome_screen_activate' );