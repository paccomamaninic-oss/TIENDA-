<?php
/**
 * Product questions: customers ask about a product, the shop answers, the answered questions show on the product page
 * (the Product Questions block or a "Questions" tab of the product page).
 *
 * - A question is a comment of the type envo_question on the product (approved = shown); the answer is in its meta
 *   (_envo_answer, _envo_answer_date, _envo_answer_by). Only approved questions with an answer are public. As comments
 *   they are in the personal data export and erasure of WordPress.
 * - Questions never mix with the reviews: comment queries of products leave the type out unless they ask for it, and
 *   the review counts of WooCommerce count reviews only.
 * - The form is a normal post to admin-post.php (works without JavaScript and on cached pages): honeypot, time check,
 *   5 questions per hour per address, the disallowed words of Settings › Discussion. New questions wait for an answer.
 * - Envo Blocks › Settings › Product Questions: settings and the questions with an answer field; publishing an answer
 *   e-mails it to the customer when they asked for it.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product questions.
 */
final class Envo_Blocks_Product_Questions {

	const TYPE   = 'envo_question';
	const OPTION = 'envo_blocks_questions';
	const PAGE   = 'envo-blocks-questions';
	const ACTION = 'envo_blocks_question';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'pre_get_comments', array( __CLASS__, 'hide_from_reviews' ) );
		add_filter( 'pre_wp_update_comment_count_now', array( __CLASS__, 'comment_count' ), 10, 3 );
		add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'tab' ), 25 );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'submit' ) );
		add_action( 'admin_post_nopriv_' . self::ACTION, array( __CLASS__, 'submit' ) );
		add_filter( 'envo_blocks_admin_pages', array( __CLASS__, 'admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'admin_post_envo_blocks_questions', array( __CLASS__, 'save_settings' ) );
		add_action( 'admin_post_envo_blocks_question_answer', array( __CLASS__, 'answer' ) );
		add_filter( 'admin_comment_types_dropdown', array( __CLASS__, 'comment_types' ) );
		add_action( 'init', array( __CLASS__, 'register_assets' ) );
	}

	/**
	 * Settings with defaults.
	 *
	 * @return array{tab:bool, guests:bool, notify:bool, email:string}
	 */
	public static function settings() {
		$saved = get_option( self::OPTION, array() );
		$out   = wp_parse_args(
			is_array( $saved ) ? $saved : array(),
			array(
				'tab'    => false,
				'guests' => true,
				'notify' => true,
				'email'  => '',
			)
		);
		$out['tab']    = (bool) $out['tab'];
		$out['guests'] = (bool) $out['guests'];
		$out['notify'] = (bool) $out['notify'];
		$out['email']  = is_email( $out['email'] ) ? $out['email'] : (string) get_option( 'admin_email' );
		return $out;
	}

	/**
	 * Styles and script of the questions (the block and the tab).
	 */
	public static function register_assets() {
		wp_register_style( 'envo-blocks-questions', ENVO_BLOCKS_URL . 'assets/css/product-questions.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/product-questions.css' ) );
		wp_register_script(
			'envo-blocks-questions',
			ENVO_BLOCKS_URL . 'assets/js/product-questions-view.js',
			array(),
			Envo_Blocks_Blocks::ver( 'assets/js/product-questions-view.js' ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

	/**
	 * Comment queries of products leave the questions out, unless they ask for them (the reviews list, the reviews
	 * block, the comments box of the product editor, the review counts).
	 *
	 * @param WP_Comment_Query $query Query.
	 */
	public static function hide_from_reviews( $query ) {
		$v     = $query->query_vars;
		$types = array_merge( (array) $v['type'], (array) $v['type__in'] );
		if ( in_array( self::TYPE, $types, true ) ) {
			return;
		}
		$post_id = ! empty( $v['post_id'] ) ? (int) $v['post_id'] : ( ! empty( $v['post_ID'] ) ? (int) $v['post_ID'] : 0 );
		$product = ( $post_id && 'product' === get_post_type( $post_id ) ) || in_array( 'product', (array) $v['post_type'], true );
		if ( $product ) {
			$query->query_vars['type__not_in'] = array_merge( (array) $v['type__not_in'], array( self::TYPE ) );
		}
	}

	/**
	 * The comment count of a product without the questions (themes show it as the number of reviews).
	 *
	 * @param int|null $count   Count (null: WordPress counts).
	 * @param int      $old     Old count.
	 * @param int      $post_id Post.
	 * @return int|null
	 */
	public static function comment_count( $count, $old, $post_id ) {
		global $wpdb;
		if ( null !== $count || 'product' !== get_post_type( $post_id ) ) {
			return $count;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- The same count as wp_update_comment_count_now(), without one type.
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1' AND comment_type NOT IN ( %s, 'note' )", $post_id, self::TYPE ) );
	}

	/**
	 * "Product questions" in the type filter of Comments.
	 *
	 * @param array $types Types.
	 * @return array
	 */
	public static function comment_types( $types ) {
		$types[ self::TYPE ] = __( 'Product questions', 'envo-blocks' );
		return $types;
	}

	/**
	 * The answered questions of a product, newest first.
	 *
	 * @param int $product_id Product.
	 * @return WP_Comment[]
	 */
	public static function answered( $product_id ) {
		return get_comments(
			array(
				'post_id'    => $product_id,
				'type'       => self::TYPE,
				'status'     => 'approve',
				'orderby'    => 'comment_date_gmt',
				'order'      => 'DESC',
				'number'     => 200,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Questions of one product.
					array(
						'key'     => '_envo_answer',
						'value'   => '',
						'compare' => '!=',
					),
				),
			)
		);
	}

	/**
	 * "Questions (3)" tab of the product page (setting).
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public static function tab( $tabs ) {
		global $product;
		if ( ! self::settings()['tab'] || ! $product instanceof WC_Product ) {
			return $tabs;
		}
		$count                    = count( self::answered( $product->get_id() ) );
		$tabs['envo_questions'] = array(
			/* translators: %d: number of answered questions */
			'title'    => $count ? sprintf( __( 'Questions (%d)', 'envo-blocks' ), $count ) : __( 'Questions', 'envo-blocks' ),
			'priority' => 35,
			'callback' => static function () use ( $product ) {
				echo self::html( $product->get_id(), array( 'title' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in html().
			},
		);
		return $tabs;
	}

	/**
	 * The questions of a product with the search, "Show more" and the form.
	 *
	 * @param int   $product_id Product.
	 * @param array $args       title, perPage, showForm, showSearch, wrapper (attributes string of a block wrapper).
	 * @return string
	 */
	public static function html( $product_id, array $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'title'      => __( 'Questions & answers', 'envo-blocks' ),
				'perPage'    => 5,
				'showForm'   => true,
				'showSearch' => true,
				'wrapper'    => 'class="sb-pq"',
			)
		);
		wp_enqueue_style( 'envo-blocks-questions' );
		wp_enqueue_script( 'envo-blocks-questions' );
		$questions = self::answered( $product_id );
		$per_page  = max( 1, min( 50, (int) $args['perPage'] ) );
		$id        = 'questions-' . (int) $product_id;
		$settings  = self::settings();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only which notice to show after a redirect.
		$state = isset( $_GET['eb-question'] ) ? sanitize_key( wp_unslash( $_GET['eb-question'] ) ) : '';

		ob_start();
		?>
		<div <?php echo $args['wrapper']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() or fixed. ?> id="<?php echo esc_attr( $id ); ?>" data-per-page="<?php echo (int) $per_page; ?>">
			<?php if ( '' !== $args['title'] ) : ?>
				<h2 class="sb-pq__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<?php endif; ?>

			<?php if ( $args['showSearch'] && count( $questions ) > 3 ) : ?>
				<p class="sb-pq__search">
					<label class="screen-reader-text" for="<?php echo esc_attr( $id . '-search' ); ?>"><?php esc_html_e( 'Search the questions', 'envo-blocks' ); ?></label>
					<input type="search" id="<?php echo esc_attr( $id . '-search' ); ?>" placeholder="<?php esc_attr_e( 'Search the questions…', 'envo-blocks' ); ?>" />
				</p>
				<p class="sb-pq__none" hidden><?php esc_html_e( 'No question matches. Ask it below.', 'envo-blocks' ); ?></p>
			<?php endif; ?>

			<?php if ( $questions ) : ?>
				<ul class="sb-pq__list">
					<?php foreach ( $questions as $i => $question ) : ?>
						<li class="sb-pq__item"<?php echo $i >= $per_page ? ' hidden data-more' : ''; ?>>
							<p class="sb-pq__q"><span class="sb-pq__label" aria-hidden="true">Q</span><span><?php echo esc_html( $question->comment_content ); ?></span></p>
							<div class="sb-pq__a">
								<span class="sb-pq__label" aria-hidden="true">A</span>
								<div>
									<?php echo wp_kses_post( wpautop( (string) get_comment_meta( $question->comment_ID, '_envo_answer', true ) ) ); ?>
									<p class="sb-pq__meta">
										<?php
										/* translators: 1: name of the customer, 2: date */
										echo esc_html( sprintf( __( 'Asked by %1$s on %2$s', 'envo-blocks' ), '' !== $question->comment_author ? $question->comment_author : __( 'a customer', 'envo-blocks' ), date_i18n( get_option( 'date_format' ), strtotime( $question->comment_date ) ) ) );
										echo ' · ';
										/* translators: %s: name of the shop */
										echo esc_html( sprintf( __( 'Answered by %s', 'envo-blocks' ), get_bloginfo( 'name' ) ) );
										?>
									</p>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php if ( count( $questions ) > $per_page ) : ?>
					<p class="sb-pq__more-wrap"><button type="button" class="sb-pq__more"><?php esc_html_e( 'Show more questions', 'envo-blocks' ); ?></button></p>
				<?php endif; ?>
			<?php else : ?>
				<p class="sb-pq__empty"><?php esc_html_e( 'No questions yet. Be the first to ask.', 'envo-blocks' ); ?></p>
			<?php endif; ?>

			<?php if ( $args['showForm'] ) : ?>
				<?php echo self::form( $product_id, $id, $state, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in form(). ?>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * The form to ask a question.
	 *
	 * @param int    $product_id Product.
	 * @param string $id         ID of the wrapper.
	 * @param string $state      Result of the last post (sent, spam, fields, rate).
	 * @param array  $settings   Settings.
	 * @return string
	 */
	private static function form( $product_id, $id, $state, $settings ) {
		$messages = array(
			'sent'   => __( 'Thank you! Your question was sent. The answer will show here – and in your e-mail if you asked for it.', 'envo-blocks' ),
			'fields' => __( 'Please fill in your name, a valid e-mail and the question.', 'envo-blocks' ),
			'rate'   => __( 'Too many questions. Please try again later.', 'envo-blocks' ),
			'spam'   => __( 'Your question could not be sent. Please try again.', 'envo-blocks' ),
		);
		$out = '';
		if ( isset( $messages[ $state ] ) ) {
			$out .= '<p class="sb-pq__notice' . ( 'sent' === $state ? ' is-success' : ' is-error' ) . '" role="status">' . esc_html( $messages[ $state ] ) . '</p>';
		}
		if ( ! is_user_logged_in() && ! $settings['guests'] ) {
			return $out . '<p class="sb-pq__login"><a href="' . esc_url( wp_login_url( get_permalink( $product_id ) . '#' . $id ) ) . '">' . esc_html__( 'Log in to ask a question', 'envo-blocks' ) . '</a></p>';
		}
		$user = wp_get_current_user();
		$out .= '<details class="sb-pq__ask"' . ( isset( $messages[ $state ] ) && 'sent' !== $state ? ' open' : '' ) . '><summary class="sb-pq__ask-button">' . esc_html__( 'Ask a question', 'envo-blocks' ) . '</summary>'
			. '<form class="sb-pq__form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
			. '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '" />'
			. '<input type="hidden" name="product_id" value="' . (int) $product_id . '" />'
			. '<input type="hidden" name="eb_q_time" value="' . (int) time() . '" />'
			. ( is_user_logged_in() ? wp_nonce_field( self::ACTION, '_wpnonce', true, false ) : '' )
			. '<p class="sb-pq__hp" aria-hidden="true"><label>Website <input type="text" name="eb_q_website" tabindex="-1" autocomplete="off" /></label></p>'
			. '<p class="sb-pq__field sb-pq__field--wide"><label for="' . esc_attr( $id . '-q' ) . '">' . esc_html__( 'Your question', 'envo-blocks' ) . '</label><textarea id="' . esc_attr( $id . '-q' ) . '" name="eb_q_question" rows="3" maxlength="1000" required></textarea></p>';
		if ( ! is_user_logged_in() ) {
			$out .= '<p class="sb-pq__field"><label for="' . esc_attr( $id . '-name' ) . '">' . esc_html__( 'Name', 'envo-blocks' ) . '</label><input type="text" id="' . esc_attr( $id . '-name' ) . '" name="eb_q_name" maxlength="80" autocomplete="given-name" required /></p>'
				. '<p class="sb-pq__field"><label for="' . esc_attr( $id . '-email' ) . '">' . esc_html__( 'E-mail', 'envo-blocks' ) . '</label><input type="email" id="' . esc_attr( $id . '-email' ) . '" name="eb_q_email" maxlength="100" autocomplete="email" required /></p>';
		} else {
			/* translators: %s: display name */
			$out .= '<p class="sb-pq__as">' . esc_html( sprintf( __( 'You ask as %s.', 'envo-blocks' ), $user->display_name ) ) . '</p>';
		}
		$out .= '<p class="sb-pq__check"><label><input type="checkbox" name="eb_q_notify" value="1" checked /> ' . esc_html__( 'E-mail me the answer', 'envo-blocks' ) . '</label></p>'
			. '<p class="sb-pq__privacy">' . esc_html__( 'Your name shows with the answered question; your e-mail is only used to send you the answer.', 'envo-blocks' ) . '</p>'
			. '<p class="sb-pq__submit"><button type="submit" class="sb-pq__send wp-element-button">' . esc_html__( 'Send question', 'envo-blocks' ) . '</button></p>'
			. '</form></details>';
		return $out;
	}

	/**
	 * A question from the form (admin-post.php, also for guests).
	 */
	public static function submit() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Guests post from cached pages without a nonce: honeypot, time check and a rate limit; signed-in users with a nonce.
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		// Back to the page of the form (a product page, a page with the block), without the notice of an earlier post.
		$back = wp_get_referer() ? wp_get_referer() : ( $product_id ? get_permalink( $product_id ) : '' );
		$back = remove_query_arg( 'eb-question', $back ? strtok( $back, '#' ) : home_url( '/' ) );
		$go         = static function ( $state ) use ( $back, $product_id ) {
			wp_safe_redirect( add_query_arg( 'eb-question', $state, $back ) . '#questions-' . $product_id );
			exit;
		};
		if ( ! $product_id || 'product' !== get_post_type( $product_id ) || 'publish' !== get_post_status( $product_id ) ) {
			$go( 'spam' );
		}
		$settings = self::settings();
		if ( is_user_logged_in() ) {
			if ( ! wp_verify_nonce( isset( $_POST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ) : '', self::ACTION ) ) {
				$go( 'spam' );
			}
			$user  = wp_get_current_user();
			$name  = $user->first_name ? $user->first_name : $user->display_name;
			$email = $user->user_email;
		} else {
			if ( ! $settings['guests'] ) {
				$go( 'spam' );
			}
			$name  = isset( $_POST['eb_q_name'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_q_name'] ) ) : '';
			$email = isset( $_POST['eb_q_email'] ) ? sanitize_email( wp_unslash( $_POST['eb_q_email'] ) ) : '';
		}
		$question = isset( $_POST['eb_q_question'] ) ? trim( sanitize_textarea_field( wp_unslash( $_POST['eb_q_question'] ) ) ) : '';
		$trap     = isset( $_POST['eb_q_website'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_q_website'] ) ) : '';
		$time     = isset( $_POST['eb_q_time'] ) ? absint( $_POST['eb_q_time'] ) : 0;
		$notify   = ! empty( $_POST['eb_q_notify'] );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( '' !== $trap || ( $time && time() - $time < 3 ) ) {
			$go( 'spam' );
		}
		$name     = mb_substr( $name, 0, 80 );
		$question = mb_substr( $question, 0, 1000 );
		if ( '' === $name || ! is_email( $email ) || mb_strlen( $question ) < 5 ) {
			$go( 'fields' );
		}
		$ip   = class_exists( 'WC_Geolocation' ) ? WC_Geolocation::get_ip_address() : ( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
		$key  = 'envo_blocks_q_rate_' . md5( $ip );
		$sent = (int) get_transient( $key );
		if ( $sent >= 5 ) {
			$go( 'rate' );
		}
		set_transient( $key, $sent + 1, HOUR_IN_SECONDS );

		$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$spam  = wp_check_comment_disallowed_list( $name, $email, '', $question, $ip, $agent );
		$id    = wp_insert_comment(
			array(
				'comment_post_ID'      => $product_id,
				'comment_author'       => $name,
				'comment_author_email' => $email,
				'comment_author_IP'    => $ip,
				'comment_agent'        => $agent,
				'comment_content'      => $question,
				'comment_type'         => self::TYPE,
				'comment_approved'     => $spam ? 'spam' : 0,
				'user_id'              => get_current_user_id(),
			)
		);
		if ( $id && ! $spam ) {
			if ( $notify ) {
				add_comment_meta( $id, '_envo_notify', 1, true );
			}
			if ( $settings['notify'] ) {
				self::mail_shop( $id );
			}
		}
		$go( 'sent' );
	}

	/**
	 * E-mail to the shop about a new question.
	 *
	 * @param int $id Question.
	 */
	private static function mail_shop( $id ) {
		$question = get_comment( $id );
		$product  = $question ? wc_get_product( $question->comment_post_ID ) : null;
		if ( ! $product ) {
			return;
		}
		$link = admin_url( 'admin.php?page=' . self::PAGE );
		/* translators: %s: product name */
		$subject = sprintf( __( 'New question about %s', 'envo-blocks' ), $product->get_name() );
		$body    = '<p>' . esc_html( sprintf( '%s <%s>', $question->comment_author, $question->comment_author_email ) ) . '</p>'
			. '<blockquote>' . nl2br( esc_html( $question->comment_content ) ) . '</blockquote>'
			. '<p><a href="' . esc_url( $link ) . '">' . esc_html__( 'Answer it in Envo Blocks › Settings › Product Questions', 'envo-blocks' ) . '</a></p>';
		self::mail( self::settings()['email'], $subject, __( 'New product question', 'envo-blocks' ), $body, $question->comment_author_email );
	}

	/**
	 * An e-mail in the design of the WooCommerce e-mails.
	 *
	 * @param string $to       Recipient.
	 * @param string $subject  Subject.
	 * @param string $heading  Heading.
	 * @param string $body     HTML.
	 * @param string $reply_to Reply-To.
	 * @return bool
	 */
	private static function mail( $to, $subject, $heading, $body, $reply_to = '' ) {
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		if ( is_email( $reply_to ) ) {
			$headers[] = 'Reply-To: ' . $reply_to;
		}
		if ( function_exists( 'WC' ) && WC()->mailer() ) {
			$mailer = WC()->mailer();
			return (bool) $mailer->send( $to, $subject, $mailer->wrap_message( $heading, $body ), $headers );
		}
		return wp_mail( $to, $subject, $body, $headers );
	}

	/* ------------------------------------------------------------------ admin */

	/**
	 * Envo Blocks › Settings › Product Questions.
	 *
	 * @param array $pages Pages.
	 * @return array
	 */
	public static function admin_page( $pages ) {
		$pages[ self::PAGE ] = array(
			'menu'       => __( 'Product Questions', 'envo-blocks' ),
			'title'      => __( 'Product Questions', 'envo-blocks' ),
			'callback'   => array( __CLASS__, 'render' ),
			'capability' => 'moderate_comments',
			'section'    => 'store',
		);
		return $pages;
	}

	/**
	 * Styles of the page.
	 *
	 * @param string $hook Admin page.
	 */
	public static function admin_assets( $hook ) {
		if ( false !== strpos( (string) $hook, self::PAGE ) ) {
			wp_enqueue_style( 'envo-blocks-checkout-fields', ENVO_BLOCKS_URL . 'assets/css/checkout-fields.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/checkout-fields.css' ) );
			wp_enqueue_style( 'envo-blocks-questions-admin', ENVO_BLOCKS_URL . 'assets/css/product-questions-admin.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/product-questions-admin.css' ) );
		}
	}

	/**
	 * The page: settings and the questions.
	 */
	public static function render() {
		if ( ! current_user_can( 'moderate_comments' ) ) {
			return;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Notices and the filter of the list.
		$done   = isset( $_GET['eb-q'] ) ? sanitize_key( wp_unslash( $_GET['eb-q'] ) ) : '';
		$filter = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : 'waiting';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		$s       = self::settings();
		$filters = array(
			'waiting'  => __( 'Waiting for an answer', 'envo-blocks' ),
			'answered' => __( 'Answered', 'envo-blocks' ),
			'hidden'   => __( 'Hidden', 'envo-blocks' ),
		);
		$filter  = isset( $filters[ $filter ] ) ? $filter : 'waiting';
		$args    = array(
			'type'    => self::TYPE,
			'number'  => 100,
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC',
		);
		if ( 'answered' === $filter ) {
			$args['status'] = 'approve';
		} else {
			$args['status'] = 'hold';
			// Waiting: no answer and not hidden; hidden: answered and taken back, or hidden without an answer.
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Admin list.
				'relation' => 'waiting' === $filter ? 'AND' : 'OR',
				array(
					'key'     => '_envo_answer',
					'compare' => 'waiting' === $filter ? 'NOT EXISTS' : 'EXISTS',
				),
				array(
					'key'     => '_envo_hidden',
					'compare' => 'waiting' === $filter ? 'NOT EXISTS' : 'EXISTS',
				),
			);
		}
		$questions = get_comments( $args );
		$notices   = array(
			'saved'     => __( 'The settings were saved.', 'envo-blocks' ),
			'answered'  => __( 'The answer is published.', 'envo-blocks' ),
			'mailed'    => __( 'The answer is published and e-mailed to the customer.', 'envo-blocks' ),
			'hidden'    => __( 'The question is hidden.', 'envo-blocks' ),
			'deleted'   => __( 'The question is in the trash.', 'envo-blocks' ),
		);
		?>
		<div class="eb-cf eb-pq-admin">
			<?php if ( isset( $notices[ $done ] ) ) : ?>
				<div class="notice notice-success inline"><p><?php echo esc_html( $notices[ $done ] ); ?></p></div>
			<?php endif; ?>
			<section class="eb-cf__card">
				<h2><?php esc_html_e( 'Questions', 'envo-blocks' ); ?></h2>
				<p class="eb-cf__intro"><?php esc_html_e( 'Customers ask about a product on the product page; a question shows there once you answer it. Publishing an answer e-mails it to the customer when they asked for it.', 'envo-blocks' ); ?></p>
				<p class="eb-pq-admin__filters">
					<?php foreach ( $filters as $key => $label ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE . '&status=' . $key ) ); ?>"<?php echo $filter === $key ? ' class="is-current" aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</p>
				<?php if ( ! $questions ) : ?>
					<p class="eb-cf__intro"><?php esc_html_e( 'No questions here.', 'envo-blocks' ); ?></p>
				<?php endif; ?>
				<?php foreach ( $questions as $question ) : ?>
					<?php
					$product = wc_get_product( $question->comment_post_ID );
					$answer  = (string) get_comment_meta( $question->comment_ID, '_envo_answer', true );
					?>
					<form class="eb-pq-admin__item" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="envo_blocks_question_answer">
						<input type="hidden" name="question" value="<?php echo (int) $question->comment_ID; ?>">
						<input type="hidden" name="status" value="<?php echo esc_attr( $filter ); ?>">
						<?php wp_nonce_field( 'envo_blocks_question_' . $question->comment_ID ); ?>
						<p class="eb-pq-admin__meta">
							<?php if ( $product ) : ?>
								<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><strong><?php echo esc_html( $product->get_name() ); ?></strong></a>
							<?php endif; ?>
							· <?php echo esc_html( $question->comment_author ); ?> &lt;<a href="mailto:<?php echo esc_attr( $question->comment_author_email ); ?>"><?php echo esc_html( $question->comment_author_email ); ?></a>&gt;
							· <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $question->comment_date ) ) ); ?>
							<?php if ( get_comment_meta( $question->comment_ID, '_envo_notify', true ) ) : ?>
								· <span class="eb-pq-admin__notify"><?php esc_html_e( 'wants the answer by e-mail', 'envo-blocks' ); ?></span>
							<?php endif; ?>
						</p>
						<blockquote class="eb-pq-admin__q"><?php echo nl2br( esc_html( $question->comment_content ) ); ?></blockquote>
						<label class="screen-reader-text" for="eb-pq-answer-<?php echo (int) $question->comment_ID; ?>"><?php esc_html_e( 'Answer', 'envo-blocks' ); ?></label>
						<textarea id="eb-pq-answer-<?php echo (int) $question->comment_ID; ?>" name="answer" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Your answer…', 'envo-blocks' ); ?>"><?php echo esc_textarea( $answer ); ?></textarea>
						<p class="eb-pq-admin__buttons">
							<button type="submit" name="do" value="publish" class="button button-primary"><?php echo esc_html( 'answered' === $filter ? __( 'Update answer', 'envo-blocks' ) : __( 'Publish answer', 'envo-blocks' ) ); ?></button>
							<?php if ( 'hidden' !== $filter ) : ?>
								<button type="submit" name="do" value="hide" class="button"><?php esc_html_e( 'Hide', 'envo-blocks' ); ?></button>
							<?php endif; ?>
							<button type="submit" name="do" value="delete" class="button-link eb-pq-admin__delete"><?php esc_html_e( 'Delete', 'envo-blocks' ); ?></button>
						</p>
					</form>
				<?php endforeach; ?>
			</section>

			<section class="eb-cf__card">
				<h2><?php esc_html_e( 'Settings', 'envo-blocks' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="envo_blocks_questions">
					<?php wp_nonce_field( 'envo_blocks_questions' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Product page', 'envo-blocks' ); ?></th>
							<td>
								<label><input type="checkbox" name="tab" value="1" <?php checked( $s['tab'] ); ?>> <?php esc_html_e( 'Add a "Questions" tab to the product page', 'envo-blocks' ); ?></label>
								<p class="description"><?php esc_html_e( 'A tab next to Description and Reviews, in every theme. Or place the Product Questions block yourself, e.g. in the product template.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Who can ask', 'envo-blocks' ); ?></th>
							<td><label><input type="checkbox" name="guests" value="1" <?php checked( $s['guests'] ); ?>> <?php esc_html_e( 'Also visitors who are not signed in (with name and e-mail)', 'envo-blocks' ); ?></label></td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-pq-email"><?php esc_html_e( 'New questions', 'envo-blocks' ); ?></label></th>
							<td>
								<label><input type="checkbox" name="notify" value="1" <?php checked( $s['notify'] ); ?>> <?php esc_html_e( 'E-mail me every new question', 'envo-blocks' ); ?></label>
								<p><input type="email" id="eb-pq-email" name="email" class="regular-text" value="<?php echo esc_attr( $s['email'] ); ?>"></p>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Save changes', 'envo-blocks' ) ); ?>
				</form>
			</section>
		</div>
		<?php
	}

	/**
	 * Saves the settings (admin-post.php).
	 */
	public static function save_settings() {
		if ( ! current_user_can( 'moderate_comments' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to change these settings.', 'envo-blocks' ), 403 );
		}
		check_admin_referer( 'envo_blocks_questions' );
		update_option(
			self::OPTION,
			array(
				'tab'    => ! empty( $_POST['tab'] ),
				'guests' => ! empty( $_POST['guests'] ),
				'notify' => ! empty( $_POST['notify'] ),
				'email'  => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			)
		);
		wp_safe_redirect( add_query_arg( 'eb-q', 'saved', admin_url( 'admin.php?page=' . self::PAGE ) ) );
		exit;
	}

	/**
	 * Publishes an answer, hides or deletes a question (admin-post.php).
	 */
	public static function answer() {
		$id = isset( $_POST['question'] ) ? absint( $_POST['question'] ) : 0;
		if ( ! current_user_can( 'moderate_comments' ) || ! current_user_can( 'edit_comment', $id ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to change these settings.', 'envo-blocks' ), 403 );
		}
		check_admin_referer( 'envo_blocks_question_' . $id );
		$question = get_comment( $id );
		if ( ! $question || self::TYPE !== $question->comment_type ) {
			wp_die( esc_html__( 'The question does not exist.', 'envo-blocks' ), 404 );
		}
		$do     = isset( $_POST['do'] ) ? sanitize_key( wp_unslash( $_POST['do'] ) ) : '';
		$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'waiting';
		$answer = isset( $_POST['answer'] ) ? trim( wp_kses_post( wp_unslash( $_POST['answer'] ) ) ) : '';
		$done   = 'answered';

		if ( 'delete' === $do ) {
			wp_trash_comment( $id );
			$done = 'deleted';
		} elseif ( 'hide' === $do ) {
			wp_set_comment_status( $id, 'hold' );
			update_comment_meta( $id, '_envo_hidden', 1 );
			$done = 'hidden';
		} elseif ( '' !== $answer ) {
			$first = '' === (string) get_comment_meta( $id, '_envo_answer', true );
			update_comment_meta( $id, '_envo_answer', wp_slash( $answer ) );
			update_comment_meta( $id, '_envo_answer_date', time() );
			update_comment_meta( $id, '_envo_answer_by', get_current_user_id() );
			delete_comment_meta( $id, '_envo_hidden' );
			wp_set_comment_status( $id, 'approve' );
			if ( $first && get_comment_meta( $id, '_envo_notify', true ) && self::mail_answer( get_comment( $id ) ) ) {
				$done = 'mailed';
			}
		} else {
			$done = '';
		}
		wp_safe_redirect( add_query_arg( array_filter( array( 'eb-q' => $done, 'status' => $status ) ), admin_url( 'admin.php?page=' . self::PAGE ) ) );
		exit;
	}

	/**
	 * E-mail to the customer with the answer.
	 *
	 * @param WP_Comment $question Question.
	 * @return bool
	 */
	private static function mail_answer( $question ) {
		$product = wc_get_product( $question->comment_post_ID );
		if ( ! $product || ! is_email( $question->comment_author_email ) ) {
			return false;
		}
		$link = $product->get_permalink() . '#questions-' . $product->get_id();
		/* translators: %s: product name */
		$subject = sprintf( __( 'Your question about %s was answered', 'envo-blocks' ), $product->get_name() );
		/* translators: %s: first name */
		$body = '<p>' . esc_html( sprintf( __( 'Hi %s,', 'envo-blocks' ), $question->comment_author ) ) . '</p>'
			/* translators: %s: product name (a link) */
			. '<p>' . sprintf( esc_html__( 'thank you for your question about %s.', 'envo-blocks' ), '<a href="' . esc_url( $link ) . '">' . esc_html( $product->get_name() ) . '</a>' ) . '</p>'
			. '<blockquote style="margin:0 0 16px;padding:8px 16px;border-left:3px solid #ddd;color:#555">' . nl2br( esc_html( $question->comment_content ) ) . '</blockquote>'
			. wp_kses_post( wpautop( (string) get_comment_meta( $question->comment_ID, '_envo_answer', true ) ) )
			. '<p><a href="' . esc_url( $link ) . '">' . esc_html__( 'See the product and the other questions', 'envo-blocks' ) . '</a></p>';
		return self::mail( $question->comment_author_email, $subject, __( 'Your question was answered', 'envo-blocks' ), $body, self::settings()['email'] );
	}
}
