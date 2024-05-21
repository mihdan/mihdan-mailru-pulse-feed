<?php
/**
 * Шаблон вывода RSS ленты.
 *
 * @var Main $this
 * @package mihdan-mailru-pulse-feed
 */

namespace Mihdan\MailRuPulseFeed;

use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * Клас генерации ленты
 */
class Feed {
	/**
	 * Данные ленты
	 *
	 * @var array
	 */
	private array $data = [];

	/**
	 * Экземпляр класса XmlEncoder
	 */
	private XmlEncoder $encoder;

	/**
	 * Экземпляр класса Options
	 */
	private Options $options;

	/**
	 * Инициализация зависимостей.
	 *
	 * @param XmlEncoder  $encoder Экземпляр класса XmlEncoder.
	 * @param Options     $options Экземпляр класса Options.
	 */
	public function __construct( XmlEncoder $encoder, Options $options ) {
		$this->encoder = $encoder;
		$this->options = $options;

		$this->populate();
	}

	/**
	 * Наполняет массив.
	 *
	 * @return array
	 */
	public function populate() {

		$type = $this->options->get_option( 'type', 'feed' );

		$this->data['@version']     = '2.0';
		$this->data['@xmlns:media'] = 'http://search.yahoo.com/mrss/';

		// Для фида в Яндекс.Новости (чтобы работал тег yandex:fulltext).
		if ( $type === 'agency' ) {
			$this->data['@xmlns:yandex'] = 'http://news.yandex.ru';
		} else {
			$this->data['@xmlns:content'] = 'http://purl.org/rss/1.0/modules/content/';
			$this->data['@xmlns:dc']      = 'http://purl.org/dc/elements/1.1/';
			$this->data['@xmlns:atom']    = 'http://www.w3.org/2005/Atom';
			$this->data['@xmlns:georss']  = 'http://www.georss.org/georss';
		}

		$channel = [
			'title'       => esc_html( $this->options->get_option( 'title', 'source' ) ),
			'link'        => esc_url( $this->options->get_option( 'link', 'source' ) ),
			'description' => esc_html( $this->options->get_option( 'description', 'source' ) ),
			'language'    => esc_html( $this->options->get_option( 'language', 'source' ) ),
			'generator'   => 'Zen Feed by mihdan, v' . esc_html( MIHDAN_MAILRU_PULSE_FEED_VERSION ),
			'webMaster'   => 'mikhail@kobzarev.com (Mikhail Kobzarev)',
			'docs'        => 'https://ru.wordpress.org/plugins/mihdan-mailru-pulse-feed/',
			'image'       => [
				'url'   => esc_url( $this->options->get_option( 'image', 'source' ) ),
				'title' => esc_html( $this->options->get_option( 'title', 'source' ) ),
				'link'  => esc_url( $this->options->get_option( 'link', 'source' ) ),
			],
		];

		// Фильтрует шапку канала.
		$this->data['channel'] = apply_filters( 'mihdan_mailru_pulse_feed_head', $channel );

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();

				$item = [
					'link'        => apply_filters( 'the_permalink_rss', get_permalink() ),
					'guid'        => get_the_guid(),
					'title'       => $this->get_post_title( get_the_ID() ),
					'author'      => get_the_author(),
					'pubDate'     => get_post_time( 'r' ),
					'description' => $this->get_post_excerpt( get_the_ID() ),
				];

				/**
				 * Тип ленты.
				 *
				 * @link https://dzen.ru/help/ru/website/rss-modify.html
				 * @link https://dzen.ru/help/news/ru/export-content/export
				 * @link https://yandex.ru/support/webmaster/search-appearance/news.html
				 */
				if ( $type === 'webmaster' || $type === '' ) {
					$item['content:encoded'] = apply_filters( 'mihdan_mailru_pulse_feed_item_content', $this->get_the_content_feed( get_the_ID() ), get_the_ID() );
				} else {
					$item['yandex:full-text'] = apply_filters( 'mihdan_mailru_pulse_feed_item_content', $this->get_the_content_feed( get_the_ID() ), get_the_ID() );
				}

				// Фильтрует конкретный item.
				$this->data['channel']['item'][] = apply_filters(
					'mihdan_mailru_pulse_feed_item',
					$item,
					get_the_ID()
				);
			}
		}

		return $this->data;
	}

	/**
	 * Отрисовывает XML.
	 *
	 * @return void
	 */
	public function render(): void {
		header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . $this->options->get_option( 'charset', 'feed' ), true );

		// phpcs:disable.
		echo $this->encoder->encode(
			$this->data,
			XmlEncoder::FORMAT,
			[
				XmlEncoder::ROOT_NODE_NAME => 'rss',
				XmlEncoder::ENCODING => 'UTF-8',
				XmlEncoder::REMOVE_EMPTY_TAGS => true,
				XmlEncoder::FORMAT_OUTPUT => true,
			]
		);
		// phpcs:enable.
	}

	/**
	 * Get post title for rss item.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string
	 */
	public function get_post_title( $post_id = null ) {
		$title = get_the_title_rss();

		if ( ! empty( get_post_meta( $post_id, MIHDAN_MAILRU_PULSE_FEED_PREFIX . '_title', true ) ) ) {
			$title = get_post_meta( $post_id, MIHDAN_MAILRU_PULSE_FEED_PREFIX . '_title', true );
		}

		return apply_filters( 'mihdan_mailru_pulse_feed_item_title', $title, $post_id );
	}

	/**
	 * Get post excerpt for rss item.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string
	 */
	public function get_post_excerpt( $post_id = null ) {
		$excerpt = get_the_excerpt();

		if ( ! empty( get_post_meta( $post_id, MIHDAN_MAILRU_PULSE_FEED_PREFIX . '_excerpt', true ) ) ) {
			$excerpt = get_post_meta( $post_id, MIHDAN_MAILRU_PULSE_FEED_PREFIX . '_excerpt', true );
		}

		return apply_filters( 'mihdan_mailru_pulse_feed_item_excerpt', $excerpt, $post_id );
	}

	/**
	 * Get post content for rss item.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return mixed|string|string[]|void
	 */
	public function get_the_content_feed( $post_id = null ) {
		$content = apply_filters( 'the_content', get_the_content( null, false, $post_id ) );
		$content = str_replace( ']]>', ']]&gt;', $content );

		return $content;
	}
}
