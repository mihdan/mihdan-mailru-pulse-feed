<?php
/**
 * Фабрика классов ленты.
 *
 * @package mihdan-mailru-pulse-feed
 */

namespace Mihdan\MailRuPulseFeed\Feed;

use Mihdan\MailRuPulseFeed\Feed;
use Mihdan\MailRuPulseFeed\Options;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * Создаёт нужный класс ленты в зависимости от опции `type`.
 *
 * Список типов вынесен из базового класса {@see Feed}, чтобы добавление
 * нового формата не требовало правки абстрактного класса — достаточно
 * повесить обработчик на фильтр `mihdan_mailru_pulse_feed_types`.
 */
class FeedFactory {
	/**
	 * Создаёт экземпляр класса ленты, соответствующий текущим настройкам.
	 *
	 * @param XmlEncoder $encoder Экземпляр класса XmlEncoder.
	 * @param Options    $options Экземпляр класса Options.
	 *
	 * @return Feed
	 */
	public static function make( XmlEncoder $encoder, Options $options ): Feed {
		$type  = $options->get_option( 'type', 'feed' );
		$types = self::get_types();

		$class = $types[ $type ] ?? $types['webmaster'];

		return new $class( $encoder, $options );
	}

	/**
	 * Возвращает карту `тип ленты => класс`.
	 *
	 * Сторонние плагины могут зарегистрировать свой тип ленты, добавив
	 * в массив пару `'my-type' => My_Feed_Class::class`, где класс наследует {@see Feed}.
	 *
	 * @return array<string, class-string<Feed>>
	 */
	public static function get_types(): array {
		$types = apply_filters(
			'mihdan_mailru_pulse_feed_types',
			[
				'webmaster' => WebmasterFeed::class,
				'agency'    => AgencyFeed::class,
				'zen-news'  => ZenNewsFeed::class,
			]
		);

		// Тип по умолчанию должен присутствовать всегда.
		if ( ! isset( $types['webmaster'] ) ) {
			$types['webmaster'] = WebmasterFeed::class;
		}

		return $types;
	}
}
