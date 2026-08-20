<?php
/**
 * Лента для Яндекс.Новостей.
 *
 * @package mihdan-mailru-pulse-feed
 */

namespace Mihdan\MailRuPulseFeed\Feed;

use Mihdan\MailRuPulseFeed\Feed;

/**
 * Тип ленты `agency`.
 *
 * Полный текст записи передаётся в теге `yandex:full-text`,
 * чтобы работал разбор ленты как у информационного агентства.
 *
 * @link https://yandex.ru/support/webmaster/search-appearance/news.html
 */
class AgencyFeed extends Feed {
	/**
	 * {@inheritDoc}
	 */
	protected function get_namespaces(): array {
		return [
			'@xmlns:yandex' => 'http://news.yandex.ru',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function add_content_fields( array $item, int $post_id ): array {
		$item['yandex:full-text'] = apply_filters( 'mihdan_mailru_pulse_feed_item_content', $this->get_the_content_feed( $post_id ), $post_id );

		return $item;
	}
}
