<?php
/**
 * Brand colours of the site.
 *
 * The templates use named palette colours (eb-brand, eb-brand-light-85, eb-on-brand…). The palette of the chosen
 * template is recolored with the customer's colours and registered in the theme palette, so the global stylesheet
 * defines --wp--preset--color--eb-* on every page (front end and editor), in any block theme.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Palette.
 */
final class Envo_Blocks_Wizard_Palette {
	/**
	 * Option with the site palette (slug => value) after the store was built.
	 */
	const OPTION = 'envo_blocks_wizard_palette';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'wp_theme_json_data_theme', array( __CLASS__, 'register' ) );
		add_action( 'wp_head', array( __CLASS__, 'preview_css' ), 100 );
		// PRO blocks accept palette colours in their colour attributes.
		add_filter( 'ebp_allow_preset_colors', '__return_true' );
		// Preview frame of the wizard: no admin bar.
		if ( isset( $_GET['ebw_frame'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display switch only.
			add_filter( 'show_admin_bar', '__return_false' );
		}
	}

	/**
	 * The palette in use: the built site palette, else the palette of the template being prepared.
	 *
	 * @return array
	 */
	public static function current() {
		$site = get_option( self::OPTION );
		if ( is_array( $site ) && $site ) {
			return $site;
		}
		$template = Envo_Blocks_Wizard_Builder::state_template();
		return $template ? $template['palette'] : array();
	}

	/**
	 * Adds the eb-* colours to the theme palette (or updates them).
	 *
	 * @param WP_Theme_JSON_Data $theme_json Theme data.
	 * @return WP_Theme_JSON_Data
	 */
	public static function register( $theme_json ) {
		$slots = self::current();
		if ( ! $slots ) {
			return $theme_json;
		}
		$data = $theme_json->get_data();
		// get_data() keys presets by origin; update_with() takes a plain list and replaces the theme's list.
		$list = isset( $data['settings']['color']['palette']['theme'] ) ? $data['settings']['color']['palette']['theme'] : array();
		$list = array_values( array_filter( $list, function ( $item ) { return isset( $item['slug'] ) && 0 !== strpos( $item['slug'], 'eb-' ); } ) );
		foreach ( $slots as $slug => $value ) {
			$list[] = array( 'slug' => $slug, 'name' => self::label( $slug ), 'color' => $value );
		}
		return $theme_json->update_with(
			array(
				'version'  => isset( $data['version'] ) ? $data['version'] : 3,
				'settings' => array( 'color' => array( 'palette' => $list ) ),
			)
		);
	}

	/**
	 * Readable name of a slot for the colour pickers: eb-brand-light-85 → "Brand light 85".
	 *
	 * @param string $slug Slug.
	 * @return string
	 */
	public static function label( $slug ) {
		return ucfirst( str_replace( '-', ' ', substr( $slug, 3 ) ) );
	}

	/**
	 * Preview frame of the wizard: the template palette recolored with ?ebw_brand / ?ebw_accent.
	 */
	public static function preview_css() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only preview for editors.
		if ( ! isset( $_GET['ebw_brand'] ) || ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		$template = Envo_Blocks_Wizard_Builder::state_template();
		if ( ! $template ) {
			return;
		}
		$bases = array( 'brand' => self::hex_param( 'ebw_brand' ) );
		if ( isset( $_GET['ebw_accent'] ) ) {
			$bases['accent'] = self::hex_param( 'ebw_accent' );
		}
		// phpcs:enable
		echo '<style id="ebw-preview">' . self::css( self::recolor( $template['palette'], array_filter( $bases ) ) ) . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Checked in css().
		if ( isset( $_GET['ebw_thumb'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// Small page preview of the import step: the final look, without popups, cookie bar or entrance animations.
			echo '<style id="ebw-thumb">.ebp-pp,#scc-banner{display:none!important}html.envo-anim-js .envo-anim,html.envo-anim-js .envo-anim-item,.envo-anim,.envo-anim-item{opacity:1!important;transform:none!important;filter:none!important;animation:none!important;visibility:visible!important}*,*::before,*::after{transition:none!important;scroll-behavior:auto!important}html,body{overflow:hidden!important}</style>' . "\n";
		}
	}

	/**
	 * A hex colour from the query string.
	 *
	 * @param string $key Key.
	 * @return string #rrggbb or ''.
	 */
	private static function hex_param( $key ) {
		$v = strtolower( preg_replace( '/[^0-9a-f]/i', '', sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- Callers check isset().
		return 6 === strlen( $v ) ? '#' . $v : '';
	}

	/**
	 * CSS custom properties of a palette.
	 *
	 * @param array $palette Slug => value.
	 * @return string
	 */
	public static function css( $palette ) {
		$css = '';
		foreach ( $palette as $slug => $value ) {
			if ( preg_match( '/^eb-[a-z0-9-]+$/', $slug ) && ! preg_match( '/[<>{};]/', (string) $value ) ) {
				$css .= '--wp--preset--color--' . $slug . ':' . $value . ';';
			}
		}
		return $css ? ':root:root{' . $css . '}' : '';
	}

	/* -------------------------------------------------------------------------------------------------------- */
	/* Colour maths                                                                                              */
	/* -------------------------------------------------------------------------------------------------------- */

	/**
	 * #rrggbb or #rrggbbaa → array( r, g, b, a ) or null.
	 *
	 * @param string $hex Colour.
	 * @return array|null
	 */
	public static function rgb( $hex ) {
		if ( ! preg_match( '/^#?([0-9a-f]{6})([0-9a-f]{2})?$/i', trim( (string) $hex ), $m ) ) {
			return null;
		}
		$c   = array_map( 'hexdec', str_split( $m[1], 2 ) );
		$c[] = isset( $m[2] ) ? hexdec( $m[2] ) / 255 : 1.0;
		return $c;
	}

	/**
	 * Array → #rrggbb.
	 *
	 * @param array $c Colour.
	 * @return string
	 */
	public static function hex( $c ) {
		return sprintf( '#%02x%02x%02x', round( $c[0] ), round( $c[1] ), round( $c[2] ) );
	}

	/**
	 * The real colour of a palette value: #hex, var(--wp--preset--color--slug) or
	 * color-mix(in srgb, <value> N%, #fff|#000|transparent).
	 *
	 * @param string $value   Value.
	 * @param array  $palette Palette.
	 * @param int    $depth   Recursion guard.
	 * @return array|null
	 */
	public static function resolve( $value, $palette, $depth = 0 ) {
		$value = trim( (string) $value );
		if ( $depth > 5 ) {
			return null;
		}
		$fixed = array(
			'#fff'        => array( 255, 255, 255, 1.0 ),
			'#000'        => array( 0, 0, 0, 1.0 ),
			'transparent' => array( 0, 0, 0, 0.0 ),
		);
		if ( isset( $fixed[ $value ] ) ) {
			return $fixed[ $value ];
		}
		if ( preg_match( '/^var\(--wp--preset--color--([a-z0-9-]+)\)$/', $value, $m ) ) {
			return isset( $palette[ $m[1] ] ) ? self::resolve( $palette[ $m[1] ], $palette, $depth + 1 ) : null;
		}
		if ( preg_match( '/^color-mix\(in srgb,\s*(.+?)\s+([\d.]+)%,\s*(.+)\)$/', $value, $m ) ) {
			$a = self::resolve( $m[1], $palette, $depth + 1 );
			$b = self::resolve( $m[3], $palette, $depth + 1 );
			if ( ! $a || ! $b ) {
				return null;
			}
			$p = $m[2] / 100;
			if ( 0.0 === (float) $b[3] ) {
				return array( $a[0], $a[1], $a[2], $a[3] * $p );
			}
			return self::mix( $a, $b, $p );
		}
		// Relative colour hsl(from <value> calc(h ± dh) calc(s ± ds) calc(l ± dl)) – an exact shade of the base.
		if ( preg_match( '/^hsl\(from\s+(var\([^)]+\))\s+(.+)\)$/', $value, $m ) ) {
			$base = self::resolve( $m[1], $palette, $depth + 1 );
			if ( ! $base ) {
				return null;
			}
			$hsl = self::to_hsl( $base );
			foreach ( array( 'h', 's', 'l' ) as $i => $name ) {
				if ( preg_match( '/calc\(' . $name . '\s*([+-])\s*([\d.]+)\)/', $m[2], $d ) ) {
					$hsl[ $i ] += ( '-' === $d[1] ? -1 : 1 ) * (float) $d[2];
				}
			}
			return self::from_hsl( fmod( $hsl[0] + 360, 360 ), max( 0, min( 100, $hsl[1] ) ), max( 0, min( 100, $hsl[2] ) ) );
		}
		return self::rgb( $value );
	}

	/**
	 * RGB → array( h 0–360, s 0–100, l 0–100 ).
	 *
	 * @param array $c Colour.
	 * @return array
	 */
	private static function to_hsl( $c ) {
		list( $r, $g, $b ) = array( $c[0] / 255, $c[1] / 255, $c[2] / 255 );
		$max = max( $r, $g, $b );
		$min = min( $r, $g, $b );
		$l   = ( $max + $min ) / 2;
		$d   = $max - $min;
		if ( $d < 1e-9 ) {
			return array( 0, 0, $l * 100 );
		}
		$s = $d / ( 1 - abs( 2 * $l - 1 ) );
		if ( $max === $r ) {
			$h = fmod( ( $g - $b ) / $d + 6, 6 );
		} elseif ( $max === $g ) {
			$h = ( $b - $r ) / $d + 2;
		} else {
			$h = ( $r - $g ) / $d + 4;
		}
		return array( $h * 60, $s * 100, $l * 100 );
	}

	/**
	 * HSL (h 0–360, s and l 0–100) → array( r, g, b, 1 ).
	 *
	 * @param float $h Hue.
	 * @param float $s Saturation.
	 * @param float $l Lightness.
	 * @return array
	 */
	private static function from_hsl( $h, $s, $l ) {
		$s  /= 100;
		$l  /= 100;
		$c   = ( 1 - abs( 2 * $l - 1 ) ) * $s;
		$x   = $c * ( 1 - abs( fmod( $h / 60, 2 ) - 1 ) );
		$m   = $l - $c / 2;
		$rgb = array( array( $c, $x, 0 ), array( $x, $c, 0 ), array( 0, $c, $x ), array( 0, $x, $c ), array( $x, 0, $c ), array( $c, 0, $x ) )[ min( 5, (int) floor( $h / 60 ) ) ];
		return array( ( $rgb[0] + $m ) * 255, ( $rgb[1] + $m ) * 255, ( $rgb[2] + $m ) * 255, 1.0 );
	}

	/**
	 * WCAG contrast of a (possibly semi-transparent) colour on an opaque background.
	 *
	 * @param array $fg Text.
	 * @param array $bg Background.
	 * @return float
	 */
	public static function contrast( $fg, $bg ) {
		$a   = isset( $fg[3] ) ? $fg[3] : 1.0;
		$fg  = array( $fg[0] * $a + $bg[0] * ( 1 - $a ), $fg[1] * $a + $bg[1] * ( 1 - $a ), $fg[2] * $a + $bg[2] * ( 1 - $a ) );
		$lum = function ( $c ) {
			$f = function ( $v ) {
				$v /= 255;
				return $v <= 0.03928 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 );
			};
			return 0.2126 * $f( $c[0] ) + 0.7152 * $f( $c[1] ) + 0.0722 * $f( $c[2] );
		};
		$x = $lum( $fg );
		$y = $lum( $bg );
		return ( max( $x, $y ) + 0.05 ) / ( min( $x, $y ) + 0.05 );
	}

	/**
	 * Like color-mix(in srgb, a p, b).
	 *
	 * @param array $a Colour.
	 * @param array $b Colour.
	 * @param float $p Share of a.
	 * @return array
	 */
	public static function mix( $a, $b, $p ) {
		return array( $a[0] * $p + $b[0] * ( 1 - $p ), $a[1] * $p + $b[1] * ( 1 - $p ), $a[2] * $p + $b[2] * ( 1 - $p ), 1.0 );
	}

	/**
	 * Recolors a template palette. New bases replace eb-brand / eb-accent, the shades follow by themselves
	 * (color-mix). The slots that depend on contrast keep the template's own value while it is at least as
	 * readable as in the original design (capped at WCAG AA 4.5:1), otherwise:
	 *   eb-on-X(-n)   text on the base – the better of white / the template's darkest text colour (secondary texts
	 *                 a softer mix of it with the background when that is still readable);
	 *   eb-X-text     the base as text on white – darkened in 5 % steps;
	 *   eb-X-on-dark  the base as text on dark sections – lightened in 5 % steps.
	 *
	 * @param array $palette Template palette.
	 * @param array $bases   'brand' / 'accent' => #rrggbb.
	 * @return array
	 */
	public static function recolor( $palette, $bases ) {
		$white = array( 255, 255, 255, 1.0 );
		$ink   = null;
		foreach ( $palette as $slug => $value ) {
			$c = self::rgb( $value );
			if ( 0 === strpos( $slug, 'eb-on-' ) && $c && 1.0 === (float) $c[3] && ( ! $ink || self::contrast( $c, $white ) > self::contrast( $ink, $white ) ) ) {
				$ink = $c; // Darkest text colour of the template.
			}
		}
		if ( ! $ink || self::contrast( $ink, $white ) < 7 ) {
			$ink = array( 17, 17, 17, 1.0 );
		}
		$old = $palette;
		foreach ( $bases as $name => $new ) {
			if ( isset( $palette[ 'eb-' . $name ] ) && self::rgb( $new ) ) {
				$palette[ 'eb-' . $name ] = $new;
			}
		}
		foreach ( array_keys( $bases ) as $name ) {
			$slug = 'eb-' . $name;
			if ( ! isset( $old[ $slug ] ) ) {
				continue;
			}
			$old_base = self::resolve( $old[ $slug ], $old );
			$new_base = self::resolve( $palette[ $slug ], $palette );
			$main     = null;
			foreach ( array_keys( $palette ) as $s ) {
				if ( ! preg_match( '/^eb-on-' . $name . '(-\d+)?$/', $s ) ) {
					continue;
				}
				$was  = self::resolve( $old[ $s ], $old );
				$now  = self::resolve( $palette[ $s ], $palette );
				$need = min( 4.5, self::contrast( $was, $old_base ) );
				if ( self::contrast( $now, $new_base ) < $need ) {
					$best = self::contrast( $white, $new_base ) >= self::contrast( $ink, $new_base ) ? $white : $ink;
					if ( null !== $main ) {
						$soft = self::mix( $main, $new_base, 0.75 );
						$best = self::contrast( $soft, $new_base ) >= $need ? $soft : $main;
					}
					$palette[ $s ] = self::hex( $best );
				}
				if ( null === $main ) {
					$main = self::resolve( $palette[ $s ], $palette );
				}
			}
			foreach ( array( '-text' => array( $white, array( 0, 0, 0, 1.0 ) ), '-on-dark' => array( $ink, $white ) ) as $suffix => $pair ) {
				if ( ! isset( $palette[ $slug . $suffix ] ) ) {
					continue;
				}
				list( $bg, $towards ) = $pair;
				$need = min( 4.5, self::contrast( $old_base, $bg ) );
				$text = $new_base;
				for ( $p = 0.95; self::contrast( $text, $bg ) < $need && $p > 0.1; $p -= 0.05 ) {
					$text = self::mix( $new_base, $towards, $p );
				}
				$palette[ $slug . $suffix ] = $text === $new_base ? 'var(--wp--preset--color--' . $slug . ')' : self::hex( $text );
			}
		}
		return $palette;
	}

	/**
	 * Short readable summary of the contrast decisions (shown under the colour pickers).
	 *
	 * @param array $palette Recolored palette.
	 * @return array
	 */
	public static function summary( $palette ) {
		$out = array();
		foreach ( array( 'brand', 'accent' ) as $name ) {
			if ( ! isset( $palette[ 'eb-on-' . $name ] ) ) {
				continue;
			}
			$on            = self::resolve( $palette[ 'eb-on-' . $name ], $palette );
			$base          = self::resolve( $palette[ 'eb-' . $name ], $palette );
			$out[ $name ] = array(
				'text'     => $on ? self::hex( $on ) : '',
				'contrast' => $on && $base ? round( self::contrast( $on, $base ), 1 ) : 0,
			);
		}
		return $out;
	}
}
