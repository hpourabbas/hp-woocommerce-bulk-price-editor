<?php

namespace HpWoocommerceBulkPriceEditor\App\Lib;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

class BaseQuery
{

    public \wpdb $wpDb;
    protected int $limit = 5;
    protected int $offset = 0;
    protected array $filters = [];
    protected array $columns = [];

    public function __construct()
    {
        global $wpdb;
        $this->wpDb = $wpdb;
    }

    public function when($condition, $callback): static
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    /**
     * @return array{
     *     page: int,
     *     per_page: int,
     *     total: int,
     *     data: array
     * }
     */
    public function paginate($page, $perPage): array
    {
        $DB = $this->wpDb;

        $this->limit = intval($perPage);
        $count_sql = $this->buildCountQuery();
        $total = $DB->get_var($count_sql);


        $last_page = ceil($total / $this->limit);
        if ($page > $last_page) {
            $page = $last_page;
        }

        if ($page < 1) {
            $page = 1;
        }


        $this->offset = ($page - 1) * $perPage;

        $sql = $this->buildSelectQuery();
        $items = $DB->get_results($sql);

        return [
            'page' => (int)$page,
            'per_page' => (int)$perPage,
            'last_page' => (int)$last_page,
            'total' => (int)$total,
            'data' => $items,
        ];
    }

}
