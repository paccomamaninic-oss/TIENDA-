<?php
/**
 * Markup of the Product Filter and Active Filters blocks (the query and the numbers are in Envo_Blocks_Filters).
 *
 * Product Filter types: price (range slider + fields), category, attribute, rating, stock status; Envo Blocks PRO adds
 * brand and "on sale", the looks swatches / buttons / dropdown, hiding options with no products and filtering
 * without reloading the page. Every option is a link (rel="nofollow": search engines do not index the
 * combinations) that adds or removes its value; the price is a GET form.
 *
 * Options that have no products on the page at all are left out; options that have none only because of the other
 * filters are shown greyed.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filter blocks.
 */
final class Envo_Blocks_Filter_Blocks {

	/**
	 * Types of the Product Filter block (free; PRO adds brand and sale).
	 */
	const TYPES = array( 'attribute', 'price', 'category', 'rating', 'stock', 'brand', 'sale' );

	/**
	 * Looks of the options (free: list, checkbox; PRO: swatches, buttons, dropdown).
	 */
	const DISPLAYS = array( 'checkbox', 'list', 'swatches', 'buttons', 'dropdown' );

	/**
	 * Address with the values of a filter parameter replaced.
	 *
	 * @param string   $base   Address.
	 * @param string   $param  Parameter.
	 * @param string[] $values Values (none: the parameter is removed).
	 * @return string
	 */
	private static function url( $base, $param, $values ) {
		$base = Envo_Blocks_Filters::first_page( $base );
		return $values ? add_query_arg( $param, implode( ',', array_map( 'rawurlencode', $values ) ), $base ) : remove_query_arg( $param, $base );
	}

	/**
	 * Values with one value toggled.
	 *
	 * @param string[] $chosen Chosen values.
	 * @param string   $value  Value.
	 * @param bool     $multi  More values at once (else the value replaces the others).
	 * @return string[]
	 */
	private static function toggle( $chosen, $value, $multi ) {
		if ( in_array( $value, $chosen, true ) ) {
			return array_values( array_diff( $chosen, array( $value ) ) );
		}
		return $multi ? array_merge( $chosen, array( $value ) ) : array( $value );
	}

	/**
	 * Renders the Product Filter block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function render_filter( $attributes ) {
		if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
			return '';
		}
		$pro     = Envo_Blocks_Filters::pro();
		$type    = Envo_Blocks_Blocks::choice( $attributes, 'filterType', self::TYPES );
		$display = Envo_Blocks_Blocks::choice( $attributes, 'display', self::DISPLAYS );
		if ( ! $pro && in_array( $type, array( 'brand', 'sale' ), true ) ) {
			return Envo_Blocks_Blocks::is_preview() ? Envo_Blocks_Blocks::notice( __( 'This filter needs Envo Blocks PRO.', 'envo-blocks' ) ) : '';
		}
		if ( ! $pro && in_array( $display, array( 'swatches', 'buttons', 'dropdown' ), true ) ) {
			$display = 'checkbox';
		}
		$options = array(
			'title'     => isset( $attributes['title'] ) ? trim( sanitize_text_field( $attributes['title'] ) ) : '',
			'showCount' => ! isset( $attributes['showCount'] ) || $attributes['showCount'],
			'hideEmpty' => $pro && ! empty( $attributes['hideEmpty'] ),
			'limit'     => isset( $attributes['limit'] ) ? max( 0, min( 50, (int) $attributes['limit'] ) ) : 0,
			'display'   => $display,
		);

		switch ( $type ) {
			case 'price':
				$body  = self::price( $attributes );
				$title = __( 'Price', 'envo-blocks' );
				break;
			case 'category':
			case 'brand':
				$taxonomy = 'category' === $type ? 'product_cat' : 'product_brand';
				if ( ! taxonomy_exists( $taxonomy ) ) {
					return '';
				}
				$body  = self::terms_filter( $taxonomy, 'category' === $type ? 'cat' : 'brand', $options, $attributes );
				$title = 'category' === $type ? __( 'Categories', 'envo-blocks' ) : __( 'Brands', 'envo-blocks' );
				break;
			case 'rating':
				$body  = self::rating( $options );
				$title = __( 'Rating', 'envo-blocks' );
				break;
			case 'stock':
				$body  = self::stock( $options );
				$title = __( 'Availability', 'envo-blocks' );
				break;
			case 'sale':
				$body  = self::sale( $options );
				$title = __( 'Offers', 'envo-blocks' );
				break;
			default:
				$taxonomy = isset( $attributes['attribute'] ) ? sanitize_title( $attributes['attribute'] ) : '';
				$names    = wc_get_attribute_taxonomy_names();
				$taxonomy = in_array( $taxonomy, $names, true ) ? $taxonomy : ( $names ? $names[0] : '' );
				if ( '' === $taxonomy ) {
					return Envo_Blocks_Blocks::is_preview() ? Envo_Blocks_Blocks::notice( __( 'Create a product attribute first (Products › Attributes).', 'envo-blocks' ) ) : '';
				}
				$body  = self::terms_filter( $taxonomy, 'attr:' . $taxonomy, $options, $attributes );
				$title = wc_attribute_label( $taxonomy );
		}

		if ( '' === $body ) {
			// On the shop pages an empty box stays (hidden): Envo Blocks PRO matches the filters of a loaded page by
			// their order.
			return Envo_Blocks_Filters::on_shop() ? '<div ' . get_block_wrapper_attributes( array( 'class' => 'eb-filter eb-filter--' . $type ) ) . ' hidden></div>' : '';
		}
		$title = '' !== $options['title'] ? $options['title'] : $title;
		$open  = ! isset( $attributes['collapsible'] ) || 'closed' !== $attributes['collapsible'];
		$head  = isset( $attributes['collapsible'] ) && in_array( $attributes['collapsible'], array( 'open', 'closed' ), true )
			? '<details class="eb-filter__box"' . ( $open ? ' open' : '' ) . '><summary class="eb-filter__title">' . esc_html( $title ) . '</summary>' . $body . '</details>'
			: ( empty( $attributes['hideTitle'] ) ? '<p class="eb-filter__title">' . esc_html( $title ) . '</p>' : '' ) . $body;

		wp_enqueue_script( 'envo-blocks-filters' );
		/**
		 * A Product Filter block is on the page (Envo Blocks PRO loads its script).
		 *
		 * @param string $type Filter type.
		 */
		do_action( 'envo_blocks_filter_rendered', $type );

