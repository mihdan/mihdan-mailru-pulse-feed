<?php
namespace Mihdan\MailRuPulseFeed;

class Settings {
	/**
	 * @var WP_OSA
	 */
	private $wposa_obj;

	/**
	 * @var string
	 */
	private $prefix = MIHDAN_MAILRU_PULSE_FEED_SLUG;

	/**
	 * @var string
	 */
	public $cpt_key = 'mytf';

	/**
	 * @var array $post_types
	 */
	private $post_types;

	/**
	 * @var array $taxonomies
	 */
	private $taxonomies;

	/**
	 * @var array $languages Массив всех языков сайта.
	 */
	private $languages = array();

	/**
	 * @var string @language Дефолтный язык - из настроек сайта.
	 */
	private $language;

	public function __construct( $wposa_obj ) {

		$this->wposa_obj = $wposa_obj;

		$this->setup();
		$this->hooks();
		$this->fields();
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

	public function hooks() {
		add_action( 'init', array( $this, 'registration' ) );
		//add_action( 'acf/init', array( $this, 'add_local_field_groups' ) );
	}

	private function fields() {
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

		$this->wposa_obj->add_section(
			array(
				'id'    => 'contacts',
				'title' => __( 'Contacts', 'mihdan-mailru-pulse-feed' ),
			)
		);

		$this->wposa_obj->add_field(
			'contacts',
			array(
				'id'   => 'donate',
				'type' => 'html',
				'name' => __( 'Нужна помощь?', 'mihdan-mailru-pulse-feed' ),
				'desc' => 'По всем вопросам пишите в телеграм @mihdan',
			)
		);
	}

	/**
	 * Регистрция произвольных типов записей и таксономий.
	 */
	public function registration() {
	}

	/**
	 * Создание метабокосов и настроек плагина и записей
	 *
	 * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
	 */
	public function add_local_field_groups() {



		/**
		 * Настройки ленты.
		 *
		 * @link https://yandex.ru/dev/turbo/doc/rss/elements/index-docpage/
		 */
		$feed_settings = new FieldsBuilder(
			'feed_settings',
			array(
				'title'                 => __( 'Settings', 'mihdan-yandex-turbo-feed' ),
				//'label_placement'       => 'left',
				'instruction_placement' => 'field',
				//'style'    => 'seamless',
			)
		);

		$feed_settings
			->addTab(
				'channel',
				array(
					'placement' => 'left',
					'label'     => __( 'Channel', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addText(
				$this->prefix . '_channel_title',
				array(
					'label'         => __( 'Channel Title', 'mihdan-yandex-turbo-feed' ),
					'default_value' => get_bloginfo_rss( 'name' ),
					'required'      => true,
				)
			)
			->addLink(
				$this->prefix . '_channel_link',
				array(
					'label'         => __( 'Channel Link', 'mihdan-yandex-turbo-feed' ),
					'default_value' => get_bloginfo_rss( 'url' ),
					'required'      => true,
				)
			)
			->addTextarea(
				$this->prefix . '_channel_description',
				array(
					'label'         => __( 'Channel Description', 'mihdan-yandex-turbo-feed' ),
					'default_value' => get_bloginfo_rss( 'description' ),
					'required'      => true,
				)
			)
			->addSelect(
				$this->prefix . '_channel_language',
				array(
					'label'         => __( 'Channel Language', 'mihdan-yandex-turbo-feed' ),
					'default_value' => $this->language,
					'choices'       => $this->languages,
				)
			)
			->addTab(
				'images',
				array(
					'placement' => 'left',
					'label'     => __( 'Images', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addText(
				$this->prefix . '_images_copyright',
				array(
					'label'         => __( 'Copyright', 'mihdan-yandex-turbo-feed' ),
					'default_value' => apply_filters(
						'mihdan_yandex_turbo_feed_copyright',
						$this->utils->get_site_domain()
					),
					'instructions'  => __( 'Adds Copyright To All Photos', 'mihdan-yandex-turbo-feed' ),
					'required'      => true,
				)
			)
			/**
			 * Настройки для таблиц.
			 */
			->addTab(
				'comments',
				array(
					'placement' => 'left',
					'label'     => __( 'Comments', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addTrueFalse(
				$this->prefix . '_comments_enable',
				array(
					'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
					'label'   => __( 'Comments', 'mihdan-yandex-turbo-feed' ),
				)
			)
			/**
			 * Форма обратной связи.
			 *
			 * @link https://yandex.ru/dev/turbo/doc/rss/elements/fos-docpage/
			 */
			->addTab(
				'callback',
				array(
					'placement' => 'left',
					'label'     => __( 'Callback', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addTrueFalse(
				$this->prefix . '_callback_enable',
				array(
					'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
					'label'   => __( 'Callback', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addText( $this->prefix . '_callback_send_to' )
			->setRequired()
			->setDefaultValue( get_bloginfo_rss( 'admin_email' ) )
			->setConfig( 'label', __( 'Callback Send To', 'mihdan-yandex-turbo-feed' ) )
			->conditional( $this->prefix . '_callback_enable', '==', '1' )
			->addText(
				$this->prefix . '_callback_agreement_company',
				array(
					'label'         => __( 'Callback Agreement Company', 'mihdan-yandex-turbo-feed' ),
					'default_value' => get_bloginfo_rss( 'name' ),
					'required'      => true,
				)
			)
			->conditional( $this->prefix . '_callback_enable', '==', '1' )
			->addLink(
				$this->prefix . '_callback_agreement_link',
				array(
					'label'         => __( 'Callback Agreement Link', 'mihdan-yandex-turbo-feed' ),
					'default_value' => get_privacy_policy_url(),
					'required'      => true,
				)
			)
			->conditional( $this->prefix . '_callback_enable', '==', '1' )
			/**
			 * Меню.
			 */
			->addTab(
				'menu',
				array(
					'placement' => 'left',
					'label'     => __( 'Menu', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addMessage(
				__( 'Attention Menu', 'mihdan-yandex-turbo-feed' ),
				/* translators: link to menu */
				sprintf( __( 'For adding menu to your feed, first <a href="%s">created it</a> and attach to "Yandex.Turbo" location', 'mihdan-yandex-turbo-feed' ), admin_url( 'nav-menus.php' ) )
			)
			->addTrueFalse(
				$this->prefix . '_menu_enable',
				array(
					'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
					'label'   => __( 'Menu', 'mihdan-yandex-turbo-feed' ),
				)
			)
			/**
			 * Аналитика.
			 *
			 * @link https://yandex.ru/dev/turbo/doc/settings/analytics-docpage
			 * @link https://yandex.ru/dev/turbo/doc/settings/find-counter-id-docpage/
			 */
			->addTab(
				'analytics',
				array(
					'placement' => 'left',
					'label'     => __( 'Analytics', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addMessage(
				__( 'Attention Analytics', 'mihdan-yandex-turbo-feed' ),
				__( 'Если информация о счетчиках передается в RSS-канале (в элементе <code>turbo:analytics</code>), то настройки счетчиков в Яндекс.Вебмастере не учитываются. Чтобы подключить счетчики в Яндекс.Вебмастере, отключите полность модуль аналитики ниже.', 'mihdan-yandex-turbo-feed' )
			)
			->addTrueFalse(
				$this->prefix . '_analytics_enable',
				array(
					'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
					'label'   => __( 'Analytics', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addText(
				$this->prefix . '_analytics_yandex_metrika',
				array(
					'label'        => __( 'Yandex.Metrika', 'mihdan-yandex-turbo-feed' ),
					'instructions' => __( 'Укажите числовой идентификатор счётчика. Например, <code>12345678</code>.', 'mihdan-yandex-turbo-feed' ),
					'placeholder'  => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->conditional( $this->prefix . '_analytics_enable', '==', '1' )
			->addText(
				$this->prefix . '_analytics_live_internet',
				array(
					'label'        => __( 'LiveInternet', 'mihdan-yandex-turbo-feed' ),
					'instructions' => __( 'Укажите имя именованного счётчика. Например, <code>example.com</code>.', 'mihdan-yandex-turbo-feed' ),
					'placeholder'  => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->conditional( $this->prefix . '_analytics_enable', '==', '1' )
			->addText(
				$this->prefix . '_analytics_google',
				array(
					'label'        => __( 'Google Analytics', 'mihdan-yandex-turbo-feed' ),
					'instructions' => __( 'Укажите идентификатор отслеживания. Например, <code>UA-12345678-9</code>.', 'mihdan-yandex-turbo-feed' ),
					'placeholder'  => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->conditional( $this->prefix . '_analytics_enable', '==', '1' )
			->addText(
				$this->prefix . '_analytics_mail_ru',
				array(
					'label'        => __( 'Rating Mail.RU', 'mihdan-yandex-turbo-feed' ),
					'instructions' => __( 'Укажите числовой идентификатор счётчика. Например, <code>12345678</code>.', 'mihdan-yandex-turbo-feed' ),
					'placeholder'  => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->conditional( $this->prefix . '_analytics_enable', '==', '1' )
			->addText(
				$this->prefix . '_analytics_rambler',
				array(
					'label'        => __( 'Rambler Top-100', 'mihdan-yandex-turbo-feed' ),
					'instructions' => __( 'Укажите числовой идентификатор счётчика. Например, <code>12345678</code>.', 'mihdan-yandex-turbo-feed' ),
					'placeholder'  => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->conditional( $this->prefix . '_analytics_enable', '==', '1' )
			->addText(
				$this->prefix . '_analytics_mediascope',
				array(
					'label'        => __( 'Mediascope (TNS)', 'mihdan-yandex-turbo-feed' ),
					'instructions' => __( 'Идентификатор проекта <code>tmsec</code> с окончанием «-<code>turbo</code>». Например, если для обычных страниц сайта установлен счетчик <code>example_total</code>, то для Турбо-страниц указывается <code>example_total-turbo</code>.', 'mihdan-yandex-turbo-feed' ),
					'placeholder'  => __( 'Введите ID счётчика', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->conditional( $this->prefix . '_analytics_enable', '==', '1' )
			/**
			 * Похожие записи.
			 */
			->addTab(
				'related_posts',
				array(
					'placement' => 'left',
					'label'     => __( 'Related Posts', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addMessage(
				__( 'Attention Related Posts', 'mihdan-yandex-turbo-feed' ),
				__( 'Если лента формируется в RSS-канале, то настройки ленты в Яндекс.Вебмастере не учитываются. Чтобы включить автоматическую ленту в Яндекс.Вебмастере, отключите данную возможность ниже.', 'mihdan-yandex-turbo-feed' )
			)
			->addTrueFalse(
				$this->prefix . '_related_posts_enable',
				array(
					'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
					'label'   => __( 'Related Posts', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addTrueFalse(
				$this->prefix . '_related_posts_infinity',
				array(
					'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
					'label'   => __( 'Infinity Feed', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->conditional( $this->prefix . '_related_posts_enable', '==', '1' )
			->addNumber(
				$this->prefix . '_related_posts_total',
				array(
					'label'         => __( 'Total Posts', 'mihdan-yandex-turbo-feed' ),
					'default_value' => 10,
					'min'           => 1,
					'max'           => 30,
					'step'          => 1,
					'required'      => true,
				)
			)
			->conditional( $this->prefix . '_related_posts_enable', '==', '1' )
			/**
			 * Рейтинг записи.
			 *
			 * @link https://yandex.ru/dev/turbo/doc/rss/elements/rating-docpage/
			 */
			->addTab(
				'rating',
				array(
					'placement' => 'left',
					'label'     => __( 'Rating', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addTrueFalse(
				$this->prefix . '_rating_enable',
				array(
					'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
					'label'   => __( 'Rating', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addNumber(
				$this->prefix . '_rating_min',
				array(
					'label'         => __( 'Minimal', 'mihdan-yandex-turbo-feed' ),
					'default_value' => 4,
					'min'           => 1,
					'max'           => 100,
					'step'          => 1,
					'required'      => true,
				)
			)
			->conditional( $this->prefix . '_rating_enable', '==', '1' )
			->addNumber(
				$this->prefix . '_rating_max',
				array(
					'label'         => __( 'Maximum', 'mihdan-yandex-turbo-feed' ),
					'default_value' => 5,
					'min'           => 2,
					'max'           => 100,
					'step'          => 1,
					'required'      => true,
				)
			)
			->conditional( $this->prefix . '_rating_enable', '==', '1' )
			/**
			 * Шеры.
			 */
			->addTab(
				'share',
				array(
					'placement' => 'left',
					'label'     => __( 'Share', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addTrueFalse(
				$this->prefix . '_share_enable',
				array(
					'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
					'label'   => __( 'Share', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addSelect(
				$this->prefix . '_share_networks',
				array(
					'label'         => __( 'Share Networks', 'mihdan-yandex-turbo-feed' ),
					'default_value' => array_keys( $this->share_networks ),
					'multiple'      => true,
					'ui'            => true,
					'choices'       => $this->share_networks,
					'required'      => true,
				)
			)
			->conditional( $this->prefix . '_share_enable', '==', '1' )
			/**
			 * Форма поиска
			 *
			 * @link https://yandex.ru/dev/turbo/doc/rss/elements/search-block-docpage/
			 */
			->addTab(
				'search',
				array(
					'placement' => 'left',
					'label'     => __( 'Search', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addTrueFalse(
				$this->prefix . '_search_enable',
				array(
					'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
					'label'   => '',
				)
			)
			->addText(
				$this->prefix . '_search_placeholder',
				array(
					'label'         => __( 'Placeholder', 'mihdan-yandex-turbo-feed' ),
					'default_value' => __( 'Search', 'mihdan-yandex-turbo-feed' ),
					'required'      => true,
				)
			)
			->addSelect(
				$this->prefix . '_search_provider',
				array(
					'label'         => __( 'Provider', 'mihdan-yandex-turbo-feed' ),
					'default_value' => 'site',
					'choices'       => wp_list_pluck( $this->providers, 'name', 'id' ),
				)
			)
			/**
			 * Настройки для таблиц.
			 */
			->addTab(
				'tables',
				array(
					'placement' => 'left',
					'label'     => __( 'Tables', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addTrueFalse(
				$this->prefix . '_invisible_border_enable',
				array(
					'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
					'label'   => __( 'Invisible Border', 'mihdan-yandex-turbo-feed' ),
				)
			)
			/**
			 * Настройки для таблиц.
			 */
			->addTab(
				'access',
				array(
					'placement' => 'left',
					'label'     => __( 'Access', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->AddMessage(
				__( 'Attention Access', 'mihdan-yandex-turbo-feed' ),
				__( 'Использовать авторизацию для доступа к файлу с данными для формирования Турбо-страниц.', 'mihdan-yandex-turbo-feed' )
			)
			->addTrueFalse(
				$this->prefix . '_access_enable',
				array(
					'message' => __( 'On', 'mihdan-yandex-turbo-feed' ),
					'label'   => __( 'Access', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->addText(
				$this->prefix . '_access_login',
				array(
					'label'    => __( 'Login', 'mihdan-yandex-turbo-feed' ),
					'required' => true,
				)
			)
			->conditional( $this->prefix . '_access_enable', '==', '1' )
			->addText(
				$this->prefix . '_access_password',
				array(
					'label'    => __( 'Password', 'mihdan-yandex-turbo-feed' ),
					'required' => true,
				)
			)
			->conditional( $this->prefix . '_access_enable', '==', '1' )
			/**
			 * Форма запроса помощи проекту.
			 */
			->addTab(
				'donate',
				array(
					'placement' => 'left',
					'label'     => __( 'Donate', 'mihdan-yandex-turbo-feed' ),
				)
			)
			->AddMessage(
				__( 'Attention Donate', 'mihdan-yandex-turbo-feed' ),
				/* translators: donate link */
				sprintf( __( 'Проект отнимает огромное количество сил, времени и энергии. Чтобы у разработчика была мотивация продолжать разрабатывать плагин и дальше, вы всегда можете <a target="_blank" href="%s">помочь символической суммой</a>.', 'mihdan-yandex-turbo-feed' ), 'https://www.kobzarev.com/donate/' )
			)
			->setLocation( 'post_type', '==', $this->cpt_key );

		acf_add_local_field_group( $feed_settings->build() );
	}

	/**
	 * @param         $key
	 * @param integer $post_id
	 *
	 * @return mixed
	 */
	public function get_option( $key, $post_id = null ) {

		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		return get_field( $this->prefix . '_' . $key, $post_id );
	}

	/**
	 * Получить название такосномии для соотношений.
	 * По-умолчанию, это category.
	 *
	 * @return array
	 */
	public function get_taxonomy() {
		return array_keys( $this->taxonomies );
	}
}
