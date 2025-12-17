<?php

namespace HpWoocommerceBulkPriceEditor\App\Lib;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

class Request
{

    private mixed $rest_request = null;

    public function __construct($rest_request = null)
    {
        $this->rest_request = $rest_request;
    }

    public function input(string $key, $default = null): mixed
    {
        if ($this->rest_request) {
            $value = $this->rest_request->get_param($key);
        } else {
            if (!$value = $_GET[$key] ?? null) {
                $value = $_POST[$key] ?? null;
            }
        }

        if (null === $value) {
            return $default;
        } else {
            return $value;
        }
    }

    public function all(): array
    {
        if (!$get = $_GET) {
            $get = [];
        }
        if (!$post = $_POST) {
            $post = [];
        }
        if (!$json = $this->rest_request?->get_json_params()) {
            $json = [];
        }

        return array_merge($get, $post, $json);
    }

}