<?php
/**
 * Product filters of the shop: the query and the numbers behind the Product Filter and Active Filters blocks.
 *
 * - The filters are parameters of the address, so a filtered shop is a normal page (links work without JavaScript,
 *   can be shared, cached and opened in a new tab). The parameters of WooCommerce are used where it has them:
 *   filter_<attribute> + query_type_<attribute>, min_price / max_price, rating_filter; Envo Blocks adds eb_cat
 *   (categories), eb_brand (brands), eb_stock (stock status) and eb_sale (on sale).
 * - One set logic serves the results and the numbers: the products of the page (the shop, a category, tag, brand or
 *   a product search, without hidden products) are narrowed by each active filter; the main product query gets the
 *   result as post__in, and the number next to an option is counted from the products of all the other filters – so
 *   an option shows how many products there are when it is added.
 * - Works with the shop of classic themes and the Product Collection block of block themes (it shows the main query),
 *   and with the Store Layouts of the shop. On another page (a landing page) with a Product Filter block and a
 *   Product Collection block the filters filter that collection and their links stay on the page ("page mode").
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product filters.
 */
final class Envo_Blocks_Filters {

	/**
	 * Parameters of Envo Blocks.
	 */
	const CAT   = 'eb_cat';
	const BRAND = 'eb_brand';
	const STOCK = 'eb_stock';
	const SALE  = 'eb_sale';

	/**
	 * Products of the page (cached per context for the request).
	 *
	 * @var array<string, int[]>
	 */
	private static $context = array();

	/**
	 * Products of one filter (cached for the request).
	 *
	 * @var array<string, int[]>
	 */
	private static $sets = array();

