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
				<title><?php the_title_rss(); ?></title>
				<author><?php the_author(); ?></author>
				<pubDate><?php echo esc_html( get_post_time( 'r', true ) ); ?></pubDate>
				<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
				<?php if ( has_post_thumbnail() ) : ?>
					<?php
					$thumbnail = get_the_post_thumbnail_url( get_the_ID(), 'large' );
					$type      = wp_check_filetype( $thumbnail );
					?>
					<enclosure url="<?php echo esc_url( $thumbnail ); ?>" type="<?php echo esc_attr( $type['type'] ); ?>"/>
				<?php endif; ?>
			</item>
		<?php endwhile; ?>
	</channel>
</rss>
