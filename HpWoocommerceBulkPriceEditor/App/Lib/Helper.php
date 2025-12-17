<?php

namespace HpWoocommerceBulkPriceEditor\App\Lib;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

class Helper
{

    public static function file_exists($path)
    {
        global $wp_filesystem;

        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();

        if (!$wp_filesystem || !$path) {
            return false;
        }

        return $wp_filesystem->exists($path);
    }

}