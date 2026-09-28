# Zen Feed
Плагин формирует RSS-ленту (фид), которая подходит для таких сервисов как: "Свежее и актуальное" в панели вебмастера Яндекс, "Яндекс.Новости", "Дзен" (как для паблишеров, так и для новостных агентств) и "Пульс" от Mail.ru.

В каждой записи ленты выводится один тег `<category>`. Основной термин берётся у загруженного SEO-плагина и проверяется: он должен быть назначен записи и относиться к таксономии, выбранной в настройках ленты.

| SEO-плагин | Источник основной категории |
| --- | --- |
| Yoast SEO | [`WPSEO_Primary_Term::get_primary_term()`](https://github.com/Yoast/wordpress-seo/blob/trunk/inc/class-wpseo-primary-term.php) |
| The SEO Framework | [`tsf()->data()->plugin()->post()->get_primary_term()`](https://github.com/sybrew/the-seo-framework/blob/master/inc/classes/data/plugin/post.class.php), включая фильтр `the_seo_framework_primary_term`; для версий до 5.0 — `the_seo_framework()->get_primary_term()` |
| Rank Math | [`RankMath\Helper::get_post_meta( 'primary_' . $taxonomy, $post_id )`](https://github.com/rankmath/seo-by-rank-math/blob/master/includes/class-common.php), как в самом плагине; внутренний метод `get_primary_term()` закрытый |
| All in One SEO | [`aioseo()->standalone->primaryTerm->getPrimaryTerm()`](https://aioseo.com/how-to-use-primary-category-to-customize-breadcrumbs-wordpress/); выбор основной категории доступен в Pro |
| SEOPress | [`get_post_meta( $post_id, '_seopress_robots_primary_cat', true )`](https://www.seopress.org/support/guides/list-of-all-post-metas-generated-by-seopress/); только для таксономии `category` |

При нескольких загруженных SEO-плагинах используется первый подходящий термин в порядке таблицы. Стандартная таксономия `category` имеет приоритет перед остальными выбранными таксономиями. Если SEO-плагины не вернули подходящий термин, выбирается первый термин по имени (по алфавиту). Если выбранные таксономии отключены, терминов нет или WordPress вернул ошибку, тег не добавляется. Метаданные отключённых SEO-плагинов не используются; при чтении метаданных действуют стандартные фильтры WordPress.
