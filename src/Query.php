<?php

namespace bemang\Database;

class Query
{
    protected $classTableName;
    protected $stringTableName;
    protected $stringDbName;
    protected $dbManager;

    public function __construct($tableName, $databaseName = null)
    {
        $this->setTableName($tableName, $databaseName);
        $this->setDBManager(DBManager::getInstance());
        return $this;
    }

    private function setTableName($tableName, $databaseName)
    {
        if (class_exists($databaseName . '_' . $tableName)) {
            $this->classTableName = $databaseName . '_' . $tableName;
            $this->stringDbName = $databaseName;
            $this->stringTableName = $tableName;
        } else {
            throw new \InvalidArgumentException('La table ' . $databaseName . '_' . $tableName . ' est inexistante');
        }
    }

    public function getDatabaseName()
    {
        return $this->stringDbName;
    }

    public function getStringTableName()
    {
        return $this->stringTableName;
    }

    public function getClassTableName()
    {
        return $this->classTableName;
    }

    public function setDBManager(DBManager $manager)
    {
        $this->dbManager = $manager;
    }

    
    public function getDbManager()
    {
        return $this->dbManager;
    }

    public function getEmptyInstance()
    {
        $className = $this->getClassTableName();
        return new $className;
    }

    public function getAll()
    {
        $db = $this->getDbManager()->getDatabase($this->getDatabaseName());
        $query = $db->prepare('SELECT * FROM ' . $this->getStringTableName());
        $query->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->getClassTableName());
        $query->execute();
        return $query->fetchAll();
    }

    public function getLast()
    {
        $db = $this->getDbManager()->getDatabase($this->getDatabaseName());
        $query = $db->prepare('SELECT * FROM ' . $this->getStringTableName() . ' LIMIT 1');
        $query->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->getClassTableName());
        $query->execute();
        return $query->fetch();
    }

    public function getWithCondition($key, $value)
    {
        $db = $this->getDbManager()->getDatabase($this->getDatabaseName());
        $query = $db->prepare('SELECT * FROM ' . $this->getStringTableName() . ' WHERE ' . $key . ' = :' . $key);
        $query->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->getClassTableName());
        $query->execute([
            $key => $value
        ]);
        return $query->fetchAll();
    }

    public function getWithInnerJoin($otherTable, $cond, $otherCond = false)
    {
        $db = $this->getDbManager()->getDatabase($this->getDatabaseName());
        $sql = 'SELECT * FROM ' . $this->getStringTableName() . ' INNER JOIN ' . $otherTable . ' ON ' . $cond;
        if ($otherCond != false) {
            $sql .= ' WHERE ' . $otherCond;
        }
        $query = $db->prepare($sql);
        $query->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->getClassTableName());
        $query->execute();
        return $query->fetchAll();
    }

    public function insert($object)
    {
        $db = $this->getDbManager()->getDatabase($this->getDatabaseName());
        $vars = array_keys(get_class_vars(get_class($object)));
        unset($vars[array_search('id', $vars)]);
        $keys = implode(' ,', $vars);
        $properties = [];
        foreach ($vars as $prop) {
            $string = "'" . $object->$prop . "'";
            $properties[] = $string;
        }
        $values = implode(' ,', $properties);
        $query = $db->prepare('INSERT INTO ' . $this->getStringTableName() . " ($keys) VALUES($values)");
        $query->execute();
    }

    public function update($object)
    {
        $db = $this->getDbManager()->getDatabase($this->getDatabaseName());
        $cond = 'id = :id';
        $vars = array_keys(get_class_vars(get_class($object)));
        unset($vars[array_search('id', $vars)]);
        $values = '';
        foreach ($vars as $var) {
            $values .= $var . "='" . $object->$var . "', ";
        }
        $values = substr($values, 0, -2);
        $tableClassName = $this->getStringTableName();
        $query = $db->prepare("UPDATE $tableClassName SET $values WHERE $cond");
        $query->execute([
            'id' => $object->id
        ]);
    }

    public function delete($object)
    {
        $db = $this->getDbManager()->getDatabase($this->getDatabaseName());
        $cond = 'id = :id';
        $query = $db->prepare('DELETE FROM ' . $this->getStringTableName() . ' WHERE ' . $cond);
        $query->execute([
            'id' => $object->id
        ]);
    }

    public function deleteCustCond($cond)
    {
        $db = $this->getDbManager()->getDatabase($this->getDatabaseName());
        $db->query('DELETE FROM ' . $this->getStringTableName() . ' WHERE ' . $cond);
    }
}
