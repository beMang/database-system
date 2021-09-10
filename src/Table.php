<?php

namespace bemang\Database;

use bemang\Database\Table\Entity;
use bemang\Database\Manager\DBManager;
use bemang\Database\Table\EntityGenerator;
use bemang\Database\Exceptions\TableException;

/**
 * Permet la gestion simple d'une table sql
 */
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

    /**
     * Renvoie une entitée vide de la table
     *
     * @return Entity
     */
    public function getNewEntity(): Entity
    {
        $className = $this->getEntityClassName();
        return new $className();
    }

    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    /**
     * Insère une entitée dans la table
     *
     * @param Entity $entity
     * @return boolean
     */
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

    /**
     * Met à jour une entité de la table
     *
     * @param Entity $entity
     * @return boolean
     */
    public function update(Entity $entity): bool
    {
        $queryBuilder = DBManager::getInstance()->getBuilder();
        $queryBuilder->setTable($this->name)->update($entity->getAttribuesAsArray())->where('id = :id');
        $query = DBManager::getInstance()->getDatabase($this->databaseName)->prepare($queryBuilder->toSql());
        return $query->execute($queryBuilder->getValues());
    }

    /**
     * Récupère une seule entité de la table
     *
     * @param Entity|numeric $id
     * @return Entity|bool Vaux faux si la valeur n'existe pas
     */
    public function fetch(Entity|int $id): Entity|bool
    {
        if ($id instanceof Entity) {
            $id = $id->getId();
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

    /**
     * Récupère toutes les entités de la table
     *
     * @return array
     */
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

    /**
     * Supprime une entité de la table
     *
     * @param integer|Entity $id
     * @return boolean
     */
    public function delete(int|Entity $id): bool
    {
        if ($id instanceof Entity) {
            $id = $id->getId();
        }
        $queryBuilder = DBManager::getInstance()->getBuilder();
        $queryBuilder->setTable($this->name)->delete('id = :id')->addValue('id', $id);
        $query = DBManager::getInstance()->getDatabase($this->databaseName)->prepare($queryBuilder->toSql());
        return $query->execute($queryBuilder->getValues());
    }
}
