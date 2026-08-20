=== Zen Feed ===
Contributors: mihdan
Tags: zen, vk, mailru, pulse, feed
Requires at least: 5.3
Tested up to: 7.1
Stable tag: 0.8.8
Requires PHP: 7.4
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.kobzarev.com/donate/

Плагин формирует RSS-ленту (фид), которая подходит для таких сервисов как: "Свежее и актуальное" в панели вебмастера Яндекс, "Яндекс.Новости", "Дзен" (как для паблишеров, так и для новостных агентств) и "Пульс" от Mail.ru..

== Description ==

Плагин формирует RSS-ленту (фид), которая подходит для таких сервисов как: "Свежее и актуальное" в панели вебмастера Яндекс, "Яндекс.Новости", "Дзен" (как для паблишеров, так и для новостных агентств) и "Пульс" от Mail.ru.

Сразу после установки и активации плагина лента будет доступна по адресу: `http://example.com/feed/mihdan-mailru-pulse-feed`

### ✅ Совместимость с сервисами, плагинами и темами ###

#### Сервисы ####
- Свежее и актуальное (Яндекс)
- Новости (Яндекс)
- Дзен (для новостей и вебмастеров)
- Пульс (Mail.Ru)

#### Плагины ####
- Elementor
- ACF
- Yoast SEO
- The SEO Framework
- SEOPress
- Rank Math

#### Темы ####
- The Voux
- Twenty Twenty

### ⛑️ Документация и поддержка ###

