<?php

namespace bemang\Database;

use bemang\Database\Table\Entity;
use bemang\Database\Manager\DBManager;
use bemang\Database\Table\EntityGenerator;

class Table
{
    protected $databaseName;
    protected $name;

    protected $entityClassName;

    /**
     * Constructeur d'une table
     *
     * @param String $name nom de la table
     * @param String $databaseName nom de la bdd de la table
     */
    public function __construct(string $name, string $databaseName)
    {
        $this->entityClassName = EntityGenerator::generate($name, $databaseName, DBManager::getInstance());
        $this->name = $name;
        $this->databaseName = $databaseName;
    }

    public function getNewEntity(): Entity
    {
        $className = $this->getEntityClassName();
        return new $className();
    }

    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    public function insert(Entity $entity): bool
    {
        $entity->emptyId();
        $queryBuilder = DBManager::getInstance()->getBuilder();
        $queryBuilder->setTable($this->name)->insert($entity->getAttribuesAsArray());
        $query = DBManager::getInstance()->getDatabase($this->databaseName)->prepare($queryBuilder->toSql());
        return $query->execute($queryBuilder->getValues());
    }

    public function update(Entity $entity): bool
    {
    }

    public function fetch(): bool
    {
    }

    public function fetchAll(): bool
    {
    }

    public function delete(Enity $entity): bool
    {
    }
}
