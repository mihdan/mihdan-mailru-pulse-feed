<?php
/**
 * @package mihdan-mailru-pulse-feed
 *
 * @link https://help.mail.ru/feed/fulltext
 */

namespace Mihdan\MailRuPulseFeed;

/**
 * Gutenberg class.
 */
class Gutenberg {
	/**
	 * Hooks init.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'render_block_core/gallery', array( $this, 'set_template_for_gallery_block' ), 10, 2 );
	}

	/**
	 * Set template for gallery block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $parsed_block  Parsed block.
	 *
	 * @return mixed|string
	 */
	public function set_template_for_gallery_block( $block_content, $parsed_block ) {
		// Парсим только при формировании ленты.
		if ( ! is_feed( Main::get_feed_name() ) ) {
			return $block_content;
		}

		if ( ! isset( $parsed_block['attrs']['ids'] ) ) {
			return $block_content;
		}

		$template = sprintf( '<gallery data-pulse-component="gallery" data-pulse-component-name="pulse_gallery_%s">', Main::get_unique_string() );

		foreach ( $parsed_block['attrs']['ids'] as $image ) {
			$src = wp_get_attachment_image_url( $image, 'large' );

			if ( ! $src ) {
				continue;
			}

			$template .= sprintf( '<figure><img src="%s"></figure>', $src );
		}

		$template .= '</gallery>';

		return $template;
	}
}
