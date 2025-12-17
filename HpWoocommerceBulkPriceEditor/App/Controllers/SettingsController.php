<?php

namespace HpWoocommerceBulkPriceEditor\App\Controllers;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

use HpWoocommerceBulkPriceEditor\App\Models\Config;
use HpWoocommerceBulkPriceEditor\App\Lib\Request;
use HpWoocommerceBulkPriceEditor\App\Lib\Validation;


class SettingsController
{

    public function show(Request $request): void
    {
        // get settings from the config
        $settings = Config::get('settings');

        wp_send_json_success(['settings' => $settings]);
    }

    public function update(Request $request): void
    {
        // validation request
        $data = $this->validate($request);

        $settings = Config::get('settings');

        $settings['price_rounding'] = $data['price_rounding'] ?? 0;
        Config::set('settings', $settings);

        wp_send_json_success(['message' => 'Your changes successfully saved!']);
    }

    protected function validate(Request $request): array
    {
        $data = [
            'price_rounding' => $request->input('price_rounding', 0),
        ];


        $rules = [
            'price_rounding' => ['numeric', 'low:1'],
        ];


        if (Validation::validate($data, $rules) !== true) {
            wp_send_json_error(['message' => __('Input data is not valid!')], 422);
        }


        return $data;
    }

}