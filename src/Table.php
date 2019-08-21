<?php

namespace bemang\Database;

abstract class Table
{
    protected $databaseName;
    protected $name;

    public function __construct(string $name, string $databaseName)
    {
        $this->name = $name;
        $this->databaseName = $databaseName;
    }
}
