<?php

namespace bemang\Database\Table;

use bemang\Database\Manager\DBManager;

class EntityGenerator
{
    public static function generate(string $tableName, string $databaseName, DBManager $manager): string
    {
        if (class_exists('\bemang\\Database\\Table\Entities\\' . $tableName)) {
            return '\bemang\\Database\\Table\Entities\\' . $tableName;
        }

        $fields = EntityGenerator::getFieldsName($tableName, $databaseName, $manager);

        if (empty($fields)) {
            throw new \bemang\Database\Exceptions\TableException('La table n\'existe pas dans la base de donnée');
        }

        $parts = ['namespace bemang\Database\Table\Entities;'];
        $parts[] = 'class ' . $tableName . ' extends \bemang\Database\Table\Entity{';
        $parts[] = self::generateAttributes($fields);
        $parts[] = self::generateMethods($fields);
        $parts[] = '}';
        eval(join('', $parts));
        return '\bemang\\Database\\Table\Entities\\' . $tableName;
    }

    protected static function getFieldsName(string $tableName, string $databaseName, DBManager $manager): array
    {
        $query = 'select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME =  :table';
        $query = $manager->sql($query, $databaseName, ['table' => $tableName]);
        $query->setFetchMode(\PDO::FETCH_OBJ);
        $queryResults = $query->fetchAll();
        $results = [];
        foreach ($queryResults as $object) {
            $results[] = $object->COLUMN_NAME;
        }
        return $results;
    }

    protected static function generateAttributes(array $fields): string
    {
        $attributes = [];
        foreach ($fields as $field) {
            if ($field != 'id') {
                $attributes[] = 'protected $' . $field . ';';
            }
        }
        return join('', $attributes);
    }

    protected static function generateMethods(array $fieldsName): string
    {
        $methods = [];
        foreach ($fieldsName as $field) {
            if ($field != 'id') {
                $methods[] = 'public function get' . ucfirst(strtolower($field)) .
                '(){return $this->' . $field . ';}';
                $methods[] = 'public function set' . ucfirst(strtolower($field)) .
                '($parameter){$this->' . $field . ' = $parameter;}';
            }
        }
        return join('', $methods);
    }
}
