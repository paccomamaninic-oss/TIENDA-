<?php
/**
 * Products, categories and brands chosen in imported templates and blocks: their IDs are those of the demo
 * site, so on import they are matched to the items of this store.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * How an ID is matched:
 * 1. by slug: the template file has "refs" (type => array( demo ID => slug )), and this store has an item
 *    with the same slug;
 * 2. otherwise an optional ID is dropped (the block then works automatically, for example with all products),
 *    and a required ID is filled with an item of this store (best-selling products, biggest categories).
 *
 * Which attributes hold IDs comes from rules (filter envo_blocks_id_rules), keyed by block name:
 *
 *     'plugin/block' => array(
 *         array(
 *             'attr'     => 'categories',  // Attribute name.
 *             'type'     => 'product_cat', // product, product_cat or product_brand.
 *             'list'     => true,          // An array of IDs (default false: one ID).
 *             'required' => false,         // Fill from this store when there is no match.
 *             'when'     => array( 'source' => 'category' ), // Optional: "required" only with these values.
 *             'fill'     => 3,             // List: at least this many items, filled from this store ("required").
 *             'empty'    => array( 'source' => array( 'manual' => 'all' ) ), // List ended up empty: change other attributes.
 *             'each'     => array( ...rules... ), // The attribute is a list of objects (tabs, hotspots); rules for each object.
 *         ),
 *     )
 */
final class Envo_Blocks_Ids {

	/**
	 * Types of the matched items.
	 */
	const TYPES = array( 'product', 'product_cat', 'product_brand' );

	/**
	 * Items of this store used to fill required IDs: type => list of IDs, and how many were used.
	 *
	 * @var array
	 */
	private static $pool = array();

	/**
	 * Matches found in this request: type => array( slug => ID here or 0 ).
	 *
	 * @var array
	 */
	private static $found = array();

	/**
	 * Rules of the blocks.
	 *
	 * @return array
	 */
	private static function rules() {
		$rules = apply_filters( 'envo_blocks_id_rules', array() );
		return is_array( $rules ) ? $rules : array();
	}

	/**
	 * Validates the "refs" of a template file.
	 *
	 * @param mixed $refs Raw value.
	 * @return array type => array( ID => slug ).
	 */
	public static function sanitize_refs( $refs ) {
		$out = array();
		if ( ! is_array( $refs ) ) {
			return $out;
		}
		foreach ( self::TYPES as $type ) {
			if ( empty( $refs[ $type ] ) || ! is_array( $refs[ $type ] ) ) {
				continue;
			}
			foreach ( array_slice( $refs[ $type ], 0, 500, true ) as $id => $slug ) {
				$id   = absint( $id );
				$slug = is_string( $slug ) ? sanitize_title( $slug ) : '';
				if ( $id && '' !== $slug ) {
					$out[ $type ][ $id ] = $slug;
				}
			}
		}
		return $out;
	}

	/**
	 * Matches the IDs of the blocks to this store.
	 *
	 * @param array $blocks Blocks (name, attributes, innerBlocks).
	 * @param array $refs   Slugs from the template file.
	 * @return array
	 */
	public static function remap( $blocks, $refs ) {
		$rules = self::rules();
		if ( ! $rules || ! is_array( $blocks ) ) {
			return $blocks;
		}
		return self::remap_blocks( $blocks, $rules, self::sanitize_refs( $refs ) );
	}

	/**
	 * Slugs of the IDs used in the blocks, for "Export as JSON" (refs of the exported file).
	 *
	 * @param array $blocks Blocks (name, attributes, innerBlocks).
	 * @return array type => array( ID => slug ).
	 */
	public static function refs( $blocks ) {
		$ids = array();
		self::collect( is_array( $blocks ) ? $blocks : array(), self::rules(), $ids );

		$refs = array();
		foreach ( $ids as $type => $list ) {
			foreach ( array_keys( $list ) as $id ) {
				$slug = self::slug( $type, $id );
				if ( '' !== $slug ) {
					$refs[ $type ][ $id ] = $slug;
				}
			}
		}
		return $refs;
	}

