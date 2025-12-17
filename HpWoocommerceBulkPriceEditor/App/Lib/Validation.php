<?php

namespace HpWoocommerceBulkPriceEditor\App\Lib;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

class Validation
{

    public static function validate(array $data, $rules): bool
    {
        foreach ($rules as $name => $rules_data) {
            $value = $data[$name];

            //if ($name === 'discount_type') {
            // var_dump($value);
            //}

            // if (null !== $value) {
            foreach ($rules_data as $rule_data) {
                $temp = explode(':', $rule_data);
                $method = $temp[0];
                $arg = $temp[1] ?? null;

                if (false === self::$method($value, $arg)) {
                    return false;
                }
            }
            //}
        }

        return true;
    }

    public static function single_in($value, $arg): bool
    {
        $arg = explode('|', $arg);

        if ('NULL' === gettype($value)) {
            $value = '';
        }

        return 'string' === gettype($value) && in_array($value, $arg);
    }

    public static function multiple_in($value, $arg): bool
    {
        $arg = explode('|', $arg);

        if ('NULL' === gettype($value)) {
            $value = [];
        }

        $value_check = function () use ($value, $arg) {
            foreach ($value as $val) {
                if (null !== $val && !in_array($val, $arg)) {
                    return false;
                }
            }
            return true;
        };

        return 'array' === gettype($value) && $value_check();
    }

    public static function max($value, $arg): bool
    {
        return strlen($value) <= (int)$arg;
    }

    public static function low($value, $arg): bool
    {
        return (float)$value >= (float)$arg;
    }

    public static function numeric($value, $arg): bool
    {
        return is_numeric($value);
    }

}