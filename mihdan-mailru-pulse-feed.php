<?php
/**
 * Mihdan: Zen Feed
 *
 * Plugin Name: Zen Feed
 * Plugin URI: https://wordpress.org/plugins/mihdan-mailru-pulse-feed/
 * Description: Плагин формирует RSS-ленту (фид) для платформы Дзен, которая создана для просмотра и создания контента.
 * Author: Mikhail Kobzarev
 * Author URI: https://www.kobzarev.com/
 * Requires at least: 5.3
 * Tested up to: 6.4
 * Version: 0.7.1
 * Stable tag: 0.7.1
 *
 * Text Domain: mihdan-mailru-pulse-feed
 * Domain Path: /languages/
 *
 * GitHub Plugin URI: https://github.com/mihdan/mihdan-mailru-pulse-feed
 *
 * @package mihdan-mailru-pulse-feed
 * @author  Mikhail Kobzarev
 * @link https://dzen.ru/help/website/rss-modify.html
 */

use Mihdan\MailRuPulseFeed\Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const MIHDAN_MAILRU_PULSE_FEED_VERSION = '0.7.1';
const MIHDAN_MAILRU_PULSE_FEED_PATH    = __DIR__;
const MIHDAN_MAILRU_PULSE_FEED_FILE    = __FILE__;
const MIHDAN_MAILRU_PULSE_FEED_SLUG    = 'mihdan-mailru-pulse-feed';

define( 'MIHDAN_MAILRU_PULSE_FEED_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Init plugin class on plugin load.
 */
if ( file_exists( MIHDAN_MAILRU_PULSE_FEED_PATH . '/vendor/autoload.php' ) ) {
	require_once MIHDAN_MAILRU_PULSE_FEED_PATH . '/vendor/autoload.php';
	new Main();
}
