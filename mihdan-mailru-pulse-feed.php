<?php
/**
 * Zen Feed
 *
 * Plugin Name: Zen Feed (Яндекс.Новости, Дзен, Пульс)
 * Plugin URI: https://wordpress.org/plugins/mihdan-mailru-pulse-feed/
 * Description: Плагин формирует RSS-ленту (фид) для платформы Дзен. Подходит для создания ленты под Яндекс.Новости, Дзен (как для паблишеров, так и для новостных агентств) и Пульс от Mail.ru.
 * Author: Mikhail Kobzarev
 * Author URI: https://www.kobzarev.com/
 * Requires at least: 5.3
 * Tested up to: 6.5
 * Version: 0.8.2
 * Stable tag: 0.8.2
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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

const MIHDAN_MAILRU_PULSE_FEED_VERSION = '0.8.2';
const MIHDAN_MAILRU_PULSE_FEED_PATH    = __DIR__;
const MIHDAN_MAILRU_PULSE_FEED_FILE    = __FILE__;
const MIHDAN_MAILRU_PULSE_FEED_SLUG    = 'mihdan-mailru-pulse-feed';
const MIHDAN_MAILRU_PULSE_FEED_PREFIX  = 'mihdan_mailru_pulse_feed';

define( 'MIHDAN_MAILRU_PULSE_FEED_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Init plugin class on plugin load.
 */
if ( file_exists( MIHDAN_MAILRU_PULSE_FEED_PATH . '/vendor/autoload.php' ) ) {
	require_once MIHDAN_MAILRU_PULSE_FEED_PATH . '/vendor/autoload.php';
	new Main();
}
