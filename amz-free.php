<?php
/*
Plugin Name: Amazon Affiliate Reviews
Description: This is the Amazon Affiliate Reviews Plugin - A plugin that adds an Amazon product to the Reviews section or WooCommerce  Products section, with the Pro version it can also create 3 or 5 column comparason charts and updates a products price to reflect as on Amazon.
Author: Xeniasites
Version: 1.0.1
*/
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
	!defined('AMZPROPLUGIN') or die('Deactivate "Amazon Affiliate Reviews Pro" Plugin and try again');

	// Hook for adding admin menus
	add_action('admin_menu', 'amz_setup_menu');
	
	// action function for above hook
	function amz_setup_menu() {
		// Add a new top-level menu (ill-advised):
		add_menu_page('Amazon Affiliate Reviews', 'Amazon Affiliate Reviews', 'manage_options', 'amz-info', '', '', 7 );
		// Add a submenu to the custom top-level menu:
		add_submenu_page('amz-info', 'Amazon Affiliate Reviews', 'Information', 'manage_options', 'amz-info', 'amz_info');
		// Add a submenu to the custom top-level menu:
if(post_type_exists( 'reviews' )) add_submenu_page('amz-info', 'Add Amazon Product to Reviews', 'Reviews', 'manage_options', 'add-review', 'amz_addreview');
		// Add a submenu to the custom top-level menu:
if(post_type_exists( 'product' )) add_submenu_page('amz-info', 'Add Amazon Product to WooCommerce', 'WooCommerce', 'manage_options', 'add-woocommerce', 'amz_addwoocommerce');
	}
	
	
	// mt_sublevel_page1() displays the page content for the first submenu
	// of the custom Test Toplevel menu
	function amz_info() {
		echo "<h2>Amazon Affiliate Reviews Information Page</h2>";
		include("aminfo.php");
	}

	// mt_sublevel_page1() displays the page content for the first submenu
	// of the custom Test Toplevel menu
	function amz_addreview() {
		echo "<h2>Add Amazon Product to Reviews</h2>";
		include("am2reviews.php");
	}
	// mt_sublevel_page1() displays the page content for the first submenu
	// of the custom Test Toplevel menu
	function amz_addwoocommerce() {
		echo "<h2>Add Amazon Product to WooCommerce</h2>";
		include("am2woo.php");
	}
	
	define('AMZPLUGIN', true);
	
	require_once('includes.php');
?>