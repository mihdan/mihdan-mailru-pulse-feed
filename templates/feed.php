<?php
/**
 * Шаблон вывода RSS ленты.
 *
 * @var Main $this
 * @package mihdan-mailru-pulse-feed
 */

use Mihdan\MailRuPulseFeed\Main;

header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . $this->wposa_obj->get_option( 'charset', 'feed' ), true );
echo '<?xml version="1.0" encoding="' . esc_attr( $this->wposa_obj->get_option( 'charset', 'feed' ) ) . '"?' . '>';
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
	<channel>
		<title><?php echo esc_html( $this->wposa_obj->get_option( 'title', 'source' ) ); ?></title>
		<link><?php echo esc_url( $this->wposa_obj->get_option( 'link', 'source' ) ); ?></link>
		<description><?php echo esc_html( $this->wposa_obj->get_option( 'description', 'source' ) ); ?></description>
		<language><?php echo esc_html( $this->wposa_obj->get_option( 'language', 'source' ) ); ?></language>
		<generator>Zen Feed by mihdan, v<?php echo esc_html( MIHDAN_MAILRU_PULSE_FEED_VERSION ); ?></generator>
		<webMaster>mikhail@kobzarev.com (Mikhail Kobzarev)</webMaster>
		<docs>https://ru.wordpress.org/plugins/mihdan-mailru-pulse-feed/</docs>
		<image>
			<url><?php echo esc_url( $this->wposa_obj->get_option( 'image', 'source' ) ); ?></url>
			<title><?php echo esc_html( $this->wposa_obj->get_option( 'title', 'source' ) ); ?></title>
			<link><?php echo esc_url( $this->wposa_obj->get_option( 'link', 'source' ) ); ?></link>
		</image>
		<?php do_action( 'rss2_head' ); ?>
		<?php do_action( 'mihdan_mailru_pulse_feed_head' ); ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<item>
				<link><?php the_permalink_rss(); ?></link>
				<guid><?php the_guid(); ?></guid>
				<?php if ( $this->is_amp_support() ) : ?>
					<amplink><?php $this->the_amp_permalink( get_the_ID() ); ?></amplink>
				<?php endif; ?>
				<title><![CDATA[<?php echo esc_html( $this->get_post_title( get_the_ID() ) ); ?>]]></title>
				<author><![CDATA[<?php the_author(); ?>]]></author>
				<pubDate><?php echo esc_html( get_post_time( 'r', true ) ); ?></pubDate>
				<description><![CDATA[<?php echo esc_html( $this->get_post_excerpt( get_the_ID() ) ); ?>]]></description>
				<?php if ( 'on' === $this->wposa_obj->get_option( 'fulltext', 'feed' ) ) : ?>
					<content:encoded><![CDATA[<?php echo apply_filters( 'mihdan_mailru_pulse_feed_item_content', $this->get_the_content_feed( get_the_ID() ), get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>]]></content:encoded>
				<?php endif; ?>
				<?php do_action( 'mihdan_mailru_pulse_feed_item', get_the_ID() ); ?>
			</item>
		<?php endwhile; ?>
	</channel>
</rss>
