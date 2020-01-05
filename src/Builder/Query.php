<?php

namespace bemang\Database\Builder;

use bemang\Database\Exceptions\QueryBuilderException;

class Query
{
    protected $selects = [];

    protected $table = [];

    protected $conditions = [];

    protected $order = [
        'by' => null,
        'order' => 'ASC'
    ];

    protected $join = [
        'type' => null,
        'table' => null,
        'on' => null
    ];

    protected $limit;
    protected $offset;

    protected $insert = [];

    protected $update = [];

    protected $delete = [];

    protected $values = []; //Valeurs de sortie pour update et insert

    public function __construct()
    {
        return $this;
    }

    /**
     * Récupère sous forme de string
     *
     * @return String
     */
    public function __toString(): string
    {
        $this->toSql();
    }

    /**
     * Méthode pour récupérer la requête sous forme d'un chaîne de caractères
     *
     * @return String
     */
    public function toSql():string
    {
        if ($this->selects) {
            $result = $this->buildSelect();
        } elseif ($this->insert) {
            $result = $this->buildInsert();
        } elseif ($this->update) {
            $result = $this->buildUpdate();
        } elseif ($this->delete) {
            $result = $this->buildDelete();
        } else {
            $result = 'error';
        }
        return $this->clearString($result);
    }

    /**
     * Permet de nettoyer les espaces inutiles
     */
    protected function clearString(string $string): string
    {
        $string = preg_replace('/\s+/', ' ', $string);
        $string = trim($string);
        return $string;
    }

    public function select(string ...$fields): self
    {
        $this->selects = $fields;
        return $this;
    }

    public function setTable(string $table, string $alias = null): self
    {
        if ($alias) {
            $this->table[$alias] = $table;
        } else {
            $this->table[] = $table;
        }
        return $this;
    }

    public function where(string ...$condition): self
    {
        $this->conditions = $condition;
        return $this;
    }

    public function count(string $column): self
    {
        $this->selects = ['COUNT(' . $column . ')'];
        return $this;
    }

    public function join(string $type = 'INNER', string $table, string ...$conditions): self
    {
        $this->join['type'] = $type;
        $this->join['table'] = $table;
        $this->join['on'] = $conditions;
        return $this;
    }

    public function order(string $field, string $order = 'ASC'): self
    {
        $this->order['by'] = $field;
        $this->order['order'] = $order;
        return $this;
    }

    public function limit(int $limit, int $offset = null): self
    {
        $this->limit = $limit;
        $this->offset = is_null($offset) ? null : $offset;
        return $this;
    }

    public function insert(array $infos): self
    {
        $this->insert = $infos;
        return $this;
    }

    public function update(array $infos): self
    {
        $this->update = $infos;
        return $this;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function delete(string ...$conditions): self
    {
        $this->delete = true;
        $this->conditions = $conditions;
        return $this;
    }

    protected function buildSelect(): string
    {
        $parts = ['SELECT'];
        $parts[] = join(', ', $this->selects);
        $parts[] = $this->buildFrom();
        $parts[] = $this->buildJoin();
        $parts[] = $this->buildConditions();
        if ($this->order['by']) {
            $parts[] = $this->buildOrder();
        }
        if ($this->limit) {
            $parts[] = $this->buildLimit();
        }
        return join(' ', $parts);
    }

    protected function buildFrom(): string
    {
        $fromParts;
        foreach ($this->table as $key => $table) {
            if (is_string($key)) {
                $fromParts = $table . ' AS ' . $key;
            } elseif (is_numeric($key)) {
                $fromParts = $table;
            } else {
                throw new QueryBuilderException('Alias indadapté (les valeurs numériques sont déconseillées)');
            }
        }
        return join(' ', [
            'FROM',
            $fromParts
        ]);
    }

    protected function buildJoin(): string
    {
        if ($this->join['type']) {
            $parts = [$this->join['type'] . ' JOIN ' . $this->join['table'] . ' ON'];
            $parts[] = '(' . join(') AND (', $this->join['on']) . ')';
            return join(' ', $parts);
        } else {
            return '';
        }
    }

    protected function buildOrder(): string
    {
        return 'ORDER BY ' . $this->order['by'] . ' ' . $this->order['order'];
    }

    protected function buildLimit(): string
    {
        $offset = is_null($this->offset) ? null : ' OFFSET ' . $this->offset;
        return 'LIMIT ' . $this->limit . $offset;
    }

    protected function buildInsert(): string
    {
        $parts = ['INSERT INTO'];
        $parts[] = $this->table[0];
        $keys = [];
        $values = [];
        $requestValue = [];
        $counter = 0;
        foreach ($this->insert as $key => $value) {
            $counter++;
            $keys[] = $key;
            $values[] = $value;
            $requestValue[] = ':v' . $counter;
        }
        $parts[] = '(' . join(', ', $keys) . ')';
        $parts[] = 'VALUES(' . join(', ', $requestValue) . ')';
        $this->setValues($requestValue, $values);
        return join(' ', $parts);
    }

    protected function buildUpdate(): string
    {
        $parts = ['UPDATE'];
        $parts[] = $this->table[0];
        $parts[] = 'SET';
        $parts[] = $this->buildUpdateValues($this->update);
        $parts[] = $this->buildConditions();
        return join(' ', $parts);
    }

    protected function buildUpdateValues(array $values): string
    {
        $parts = [];
        $counter = 0;
        foreach ($values as $key => $value) {
            $counter++;
            $parts[] = $key . ' = :v' . $counter;
        }
        return join(', ', $parts);
    }

    protected function buildConditions(): string
    {
        if ($this->conditions) {
            return 'WHERE (' . join(') AND (', $this->conditions) . ')';
        } else {
            return '';
        }
    }

    protected function setValues(array $requestValue, array $values): bool
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

    protected function buildDelete(): string
    {
        $parts = ['DELETE'];
        $parts[] = $this->buildFrom();
        $parts[] = $this->buildConditions();
        return join(' ', $parts);
    }
}
