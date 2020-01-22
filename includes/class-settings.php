<?php
/**
 * @package mihdan-mailru-pulse-feed
 * @link https://help.mail.ru/feed/rss
 */
namespace Mihdan\MailRuPulseFeed;

class Settings {
	/**
	 * @var WP_OSA
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
	 * @param WP_OSA $wposa_obj
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
	 * Hooks init.
	 */
	public function hooks() {
		add_action( 'init', [ $this, 'setup' ], 100 );
		add_action( 'init', [ $this, 'fields' ], 111 );
	}

	public function fields() {
		$this->wposa_obj->add_section(
			array(
				'id'    => 'feed',
				'title' => __( 'Feed', 'mihdan-mailru-pulse-feed' ),
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
				'default' => array( 'category' => 'category' ),
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
				'id'   => 'fulltext',
				'type' => 'checkbox',
				'name' => __( 'Fulltext', 'mihdan-mailru-pulse-feed' ),
				'desc' => __( 'Enable Fulltext Support', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_section(
			array(
				'id'    => 'source',
				'title' => __( 'Source', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'source',
			array(
				'id'   => 'slug',
				'type' => 'html',
				'name' => __( 'URL', 'mihdan-mailru-pulse-feed' ),
				'desc' => sprintf(
					/* translators: URL to feed */
					__( 'Your feed is available by url <a href="%1$s" target="_blank">%1$s</a>.', 'mihdan-mailru-pulse-feed' ),
					get_home_url() . '/feed/mihdan-mailru-pulse-feed/'
				),
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

		$this->wposa_obj->add_section(
			array(
				'id'    => 'widget',
				'title' => __( 'Widget', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'widget',
			array(
				'id'   => 'shortcode',
				'type' => 'html',
				'name' => __( 'Shortcode', 'mihdan-mailru-pulse-feed' ),
				'desc' => __( 'Widget also available by shortcode <code>[mihdan-mailru-pulse-widget]</code>', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'widget',
			array(
				'id'          => 'id',
				'type'        => 'text',
				'name'        => __( 'Widget ID', 'mihdan-mailru-pulse-feed' ),
				'placeholder' => 'partners_widget_domain',
				'desc'        => __( 'Идентификатор можно посмотреть в разделе "Личный кабинет партнёра &rarr; <a href="https://pulse.mail.ru/cabinet/widgets" target="_blank">Виджеты</a>"', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'widget',
			array(
				'id'   => 'auto_append',
				'type' => 'checkbox',
				'name' => __( 'Auto Append', 'mihdan-mailru-pulse-feed' ),
				'desc' => __( 'Автоматически добавлять виджет в конец записей', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_section(
			array(
				'id'    => 'contacts',
				'title' => __( 'Contacts', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'contacts',
			array(
				'id'   => 'help',
				'type' => 'html',
				'name' => __( 'Помощь', 'mihdan-mailru-pulse-feed' ),
				'desc' => __( 'Нужна помощь?<br />По всем вопросам пишите в телеграм <a href="https://t.me/mihdan" target="_blank">@mihdan</a>.', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'contacts',
			array(
				'id'   => 'donate',
				'type' => 'html',
				'name' => __( 'Благодарность', 'mihdan-mailru-pulse-feed' ),
				'desc' => __( 'Хотите отблагодарить автора?<br />Сделать это можно на <a href="https://www.kobzarev.com/donate/" target="_blank">официальном сайте</a>.', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'contacts',
			array(
				'id'   => 'mark',
				'type' => 'html',
				'name' => __( 'Оценка', 'mihdan-mailru-pulse-feed' ),
				'desc' => __( 'Хотите оценить плагин ★★★★★?<br />Сделать это можно на <a href="https://wordpress.org/support/plugin/mihdan-mailru-pulse-feed/reviews/?rate=5#new-post" target="_blank">официальном странице</a> плагина.', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'contacts',
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
