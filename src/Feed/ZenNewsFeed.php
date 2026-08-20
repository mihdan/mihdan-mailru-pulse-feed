<?php
/**
 * Единая лента для Дзена и Новостей (Seamless RSS).
 *
 * @package mihdan-mailru-pulse-feed
 */

namespace Mihdan\MailRuPulseFeed\Feed;

/**
 * Тип ленты `zen-news`.
 *
 * Использует те же namespace-ы и `content:encoded`, что и {@see WebmasterFeed},
 * но дополнительно добавляет тег `<contentType>`, которым Дзен определяет,
 * где именно должен показываться материал.
 *
 * @link https://dzen.ru/help/ru/news/seamless/rss.html
 */
class ZenNewsFeed extends WebmasterFeed {
	/**
	 * Допустимые значения тега `<contentType>`.
	 */
	public const CONTENT_TYPES = [ 'news', 'news_only', 'blogs_only' ];

	/**
	 * {@inheritDoc}
	 */
	protected function add_content_fields( array $item, int $post_id ): array {
		$item = parent::add_content_fields( $item, $post_id );

		// Значение можно переопределить для конкретной записи в метабоксе.
		$content_type = get_post_meta( $post_id, MIHDAN_MAILRU_PULSE_FEED_PREFIX . '_content_type', true );

		if ( ! in_array( $content_type, self::CONTENT_TYPES, true ) ) {
			$content_type = $this->options->get_option( 'zen_news_content_type', 'feed', 'news' );
		}

		$item['contentType'] = esc_html( $content_type );

		return $item;
	}
}
