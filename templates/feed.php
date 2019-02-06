<?php
/**
 * @link https://yandex.ru/support/webmaster/turbo/feed.html
 * @link https://yandex.ru/support/webmaster/turbo/rss-elements.html
 *
 * @var Mihdan_Yandex_Turbo_Feed $this
 */
header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . $this->get_option( 'feed_charset' ), true );
echo '<?xml version="1.0" encoding="' . esc_html( $this->get_option( 'feed_charset' ) ) . '"?' . '>';
?>
<rss version="2.0" xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" xmlns:turbo="http://turbo.yandex.ru">
	<channel>
		<title><?php echo esc_html( $this->get_option( 'channel_title' ) ); ?></title>
		<link><?php echo esc_html( $this->get_option( 'channel_link' ) ); ?></link>
		<description><?php echo esc_html( $this->get_option( 'channel_description' ) ); ?></description>
		<language><?php echo esc_html( $this->get_option( 'channel_language' ) ); ?></language>
		<turbo:cms_plugin>7391CC2B1408947EFD5084459F5BD0CA</turbo:cms_plugin>
		<?php do_action( 'rss2_head' ); ?>
		<?php do_action( 'mihdan_yandex_turbo_feed_channel' ); ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<item turbo="true">
				<link><?php the_permalink_rss(); ?></link>
				<title><?php the_title_rss(); ?></title>
				<author><?php the_author(); ?></author>
				<pubDate><?php echo esc_html( get_post_time( 'r', true ) ); ?></pubDate>
				<turbo:content>
					<![CDATA[
					<header>
						<?php if ( has_post_thumbnail() ) : ?>
							<figure>
								<?php the_post_thumbnail( 'large' ); ?>
							</figure>
						<?php endif; ?>
						<h1><?php the_title_rss(); ?></h1>
						<?php do_action( 'mihdan_yandex_turbo_feed_item_header', get_the_ID() ); ?>
					</header>
					<?php if ( get_option( 'rss_use_excerpt' ) ) : ?>
						<?php the_content_feed(); ?>
					<?php else : ?>
						<?php the_content_feed(); ?>
					<?php endif; ?>
					<?php do_action( 'mihdan_yandex_turbo_feed_item_content', get_the_ID() ); ?>
					]]>
				</turbo:content>
				<?php do_action( 'mihdan_yandex_turbo_feed_item', get_the_ID() ); ?>
			</item>
		<?php endwhile; ?>
	</channel>
</rss>
