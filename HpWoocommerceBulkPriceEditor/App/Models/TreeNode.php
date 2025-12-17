<?php

namespace HpWoocommerceBulkPriceEditor\App\Models;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

class TreeNode
{

    protected static ?array $terms = null;

    public static function flat(): array
    {
        $nodes = get_terms(static::$terms);

        $result = [];
        foreach ($nodes as $node) {
            $result[] = [
                'id' => $node->term_id,
                'name' => $node->name,
            ];
        }

        return $result;
    }

    public static function tree($parent_id = 0): array
    {
        $terms = static::$terms;
        $terms['parent'] =  $parent_id;
        $nodes = get_terms($terms);

        $result = [];
        foreach ($nodes as $node) {
            $result[] = [
                'id' => $node->term_id,
                'name' => $node->name,
                'children' => self::tree($node->term_id),
            ];
        }

        return $result;
    }

    public static function toFlat($nodes, $prefix = ''): array
    {
        $result = [];

        foreach ($nodes as $node) {
            $result[] = [
                'id' => $node['id'],
                'name' => $prefix . $node['name'],
            ];

            if (!empty($node['children'])) {
                $result = array_merge(
                    $result,
                    self::toFlat($node['children'], $prefix . $node['name'] . ' - ')
                );
            }
        }

        return $result;
    }

}