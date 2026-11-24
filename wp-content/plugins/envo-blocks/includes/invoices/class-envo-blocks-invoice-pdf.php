<?php
/**
 * PDF of a document snapshot: HTML template (views/invoice.php) rendered by mPDF (bundled with a prefixed namespace
 * in vendor-prefixed/). Fonts: Noto Sans bundled (Latin, Greek, Cyrillic), packs for other scripts downloaded to
 * uploads/envo-blocks-fonts/ on request.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Invoice PDF.
 */
final class Envo_Blocks_Invoice_PDF {

	const MPDF      = 'EnvoBlocks\Vendor\Mpdf\Mpdf';
	const FONTS_DIR = 'envo-blocks-fonts';

	/**
	 * Font packs for scripts outside the bundled font: key => name, mPDF font name, files, OpenType layout, script
	 * ranges (Unicode code points). CJK: Noto Sans (no shaping needed). Scripts with shaping (Indic, Arabic, Hebrew,
	 * Thai): the fonts mPDF is tested with – newer Noto fonts use OpenType tables mPDF 8 cannot read.
	 *
	 * @return array
	 */
	public static function packs() {
		return array(
			'jp'     => array(
				'name'   => __( 'Japanese', 'envo-blocks' ),
				'font'   => 'notosansjp',
				'files'  => array( 'R' => 'NotoSansJP-Regular.ttf', 'B' => 'NotoSansJP-Bold.ttf' ),
				'otl'    => false,
				'ranges' => array( array( 0x3040, 0x30FF ), array( 0x31F0, 0x31FF ), array( 0xFF65, 0xFF9F ) ),
				'size'   => 6.4,
				'sha256' => array(
					'NotoSansJP-Regular.ttf' => 'bc2399122eb7fa0e1e4ec963f8ab7b529619d71968caefb3845dd3a6e3f40956',
					'NotoSansJP-Bold.ttf' => '19c859e8dce60c05d953dc64b27c407f95d93a6ac0a34f986b091565d1645c38',
				),
			),
			'sc'     => array(
				'name'   => __( 'Chinese (Simplified)', 'envo-blocks' ),
				'font'   => 'notosanssc',
				'files'  => array( 'R' => 'NotoSansSC-Regular.ttf', 'B' => 'NotoSansSC-Bold.ttf' ),
				'otl'    => false,
				'ranges' => array( array( 0x4E00, 0x9FFF ), array( 0x3400, 0x4DBF ), array( 0x3000, 0x303F ), array( 0xFF00, 0xFF64 ) ),
				'size'   => 12.2,
				'sha256' => array(
					'NotoSansSC-Regular.ttf' => 'e444ec95836f75c8c0e030c50fd0146d84f94ff0ed12a6d4763763053d2069b6',
					'NotoSansSC-Bold.ttf' => 'b1e17d946e17c5e1ba55d299dbf2543ab02c73166c627bd08210b2c9daca3015',
				),
			),
			'tc'     => array(
				'name'   => __( 'Chinese (Traditional)', 'envo-blocks' ),
				'font'   => 'notosanstc',
				'files'  => array( 'R' => 'NotoSansTC-Regular.ttf', 'B' => 'NotoSansTC-Bold.ttf' ),
				'otl'    => false,
				'ranges' => array(),
				'size'   => 8.2,
				'sha256' => array(
					'NotoSansTC-Regular.ttf' => '622a9d5853edab907d750fed155837d936ee2e6e6c8a6da45c0558d151819b7c',
					'NotoSansTC-Bold.ttf' => '60b58733739e6825dec90a0cc248ccbf05819ff6bd3f689939285a5a340ac537',
				),
			),
			'kr'     => array(
				'name'   => __( 'Korean', 'envo-blocks' ),
				'font'   => 'notosanskr',
				'files'  => array( 'R' => 'NotoSansKR-Regular.ttf', 'B' => 'NotoSansKR-Bold.ttf' ),
				'otl'    => false,
				'ranges' => array( array( 0xAC00, 0xD7AF ), array( 0x1100, 0x11FF ), array( 0x3130, 0x318F ) ),
				'size'   => 5.8,
				'sha256' => array(
					'NotoSansKR-Regular.ttf' => 'f5bb164a225b382a5cf1144272422e35cf2b4de3a3bc8b9fdf790788e4bfffa0',
					'NotoSansKR-Bold.ttf' => '0b2d48ed946c9ac8a5a5d3368348edff5a9fd9ce26d2b8aa7aeefd4f71592cfa',
				),
			),
			'indic'  => array(
				'name'   => __( 'Indian scripts', 'envo-blocks' ),
				'font'   => 'freeserif',
				'files'  => array( 'R' => 'FreeSerif.ttf', 'B' => 'FreeSerifBold.ttf' ),
				'extra'  => array(
					'lohitkannada' => array( 'R' => 'Lohit-Kannada.ttf' ),
					'pothana2000'  => array( 'R' => 'Pothana2000.ttf' ),
				),
				'otl'    => true,
				'ranges' => array( array( 0x0900, 0x0D7F ) ),
				'size'   => 2.6,
				'sha256' => array(
					'FreeSerif.ttf'     => '658563c732eafdc851de2b3f38b27d0afcde5674b2f93440971b9693438c6494',
					'FreeSerifBold.ttf' => '973791e2bb5ef4ba7ac9e7423f8a8d8441468da3a37c7e38ab38256011f5f261',
					'Lohit-Kannada.ttf' => '64a9b3d19da121e0916c87ac1b7c223a1209d42598249bb0ef176888f710aff7',
					'Pothana2000.ttf'   => 'dc995936c4067ba61f424dc0ea48ea9fd2c79f9fc7df8a7a69bf970572d1b88f',
				),
			),
			'arabic' => array(
				'name'   => __( 'Arabic', 'envo-blocks' ),
				'font'   => 'xbriyaz',
				'files'  => array( 'R' => 'XB Riyaz.ttf', 'B' => 'XB RiyazBd.ttf' ),
				'otl'    => true,
				'kashida' => true,
				'ranges' => array( array( 0x0600, 0x06FF ), array( 0x0750, 0x077F ), array( 0x08A0, 0x08FF ), array( 0xFB50, 0xFDFF ), array( 0xFE70, 0xFEFF ) ),
				'size'   => 0.3,
				'sha256' => array(
					'XB Riyaz.ttf' => '5e113bf5533436c34b9f038c0fc554a31857f08eff216b6618166acfc73fce25',
					'XB RiyazBd.ttf' => 'b3672410bd26bf4d9d26616c4e9a2e84d8a072f1997bf62edd3ac839cdec6f42',
				),
			),
			'hebrew' => array(
				'name'   => __( 'Hebrew', 'envo-blocks' ),
				'font'   => 'taameydavidclm',
				'files'  => array( 'R' => 'TaameyDavidCLM-Medium.ttf' ),
				'otl'    => true,
				'ranges' => array( array( 0x0590, 0x05FF ), array( 0xFB1D, 0xFB4F ) ),
				'size'   => 0.1,
				'sha256' => array(
					'TaameyDavidCLM-Medium.ttf' => '2f668ae127a2bd17e9f312d8c9908daeb2298b79f84bce42711a0095bc5263b6',
				),
			),
			'thai'   => array(
				'name'   => __( 'Thai', 'envo-blocks' ),
				'font'   => 'garuda',
				'files'  => array( 'R' => 'Garuda.ttf', 'B' => 'Garuda-Bold.ttf' ),
				'otl'    => true,
				'ranges' => array( array( 0x0E00, 0x0E7F ) ),
				'size'   => 0.1,
				'sha256' => array(
					'Garuda.ttf' => '939ee9fff399682cd36cfbfcf5922c789f86912687096599b3e3a53a3b67e62e',
					'Garuda-Bold.ttf' => '6023d101a2318a8e12e8ddbe58fdd2f15a5913b94a976bee4e6f6f51681a5f3d',
				),
			),
		);
	}

