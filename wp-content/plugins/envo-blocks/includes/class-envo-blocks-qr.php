<?php
/**
 * QR code generator (byte mode, error correction L/M/Q/H, versions 1–40, automatic mask) with SVG and PNG output.
 * Everything runs on the site: no data leaves the server.
 *
 * Based on the algorithm of "QR Code generator library" by Project Nayuki (MIT License,
 * https://www.nayuki.io/page/qr-code-generator-library), rewritten in PHP for PHP 7.4+.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * QR code.
 */
final class Envo_Blocks_QR {

	/**
	 * Error correction codewords per block, by level and version (index 0 unused).
	 */
	const ECC_PER_BLOCK = array(
		'L' => array( -1, 7, 10, 15, 20, 26, 18, 20, 24, 30, 18, 20, 24, 26, 30, 22, 24, 28, 30, 28, 28, 28, 28, 30, 30, 26, 28, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30 ),
		'M' => array( -1, 10, 16, 26, 18, 24, 16, 18, 22, 22, 26, 30, 22, 22, 24, 24, 28, 28, 26, 26, 26, 26, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28 ),
		'Q' => array( -1, 13, 22, 18, 26, 18, 24, 18, 22, 20, 24, 28, 26, 24, 20, 30, 24, 28, 28, 26, 30, 28, 30, 30, 30, 30, 28, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30 ),
		'H' => array( -1, 17, 28, 22, 16, 22, 28, 26, 26, 24, 28, 24, 28, 22, 24, 24, 30, 28, 28, 26, 28, 30, 24, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30 ),
	);

	/**
	 * Number of error correction blocks, by level and version (index 0 unused).
	 */
	const BLOCKS = array(
		'L' => array( -1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 4, 4, 4, 4, 4, 6, 6, 6, 6, 7, 8, 8, 9, 9, 10, 12, 12, 12, 13, 14, 15, 16, 17, 18, 19, 19, 20, 21, 22, 24, 25 ),
		'M' => array( -1, 1, 1, 1, 2, 2, 4, 4, 4, 5, 5, 5, 8, 9, 9, 10, 10, 11, 13, 14, 16, 17, 17, 18, 20, 21, 23, 25, 26, 28, 29, 31, 33, 35, 37, 38, 40, 43, 45, 47, 49 ),
		'Q' => array( -1, 1, 1, 2, 2, 4, 4, 6, 6, 8, 8, 8, 10, 12, 16, 12, 17, 16, 18, 21, 20, 23, 23, 25, 27, 29, 34, 34, 35, 38, 40, 43, 45, 48, 51, 53, 56, 59, 62, 65, 68 ),
		'H' => array( -1, 1, 1, 2, 4, 4, 4, 5, 6, 8, 8, 11, 11, 16, 16, 18, 16, 19, 21, 25, 25, 25, 34, 30, 32, 35, 37, 40, 42, 45, 48, 51, 54, 57, 60, 63, 66, 70, 74, 77, 81 ),
	);

	/**
	 * Format bits of the levels.
	 */
	const FORMAT = array(
		'L' => 1,
		'M' => 0,
		'Q' => 3,
		'H' => 2,
	);

	/**
	 * Modules (true = dark).
	 *
	 * @var bool[][]
	 */
	private $modules = array();

	/**
	 * Function pattern modules (not masked).
	 *
	 * @var bool[][]
	 */
	private $function = array();

	/**
	 * Size in modules.
	 *
	 * @var int
	 */
	private $size = 0;

	/**
	 * Version 1–40.
	 *
	 * @var int
	 */
	private $version = 1;

	/**
	 * Error correction level.
	 *
	 * @var string
	 */
	private $ecl = 'M';

	/**
	 * Mask 0–7.
	 *
	 * @var int
	 */
	private $mask = 0;

	/* ------------------------------------------------------------------ */
	/* Public API                                                         */
	/* ------------------------------------------------------------------ */

