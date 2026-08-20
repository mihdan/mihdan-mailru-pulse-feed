<?php
/**
 * @package mihdan-mailru-pulse-feed
 * @link https://help.mail.ru/feed/rss
 */

namespace Mihdan\MailRuPulseFeed;

use WP_Plugin_Install_List_Table;

class Settings {
	/**
	 * @var Options
	 */
	private $wposa_obj;

	/**
	 * @var array $post_types
	 */
	private $post_types;

	/**
	 * @var array $taxonomies
	 */
	private $taxonomies;

	/**
	 * Settings constructor.
	 *
	 * @param Options  $wposa_obj
	 */
	public function __construct( $wposa_obj ) {

		$this->wposa_obj = $wposa_obj;
		$this->hooks();
	}

	public function setup() {
		// Список всех публичных CPT.
		$args = array(
			'public' => true,
		);

		$this->post_types = wp_list_pluck( get_post_types( $args, 'objects' ), 'label', 'name' );

		// Список всех зареганных таксономий.
		$args = array(
			'public' => true,
		);

		$this->taxonomies = wp_list_pluck( get_taxonomies( $args, 'objects' ), 'label', 'name' );
	}

	/**
	 * Возвращает callback для ограничения значения select-поля белым списком.
	 *
	 * @param array  $allowed Допустимые значения.
	 * @param string $default Значение по умолчанию, если пришло что-то чужое.
	 *
	 * @return callable
	 */
	private function sanitize_enum( array $allowed, string $default ): callable {
		return static function ( $value ) use ( $allowed, $default ) {
			return in_array( $value, $allowed, true ) ? $value : $default;
		};
	}

	/**
	 * Get all registered image sizes.
	 *
	 * @return array
	 */
	public function get_registered_image_sizes() {
		$sizes = [];

		foreach ( wp_get_registered_image_subsizes() as $key => $data ) {
			$sizes[ $key ] = $key;
		}

		return $sizes;
	}

