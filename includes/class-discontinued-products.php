<?php
/**
 * Discontinued_Products Class.
 *
 * @package woocommerce
 * @since 1.0.0
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
		 * Background process to update data.
		 *
		 * @var DP_Data_Background_Process
		 */
		protected static $background_process;

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
			include DP_PATH . 'includes/class-dp-settings.php';
			include DP_PATH . 'includes/class-dp-shortcode-discontinued.php';
			include DP_PATH . 'includes/class-dp-taxonomy.php';
			include_once DP_PATH . 'includes/class-dp-data-background-process.php';
			self::$background_process = new DP_Data_Background_Process();
			add_action( 'admin_notices', array( $this, 'update_notice' ) );
			$this->maybe_run_data_updater();
		}

		/**
		 * Take care of anything that needs woocommerce to be loaded.
		 * For instance, if you need access to the $woocommerce global
		 */
		public function maybe_run_data_updater() {
			if ( isset( $_GET['do_update_dp'] ) && check_admin_referer( 'dp_update_nonce', '_wpnonce' ) ) {
				// Run the data updater.
				self::$background_process->start();
				set_transient( 'do_update_dp', 'updating', 0 );
				return;
			}
			if ( isset( $_GET['wc-hide-notice'] ) && 'dp_updated' === $_GET['wc-hide-notice'] ) {
				delete_transient( 'do_update_dp' );
				return;
			}
			if ( 'updating' === get_transient( 'do_update_dp' ) && ! self::has_legacy_meta() ) {
				set_transient( 'do_update_dp', 'updated', 0 );
			}
		}

		/**
		 * If we need to update the data, include a message with the data update button.
		 */
		public static function update_notice() {

			$update_dp_transient = get_transient( 'do_update_dp' );
			// Check if any products have legacy meta keys.
			$has_legacy_meta = self::has_legacy_meta();

			if ( $has_legacy_meta ) {

				if ( 'updating' === $update_dp_transient ) {
					include DP_PATH . 'views/html-notice-updating.php';
				} else {
					include DP_PATH . 'views/html-notice-update.php';
				}
			} elseif ( 'updated' === $update_dp_transient ) {
				include DP_PATH . 'views/html-notice-updated.php';
			}
		}

		/**
		 * CHeck if legacy meta keys exist.
		 */
		public static function has_legacy_meta() {
			global $wpdb;

			// Check if any products have legacy meta keys.
			$has_legacy_meta = $wpdb->get_var(
				"
					SELECT 1 
					FROM {$wpdb->postmeta} pm
					JOIN {$wpdb->posts} p ON pm.post_id = p.ID
					WHERE p.post_type = 'product'
					AND pm.meta_key IN ('_hide_from_search', '_hide_from_shop', '_is_discontinued')
					LIMIT 1
				"
			);

			return $has_legacy_meta;
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
