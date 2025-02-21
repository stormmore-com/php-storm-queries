<?php

namespace integration\queries\basic;

use data\ConnectionProvider;
use PHPUnit\Framework\TestCase;

final class DeleteTest extends TestCase
{
    public function testDelete(): void
    {
        $queries = ConnectionProvider::getStormQueries();

        $queries->delete('delete_test', ['id' => 1]);
        $count = $queries->count('delete_test');

        $this->assertEquals(2, $count);
    }

    public function testDeleteSqlWhere(): void
    {
        $queries = ConnectionProvider::getStormQueries();

        $queries->delete('delete_test', 'id = ?', 2);
        $count = $queries->count('delete_test');

        $this->assertEquals(1, $count);
    }

    public function testDeleteQuery(): void
    {
        $queries = ConnectionProvider::getStormQueries();

        $queries->deleteQuery('delete_test')->where('id', 3)->execute();
        $count = $queries->count('delete_test');

        $this->assertEquals(0, $count);
    }
}