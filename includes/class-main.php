<?php
/**
 * Created by PhpStorm.
 * User: mihdan
 * Date: 06.02.19
 * Time: 21:57
 */
namespace Mihdan\MailRuPulseFeed;

use WPTRT\AdminNotices\Notices;

class Main {

	private $slug;
	private $feedname;
	private $allowable_tags = array(
		'p' => array(),
	);

	private $total_posts = 100;

	/**
	 * @var array список постов для вывода
	 */
	private $post_type = array( 'post', 'publication' );

	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * @var Notifications
	 */
	private $notifications;

	public function __construct() {
		$this->setup();
		$this->hooks();
	}

	private function setup() {
		$this->slug          = str_replace( '-', '_', MIHDAN_MAILRU_PULSE_FEED_SLUG );
		$this->settings      = new Settings();
		$this->notifications = new Notifications();
	}

	private function hooks() {
		add_action( 'init', array( $this, 'add_feed' ) );
		add_action( 'init', array( $this, 'flush_rewrite_rules' ), 99 );
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
		add_filter( 'wpseo_include_rss_footer', array( $this, 'hide_wpseo_rss_footer' ) );
		add_filter( 'the_excerpt_rss', array( $this, 'the_excerpt_rss' ), 99 );
		add_action( 'template_redirect', array( $this, 'send_headers_for_aio_seo_pack' ), 20 );
		add_action( 'pre_get_posts', array( $this, 'alter_query' ) );

		register_activation_hook( MIHDAN_MAILRU_PULSE_FEED_FILE, array( $this, 'on_activate' ) );
		register_deactivation_hook( MIHDAN_MAILRU_PULSE_FEED_FILE, array( $this, 'on_deactivate' ) );
	}

	/**
	 * Подправляем основной луп фида
	 *
	 * @param \WP_Query $wp_query объект запроса
	 */
	public function alter_query( \WP_Query $wp_query ) {
		if ( $wp_query->is_main_query() && $wp_query->is_feed( $this->feedname ) ) {
			// Ограничить посты 50-ю
			$wp_query->set( 'posts_per_rss', $this->total_posts );
			// Впариваем нужные нам типы постов
			$wp_query->set( 'post_type', $this->post_type );
			// Указываем поле для сортировки.
			$wp_query->set( 'orderby', 'modified' );
			// Указываем направление сортировки.
			$wp_query->set( 'order', 'DESC' );
		}
	}

	function the_excerpt_rss( $excerpt ) {
		if ( is_feed( $this->feedname ) ) {
			$excerpt = wp_kses( $excerpt, $this->allowable_tags );
		}

		return $excerpt;
	}

	public function after_setup_theme() {
		$this->feedname = apply_filters( 'mihdan_mailru_pulse_feed_feedname', MIHDAN_MAILRU_PULSE_FEED_SLUG );
	}

	public function add_feed() {
		add_feed( $this->feedname, array( $this, 'require_feed_template' ) );
	}

	public function require_feed_template() {
		require MIHDAN_MAILRU_PULSE_FEED_PATH . '/templates/feed.php';
	}

	public function flush_rewrite_rules() {

		// Ищем опцию.
		if ( get_option( $this->slug . '_flush_rewrite_rules' ) ) {

			// Скидываем реврайты.
			flush_rewrite_rules();

			// Удаляем опцию.
			delete_option( $this->slug . '_flush_rewrite_rules' );
		}
	}

	public function hide_wpseo_rss_footer( $include_footer = true ) {

		if ( is_feed( $this->feedname ) ) {
			$include_footer = false;
		}

		return $include_footer;
	}

	public function send_headers_for_aio_seo_pack() {
		// Добавим заголовок `X-Robots-Tag`
		// для решения проблемы с сеошными плагинами.
		header( 'X-Robots-Tag: index, follow', true );
	}

	public function on_activate() {

		// Добавим флаг, свидетельствующий о том,
		// что нужно сбросить реврайты.
		update_option( $this->slug . '_flush_rewrite_rules', 1, true );
	}

	public function on_deactivate() {

		// Сбросить правила реврайтов
		flush_rewrite_rules();
	}
}

// eof;
