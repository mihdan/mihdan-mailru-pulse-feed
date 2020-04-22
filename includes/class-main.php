<?php
/**
 * @package mihdan-mailru-pulse-feed
 *
 * @link https://help.mail.ru/feed/fulltext
 */

namespace Mihdan\MailRuPulseFeed;

use WPTRT\AdminNotices\Notices;

class Main {

	private $feedname;
	private $allowable_tags = array(
		'p'          => array(),
		'em'         => array(),
		'i'          => array(),
		'b'          => array(),
		'strong'     => array(),
		'img'        => array(),
		'video'      => array(),
		'figure'     => array(),
		'figcaption' => array(),
		'iframe'     => array(),
	);

	/**
	 * @var int
	 */
	private $total_posts;

	/**
	 * @var array список постов для вывода
	 */
	private $post_type;

	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * @var Notifications
	 */
	private $notifications;

	/**
	 * @var WP_OSA
	 */
	private $wposa_obj;

	/**
	 * @var Widget
	 */
	private $widget;

	/**
	 * @var array $defaults Default settings.
	 */
	private $defaults = [
		'feed'   => [
			'charset'     => 'UTF-8',
			'orderby'     => 'date',
			'order'       => 'DESC',
			'post_types'  => [
				'post' => 'post',
			],
			'taxonomies'  => [
				'category' => 'category',
			],
			'total_posts' => 1000,
			'fulltext'    => 'off',
		],
		'widget' => [
			'auto_append' => 'off',
		],
	];

	/**
	 * @var array $amp_data array of AMP plugins.
	 */
	private $amp_data = [
		'amp_init'      => 'amp_get_permalink',
		'ampforwp_init' => '\AMPforWP\AMPVendor\amp_get_permalink',
	];

	private $amp_provider = '';

	/**
	 * @var string $version Plugin version.
	 */
	private $version;

	/**
	 * @var string $slug Plugin slug.
	 */
	private $slug;

	public function __construct() {
		$this->setup();
		$this->hooks();
	}

	private function setup() {
		$this->version       = MIHDAN_MAILRU_PULSE_FEED_VERSION;
		$this->slug          = str_replace( '-', '_', MIHDAN_MAILRU_PULSE_FEED_SLUG );
		$this->wposa_obj     = new WP_OSA();
		$this->settings      = new Settings( $this->wposa_obj );
		$this->notifications = new Notifications( $this->slug );
		$this->widget        = new Widget( $this->wposa_obj );

		$this->post_type   = $this->wposa_obj->get_option( 'post_types', 'feed' );
		$this->total_posts = $this->wposa_obj->get_option( 'total_posts', 'feed', 10 );
	}

