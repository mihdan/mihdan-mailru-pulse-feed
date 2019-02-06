<?php
/**
 * Mihdan: Mail.ru Pulse Feed
 *
 * Plugin Name: Mihdan: Mail.ru Pulse Feed
 * Plugin URI: https://github.com/mihdan/mihdan-mailru-pulse-feed
 * Description: WordPress плагин, формирующий ленту для новой рекомендательной системы Пульс от компании Mail.ru.
 * Author: Mikhail Kobzarev
 * Author URI: https://www.kobzarev.com/
 * Requires at least: 2.3
 * Tested up to: 5.1
 * Version: 0.0.1
 * Stable tag: 0.0.1
 *
 * Text Domain: mihdan-mailru-pulse-feed
 * Domain Path: /languages/
 *
 * @package mihdan-mailru-pulse-feed
 * @author  Mikhail Kobzarev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'MIHDAN_MAILRU_PULSE_FEED_VERSION', '0.0.1' );
define( 'MIHDAN_MAILRU_PULSE_FEED_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'MIHDAN_MAILRU_PULSE_FEED_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Init plugin class on plugin load.
 */

static $plugin;

if ( ! isset( $plugin ) ) {
	require_once MIHDAN_MAILRU_PULSE_FEED_PATH . 'vendor/autoload.php';
	$plugin = new Mihdan_Mailru_Pulse_Feed_Main();
}

// eof;
