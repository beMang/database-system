<?php

namespace Test;

use bemang\Config;
use bemang\Database\Query;
use bemang\Database\DBManager;

class DatabaseTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass()
    {
        require(dirname(__FILE__) . '/../vendor/autoload.php');
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
        $pdo->prepare('CREATE TABLE test2 (
                        id int NOT NULL AUTO_INCREMENT,
                        value int,
                        content varchar(255),
                        user_id varchar(200),
                        PRIMARY KEY (id)
                    )')->execute();
    }

    public static function tearDownAfterClass()
    {
        $pdo = new \PDO('mysql:host=localhost', 'root', '', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
        $pdo->prepare('DROP DATABASE `test`')->execute();
    }

    public function testWithEmptyConfig()
    {
        $this->expectExceptionMessage('Lors de la première récupération d\'instance, $config doit être défini');
        $manager = DBManager::getInstance();
    }

    public function testInitAndAddDatabase()
    {
        $config = new Config();
        $manager = DBManager::getInstance($config);
        $this->assertTrue($manager->addDatabase('base', 'mysql:host=localhost;dbname=test', 'root', ''));
    }

    public function testAddAlReadyExistDataBase()
    {
        $manager = DBManager::getInstance();
        $this->expectExceptionMessage('La base de donnée existe déjà.');
        $manager->addDatabase('base', 'mysql:host=localhost;dbname=test', 'root', '');
    }

    public function testInvalidStringOnAddDatabase()
    {
        $manager = DBManager::getInstance();
        $this->expectExceptionMessage('L\'identifiant de la db doit être une chaine de caractères non-vides');
        $manager->addDatabase(5757, 'mysql:host=localhost;dbname=test', 'root', '');
        $manager->addDatabase('', 'mysql:host=localhost;dbname=test', 'root', '');
    }

    public function testGetInvalidDatabase()
    {
        $this->expectExceptionMessage('La base de donnée est inexistante');
        $manager = DBManager::getInstance();
        $manager->getDatabase(uniqid());
    }

    public function testGetInvalidStringDatabase()
    {
        $this->expectExceptionMessage('L\'identifiant doit être une chaine de caractères');
        $manager = DBManager::getInstance();
        $manager->getDatabase(55454);
    }

    public function testDatabaseExist()
    {
        $manager = DBManager::getInstance();
        $this->assertTrue($manager->dataBaseExist('base'));
        $this->assertFalse($manager->dataBaseExist(uniqid()));
        $this->assertFalse($manager->dataBaseExist(545));
    }

    public function testInvalidTableName()
    {
        $tableName = uniqid();
        $dbName = uniqid();
        $this->expectExceptionMessage('La table ' . $dbName . '_' . $tableName . ' est inexistante');
        new Query($tableName, $dbName);
    }

    public function testEmptyInstance()
    {
        $query = new Query('user_test', 'base');
        $instance = $query->getEmptyInstance();
        $this->assertInstanceOf('base_user_test', $instance);
    }

    public function testInsert()
    {
        $query = new Query('user_test', 'base');
        $instance = $query->getEmptyInstance();
        $instance->name = 'Arnlold';
        $instance->surname = 'Pierre';
        $instance->pseudo = 'griuKu';
        $query->insert($instance);
        $instance->id = 1;
        $this->assertEquals($query->getLast(), $instance);
    }

    public function testUpdate()
    {
        $query = new Query('user_test', 'base');
        $instance = $query->getLast();
        $instance->name = 'Viktor';
        $instance->surname = 'Vladivostok';
        $instance->pseudo = uniqid();
        $query->update($instance);
        $this->assertEquals($query->getLast(), $instance);
    }

    public function testGetWithCondition()
    {
        $query = new Query('user_test', 'base');
        $result = $query->getWithCondition('name', 'Viktor');
        $this->assertEquals([$query->getLast()], $result);
    }

    public function testGetAllAndSqlDbManager()
    {
        $query = new Query('user_test', 'base');
        $instance = $query->getEmptyInstance();
        $instance->pseudo = uniqid();
        $instance->surname = uniqid();
        $instance->name = uniqid();
        $query->insert($instance);
        $results = $query->getAll();
        $manager = DBManager::getInstance();
        $realInBdd = $manager->sql('SELECT * FROM user_test', false, 'base');
        $this->assertEquals($realInBdd[0]->name, $results[0]->name);
        $this->expectExceptionMessage('Error sql');
        $manager->sql('SELECT * FROM' . uniqid());
    }

    public function testDelete()
    {
        $query = new Query('user_test', 'base');
        $toDelete = $query->getLast();
        $id = $toDelete->id;
        $query->delete($toDelete);
        $this->assertEmpty($query->getWithCondition('id', $id));
    }

    public function testDeleteCust()
    {
        $query = new Query('user_test', 'base');
        $toDelete = $query->getLast();
        $id = $toDelete->id;
        $query->deleteCustCond('id = ' . $id);
        $this->assertEmpty($query->getWithCondition('id', $id));
    }

    public function testInnerJoin(Type $var = null)
    {
        $query = new Query('user_test', 'base');
        $this->assertEmpty($query->getWithInnerJoin('test2', 'user_test.name = test2.value', 'user_test.id = 4'));
    }
}
