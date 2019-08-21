<?php

namespace Test;

use bemang\Database\QueryBuilder;

class QueryBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testSimpleSelectQuery()
    {
        $query = (new QueryBuilder())->from('post')->select('name', 'num');
        $this->assertEquals((string)$query, 'SELECT name, num FROM post');

        $query = (new QueryBuilder())->from('post')->select('*');
        $this->assertEquals((string)$query, 'SELECT * FROM post');
    }

    public function testSelectQueryWithWhere()
    {
        $query = (new QueryBuilder())->from('user')->select('*')->where(
            'mail = :mail@mail.com OR mail = :mail@gmail.com', 
            'id = :3'
        );
        $this->assertEquals((string)$query, 'SELECT * FROM user WHERE (mail = :mail@mail.com OR mail = :mail@gmail.com) AND (id = :3)');
    }
}