<?php
/**
 * Language Switcher: the languages of WPML, Polylang or TranslatePress (see Envo_Blocks_Language_Switcher).
 *
 * The dropdown is a <details> element, so it opens without JavaScript; language-switcher-view.js closes it on a
 * click outside or Escape.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_hide_missing = ! empty( $attributes['hideMissing'] );
$envo_blocks_langs        = Envo_Blocks_Language_Switcher::languages( $envo_blocks_hide_missing );
$envo_blocks_sample       = false;
if ( ! $envo_blocks_langs ) {
	if ( ! Envo_Blocks_Blocks::is_preview() ) {
		return;
	}
	// The editor without a multilingual plugin: sample languages show the look.
	$envo_blocks_langs  = Envo_Blocks_Language_Switcher::samples();
	$envo_blocks_sample = true;
}

$envo_blocks_layout = Envo_Blocks_Blocks::choice( $attributes, 'layout', array( 'dropdown', 'inline' ) );
$envo_blocks_show   = Envo_Blocks_Blocks::choice( $attributes, 'show', array( 'flag-name', 'name', 'flag', 'code', 'flag-code' ) );
$envo_blocks_shape  = Envo_Blocks_Blocks::choice( $attributes, 'flagShape', array( 'rect', 'round' ) );
$envo_blocks_just   = Envo_Blocks_Blocks::choice( $attributes, 'justify', array( 'left', 'center', 'right' ) );
$envo_blocks_menu   = Envo_Blocks_Blocks::choice( $attributes, 'menuAlign', array( 'left', 'right' ) );

$envo_blocks_current = null;
foreach ( $envo_blocks_langs as $envo_blocks_lang ) {
	if ( ! empty( $envo_blocks_lang['current'] ) ) {
		$envo_blocks_current = $envo_blocks_lang;
		break;
	}
}
if ( null === $envo_blocks_current ) {
	$envo_blocks_current = $envo_blocks_langs[0];
}

// One language: label, flag and lang attribute.
$envo_blocks_item = static function ( $lang ) use ( $envo_blocks_show ) {
	$html = '';
	$flag = (string) $lang['flag'];
	if ( false !== strpos( $envo_blocks_show, 'flag' ) && '' !== $flag ) {
		$src   = 0 === strpos( $flag, 'data:image/svg+xml;base64,' ) && preg_match( '#^data:image/svg\+xml;base64,[A-Za-z0-9+/=]+$#', $flag ) ? $flag : esc_url( $flag );
		$html .= '<img class="sb-langs__flag" src="' . esc_attr( $src ) . '" alt="" width="20" height="14" loading="lazy" decoding="async">';
	}
	if ( false !== strpos( $envo_blocks_show, 'name' ) ) {
		$html .= '<span class="sb-langs__name">' . esc_html( $lang['name'] ) . '</span>';
	} elseif ( false !== strpos( $envo_blocks_show, 'code' ) ) {
		$html .= '<span class="sb-langs__code">' . esc_html( strtoupper( (string) $lang['short'] ) ) . '</span>';
	}
	if ( 'flag' === $envo_blocks_show ) {
		$html .= '<span class="screen-reader-text">' . esc_html( $lang['name'] ) . '</span>';
	}
	return $html;
};
$envo_blocks_link = static function ( $lang ) use ( $envo_blocks_item ) {
	$code = str_replace( '_', '-', (string) $lang['code'] );
	if ( ! empty( $lang['current'] ) ) {
		return '<li><a class="sb-langs__link is-current" href="' . esc_url( $lang['url'] ) . '" lang="' . esc_attr( $code ) . '" aria-current="true">' . $envo_blocks_item( $lang ) . '</a></li>';
	}
	return '<li><a class="sb-langs__link" href="' . esc_url( $lang['url'] ) . '" lang="' . esc_attr( $code ) . '" hreflang="' . esc_attr( $code ) . '">' . $envo_blocks_item( $lang ) . '</a></li>';
};

$envo_blocks_style = Envo_Blocks_Blocks::color_vars(
	$attributes,
	array(
		'textColor'      => '--sb-langs-text',
		'activeColor'    => '--sb-langs-active',
		'background'     => '--sb-langs-bg',
		'menuBackground' => '--sb-langs-menu-bg',
		'menuTextColor'  => '--sb-langs-menu-text',
		'borderColor'    => '--sb-langs-border',
	)
);

$envo_blocks_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'sb-langs sb-langs--' . $envo_blocks_layout . ' sb-langs--flag-' . $envo_blocks_shape . ' sb-langs--justify-' . $envo_blocks_just . ' sb-langs--menu-' . $envo_blocks_menu,
		'style' => $envo_blocks_style,
	)
);

$envo_blocks_html = '';
if ( 'dropdown' === $envo_blocks_layout ) {
	$envo_blocks_others = '';
	foreach ( $envo_blocks_langs as $envo_blocks_lang ) {
		if ( $envo_blocks_lang !== $envo_blocks_current ) {
			$envo_blocks_others .= $envo_blocks_link( $envo_blocks_lang );
		}
	}
	$envo_blocks_html = '<details class="sb-langs__menu"><summary class="sb-langs__toggle" aria-label="'
		/* translators: %s: name of the current language. */
		. esc_attr( sprintf( __( 'Language: %s', 'envo-blocks' ), $envo_blocks_current['name'] ) ) . '">'
		. $envo_blocks_item( $envo_blocks_current )
		. '<svg class="sb-langs__chevron" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
		. '</summary><ul class="sb-langs__list">' . $envo_blocks_others . '</ul></details>';
} else {
	$envo_blocks_items = '';
	foreach ( $envo_blocks_langs as $envo_blocks_lang ) {
		if ( ! empty( $attributes['hideCurrent'] ) && $envo_blocks_lang === $envo_blocks_current ) {
			continue;
		}
		$envo_blocks_items .= $envo_blocks_link( $envo_blocks_lang );
	}
	$envo_blocks_html = '<ul class="sb-langs__list">' . $envo_blocks_items . '</ul>';
}

// data-no-translation: TranslatePress must not move the links into the current language (nor translate the names).
echo '<nav ' . $envo_blocks_attrs . ' aria-label="' . esc_attr__( 'Languages', 'envo-blocks' ) . '" data-no-translation>' . $envo_blocks_html . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
if ( $envo_blocks_sample ) {
	echo Envo_Blocks_Blocks::notice( __( 'Sample languages: the block shows the languages of WPML, Polylang or TranslatePress. Without one of them it shows nothing on the site.', 'envo-blocks' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in notice().
}
