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
            return $this->buildSelect();
        } else {
            return 'error';
        }
    }

    public function select(string ...$fields) :self
    {
        $this->selects = $fields;
        return $this;
    }

    public function from(string $table, string $alias = null) :self
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

    }

    public function update(array $infos) :self
    {

    }

    public function delete()
    {

    }

    protected function buildSelect() :string
    {
        $parts = ['SELECT'];
        $parts[] = join(', ', $this->selects);
        $parts[] = $this->buildFrom();
        if ($this->conditions) {
            $parts[] = 'WHERE';
            $parts[] = '(' . join(') AND (', $this->conditions) . ')';
        }
        return join(' ', $parts);
    }

    protected function buildFrom() :string
    {
        $fromParts;
        foreach ($this->table as $key => $table) {
            if (is_string($key)) {
                $fromParts = '' . $table . ' AS ' . $key . '';
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
}
