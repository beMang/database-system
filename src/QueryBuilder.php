<?php

namespace bemang\Database;

/**
 * FEATURE A INTEGRER
 * 
 *  JOINTURE
 * 
 *  UPDATE
 * 
 *  INSERT
 * 
 *  DELETE
 */

class QueryBuilder
{
    protected $selects = [];

    protected $table = [];

    protected $conditions = [];

    protected $group = [];

    protected $order;

    protected $limit;

    protected $insert = [];

    protected $update = [];

    protected $values = []; //Valeurs de sortie pour update et insert

    public function __construct()
    {
        return $this;
    }

    /**
     * Méthode pour récupérer la requête sous forme d'un chaîne de caractères
     *
     * @return String
     */
    public function __toString() :string
    {
        if ($this->selects) {
            $result = $this->buildSelect();
        } elseif ($this->insert) {
            $result = $this->buildInsert();
        } elseif($this->update) {
            $result = $this->buildUpdate();
        } else {
            $result = 'error';
        }
        return trim($result);
    }

    public function select(string ...$fields) :self
    {
        $this->selects = $fields;
        return $this;
    }

    public function setTable(string $table, string $alias = null) :self
    {
        if ($alias) {
            $this->table[$alias] = $table;
        } else {
            $this->table[] = $table;
        }
        return $this;
    }

    public function where(string ...$condition) :self
    {
        $this->conditions = $condition;
        return $this;
    }

    public function count(string $column) :self
    {
        $this->selects = ['COUNT(' . $column . ')'];
        return $this;
    }

    public function insert(array $infos) :self
    {
        $this->insert = $infos;
        return $this;
    }

    public function getValues() :array
    {
        return $this->values;
    }

    public function update(array $infos) :self
    {
        $this->update = $infos;
        return $this;
    }

    public function delete()
    {
        return $this;
    }

    protected function buildSelect() :string
    {
        $parts = ['SELECT'];
        $parts[] = join(', ', $this->selects);
        $parts[] = $this->buildFrom();
        $parts[] = $this->buildConditions();
        return join(' ', $parts);
    }

    protected function buildFrom() :string
    {
        $fromParts;
        foreach ($this->table as $key => $table) {
            if (is_string($key)) {
                $fromParts = $table . ' AS ' . $key;
            } elseif (is_numeric($key)) {
                $fromParts = $table;
            } else {
                throw new Exception('Alias indadapté (les valeurs numériques sont déconseillées)');
            }
        }
        return join(' ', [
            'FROM',
            $fromParts
        ]);
    }

    protected function buildInsert() :string
    {
        $parts = ['INSERT INTO'];
        $parts[] = $this->table[0];
        $keys = [];
        $values = [];
        $requestValue = [];
        $counter = 0;
        foreach ($this->insert as $key => $value) {
            $counter ++;
            $keys[] = $key;
            $values[] = $value;
            $requestValue[] = ':v' . $counter;
        }
        $parts[] = '('. join(', ', $keys) . ')';
        $parts[] = 'VALUES(' . join(', ', $requestValue) . ')';
        $this->setValues($requestValue, $values);
        return join(' ', $parts);
    }

    protected function buildUpdate() :string
    {
        $parts = ['UPDATE'];
        $parts[] = $this->table[0];
        $parts[] = 'SET';
        $parts[] = $this->buildUpdateValues($this->update);
        $parts[] = $this->buildConditions();
        return join(' ', $parts);
    }

    protected function buildUpdateValues(array $values) :string
    {
        $parts = [];
        $counter = 0;
        foreach ($values as $key => $value) {
            $counter ++;
            $parts[] = $key . ' = :v' . $counter;
        }
        return join(', ', $parts);
    }

    protected function buildConditions() :string
    {
        if ($this->conditions) {
           return 'WHERE (' . join(') AND (', $this->conditions) . ')';
        } else {
            return '';
        }
    }

    protected function setValues(array $requestValue, array $values) :bool
    {
        if (sizeof($requestValue) === sizeof($values)) {
            $result = [];
            foreach ($requestValue as $key => $value) {
                $result[$value] = $values[$key];
            }
            $this->values = $result;
            return true;
        } else {
            return false;
        }
    }
}