	/**
	 * Address of the font pack zips.
	 */
	const PACKS_URL = 'https://assets.envoblocks.com/fonts/';

	/**
	 * All font files of a pack (main and extra fonts).
	 *
	 * @param array $pack Pack.
	 * @return string[]
	 */
	public static function pack_files( array $pack ) {
		$files = array_values( $pack['files'] );
		foreach ( isset( $pack['extra'] ) ? $pack['extra'] : array() as $extra ) {
			$files = array_merge( $files, array_values( $extra ) );
		}
		return array_values( array_unique( $files ) );
	}

	/**
	 * Installs a font pack from a zip: every font file must match its checksum, nothing else is taken.
	 *
	 * @param string $key  Pack key.
	 * @param string $zip  Path of the zip.
	 * @return true|WP_Error
	 */
	public static function install_zip( $key, $zip ) {
		$packs = self::packs();
		if ( ! isset( $packs[ $key ] ) ) {
			return new WP_Error( 'envo_blocks_font_pack', __( 'Unknown font pack.', 'envo-blocks' ) );
		}
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new WP_Error( 'envo_blocks_font_zip', __( 'The PHP zip extension is missing on this server.', 'envo-blocks' ) );
		}
		$archive = new ZipArchive();
		if ( true !== $archive->open( $zip ) ) {
			return new WP_Error( 'envo_blocks_font_zip', __( 'The file is not a zip archive.', 'envo-blocks' ) );
		}
		$fonts = array();
		foreach ( self::pack_files( $packs[ $key ] ) as $file ) {
			$data = $archive->getFromName( $file );
			if ( false === $data || ! isset( $packs[ $key ]['sha256'][ $file ] ) || ! hash_equals( $packs[ $key ]['sha256'][ $file ], hash( 'sha256', $data ) ) ) {
				$archive->close();
				/* translators: %s: file name. */
				return new WP_Error( 'envo_blocks_font_hash', sprintf( __( 'The font pack is not the right one: %s is missing or changed.', 'envo-blocks' ), $file ) );
			}
			$fonts[ $file ] = $data;
		}
		$archive->close();
		$dir = self::fonts_dir() . $key . '/';
		Envo_Blocks_Invoices::protect( self::fonts_dir() );
		wp_mkdir_p( $dir );
		foreach ( $fonts as $file => $data ) {
			if ( false === file_put_contents( $dir . $file, $data ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Own folder in uploads.
				return new WP_Error( 'envo_blocks_font_write', __( 'The font files could not be saved in the uploads folder.', 'envo-blocks' ) );
			}
		}
		// The font cache of mPDF may hold an older font of the same name.
		self::clear_font_cache();
		return true;
	}

