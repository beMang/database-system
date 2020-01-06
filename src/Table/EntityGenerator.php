<?php

namespace bemang\Database\Table;

use bemang\Database\Manager\DBManager;

class EntityGenerator
{
    public static function generate(string $tableName, string $databaseName, DBManager $manager): void
    {
        $fields = EntityGenerator::getFieldsName($tableName, $databaseName, $manager);

        $parts = ['namespace bemang\Database\Table;'];
        $parts[] = 'class ' . $tableName . ' extends Entity{';
        $parts[] = self::generateAttributes($fields);
        $parts[] = self::generateMethods($fields);
        $parts[] = '}';

        $code = join('', $parts);
        var_dump($code);
        //eval($code);
    }

    protected static function getFieldsName(string $tableName, string $databaseName, DBManager $manager): array
    {
        $query = 'select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME =  :table'; //TODO : vérifier que la table existe
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
        $string = '';
        return $string;
    }
}
