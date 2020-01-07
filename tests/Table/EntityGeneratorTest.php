<?php

namespace tests\Table;

use PHPUnit\Framework\TestCase;
use bemang\Database\Manager\DBManager;
use bemang\Database\Table\EntityGenerator;

class EntityGeneratorTest extends TestCase
{
    protected $attributes = ['id','name','surname', 'pseudo'];
    protected static $config = [
            'databases' => [
                'test' => ['mysql:host=localhost;dbname=test', 'root', '']
            ]
        ];

    public static function setUpBeforeClass(): void
    {
        require(dirname(__FILE__) . '/../../vendor/autoload.php');
        $pdo = new \PDO('mysql:host=localhost', 'root', '', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
        $pdo->prepare('CREATE DATABASE test DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci')->execute();
        unset($pdo);
        $pdo = new \PDO('mysql:host=localhost;dbname=test', 'root', '', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
        $pdo->prepare('CREATE TABLE user_test (
                        id int NOT NULL AUTO_INCREMENT,
                        name varchar(255),
                        surname varchar(255),
                        pseudo varchar(48),
                        PRIMARY KEY (id)
                    )')->execute();
        unset($pdo);
        DBManager::config(new \bemang\Config(self::$config));
        $manager = DBManager::getInstance();
        if (!$manager->dataBaseExist('tests')) {
            $manager->addDatabase('tests', 'mysql:host=localhost;dbname=test', 'root', '');
        }
    }

    /**
     * Action après les tests
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        $pdo = new \PDO('mysql:host=localhost', 'root', '', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
        $pdo->prepare('DROP DATABASE `test`')->execute();
        try {
            DBManager::getInstance()->reset();
        } catch (\Exception $e) {
            //don't mind
        }
    }

    /** @test */
    public function testGenerateClass()
    {
        $className = EntityGenerator::generate('user_test', 'tests', DBManager::getInstance());
        $obj = new $className();
        $good = true;
        foreach ($this->attributes as $attribute) {
            $this->assertClassHasAttribute($attribute, $className);
            if (!\method_exists($obj, 'get' . ucfirst(strtolower($attribute)))) {
                $good = false;
            }
        }
        $this->assertTrue($good);
    }

    /** @test */
    public function testGenerateClassWithInexistantTable()
    {
        $this->expectExceptionMessage('La table n\'existe pas dans la base de donnée');
        $className = EntityGenerator::generate('lambda', 'tests', DBManager::getInstance());
    }

    public function testAlreadyGeneratedClass()
    {
        $this->expectExceptionMessage('La classe de cette table a déjà été générée');
        EntityGenerator::generate('user_test', 'tests', DBManager::getInstance());
    }
}