	/**
	 * Downloads and installs a font pack from assets.envoblocks.com.
	 *
	 * @param string $key Pack key.
	 * @return true|WP_Error
	 */
	public static function download( $key ) {
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		$file = download_url( self::PACKS_URL . rawurlencode( $key ) . '.zip', 300 );
		if ( is_wp_error( $file ) ) {
			return $file;
		}
		$result = self::install_zip( $key, $file );
		wp_delete_file( $file );
		return $result;
	}

	/**
	 * Removes an installed font pack.
	 *
	 * @param string $key Pack key.
	 */
	public static function remove( $key ) {
		$packs = self::packs();
		if ( ! isset( $packs[ $key ] ) ) {
			return;
		}
		foreach ( self::pack_files( $packs[ $key ] ) as $file ) {
			wp_delete_file( self::fonts_dir() . $key . '/' . $file );
		}
		@rmdir( self::fonts_dir() . $key ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Empty folder only.
		self::clear_font_cache();
	}

	/**
	 * Deletes the font cache of mPDF (made again on the next PDF).
	 */
	public static function clear_font_cache() {
		$dir = Envo_Blocks_Invoices::base_dir() . 'tmp/mpdf/ttfontdata/';
		foreach ( (array) glob( $dir . '*' ) as $file ) {
			if ( is_file( $file ) ) {
				wp_delete_file( $file );
			}
		}
	}

	/**
	 * Folder of the downloaded font packs (trailing slash).
	 *
	 * @return string
	 */
	public static function fonts_dir() {
		$uploads = wp_upload_dir( null, false );
		return trailingslashit( $uploads['basedir'] ) . self::FONTS_DIR . '/';
	}

	/**
	 * Whether a font pack is installed (all its files are there).
	 *
	 * @param string $key Pack key.
	 * @return bool
	 */
	public static function installed( $key ) {
		$packs = self::packs();
		if ( ! isset( $packs[ $key ] ) ) {
			return false;
		}
		foreach ( $packs[ $key ]['files'] as $file ) {
			if ( ! is_file( self::fonts_dir() . $key . '/' . $file ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Whether the PDF library is available.
	 *
	 * @return bool
	 */
	public static function available() {
		if ( ! class_exists( self::MPDF ) && is_file( ENVO_BLOCKS_PATH . 'vendor-prefixed/autoload.php' ) ) {
			require_once ENVO_BLOCKS_PATH . 'vendor-prefixed/autoload.php';
		}
		return class_exists( self::MPDF );
	}

	/**
	 * Font packs a document needs (by the characters it contains), installed or not.
	 *
	 * @param array $data Snapshot.
	 * @return string[] Pack keys.
	 */
	public static function needed_packs( array $data ) {
		$text = wp_json_encode( array( $data['labels'], $data['seller'], $data['buyer'], wp_list_pluck( $data['lines'], 'name' ), wp_list_pluck( $data['lines'], 'meta' ), $data['texts'], $data['note'], $data['terms'] ), JSON_UNESCAPED_UNICODE );
		$need = array();
		if ( ! preg_match_all( '/[^\x{0000}-\x{052F}\x{1E00}-\x{1FFF}\x{2000}-\x{2BFF}]/u', (string) $text, $m ) ) {
			return $need;
		}
		$packs = self::packs();
		$kana  = false;
		foreach ( array_unique( $m[0] ) as $char ) {
			$code = self::ord( $char );
			foreach ( $packs as $key => $pack ) {
				foreach ( $pack['ranges'] as $range ) {
					if ( $code >= $range[0] && $code <= $range[1] ) {
						$need[ $key ] = true;
						$kana         = $kana || 'jp' === $key;
					}
				}
			}
		}
		// Han characters: Japanese when the document has kana or is Japanese, else Chinese.
		if ( isset( $need['sc'] ) && ( $kana || 'ja' === $data['lang'] ) ) {
			unset( $need['sc'] );
			$need['jp'] = true;
		}
		return array_keys( $need );
	}

	/**
	 * Font packs a document needs that are not installed.
	 *
	 * @param array $data Snapshot.
	 * @return string[]
	 */
	public static function missing_packs( array $data ) {
		$han = self::installed( 'jp' ) || self::installed( 'sc' ) || self::installed( 'tc' );
		return array_values( array_filter( self::needed_packs( $data ), static function ( $key ) use ( $han ) {
			// Chinese characters are drawn with any installed Chinese or Japanese font.
			return ! self::installed( $key ) && ! ( $han && in_array( $key, array( 'sc', 'tc' ), true ) );
		} ) );
	}

	/**
	 * Code point of one UTF-8 character.
	 *
	 * @param string $char Character.
	 * @return int
	 */
	private static function ord( $char ) {
		if ( function_exists( 'mb_ord' ) ) {
			return (int) mb_ord( $char, 'UTF-8' );
		}
		$code = unpack( 'N', mb_convert_encoding( $char, 'UCS-4BE', 'UTF-8' ) );
		return (int) $code[1];
	}

	/**
	 * HTML of a document.
	 *
	 * @param array $data    Snapshot.
	 * @param bool  $preview For a browser (no mPDF tags, a page frame).
	 * @return string
	 */
	public static function html( array $data, $preview = false ) {
		ob_start();
		include ENVO_BLOCKS_PATH . 'views/invoice.php';
		return (string) ob_get_clean();
	}

	/**
	 * PDF data of a document.
	 *
	 * @param array $data Snapshot.
	 * @return string|WP_Error
	 */
	public static function pdf( array $data ) {
		if ( ! self::available() ) {
			return new WP_Error( 'envo_blocks_no_pdf', __( 'The PDF library is missing.', 'envo-blocks' ) );
		}
		$tmp = Envo_Blocks_Invoices::base_dir() . 'tmp';
		Envo_Blocks_Invoices::protect( Envo_Blocks_Invoices::base_dir() );
		wp_mkdir_p( $tmp );

		$fontdata = array(
			'notosans' => array(
				'R' => 'NotoSans-Regular.ttf',
				'B' => 'NotoSans-Bold.ttf',
			),
		);
		$dirs     = array( ENVO_BLOCKS_PATH . 'assets/fonts' );
		$names    = array();
		$backup   = array( 'notosans' ); // Characters missing in the main font (Czech letters in a Japanese document).

		foreach ( self::packs() as $key => $pack ) {
			if ( ! self::installed( $key ) ) {
				continue;
			}
			$dirs[] = self::fonts_dir() . $key;
			$fonts  = array( $pack['font'] => $pack['files'] ) + ( isset( $pack['extra'] ) ? $pack['extra'] : array() );
			foreach ( $fonts as $name => $files ) {
				if ( ! is_file( self::fonts_dir() . $key . '/' . $files['R'] ) ) {
					continue;
				}
				$fontdata[ $name ] = $files;
				if ( $pack['otl'] ) {
					$fontdata[ $name ]['useOTL'] = 0xFF;
				}
				if ( ! empty( $pack['kashida'] ) ) {
					$fontdata[ $name ]['useKashida'] = 75;
				}
				$names[] = $name;
				if ( ! $pack['otl'] ) {
					$backup[] = $name;
				}
			}
		}
		// Main font Noto Sans; a Japanese document uses the Japanese font (it has Latin too). Runs of other scripts
		// get the font of their pack through the language map (with OpenType shaping).
		$default = 'ja' === $data['lang'] && in_array( 'notosansjp', $names, true ) ? 'notosansjp' : 'notosans';
		require_once ENVO_BLOCKS_PATH . 'includes/invoices/class-envo-blocks-invoice-language-fonts.php';

		// mPDF reports notices for glyphs without their own character (contextual forms of Arabic, CJK …) while it
		// reads a font the first time. They are harmless but would flood debug.log: ignore them from the library.
		$vendor = wp_normalize_path( ENVO_BLOCKS_PATH . 'vendor-prefixed/' );
		set_error_handler( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Only around mPDF, restored below.
			static function ( $level, $message, $file ) use ( $vendor ) {
				return 0 === strpos( wp_normalize_path( (string) $file ), $vendor ) && in_array( $level, array( E_NOTICE, E_WARNING, E_DEPRECATED, E_USER_NOTICE, E_USER_DEPRECATED ), true );
			}
		);
		try {
			$class = self::MPDF;
			$mpdf  = new $class(
				array(
					'mode'             => 'utf-8',
					'format'           => 'Letter' === $data['paper'] ? 'Letter' : 'A4',
					'tempDir'          => $tmp,
					'fontDir'          => $dirs,
					'fontdata'         => $fontdata,
					'default_font'     => $default,
					'directionality'   => 'rtl' === $data['dir'] ? 'rtl' : 'ltr',
					'autoScriptToLang' => true,
					'autoLangToFont'   => true,
					'languageToFont'   => new Envo_Blocks_Invoice_Language_Fonts( $names, $data['lang'] ),
					'useSubstitutions' => true,
					'backupSubsFont'   => $backup,
					'margin_left'      => 15,
					'margin_right'     => 15,
					'margin_top'       => 15,
					'margin_bottom'    => 20,
					'margin_footer'    => 8,
				)
			);
			$title = 'packing' === $data['type'] ? $data['labels']['packing_slip'] : $data['labels'][ 'credit' === $data['type'] ? 'credit_note' : 'invoice' ] . ' ' . $data['number'];
			$mpdf->SetTitle( $title );
			$mpdf->SetAuthor( $data['seller']['name'] );
			$mpdf->SetCreator( 'Envo Blocks' );
			$mpdf->WriteHTML( self::html( $data ) );
			$pdf = $mpdf->Output( '', 'S' );
		} catch ( \Throwable $e ) {
			$pdf = new WP_Error( 'envo_blocks_pdf_failed', $e->getMessage() );
		}
		restore_error_handler();
		return $pdf;
	}
}
