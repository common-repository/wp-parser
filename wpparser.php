<?php
	/**
	 * Plugin Name: WP Parser - универсальный парсер контента
	 * Plugin URI: http://wpparser.com
     * Description: Универсальный парсер контента для автонаполнения блогов, новостных сайтов, сайтов каталогов и т.д. Парсер охватывает все виды контента, текст, изображения, видео.
     * Author: WPParser <wpparser@gmail.com>
	 * Version: 1.0.5
	 * Text Domain: webcraftic-cloud-scraper
	 * Domain Path: /languages/
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	if( defined('WSCR_PLUGIN_ACTIVE') ) {
		return;
	}

	// Устанавливаем константу, что плагин активирован
	define('WSCR_PLUGIN_ACTIVE', true);

	// Корневая директория плагина
	define('WSCR_PLUGIN_DIR', dirname(__FILE__));
	
	// Абсолютный url корневой директории плагина
	define('WSCR_PLUGIN_URL', plugins_url(null, __FILE__));

	// Относительный url плагина
	define('WSCR_PLUGIN_BASE', plugin_basename(__FILE__));
	
	// Тип записей используемый для заданий парсера
	define('WSCR_SCRAPES_POST_TYPE', 'wbcr-scrapes');

	// Адрес удаленного сервера
    define('WSCR_API_SERVER_URL', 'https://lk.wpparser.com');



	

	$current_encoding = mb_internal_encoding();
	mb_internal_encoding("UTF-8");

	// Устранение проблем в случае использования старых версих PHP на сервере клиента
	if( version_compare(PHP_VERSION, '5.4.0', '>') ) {

		require_once(WSCR_PLUGIN_DIR . '/libs/factory/core/boot.php');

		require_once(WSCR_PLUGIN_DIR . '/includes/class.helpers.php');
		require_once(WSCR_PLUGIN_DIR . '/includes/class.plugin.php');

		new WSCR_Plugin(__FILE__, array(
			'prefix' => 'wbcr_scr_',
			'plugin_name' => 'wbcr_scraper',
			'plugin_title' => __('WP Parser - универсальный парсер контента', 'webcraftic-cloud-scraper'),
			'plugin_version' => '1.0.1',
			'required_php_version' => '5.4',
			'required_wp_version' => '4.2',
			'plugin_build' => 'free',
			//'updates' => WSCR_PLUGIN_DIR . '/updates/'
		));
	} else {
		// Уведомление о старой версии PHP
		add_action('admin_notices', function () {
			echo '<div class="notice notice-error is-dismissible"><p>Вы используете старую версию PHP. Пожалуйста, обновите версию PHP до 5.4 и выше!</p></div>';
		});
	}
	mb_internal_encoding($current_encoding);


    add_action( 'activated_plugin', array('WSCR_Plugin', 'registrationHook'), 10, 2);

