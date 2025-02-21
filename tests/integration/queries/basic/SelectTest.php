<?php

namespace integration\queries\basic;

use data\ConnectionProvider;
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{
    public function testFindOne(): void
    {
        $queries = ConnectionProvider::getStormQueries();

        $item = $queries
            ->select('customers')
            ->where('city', 'London')
            ->find();
        $vars = get_object_vars($item);

        $this->assertNotNull($item);
        $this->assertCount(7, $vars);
    }

    public function testFindOneWithColumns(): void
    {
        $queries = ConnectionProvider::getStormQueries();

        $item = $queries
            ->select('customers', 'customer_id', 'customer_name')
            ->where('city', 'London')
            ->find();
        $vars = get_object_vars($item);

        $this->assertNotNull($item);
        $this->assertCount(2, $vars);
    }

    public function testFindAll(): void
    {
        $queries = ConnectionProvider::getStormQueries();

        $items = $queries
            ->select('customers')
            ->where('city', 'London')
            ->findAll();

        $this->assertCount(6, $items);
    }
}