		return sprintf(
			'<div %1$s>%2$s</div>',
			get_block_wrapper_attributes( array( 'class' => 'eb-filter eb-filter--' . $type . ' eb-filter--' . $display ) ),
			$head
		);
	}

	/**
	 * One option.
	 *
	 * @param array  $o       Options of the block.
	 * @param string $url     Address that toggles it.
	 * @param string $label   Text (escaped).
	 * @param int    $count   Products.
	 * @param bool   $on      Chosen.
	 * @param string $face    Swatch or stars (HTML).
	 * @param string $children Child options (HTML).
	 * @return string
	 */
	private static function option( $o, $url, $label, $count, $on, $face = '', $children = '' ) {
		if ( $count < 1 && ! $on && $o['hideEmpty'] ) {
			return '';
		}
		$check = 'checkbox' === $o['display'] ? '<span class="eb-filter__check" aria-hidden="true"></span>' : '';
		$name  = '<span class="eb-filter__name">' . $label . '</span>';
		$num   = $o['showCount'] ? '<span class="eb-filter__count">' . (int) $count . '</span>' : '';
		$inner = $check . $face . $name . $num;
		$link  = $count < 1 && ! $on
			? '<span class="eb-filter__link" aria-disabled="true">' . $inner . '</span>'
			: '<a class="eb-filter__link" href="' . esc_url( $url ) . '" rel="nofollow"' . ( $on ? ' aria-current="true"' : '' ) . '>' . $inner . '</a>';
		return '<li class="eb-filter__item' . ( $on ? ' is-active' : '' ) . ( $count < 1 ? ' is-empty' : '' ) . '">' . $link . $children . '</li>';
	}

	/**
	 * List of options, or a dropdown (PRO), with "Show more" after the limit.
	 *
	 * @param array    $o     Options of the block.
	 * @param string[] $items Options (HTML li) in order.
	 * @param array    $opts  For a dropdown: [ url, label, on ] per option, and the "all" address.
	 * @return string
	 */
	private static function wrap( $o, $items, $opts = array() ) {
		$items = array_values( array_filter( $items ) );
		if ( ! $items ) {
			return '';
		}
		if ( 'dropdown' === $o['display'] && $opts ) {
			$html = '<select class="eb-filter__select" aria-label="' . esc_attr( $o['title'] ? $o['title'] : __( 'Filter', 'envo-blocks' ) ) . '"><option value="' . esc_url( $opts['all'] ) . '">' . esc_html__( 'Any', 'envo-blocks' ) . '</option>';
			foreach ( $opts['items'] as $item ) {
				$html .= '<option value="' . esc_url( $item[0] ) . '"' . selected( $item[2], true, false ) . '>' . esc_html( $item[1] ) . '</option>';
			}
			return $html . '</select>';
		}
		$more = '';
		if ( $o['limit'] && count( $items ) > $o['limit'] ) {
			foreach ( $items as $i => $item ) {
				if ( $i >= $o['limit'] && false === strpos( $item, 'is-active' ) ) {
					$items[ $i ] = preg_replace( '/^<li class="/', '<li hidden class="is-more ', $item );
				}
			}
			$more = '<button type="button" class="eb-filter__more" data-less="' . esc_attr__( 'Show less', 'envo-blocks' ) . '">' . esc_html__( 'Show more', 'envo-blocks' ) . '</button>';
		}
		return '<ul class="eb-filter__list">' . implode( '', $items ) . '</ul>' . $more;
	}

	/**
	 * Category, brand or attribute options.
	 *
	 * @param string $taxonomy   Taxonomy.
	 * @param string $key        Filter key (cat, brand, attr:pa_x).
	 * @param array  $o          Options of the block.
	 * @param array  $attributes Block attributes.
	 * @return string
	 */
	private static function terms_filter( $taxonomy, $key, $o, $attributes ) {
		$active = Envo_Blocks_Filters::active();
		$chosen = isset( $active[ $key ] ) ? $active[ $key ]['values'] : array();
		$here   = Envo_Blocks_Filters::term_counts_here( $taxonomy );
		$counts = $active ? Envo_Blocks_Filters::term_counts( $taxonomy, $key ) : $here;
		$base   = Envo_Blocks_Filters::base_url();
		$attr   = 0 === strpos( $key, 'attr:' );
		$param  = $attr ? 'filter_' . wc_attribute_taxonomy_slug( $taxonomy ) : Envo_Blocks_Filters::params_of( $key )[0];
		$and    = $attr && isset( $attributes['queryType'] ) && 'and' === $attributes['queryType'];
		$multi  = 'dropdown' !== $o['display'];
		$tree   = is_taxonomy_hierarchical( $taxonomy );
		$depth  = $tree ? max( 1, min( 3, isset( $attributes['depth'] ) ? (int) $attributes['depth'] : 1 ) ) : 1;

		// Categories: the subcategories of the category of the page, else the top level.
		$parent = 0;
		if ( $tree && is_tax( $taxonomy ) ) {
			$parent = (int) get_queried_object_id();
		}
		$visual = 'swatches' === $o['display'] && $attr;
		$drop   = array(
			'all'   => self::url( $base, $param, array() ),
			'items' => array(),
		);

		$build = function ( $parent_id, $level ) use ( &$build, $taxonomy, $chosen, $here, $counts, $base, $param, $attr, $and, $multi, $tree, $depth, $o, $visual, &$drop ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'parent'     => $tree ? $parent_id : '',
					'orderby'    => $attr ? 'menu_order' : 'name',
				)
			);
			$items = array();
			foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
				$on = in_array( $term->slug, $chosen, true );
				if ( empty( $here[ $term->term_id ] ) && ! $on ) {
					continue; // Nothing on this page has it.
				}
				$values = self::toggle( $chosen, $term->slug, $multi );
				$url    = self::url( $base, $param, $values );
				if ( $attr ) {
					$type_param = 'query_type_' . wc_attribute_taxonomy_slug( $taxonomy );
					$url        = count( $values ) > 1 ? add_query_arg( $type_param, $and ? 'and' : 'or', $url ) : remove_query_arg( $type_param, $url );
				}
				$count = isset( $counts[ $term->term_id ] ) ? (int) $counts[ $term->term_id ] : 0;
				$face  = '';
				if ( $visual ) {
					/**
					 * Color or image of an attribute term for the swatches look (Envo Blocks PRO).
					 *
					 * @param array|null $visual  { color, color2, image } or null.
					 * @param WP_Term    $term    Term.
					 */
					$v = apply_filters( 'envo_blocks_filter_term_visual', null, $term );
					if ( is_array( $v ) && ! empty( $v['image'] ) ) {
						$face = '<span class="eb-filter__swatch eb-filter__swatch--image"><img src="' . esc_url( $v['image'] ) . '" alt="" loading="lazy" decoding="async"></span>';
					} elseif ( is_array( $v ) && ! empty( $v['color'] ) ) {
						$bg   = ! empty( $v['color2'] ) ? 'linear-gradient(135deg,' . $v['color'] . ' 50%,' . $v['color2'] . ' 50%)' : $v['color'];
						$face = '<span class="eb-filter__swatch" style="background:' . esc_attr( $bg ) . '"></span>';
					}
				}
				$children = $tree && $level < $depth ? array_filter( $build( (int) $term->term_id, $level + 1 ) ) : array();
				$children = $children ? '<ul class="eb-filter__children">' . implode( '', $children ) . '</ul>' : '';
				$drop['items'][] = array( $url, str_repeat( '— ', $level - 1 ) . $term->name, $on );
				$items[]         = self::option( $o, $url, esc_html( $term->name ), $count, $on, $face, $children );
			}
			return $items;
		};

		return self::wrap( $o, $build( $parent, 1 ), $drop );
	}

	/**
	 * Rating options (5 to 1 stars).
	 *
	 * @param array $o Options of the block.
	 * @return string
	 */
	private static function rating( $o ) {
		$active = Envo_Blocks_Filters::active();
		$chosen = isset( $active['rating'] ) ? $active['rating']['values'] : array();
		$here   = Envo_Blocks_Filters::rating_counts( true );
		$counts = $active ? Envo_Blocks_Filters::rating_counts() : $here;
		$base   = Envo_Blocks_Filters::base_url();
		$items  = array();
		$drop   = array(
			'all'   => self::url( $base, 'rating_filter', array() ),
			'items' => array(),
		);
		for ( $n = 5; $n >= 1; $n-- ) {
			$on = in_array( (string) $n, $chosen, true );
			if ( empty( $here[ $n ] ) && ! $on ) {
				continue;
			}
			$url = self::url( $base, 'rating_filter', self::toggle( $chosen, (string) $n, 'dropdown' !== $o['display'] ) );
			/* translators: %d: number of stars */
			$label           = sprintf( _n( 'Rated %d star', 'Rated %d stars', $n, 'envo-blocks' ), $n );
			$stars           = '<span class="eb-filter__stars" aria-hidden="true">' . str_repeat( '★', $n ) . '<span>' . str_repeat( '★', 5 - $n ) . '</span></span>';
			$drop['items'][] = array( $url, $label, $on );
			$items[]         = self::option( $o, $url, '<span class="screen-reader-text">' . esc_html( $label ) . '</span>', isset( $counts[ $n ] ) ? $counts[ $n ] : 0, $on, $stars );
		}
		return self::wrap( $o, $items, $drop );
	}

	/**
	 * Stock status options.
	 *
	 * @param array $o Options of the block.
	 * @return string
	 */
	private static function stock( $o ) {
		$active = Envo_Blocks_Filters::active();
		$chosen = isset( $active['stock'] ) ? $active['stock']['values'] : array();
		$here   = Envo_Blocks_Filters::stock_counts( true );
		$counts = $active ? Envo_Blocks_Filters::stock_counts() : $here;
		$base   = Envo_Blocks_Filters::base_url();
		$items  = array();
		$drop   = array(
			'all'   => self::url( $base, Envo_Blocks_Filters::STOCK, array() ),
			'items' => array(),
		);
		foreach ( wc_get_product_stock_status_options() as $status => $label ) {
			$on = in_array( $status, $chosen, true );
			if ( empty( $here[ $status ] ) && ! $on ) {
				continue;
			}
			$url             = self::url( $base, Envo_Blocks_Filters::STOCK, self::toggle( $chosen, $status, 'dropdown' !== $o['display'] ) );
			$drop['items'][] = array( $url, $label, $on );
			$items[]         = self::option( $o, $url, esc_html( $label ), isset( $counts[ $status ] ) ? $counts[ $status ] : 0, $on );
		}
		return self::wrap( $o, $items, $drop );
	}

	/**
	 * "On sale" option (PRO).
	 *
	 * @param array $o Options of the block.
	 * @return string
	 */
	private static function sale( $o ) {
		$active = Envo_Blocks_Filters::active();
		$on     = isset( $active['sale'] );
		if ( ! Envo_Blocks_Filters::sale_count( true ) && ! $on ) {
			return '';
		}
		$base = Envo_Blocks_Filters::base_url();
		$url  = $on ? remove_query_arg( Envo_Blocks_Filters::SALE, $base ) : add_query_arg( Envo_Blocks_Filters::SALE, '1', Envo_Blocks_Filters::first_page( $base ) );
		$o['display'] = 'dropdown' === $o['display'] ? 'checkbox' : $o['display'];
		return self::wrap( $o, array( self::option( $o, $url, esc_html__( 'On sale', 'envo-blocks' ), Envo_Blocks_Filters::sale_count(), $on ) ) );
	}

	/**
	 * Price: a range slider with two fields, sent as a GET form (the other filters and the sorting as hidden fields).
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	private static function price( $attributes ) {
		$range = Envo_Blocks_Filters::price_range();
		if ( ! $range || $range['max'] <= $range['min'] ) {
			return '';
		}
		$active = Envo_Blocks_Filters::active();
		$min    = isset( $active['price'] ) && null !== $active['price']['min'] ? $active['price']['min'] : $range['min'];
		$max    = isset( $active['price'] ) && null !== $active['price']['max'] ? $active['price']['max'] : $range['max'];
		$base   = Envo_Blocks_Filters::base_url();
		$step   = isset( $attributes['step'] ) ? max( 1, (int) $attributes['step'] ) : 1;
		$action = strtok( $base, '?' );
		$hidden = '';
		$query  = (string) wp_parse_url( $base, PHP_URL_QUERY );
		parse_str( $query, $vars );
		foreach ( $vars as $name => $value ) {
			if ( ! in_array( $name, array( 'min_price', 'max_price' ), true ) && is_scalar( $value ) ) {
				$hidden .= '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '">';
			}
		}
		$symbol = html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' );
		$field  = static function ( $name, $value, $label ) use ( $range, $step, $symbol ) {
			return '<label class="eb-filter__field"><span class="screen-reader-text">' . esc_html( $label ) . '</span><span class="eb-filter__currency" aria-hidden="true">' . esc_html( $symbol ) . '</span>'
				. '<input type="number" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '" min="' . esc_attr( (string) $range['min'] ) . '" max="' . esc_attr( (string) $range['max'] ) . '" step="' . esc_attr( (string) $step ) . '" inputmode="decimal"></label>';
		};
		return '<form class="eb-filter__price" method="get" action="' . esc_url( $action ) . '" data-min="' . esc_attr( (string) $range['min'] ) . '" data-max="' . esc_attr( (string) $range['max'] ) . '">'
			. $hidden
			. '<div class="eb-filter__slider" hidden>'
			. '<input type="range" class="eb-filter__range eb-filter__range--min" min="' . esc_attr( (string) $range['min'] ) . '" max="' . esc_attr( (string) $range['max'] ) . '" step="' . esc_attr( (string) $step ) . '" value="' . esc_attr( (string) $min ) . '" aria-label="' . esc_attr__( 'Lowest price', 'envo-blocks' ) . '">'
			. '<input type="range" class="eb-filter__range eb-filter__range--max" min="' . esc_attr( (string) $range['min'] ) . '" max="' . esc_attr( (string) $range['max'] ) . '" step="' . esc_attr( (string) $step ) . '" value="' . esc_attr( (string) $max ) . '" aria-label="' . esc_attr__( 'Highest price', 'envo-blocks' ) . '">'
			. '</div>'
			. '<div class="eb-filter__fields">' . $field( 'min_price', $min, __( 'Lowest price', 'envo-blocks' ) ) . '<span class="eb-filter__dash" aria-hidden="true">–</span>' . $field( 'max_price', $max, __( 'Highest price', 'envo-blocks' ) ) . '</div>'
			. '<button type="submit" class="eb-filter__apply wp-element-button">' . esc_html__( 'Apply', 'envo-blocks' ) . '</button>'
			. '</form>';
	}

	/**
	 * Renders the Active Filters block: a chip per chosen value (a click removes it) and "Clear all".
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function render_active( $attributes ) {
		if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
			return '';
		}
		$preview = Envo_Blocks_Blocks::is_preview();
		$base    = Envo_Blocks_Filters::base_url();
		$chips   = array();
		foreach ( ( $preview || ! Envo_Blocks_Filters::on_shop() ) ? array() : Envo_Blocks_Filters::active() as $key => $filter ) {
			$params = Envo_Blocks_Filters::params_of( $key );
			if ( 'price' === $key ) {
				$from    = null === $filter['min'] ? '' : wp_strip_all_tags( wc_price( $filter['min'], array( 'decimals' => 0 ) ) );
				$to      = null === $filter['max'] ? '' : wp_strip_all_tags( wc_price( $filter['max'], array( 'decimals' => 0 ) ) );
				if ( '' !== $from && '' !== $to ) {
					$range = $from . ' – ' . $to;
				} elseif ( '' !== $to ) {
					/* translators: %s: price */
					$range = sprintf( __( 'up to %s', 'envo-blocks' ), $to );
				} else {
					/* translators: %s: price */
					$range = sprintf( __( 'from %s', 'envo-blocks' ), $from );
				}
				$chips[] = array( __( 'Price', 'envo-blocks' ), $range, remove_query_arg( $params, $base ) );
				continue;
			}
			if ( 'sale' === $key ) {
				$chips[] = array( '', __( 'On sale', 'envo-blocks' ), remove_query_arg( $params, $base ) );
				continue;
			}
			foreach ( $filter['values'] as $value ) {
				$rest = array_values( array_diff( $filter['values'], array( $value ) ) );
				$url  = $rest ? add_query_arg( $params[0], implode( ',', $rest ), $base ) : remove_query_arg( $params, $base );
				if ( 'rating' === $key ) {
					/* translators: %s: number of stars */
					$chips[] = array( __( 'Rating', 'envo-blocks' ), sprintf( __( '%s stars', 'envo-blocks' ), $value ), $url );
				} elseif ( 'stock' === $key ) {
					$options = wc_get_product_stock_status_options();
					$chips[] = array( '', isset( $options[ $value ] ) ? $options[ $value ] : $value, $url );
				} else {
					$term    = get_term_by( 'slug', $value, $filter['taxonomy'] );
					$label   = 0 === strpos( $key, 'attr:' ) ? wc_attribute_label( $filter['taxonomy'] ) : '';
					$chips[] = array( $label, $term instanceof WP_Term ? $term->name : $value, $url );
				}
			}
		}
		if ( $preview ) {
			$chips = array(
				array( __( 'Color', 'envo-blocks' ), __( 'Blue', 'envo-blocks' ), '#' ),
				array( __( 'Price', 'envo-blocks' ), '10 – 50', '#' ),
			);
		}
		if ( ! $chips ) {
			return Envo_Blocks_Filters::on_shop() ? '<div ' . get_block_wrapper_attributes( array( 'class' => 'eb-active-filters' ) ) . ' hidden></div>' : '';
		}
		$items = '';
		foreach ( $chips as $chip ) {
			$items .= '<li><a class="eb-active-filters__chip" href="' . esc_url( Envo_Blocks_Filters::first_page( $chip[2] ) ) . '" rel="nofollow" aria-label="' . esc_attr(
				/* translators: %s: filter value */
				sprintf( __( 'Remove filter: %s', 'envo-blocks' ), $chip[1] )
			) . '">' . ( '' !== $chip[0] ? '<span class="eb-active-filters__label">' . esc_html( $chip[0] ) . ':</span> ' : '' ) . esc_html( $chip[1] ) . '<span class="eb-active-filters__x" aria-hidden="true">×</span></a></li>';
		}
		$clear = ! isset( $attributes['showClear'] ) || $attributes['showClear'];
		if ( $clear ) {
			$items .= '<li><a class="eb-active-filters__clear" href="' . esc_url( $preview ? '#' : Envo_Blocks_Filters::clear_url() ) . '" rel="nofollow">' . esc_html__( 'Clear all', 'envo-blocks' ) . '</a></li>';
		}
		$title = isset( $attributes['title'] ) ? trim( sanitize_text_field( $attributes['title'] ) ) : '';
		return sprintf(
			'<div %1$s>%2$s<ul class="eb-active-filters__list">%3$s</ul></div>',
			get_block_wrapper_attributes( array( 'class' => 'eb-active-filters' ) ),
			'' !== $title ? '<p class="eb-filter__title">' . esc_html( $title ) . '</p>' : '',
			$items
		);
	}
}
