<?php
/**
 * Hp Woocommerce Bulk Price Editor
 *
 * @package           Hp
 * @author            Hadi Pourabbas
 * @copyright         2025 Hadi Pourabbas
 * @license           GPL-2.0-or-later
 *
 * Plugin Name: Hp Woocommerce Bulk Price Editor
 * Plugin URI: https://github.com/hpourabbas/hp-woocommerce-bulk-price-editor
 * Description: Changing the price of WooCommerce products and applying discounts to products in bulk
 * Version: 1.0.0
 * Author: Hadi Pourabbas
 * Author URI: https://github.com/hpourabbas
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hp-woocommerce-bulk-price-editor
 * Domain Path: /languages
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 * Requires at least: 6.0
 * Tested up to: 6.9
 * WC requires at least: 10.3
 * tested up to: 10.3
 * Copyright 2025 Hadi Pourabbas
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!defined('HP_EXEC')) {
    define('HP_EXEC', true);
}

function hp_woocommerce_bulk_price_editor_plugin_autoloader($class_name): void
{
    // Only load classes from plugin's namespace
    if (str_starts_with($class_name, 'HpWoocommerceBulkPriceEditor\\')) {
        $class_file = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
        $file = plugin_dir_path(__FILE__) . $class_file . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

// Autoload classes
spl_autoload_register('hp_woocommerce_bulk_price_editor_plugin_autoloader');


// Create application
HpWoocommerceBulkPriceEditor\App\Lib\Factory::getApplication()->run();







