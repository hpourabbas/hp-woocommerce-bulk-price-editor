<?php

namespace HpWoocommerceBulkPriceEditor\App\Models;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

class Brand extends TreeNode
{

    public static ?array $terms = [
        'taxonomy' => 'product_brand',
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => false,
    ];

    /*public static function all(): array
    {
        $productBrands = get_terms(array(
            'taxonomy' => 'product_brand',
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));

        $brands = [];
        foreach ($productBrands as $brand) {
            $brands[] = [
                'id' => $brand->term_id,
                'name' => $brand->name,
            ];
        }

        return $brands;
    }*/

}