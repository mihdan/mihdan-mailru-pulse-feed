=== Mihdan: Mail.ru Pulse Feed ===
Contributors: mihdan
Tags: mailru, pulse, feed
Requires at least: 2.3
Tested up to: 5.1
Stable tag: 0.0.1
Requires PHP: 5.3

WordPress плагин, формирующий ленту для новой рекомендательной системы Пульс от компании Mail.ru.

== Description ==

WordPress плагин, формирующий ленту для новой рекомендательной системы Пульс от компании Mail.ru.
Сразу после установки и активации плагина лента будет доступна по адресу: `http://example.com/feed/mihdan-mailru-pulse-feed`

== Installation ==

1. Upload `mihdan-mailru-pulse-feed` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Как изменить слаг ленты =

Добавьте в файл `functions.php` вашей активной темы следующий код (лучше это делать в дочерней теме):

`
add_filter( 'mihdan_mailru_pulse_feed_feedname', function() {
    return 'mailru'
} );
`

= Can I contribute? =

Yes you can! Join in on our [GitHub repository](https://github.com/mihdan/cyr2lat)

== Changelog ==

= 0.0.1 (07.02.2019) =
* Initial release