<?php
/*
Plugin Name: Pricemesh - Price Comparison | Preisvergleich
Plugin URI: https://www.pricemesh.io/plugins/wordpress/
Description: Extend WordPress with your own price comparison | WordPress um einen eigenen Preisvergleich erweitern.
Version: 1.6.5
Author: pricemesh
Author URI: https://www.pricemesh.io
*/

// If this file is called directly, abort.
if (!defined('WPINC')){
	die;
}

/*----------------------------------------------------------------------------*
 * Baseclass
 *----------------------------------------------------------------------------*/
require_once(plugin_dir_path(__FILE__).'pricemesh-base.php');

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/
require_once(plugin_dir_path(__FILE__).'public/pricemesh-public.php');

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook(__FILE__, array('PricemeshPublic', 'activate'));
register_deactivation_hook(__FILE__, array('PricemeshPublic', 'deactivate'));

/*
 */
add_action('plugins_loaded', array('PricemeshPublic', 'get_instance'));
add_action( 'widgets_init', create_function('', 'return register_widget("PricemeshWidget");') );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*
 
/*
 * The code below is intended to to give the lightest footprint possible.
 */
if(is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX)){

	require_once(plugin_dir_path(__FILE__).'admin/pricemesh-admin.php');
	add_action('plugins_loaded', array( 'PricemeshAdmin', 'get_instance'));
}?>