	/**
	 * Encodes text (UTF-8 bytes) into a QR code.
	 *
	 * @param string   $text Text.
	 * @param string   $ecl  Error correction level L, M, Q or H.
	 * @param int|null $mask Mask 0–7, or null for the best one.
	 * @return self|null Null when the text is too long.
	 */
	public static function encode( $text, $ecl = 'M', $mask = null ) {
		$ecl   = isset( self::FORMAT[ $ecl ] ) ? $ecl : 'M';
		$bytes = array_values( unpack( 'C*', (string) $text ) ?: array() );
		$len   = count( $bytes );

		for ( $version = 1; $version <= 40; $version++ ) {
			$capacity = self::data_codewords( $version, $ecl ) * 8;
			$count    = $version < 10 ? 8 : 16;
			if ( 4 + $count + $len * 8 <= $capacity ) {
				break;
			}
		}
		if ( $version > 40 ) {
			return null;
		}

		// Bit stream: byte mode, length, data, terminator, padding.
		$bits = array();
		self::append_bits( $bits, 4, 4 );
		self::append_bits( $bits, $len, $count );
		foreach ( $bytes as $b ) {
			self::append_bits( $bits, $b, 8 );
		}
		$capacity = self::data_codewords( $version, $ecl ) * 8;
		self::append_bits( $bits, 0, min( 4, $capacity - count( $bits ) ) );
		self::append_bits( $bits, 0, ( 8 - count( $bits ) % 8 ) % 8 );
		for ( $pad = 0xEC; count( $bits ) < $capacity; $pad ^= 0xEC ^ 0x11 ) {
			self::append_bits( $bits, $pad, 8 );
		}
		$data = array();
		foreach ( array_chunk( $bits, 8 ) as $byte ) {
			$value = 0;
			foreach ( $byte as $bit ) {
				$value = ( $value << 1 ) | $bit;
			}
			$data[] = $value;
		}

		$qr          = new self();
		$qr->version = $version;
		$qr->ecl     = $ecl;
		$qr->size    = $version * 4 + 17;
		$qr->build( self::add_ecc( $data, $version, $ecl ), $mask );
		return $qr;
	}

	/**
	 * Size in modules (without the quiet zone).
	 *
	 * @return int
	 */
	public function size() {
		return $this->size;
	}

	/**
	 * Version 1–40.
	 *
	 * @return int
	 */
	public function version() {
		return $this->version;
	}

	/**
	 * Mask used.
	 *
	 * @return int
	 */
	public function mask() {
		return $this->mask;
	}

	/**
	 * Whether a module is dark (outside of the code: light).
	 *
	 * @param int $x Column.
	 * @param int $y Row.
	 * @return bool
	 */
	public function get( $x, $y ) {
		return $x >= 0 && $y >= 0 && $x < $this->size && $y < $this->size && $this->modules[ $y ][ $x ];
	}

	/**
	 * SVG with one path (scales without blur).
	 *
	 * @param int    $margin Quiet zone in modules (4 by the standard).
	 * @param string $label  Accessible name (empty = decorative).
	 * @param string $dark   Color of the dark modules.
	 * @param string $light  Background color.
	 * @return string
	 */
	public function svg( $margin = 4, $label = '', $dark = '#000000', $light = '#ffffff' ) {
		$total = $this->size + 2 * $margin;
		$path  = '';
		for ( $y = 0; $y < $this->size; $y++ ) {
			for ( $x = 0; $x < $this->size; $x++ ) {
				if ( $this->modules[ $y ][ $x ] ) {
					$path .= 'M' . ( $x + $margin ) . ',' . ( $y + $margin ) . 'h1v1h-1z';
				}
			}
		}
		$aria = '' === $label ? ' aria-hidden="true" focusable="false"' : ' role="img" aria-label="' . esc_attr( $label ) . '"';
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $total . ' ' . $total . '" shape-rendering="crispEdges"' . $aria . '>'
			. '<rect width="100%" height="100%" fill="' . esc_attr( $light ) . '"/>'
			. '<path d="' . $path . '" fill="' . esc_attr( $dark ) . '"/></svg>';
	}

	/**
	 * PNG image data (needs the GD extension), or '' without GD.
	 *
	 * @param int $scale  Pixels per module.
	 * @param int $margin Quiet zone in modules.
	 * @return string
	 */
	public function png( $scale = 6, $margin = 4 ) {
		if ( ! function_exists( 'imagecreate' ) || ! function_exists( 'imagepng' ) ) {
			return '';
		}
		$scale = max( 1, (int) $scale );
		$total = ( $this->size + 2 * $margin ) * $scale;
		$image = imagecreate( $total, $total );
		$white = imagecolorallocate( $image, 255, 255, 255 );
		$black = imagecolorallocate( $image, 0, 0, 0 );
		imagefill( $image, 0, 0, $white );
		for ( $y = 0; $y < $this->size; $y++ ) {
			for ( $x = 0; $x < $this->size; $x++ ) {
				if ( $this->modules[ $y ][ $x ] ) {
					$left = ( $x + $margin ) * $scale;
					$top  = ( $y + $margin ) * $scale;
					imagefilledrectangle( $image, $left, $top, $left + $scale - 1, $top + $scale - 1, $black );
				}
			}
		}
		ob_start();
		imagepng( $image );
		$data = (string) ob_get_clean();
		imagedestroy( $image );
		return $data;
	}

