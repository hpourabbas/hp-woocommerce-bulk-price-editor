<?php

namespace HpWoocommerceBulkPriceEditor\App\Models;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

class Config
{

    public static function get($key = null, $default = null): mixed
    {
        $data = get_option('hp_woocommerce_bulk_price_editor_config');
        if (!$data) {
            $data = [
                'settings' => [
                    'price_rounding' => 1,
                ]
            ];
        }

        if (null === $key) {
            return $data;
        }

        $value = $data;

        // split the key into keys
        $keys = explode('.', $key);
        foreach ($keys as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                // return default if key does not exist
                return $default;
            }
        }

        // return the found value
        return $value;
    }

    public static function set($key, $value): void
    {
        $data = self::get();

        // split the key into keys
        $keys = explode('.', $key);
        $current = &$data;

        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                // initialize as an array if the key doesn't exist or isn't an array
                $current[$key] = [];
            }
            // move the reference deeper into the array
            $current = &$current[$key];
        }

        // set the final value
        $current = $value;

        update_option('hp_woocommerce_bulk_price_editor_config', $data);
    }

}
