<?php

namespace Tuurbo\Spreedly\Exceptions;

class MissingGatewayTokenException extends \Exception
{
    public function __construct($message = 'Gateway token is required', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
