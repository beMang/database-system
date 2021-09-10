<?php

namespace bemang\Database;

use bemang\Database\Table\Entity;
use bemang\Database\Manager\DBManager;
use bemang\Database\Table\EntityGenerator;
use bemang\Database\Exceptions\TableException;

class Table
{
    protected string $databaseName;
    protected string $name;

    protected string $entityClassName;

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
        $id = $entity->getId();
        $entity->emptyId();
        $queryBuilder = DBManager::getInstance()->getBuilder();
        $queryBuilder->setTable($this->name)->insert($entity->getAttribuesAsArray());
        $query = DBManager::getInstance()->getDatabase($this->databaseName)->prepare($queryBuilder->toSql());
        if (is_int($id)) {
            $entity->setId($id);
        }
        return $query->execute($queryBuilder->getValues());
    }

    public function update(Entity $entity): bool
    {
        $queryBuilder = DBManager::getInstance()->getBuilder();
        $queryBuilder->setTable($this->name)->update($entity->getAttribuesAsArray())->where('id = :id');
        $query = DBManager::getInstance()->getDatabase($this->databaseName)->prepare($queryBuilder->toSql());
        return $query->execute($queryBuilder->getValues());
    }

    public function fetch($id): Entity
    {
        if ($id instanceof Entity) {
            $id = $id->getId();
        } elseif (is_numeric($id)) {
            $id = $id;
        } else {
            throw new TableException('L\'id doit être numérique ou avoir la classe Entity');
        }

        $queryBuilder = DBManager::getInstance()->getBuilder();
        $queryBuilder->setTable($this->name)->select('*')->where('id = :id')->addValue('id', $id);
        $query = DBManager::getInstance()->getDatabase($this->databaseName)->prepare($queryBuilder->toSql());
        $succes = $query->execute($queryBuilder->getValues());
        if ($query->rowCount() > 1) { //If 2 line have the same id (impossible normally)
            return $query->fetchAll(\PDO::FETCH_CLASS, $this->getEntityClassName());
        } elseif ($succes == true) {
            $query->setFetchMode(\PDO::FETCH_CLASS, $this->getEntityClassName());
            return $query->fetch();
        } else {
            throw new TableException($query->errorInfo()[2]);
        }
    }

    public function fetchAll(): array
    {
        $queryBuilder = DBManager::getInstance()->getBuilder();
        $queryBuilder->setTable($this->name)->select('*');
        $query = DBManager::getInstance()->getDatabase($this->databaseName)->prepare($queryBuilder->toSql());
        if ($query->execute() == true) {
            return $query->fetchAll(\PDO::FETCH_CLASS, $this->getEntityClassName());
        } else {
            throw new TableException('Sql Error : ' . $query->errorInfo()[2]);
        }
    }

    public function delete($id): bool
    {
        if ($id instanceof Entity) {
            $id = $id->getId();
        } elseif (is_numeric($id)) {
            $id = $id;
        } else {
            throw new TableException('L\'id doit être numérique ou avoir la classe Entity');
        }
        $queryBuilder = DBManager::getInstance()->getBuilder();
        $queryBuilder->setTable($this->name)->delete('id = :id')->addValue('id', $id);
        $query = DBManager::getInstance()->getDatabase($this->databaseName)->prepare($queryBuilder->toSql());
        return $query->execute($queryBuilder->getValues());
    }
}