	private function hooks() {
		add_action( 'init', array( $this, 'add_feed' ) );
		add_action( 'init', array( $this, 'flush_rewrite_rules' ), 99 );
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
		add_filter( 'wpseo_include_rss_footer', array( $this, 'hide_wpseo_rss_footer' ) );
		add_filter( 'the_excerpt_rss', array( $this, 'the_excerpt_rss' ), 99 );
		add_action( 'template_redirect', array( $this, 'send_headers_for_aio_seo_pack' ), 20 );
		add_action( 'pre_get_posts', array( $this, 'alter_query' ) );
		add_filter( 'plugin_action_links', [ $this, 'add_settings_link' ], 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'add_post_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post_meta_box' ) );
		add_action( 'category_edit_form', array( $this, 'add_category_meta_box' ) );
		add_action( 'edited_category', array( $this, 'save_category_meta_box' ) );
		add_action( 'upgrader_process_complete', array( $this, 'upgrade' ), 10, 2 );
		add_filter( 'image_send_to_editor', array( $this, 'wrap_image' ), 10, 8 );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			add_filter( 'posts_request', [ $this, 'dump_request' ] );
		}

		register_activation_hook( MIHDAN_MAILRU_PULSE_FEED_FILE, array( $this, 'on_activate' ) );
		register_deactivation_hook( MIHDAN_MAILRU_PULSE_FEED_FILE, array( $this, 'on_deactivate' ) );
	}

	/**
	 * Retrieves the image HTML to send to the editor.
	 *
	 * @since 2.5.0
	 *
	 * @param string       $html    HTML for image.
	 * @param int          $id      Image attachment id.
	 * @param string       $caption Image caption.
	 * @param string       $title   Image title attribute.
	 * @param string       $align   Image CSS alignment property.
	 * @param string       $url     Optional. Image src URL. Default empty.
	 * @param bool|string  $rel     Optional. Value for rel attribute or whether to add a default value. Default false.
	 * @param string|array $size    Optional. Image size. Accepts any valid image size, or an array of width
	 *                              and height values in pixels (in that order). Default 'medium'.
	 * @param string       $alt     Optional. Image alt attribute. Default empty.
	 * @return string The HTML output to insert into the editor.
	 */
	public function wrap_image( $html, $id, $caption, $title, $align, $url, $size, $alt ) {

		// If theme not support html5.
		if ( current_theme_supports( 'html5' ) ) {
			$src = wp_get_attachment_image_src( $id, $size );

			$html = '';

			$html .= "<figure id='post-{$id} media-{$id}' class='align-{$align}'>";

			if ( $url ) {
				$html .= '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $title ) . '">';
			}

			$html .= "<img src='{$src[0]}' alt='{$alt}' />";

			if ( $url ) {
				$html .= "</a>";
			}

			if ( $caption ) {
				$html .= "<figcaption>{$caption}</figcaption>";
			}

			$html .= "</figure>";
		}

		return $html;
	}

	/**
	 * Set plugin version.
	 *
	 * @param \WP_Upgrader $upgrader WP_Upgrader instance.
	 * @param array        $options  Array of bulk item update data.
	 */
	public function upgrade( \WP_Upgrader $upgrader, $options ) {
		$our_plugin = plugin_basename( MIHDAN_MAILRU_PULSE_FEED_FILE );

		if ( 'update' === $options['action'] && 'plugin' === $options['type'] && isset( $options['plugins'] ) ) {
			foreach ( $options['plugins'] as $plugin ) {
				if ( $plugin === $our_plugin ) {
					update_option( $this->slug . '_version', $this->version, false );
				}
			}
		}
	}

	/**
	 * Check if AMP is supported.
	 *
	 * @return bool
	 */
	public function is_amp_support() {
		foreach ( $this->amp_data as $function => $data ) {
			if ( function_exists( $function ) ) {
				$this->amp_provider = $function;
				return true;
			}
		}

		return false;
	}

	/**
	 * Get AMP permalink for post.
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function get_amp_permalink( $post_id ) {
		return $this->amp_data[ $this->amp_provider ]( $post_id );
	}

	/**
	 * Display AMP permalink for post.
	 *
	 * @param int $post_id
	 */
	public function the_amp_permalink( $post_id ) {
		echo $this->get_amp_permalink( $post_id );
	}

	/**
	 * Dump main sql query.
	 *
	 * @param $request
	 *
	 * @return mixed
	 */
	public function dump_request( $request ) {
		//print_r( $request );

		return $request;
	}

	/**
	 * Add settings Meta Box for categories.
	 *
	 * @param \WP_Term $term Current taxonomy term object
	 */
	public function add_category_meta_box( \WP_Term $term ) {
		$exclude = (bool) get_term_meta( $term->term_id, $this->slug . '_exclude', true );
		?>
		<h2><?php _e( 'Pulse Mail.ru', 'mihdan-mailru-pulse-feed' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr class="form-field">
				<th scope="row">
					<label for="<?php echo esc_attr( $this->slug ); ?>_exclude"><?php _e( 'Exclude From Feed', 'mihdan-mailru-pulse-feed' ); ?></label>
				</th>
				<td>
					<input type="checkbox" value="1" name="<?php echo esc_attr( $this->slug ); ?>_exclude" id="<?php echo esc_attr( $this->slug ); ?>_exclude" <?php checked( $exclude, true ); ?> />
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save settings Meta Box for categories.
	 *
	 * @param int $term_id Term ID .
	 */
	public function save_category_meta_box( $term_id ) {
		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}

		if ( isset( $_POST[ $this->slug . '_exclude' ] ) ) {
			update_term_meta( $term_id, $this->slug . '_exclude', 1 );
		} else {
			delete_term_meta( $term_id, $this->slug . '_exclude' );
		}
	}

	/**
	 * Add settings Meta Box for posts.
	 */
	public function add_post_meta_box() {
		add_meta_box(
			$this->slug,
			__( 'Pulse Mail.ru', 'mihdan-mailru-pulse-feed' ),
			[ $this, 'render_meta_box' ],
			$this->post_type,
			'side',
			'high'
		);
	}

	/**
	 * Render settings Meta Box for posts.
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function render_meta_box( $post ) {
		$exclude = (bool) get_post_meta( $post->ID, $this->slug . '_exclude', true );
		?>
		<label for="<?php echo esc_attr( $this->slug ); ?>_exclude" title="Включить/Исключить запись из ленты">
			<input type="checkbox" value="1" name="<?php echo esc_attr( $this->slug ); ?>_exclude" id="<?php echo esc_attr( $this->slug ); ?>_exclude" <?php checked( $exclude, true ); ?>> <?php _e( 'Exclude From Feed', 'mihdan-mailru-pulse-feed' ); ?>
		</label>
		<?php
	}
	/**
	 * Save  settings Meta Box for posts.
	 *
	 * @param int $post_id Post ID .
	 */
	public function save_post_meta_box( $post_id ) {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST[ $this->slug . '_exclude' ] ) ) {
			update_post_meta( $post_id, $this->slug . '_exclude', 1 );
		} else {
			delete_post_meta( $post_id, $this->slug . '_exclude' );
		}
	}

	/**
	 * Add plugin action links
	 *
	 * @param array $actions
	 * @param string $plugin_file
	 *
	 * @return array
	 */
	public function add_settings_link( $actions, $plugin_file ) {
		if ( 'mihdan-mailru-pulse-feed/mihdan-mailru-pulse-feed.php' === $plugin_file ) {
			$actions[] = sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'options-general.php?page=mihdan_mailru_pulse_feed' ),
				esc_html__( 'Settings', 'mihdan-mailru-pulse-feed' )
			);
			$actions[] = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( 'https://www.kobzarev.com/donate/' ),
				esc_html__( 'Donate', 'mihdan-mailru-pulse-feed' )
			);
		}

		return $actions;
	}

	/**
	 * Подправляем основной луп фида
	 *
	 * @param \WP_Query $wp_query объект запроса
	 */
	public function alter_query( \WP_Query $wp_query ) {
		if ( $wp_query->is_main_query() && $wp_query->is_feed( $this->feedname ) ) {
			// Ограничить посты 50-ю
			$wp_query->set( 'posts_per_rss', $this->total_posts );

			// Впариваем нужные нам типы постов
			$wp_query->set( 'post_type', $this->post_type );

			// Указываем поле для сортировки.
			$wp_query->set( 'orderby', $this->wposa_obj->get_option( 'orderby', 'feed', 'date' ) );

			// Указываем направление сортировки.
			$wp_query->set( 'order', $this->wposa_obj->get_option( 'order', 'feed', 'DESC' ) );

			// Получаем текущие мета запросы.
			$meta_query = $wp_query->get( 'meta_query', array() );

			// Добавляем исключения.
			$meta_query[] = array(
				'key'     => $this->slug . '_exclude',
				'compare' => 'NOT EXISTS',
			);

			// Исключаем записи с галочкой в админке
			$wp_query->set( 'meta_query', $meta_query );

			// Ищем категории, которые исключены из ленты.
			$args = [
				'taxonomy'   => 'category',
				'fields'     => 'ids',
				'meta_query' => [
					[
						'key'     => $this->slug . '_exclude',
						'compare' => 'EXISTS',
					],
				],
			];

			$excluded_categories = get_terms( $args );

			if ( $excluded_categories ) {

				$tax_query = $wp_query->get( 'tax_query', array() );

				$tax_query[] = array(
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => $excluded_categories,
					'operator' => 'NOT IN',
				);

				$wp_query->set( 'tax_query', $tax_query );
			}
		}
	}

	function the_excerpt_rss( $excerpt ) {
		if ( is_feed( $this->feedname ) ) {
			$excerpt = wp_kses( $excerpt, $this->allowable_tags );
		}

		return $excerpt;
	}

	public function after_setup_theme() {
		$this->feedname = apply_filters( 'mihdan_mailru_pulse_feed_feedname', MIHDAN_MAILRU_PULSE_FEED_SLUG );

		if ( 'on' === $this->wposa_obj->get_option( 'html5', 'feed' ) ) {
			add_theme_support( 'html5', array( 'gallery', 'caption'  ) );
		}
	}

	public function add_feed() {
		add_feed( $this->feedname, array( $this, 'require_feed_template' ) );
	}

	public function require_feed_template() {
		require MIHDAN_MAILRU_PULSE_FEED_PATH . '/templates/feed.php';
	}

	public function flush_rewrite_rules() {

		// Ищем опцию.
		if ( get_option( $this->slug . '_flush_rewrite_rules' ) ) {

			// Скидываем реврайты.
			flush_rewrite_rules();

			// Удаляем опцию.
			delete_option( $this->slug . '_flush_rewrite_rules' );
		}
	}

	public function hide_wpseo_rss_footer( $include_footer = true ) {

		if ( is_feed( $this->feedname ) ) {
			$include_footer = false;
		}

		return $include_footer;
	}

	public function send_headers_for_aio_seo_pack() {
		// Добавим заголовок `X-Robots-Tag`
		// для решения проблемы с сеошными плагинами.
		if ( is_feed( $this->feedname ) ) {
			header( 'X-Robots-Tag: index, follow', true );
		}
	}

	public function on_activate() {

		// Смотрим, есть ли настройки в базе данных,
		// если нет - создадим дефолтные.
		$settings = $this->wposa_obj->get_option( 'charset', 'feed' );

		if ( ! $settings ) {
			foreach ( $this->defaults as $section => $defaults ) {
				update_option( $section, $defaults, false );
			}
		}

		// Добавим флаг, свидетельствующий о том,
		// что нужно сбросить реврайты.
		update_option( $this->slug . '_flush_rewrite_rules', 1, true );

		// Set plugin version.
		update_option( $this->slug . '_version', $this->version, false );
	}

	public function on_deactivate() {

		// Сбросить правила реврайтов
		flush_rewrite_rules();
	}
}

// eof;
