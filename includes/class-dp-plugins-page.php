<?php
/**
 * WooCommerce Discontinued Products Plugins Page Class
 *
 * Add a link from plugins.php to the plugin settings.
 *
 * @package woocommerce
 * @since 1.2.0
 */

if ( ! class_exists( 'DP_Plugins_Page' ) ) {
	/**
	 * DP_Plugins_Page Class
	 *
	 * @since 1.2.0
	 */
	class DP_Plugins_Page {

		/**
		 * Initiate the DP_Plugins_Page.
		 *
		 * @since 1.2.0
		 */
		public function __construct() {

			$plugin_basename = basename( DP_PATH ) . '/woocommerce-discontinued-products.php';

			add_filter( "plugin_action_links_{$plugin_basename}", array( $this, 'plugin_action_links' ), 10, 4 );
		}

		/**
		 * Adds 'Settings' link under the plugin name on plugins.php.
		 *
		 * @hooked plugin_action_links_{plugin basename}
		 * @see \WP_Plugins_List_Table::display_rows()
		 *
		 * @param array<int|string, string>  $action_links      The existing plugin links (usually "Deactivate").
		 * @param string                     $_plugin_basename  The plugin's directory/filename.php.
		 * @param array<string, string|bool> $_plugin_data      Associative array including PluginURI, slug, Author, Version. See `get_plugin_data()`.
		 * @param string                     $_context          The plugin context. By default this can include 'all', 'active', 'inactive',
		 *                                                      'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
		 *
		 * @return array<int|string, string> The links to display below the plugin name on plugins.php.
		 */
		public function plugin_action_links( array $action_links, string $_plugin_basename, $_plugin_data, $_context ): array {

			// If WooCommerce is not active, we cannot link to the WooCommerce settings' subsection.
			if ( ! class_exists( WooCommerce::class ) ) {
				return $action_links;
			}

			$settings_url = admin_url( 'admin.php?page=wc-settings&tab=products&section=discontinued_products' );

			array_unshift( $action_links, '<a href="' . $settings_url . '">' . __( 'Settings', 'discontinued-products' ) . '</a>' );

			return $action_links;
		}
	}

	$dp_plugins_page = new DP_Plugins_Page();
}
