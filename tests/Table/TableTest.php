<?php

namespace tests\Manager\Table;

use bemang\Database\Exceptions\TableException;
use bemang\Database\Table;
use bemang\Database\Manager\DBManager;

class TableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Config utilisée pour le DBManager
     *
     * @var array
     */
    protected static $config = [
            'databases' => [
                'test' => ['mysql:host=localhost;dbname=test', 'root', '']
            ]
        ];

    /**
     * Préparation de la bdd pour les tests et du manager
     *
     * @return void
     */
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
                        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                        name varchar(255),
                        surname varchar(255),
                        pseudo varchar(48),
                        PRIMARY KEY (id)
                    )')->execute();
        $pdo->prepare('CREATE TABLE test2 (
                        id int NOT NULL AUTO_INCREMENT,
                        value int,
                        content varchar(255),
                        user_id varchar(200),
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


    public function testInsertTable()
    {
        $table = new Table('user_test', 'tests');
        $entity = $table->getNewEntity();
        $table->insert($entity);
        $entity->setId(1);
        $inDB = DBManager::getInstance()->sql('SELECT * FROM user_test WHERE id = 1', 'tests')
        ->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals($entity->getAttribuesAsArray(), $inDB);
        $table->insert($entity);
    }

    public function testUpdateTable()
    {
        $table = new Table('user_test', 'tests');
        $entity = $table->getNewEntity();
        $entity->setId(1);
        $entity->setName('Adrien');
        $table->update($entity);
        $inDB = DBManager::getInstance()->sql('SELECT name FROM user_test WHERE id = 1', 'tests')
        ->fetch(\PDO::FETCH_BOTH);
        $this->assertEquals('Adrien', $inDB['name']);
    }

    public function testFetch()
    {
        $table = new Table('user_test', 'tests');
        $entity = $table->fetch(1);
        $query = DBManager::getInstance()->sql('SELECT * FROM user_test WHERE id = 1', 'tests');
        $query->setFetchMode(\PDO::FETCH_CLASS, $table->getEntityClassName());
        $inDb = $query->fetch();
        $this->assertEquals($inDb, $entity);

        $entity = $table->getNewEntity();
        $entity->setId(1);
        $entity = $table->fetch($entity);
        $this->assertEquals($inDb, $entity);
    }

    public function testFetchException()
    {
        $table = new Table('user_test', 'tests');
        $this->assertFalse($table->fetch(intval(uniqid())));
    }

    public function testFetchAll()
    {
        $table = new Table('user_test', 'tests');
        $entities = $table->fetchAll();
        $query = DBManager::getInstance()->sql('SELECT * FROM user_test', 'tests');
        $inDb = $query->fetchAll(\PDO::FETCH_CLASS, $table->getEntityClassName());
        $this->assertEquals($inDb, $entities);
    }

    public function testDelete()
    {
        $table = new Table('user_test', 'tests');
        $entity = $table->getNewEntity();
        $entity->setId(1);
        $table->delete($entity);
        $inDB = DBManager::getInstance()->sql('SELECT * FROM user_test WHERE id = 1', 'tests')
        ->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(null, $inDB);

        $table->delete(2);
        $inDB = DBManager::getInstance()->sql('SELECT * FROM user_test WHERE id = 2', 'tests')
        ->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals(null, $inDB);
    }
}
