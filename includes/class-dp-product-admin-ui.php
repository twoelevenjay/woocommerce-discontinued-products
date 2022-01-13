<?php
/**
 * WooCommerce Discontinued Product Admin UI Class
 *
 * Add a filter in the admin product list view to show/hide discontinued products.
 *
 * @package woocommerce
 * @since 1.2.0
 */

if ( ! class_exists( 'DP_Product_Admin_UI' ) ) {
	/**
	 * DP_Product_Admin_UI Class
	 *
	 * @since 1.2.0
	 */
	class DP_Product_Admin_UI {

		/**
		 * Initiate DP_Product_Admin_UI.
		 *
		 * @since 1.2.0
		 */
		public function __construct() {

			add_action( 'restrict_manage_posts', array( $this, 'print_filter_products_visibility_by_discontinued_status' ) );
			add_filter( 'request', array( $this, 'filter_orders_by_discontinued_status_query' ) );
		}

		/**
		 * Add select option on orders screen for selecting domestic/international.
		 *
		 * @hooked restrict_manage_posts
		 *
		 * @since 1.2.0
		 */
		public function print_filter_products_visibility_by_discontinued_status(): void {
			global $typenow;

			if ( 'product' !== $typenow ) {
				return;
			}

			$filter_options = array(
				'include_discontinued' => __( 'Show All Products', 'discontinued-products' ),
				'only_discontinued'    => __( 'Show Only Discontinued Products', 'discontinued-products' ),
				'hide_discontinued'    => __( 'Hide Discontinued Products', 'discontinued-products' ),
			);

			if( isset( $_GET['_product_discontinued_visibility'] ) && ! empty( $_GET['_product_discontinued_visibility'] ) ) {
				$selected = sanitize_key( $_GET['_product_discontinued_visibility'] );
			} else {
				$selected = get_option( 'dp_admin_list_ui_default', 'include_discontinued' );
			}

			?>
			<select name="_product_discontinued_visibility" id="dropdown_product_discontinued_visibility">
				<?php foreach ( $filter_options as $id => $friendly_name ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php echo esc_attr( selected( $id, $selected, true ) ); ?>>
						<?php echo esc_html( $friendly_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php

		}

		/**
		 * Add the filter to the products query.
		 *
		 * @hooked request
		 *
		 * @see WP::parse_request()
		 * @see https://applerinquest.com/how-to-filter-posts-by-custom-taxonomies-in-wordpress-admin-area/
		 *
		 * @since 1.2.0
		 *
		 * @param array $vars query vars without filtering
		 * @return array $vars query vars with (maybe) filtering
		 */
		public function filter_orders_by_discontinued_status_query( array $vars ): array {
			global $typenow;

            if ( 'product' !== $typenow ) {
                return $vars;
            }

            if( isset( $_GET['_product_discontinued_visibility'] ) && ! empty( $_GET['_product_discontinued_visibility'] ) ) {
	            $selected = sanitize_key( $_GET['_product_discontinued_visibility'] );
            } else {
	            $selected = get_option( 'dp_admin_list_ui_default', 'include_discontinued' );
            }

            $dp_discontinued_term = (int) get_option( 'dp_discontinued_term' );

            switch( $selected ) {

                case 'hide_discontinued':
                    $vars['tax_query'][] = array(
                        'taxonomy' => 'product_discontinued',
                        'field'    => 'id',
                        'terms'    => $dp_discontinued_term,
                        'operator' => 'NOT IN',
                    );
                    break;
                case 'only_discontinued':
                    $vars['tax_query'][] = array(
                        'taxonomy' => 'product_discontinued',
                        'field'    => 'id',
                        'terms'    => $dp_discontinued_term,
                        'operator' => 'IN',
                    );
                    break;
                default:
                    // include_discontinued.
                    break;
            }

			return $vars;
		}

	}

	$dp_product_admin_ui = new DP_Product_Admin_UI();
}