Если у вас возникли какие-то вопросы или появились предложения, милости просим на наш [форум поддержки](https://wordpress.org/support/plugin/mihdan-mailru-pulse-feed/) или [официальную страницу](https://www.kobzarev.com/projects/mail-ru-pulse-feed/) плагина.

### 💙 Нравится плагин? ###

Если плагин был полезен, поставьте ему [5 звезд](https://wordpress.org/support/plugin/mihdan-mailru-pulse-feed/reviews/#new-post) и напишите пару приятных слов.

== Installation ==

1. Upload `mihdan-mailru-pulse-feed` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Как подключиться к Пульсу =

Перейдите на [официальный сайт](https://pulse.mail.ru/) рекомендательной системы Пульс и щёлкните по ссылке "Для паблишеров".

Для подключения потребуется:

1. RSS с анонсами публикаций. Формат и требования к RSS доступны по [ссылке](https://help.mail.ru/feed/rss). Материалы, попадающие в RSS также должны соответствовать нашим [требованиям](https://help.mail.ru/feed/policy). Материалы в RSS необходимо регулярно обновлять (не реже одного раза в три дня), иначе наша система может посчитать, что источник не работает.
2. Установленный на вашем сайте счетчик [Рейтинг Mail.ru](https://top.mail.ru/). Счетчик должен быть установлен на страницах материалов, которые попадают в RSS. Пожалуйста, пришлите нам ID установленного счетчика.
3. Пройти модерацию

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

= Как автоматически исключать новые записи из ленты =

Добавьте в файл `functions.php` вашей дочерней темы или через плагин сниппетов следующий код:

`add_filter( 'mihdan_mailru_pulse_feed_exclude_post_by_default', '__return_true' );`

= Как автоматически исключать новые термы из ленты =

Добавьте в файл `functions.php` вашей дочерней темы или через плагин сниппетов следующий код:

`add_filter( 'mihdan_mailru_pulse_feed_exclude_term_by_default', '__return_true' );`


= Вместо ленты я вижу с ошибку 404 =

Скорее всего, нужно обновить постоянные ссылки. Перейти Консоль -> Настройки -> Постоянные ссылки. После посещения этой страницы в админке попробуйте снова открыть вашу ленту.

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

= 0.8.8 (20.08.2026) =
* Подтверждена совместимость с WordPress 7.1

= 0.8.7 (20.08.2026) =
* Подтверждена совместимость с WordPress 7.0

= 0.8.6 (20.08.2026) =
* Добавлен новый формат ленты `Zen & News (seamless)` для одновременного использования в Дзене и Новостях
* Проведён рефакторинг кода, улучшена архитектура плагина
* Повышена безопасность плагина

= 0.8.5 (28.09.2024) =
* Добавлена поддержка WordPress 6.6+
* Исправлена ошибка вставки изображений в ленту

= 0.8.4 (21.05.2024) =
* Исправлена ошибка вывода даты без указания часового пояса
* Исправлена опечатка в названиии тега `<yandex:fulltext/>`

= 0.8.3 (03.05.2024) =
* Исправлена оишбка вложенности тега `<channel/>` в ленте

= 0.8.2 (01.05.2024) =
* Изменен `Content-Type` ленты с `text/xml` на `application/rss+xml`

= 0.8.1 (30.04.2024) =
* Исправлены ошибки отображения пролога ленты

= 0.8.0 (22.04.2024) =
* Включена возможность использования полнотекстового формата на постоянной основе
* Отключен функционал виджета и его шорткода, так он больше не используется
* Исправлена ошибка с отключением таксономий
* Начат плавный переход от старой бибилиотеки `imangazaliev/didom` в пользу `symfony/serializer`

= 0.7.1 (18.11.2023) =
* Добавлена возможность изменять название ленты
* Добавлена поддержка WordPress 6.4+
* Обновлён пролог ленты в шаблоне RSS

= 0.7.0 (30.09.2023) =
* Добавлена возможность верификации сайта на платформе Дзен
* Произведён полный ребрендинг плагина в связи с объединением сервисов Яндекс.Дзен и Пульс от Mail.ru в единую платформу под названием Дзен.

= 0.6.0 (30.11.2022) =
* Обновлена минимальная поддерживаемая версия PHP 7.4+
* Добавлена интеграция с плагином Imagify
* Исправлены критические ошибки WPCS

= 0.5.0 (25.11.2022) =
* Добавлена поддержка WordPress 6.0+
* Добавлена ссылка на форум поддержки в Telegram
* Добавлена возможность автоматически удалять ссылки со всех изображений
* Добавлена поддержка Gutenberg блока "Галерея"
* Исправлена ссылка на ленту при отключенных пермалинках
* Исправлена ошибка в парсинге некоторых шоркодов WordPress

= 0.4 (15.10.2021) =
* Добавлена возможность отложить публикацию записей в ленте
* Добавлена возможность выбора размера для обложки записи
* Исправлена ошибка добавления лишнего слэша в amp-ссылки
* Исправлена ошибка сохранения настроек плагина по умолчанию
* Удалена неиспользуемая настройка для футера Yoast SEO

= 0.3.23 (29.04.2021) =
* Fixed bug with `figure` tag

= 0.3.22 (21.04.2021) =
* Added `guid` tag for feed

= 0.3.21 (19.04.2021) =
* Remove default site icon from feed

= 0.3.20 (03.04.2021) =
* Added a categories for feed items

= 0.3.19 (03.04.2021) =
* Remove paragraphs from all blockquotes
* Fixed bug with old libxml library
* Fixed bug "DOMElement::hasAttribute(): Couldn't fetch DOMElement"
* Fixed bug "Undefined property: DOMElement::$tagName"
* Fixed bug "Couldn't fetch DOMElement. Node no longer exists"

= 0.3.18 (24.03.2021) =
* Added the post thumbnail to the beginning of the list of enclosures

= 0.3.17 (23.03.2021) =
* Added settings for excluding blocks
* Added support for tagDiv sliders
* Updated DiDOM library
* Remove `&lt;style>`/`&lt;script>` tags
* Fixed [#14194837](https://wordpress.org/support/?p=14194837)

= 0.3.16 (07.03.2021) =
* Fixed bug with filter `mihdan_mailru_pulse_feed_exclude_post_by_default`

= 0.3.15 (08.02.2021) =
* Added a new tab in settings with other author plugins
* Fixed bug with admin meta box layout

= 0.3.14 (04.02.2021) =
* Fixed tons of bugs
* Added try/catch for DiDOM

= 0.3.13 (13.01.2021) =
* Added new filter `mihdan_mailru_pulse_feed_item_title`
* Added the ability to override the post title inside the feed
* Added the ability to override the post excerpt inside the feed

= 0.3.12 (13.01.2021) =
* Fixed bug with filter `mihdan_mailru_pulse_feed_feedname`

= 0.3.11 (02.12.2020) =
* Full text feed is now enabled by default
* Fixed bug "Couldn't fetch DOMElement"

= 0.3.10 (02.12.2020) =
* Added new filter `mihdan_mailru_pulse_feed_exclude_post_by_default`
* Added new filter `mihdan_mailru_pulse_feed_exclude_term_by_default`

= 0.3.9 (20.10.2020) =
* Updated readme
* Updated plugin assets

= 0.3.8 (19.10.2020) =
* Added new filter `mihdan_mailru_pulse_feed_allowable_tags`
* Added support for "The Voux" theme.

= 0.3.7 (17.08.2020) =
* Fixed bug with entities

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
