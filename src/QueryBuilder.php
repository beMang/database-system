<?php

namespace bemang\Database;

class QueryBuilder
{
    protected $selects = [];

    protected $table;

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
    public function __toString()
    {
        if($this->selects) {
            $parts = ['SELECT'];
            $parts[] = join(', ', $this->selects);
            $parts[] = 'FROM';
            $parts[] = $this->table;
            if($this->conditions) {
                $parts[] = 'WHERE';
                $parts[] = '(' . join(') AND (', $this->conditions) . ')';
            }
            return join(' ', $parts);
        }
    }

    public function select(string ...$fields) :self
    {
        $this->selects = $fields;
        return $this;
    }

    public function from(string $table) :self //TODO : ajouter les alias
    {
        $this->table = $table;
        return $this;
    }

    public function where(string ...$condition) :self
    {
        $this->conditions = $condition;
        return $this;
    }
}
