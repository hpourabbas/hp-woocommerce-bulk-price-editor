<?php

namespace HpWoocommerceBulkPriceEditor\App\Lib;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}


class Factory
{

    public static function getApplication()
    {
        $namespace = explode('\\', __NAMESPACE__)[0];

        $root = str_replace("/{$namespace}/App/Lib/Factory.php", '', __FILE__);

        $applicationClass = "{$namespace}\App\Application";

        return new $applicationClass($namespace, $root);
    }

}