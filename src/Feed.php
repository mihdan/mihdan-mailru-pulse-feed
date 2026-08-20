<?php
/**
 * Базовый класс генерации RSS ленты.
 *
 * @var Main $this
 * @package mihdan-mailru-pulse-feed
 */

namespace Mihdan\MailRuPulseFeed;

use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * Базовый клас генерации ленты.
 *
 * Общая логика (шапка канала, цикл по постам, рендер XML) живёт здесь.
 * Наследники отличаются только namespace-ами и способом добавления
 * полного текста записи в item.
 */
abstract class Feed {
	/**
	 * Данные ленты
	 *
	 * @var array
	 */
	protected array $data = [];

	/**
	 * Экземпляр класса XmlEncoder
	 */
	protected XmlEncoder $encoder;

	/**
	 * Экземпляр класса Options
	 */
	protected Options $options;

	/**
	 * Инициализация зависимостей.
	 *
	 * @param XmlEncoder $encoder Экземпляр класса XmlEncoder.
	 * @param Options    $options Экземпляр класса Options.
	 */
	public function __construct( XmlEncoder $encoder, Options $options ) {
		$this->encoder = $encoder;
		$this->options = $options;

		$this->populate();
	}

	/**
	 * Возвращает список xmlns-атрибутов, специфичных для типа ленты.
	 *
	 * @return array<string, string>
	 */
	abstract protected function get_namespaces(): array;

	/**
	 * Добавляет в item поля с полным текстом записи (и всё, что с этим связано).
	 *
	 * @param array $item    Item ленты.
	 * @param int   $post_id ID записи.
	 *
	 * @return array
	 */
	abstract protected function add_content_fields( array $item, int $post_id ): array;

	/**
	 * Наполняет массив.
	 *
	 * @return array
	 */
	public function populate() {

		$this->data['@version']     = '2.0';
		$this->data['@xmlns:media'] = 'http://search.yahoo.com/mrss/';

		foreach ( $this->get_namespaces() as $attribute => $url ) {
			$this->data[ $attribute ] = $url;
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
				 * Полный текст записи специфичен для типа ленты.
				 *
				 * @link https://dzen.ru/help/ru/website/rss-modify.html
				 * @link https://dzen.ru/help/news/ru/export-content/export
				 * @link https://dzen.ru/help/ru/news/seamless/rss.html
				 * @link https://yandex.ru/support/webmaster/search-appearance/news.html
				 */
				$item = $this->add_content_fields( $item, get_the_ID() );

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
			$title = esc_html( get_post_meta( $post_id, MIHDAN_MAILRU_PULSE_FEED_PREFIX . '_title', true ) );
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
