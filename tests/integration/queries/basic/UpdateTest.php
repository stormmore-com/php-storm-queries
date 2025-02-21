<?php

namespace integration\queries\basic;

use data\ConnectionProvider;
use PHPUnit\Framework\TestCase;

final class UpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $queries = ConnectionProvider::getStormQueries();

        $queries->update('update_test', ['id' => 1], ['name' => 'first-up']);
        $item = $queries->find('update_test', ['id' => 1]);

        $this->assertEquals('first-up', $item->name);
    }

    public function testUpdateSqlWhere(): void
    {
        $queries = ConnectionProvider::getStormQueries();

        $queries->update('update_test', 'id = ?', 2, ['name' => 'second-up']);
        $item = $queries->find('update_test', ['id' => 2]);

        $this->assertEquals('second-up', $item->name);
    }

    public function testUpdateQueryExpression(): void
    {
        $queries = ConnectionProvider::getStormQueries();

        $queries->updateQuery('update_test')
            ->where('id', 3)
            ->set('name = ?', '3')
            ->execute();
        $item = $queries->find('update_test', ['id' => 3]);

        $this->assertEquals('3', $item->name);
    }

    public function testUpdateBySetExpression(): void
    {
        $queries = ConnectionProvider::getStormQueries();

        $queries->updateQuery('products')
            ->where('product_id', 10)
            ->set('price = price + 4')
            ->execute();
        $item = $queries->find('products', ['product_id' => 10]);

        $this->assertEquals(35, $item->price);
    }
}