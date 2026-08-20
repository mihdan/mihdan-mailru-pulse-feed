<?php
/**
 * Лента для Дзен.Вебмастера.
 *
 * @package mihdan-mailru-pulse-feed
 */

namespace Mihdan\MailRuPulseFeed\Feed;

use Mihdan\MailRuPulseFeed\Feed;

/**
 * Тип ленты `webmaster` (используется по умолчанию).
 *
 * Полный текст записи передаётся в стандартном RSS-теге `content:encoded`.
 *
 * @link https://dzen.ru/help/ru/website/rss-modify.html
 */
class WebmasterFeed extends Feed {
	/**
	 * {@inheritDoc}
	 */
	protected function get_namespaces(): array {
		return [
			'@xmlns:content' => 'http://purl.org/rss/1.0/modules/content/',
			'@xmlns:dc'      => 'http://purl.org/dc/elements/1.1/',
			'@xmlns:atom'    => 'http://www.w3.org/2005/Atom',
			'@xmlns:georss'  => 'http://www.georss.org/georss',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function add_content_fields( array $item, int $post_id ): array {
		$item['content:encoded'] = apply_filters( 'mihdan_mailru_pulse_feed_item_content', $this->get_the_content_feed( $post_id ), $post_id );

		return $item;
	}
}
