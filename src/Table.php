<?php

namespace bemang\Database;

use bemang\Database\Table\Entity;

class Table
{
    protected $databaseName;
    protected $name;

    protected $entityType;

    /**
     * Constructeur d'une table
     *
     * @param String $name nom de la table
     * @param String $databaseName nom de la bdd de la table
     */
    public function __construct(string $name, string $databaseName)
    {
        $this->name = $name;
        $this->databaseName = $databaseName;
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }

    public function add(): bool
    {
    }

    public function update(): bool
    {
    }

    public function fetch(): bool
    {
    }

    public function fetchAll(): bool
    {
    }

    public function delete(): bool
    {
    }
}
