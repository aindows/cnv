<?php

namespace aindows\pay\exception;
class ConfigException extends \Exception
{
    public function __construct($message = '', $code = 0)
    {
        $message = '' === $message ? 'Unknown Error' : $message;
        parent::__construct($message, intval($code));
    }
}