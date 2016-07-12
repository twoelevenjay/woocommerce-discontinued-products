<?php
/**
 * Plugin Name: WooCommerce Discontinued Products
 * Plugin URI: https://wordpress.org/plugins/discontinued-products/
 * Description: Enables WooCommerce Discontinued Products.
 * Author: Leon @ 211J
 * Author URI: http://211j.com/
 * Version: 1.1.0
 * Text Domain: woocommerce-discontinued-products
 * Domain Path: /languages

 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html

 * @package woocommerce
 */

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {

	if ( ! class_exists( 'WC_Discontinued_Products' ) ) {

		if ( ! defined( 'WC_DP_URI' ) ) {
			define( 'WC_DP_URI', plugins_url( '', __FILE__ ) );
		}

		if ( ! defined( 'WC_DP_PATH' ) ) {
			define( 'WC_DP_PATH', plugin_dir_path( __FILE__ ) );
		}

		/**
		 * The main WooCommerce Discontinued Products Class.
		 * Check if WooCommerce is active, load functions and other files.
		 *
		 * @since 1.0.0
		 */
		class WC_Discontinued_Products {

	 		/**
	 		 * Inititiate the WC_Discontinued_Products Class.
	 		 *
	 		 * @since 1.0.0
	 		 */
			public function __construct() {
				// Called only after woocommerce has finished loading.
				add_action( 'woocommerce_init', array( $this, 'woocommerce_loaded' ) );
				// Called after all plugins have loaded.
				add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

				// Called just before the woocommerce template functions are included.
				add_action( 'init', array( $this, 'include_template_functions' ), 20 );

				// Indicates we are running the admin.
				if ( is_admin() ) {
					is_admin();
				}

				// Indicates we are being served over ssl.
				if ( is_ssl() ) {
					is_ssl();
				}

				// Take care of anything else that needs to be done immediately upon plugin instantiation, here in the constructor.
			}

			/**
			 * Take care of anything that needs woocommerce to be loaded.
			 * For instance, if you need access to the $woocommerce global
			 */
			public function woocommerce_loaded() {

				include( 'includes/wc-class-dp-discontinued-product.php' );
				include( 'includes/wc-class-dp-settings.php' );
			}

			/**
			 * Take care of anything that needs all plugins to be loaded
			 */
			public function plugins_loaded() {

				/**
				 * Localisation
				 */
				load_plugin_textdomain( 'woocommerce-discontinued-products', false, dirname( plugin_basename( __FILE__ ) ) . '/' );
			}

			/**
			 * Override any of the template functions from woocommerce/woocommerce-template.php
			 * with our own template functions file
			 */
			public function include_template_functions() {

				include( 'woocommerce-template.php' );
			}
		}

		// Finally instantiate our plugin class and add it to the set of globals.
		$GLOBALS['wc_discontinued_products'] = new WC_Discontinued_Products();
	}
}
