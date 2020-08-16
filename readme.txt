=== Mihdan: Mail.ru Pulse Feed ===
Contributors: mihdan
Tags: mailru, pulse, feed, seo, seo-friendly
Requires at least: 5.0
Tested up to: 5.5
Stable tag: 0.3.6
Requires PHP: 5.6.20

WordPress плагин, формирующий ленту для новой рекомендательной системы Пульс от компании Mail.ru.

== Description ==

WordPress плагин, формирующий ленту для новой рекомендательной системы Пульс от компании Mail.ru. Пульс создает персонализованный контент на базе технологий машинного обучения.

Сразу после установки и активации плагина лента будет доступна по адресу: `http://example.com/feed/mihdan-mailru-pulse-feed`

== Installation ==

1. Upload `mihdan-mailru-pulse-feed` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Где искать созданную RSS ленту =

Сразу после установки плагина RSS лента будет доступна по адресу `http://example.com/feed/mihdan-mailru-pulse-feed`.

= Как изменить слаг ленты =

Добавьте в файл `functions.php` вашей дочерней темы или через плагин сниппетов следующий код:

`
add_filter(
	'mihdan_mailru_pulse_feed_feedname',
	function() {
    	return 'mailru';
	}
);
`

= Как вставить изображение записи в начало полнотекстовой ленты =

В настройках ленты поставьте галочку "Вставить изображение записи в начало полнотекстовой ленты".

= Вместо ленты я вижу с ошибку 404 =

Скорее всего, нужно обновить постоянные ссылки. Перейти Консоль -> Настройки -> Постоянные ссылки. После посещения этой страницы в админке попробуйте снова открыть вашу ленту.

= Как подключиться к Пульсу =

Перейдите на [официальный сайт](https://pulse.mail.ru/) рекомендательной системы Пульс и щёлкните по ссылке "Для паблишеров".

Для подключения потребуется:

1. RSS с анонсами публикаций. Формат и требования к RSS доступны по [ссылке](https://help.mail.ru/feed/rss). Материалы, попадающие в RSS также должны соответствовать нашим [требованиям](https://help.mail.ru/feed/policy). Материалы в RSS необходимо регулярно обновлять (не реже одного раза в три дня), иначе наша система может посчитать, что источник не работает.
2. Установленный на вашем сайте счетчик [Рейтинг Mail.ru](https://top.mail.ru/). Счетчик должен быть установлен на страницах материалов, которые попадают в RSS. Пожалуйста, пришлите нам ID установленного счетчика.
3. Пройти модерацию

= Как помочь в развитии проекта =

Присоединяйтесь к нам в [официальном GitHub репозитории](https://github.com/mihdan/mihdan-mailru-pulse-feed).

= Где найти идентификатор Виджета =

Идентификатор можно посмотреть в разделе "Личный кабинет партнёра &rarr; <a href="https://pulse.mail.ru/cabinet/widgets" target="_blank">Виджеты</a>.

= Как вставить Виджет в конец записи =

В плагине есть возможность автоматически вставлять Виджет в конец содержимого записи/страницы/товара. Для этого в настройках плагина в разделе "Виджет" укажите "ID виджета" и поставьте галочку "Автовставка".

= Как вставить Виджет через редактор блоков Gutenberg =

Откройте на редактирование запись, в которую вы хотите добавить Виджет. В нужном месте записи нажмите "плюсик", в окне выбора блоков щёлкните на "Шорткод" и впишите туда `[mihdan-mailru-pulse-widget]`.

= Как вставить Виджет в любое место темы =

Откройте на редактирование интересующий вас файл темы и в нужном месте впишите вызов шорткода плагина:

`
<?php echo do_shortcode( '[mihdan-mailru-pulse-widget]' ); ?>
`
= Как включить поддержку полнотекстовых публикаций =

В настройках плагина в разделе "Лента" поставьте галочку "Полностраничная".

== Changelog ==

= 0.3.6 (17.08.2020) =
* Fixed bug with plugin update

= 0.3.5 (16.08.2020) =
* Added new filter `mihdan_mailru_pulse_feed_entities_replacement`
* Added support for WordPress 5.5
* Fixed bug with `EntityRef`

= 0.3.4 (21.05.2020) =
* Added a post thumbnail to beginning of the feed item

= 0.3.3 (06.05.2020) =
* Wrap all `<video>` with `<figure>`
* Wrap all `<iframe>` with `<figure>`
* Remove all parent `<a>` for `<img>`

= 0.3.2 (27.04.2020) =
* Added h1-h6 tags to allowed list
* Added table/tbody/tr/th/td tags to allowed list
* Fixed bug with enclosures list

= 0.3.1 (24.04.2020) =
* Fixed bug with allowed tags
* Fixed bug with charset in post content

= 0.3 (24.04.2020) =
* Added settings for Yoast SEO footer
* Added settings for HTML5 support
* Wrap all `<img>` with `<figure>` via DOMDocument.

= 0.2.3 (20.04.2020) =
* Wrap image with `<figure>` tag for fulltext.
* Added `<figure>`, `<figcaption>`, `<iframe>` to allowable tags
* Added filter `mihdan_mailru_pulse_feed_item_excerpt`
* Added filter `mihdan_mailru_pulse_feed_item_content`

= 0.2.2 (10.02.2020) =
* Added `uninstall.php` for remove options on uninstall
* Updated requirements
* Set autoload=false for performance

= 0.2.1 (25.01.2020) =
* Added more allowable tags for excerpt
* Added support for AMP links

= 0.2 (22.01.2020) =
* Added Pulse widget for pages & posts
* Added Pulse widget for custom post types
* Added shortcode `[pulse]` for widget
* Added tag `<content:encoded>` for fulltext
* Added hook `mihdan_mailru_pulse_feed_item`

= 0.1.7 (10.01.2020) =
* Fixed bug with CPT

= 0.1.6 (31.12.2019) =
* Fixed bug with `<image>` tag.

= 0.1.5 (28.12.2019) =
* Fixed bugs

= 0.1.4 (27.12.2019) =
* Ability to exclude posts from feed
* Ability to exclude categories from feed

= 0.1.3 (27.12.2019) =
* Added settings for feed source
* Added new hook `mihdan_mailru_pulse_feed_head`
* Added setting link to plugins list
* Fixed bugs

= 0.1.2 (06.12.2019) =
* Added default settings to prevent fatal error

= 0.1.1 (28.11.2019) =
* Updated readme.txt
* Fixed error with localization

= 0.1 (24.10.2019) =
* Добавил страницу настроек
* Добавил систему уведомлений
* Обновил зависимости

= 0.0.2 (25.02.2019) =
* Обновил FAQ
* Обновил readme.txt
* Добавил ресурсы плагина

= 0.0.1 (07.02.2019) =
* Initial release
