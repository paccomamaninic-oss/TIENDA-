<?php
/**
 * Language to font map of mPDF for the invoices: runs of text that mPDF marks with a language or script (Arabic,
 * Devanagari, Japanese …) get the font of the installed pack, with OpenType shaping. Everything else uses Noto Sans.
 * Loaded only after the bundled mPDF (it implements its interface).
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Language fonts.
 */
final class Envo_Blocks_Invoice_Language_Fonts implements \EnvoBlocks\Vendor\Mpdf\Language\LanguageToFontInterface {

	/**
	 * Installed fonts: mPDF font name => true.
	 *
	 * @var array
	 */
	private $fonts;

	/**
	 * Language of the document (Han characters: Japanese or Chinese).
	 *
	 * @var string
	 */
	private $lang;

	/**
	 * Constructor.
	 *
	 * @param array  $fonts Installed mPDF font names.
	 * @param string $lang  Document language.
	 */
	public function __construct( array $fonts, $lang ) {
		$this->fonts = array_fill_keys( $fonts, true );
		$this->lang  = (string) $lang;
	}

	/**
	 * Font of a language tag (e.g. "ar", "hi", "und-Deva", "zh-TW").
	 *
	 * @param string $llcc     Language tag.
	 * @param bool   $adobe_cjk Adobe CJK fonts (not used).
	 * @return array array( core fonts suitable, font name or '' ).
	 */
	public function getLanguageOptions( $llcc, $adobe_cjk ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Interface of mPDF.
		$tags    = explode( '-', strtolower( (string) $llcc ) );
		$lang    = $tags[0];
		$script  = '';
		$country = '';
		foreach ( array_slice( $tags, 1 ) as $tag ) {
			if ( 4 === strlen( $tag ) ) {
				$script = $tag;
			} else {
				$country = $tag;
			}
		}
		$scripts = array(
			'arab' => 'xbriyaz',
			'hebr' => 'taameydavidclm',
			'thai' => 'garuda',
			'deva' => 'freeserif',
			'beng' => 'freeserif',
			'guru' => 'freeserif',
			'gujr' => 'freeserif',
			'taml' => 'freeserif',
			'telu' => 'pothana2000',
			'knda' => 'lohitkannada',
			'mlym' => 'freeserif',
			'jpan' => 'notosansjp',
			'hira' => 'notosansjp',
			'kana' => 'notosansjp',
			'hans' => 'notosanssc',
			'hant' => 'notosanstc',
			'hang' => 'notosanskr',
			'kore' => 'notosanskr',
			'hani' => 'ja' === $this->lang ? 'notosansjp' : 'notosanssc',
		);
		$langs   = array(
			'ar' => 'xbriyaz',
			'fa' => 'xbriyaz',
			'ur' => 'xbriyaz',
			'ps' => 'xbriyaz',
			'he' => 'taameydavidclm',
			'yi' => 'taameydavidclm',
			'th' => 'garuda',
			'hi' => 'freeserif',
			'mr' => 'freeserif',
			'ne' => 'freeserif',
			'sa' => 'freeserif',
			'bn' => 'freeserif',
			'as' => 'freeserif',
			'pa' => 'freeserif',
			'gu' => 'freeserif',
			'ta' => 'freeserif',
			'te' => 'pothana2000',
			'kn' => 'lohitkannada',
			'ml' => 'freeserif',
			'ja' => 'notosansjp',
			'ko' => 'notosanskr',
			'zh' => in_array( $country, array( 'tw', 'hk', 'mo' ), true ) ? 'notosanstc' : 'notosanssc',
		);
		$font = isset( $scripts[ $script ] ) ? $scripts[ $script ] : ( isset( $langs[ $lang ] ) ? $langs[ $lang ] : '' );
		// Han characters: any installed Chinese or Japanese font is better than none.
		$han = array( 'notosansjp', 'notosanssc', 'notosanstc' );
		if ( ! isset( $this->fonts[ $font ] ) && in_array( $font, $han, true ) ) {
			foreach ( $han as $other ) {
				if ( isset( $this->fonts[ $other ] ) ) {
					$font = $other;
					break;
				}
			}
		}
		return array( false, isset( $this->fonts[ $font ] ) ? $font : '' );
	}
}
