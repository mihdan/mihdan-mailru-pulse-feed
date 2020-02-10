<?php
/**
 * @package mihdan-mailru-pulse-feed
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wptrt_notice_dismissed_mihdan-mailru-pulse-feed' );
delete_option( 'feed' );
delete_option( 'source' );
delete_option( 'widget' );
delete_option( 'contacts' );
// eol.

