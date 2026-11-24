<?php
/**
 * Language Switcher: the languages of WPML, Polylang or TranslatePress as a dropdown or a row of links, with flags,
 * names or codes – for headers and footers built from blocks.
 *
 * - languages() reads the languages of the active plugin into one list; the filter
 *   envo_blocks_language_switcher_languages can give another list (e.g. the sample languages of a demo).
 * - Without a multilingual plugin the block shows nothing on the site and sample languages in the editor.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Language Switcher.
 */
final class Envo_Blocks_Language_Switcher {

	/**
	 * Languages of the multilingual plugin, in its order.
	 *
	 * @param bool $hide_missing Leave out the languages without a translation of the current page.
	 * @return array[] List of array( code, name, short, url, flag, current, missing ).
	 */
	public static function languages( $hide_missing = false ) {
		$list = array();
		switch ( Envo_Blocks_Multilang::plugin() ) {
			case 'polylang':
				$raw = function_exists( 'pll_the_languages' ) ? pll_the_languages(
					array(
						'raw'           => 1,
						'hide_if_empty' => 0,
					)
				) : array();
				foreach ( (array) $raw as $lang ) {
					$list[] = array(
						'code'    => (string) $lang['slug'],
						'name'    => (string) $lang['name'],
						'short'   => (string) $lang['slug'],
						'url'     => (string) $lang['url'],
						'flag'    => isset( $lang['flag'] ) ? (string) $lang['flag'] : '',
						'current' => ! empty( $lang['current_lang'] ),
						'missing' => ! empty( $lang['no_translation'] ),
					);
				}
				break;
			case 'wpml':
				$raw = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
				foreach ( (array) $raw as $lang ) {
					$list[] = array(
						'code'    => (string) $lang['code'],
						'name'    => (string) ( $lang['native_name'] ?? $lang['code'] ),
						'short'   => (string) $lang['code'],
						'url'     => (string) $lang['url'],
						'flag'    => (string) ( $lang['country_flag_url'] ?? '' ),
						'current' => ! empty( $lang['active'] ),
						'missing' => ! empty( $lang['missing'] ),
					);
				}
				break;
			case 'translatepress':
				$raw     = function_exists( 'trp_custom_language_switcher' ) ? trp_custom_language_switcher() : array();
				$current = Envo_Blocks_Multilang::current();
				foreach ( (array) $raw as $code => $lang ) {
					$list[] = array(
						'code'    => (string) $code,
						'name'    => (string) $lang['language_name'],
						'short'   => (string) $lang['short_language_name'],
						'url'     => (string) $lang['current_page_url'],
						'flag'    => (string) $lang['flag_link'],
						'current' => $code === $current,
						'missing' => false,
					);
				}
				break;
		}

		/**
		 * Filters the languages of the Language Switcher block.
		 *
		 * @param array[] $list List of array( code, name, short, url, flag, current, missing ).
		 */
		$list = (array) apply_filters( 'envo_blocks_language_switcher_languages', $list );
		if ( $hide_missing ) {
			$list = array_values(
				array_filter(
					$list,
					static function ( $lang ) {
						return ! empty( $lang['current'] ) || empty( $lang['missing'] );
					}
				)
			);
		}
		return $list;
	}

	/**
	 * Sample languages for the editor preview (and demos): English, German, French, Italian, Dutch.
	 *
	 * @return array[]
	 */
	public static function samples() {
		$flag = static function ( $svg ) {
			return 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 20">' . $svg . '</svg>' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- A data URI of a small flag.
		};
		$h    = static function ( $a, $b, $c ) {
			return '<rect width="30" height="20" fill="' . $c . '"/><rect width="30" height="13.34" fill="' . $b . '"/><rect width="30" height="6.67" fill="' . $a . '"/>';
		};
		$v    = static function ( $a, $b, $c ) {
			return '<rect width="30" height="20" fill="' . $c . '"/><rect width="20" height="20" fill="' . $b . '"/><rect width="10" height="20" fill="' . $a . '"/>';
		};
		$uk   = '<rect width="30" height="20" fill="#012169"/><path d="M0 0l30 20M30 0L0 20" stroke="#fff" stroke-width="4"/><path d="M0 0l30 20M30 0L0 20" stroke="#c8102e" stroke-width="1.6"/><path d="M15 0v20M0 10h30" stroke="#fff" stroke-width="6"/><path d="M15 0v20M0 10h30" stroke="#c8102e" stroke-width="3.6"/>';
		$list = array(
			array( 'en', 'English', $flag( $uk ) ),
			array( 'de', 'Deutsch', $flag( $h( '#000', '#dd0000', '#ffce00' ) ) ),
			array( 'fr', 'Français', $flag( $v( '#002395', '#fff', '#ed2939' ) ) ),
			array( 'it', 'Italiano', $flag( $v( '#009246', '#fff', '#ce2b37' ) ) ),
			array( 'nl', 'Nederlands', $flag( $h( '#ae1c28', '#fff', '#21468b' ) ) ),
		);
		$out  = array();
		foreach ( $list as $i => $lang ) {
			$out[] = array(
				'code'    => $lang[0],
				'name'    => $lang[1],
				'short'   => $lang[0],
				'url'     => '#',
				'flag'    => $lang[2],
				'current' => 0 === $i,
				'missing' => false,
			);
		}
		return $out;
	}
}
