<?php
/**
 * Discontinued_Products Class.
 *
 * @package woocommerce
 * @since 1.1.7
 */

if ( ! class_exists( 'Discontinued_Products' ) ) {

	/**
	 * The main WooCommerce Discontinued Products Class.
	 * Check if WooCommerce is active, load functions and other files.
	 *
	 * @since 1.0.0
	 */
	class Discontinued_Products {

		/**
		 * Inititiate the Discontinued_Products Class.
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

			include DP_PATH . 'includes/class-dp-csv-import-export.php';
			include DP_PATH . 'includes/class-dp-discontinued-product.php';
			include DP_PATH . 'includes/class-dp-plugins-page.php';
			include DP_PATH . 'includes/class-dp-settings.php';
			include DP_PATH . 'includes/class-dp-shortcode-discontinued.php';
			include DP_PATH . 'includes/class-dp-taxonomy.php';
		}

		/**
		 * Take care of anything that needs all plugins to be loaded
		 */
		public function plugins_loaded() {

			/**
			 * Localisation
			 */
			load_plugin_textdomain( 'discontinued-products', false, dirname( plugin_basename( __FILE__ ) ) . '/' );
		}

		/**
		 * Override any of the template functions from woocommerce/woocommerce-template.php
		 * with our own template functions file
		 */
		public function include_template_functions() {

			include DP_PATH . 'woocommerce-template.php';
		}
	}
}
