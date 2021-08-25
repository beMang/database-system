<?php

namespace tests\Builder;

use bemang\Database\Builder\Query;

class QueryBuilderTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        require(dirname(__FILE__) . '/../../vendor/autoload.php');
    }

    public function testSelectQuery()
    {
        $query = (new Query())->setTable('post')->select('name', 'num');
        $this->assertEquals('SELECT name, num FROM post', (string)$query);

        $query = (new Query())->setTable('post')->select('*');
        $this->assertEquals('SELECT * FROM post', (string)$query);

        //With order
        $query = (new Query())->setTable('post')->select('*')->order('title', 'DESC');
        $this->assertEquals('SELECT * FROM post ORDER BY title DESC', (string)$query);

        $query = (new Query())->setTable('post')->select('*')->order('title', 'ASC');
        $this->assertEquals('SELECT * FROM post ORDER BY title ASC', (string)$query);

        $query = (new Query())->setTable('post')->select('*')->order('title');
        $this->assertEquals('SELECT * FROM post ORDER BY title ASC', (string)$query);

        //Conditions test
        $query = (new Query())->setTable('user')->select('*')->where(
            'mail = :mail@mail.com OR mail = :mail@gmail.com',
            'id = :3'
        );
        $this->assertEquals(
            'SELECT * FROM user WHERE (mail = :mail@mail.com OR mail = :mail@gmail.com) AND (id = :3)',
            (string)$query
        );

        //Alias test
        $query = (new Query())->setTable('user', 'u')->select('*')->where(
            'mail = :mail@mail.com OR mail = :mail@gmail.com',
            'id = :3'
        );
        $this->assertEquals(
            'SELECT * FROM user AS u WHERE (mail = :mail@mail.com OR mail = :mail@gmail.com) AND (id = :3)',
            (string)$query
        );

        //Limit test
        $query = (new Query())->setTable('user')->select('*')->limit(9, 0);
        $this->assertEquals('SELECT * FROM user LIMIT 9 OFFSET 0', (string)$query);

        $query = (new Query())->setTable('user')->select('*')->limit(100);
        $this->assertEquals('SELECT * FROM user LIMIT 100', (string)$query);

        $query = (new Query())->setTable('user')->select('*')->limit(50, 20);
        $this->assertEquals('SELECT * FROM user LIMIT 50 OFFSET 20', (string)$query);
    }

    public function testJoin()
    {
        $query = (new Query())->setTable('user')->select('*')
        ->join('jeux', 'INNER', 'jeux.user_id = user.id', 'jeux.nom = \'minercraft\'');
        $this->assertEquals(
            'SELECT * FROM user INNER JOIN jeux ON (jeux.user_id = user.id) AND (jeux.nom = \'minercraft\')',
            (string)$query
        );
        $query = (new Query())->setTable('nom')->select('nom.nom, jeux.nom_jeux')
        ->join('jeux', 'LEFT', 'jeux.id_proprio = nom.id');
        $this->assertEquals(
            'SELECT nom.nom, jeux.nom_jeux FROM nom LEFT JOIN jeux ON (jeux.id_proprio = nom.id)',
            (string)$query
        );
    }

    public function testCount()
    {
        $query = (new Query())->count('pseudo')->setTable('user');
        $this->assertEquals('SELECT COUNT(pseudo) FROM user', (string)$query);
    }

    public function testSimpleInsert()
    {
        $values = [
            'pseudo' => 'test',
            'mail' => 'test@example.com'
        ];
        $query = (new Query())->setTable('user')->insert($values);
        $this->assertEquals('INSERT INTO user (pseudo, mail) VALUES(:v1, :v2)', (string)$query);
        $this->assertEquals([
            ':v1' => 'test',
            ':v2' => 'test@example.com'
        ], $query->getValues());
    }

    public function testSimpleUpdate()
    {
        $query = (new Query())->update([
            'pseudo' => 'beMang',
            'mail' => 'mail@example.com'
        ])->setTable('user')->where('id = :id')->addValue('id', 5);
        $this->assertEquals('UPDATE user SET pseudo = :pseudo, mail = :mail WHERE (id = :id)', (string)$query);
        $this->assertEquals([
            'pseudo' => 'beMang',
            'mail' => 'mail@example.com',
            'id' => 5
        ], $query->getValues());
    }

    public function testSimpleDelete()
    {
        $query = (new Query())->delete('id = 9')->setTable('users');
        $this->assertEquals('DELETE FROM users WHERE (id = 9)', (string)$query);
    }
}
