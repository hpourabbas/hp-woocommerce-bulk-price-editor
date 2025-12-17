<?php

namespace HpWoocommerceBulkPriceEditor\App\Models;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

class Category extends TreeNode
{

    public static ?array $terms = [
        'taxonomy' => 'product_cat',
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => false,
    ];



    /*public static function flat(): array
    {
        $productCategories = get_terms(array(
            'taxonomy' => 'product_cat',
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        ));


        $categories = [];
        foreach ($productCategories as $category) {
            $categories[] = [
                'id' => $category->term_id,
                'name' => $category->name,
            ];
        }

        return parent::flat();
    }

    public static function tree($parent_id = 0): array
    {
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'parent' => $parent_id,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        $tree = [];

        foreach ($terms as $term) {
            $tree[] = [
                'id' => $term->term_id,
                'name' => $term->name,
                'children' => self::tree($term->term_id),
            ];
        }

        return $tree;
    }

    public static function flat_tree($categories, $prefix = ''): array
    {
        $result = [];

        foreach ($categories as $cat) {
            $result[] = [
                'id' => $cat['id'],
                'name' => $prefix . $cat['name'],
            ];

            if (!empty($cat['children'])) {
                $result = array_merge(
                    $result,
                    self::flat_tree($cat['children'], $prefix . $cat['name'] . ' - ')
                );
            }
        }

        return $result;
    }*/

}