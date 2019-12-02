<?php

namespace Test;

use bemang\Database\QueryBuilder;

class QueryBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testSimpleSelectQuery()
    {
        $query = (new QueryBuilder())->setTable('post')->select('name', 'num');
        $this->assertEquals('SELECT name, num FROM post', (string)$query);

        $query = (new QueryBuilder())->setTable('post')->select('*');
        $this->assertEquals('SELECT * FROM post', (string)$query);
    }

    public function testSelectQueryWithWhere()
    {
        $query = (new QueryBuilder())->setTable('user')->select('*')->where(
            'mail = :mail@mail.com OR mail = :mail@gmail.com',
            'id = :3'
        );
        $this->assertEquals('SELECT * FROM user WHERE (mail = :mail@mail.com OR mail = :mail@gmail.com) AND (id = :3)', (string)$query);

        //Alias test
        $query = (new QueryBuilder())->setTable('user', 'u')->select('*')->where(
            'mail = :mail@mail.com OR mail = :mail@gmail.com',
            'id = :3'
        );
        $this->assertEquals('SELECT * FROM user AS u WHERE (mail = :mail@mail.com OR mail = :mail@gmail.com) AND (id = :3)', (string)$query);
    }

    public function testCount()
    {
        $query = (new QueryBuilder())->count('pseudo')->setTable('user');
        $this->assertEquals('SELECT COUNT(pseudo) FROM user', (string)$query);
    }

    public function testSimpleInsert()
    {
        $values = [
            'pseudo' => 'test',
            'mail' => 'test@example.com'
        ];
        $query = (new QueryBuilder())->setTable('user')->insert($values);
        $this->assertEquals('INSERT INTO user (pseudo, mail) VALUES(:v1, :v2)', (string)$query);
        $this->assertEquals([
            ':v1' => 'test',
            ':v2' => 'test@example.com'
        ], $query->getValues());
    }

    public function testSimpleUpdate()
    {
        $query = (new QueryBuilder())->update([
            'pseudo' => 'beMang',
            'mail' => 'mail@example.com'
        ])->setTable('user')->where('id = :3');
        $this->assertEquals('UPDATE user SET pseudo = :v1, mail = :v2 WHERE (id = :3)', (string)$query);
    }

    public function testSimpleDelete()
    {
        $query = (new QueryBuilder())->delete('id = 9')->setTable('users');
        $this->assertEquals('DELETE FROM users WHERE (id = 9)', (string)$query);
    }
}
