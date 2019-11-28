<?php
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

	public function hooks() {}

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
}

// eol.
