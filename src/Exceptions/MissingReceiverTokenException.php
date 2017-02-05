<?php

namespace Tuurbo\Spreedly\Exceptions;

class MissingReceiverTokenException extends \Exception
{
    public function __construct($message = 'Receiver token is required', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
