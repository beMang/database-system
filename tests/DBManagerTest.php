<?php

namespace Test;

use bemang\Config;
use bemang\Database\Query;
use bemang\Database\DBManager;

class DatabaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Préparation de la bdd pour les tests
     *
     * @return void
     */
    public static function setUpBeforeClass() :void
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

    /**
     * Action après les tests
     *
     * @return void
     */
    public static function tearDownAfterClass() :void
    {
        $pdo = new \PDO('mysql:host=localhost', 'root', '', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
        $pdo->prepare('DROP DATABASE `test`')->execute();
    }

    public function testWithEmptyConfig()
    {
        $this->expectExceptionMessage('Le manager doit d\'abord être configuré avant d\'être utilisé');
        $manager = DBManager::getInstance();
    }

    public function testInvalidConfig()
    {
        $this->expectExceptionMessage('Le champ databases n\'existe pas dans cette configuration');
        $config = new Config();
        $this->assertFalse(DBManager::config($config));
    }

    public function testValidConfig()
    {
        $config = new Config([
            'databases' => [
                'test' => ['mysql:host=localhost;dbname=test', 'root', '']
            ]
        ]);
        $this->assertTrue(DBManager::config($config));
    }

    public function testAddDatabase()
    {
        $manager = DBManager::getInstance();
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
}
