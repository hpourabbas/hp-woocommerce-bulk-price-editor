<?php

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

use HpWoocommerceBulkPriceEditor\App\Controllers\ProductController;
use HpWoocommerceBulkPriceEditor\App\Controllers\SettingsController;

return [
    // product
    'product.index' => [
        'method' => 'GET',
        'callback' => [ProductController::class, 'index'],
    ],
    'product.categories' => [
        'method' => 'GET',
        'callback' => [ProductController::class, 'categories'],
    ],
    'product.brands' => [
        'method' => 'GET',
        'callback' => [ProductController::class, 'brands'],
    ],
    'product.update-all' => [
        'method' => 'POST',
        'callback' => [ProductController::class, 'updateAll'],
    ],

    // settings
    'settings.show' => [
        'method' => 'GET',
        'callback' => [SettingsController::class, 'show'],
    ],
    'settings.update' => [
        'method' => 'POST',
        'callback' => [SettingsController::class, 'update'],
    ],
];