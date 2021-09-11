<?php

namespace bemang\Database\Exceptions;

/**
 * Exception pour le query builder
 * @codeCoverageIgnore
 */
class QueryBuilderException extends \Exception
{
    public function __toString()
    {
        return $this->message;
    }
}
