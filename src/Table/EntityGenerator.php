<?php

namespace bemang\Database\Table;

use bemang\Database\Manager\DBManager;

class EntityGenerator
{
    public static function generate(string $tableName, string $databaseName, DBManager $manager): string
    {
        $fields = EntityGenerator::getFieldsName($tableName, $databaseName, $manager);

        if (empty($fields)) {
            throw new \bemang\Database\Exceptions\TableException('La table n\'existe pas dans la base de donnée');
        }

        if (class_exists('\bemang\\Database\\Table\Entities\\' . $tableName)) {
            throw new \bemang\Database\Exceptions\TableException(
                'La classe de cette table a déjà été générée'
            );
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
        $queryResults = $manager->sql($query, $databaseName, ['table' => $tableName]);
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
            $attributes[] = 'protected $' . $field . ';';
        }
        return join('', $attributes);
    }

    protected static function generateMethods(array $fieldsName): string
    {
        $methods = [];
        foreach ($fieldsName as $field) {
            $methods[] = 'public function get' . ucfirst(strtolower($field)) .
            '(){return $this->' . $field . ';}';
        }
        return join('', $methods);
    }
}
