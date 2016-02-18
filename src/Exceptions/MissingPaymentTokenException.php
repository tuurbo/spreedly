<?php

namespace Tuurbo\Spreedly\Exceptions;

class MissingPaymentTokenException extends \Exception
{
    public function __construct($message = 'Payment token is required', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
