<?php
/**
 * Plugin Name: Woo Category Slider
 * Description: A WordPress plugin for displaying WooCommerce categories in a slider.
 * Plugin URI: https://github.com/KiOui/woocommerce-category-slider
 * Version: 1.0.0
 * Author: Lars van Rhijn
 * Author URI: https://larsvanrhijn.nl/
 * Text Domain: woo-category-slider
 * Domain Path: /languages/
 *
 * @package woo-category-slider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WCS_PLUGIN_FILE' ) ) {
	define( 'WCS_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'WCS_PLUGIN_URI' ) ) {
	define( 'WCS_PLUGIN_URI', plugin_dir_url( __FILE__ ) );
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	include_once __DIR__ . '/includes/WcsCore.php';
	$GLOBALS['WcsCore'] = WcsCore::instance();
} else {
	/**
	 * Display an admin notice when WooCommerce is inactive.
	 *
	 * @return void
	 */
	function wcb_admin_notice_woocommerce_inactive(): void {
		if ( is_admin() && current_user_can( 'edit_plugins' ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( __( 'Woo Category Banners requires WooCommerce to be active. Please activate WooCommerce to use Woo Category Banners.' ) ) . '</p></div>';
		}
	}
	add_action( 'admin_notices', 'wcb_admin_notice_woocommerce_inactive' );
}
