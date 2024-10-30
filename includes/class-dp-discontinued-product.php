<?php
/**
 * WooCommerce Discontinued Product Class
 *
 * @package woocommerce
 * @since 1.0.0
 */

if ( ! class_exists( 'DP_Discontinued_Product' ) ) {
	/**
	 * DP_Discontinued_Product Class
	 *
	 * @since 1.0.0
	 */
	class DP_Discontinued_Product {

		/**
		 * Get the current page ID before it is set in WP_Query.
		 *
		 * @var int
		 */
		private $current_page_id = 0;

		/**
		 * Inititiate the DP_Discontinued_Product.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			if ( ! is_admin() ) {
				$page                  = isset( $_SERVER['REQUEST_URI'] ) ? get_page_by_path( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : null;
				$page_id               = is_object( $page ) && property_exists( $page, 'ID' ) ? $page->ID : false;
				$this->current_page_id = $page_id ? $page_id : $this->current_page_id;
				add_action( 'init', array( $this, 'woocommerce_flush_rewrite_rules' ) );
				add_action( 'woocommerce_product_query', array( $this, 'exclude_discontinued_products' ), 1000 );
				add_filter( 'woocommerce_get_shop_page_id', array( $this, 'override_shop_page_id' ), 1, 1 );
				add_action( 'template_redirect', array( $this, 'add_dp_alt_products' ) );
			}
			if ( is_admin() ) {
				add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_discontinued_product_tab' ) );
				add_action( 'woocommerce_product_data_panels', array( $this, 'add_discontinued_product_panel' ) );
				add_action( 'woocommerce_process_product_meta', array( $this, 'save' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			}
		}
		/**
		 * Conditionally flush rewrite rules.
		 *
		 * @since 2.0.0
		 */
		public function woocommerce_flush_rewrite_rules() {

			$dc_shop_page_id = (int) get_option( 'dp_shop_page_id' );
			$shop_page_id    = wc_get_page_id( 'shop' );
			if ( $dc_shop_page_id && ( $this->current_page_id === $shop_page_id || $this->current_page_id === $dc_shop_page_id ) ) {
				/**
				 * Fires to flush WooCommerce rewrite rules.
				 *
				 * @since 2.0.0
				 */
				do_action( 'woocommerce_flush_rewrite_rules' );
			}
		}
		/**
		 * Enqueue CSS and JS.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {

			$screen = get_current_screen();
			if ( 'product' === $screen->post_type ) {

				wp_register_style( 'discontinued_product_styles', DP_URI . '/assets/css/discontinued_product.css', array(), DP_VER );
				wp_enqueue_style( 'discontinued_product_styles' );
			}
		}

		/**
		 * Add tab to the Product Data meta box.
		 *
		 * @since 1.0.0
		 */
		public function add_discontinued_product_tab() {

			?>
			<li class="discontinued_product_tab"><a href="#discontinued_product_tab_data"><span><?php esc_html_e( 'Discontinued Products', 'discontinued-products' ); ?></span></a></li>
			<?php
		}

		/**
		 * Add tab to the Product Data meta box.
		 *
		 * @since 1.0.0
		 */
		public function add_discontinued_product_panel() {

			global $post;
			?>
			<div id="discontinued_product_tab_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper">
				<div class="options_group">
					<?php
						woocommerce_wp_checkbox(
							array(
								'id'            => '_is_discontinued',
								'wrapper_class' => '',
								'label'         => __( 'Is Discontinued', 'discontinued-products' ),
								'description'   => __( 'Check if this product is discontinued', 'discontinued-products' ),
								'value'         => has_term( 'dp-discontinued', 'product_discontinued', $post->ID ) ? 'yes' : '',
							)
						);
						$placeholder = get_option( 'dp_discontinued_text' );
						woocommerce_wp_text_input(
							array(
								'id'          => '_discontinued_product_text',
								'label'       => __( 'Display text', 'discontinued-products' ),
								'placeholder' => $placeholder,
								'desc_tip'    => 'true',
								'description' => __( 'Enter text to be shown when this product is discontinued', 'discontinued-products' ),
							)
						);
					?>
				</div>

				<div class="options_group">

					<p class="form-field">
						<label for="alt_products"><?php esc_html_e( 'Alternative Products', 'discontinued-products' ); ?></label>
						<select name="alt_products[]" class="wc-product-search" multiple="multiple" style="width: 50%;" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'discontinued-products' ); ?>" data-action="woocommerce_json_search_products_and_variations">
							<?php
							$product_ids = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, '_alt_products', true ) ) );

							foreach ( $product_ids as $product_id ) {
								$product = wc_get_product( $product_id );
								if ( is_object( $product ) ) {
									echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
								}
							}
							?>
						</select>
						<?php
						// @codingStandardsIgnoreStart
						echo wc_help_tip( __( 'Any product that is added to this field will generate a button for the add to cart area that will link to the corresponding product.', 'discontinued-products' ) );
						// @codingStandardsIgnoreEnd
						?>
					</p>


					<?php
						$placeholder = get_option( 'dp_alt_text' );
						woocommerce_wp_text_input(
							array(
								'id'          => '_alt_product_text',
								'label'       => __( 'Alternative product text', 'discontinued-products' ),
								'placeholder' => $placeholder,
								'desc_tip'    => 'true',
								'description' => __( 'Enter text to be shown when alternative product are suggested.', 'discontinued-products' ),
							)
						);
					?>
				</div>

				<div class="options_group">
				<?php
				$hide_from_shop = '';
				$hide_from_shop = has_term( 'dp-hide-shop', 'product_discontinued', $post->ID ) ? 'hide' : $hide_from_shop;
				$hide_from_shop = has_term( 'dp-show-shop', 'product_discontinued', $post->ID ) ? 'show' : $hide_from_shop;
				woocommerce_wp_select(
					array(
						'id'          => '_hide_from_shop',
						'label'       => __( 'Hide on shop / archive.', 'discontinued-products' ),
						'desc_tip'    => 'true',
						'description' => __( 'Hide from shop / archive pages.', 'discontinued-products' ),
						'options'     => array(
							''     => __( 'Default', 'discontinued-products' ),
							'hide' => __( 'Hide', 'discontinued-products' ),
							'show' => __( 'Show', 'discontinued-products' ),
						),
						'value'       => $hide_from_shop,
					)
				);
				$hide_from_search = '';
				$hide_from_search = has_term( 'dp-hide-search', 'product_discontinued', $post->ID ) ? 'hide' : $hide_from_search;
				$hide_from_search = has_term( 'dp-show-search', 'product_discontinued', $post->ID ) ? 'show' : $hide_from_search;
				woocommerce_wp_select(
					array(
						'id'          => '_hide_from_search',
						'label'       => __( 'Hide on search.', 'discontinued-products' ),
						'desc_tip'    => 'true',
						'description' => __( 'Hide from search results.', 'discontinued-products' ),
						'options'     => array(
							''     => __( 'Default', 'discontinued-products' ),
							'hide' => __( 'Hide', 'discontinued-products' ),
							'show' => __( 'Show', 'discontinued-products' ),
						),
						'value'       => $hide_from_search,
					)
				);
				?>
				</div>

			</div>
			<?php
		}
		/**
		 * Save dicontinued product settings.
		 *
		 * @since 2.0.0
		 * @param int $post_id Optional. ID of the product to update.
		 */
		public static function save( $post_id ) {

			$terms = array();
			update_post_meta( $post_id, '_discontinued_product_text', filter_input( INPUT_POST, '_discontinued_product_text' ) );
			$alt_products = filter_input( INPUT_POST, 'alt_products', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ? filter_input( INPUT_POST, 'alt_products', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) : array();
			update_post_meta( $post_id, '_alt_products', $alt_products );
			update_post_meta( $post_id, '_alt_product_text', filter_input( INPUT_POST, '_alt_product_text' ) );
			$is_discontinued = filter_input( INPUT_POST, '_is_discontinued' );
			if ( $is_discontinued ) {
				$terms[] = 'dp-discontinued';
			}
			$hide_from_shop = filter_input( INPUT_POST, '_hide_from_shop' );
			if ( 'hide' === $hide_from_shop ) {
				$terms[] = 'dp-hide-shop';
			} elseif ( 'show' === $hide_from_shop ) {
				$terms[] = 'dp-show-shop';
			}
			$hide_from_search = filter_input( INPUT_POST, '_hide_from_search' );
			if ( 'hide' === $hide_from_search ) {
				$terms[] = 'dp-hide-search';
			} elseif ( 'show' === $hide_from_search ) {
				$terms[] = 'dp-show-search';
			}
			wp_set_object_terms( $post_id, $terms, 'product_discontinued' );
		}

		/**
		 * Remove add to cart.
		 * Remove add to cart and all related buttons like wishlist, and add the alt products.
		 *
		 * @since 1.0.0
		 */
		public static function add_dp_alt_products() {

			if ( dp_is_discontinued() ) {
				add_action( 'woocommerce_single_product_summary', 'dp_alt_products', 60 );
			}
		}

		/**
		 * Check if the current page is a discontinued product archive page.
		 *
		 * @since 2.0.0
		 * @return bool True if it is a discontinued product archive page, false otherwise.
		 */
		public function is_dp_archive_page() {

			$dc_shop_page_id = (int) get_option( 'dp_shop_page_id' );
			return ( is_shop() || is_product_category() ) && $this->current_page_id !== $dc_shop_page_id && ! is_search();
		}

		/**
		 * Build the tax query for discontinued products.
		 *
		 * @since 2.0.0
		 * @param WP_Query $q Main WP Query.
		 * @return array $tax_queries Tax queries for the WP_Query.
		 */
		public function build_tax_query( $q ) {
			$dc_shop_page_id  = (int) get_option( 'dp_shop_page_id' );
			$hide_from_shop   = get_option( 'dp_hide_from_shop' );
			$hide_from_search = get_option( 'dp_hide_from_search' );

			$tax_queries = $q->get( 'tax_query' );
			$tax_queries = is_array( $tax_queries ) ? $tax_queries : array();

			if ( $this->current_page_id && $dc_shop_page_id && $dc_shop_page_id === $this->current_page_id ) {
				$tax_queries[] = array(
					'taxonomy' => 'product_discontinued',
					'field'    => 'slug',
					'terms'    => 'dp-discontinued',
					'operator' => 'IN',
				);
				return $tax_queries;
			}

			if ( $this->is_dp_archive_page() && 'yes' === $hide_from_shop ) {
				$tax_queries[] = array(
					'relation' => 'OR',
					array(
						'taxonomy' => 'product_discontinued',
						'field'    => 'slug',
						'terms'    => 'dp-discontinued',
						'operator' => 'NOT IN',
					),
					array(
						'taxonomy' => 'product_discontinued',
						'field'    => 'slug',
						'terms'    => 'dp-show-shop',
						'operator' => 'IN',
					),
				);
				return $tax_queries;
			}

			if ( $this->is_dp_archive_page() ) {
				$tax_queries[] = array(
					array(
						'taxonomy' => 'product_discontinued',
						'field'    => 'slug',
						'terms'    => 'dp-hide-shop',
						'operator' => 'NOT IN',
					),
				);
				return $tax_queries;
			}

			if ( $q->is_search() && 'yes' === $hide_from_search ) {
				$tax_queries[] = array(
					'relation' => 'OR',
					array(
						'taxonomy' => 'product_discontinued',
						'field'    => 'slug',
						'terms'    => 'dp-discontinued',
						'operator' => 'NOT IN',
					),
					array(
						'taxonomy' => 'product_discontinued',
						'field'    => 'slug',
						'terms'    => 'dp-show-search',
						'operator' => 'IN',
					),
				);
				return $tax_queries;
			}

			if ( $q->is_search() ) {
				$tax_queries[] = array(
					array(
						'taxonomy' => 'product_discontinued',
						'field'    => 'slug',
						'terms'    => 'dp-show-search',
						'operator' => 'IN',
					),
				);
				return $tax_queries;
			}

			return $tax_queries;
		}

		/**
		 * Exclude discontinued products.
		 * Add a the post__not_in argiment to the main query for products.
		 *
		 * @since 1.0.0
		 * @param object $q Main WP Query.
		 */
		public function exclude_discontinued_products( $q ) {
			if ( is_admin() || ! $q->is_main_query() ) {
				return;
			}
			$tax_queries = $this->build_tax_query( $q );
			$q->set( 'tax_query', $tax_queries );
		}


		/**
		 * Borrow the WooCommerce shop template for discontinued products.
		 *
		 * @since 1.4.0
		 * @param int $shop_page_id Shop page ID.
		 */
		public function override_shop_page_id( $shop_page_id ) {

			$dc_shop_page_id = (int) get_option( 'dp_shop_page_id' );

			if ( $this->current_page_id === $dc_shop_page_id ) {
				$shop_page_id = $dc_shop_page_id;
			}
			return $shop_page_id;
		}
	}

	$dp_discontinued_product = new DP_Discontinued_Product();
}