	/* ------------------------------------------------------------------ */
	/* Codewords                                                          */
	/* ------------------------------------------------------------------ */

	/**
	 * Appends the low bits of a value.
	 *
	 * @param int[] $bits  Bit list.
	 * @param int   $value Value.
	 * @param int   $count Number of bits.
	 */
	private static function append_bits( array &$bits, $value, $count ) {
		for ( $i = $count - 1; $i >= 0; $i-- ) {
			$bits[] = ( $value >> $i ) & 1;
		}
	}

	/**
	 * Modules for data and error correction of a version.
	 *
	 * @param int $version Version.
	 * @return int
	 */
	private static function raw_modules( $version ) {
		$result = ( 16 * $version + 128 ) * $version + 64;
		if ( $version >= 2 ) {
			$align   = intdiv( $version, 7 ) + 2;
			$result -= ( 25 * $align - 10 ) * $align - 55;
			if ( $version >= 7 ) {
				$result -= 36;
			}
		}
		return $result;
	}

	/**
	 * Data codewords of a version and level.
	 *
	 * @param int    $version Version.
	 * @param string $ecl     Level.
	 * @return int
	 */
	private static function data_codewords( $version, $ecl ) {
		return intdiv( self::raw_modules( $version ), 8 ) - self::ECC_PER_BLOCK[ $ecl ][ $version ] * self::BLOCKS[ $ecl ][ $version ];
	}

	/**
	 * Splits the data into blocks, adds Reed-Solomon error correction and interleaves them.
	 *
	 * @param int[]  $data    Data codewords.
	 * @param int    $version Version.
	 * @param string $ecl     Level.
	 * @return int[]
	 */
	private static function add_ecc( array $data, $version, $ecl ) {
		$blocks_count = self::BLOCKS[ $ecl ][ $version ];
		$ecc_len      = self::ECC_PER_BLOCK[ $ecl ][ $version ];
		$raw          = intdiv( self::raw_modules( $version ), 8 );
		$short_count  = $blocks_count - $raw % $blocks_count;
		$short_len    = intdiv( $raw, $blocks_count );
		$divisor      = self::rs_divisor( $ecc_len );

		$blocks = array();
		$k      = 0;
		for ( $i = 0; $i < $blocks_count; $i++ ) {
			$length = $short_len - $ecc_len + ( $i < $short_count ? 0 : 1 );
			$chunk  = array_slice( $data, $k, $length );
			$k     += $length;
			$ecc    = self::rs_remainder( $chunk, $divisor );
			if ( $i < $short_count ) {
				$chunk[] = -1; // Placeholder so all blocks are the same length.
			}
			$blocks[] = array_merge( $chunk, $ecc );
		}

		$result = array();
		$length = count( $blocks[0] );
		for ( $i = 0; $i < $length; $i++ ) {
			foreach ( $blocks as $j => $block ) {
				if ( $i !== $short_len - $ecc_len || $j >= $short_count ) {
					$result[] = $block[ $i ];
				}
			}
		}
		return $result;
	}

	/**
	 * Reed-Solomon generator polynomial of a degree.
	 *
	 * @param int $degree Degree.
	 * @return int[]
	 */
	private static function rs_divisor( $degree ) {
		$result                = array_fill( 0, $degree, 0 );
		$result[ $degree - 1 ] = 1;
		$root                  = 1;
		for ( $i = 0; $i < $degree; $i++ ) {
			for ( $j = 0; $j < $degree; $j++ ) {
				$result[ $j ] = self::rs_multiply( $result[ $j ], $root );
				if ( $j + 1 < $degree ) {
					$result[ $j ] ^= $result[ $j + 1 ];
				}
			}
			$root = self::rs_multiply( $root, 0x02 );
		}
		return $result;
	}