	/**
	 * Remaps a list of blocks.
	 *
	 * @param array $blocks Blocks.
	 * @param array $rules  Rules.
	 * @param array $refs   Slugs.
	 * @return array
	 */
	private static function remap_blocks( $blocks, $rules, $refs ) {
		foreach ( $blocks as $i => $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}
			$name = isset( $block['name'] ) ? (string) $block['name'] : '';
			if ( isset( $rules[ $name ] ) && isset( $block['attributes'] ) && is_array( $block['attributes'] ) ) {
				$blocks[ $i ]['attributes'] = self::apply( $block['attributes'], (array) $rules[ $name ], $refs, self::defaults( $name ) );
			}
			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$blocks[ $i ]['innerBlocks'] = self::remap_blocks( $block['innerBlocks'], $rules, $refs );
			}
		}
		return $blocks;
	}

	/**
	 * Default attribute values of a registered block (an attribute with its default value is not saved).
	 *
	 * @param string $name Block name.
	 * @return array
	 */
	private static function defaults( $name ) {
		$type     = WP_Block_Type_Registry::get_instance()->get_registered( $name );
		$defaults = array();
		foreach ( ( $type && is_array( $type->attributes ) ) ? $type->attributes : array() as $key => $schema ) {
			if ( is_array( $schema ) && array_key_exists( 'default', $schema ) ) {
				$defaults[ $key ] = $schema['default'];
			}
		}
		return $defaults;
	}

	/**
	 * Applies the rules of one block (or of one object of a list such as a tab) to its attributes.
	 *
	 * @param array $attrs    Attributes.
	 * @param array $rules    Rules.
	 * @param array $refs     Slugs.
	 * @param array $defaults Default attribute values.
	 * @return array
	 */
	private static function apply( $attrs, $rules, $refs, $defaults = array() ) {
		foreach ( $rules as $rule ) {
			if ( ! is_array( $rule ) || empty( $rule['attr'] ) || ! array_key_exists( $rule['attr'], $attrs ) ) {
				continue;
			}
			$key = $rule['attr'];

			if ( ! empty( $rule['each'] ) ) {
				if ( is_array( $attrs[ $key ] ) ) {
					foreach ( $attrs[ $key ] as $n => $item ) {
						if ( is_array( $item ) ) {
							$attrs[ $key ][ $n ] = self::apply( $item, (array) $rule['each'], $refs );
						}
					}
				}
				continue;
			}

			$type = isset( $rule['type'] ) && in_array( $rule['type'], self::TYPES, true ) ? $rule['type'] : '';
			if ( '' === $type ) {
				continue;
			}

			$required = ! empty( $rule['required'] );
			if ( $required && ! empty( $rule['when'] ) && is_array( $rule['when'] ) ) {
				foreach ( $rule['when'] as $other => $value ) {
					$current = isset( $attrs[ $other ] ) ? $attrs[ $other ] : ( isset( $defaults[ $other ] ) ? $defaults[ $other ] : null );
					if ( ! is_scalar( $current ) || (string) $current !== (string) $value ) {
						$required = false;
					}
				}
			}

			if ( ! empty( $rule['list'] ) ) {
				$list = array();
				foreach ( is_array( $attrs[ $key ] ) ? $attrs[ $key ] : array() as $id ) {
					$here = self::match( $type, absint( $id ), $refs );
					if ( ! $here && $required ) {
						$here = self::fill( $type );
					}
					if ( $here && ! in_array( $here, $list, true ) ) {
						$list[] = $here;
					}
				}
				// At least "fill" items (a comparison table needs a few products).
				if ( $required && ! empty( $rule['fill'] ) && count( $list ) < (int) $rule['fill'] ) {
					for ( $n = count( $list ); $n < (int) $rule['fill'] * 2 && count( $list ) < (int) $rule['fill']; $n++ ) {
						$here = self::fill( $type );
						if ( $here && ! in_array( $here, $list, true ) ) {
							$list[] = $here;
						}
					}
				}
				$had          = is_array( $attrs[ $key ] ) && $attrs[ $key ];
				$attrs[ $key ] = $list;

				// The list was set in the template but nothing matched: switch to the automatic mode.
				if ( $had && ! $list && ! empty( $rule['empty'] ) && is_array( $rule['empty'] ) ) {
					foreach ( $rule['empty'] as $other => $change ) {
						if ( isset( $attrs[ $other ] ) && is_array( $change ) && isset( $change[ (string) $attrs[ $other ] ] ) ) {
							$attrs[ $other ] = $change[ (string) $attrs[ $other ] ];
						}
					}
				}
				continue;
			}

			$id = absint( $attrs[ $key ] );
			if ( ! $id ) {
				continue;
			}
			$here = self::match( $type, $id, $refs );
			if ( ! $here && $required ) {
				$here = self::fill( $type );
			}
			$attrs[ $key ] = $here;
		}

		return $attrs;
	}

	/**
	 * ID of the same item in this store (by slug), or 0.
	 *
	 * @param string $type Type.
	 * @param int    $id   Demo ID.
	 * @param array  $refs Slugs.
	 * @return int
	 */
	private static function match( $type, $id, $refs ) {
		if ( ! $id || empty( $refs[ $type ][ $id ] ) ) {
			return 0;
		}
		$slug = $refs[ $type ][ $id ];
		if ( isset( self::$found[ $type ][ $slug ] ) ) {
			return self::$found[ $type ][ $slug ];
		}

		$here = 0;
		if ( 'product' === $type ) {
			$post = get_page_by_path( $slug, OBJECT, 'product' );
			$here = ( $post && 'publish' === $post->post_status ) ? (int) $post->ID : 0;
		} elseif ( taxonomy_exists( $type ) ) {
			$term = get_term_by( 'slug', $slug, $type );
			$here = ( $term && ! is_wp_error( $term ) ) ? (int) $term->term_id : 0;
		}

		self::$found[ $type ][ $slug ] = $here;
		return $here;
	}

	/**
	 * Next unused item of this store for a required ID: best-selling products, categories and brands with
	 * the most products. 0 when the store has none (left).
	 *
	 * @param string $type Type.
	 * @return int
	 */
	private static function fill( $type ) {
		if ( ! isset( self::$pool[ $type ] ) ) {
			$ids = array();
			if ( 'product' === $type ) {
				$ids = get_posts(
					array(
						'post_type'        => 'product',
						'post_status'      => 'publish',
						'posts_per_page'   => 30,
						'fields'           => 'ids',
						'no_found_rows'    => true,
						'meta_key'         => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'orderby'          => array(
							'meta_value_num' => 'DESC',
							'date'           => 'DESC',
						),
						'suppress_filters' => false,
					)
				);
			} elseif ( taxonomy_exists( $type ) ) {
				$args = array(
					'taxonomy'   => $type,
					'hide_empty' => true,
					'orderby'    => 'count',
					'order'      => 'DESC',
					'number'     => 30,
					'fields'     => 'ids',
				);
				if ( 'product_cat' === $type ) {
					$args['exclude'] = array( absint( get_option( 'default_product_cat' ) ) );
				}
				$ids = get_terms( $args );
				$ids = is_wp_error( $ids ) ? array() : $ids;
			}
			self::$pool[ $type ] = array(
				'ids'  => array_map( 'absint', (array) $ids ),
				'next' => 0,
			);
		}

		$pool = &self::$pool[ $type ];
		if ( ! $pool['ids'] ) {
			return 0;
		}
		// Different items for the blocks of one import; starts over when all were used.
		$id = $pool['ids'][ $pool['next'] % count( $pool['ids'] ) ];
		++$pool['next'];
		return $id;
	}

	/**
	 * Slug of an item of this site, or "".
	 *
	 * @param string $type Type.
	 * @param int    $id   ID.
	 * @return string
	 */
	private static function slug( $type, $id ) {
		if ( 'product' === $type ) {
			$post = get_post( $id );
			return ( $post && 'product' === $post->post_type ) ? (string) $post->post_name : '';
		}
		$term = taxonomy_exists( $type ) ? get_term( $id, $type ) : null;
		return ( $term && ! is_wp_error( $term ) ) ? (string) $term->slug : '';
	}

	/**
	 * Collects the IDs used in blocks (for refs).
	 *
	 * @param array $blocks Blocks.
	 * @param array $rules  Rules.
	 * @param array $ids    Collected: type => array( ID => true ).
	 */
	private static function collect( $blocks, $rules, &$ids ) {
		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}
			$name = isset( $block['name'] ) ? (string) $block['name'] : '';
			if ( isset( $rules[ $name ] ) && isset( $block['attributes'] ) && is_array( $block['attributes'] ) ) {
				self::collect_attrs( $block['attributes'], (array) $rules[ $name ], $ids );
			}
			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				self::collect( $block['innerBlocks'], $rules, $ids );
			}
		}
	}

	/**
	 * Collects the IDs of one block or object.
	 *
	 * @param array $attrs Attributes.
	 * @param array $rules Rules.
	 * @param array $ids   Collected.
	 */
	private static function collect_attrs( $attrs, $rules, &$ids ) {
		foreach ( $rules as $rule ) {
			if ( ! is_array( $rule ) || empty( $rule['attr'] ) || ! isset( $attrs[ $rule['attr'] ] ) ) {
				continue;
			}
			$value = $attrs[ $rule['attr'] ];
			if ( ! empty( $rule['each'] ) ) {
				foreach ( is_array( $value ) ? $value : array() as $item ) {
					if ( is_array( $item ) ) {
						self::collect_attrs( $item, (array) $rule['each'], $ids );
					}
				}
				continue;
			}
			if ( empty( $rule['type'] ) || ! in_array( $rule['type'], self::TYPES, true ) ) {
				continue;
			}
			foreach ( is_array( $value ) ? $value : array( $value ) as $id ) {
				$id = absint( $id );
				if ( $id ) {
					$ids[ $rule['type'] ][ $id ] = true;
				}
			}
		}
	}
}
