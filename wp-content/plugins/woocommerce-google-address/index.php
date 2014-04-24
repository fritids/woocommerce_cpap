<?php

/**
 * Plugin Name: Google Address Autocomplete for Woocommerce
 * Description: Helps the user to select a valid address based on Google Place search
 * Version: 1.0.4
 * Author: MB Création
 * Author URI: http://www.mbcreation.net
 * License: http://codecanyon.net/licenses/regular_extended
 *
 */

// Required Classes

require_once('class.front.php');

// Loader
function WooCommerce_Google_Address_Loader(){

	if(class_exists('Woocommerce')) {
		
		load_plugin_textdomain('woogoogad', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');

		$GLOBALS['WooCommerce_Google_Address_Plugin_Front'] = new WooCommerce_Google_Address_Plugin_Front();
	
	}
	
} //WooCommerce_Google_Address_Loader

add_action( 'plugins_loaded' , 'WooCommerce_Google_Address_Loader');