	/**
	 * Reed-Solomon remainder of data by the divisor.
	 *
	 * @param int[] $data    Data.
	 * @param int[] $divisor Divisor.
	 * @return int[]
	 */
	private static function rs_remainder( array $data, array $divisor ) {
		$result = array_fill( 0, count( $divisor ), 0 );
		foreach ( $data as $b ) {
			$factor = $b ^ array_shift( $result );
			$result[] = 0;
			foreach ( $divisor as $i => $coef ) {
				$result[ $i ] ^= self::rs_multiply( $coef, $factor );
			}
		}
		return $result;
	}

	/**
	 * Multiplication in GF(2^8) with the polynomial 0x11D.
	 *
	 * @param int $x Factor.
	 * @param int $y Factor.
	 * @return int
	 */
	private static function rs_multiply( $x, $y ) {
		$z = 0;
		for ( $i = 7; $i >= 0; $i-- ) {
			$z  = ( $z << 1 ) ^ ( ( $z >> 7 ) * 0x11D );
			$z ^= ( ( $y >> $i ) & 1 ) * $x;
		}
		return $z;
	}

	/* ------------------------------------------------------------------ */
	/* Matrix                                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * Draws everything and picks the mask.
	 *
	 * @param int[]    $codewords All codewords.
	 * @param int|null $mask      Forced mask or null.
	 */
	private function build( array $codewords, $mask ) {
		$this->modules  = array_fill( 0, $this->size, array_fill( 0, $this->size, false ) );
		$this->function = $this->modules;
		$this->draw_function_patterns();
		$this->draw_codewords( $codewords );

		if ( null === $mask ) {
			$best = PHP_INT_MAX;
			for ( $m = 0; $m < 8; $m++ ) {
				$this->apply_mask( $m );
				$this->draw_format_bits( $m );
				$penalty = $this->penalty();
				if ( $penalty < $best ) {
					$mask = $m;
					$best = $penalty;
				}
				$this->apply_mask( $m ); // XOR again undoes it.
			}
		}
		$this->mask = (int) $mask;
		$this->apply_mask( $this->mask );
		$this->draw_format_bits( $this->mask );
	}

	/**
	 * Sets a function module.
	 *
	 * @param int  $x    Column.
	 * @param int  $y    Row.
	 * @param bool $dark Dark.
	 */
	private function set_function( $x, $y, $dark ) {
		$this->modules[ $y ][ $x ]  = (bool) $dark;
		$this->function[ $y ][ $x ] = true;
	}

	/**
	 * Timing, finder and alignment patterns, format and version areas.
	 */
	private function draw_function_patterns() {
		for ( $i = 0; $i < $this->size; $i++ ) {
			$this->set_function( 6, $i, 0 === $i % 2 );
			$this->set_function( $i, 6, 0 === $i % 2 );
		}
		$this->draw_finder( 3, 3 );
		$this->draw_finder( $this->size - 4, 3 );
		$this->draw_finder( 3, $this->size - 4 );

		$positions = $this->alignment_positions();
		$count     = count( $positions );
		for ( $i = 0; $i < $count; $i++ ) {
			for ( $j = 0; $j < $count; $j++ ) {
				if ( ! ( ( 0 === $i && 0 === $j ) || ( 0 === $i && $count - 1 === $j ) || ( $count - 1 === $i && 0 === $j ) ) ) {
					$this->draw_alignment( $positions[ $i ], $positions[ $j ] );
				}
			}
		}
		$this->draw_format_bits( 0 ); // Reserved now, drawn again with the chosen mask.
		$this->draw_version();
	}

	/**
	 * A finder pattern with its separator.
	 *
	 * @param int $x Center column.
	 * @param int $y Center row.
	 */
	private function draw_finder( $x, $y ) {
		for ( $dy = -4; $dy <= 4; $dy++ ) {
			for ( $dx = -4; $dx <= 4; $dx++ ) {
				$dist = max( abs( $dx ), abs( $dy ) );
				$xx   = $x + $dx;
				$yy   = $y + $dy;
				if ( $xx >= 0 && $xx < $this->size && $yy >= 0 && $yy < $this->size ) {
					$this->set_function( $xx, $yy, 2 !== $dist && 4 !== $dist );
				}
			}
		}
	}

