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

        dd($data);

        /*// Get cid and lid from the user input
        $name = $request->input('name', null);



        $settings = Config::get('settings');

        $data = [];
        $error = '';
        switch ($name) {
            case 'format_quality':
                $data = [
                    'input_extensions' => $request->input('input_extensions', []),
                    'output_extensions' => $request->input('output_extensions', []),
                    'quality' => $request->input('quality', 1),
                ];
                break;
            case 'dpr':
                $data = $request->input('dpr', []);
                $data = Config::check_dpr($data);
                break;
            case 'general':
                $metadata = $request->input('metadata', 'all');
                if (!License::can('REMOVE_METADATA')) {
                    $metadata = 'all';
                }
                $data = [
                    'metadata' => $metadata,
                    'creator' => $request->input('creator', ''),
                    'credit' => $request->input('credit', ''),
                    'copyright' => $request->input('copyright', ''),
                    'make_backup' => $request->input('make_backup', '1'),
                    'delete_backup' => $request->input('delete_backup', ''),
                    'htaccess' => $request->input('htaccess', ''),
                    'generate_on_upload' => $request->input('generate_on_upload', ''),
                    'background_image' => $request->input('background_image', ''),
                ];

                if ($data['htaccess']) {
                    if (!Htaccess::append()) {
                        $data['htaccess'] = '';
                        $error = '.htaccess file is not writable!';
                    }
                } else {
                    if (!Htaccess::remove()) {
                        $data['htaccess'] = '1';
                        $error = '.htaccess file is not writable!';
                    }
                }

                break;
            case 'watermark':
                $type = $request->input('type', 'none');
                $image = $request->input('image', []);
                $text = $request->input('text', []);
                $prefix = strtoupper($type);
                if (!License::can("{$prefix}_WATERMARK")) {
                    $type = 'none';
                }

                $data = [
                    'type' => $type,
                    'image' => [
                        'url' => $image['url'] ?? '',
                        'width' => $image['width'] ?? 100,
                        'height' => $image['height'] ?? 100,
                        'positions' => $image['pro']['positions'] ?? [],
                        'rotation' => $image['pro']['rotation'] ?? 0,
                        'opacity' => $image['pro']['opacity'] ?? 100,
                    ],
                    'text' => [
                        'content' => $text['content'] ?? 'Sample Text',
                        'font_name' => $text['font_name'] ?? '',
                        'font_color' => $text['font_color'] ?? '#000000',
                        'font_size' => $text['font_size'] ?? 14,
                        'positions' => $text['pro']['positions'] ?? [],
                        'rotation' => $text['pro']['rotation'] ?? 0,
                        'opacity' => $text['pro']['opacity'] ?? 100,
                    ],
                ];

                break;
        }

        $settings[$name] = $data;
        Config::set('settings', $settings);

        if ($error) {
            wp_send_json_error(['message' => $error], 500);
        } else {
            wp_send_json_success(['message' => 'Your changes successfully saved!']);
        }*/
    }

    protected function validate(Request $request): array
    {
        $data = [
            'price_rounding' => $request->input('price_rounding', 0),
        ];


        $rules = [
            'price_rounding' => ['numeric', 'low:0'],
        ];


        if (Validation::validate($data, $rules) !== true) {
            wp_send_json_error(['message' => __('Input data is not valid!')], 422);
        }


        return $data;
    }

}