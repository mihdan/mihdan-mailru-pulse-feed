<?php
/**
 * @package mihdan-mailru-pulse-feed
 *
 * @link https://help.mail.ru/feed/fulltext
 */

namespace Mihdan\MailRuPulseFeed;

use DiDom\Document;
use DiDom\Element;
use WPTRT\AdminNotices\Notices;

class Main {

	private static $feedname;
	private $allowable_tags = array(
		'a'          => array(
			'href'   => true,
			'target' => true,
			'title'  => true,
		),
		'abbr'       => array(
			'title' => true,
		),
		'acronym'    => array(
			'title' => true,
		),
		'q'          => array(
			'cite' => true,
		),
		'blockquote' => array(
			'cite' => true,
		),
		'p'          => array(),
		'em'         => array(),
		'i'          => array(),
		'b'          => array(),
		'strong'     => array(),
		's'          => array(),
		'strike'     => array(),
		'img'        => array(
			'src'    => true,
			'width'  => true,
			'height' => true,
			'alt'    => true,
			'title'  => true,
		),
		'video'      => array(
			'src'      => true,
			'autoplay' => true,
			'controls' => true,
			'height'   => true,
			'width'    => true,
			'loop'     => true,
			'poster'   => true,
			'preload'  => true,
		),
		'source'     => array(
			'src'   => true,
			'type'  => true,
			'media' => true,
		),
		'figure'     => array(),
		'figcaption' => array(),
		'iframe'     => array(
			'src'    => true,
			'width'  => true,
			'height' => true,
		),
		'cite'       => array(),
		'code'       => array(),
		'pre'        => array(),
		'del'        => array(),
		'h1'         => array(),
		'h2'         => array(),
		'h3'         => array(),
		'h4'         => array(),
		'h5'         => array(),
		'h6'         => array(),
		'table'      => array(),
		'tbody'      => array(),
		'tr'         => array(),
		'th'         => array(),
		'td'         => array(),
		'ul'         => array(),
		'ol'         => array(),
		'li'         => array(),
	);

	/**
	 * @var array $enclosures Enclosures list.
	 */
	private $enclosures = array();

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
			'fulltext'    => 'on',
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

	/**
	 * Main constructor.
	 */
	public function __construct() {
		if ( ! $this->requirements() ) {
			return;
		}

		$this->setup();
		$this->hooks();
	}

	/**
	 * Check plugin requirements.
	 *
	 * @return bool
	 */
	public function requirements() {
		/**
		 * TODO: Перенести в SiteHealth.
		 */
		$this->version       = MIHDAN_MAILRU_PULSE_FEED_VERSION;
		$this->slug          = str_replace( '-', '_', MIHDAN_MAILRU_PULSE_FEED_SLUG );
		$this->notifications = new Notices();

		if ( ! class_exists( 'DOMDocument' ) ) {

			$this->notifications->add(
				'dom_document_error',
				false,
				__( 'Для правильной работы плагина <strong>Mail.ru Pulse Feed</strong> необходимо расширение <strong>DOMDocument</strong>. Обратитесь в техподдержку вашего хостинга или к вашему системному администратору.', 'mihdan-mailru-pulse-feed' ),
				[
					'scope'         => 'user',
					'option_prefix' => $this->slug,
					'type'          => 'error',
				]
			);

			$this->notifications->boot();

			return false;
		}

		return true;
	}

	/**
	 * Setup requirements.
	 */
	private function setup() {
		$this->wposa_obj     = new WP_OSA();
		$this->settings      = new Settings( $this->wposa_obj );
		$this->widget        = new Widget( $this->wposa_obj );

		$this->post_type   = $this->wposa_obj->get_option( 'post_types', 'feed' );
		$this->total_posts = $this->wposa_obj->get_option( 'total_posts', 'feed', 10 );

		$this->allowable_tags = apply_filters( 'mihdan_mailru_pulse_feed_allowable_tags', $this->allowable_tags );
	}

