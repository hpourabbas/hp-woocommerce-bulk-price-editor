<?php

namespace HpWoocommerceBulkPriceEditor\App\Lib;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

class Response
{

    protected int $_status_code = 200;
    protected string $_error_code = '';
    protected string $_error_message = '';
    protected ?array $_json = null;

    public function __construct($ret)
    {
        if (is_wp_error($ret)) {
            $this->_status_code = 503;
            $this->_error_code = $ret->get_error_code();
            $this->_error_message = $ret->get_error_message();

            $this->_json['message'] = $this->_error_code . ' | ' . $this->_error_message;
        } else {
            $this->_status_code = wp_remote_retrieve_response_code($ret);

            // Retrieve the body of the response
            $this->_json = json_decode(wp_remote_retrieve_body($ret), true);
        }
    }

    public function errorCode(): string
    {
        return $this->_error_code;
    }

    public function errorMessage(): string
    {
        return $this->_error_message . ' ' . ($this->_json['message'] ?? '');
    }

    public function json(): mixed
    {
        return $this->_json;
    }

    public function statusCode(): int
    {
        return $this->_status_code;
    }

    public function error(): bool
    {
        return $this->_status_code !== 200;
    }

    public function successful(): bool
    {
        return !$this->error();
    }

}