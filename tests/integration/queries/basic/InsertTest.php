<?php

namespace integration\queries\basic;

use data\ConnectionProvider;
use PHPUnit\Framework\TestCase;

final class InsertTest extends TestCase
{
    public function testInsert(): void
    {
        $queries = ConnectionProvider::getStormQueries();

        $id = $queries->insert('insert_test', ['name' => 'first']);

        $this->assertEquals(1, $id);
    }

    public function testInsertMany(): void
    {
        $queries = ConnectionProvider::getStormQueries();

        $queries->insertMany('insert_test', [
            ['name' => 'name1'],
            ['name' => 'name2'],
            ['name' => 'name3']
        ]);

        $this->assertEquals(4, $queries->count('insert_test'));
    }
}