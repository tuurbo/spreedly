<?php

namespace Tuurbo\Spreedly\Exceptions;

class MissingTransactionTokenException extends \Exception
{
    public function __construct($message = 'Transaction token is required', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