	/**
	 * An alignment pattern.
	 *
	 * @param int $x Center column.
	 * @param int $y Center row.
	 */
	private function draw_alignment( $x, $y ) {
		for ( $dy = -2; $dy <= 2; $dy++ ) {
			for ( $dx = -2; $dx <= 2; $dx++ ) {
				$this->set_function( $x + $dx, $y + $dy, 1 !== max( abs( $dx ), abs( $dy ) ) );
			}
		}
	}

	/**
	 * Centers of the alignment patterns.
	 *
	 * @return int[]
	 */
	private function alignment_positions() {
		if ( 1 === $this->version ) {
			return array();
		}
		$count  = intdiv( $this->version, 7 ) + 2;
		$step   = intdiv( $this->version * 8 + $count * 3 + 5, $count * 4 - 4 ) * 2;
		$result = array( 6 );
		for ( $pos = $this->size - 7; count( $result ) < $count; $pos -= $step ) {
			array_splice( $result, 1, 0, array( $pos ) );
		}
		return $result;
	}

	/**
	 * Format bits (level and mask) in both copies.
	 *
	 * @param int $mask Mask.
	 */
	private function draw_format_bits( $mask ) {
		$data = ( self::FORMAT[ $this->ecl ] << 3 ) | $mask;
		$rem  = $data;
		for ( $i = 0; $i < 10; $i++ ) {
			$rem = ( $rem << 1 ) ^ ( ( $rem >> 9 ) * 0x537 );
		}
		$bits = ( ( $data << 10 ) | $rem ) ^ 0x5412;

		for ( $i = 0; $i <= 5; $i++ ) {
			$this->set_function( 8, $i, ( $bits >> $i ) & 1 );
		}
		$this->set_function( 8, 7, ( $bits >> 6 ) & 1 );
		$this->set_function( 8, 8, ( $bits >> 7 ) & 1 );
		$this->set_function( 7, 8, ( $bits >> 8 ) & 1 );
		for ( $i = 9; $i < 15; $i++ ) {
			$this->set_function( 14 - $i, 8, ( $bits >> $i ) & 1 );
		}
		for ( $i = 0; $i < 8; $i++ ) {
			$this->set_function( $this->size - 1 - $i, 8, ( $bits >> $i ) & 1 );
		}
		for ( $i = 8; $i < 15; $i++ ) {
			$this->set_function( 8, $this->size - 15 + $i, ( $bits >> $i ) & 1 );
		}
		$this->set_function( 8, $this->size - 8, true ); // Always dark.
	}

	/**
	 * Version information (version 7 and up).
	 */
	private function draw_version() {
		if ( $this->version < 7 ) {
			return;
		}
		$rem = $this->version;
		for ( $i = 0; $i < 12; $i++ ) {
			$rem = ( $rem << 1 ) ^ ( ( $rem >> 11 ) * 0x1F25 );
		}
		$bits = ( $this->version << 12 ) | $rem;
		for ( $i = 0; $i < 18; $i++ ) {
			$bit = ( $bits >> $i ) & 1;
			$a   = $this->size - 11 + $i % 3;
			$b   = intdiv( $i, 3 );
			$this->set_function( $a, $b, $bit );
			$this->set_function( $b, $a, $bit );
		}
	}

	/**
	 * Places the codewords in the zigzag order.
	 *
	 * @param int[] $codewords Codewords.
	 */
	private function draw_codewords( array $codewords ) {
		$i     = 0;
		$total = count( $codewords ) * 8;
		for ( $right = $this->size - 1; $right >= 1; $right -= 2 ) {
			if ( 6 === $right ) {
				$right = 5;
			}
			for ( $vert = 0; $vert < $this->size; $vert++ ) {
				for ( $j = 0; $j < 2; $j++ ) {
					$x      = $right - $j;
					$upward = 0 === ( ( $right + 1 ) & 2 );
					$y      = $upward ? $this->size - 1 - $vert : $vert;
					if ( ! $this->function[ $y ][ $x ] && $i < $total ) {
						$this->modules[ $y ][ $x ] = 1 === ( ( $codewords[ $i >> 3 ] >> ( 7 - ( $i & 7 ) ) ) & 1 );
						++$i;
					}
				}
			}
		}
	}

