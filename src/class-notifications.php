<?php
namespace Mihdan\MailRuPulseFeed;
use WPTRT\AdminNotices\Notices;

class Notifications {
	const URL = 'https://wordpress.org/support/plugin/mihdan-mailru-pulse-feed/reviews/?rate=5#new-post';

	/**
	 * @var Notices
	 */
	private $notices;

	/**
	 * @var string $slug Plugni slug.
	 */
	private $slug;

	/**
	 * Notifications constructor.
	 *
	 * @param string $slug
	 */
	public function __construct( $slug = '' ) {
		$this->slug    = $slug;
		$this->notices = new Notices();

		$template  = '<p>';
		$template .= __( 'Hello!', 'mihdan-mailru-pulse-feed' );
		$template .= '<br />';
		/* translators: ссылка на голосование */
		$template .= sprintf( __( 'We are very pleased that you by now have been using the <strong>Mihdan: Mail.ru Pulse Feed</strong> plugin a few days. Please <a href="%s" target="_blank">rate ★★★★★ plugin</a>. It will help us a lot.', 'mihdan-mailru-pulse-feed' ), self::URL );
		$template .= '</p>';

		$this->notices->add(
			'review_dismissed',
			false,
			$template,
			[
				'scope'         => 'user',
				'option_prefix' => $this->slug,
			]
		);

		$this->notices->boot();
	}
}
