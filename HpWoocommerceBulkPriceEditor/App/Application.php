<?php

namespace HpWoocommerceBulkPriceEditor\App;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

use HpWoocommerceBulkPriceEditor\App\Lib\BaseApplication;

class Application extends BaseApplication
{

    protected ?string $env = null; // development | production
    protected ?string $pluginMenuName = 'Hp Woocommerce Bulk Price Editor';

    public function onActivation()
    {

    }

    public function hooks(): void
    {
        parent::hooks();
    }

    public function onDeactivation()
    {

    }

}