	/**
	 * XORs a mask over the non-function modules.
	 *
	 * @param int $mask Mask 0–7.
	 */
	private function apply_mask( $mask ) {
		for ( $y = 0; $y < $this->size; $y++ ) {
			for ( $x = 0; $x < $this->size; $x++ ) {
				switch ( $mask ) {
					case 0:
						$invert = 0 === ( $x + $y ) % 2;
						break;
					case 1:
						$invert = 0 === $y % 2;
						break;
					case 2:
						$invert = 0 === $x % 3;
						break;
					case 3:
						$invert = 0 === ( $x + $y ) % 3;
						break;
					case 4:
						$invert = 0 === ( intdiv( $x, 3 ) + intdiv( $y, 2 ) ) % 2;
						break;
					case 5:
						$invert = 0 === $x * $y % 2 + $x * $y % 3;
						break;
					case 6:
						$invert = 0 === ( $x * $y % 2 + $x * $y % 3 ) % 2;
						break;
					default:
						$invert = 0 === ( ( $x + $y ) % 2 + $x * $y % 3 ) % 2;
				}
				if ( $invert && ! $this->function[ $y ][ $x ] ) {
					$this->modules[ $y ][ $x ] = ! $this->modules[ $y ][ $x ];
				}
			}
		}
	}

	/**
	 * Penalty score of the current matrix (lower is better).
	 *
	 * @return int
	 */
	private function penalty() {
		$result = 0;
		$size   = $this->size;

		foreach ( array( true, false ) as $rows ) {
			for ( $a = 0; $a < $size; $a++ ) {
				$color   = false;
				$run     = 0;
				$history = array( 0, 0, 0, 0, 0, 0, 0 );
				for ( $b = 0; $b < $size; $b++ ) {
					$cell = $rows ? $this->modules[ $a ][ $b ] : $this->modules[ $b ][ $a ];
					if ( $cell === $color ) {
						++$run;
						if ( 5 === $run ) {
							$result += 3;
						} elseif ( $run > 5 ) {
							++$result;
						}
					} else {
						$this->finder_history_add( $run, $history );
						if ( ! $color ) {
							$result += $this->finder_count( $history ) * 40;
						}
						$color = $cell;
						$run   = 1;
					}
				}
				$result += $this->finder_terminate( $color, $run, $history ) * 40;
			}
		}

		for ( $y = 0; $y < $size - 1; $y++ ) {
			for ( $x = 0; $x < $size - 1; $x++ ) {
				$color = $this->modules[ $y ][ $x ];
				if ( $color === $this->modules[ $y ][ $x + 1 ] && $color === $this->modules[ $y + 1 ][ $x ] && $color === $this->modules[ $y + 1 ][ $x + 1 ] ) {
					$result += 3;
				}
			}
		}

		$dark = 0;
		foreach ( $this->modules as $row ) {
			foreach ( $row as $cell ) {
				$dark += $cell ? 1 : 0;
			}
		}
		$total   = $size * $size;
		$k       = intdiv( abs( $dark * 20 - $total * 10 ) + $total - 1, $total ) - 1;
		$result += $k * 10;
		return $result;
	}

	/**
	 * Finder-like patterns ending at the current run.
	 *
	 * @param int[] $h Run history.
	 * @return int
	 */
	private function finder_count( array $h ) {
		$n    = $h[1];
		$core = $n > 0 && $h[2] === $n && $h[3] === $n * 3 && $h[4] === $n && $h[5] === $n;
		return ( $core && $h[0] >= $n * 4 && $h[6] >= $n ? 1 : 0 ) + ( $core && $h[6] >= $n * 4 && $h[0] >= $n ? 1 : 0 );
	}

	/**
	 * Ends a line for the finder check.
	 *
	 * @param bool  $color   Color of the last run.
	 * @param int   $run     Length of the last run.
	 * @param int[] $history Run history.
	 * @return int
	 */
	private function finder_terminate( $color, $run, array &$history ) {
		if ( $color ) {
			$this->finder_history_add( $run, $history );
			$run = 0;
		}
		$run += $this->size;
		$this->finder_history_add( $run, $history );
		return $this->finder_count( $history );
	}

	/**
	 * Pushes a run length into the history.
	 *
	 * @param int   $run     Run length.
	 * @param int[] $history Run history.
	 */
	private function finder_history_add( $run, array &$history ) {
		if ( 0 === $history[0] ) {
			$run += $this->size; // The light area before the first run.
		}
		array_pop( $history );
		array_unshift( $history, $run );
	}
}