	/**
	 * Active filters of the request (null until read).
	 *
	 * @var array|null
	 */
	private static $active = null;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'woocommerce_product_query', array( __CLASS__, 'product_query' ), 20 );
		add_filter( 'query_loop_block_query_vars', array( __CLASS__, 'collection_query' ), 20, 2 );
	}

	/* ------------------------------------------------------------------ parameters */

	/**
	 * Values of a comma separated parameter.
	 *
	 * @param string $key Parameter.
	 * @return string[]
	 */
	private static function list_param( $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter parameters of the shop.
		if ( ! isset( $_GET[ $key ] ) || is_array( $_GET[ $key ] ) ) {
			return array();
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter parameters of the shop.
		$raw = wc_clean( wp_unslash( $_GET[ $key ] ) );
		return array_values( array_unique( array_filter( array_map( 'sanitize_title', explode( ',', (string) $raw ) ) ) ) );
	}

	/**
	 * A number parameter, or null.
	 *
	 * @param string $key Parameter.
	 * @return float|null
	 */
	private static function number_param( $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter parameters of the shop.
		if ( ! isset( $_GET[ $key ] ) || is_array( $_GET[ $key ] ) || '' === $_GET[ $key ] ) {
			return null;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter parameters of the shop.
		$value = wc_clean( wp_unslash( $_GET[ $key ] ) );
		return is_numeric( $value ) ? max( 0.0, (float) $value ) : null;
	}

	/**
	 * Active filters: key => definition. Keys: "attr:pa_color", "price", "rating", "cat", "brand", "stock", "sale".
	 *
	 * @return array<string, array>
	 */
	public static function active() {
		if ( null !== self::$active ) {
			return self::$active;
		}
		$active = array();
		if ( function_exists( 'wc_get_attribute_taxonomy_names' ) ) {
			foreach ( wc_get_attribute_taxonomy_names() as $taxonomy ) {
				$slug   = wc_attribute_taxonomy_slug( $taxonomy );
				$values = self::list_param( 'filter_' . $slug );
				if ( $values ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter parameters of the shop.
					$type                       = isset( $_GET[ 'query_type_' . $slug ] ) && 'and' === sanitize_key( wp_unslash( $_GET[ 'query_type_' . $slug ] ) ) ? 'and' : 'or';
					$active[ 'attr:' . $taxonomy ] = array(
						'taxonomy' => $taxonomy,
						'values'   => $values,
						'type'     => $type,
					);
				}
			}
		}
		$min = self::number_param( 'min_price' );
		$max = self::number_param( 'max_price' );
		if ( null !== $min || null !== $max ) {
			$active['price'] = array(
				'min' => $min,
				'max' => $max,
			);
		}
		$ratings = array_values( array_intersect( self::list_param( 'rating_filter' ), array( '1', '2', '3', '4', '5' ) ) );
		if ( $ratings ) {
			$active['rating'] = array( 'values' => $ratings );
		}
		foreach ( array(
			'cat'   => array( self::CAT, 'product_cat' ),
			'brand' => array( self::BRAND, 'product_brand' ),
		) as $key => $def ) {
			$values = self::list_param( $def[0] );
			if ( $values && taxonomy_exists( $def[1] ) ) {
				$active[ $key ] = array(
					'taxonomy' => $def[1],
					'values'   => $values,
				);
			}
		}
		$stock = array_values( array_intersect( self::list_param( self::STOCK ), array( 'instock', 'onbackorder', 'outofstock' ) ) );
		if ( $stock ) {
			$active['stock'] = array( 'values' => $stock );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter parameters of the shop.
		if ( ! empty( $_GET[ self::SALE ] ) ) {
			$active['sale'] = array();
		}
		self::$active = $active;
		return $active;
	}

	/**
	 * Parameters that a filter key uses (to remove it from an address).
	 *
	 * @param string $key Filter key.
	 * @return string[]
	 */
	public static function params_of( $key ) {
		if ( 0 === strpos( $key, 'attr:' ) ) {
			$slug = wc_attribute_taxonomy_slug( substr( $key, 5 ) );
			return array( 'filter_' . $slug, 'query_type_' . $slug );
		}
		$map = array(
			'price'  => array( 'min_price', 'max_price' ),
			'rating' => array( 'rating_filter' ),
			'cat'    => array( self::CAT ),
			'brand'  => array( self::BRAND ),
			'stock'  => array( self::STOCK ),
			'sale'   => array( self::SALE ),
		);
		return isset( $map[ $key ] ) ? $map[ $key ] : array();
	}

	/**
	 * All filter parameters of the request.
	 *
	 * @return string[]
	 */
	public static function all_params() {
		$params = array( 'min_price', 'max_price', 'rating_filter', self::CAT, self::BRAND, self::STOCK, self::SALE );
		if ( function_exists( 'wc_get_attribute_taxonomy_names' ) ) {
			foreach ( wc_get_attribute_taxonomy_names() as $taxonomy ) {
				$slug     = wc_attribute_taxonomy_slug( $taxonomy );
				$params[] = 'filter_' . $slug;
				$params[] = 'query_type_' . $slug;
			}
		}
		return $params;
	}

	/* ------------------------------------------------------------------ where */

	/**
	 * Whether the page lists the products of the main query (shop, product taxonomies, product search).
	 *
	 * @return bool
	 */
	public static function on_shop() {
		if ( ! function_exists( 'is_shop' ) ) {
			return false;
		}
		/**
		 * Filters whether the product filters filter the current page (true) or link to the shop (false).
		 *
		 * @param bool $on Shop, product category, tag, attribute or brand archive, or a product search.
		 */
		return (bool) apply_filters( 'envo_blocks_filters_on_shop', is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) || self::page_mode() );
	}

	/**
	 * Whether the page is a page (or post) with a Product Filter block and a Product Collection block: the filters
	 * filter that collection.
	 *
	 * @return bool
	 */
	public static function page_mode() {
		if ( ! is_singular() || ( function_exists( 'is_product' ) && is_product() ) ) {
			return false;
		}
		$post = get_queried_object();
		return $post instanceof WP_Post && has_block( 'envo-blocks/product-filter', $post ) && has_block( 'woocommerce/product-collection', $post );
	}

	/**
	 * Product categories of the first Product Collection block of the page (page mode).
	 *
	 * @return int[]
	 */
	private static function collection_categories() {
		static $cache = array();
		$post = get_queried_object();
		if ( ! $post instanceof WP_Post ) {
			return array();
		}
		if ( ! isset( $cache[ $post->ID ] ) ) {
			$find = static function ( $blocks ) use ( &$find ) {
				foreach ( $blocks as $block ) {
					if ( 'woocommerce/product-collection' === $block['blockName'] && empty( $block['attrs']['query']['inherit'] ) ) {
						$cats = isset( $block['attrs']['query']['taxQuery']['product_cat'] ) ? (array) $block['attrs']['query']['taxQuery']['product_cat'] : array();
						return array_values( array_filter( array_map( 'intval', $cats ) ) );
					}
					if ( ! empty( $block['innerBlocks'] ) ) {
						$found = $find( $block['innerBlocks'] );
						if ( null !== $found ) {
							return $found;
						}
					}
				}
				return null;
			};
			$found              = $find( parse_blocks( $post->post_content ) );
			$cache[ $post->ID ] = $found ? $found : array();
		}
		return $cache[ $post->ID ];
	}

	/**
	 * In page mode a Product Collection that does not show the main query gets the products of the filters.
	 *
	 * @param array    $query Query vars of the collection.
	 * @param WP_Block $block Product Template block inside the collection.
	 * @return array
	 */
	public static function collection_query( $query, $block ) {
		$context = $block instanceof WP_Block && isset( $block->context['query'] ) ? $block->context['query'] : array();
		if ( empty( $context['isProductCollectionBlock'] ) || ! empty( $context['inherit'] ) || ! self::active() || ! self::page_mode() ) {
			return $query;
		}
		$ids = self::products();
		if ( ! empty( $query['post__in'] ) && is_array( $query['post__in'] ) ) {
			$ids = array_values( array_intersect( $ids, array_map( 'intval', $query['post__in'] ) ) );
		}
		$query['post__in'] = $ids ? $ids : array( 0 );
		return $query;
	}

	/**
	 * Address without page numbers (shop pages, and the page parameters of Query Loop / Product Collection blocks).
	 *
	 * @param string $url Address.
	 * @return string
	 */
	public static function first_page( $url ) {
		$query = (string) wp_parse_url( $url, PHP_URL_QUERY );
		parse_str( $query, $vars );
		$drop = array( 'paged', 'product-page' );
		foreach ( array_keys( $vars ) as $name ) {
			if ( preg_match( '/^query-[\w-]*page$/', (string) $name ) ) {
				$drop[] = $name;
			}
		}
		return remove_query_arg( $drop, $url );
	}

	/**
	 * Address the filter links start from: this page without page numbers (on the shop pages), else the shop.
	 *
	 * @return string
	 */
	public static function base_url() {
		if ( self::on_shop() ) {
			return self::first_page( get_pagenum_link( 1, false ) );
		}
		$shop = wc_get_page_permalink( 'shop' );
		return $shop ? $shop : home_url( '/' );
	}

	/**
	 * Address with all filters removed (the sorting stays).
	 *
	 * @return string
	 */
	public static function clear_url() {
		return remove_query_arg( self::all_params(), self::base_url() );
	}

	/* ------------------------------------------------------------------ sets */

	/**
	 * The context of a query: its product taxonomy term and search words.
	 *
	 * @param WP_Query|null $query Query (the main query when null).
	 * @return array{taxonomy:string, term:int, search:string}
	 */
	private static function context_of( $query = null ) {
		$query   = $query instanceof WP_Query ? $query : $GLOBALS['wp_query'];
		$context = array(
			'taxonomy' => '',
			'term'     => 0,
			'search'   => '',
		);
		if ( ! $query instanceof WP_Query ) {
			return $context;
		}
		if ( $query->is_main_query() && self::page_mode() ) {
			// Page mode: the categories of the Product Collection of the page (all products when it has none).
			$terms = self::collection_categories();
			if ( $terms ) {
				$context['taxonomy'] = 'product_cat';
				$context['term']     = $terms;
			}
			return $context;
		}
		if ( $query->is_tax() || $query->is_category() || $query->is_tag() ) {
			$term = $query->get_queried_object();
			if ( $term instanceof WP_Term && is_object_in_taxonomy( 'product', $term->taxonomy ) ) {
				$context['taxonomy'] = $term->taxonomy;
				$context['term']     = (int) $term->term_id;
			}
		}
		if ( $query->is_search() ) {
			$context['search'] = (string) $query->get( 's' );
		}
		return $context;
	}

	/**
	 * Products of the page: published, visible in the catalog (or in the search), in the term of the archive or found
	 * by the search; out of stock ones left out when the shop hides them.
	 *
	 * @param array $context Context (context_of()).
	 * @return int[]
	 */
	private static function context_ids( $context ) {
		$key = md5( wp_json_encode( $context ) );
		if ( isset( self::$context[ $key ] ) ) {
			return self::$context[ $key ];
		}
		$hidden    = array( '' !== $context['search'] ? 'exclude-from-search' : 'exclude-from-catalog' );
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$hidden[] = 'outofstock';
		}
		$tax_query = array(
			array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => $hidden,
				'operator' => 'NOT IN',
			),
		);
		if ( $context['term'] ) {
			$tax_query[] = array(
				'taxonomy'         => $context['taxonomy'],
				'field'            => 'term_id',
				'terms'            => array_map( 'intval', (array) $context['term'] ),
				'include_children' => true,
			);
		}
		$args = array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'fields'                 => 'ids',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'suppress_filters'       => true, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.SuppressFilters_suppress_filters -- Only product IDs; other plugins must not change the filter sets.
			'tax_query'              => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- The products of the page, once per request.
		);
		if ( '' !== $context['search'] ) {
			$args['s']                = $context['search'];
			$args['suppress_filters'] = false; // Search plugins extend the product search through the filters.
		}
		$ids                    = array_map( 'intval', get_posts( $args ) );
		self::$context[ $key ] = $ids;
		return $ids;
	}

	/**
	 * Products in terms of a taxonomy (with their child terms).
	 *
	 * @param string   $taxonomy Taxonomy.
	 * @param string[] $slugs    Term slugs.
	 * @param bool     $all      Products that have all the terms (else any of them).
	 * @return int[]
	 */
	private static function term_products( $taxonomy, $slugs, $all ) {
		global $wpdb;
		$groups = array();
		foreach ( $slugs as $slug ) {
			$term = get_term_by( 'slug', $slug, $taxonomy );
			if ( ! $term instanceof WP_Term ) {
				$groups[] = array();
				continue;
			}
			$ids      = is_taxonomy_hierarchical( $taxonomy ) ? array_merge( array( (int) $term->term_id ), array_map( 'intval', (array) get_term_children( $term->term_id, $taxonomy ) ) ) : array( (int) $term->term_id );
			$list     = implode( ',', $ids );
			$groups[] = array_map(
				'intval',
				$wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Cached per request in set().
					$wpdb->prepare(
						"SELECT DISTINCT tr.object_id FROM {$wpdb->term_relationships} tr INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tt.taxonomy = %s AND tt.term_id IN ( {$list} )", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Integers joined above.
						$taxonomy
					)
				)
			);
		}
		if ( ! $groups ) {
			return array();
		}
		$result = array_shift( $groups );
		foreach ( $groups as $group ) {
			$result = $all ? array_intersect( $result, $group ) : array_merge( $result, $group );
		}
		return array_values( array_unique( $result ) );
	}

	/**
	 * Products of one active filter.
	 *
	 * @param string $key    Filter key.
	 * @param array  $filter Definition (active()).
	 * @return int[]
	 */
	private static function set( $key, $filter ) {
		global $wpdb;
		$cache = $key . ':' . md5( wp_json_encode( $filter ) );
		if ( isset( self::$sets[ $cache ] ) ) {
			return self::$sets[ $cache ];
		}
		$lookup = $wpdb->prefix . 'wc_product_meta_lookup';
		$ids    = array();
		if ( 0 === strpos( $key, 'attr:' ) ) {
			$ids = self::term_products( $filter['taxonomy'], $filter['values'], 'and' === $filter['type'] );
		} elseif ( 'cat' === $key || 'brand' === $key ) {
			$ids = self::term_products( $filter['taxonomy'], $filter['values'], false );
		} elseif ( 'price' === $key ) {
			// Products whose price range meets the range of the filter (as WooCommerce filters by price).
			$min = null === $filter['min'] ? 0 : $filter['min'];
			$max = null === $filter['max'] ? PHP_INT_MAX : $filter['max'];
			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT product_id FROM {$lookup} WHERE NOT ( max_price < %f OR min_price > %f )", $min, $max ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name of WooCommerce.
		} elseif ( 'rating' === $key ) {
			$ids = self::term_products(
				'product_visibility',
				array_map(
					static function ( $n ) {
						return 'rated-' . $n;
					},
					$filter['values']
				),
				false
			);
		} elseif ( 'stock' === $key ) {
			$in  = implode( ',', array_fill( 0, count( $filter['values'] ), '%s' ) );
			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT product_id FROM {$lookup} WHERE stock_status IN ( {$in} )", $filter['values'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Placeholders built above.
		} elseif ( 'sale' === $key ) {
			$ids = wc_get_product_ids_on_sale();
		}
		self::$sets[ $cache ] = array_values( array_unique( array_map( 'intval', (array) $ids ) ) );
		return self::$sets[ $cache ];
	}

	/**
	 * Products of the page with the active filters, leaving one out (for its numbers).
	 *
	 * @param string        $except Filter key to leave out ('' for none).
	 * @param WP_Query|null $query  Query of the page (the main query when null).
	 * @return int[]
	 */
	public static function products( $except = '', $query = null ) {
		$ids = self::context_ids( self::context_of( $query ) );
		foreach ( self::active() as $key => $filter ) {
			if ( $key !== $except && $ids ) {
				$ids = array_values( array_intersect( $ids, self::set( $key, $filter ) ) );
			}
		}
		return $ids;
	}

	/**
	 * The main product query with filters gets their products.
	 *
	 * @param WP_Query $query Main product query (WooCommerce).
	 */
	public static function product_query( $query ) {
		if ( ! self::active() ) {
			return;
		}
		$ids = self::products( '', $query );
		$in  = $query->get( 'post__in' );
		if ( is_array( $in ) && $in ) {
			$ids = array_values( array_intersect( $ids, array_map( 'intval', $in ) ) );
		}
		$query->set( 'post__in', $ids ? $ids : array( 0 ) );
	}

	/* ------------------------------------------------------------------ numbers */

	/**
	 * Numbers of products per term among the products of the page with the other filters.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $except   Filter key to leave out.
	 * @return array<int,int> Term ID => products (hierarchical taxonomies: with the child terms).
	 */
	public static function term_counts( $taxonomy, $except ) {
		return self::term_counts_in( $taxonomy, self::products( $except ) );
	}

	/**
	 * Numbers of products per term among the products of the page without any filter (which options exist here).
	 *
	 * @param string $taxonomy Taxonomy.
	 * @return array<int,int>
	 */
	public static function term_counts_here( $taxonomy ) {
		return self::term_counts_in( $taxonomy, self::context_ids( self::context_of() ) );
	}

	/**
	 * Numbers of products per term among some products.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param int[]  $ids      Products.
	 * @return array<int,int>
	 */
	private static function term_counts_in( $taxonomy, $ids ) {
		global $wpdb;
		if ( ! $ids ) {
			return array();
		}
		$list = implode( ',', $ids );
		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT tt.term_id, tr.object_id FROM {$wpdb->term_relationships} tr INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tt.taxonomy = %s AND tr.object_id IN ( {$list} )", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Integers joined above.
				$taxonomy
			)
		);
		$sets = array();
		foreach ( (array) $rows as $row ) {
			$sets[ (int) $row->term_id ][ (int) $row->object_id ] = true;
		}
		if ( is_taxonomy_hierarchical( $taxonomy ) ) {
			// A product in a child category counts for its parents too (once).
			foreach ( array_keys( $sets ) as $term_id ) {
				foreach ( get_ancestors( $term_id, $taxonomy, 'taxonomy' ) as $parent ) {
					$sets[ $parent ] = ( isset( $sets[ $parent ] ) ? $sets[ $parent ] : array() ) + $sets[ $term_id ];
				}
			}
		}
		return array_map( 'count', $sets );
	}

	/**
	 * Numbers of products per stock status.
	 *
	 * @param bool $here Among the products of the page without any filter (else with the other filters).
	 * @return array<string,int>
	 */
	public static function stock_counts( $here = false ) {
		global $wpdb;
		$ids = $here ? self::context_ids( self::context_of() ) : self::products( 'stock' );
		if ( ! $ids ) {
			return array();
		}
		$list = implode( ',', $ids );
		$rows = $wpdb->get_results( "SELECT stock_status, COUNT(*) AS n FROM {$wpdb->prefix}wc_product_meta_lookup WHERE product_id IN ( {$list} ) GROUP BY stock_status" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Integers joined above.
		$out  = array();
		foreach ( (array) $rows as $row ) {
			$out[ (string) $row->stock_status ] = (int) $row->n;
		}
		return $out;
	}

	/**
	 * Number of products on sale.
	 *
	 * @param bool $here Among the products of the page without any filter (else with the other filters).
	 * @return int
	 */
	public static function sale_count( $here = false ) {
		$ids = $here ? self::context_ids( self::context_of() ) : self::products( 'sale' );
		return count( array_intersect( $ids, array_map( 'intval', wc_get_product_ids_on_sale() ) ) );
	}

	/**
	 * Numbers of products per rating (1–5).
	 *
	 * @param bool $here Among the products of the page without any filter (else with the other filters).
	 * @return array<int,int>
	 */
	public static function rating_counts( $here = false ) {
		$counts = array();
		foreach ( ( $here ? self::term_counts_here( 'product_visibility' ) : self::term_counts( 'product_visibility', 'rating' ) ) as $term_id => $n ) {
			$term = get_term( $term_id, 'product_visibility' );
			if ( $term instanceof WP_Term && preg_match( '/^rated-([1-5])$/', $term->slug, $m ) ) {
				$counts[ (int) $m[1] ] = $n;
			}
		}
		return $counts;
	}

	/**
	 * Lowest and highest price of the products of the page with the other filters.
	 *
	 * @return array{min:float, max:float}|null
	 */
	public static function price_range() {
		global $wpdb;
		$ids = self::products( 'price' );
		if ( ! $ids ) {
			return null;
		}
		$list = implode( ',', $ids );
		$row  = $wpdb->get_row( "SELECT MIN( min_price ) AS min_price, MAX( max_price ) AS max_price FROM {$wpdb->prefix}wc_product_meta_lookup WHERE product_id IN ( {$list} )" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Integers joined above.
		if ( ! $row || null === $row->max_price ) {
			return null;
		}
		return array(
			'min' => (float) floor( (float) $row->min_price ),
			'max' => (float) ceil( (float) $row->max_price ),
		);
	}

	/* ------------------------------------------------------------------ PRO */

	/**
	 * Whether Envo Blocks PRO adds its filter features (AJAX, more types and looks).
	 *
	 * @return bool
	 */
	public static function pro() {
		/**
		 * Filters whether the PRO features of the product filters are on (Envo Blocks PRO).
		 *
		 * @param bool $pro Whether they are on.
		 */
		return (bool) apply_filters( 'envo_blocks_filters_pro', false );
	}
}
