<?php
/**
 * Mihdan: Mail.ru Pulse Feed
 *
 * Plugin Name: Mail.ru Pulse Feed
 * Plugin URI: https://wordpress.org/plugins/mihdan-mailru-pulse-feed/
 * Description: Плагин, формирует ленту для новой рекомендательной системы Пульс от компании Mail.ru. Пульс создает персонализованный контент на базе технологий машинного обучения.
 * Author: Mikhail Kobzarev
 * Author URI: https://www.kobzarev.com/
 * Requires at least: 5.3
 * Tested up to: 5.6
 * Version: 0.4
 * Stable tag: 0.4
 *
 * Text Domain: mihdan-mailru-pulse-feed
 * Domain Path: /languages/
 *
 * GitHub Plugin URI: https://github.com/mihdan/mihdan-mailru-pulse-feed
 *
 * @package mihdan-mailru-pulse-feed
 * @author  Mikhail Kobzarev
 */
use Mihdan\MailRuPulseFeed\Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'MIHDAN_MAILRU_PULSE_FEED_VERSION', '0.4' );
define( 'MIHDAN_MAILRU_PULSE_FEED_PATH', __DIR__ );
define( 'MIHDAN_MAILRU_PULSE_FEED_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'MIHDAN_MAILRU_PULSE_FEED_FILE', __FILE__ );
define( 'MIHDAN_MAILRU_PULSE_FEED_SLUG', 'mihdan-mailru-pulse-feed' );

/**
 * Init plugin class on plugin load.
 */

static $plugin;

if ( ! isset( $plugin ) ) {
	require_once MIHDAN_MAILRU_PULSE_FEED_PATH . '/vendor/autoload.php';
	$plugin = new Main();
}

// eof;
