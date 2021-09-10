<?php

namespace bemang\Database\Exceptions;

/**
 * Exception pour le query builder
 * @codeCoverageIgnore
 */
class TableException extends \Exception
{
    public function __toString()
    {
        return $this->message;
    }
}
