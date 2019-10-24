<?php
/**
 * @var \Mihdan\MailRuPulseFeed\Main $this
 */
header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . $this->wposa_obj->get_option( 'charset', 'feed' ), true );
echo '<?xml version="1.0" encoding="' . $this->wposa_obj->get_option( 'charset', 'feed' ) . '"?' . '>';
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
	<channel>
		<title><?php bloginfo_rss( 'name' ); ?></title>
		<link><?php bloginfo_rss( 'url' ); ?></link>
		<description><?php bloginfo_rss( 'description' ); ?></description>
		<language><?php echo substr( get_bloginfo_rss( 'language' ), 0, 2 ); ?></language>
		<?php do_action( 'rss2_head' ); ?>
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