	/**
	 * Hooks init.
	 */
	public function hooks() {
		add_action( 'init', [ $this, 'setup' ], 100 );
		add_action( 'init', [ $this, 'fields' ], 111 );
		add_filter( 'install_plugins_nonmenu_tabs', array( $this, 'install_plugins_nonmenu_tabs' ) );
		add_filter( 'install_plugins_table_api_args_' . MIHDAN_MAILRU_PULSE_FEED_SLUG, array( $this, 'install_plugins_table_api_args' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'plugin_install' );
		wp_enqueue_script( 'updates' );
		add_thickbox();
	}

	/**
	 * Форматирует строку с минутами или часами или днями,
	 * добавляя ведущие нули.
	 *
	 * @param string $str Входная строка.
	 *
	 * @return string
	 */
	public function format_date( $str ) {
		return sprintf( '%02d', $str );
	}

	public function install_plugins_nonmenu_tabs( $tabs ) {

		$tabs[] = MIHDAN_MAILRU_PULSE_FEED_SLUG;

		return $tabs;
	}

	public function install_plugins_table_api_args( $args ) {
		global $paged;

		return array(
			'page'     => $paged,
			'per_page' => 100,
			'locale'   => get_user_locale(),
			'author'   => 'mihdan',
		);
	}

	public function fields() {
		$this->wposa_obj->add_section(
			array(
				'id'    => 'source',
				'title' => __( 'Source', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'source',
			array(
				'id'   => 'zen_verification',
				'type' => 'text',
				'name' => __( 'Zen verification', 'mihdan-mailru-pulse-feed' ),
				'desc' => __( 'Добавляет тег <code>&lt;meta name="zen-verification"&gt;</code> <br />в код страницы для верификации вашего сайта в Дзене.', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'source',
			array(
				'id'      => 'title',
				'type'    => 'text',
				'name'    => __( 'Title', 'mihdan-mailru-pulse-feed' ),
				'default' => get_bloginfo_rss( 'name' ),
			)
		);

		$this->wposa_obj->add_field(
			'source',
			array(
				'id'      => 'link',
				'type'    => 'text',
				'name'    => __( 'Link', 'mihdan-mailru-pulse-feed' ),
				'default' => get_bloginfo_rss( 'url' ),
			)
		);

		$this->wposa_obj->add_field(
			'source',
			array(
				'id'      => 'description',
				'type'    => 'textarea',
				'name'    => __( 'Description', 'mihdan-mailru-pulse-feed' ),
				'default' => get_bloginfo_rss( 'description' ),
			)
		);

		$this->wposa_obj->add_field(
			'source',
			array(
				'id'      => 'language',
				'type'    => 'select',
				'name'    => __( 'Language', 'mihdan-mailru-pulse-feed' ),
				'options' => array(
					'ru' => __( 'Russian', 'mihdan-mailru-pulse-feed' ),
					'en' => __( 'English', 'mihdan-mailru-pulse-feed' ),
				),
				'default' => 'ru',
			)
		);

		$this->wposa_obj->add_field(
			'source',
			array(
				'id'      => 'image',
				'type'    => 'image',
				'name'    => __( 'Image', 'mihdan-mailru-pulse-feed' ),
				'desc'    => __( 'Размер картинки должен быть не менее 200 пикселей по ширине и высоте.<br />Изображение будет кадрировано до квадратного.<br />Не допускается анимация и прозрачный фон.', 'mihdan-mailru-pulse-feed' ),
				'default' => admin_url( 'images/w-logo-blue.png' ),
			)
		);

		/**
		 * Feed tab.
		 */
		$this->wposa_obj->add_section(
			array(
				'id'    => 'feed',
				'title' => __( 'Feed', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$feed_url = ( get_option( 'permalink_structure', '' ) === '' )
			? add_query_arg( [ 'feed' => Main::get_feed_name() ], home_url( '/' ) )
			: home_url( '/feed/' . Main::get_feed_name() );

		$this->wposa_obj->add_field(
			'feed',
			array(
				'id'      => 'slug',
				'type'    => 'text',
				'name'    => __( 'Slug', 'mihdan-mailru-pulse-feed' ),
				'default' => 'mihdan-mailru-pulse-feed',
				'desc'    => sprintf(
				/* translators: URL to feed */
					__( 'Your feed is available by <a href="%s" target="_blank">url</a>.', 'mihdan-mailru-pulse-feed' ),
					esc_url( $feed_url )
				),
			)
		);

		$this->wposa_obj->add_field(
			'feed',
			array(
				'id'      => 'type',
				'type'    => 'select',
				'name'    => __( 'Type', 'mihdan-mailru-pulse-feed' ),
				'options' => [
					'webmaster' => __( 'Webmaster', 'mihdan-mailru-pulse-feed' ),
					'agency'    => __( 'News Agency', 'mihdan-mailru-pulse-feed' ),
					'zen-news'  => __( 'Zen & News (seamless)', 'mihdan-mailru-pulse-feed' ),
				],
				'default'           => 'webmaster',
				'sanitize_callback' => $this->sanitize_enum( [ 'webmaster', 'agency', 'zen-news' ], 'webmaster' ),
			)
		);

		$this->wposa_obj->add_field(
			'feed',
			array(
				'id'        => 'zen_news_content_type',
				'type'      => 'select',
				'name'      => __( 'Content Type', 'mihdan-mailru-pulse-feed' ),
				'condition' => array(
					'field' => 'type',
					'value' => 'zen-news',
				),
				'desc'      => sprintf(
				/* translators: %s: URL to Dzen help page */
					__( 'Применяется только для типа ленты <b>Zen & News (seamless)</b>.<br />
					<b>news</b> — материал с полным текстом новости будет опубликован для показа и в Новостях, и в лентах Дзена. Из сервиса Новости в Дзене сообщение будет вести на статью в вашем канале.<br />
					<b>news_only</b> — уводящая на ваш сайт новость. Подходит только для сервиса Новости в Дзене.<br />
					<b>blogs_only</b> — статья («вечнозелёный» контент), предназначенная только для рекомендательных лент. Заголовки таких материалов могут не совпадать с заголовками на сайте.<br />
					Подробнее в <a href="%s" target="_blank">документации</a>.', 'mihdan-mailru-pulse-feed' ),
					esc_url( 'https://dzen.ru/help/ru/news/seamless/rss.html' )
				),
				'options' => array(
					'news'       => __( 'news — Дзен и Новости', 'mihdan-mailru-pulse-feed' ),
					'news_only'  => __( 'news_only — Только Новости', 'mihdan-mailru-pulse-feed' ),
					'blogs_only' => __( 'blogs_only — Только Дзен', 'mihdan-mailru-pulse-feed' ),
				),
				'default'           => 'news',
				'sanitize_callback' => $this->sanitize_enum( [ 'news', 'news_only', 'blogs_only' ], 'news' ),
			)
		);

		$this->wposa_obj->add_field(
			'feed',
			array(
				'id'      => 'charset',
				'type'    => 'select',
				'name'    => __( 'Feed Charset', 'mihdan-mailru-pulse-feed' ),
				'options' => array(
					'UTF-8'        => 'UTF-8',
					'KOI8-R'       => 'KOI8-R',
					'Windows-1251' => 'Windows-1251',
				),
				'default' => 'UTF-8',
			)
		);

		$this->wposa_obj->add_field(
			'feed',
			array(
				'id'      => 'orderby',
				'type'    => 'select',
				'name'    => __( 'Order By', 'mihdan-mailru-pulse-feed' ),
				'options' => array(
					'date'          => __( 'Date', 'mihdan-mailru-pulse-feed' ),
					'modified'      => __( 'Last modified date', 'mihdan-mailru-pulse-feed' ),
					'rand'          => __( 'Random', 'mihdan-mailru-pulse-feed' ),
					'ID'            => __( 'ID', 'mihdan-mailru-pulse-feed' ),
					'author'        => __( 'Author', 'mihdan-mailru-pulse-feed' ),
					'title'         => __( 'Title', 'mihdan-mailru-pulse-feed' ),
					'name'          => __( 'Post name', 'mihdan-mailru-pulse-feed' ),
					'type'          => __( 'Post type', 'mihdan-mailru-pulse-feed' ),
					'comment_count' => __( 'Comment_count', 'mihdan-mailru-pulse-feed' ),
					'relevance'     => __( 'Relevance', 'mihdan-mailru-pulse-feed' ),
					'menu_order'    => __( 'Menu order', 'mihdan-mailru-pulse-feed' ),
				),
				'default' => 'date',
			)
		);

		$this->wposa_obj->add_field(
			'feed',
			array(
				'id'      => 'order',
				'type'    => 'select',
				'name'    => __( 'Order', 'mihdan-mailru-pulse-feed' ),
				'options' => array(
					'DESC' => __( 'DESC', 'mihdan-mailru-pulse-feed' ),
					'ASC'  => __( 'ASC', 'mihdan-mailru-pulse-feed' ),
				),
				'default' => 'DESC',
			)
		);

		$this->wposa_obj->add_field(
			'feed',
			array(
				'id'      => 'post_types',
				'type'    => 'multicheck',
				'name'    => __( 'Post Types', 'mihdan-mailru-pulse-feed' ),
				'options' => $this->post_types,
				'default' => array( 'post' => 'post' ),
			)
		);

		$this->wposa_obj->add_field(
			'feed',
			array(
				'id'      => 'taxonomies',
				'type'    => 'multicheck',
				'name'    => __( 'Taxonomies', 'mihdan-mailru-pulse-feed' ),
				'options' => $this->taxonomies,
				'default' => [],
			)
		);

		$this->wposa_obj->add_field(
			'feed',
			array(
				'id'      => 'delayed_publication_unit',
				'type'    => 'select',
				'name'    => __( 'Delay Publication: Unit', 'mihdan-mailru-pulse-feed' ),
				'options' => array(
					'MINUTE' => __( 'Minutes', 'mihdan-mailru-pulse-feed' ),
					'HOUR'   => __( 'Hours', 'mihdan-mailru-pulse-feed' ),
					'DAY'    => __( 'Days', 'mihdan-mailru-pulse-feed' ),
					'WEEK'   => __( 'Week', 'mihdan-mailru-pulse-feed' ),
					'MONTH'  => __( 'Month', 'mihdan-mailru-pulse-feed' ),
					'YEAR'   => __( 'Year', 'mihdan-mailru-pulse-feed' ),
				),
				'default' => 'MINUTE',
			)
		);

		$this->wposa_obj->add_field(
			'feed',
			array(
				'id'      => 'delayed_publication_value',
				'type'    => 'number',
				'name'    => __( 'Delay Publication: Value', 'mihdan-mailru-pulse-feed' ),
				'default' => 0,
			)
		);

		$this->wposa_obj->add_field(
			'feed',
			array(
				'id'                => 'total_posts',
				'type'              => 'number',
				'name'              => __( 'Total Posts', 'mihdan-mailru-pulse-feed' ),
				'default'           => 1000,
				'sanitize_callback' => 'intval',
			)
		);

		$this->wposa_obj->add_field(
			'feed',
			array(
				'id'   => 'post_thumbnail',
				'type' => 'checkbox',
				'name' => __( 'Post thumbnail', 'mihdan-mailru-pulse-feed' ),
				'desc' => __( 'Add a post thumbnail to beginning of the feed item', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'feed',
			array(
				'id'      => 'post_thumbnail_size',
				'type'    => 'select',
				'name'    => __( 'Post thumbnail size', 'mihdan-mailru-pulse-feed' ),
				'desc'    => __( 'Add a post thumbnail to beginning of the feed item', 'mihdan-mailru-pulse-feed' ),
				'options' => $this->get_registered_image_sizes(),
				'default' => 'large',
			)
		);

		/**
		 * Content tab.
		 */
		$this->wposa_obj->add_section(
			array(
				'id'    => 'content',
				'title' => __( 'Content', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'content',
			array(
				'id'      => 'exclude',
				'type'    => 'textarea',
				'name'    => __( 'Exclude', 'mihdan-mailru-pulse-feed' ),
				'desc'    => __( 'Выражения <code>xpath</code> для исключения тегов, блоков, рекламных вставок <br />из содержимого записей. Каждое выражение с новой строки.', 'mihdan-mailru-pulse-feed' ),
				'placeholder' => __( 'Например, //div[contains(@class, \'td-doubleSlider-2\')]', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_section(
			array(
				'id'    => 'mmpf_plugins',
				'title' => __( 'Plugins', 'mihdan-mailru-pulse-feed' ),
				'desc'  => __( 'Другие плагины автора', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'mmpf_plugins',
			array(
				'id'   => 'plugins',
				'type' => 'html',
				'name' => '',
				'desc' => function () {
					$transient = MIHDAN_MAILRU_PULSE_FEED_SLUG . '-plugins';
					$cached    = get_transient( $transient );

					if ( false !== $cached ) {
						return $cached;
					}

					ob_start();
					require_once ABSPATH . 'wp-admin/includes/class-wp-plugin-install-list-table.php';
					$_POST['tab'] = MIHDAN_MAILRU_PULSE_FEED_SLUG;
					$table = new WP_Plugin_Install_List_Table();
					$table->prepare_items();


					$table->display();

					$content = ob_get_clean();
					set_transient( $transient, $content, 1 * DAY_IN_SECONDS );

					return $content;
				},
			)
		);

		$this->wposa_obj->add_section(
			array(
				'id'    => 'mmpf_contacts',
				'title' => __( 'Contacts', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'mmpf_contacts',
			array(
				'id'   => 'help',
				'type' => 'html',
				'name' => __( 'Помощь', 'mihdan-mailru-pulse-feed' ),
				'desc' => __( 'Нужна помощь?<br />По всем вопросам пишите в наш <a href="https://t.me/+uNVaYXy1H3wyYTEy" target="_blank">Telegram</a>.', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'mmpf_contacts',
			array(
				'id'   => 'donate',
				'type' => 'html',
				'name' => __( 'Благодарность', 'mihdan-mailru-pulse-feed' ),
				'desc' => __( 'Хотите отблагодарить автора?<br />Сделать это можно на <a href="https://www.kobzarev.com/donate/" target="_blank">официальном сайте</a>.', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'mmpf_contacts',
			array(
				'id'   => 'mark',
				'type' => 'html',
				'name' => __( 'Оценка', 'mihdan-mailru-pulse-feed' ),
				'desc' => __( 'Хотите оценить плагин ★★★★★?<br />Сделать это можно на <a href="https://wordpress.org/support/plugin/mihdan-mailru-pulse-feed/reviews/?rate=5#new-post" target="_blank">официальном странице</a> плагина.', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'mmpf_contacts',
			array(
				'id'   => 'plugins',
				'type' => 'html',
				'name' => __( 'Плагины автора', 'mihdan-mailru-pulse-feed' ),
				'desc' => __( 'Понравился плагин?<br />Остальные полезные плагины автора вы можете посмотреть в <a href="https://profiles.wordpress.org/mihdan/#content-plugins" target="_blank">официальном репозитории</a> wp.org.', 'mihdan-mailru-pulse-feed' ),
			)
		);
	}
}

// eol.
