<?php
/**
 * FAQ: questions with answers that open and close (the FAQ block with FAQ Item blocks) and their schema.org FAQPage
 * data for search engines.
 *
 * - The FAQ Item blocks render before their parent: each one leaves its question and answer on a stack and the FAQ
 *   block takes them (see collect() and take()), like the Product Tab blocks.
 * - The FAQPage data of all FAQ blocks of a page goes out once, as JSON-LD in the footer: a page may have only one
 *   FAQPage. The filter envo_blocks_faq_schema turns it off (e.g. when an SEO plugin already adds one).
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * FAQ.
 */
final class Envo_Blocks_FAQ {

	/**
	 * Data of the rendered FAQ Item blocks, waiting for their parent.
	 *
	 * @var array
	 */
	private static $stack = array();

	/**
	 * Questions and answers of the page for the FAQPage data.
	 *
	 * @var array
	 */
	private static $schema = array();

	/**
	 * Anchors used on the page.
	 *
	 * @var array
	 */
	private static $anchors = array();

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'wp_footer', array( __CLASS__, 'print_schema' ), 20 );
	}

	/**
	 * Keeps the data of a FAQ Item block for its parent.
	 *
	 * @param array  $attributes Attributes of the FAQ Item block.
	 * @param string $content    Its answer (rendered inner blocks).
	 */
	public static function collect( array $attributes, $content ) {
		self::$stack[] = array(
			'question' => isset( $attributes['question'] ) ? (string) $attributes['question'] : '',
			'answer'   => (string) $content,
			'open'     => ! empty( $attributes['open'] ),
		);
	}

	/**
	 * Data of the last $count FAQ Item blocks (the children of the FAQ block that renders now).
	 *
	 * @param int $count Number of children.
	 * @return array
	 */
	public static function take( $count ) {
		if ( $count <= 0 ) {
			return array();
		}
		return array_splice( self::$stack, -min( $count, count( self::$stack ) ) );
	}

	/**
	 * A unique anchor of a question on the page (faq-shipping-time), so it can be linked.
	 *
	 * @param string $question Question (may contain inline HTML).
	 * @return string
	 */
	public static function anchor( $question ) {
		$base = sanitize_title( wp_strip_all_tags( $question ) );
		$base = 'faq-' . ( '' !== $base ? substr( $base, 0, 60 ) : 'question' );
		$id   = $base;
		$n    = 2;
		while ( isset( self::$anchors[ $id ] ) ) {
			$id = $base . '-' . $n++;
		}
		self::$anchors[ $id ] = true;
		return $id;
	}

	/**
	 * Adds questions to the FAQPage data of the page.
	 *
	 * @param array $items Items with question and answer.
	 */
	public static function add_schema( array $items ) {
		foreach ( $items as $item ) {
			$question = trim( wp_strip_all_tags( $item['question'] ) );
			// The HTML Google reads in answers: headings, paragraphs, lists, links, bold and italic.
			$answer = trim( preg_replace( '/\s+/', ' ', wp_kses( $item['answer'], self::answer_html() ) ) );
			// A question once per page (the same question in two FAQ blocks).
			$key = strtolower( $question );
			if ( '' !== $question && '' !== wp_strip_all_tags( $answer ) && ! isset( self::$schema[ $key ] ) ) {
				self::$schema[ $key ] = array(
					'@type'          => 'Question',
					'name'           => html_entity_decode( $question, ENT_QUOTES, 'UTF-8' ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $answer,
					),
				);
			}
		}
	}

	/**
	 * Tags allowed in the answer text of the FAQPage data.
	 *
	 * @return array
	 */
	private static function answer_html() {
		$tags = array();
		foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'br', 'ol', 'ul', 'li', 'p', 'b', 'strong', 'i', 'em' ) as $tag ) {
			$tags[ $tag ] = array();
		}
		$tags['a'] = array( 'href' => true );
		return $tags;
	}

	/**
	 * Prints the FAQPage data of the page (once, for all FAQ blocks).
	 */
	public static function print_schema() {
		/**
		 * Whether the FAQ blocks add schema.org FAQPage data to the page.
		 *
		 * @param bool  $enabled   True.
		 * @param array $questions Question entities.
		 */
		if ( ! self::$schema || ! apply_filters( 'envo_blocks_faq_schema', true, self::$schema ) ) {
			return;
		}
		$data = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => array_values( self::$schema ),
		);
		echo '<script type="application/ld+json" class="envo-blocks-faq-schema">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . '</script>' . "\n";
		self::$schema = array();
	}
}