	/**
	 * Setup hooks.
	 */
	private function hooks() {
		add_action( 'init', array( $this, 'add_feed' ) );
		add_action( 'init', array( $this, 'flush_rewrite_rules' ), 99 );

		// The SEO Framework.
		add_action( 'the_seo_framework_after_front_init', [ $this, 'disable_seo_framework_for_feed'] );

		// SEO by Yoast.
		add_filter( 'wpseo_include_rss_footer', array( $this, 'hide_wpseo_rss_footer' ) );

		// All In One SEO Pack.
		add_action( 'template_redirect', array( $this, 'send_headers_for_aio_seo_pack' ), 20 );

		// "The Voux" theme. Disable lazy load for feed.
		add_action(
			'after_setup_theme',
			function() {
				remove_filter( 'the_content', 'thb_lazy_images_filter', 200 );
				remove_filter( 'wp_get_attachment_image_attributes', 'thb_lazy_low_quality', 10 );
			}
		);

		add_filter( 'default_post_metadata', array( $this, 'exclude_post_by_default' ), 10, 3 );
		add_filter( 'default_post_metadata', array( $this, 'exclude_term_by_default' ), 10, 3 );

		add_action( 'pre_get_posts', array( $this, 'alter_query' ) );
		add_filter( 'plugin_action_links', [ $this, 'add_settings_link' ], 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'add_post_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post_meta_box' ) );
		add_action( 'category_edit_form', array( $this, 'add_category_meta_box' ) );
		add_action( 'edited_category', array( $this, 'save_category_meta_box' ) );
		add_action( 'upgrader_process_complete', array( $this, 'upgrade' ), 10, 2 );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ) );
		add_filter( 'mihdan_mailru_pulse_feed_item_excerpt', array( $this, 'the_excerpt_rss' ), 99 );
		add_filter( 'mihdan_mailru_pulse_feed_item_content', array( $this, 'kses_content' ), 99 );
		add_filter( 'mihdan_mailru_pulse_feed_item_content', array( $this, 'wrap_image_with_figure' ), 100, 2 );
		add_filter( 'mihdan_mailru_pulse_feed_item_content', array( $this, 'add_thumbnail_to_item_content' ), 200, 2 );
		add_action( 'mihdan_mailru_pulse_feed_item', array( $this, 'add_thumbnail_to_enclosure' ) );
		add_action( 'mihdan_mailru_pulse_feed_item', array( $this, 'add_enclosures_to_item' ), 99 );

		register_activation_hook( MIHDAN_MAILRU_PULSE_FEED_FILE, array( $this, 'on_activate' ) );
		register_deactivation_hook( MIHDAN_MAILRU_PULSE_FEED_FILE, array( $this, 'on_deactivate' ) );
	}

	/**
	 * @return mixed|string|string[]|void
	 */
	public function get_the_content_feed( $post_id = null ) {
		$content = apply_filters( 'the_content', get_the_content( null, false, $post_id ) );
		$content = str_replace( ']]>', ']]&gt;', $content );

		return $content;
	}

	/**
	 * Exclude post from feed by default.
	 *
	 * @param mixed  $value The value to return, either a single metadata value or an array
	 *                      of values depending on the value of `$single`.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key Metadata key.
	 *
	 * @return mixed
	 */
	public function exclude_post_by_default( $value, $object_id, $meta_key ) {

		if ( $this->slug . '_exclude' !== $meta_key ) {
			return $value;
		}

		return apply_filters( 'mihdan_mailru_pulse_feed_exclude_post_by_default', false );
	}

	/**
	 * Exclude term from feed by default.
	 *
	 * @param mixed  $value The value to return, either a single metadata value or an array
	 *                      of values depending on the value of `$single`.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key Metadata key.
	 *
	 * @return mixed
	 */
	public function exclude_term_by_default( $value, $object_id, $meta_key ) {

		if ( $this->slug . '_exclude' !== $meta_key ) {
			return $value;
		}

		return apply_filters( 'mihdan_mailru_pulse_feed_exclude_term_by_default', false );
	}

	/**
	 * Add a post thumbnail to beginning of the feed item.
	 *
	 * @param string $content Item content.
	 * @param int    $post_id Post ID.
	 *
	 * @return string
	 */
	public function add_thumbnail_to_item_content( $content, $post_id ) {
		if (
			'on' === $this->wposa_obj->get_option( 'fulltext', 'feed' ) &&
			'on' === $this->wposa_obj->get_option( 'post_thumbnail', 'feed' ) &&
			has_post_thumbnail( $post_id )
		) {
			$content = '<figure>' . get_the_post_thumbnail( $post_id, 'full' ) . '</figure>' . $content;
		}

		return $content;
	}

	/**
	 * Add admin footer text.
	 *
	 * @param string $text Default text.
	 *
	 * @return string
	 */
	public function admin_footer_text( $text ) {

		$current_screen = get_current_screen();

		$white_list = array(
			'settings_page_mihdan_mailru_pulse_feed',
		);

		if ( isset( $current_screen ) && in_array( $current_screen->id, $white_list ) ) {
			$text = '<span class="mytf-admin-footer-text">';
			$text .= sprintf( __( 'Enjoyed <strong>Mail.ru Pulse Feed</strong>? Please leave us a <a href="%s" target="_blank" title="Rate & review it">★★★★★</a> rating. We really appreciate your support', 'mihdan-yandex-turbo-feed' ), 'https://wordpress.org/support/plugin/mihdan-mailru-pulse-feed/reviews/#new-post' );
			$text .= '</span>';
		}

		return  $text;
	}

	/**
	 * Add enclosures to item.
	 *
	 * @param int $post_id Post ID.
	 */
	public function add_enclosures_to_item( $post_id ) {
		foreach ( $this->get_enclosures( $post_id ) as $enclosure ) {
			echo $this->create_enclosure( $enclosure['url'], $enclosure['type'] );
		}
	}

	/**
	 * Add post thumbnail to enclosures list.
	 *
	 * @param int $post_id Post ID.
	 */
	public function add_thumbnail_to_enclosure( $post_id ) {
		if ( has_post_thumbnail( $post_id ) ) {
			$url  = get_the_post_thumbnail_url( $post_id, 'large' );
			$type = $this->get_mime_type_from_url( $url );

			$this->set_enclosure( $post_id, $url, $type );
		}
	}

	/**
	 * Get basename for enclosure URL.
	 *
	 * @param string $url URL.
	 *
	 * @return string|string[]|null
	 */
	public function get_basename_for_enclosure_url( $url ) {
		return preg_replace( '#\?.*#', '', $url );
	}

	/**
	 * Replace entities.
	 *
	 * @param string $url URL.
	 *
	 * @return string
	 *
	 * @link https://yandex.ru/support/news/feed.html#code
	 */
	public function replace_entities( $url ) {
		$replacements = array(
			'&'  => '&amp;',  // Амперсанд.
			'>'  => '&gt;',   // Правая угловая скобка.
			'<'  => '&lt;',   // Левая угловая скобка.
			'"'  => '&quot;', // Знак кавычек.
			'\'' => '&apos;', // Апостроф.
		);

		$replacements = apply_filters( 'mihdan_mailru_pulse_feed_entities_replacement', $replacements );
		$url          = strtr( $url, $replacements );

		return $url;
	}

	/**
	 * Add enclosure to array.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $url Absolute URL.
	 * @param string $type Mime type.
	 */
	private function set_enclosure( $post_id, $url, $type ) {
		$hash = md5( $url );
		$this->enclosures[ $post_id ][ $hash ] = array(
			'url'  => $this->replace_entities( $url ),
			'type' => $type,
		);
	}

	/**
	 * Get enclosures list for given post id.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array
	 */
	private function get_enclosures( $post_id ) {
		if ( isset( $this->enclosures[ $post_id ] ) && is_array( $this->enclosures[ $post_id ] ) ) {
			return $this->enclosures[ $post_id ];
		}

		return array();
	}

	/**
	 * Create enclosure tag.
	 *
	 * @param string $url URL.
	 * @param string $type Mime type.
	 *
	 * @return string
	 */
	private function create_enclosure( $url, $type ) {
		return sprintf( '<enclosure url="%s" type="%s"/>', $url, $type );
	}

	/**
	 * Get image mime-type from given URL.
	 *
	 * @param string $url Absolute URL.
	 *
	 * @return mixed
	 */
	private function get_mime_type_from_url( $url ) {
		return wp_check_filetype( $this->get_basename_for_enclosure_url( $url ) )['type'];
	}

	/**
	 * Wrap all image in content with <figure> tag.
	 *
	 * @param  string $content
	 * @param  int    $post_id Post ID.
	 *
	 * @return string
	 *
	 * @link   https://wp-punk.com/domdocument/
	 * @link   https://help.mail.ru/feed/fulltext
	 */
	public function wrap_image_with_figure( $content, $post_id ) {

		$document = new Document();
		$document->format();
		$document->loadHtml( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		// TODO: Experiment.
		// $a = $document->xpath( 'video[not(ancestor::figure)]' ); print_r($a);

		/**
		 * Убираем ссылки со всех картинок.
		 * a>img -> img
		 */
		$anchored_images = $document->find( 'a > img' );

		if ( count( $anchored_images ) > 0 ) {
			foreach ( $anchored_images as $anchored_image ) {
				// Get image URL.
				$src = $anchored_image->getAttribute( 'src' );

				if ( ! $src ) {
					continue;
				}

				$this->set_enclosure( $post_id, $src, $this->get_mime_type_from_url( $src ) );

				$anchored_image->parent()->replace( $anchored_image );
			}
		}

		/**
		 * Оборачиваем все картинки в <figure>.
		 * p>img -> figure>img
		 */
		$nonfigured_images = $document->find( 'p > img' );

		if ( count( $nonfigured_images ) > 0 ) {
			foreach ( $nonfigured_images as $nonfigured_image ) {

				if ( ! $nonfigured_image->tag ) {
					continue;
				}

				// Get image URL.
				$src = $nonfigured_image->getAttribute( 'src' );

				if ( ! $src ) {
					continue;
				}

				$this->set_enclosure( $post_id, $src, $this->get_mime_type_from_url( $src ) );

				$figure = new Element( 'figure' );
				$figure->setInnerHtml( $nonfigured_image->html() );
				$nonfigured_image->parent()->replace( $figure );
			}
		}

		/**
		 * Оборачиваем все <iframe> в <figure>.
		 * p>iframe -> figure>iframe
		 */
		$nonfigured_frames = $document->find( 'p > iframe' );

		if ( count( $nonfigured_frames ) > 0 ) {
			foreach ( $nonfigured_frames as $nonfigured_frame ) {
				$figure = new Element( 'figure' );
				$figure->setInnerHtml( $nonfigured_frame->html() );
				$nonfigured_frame->parent()->replace( $figure );
			}
		}

		/**
		 * Оборачиваем все <video> в <figure>,
		 * если они еще не обернуты. Gutenberg сам оборачивает.
		 *
		 * video -> figure>video
		 * figure>video -> figure>video
		 */
		$videos = $document->find( 'video' );

		if ( count( $videos ) > 0 ) {
			foreach ( $videos as $video ) {
				$parent = $video->parentNode;

				// Пропустить видео, если оно уже обернуто в <figure>
				if ( 'figure' === $parent->tagName ) {
					continue;
				}

				$figure = new Element( 'figure' );
				$figure->setInnerHtml( $video->html() );
				$video->replace( $figure );
			}
		}

		$content = $document->toElement()->innerHtml();

		return force_balance_tags( $content );
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
			$actions[] = sprintf(
				'<a href="%s" target="_blank" class="">%s</a>',
				esc_url( site_url( sprintf( '/feed/%s/', $this->get_feed_name() ) ) ),
				esc_html__( 'Your Feed', 'mihdan-mailru-pulse-feed' )
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
		if ( $wp_query->is_main_query() && $wp_query->is_feed( self::get_feed_name() ) ) {
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
		if ( is_feed( self::get_feed_name() ) ) {
			$excerpt = wp_kses( $excerpt, $this->allowable_tags );
		}

		return $excerpt;
	}

	/**
	 * Filters text content and strips out disallowed HTML.
	 *
	 * @param string $content Content with HTML.
	 *
	 * @return string
	 */
	function kses_content( $content ) {
		if ( is_feed( self::get_feed_name() ) ) {
			$content = wp_kses( $content, $this->allowable_tags );
		}

		return $content;
	}

	public static function get_feed_name() {
		return apply_filters( 'mihdan_mailru_pulse_feed_feedname', MIHDAN_MAILRU_PULSE_FEED_SLUG );
	}

	public function add_feed() {
		add_feed( self::get_feed_name(), array( $this, 'require_feed_template' ) );
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

	/**
	 * Show/Hide Yoast SEO Footer.
	 *
	 * @param bool $include_footer
	 *
	 * @return bool
	 */
	public function hide_wpseo_rss_footer( $include_footer ) {

		if ( is_feed( self::get_feed_name() ) ) {
			if ( 'on' === $this->wposa_obj->get_option( 'yoast_seo_footer', 'feed' ) ) {
				$include_footer = true;
			} else {
				$include_footer = false;
			}
		}

		return $include_footer;
	}

	public function send_headers_for_aio_seo_pack() {
		// Добавим заголовок `X-Robots-Tag`
		// для решения проблемы с сеошными плагинами.
		if ( is_feed( self::get_feed_name() ) ) {
			header( 'X-Robots-Tag: index, follow', true );
		}
	}

	/**
	 * Disable inserting source link
	 * by The SEO Framework plugin from excerpt.
	 */
	public function disable_seo_framework_for_feed() {

		if ( is_feed( self::get_feed_name() ) ) {
			return;
		}

		$instance = the_seo_framework();
		remove_filter( 'the_content_feed', [ $instance, 'the_content_feed' ] );
		remove_filter( 'the_excerpt_rss', [ $instance, 'the_content_feed' ] );
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
