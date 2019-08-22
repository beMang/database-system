<?php

namespace Test;

use bemang\Database\QueryBuilder;

class QueryBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testSimpleSelectQuery()
    {
        $query = (new QueryBuilder())->from('post')->select('name', 'num');
        $this->assertEquals('SELECT name, num FROM post', (string)$query, );

        $query = (new QueryBuilder())->from('post')->select('*');
        $this->assertEquals('SELECT * FROM post', (string)$query);
    }

    public function testSelectQueryWithWhere()
    {
        $query = (new QueryBuilder())->from('user')->select('*')->where(
            'mail = :mail@mail.com OR mail = :mail@gmail.com',
            'id = :3'
        );
        $this->assertEquals('SELECT * FROM user WHERE (mail = :mail@mail.com OR mail = :mail@gmail.com) AND (id = :3)', (string)$query);

        //Alias test
        $query = (new QueryBuilder())->from('user', 'u')->select('*')->where(
            'mail = :mail@mail.com OR mail = :mail@gmail.com',
            'id = :3'
        );
        $this->assertEquals('SELECT * FROM user AS u WHERE (mail = :mail@mail.com OR mail = :mail@gmail.com) AND (id = :3)', (string)$query);
    }

    public function testCount()
    {
        $query = (new QueryBuilder())->count('pseudo')->from('user');
        $this->assertEquals('SELECT COUNT(pseudo) FROM user', (string)$query);
    }
}
