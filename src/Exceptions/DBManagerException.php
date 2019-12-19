<?php

namespace bemang\Database\Exceptions;

/**
 * Exception du manager
 */
class DBManagerException extends \Exception
{
    public function __toString()
    {
        return $this->message;
    }
}
