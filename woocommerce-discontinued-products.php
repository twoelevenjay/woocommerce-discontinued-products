<?php
/**
 * Plugin Name: WooCommerce Discontinued Products
 * Plugin URI: https://wordpress.org/plugins/discontinued-products/
 * Description: Enables WooCommerce Discontinued Products.
 * Author: Leon @ 211J
 * Author URI: http://211j.com/
 * Version: 2.0.0
 * Text Domain: discontinued-products
 * Domain Path: /languages

 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html

 * @package woocommerce
 */

/**
 * Check if WooCommerce is active.
 *
 * @since 2.0.0
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {

	if ( ! defined( 'DP_URI' ) ) {
		define( 'DP_URI', plugins_url( '', __FILE__ ) );
	}

	if ( ! defined( 'DP_PATH' ) ) {
		define( 'DP_PATH', plugin_dir_path( __FILE__ ) );
	}

	if ( ! defined( 'DP_VER' ) ) {
		define( 'DP_VER', '1.1.7' );
	}

	include DP_PATH . 'includes/class-discontinued-products.php';

	// Finally instantiate our plugin class and add it to the set of globals.
	$GLOBALS['wc_discontinued_products'] = new Discontinued_Products();
}
