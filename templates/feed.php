<?php
/**
 * @var \Mihdan\MailRuPulseFeed\Main $this
 */
header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . $this->wposa_obj->get_option( 'charset', 'feed' ), true );
echo '<?xml version="1.0" encoding="' . $this->wposa_obj->get_option( 'charset', 'feed' ) . '"?' . '>';
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
	<channel>
		<title><?php echo esc_html( $this->wposa_obj->get_option( 'title', 'source' ) ); ?></title>
		<link><?php echo esc_url( $this->wposa_obj->get_option( 'link', 'source' ) ); ?></link>
		<description><?php echo esc_html( $this->wposa_obj->get_option( 'description', 'source' ) ); ?></description>
		<language><?php echo esc_html( $this->wposa_obj->get_option( 'language', 'source' ) ); ?></language>
		<generator>mihdan-mailru-pulse-feed</generator>
		<image>
			<url><?php echo esc_url( $this->wposa_obj->get_option( 'image', 'source' ) ); ?></url>
		</image>
		<?php do_action( 'rss2_head' ); ?>
		<?php do_action( 'mihdan_mailru_pulse_feed_head' ); ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<item>
				<link><?php the_permalink_rss(); ?></link>
				<?php if ( $this->is_amp_support() ) : ?>
					<amplink><?php $this->the_amp_permalink( get_the_ID() ); ?></amplink>
				<?php endif; ?>
				<title><?php the_title_rss(); ?></title>
				<author><?php the_author(); ?></author>
				<pubDate><?php echo esc_html( get_post_time( 'r', true ) ); ?></pubDate>
				<description><![CDATA[<?php echo apply_filters( 'mihdan_mailru_pulse_feed_item_excerpt', get_the_excerpt(), get_the_ID() ); ?>]]></description>
				<?php if ( 'on' === $this->wposa_obj->get_option( 'fulltext', 'feed' ) ) : ?>
					<content:encoded><![CDATA[<?php echo apply_filters( 'mihdan_mailru_pulse_feed_item_content', get_the_content_feed(), get_the_ID() ); ?>]]></content:encoded>
				<?php endif; ?>
				<?php do_action( 'mihdan_mailru_pulse_feed_item', get_the_ID() ); ?>
			</item>
		<?php endwhile; ?>
	</channel>
</rss>
