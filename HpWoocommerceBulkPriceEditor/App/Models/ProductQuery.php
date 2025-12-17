<?php

namespace HpWoocommerceBulkPriceEditor\App\Models;

// If this file is called directly, abort.
use HpWoocommerceBulkPriceEditor\App\Lib\BaseQuery;

if (!defined('HP_EXEC')) {
    die;
}

class ProductQuery extends BaseQuery
{

    public function __construct()
    {
        parent::__construct();

        $this->columns = [
            'p.ID',
            'p.post_title',
            'rp.meta_value AS regular_price',
            'sp.meta_value AS sale_price',
            'pr.meta_value AS price'
        ];
    }

    public function filterCategory(array $term_ids): static
    {
        foreach ($term_ids as $term_id) {
            $categories = Category::tree($term_id);
            $categories = Category::toFlat($categories);
            $ids = array_map(function ($cat) {
                return $cat['id'];
            }, $categories);
            $term_ids = array_merge($term_ids, $ids);
        }

        $term_ids = array_unique($term_ids);

        $this->filters[] = [
            'taxonomy' => 'product_cat',
            'terms' => $term_ids
        ];

        return $this;
    }

    public function filterBrand(array $term_ids): static
    {
        foreach ($term_ids as $term_id) {
            $brands = Brand::tree($term_id);
            $brands = Brand::toFlat($brands);
            $ids = array_map(function ($brn) {
                return $brn['id'];
            }, $brands);
            $term_ids = array_merge($term_ids, $ids);
        }

        $term_ids = array_unique($term_ids);

        $this->filters[] = [
            'taxonomy' => 'product_brand',
            'terms' => $term_ids
        ];

        return $this;
    }

    public function getIds(): array|object|null
    {
        $DB = $this->wpDb;

        $sql = $this->buildSelectQuery(false, ['p.ID']);
        return $DB->get_results($sql);
    }

    public function buildSelectQuery($paginate = true, array $columns = null): string
    {
        $DB = $this->wpDb;

        list($joins, $wheres) = $this->buildFilterJoins();

        if (!$columns) {
            $columns = $this->columns;
        }
        $columns = implode(', ', $columns);

        return "
            SELECT 
                {$columns}
            FROM {$DB->posts} AS p
            {$joins}
            LEFT JOIN {$DB->postmeta} AS rp 
                ON rp.post_id = p.ID AND rp.meta_key = '_regular_price'
            LEFT JOIN {$DB->postmeta} AS sp 
                ON sp.post_id = p.ID AND sp.meta_key = '_sale_price'
            LEFT JOIN {$DB->postmeta} AS pr 
                ON pr.post_id = p.ID AND pr.meta_key = '_price'                           
            WHERE p.post_type = 'product'
              AND p.post_status = 'publish'
              AND NOT EXISTS (SELECT * FROM {$DB->posts} WHERE post_status = 'publish' AND post_parent = p.ID)            
              {$wheres}
            GROUP BY p.ID" . ($paginate ? " LIMIT {$this->limit} OFFSET {$this->offset}" : "");
    }

    public function buildCountQuery(): string
    {
        $DB = $this->wpDb;

        list($joins, $wheres) = $this->buildFilterJoins();

        return "
            SELECT COUNT(DISTINCT p.ID)
            FROM {$DB->posts} AS p
            {$joins}
            WHERE p.post_type = 'product'
              AND p.post_status = 'publish'
              AND NOT EXISTS (SELECT * FROM {$DB->posts} WHERE post_status = 'publish' AND post_parent = p.ID)
              {$wheres}";
    }

    private function buildFilterJoins(): array
    {
        $DB = $this->wpDb;

        $joins = '';
        $wheres = '';

        $index = 1;

        foreach ($this->filters as $filter) {

            $alias_tr = "tr{$index}";
            $alias_tt = "tt{$index}";
            $terms = implode(',', array_map('intval', $filter['terms']));

            $joins .= "
                INNER JOIN {$DB->term_relationships} AS {$alias_tr} 
                    ON p.ID = {$alias_tr}.object_id
                INNER JOIN {$DB->term_taxonomy} AS {$alias_tt} 
                    ON {$alias_tr}.term_taxonomy_id = {$alias_tt}.term_taxonomy_id
            ";

            $wheres .= "
                AND {$alias_tt}.taxonomy = '{$filter['taxonomy']}'
                AND {$alias_tt}.term_id IN ({$terms})               
            ";

            $index++;
        }

        return [$joins, $wheres];
    }


}
