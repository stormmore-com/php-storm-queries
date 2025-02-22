# Storm PHP Queries

It's combination of query builder and ORM made to fetch data in most effective way.\
Intuitive, easy to learn, light yet powerful.
If you work with ORMs you will notice that you don't have to configure nothing but still you 
have possibility to build queries easily and retrieve in hierarchical way. 

- hierarchical models (biggest advantage of ORM without disadvantages of over-configuration etc.)
- query builder supporting assembling queries (support criteria-finder pattern for modern architectures)
- subqueries
- no configuration needed
- no need to describe db schema
- lightweight and tidy code (no additional dependencies)
- supports various databases (tested with PostgreSql, MySql, MariaDB, MSSQL, SQLite)
- fluent api
- no need to change your models (keep DDD aggregates clean)
- developer friendly (profiling queries, overview of generated sql)
- intuitive and flexible

<a name="index"></a>
* [1. Quick start](#quickstart)
* [2. Basic Queries](#basicqueries)
* [2. Select Query](#selectquery)
  * [Aggregation functions](#aggregatefunctions)
  * [Join/Left join](#leftjoin)
  * [Where](#where)
  * [OrderBy](#orderby)
  * [GroupBy](#groupby)
  * [Having](#having)
* [3. ORM](#orm)
* [4. SubQueries](#subquery)
* [5. Assembling query](#assemblingquery)
* [6. Insert Query](#insertquery)
* [7. Update Query](#updatequery)
* [8. Delete Query](#deletequery)
* [9. Profiling nad logging queries](#profiling)
* [10. Notice](#notice)
* [11. Tests](#tests)
* [12. Examples](#examples)
* [13. Author](#author)
* [14. License ](#license)

<a name="quickstart"></a>
## Quick start

### Installation

You will need composer or use Storm PHP Framework (Storm PHP Queries are part of it)

```php
composer require stormmore/queries
```

### Establishing connection
StormQueries uses PDO.

```php
use Stormmore\Queries\ConnectionFactory;
use Stormmore\Queries\StormQueries;

$connection = ConnectionFactory::createFromstring("dsn", "user", "password");
$queries = new StormQueries($connection);
```

<a name="basicqueries"></a>
## Basic queries
Finding product by id
```php
$product = $queries->find('products', 'id = ?', 5);
//or
$product = $queries->find('products', ['id' => 5]);
```
Finding all products in category
```php
$products = $queries->findAll('products', 'category_id = ?', 10);
//or
$products = $queries->findAll('products', ['category_id' => 10]);
```

Inserting product
```php
$query->insert('products', ['name' => 'Golden socks', 'price' => 777]);
```
Updating product
```php
$queries->update('products', 'id = ?', 5, ['name' => 'Renamed product']);
//or
$queries->update('products', ['id' => 5], ['name' => 'Renamed product']);
```

Deleting product
```php
$queries->delete('products', 'id = ?', 5);
//or
$queries->delete('products', ['id' => 5]);
```

Count products
```php
$count = $queries->count('products', 'in_sale', true);
or
$count = $queries->count('products', ['in_sale' => true]);
````

Check if product exists
```php
$exists = $queries->exist('products', 'id = ?', 5);
//or
$exists = $queries->exist('products', ['id' => 5]);
```

Mapping records to user class
```php
$product = $queries->find('products', 'id = ?', 5, Map::select([
    'product_id' => 'id', 
    'product_name' => 'name'
], UserProduct::class));
//or
$product = $queries->find('products', ['id' => 5], Map::select([
  'product_id' => 'id', 
  'product_name' => 'name'
], UserProduct::class));
```

### Full working example

```php
use Stormmore\Queries\StormQueries;
use Stormmore\Queries\Mapper\Map;
use Stormmore\Queries\ConnectionFactory;

$connection = ConnectionFactory::createFromString(
    "mysql:host=localhost;port=7801;dbname=storm_test", "mysql", "mysql")
$queries = new StormQueries($connection);

$queries
  ->select('customers c',  Map::select([
      'customer_id' => 'id',
      'customer_name' => 'name'
  ]))
  ->leftJoin('orders o', 'o.customer_id = c.customer_id', Map::many("orders", [
          'order_id' => 'id'
  ]))
  ->leftJoin('shippers sh', 'sh.shipper_id = o.shipper_id', Map::one('shipper', [
          'shipper_id' => 'id',
          'shipper_name' => 'name'
  ]))
  ->leftJoin('order_details od', 'od.order_id = o.order_id', Map::many('details', [
      'order_detail_id' => 'id',
      'quantity' => 'quantity'
  ]))
  ->leftJoin('products p', 'p.product_id = od.product_id', Map::one('product', [
      'product_id' => 'id',
      'product_name' => 'name',
      'price' => 'price'
  ]))
  ->findAll();
     
foreach($customers as $customer) {
    print_customer($customer);
    foreach($customer->orders as $order) {
      print_order($order);  
      foreach($order->details as $detail) {
        print_detail($detail);
      }    
    }
}                   
```

<a name="selectquery"></a>
## Select Query

Build select query with `select` method
Build it with fluent API
```php
$queries
    ->select("table", "column1", "column2", "column3")
    ->where('id', 2);
    ->find();
```

StormQueries is made to handle assembling queries step by step in various scenarios so every time you invoke methods like\
`select`
`join`
`leftJoin`
`where`
`orWhere`
`having`
`orHaving`
`orderBy`
`orderByAsc`
`orderByDesc`\
they add parameters instead replacing them to final query

```php
$queries->select('columnA')->select('columnB')->select('columnC');
```
builds `SELECT columnA, columnB, columnC`

<a name="aggregatefunctions"></a>
### Aggregation functions 

 ```php
$queries->select('products')->count();
$queries->select('products')->min('price');
$queries->select('products')->max('price');
$queries->select('products')->sum('price');
$queries->select('products')->avg('price');
 ```

<a name="leftjoin"></a>
### Join/Left join 
```php
$queries
    ->select('tableA')
    ->join('tableB', 'tableB.id = tableA.id')
    ->find();
```
```php
$queries
    ->select('tableA')
    ->leftJoin('tableB', 'tableB.id = tableA.id')
    ->find();
```
<a name="where"></a>
### Where 
```php
$queries
    ->select('tableA')
    ->where('column', 'val1')
    ->where('column', '=', 'val1')
    ->where('column', 'IN', ['val2', 'val3'])
    ->where('column', 'LIKE', '%a%')
    ->where('column', '<>', 15)
    ->where('column', 'BETWEEN', 5, 10)
    ->where('column', '>', 1)
    ->where('column', '>=', 1)
    ->where('column', '<', 1)
    ->where('column', '<=', 1)
    ->where('column', 'IS NULL')
    ->where('column', 'IS NOT NULL')
    ->where('columnA = ? and columnB = ? and columnC = ?', ['valA', 'valB', 3])
    ->where([
        'columnA' => 'valA',
        'columnB' => 'valB',
        'columnC' => 3
    ])
``` 
Default conjunction is `AND`. To use `OR` use `orWhere`

```php
$queries
    ->select('tableA')
    ->where('columnA', 'val1')
    ->orWhere('column', 'IN', ['val2', 'val3'])
```

#### Nested conditions
If you want to use group of conditions enclosed in parenthesis use closure
```php
$queries
    ->select('tableA')
    ->where('columnA', 'val1')
    ->where(function($query) {
        $query->where('column', 'val2')->orWhere('column', 'val3')
    });
```

<a name="orderby"></a>
### OrderBy
```php
$queries->select('table')->orderByDesc('column1');
$queries->select('table')->orderByAsc('column1');
$queries->select('table')->orderBy('column1', -1); //descending 
$queries->select('table')->orderBy('column1', 1) //ascending
$queries->select('table')->orderByDesc('column1')->orderByDesc('column2');
```

<a name="groupby"></a>
### GroupBy
```php
$queries->select('table')->groupBy('column1', 'column2');
$queries->select('table')->groupBy('column1')->groupBy('column2')->groupBy('column3')
```
<a name="having"></a>
### Having 
Everything what refers to `where` or `orWhere` refers to `having` and `orHaving`
```php
$queries
    ->select('cutomers', 'country, city, count(*)')
    ->groupBy('country, city')
    ->having('count(*)', '>', 1)
    ->having('city', 'LIKE', '%o%')
    ->find();
``` 

<a name="orm"></a>
## ORM

To use StormQueries as ORM you need add map in `from` and `join` clause.

### Map
Map defines
 - columns and their class mapping properties
 - class name (by default `stdClass`) 
 - identity property (by default `id`)

### From
```php
$queries
  ->select('customers', 'customer_id = ?', 28, Map::select([
      'customer_id' => 'id',
      'customer_name' => 'name'
  ]))
  ->find()
```
#### Map to user class
```php
$queries
  ->select('customers',  Map::select([
      'customer_id' => 'id',
      'customer_name' => 'name'
  ], Customer::class))
  ->find()
```

#### Map as combination of index and associative array 
```php
$queries
  ->select('customers',  Map::select([
      'customer_id' => 'id',
      'customer_name' => 'name',
      'city', 
      'country'
  ], Customer::class))
  ->find()
```

### Joins

#### One-to-one relationship
```php
$orders = $queries
  ->select('orders o', Map::select("orders", [
    'order_id' => 'id',
    'order_date' => 'date'
  ]))
  ->leftJoin('shippers sh', 'sh.shipper_id = o.shipper_id', Map::one('shipper', [
          'shipper_id' => 'id',
          'shipper_name' => 'name'
  ]))
  ->findAll();

foreach($orders as $order) {
    print_order($order);
    print_shipper($order->shipper);
}
```

#### One-to-one non-hierarchical relationship
```php
$products = $this->queries
  ->select('products p', Map::select([
      'p.product_id' => 'id',
      'p.product_name' => 'name',
      's.supplier_name' => 'supplierName',
      'c.category_name' => 'categoryName',
  ]))
  ->leftJoin('categories c', 'c.category_id = p.category_id')
  ->leftJoin('suppliers s', 's.supplier_id = p.supplier_id')
  ->findAll();

foreach($products as $product) {
    echo "$product->name $product->supplierName $product->categoryName";
}
```

#### One-to-many relationship
```php
$queries
  ->select('customers c',  Map::select([
      'customer_id' => 'id',
      'customer_name' => 'name'
  ]))
  ->leftJoin('orders o', 'o.customer_id = c.customer_id', Map::many("orders", [
          'order_id' => 'id'
  ]))
```
#### Many-to-many relationship
```php
$queries
  ->select('products p', Map::select([
      'product_id' => 'id',
      'product_name' => 'name'
  ]))
  ->leftJoin('products_tags pt', 'pt.product_id = p.product_id', Map::join())
  ->leftJoin('tags t', 't.tag_id = pt.tag_id', Map::many("tags", [
      'tag_id' => 'id',
      'name' => 'name'
  ]))
  ->where('p.product_id', 'in', [1,2,3,4])
  ->findAll();
```

<a name="subquery"></a>
## Sub queries

#### from
```php
$queries
->select(
    SubQuery::create($queries->select('products'), 'p')
)
->where('p.product_id', 7)
->find();
```
#### left join
```php
$queries
->select(
    SubQuery::create($queries->from('products'), 'p')
)
->leftJoin(
    SubQuery::create($queries->from('suppliers'), 's'), 's.supplier_id = p.supplier_id')
)
->findAll();
```

#### where 
```php
$queries
->select("products")
->where("category_id", 1)
->where('price', '<=',
    $queries
        ->select("avg(price)")
        ->from("products")
        ->where("category_id", 1)
)
->findAll();
```

### Restrictions
Using joins requires using aliases in `from` and `join` clause.

<a name="assemblingquery"></a>
## Assembling query

```php 
$query = $queries
    ->select('products')
    ->join('product_photos', 'product_photos.product_id = products.id')
    ->where('is_in_sale', true);
if ($criteria->hasCategory()) {
    $query->where('category_id', $criteria->getCategoryId());
}
if ($criteria->hasOrder()) {
    $query->orderBy($criteria->getOrderField(), $criteria->getOrderDirection());
}
if ($criteria->hasSearchPhrase()) {
    $query->where('description', "LIKE", '%' . $criteria->getPhrase() . '%');
}

$products = $query->findAll();
```

<a name="insertquery"></a>
## Insert
```php
$id = $queries->insert('person', ['name' => 'Micheal']);
```
If you don't want to invoke `getLastInsertedId` on `PDO` pass `false` to `execute` method
```php
$queries->insertQuery('person', ['name' => 'Micheal'])->execute(false);
```

### Insert many
```php
$queries
->insertMany('person', [
    ['name' => 'Michael'],
    ['name' => 'Kevin'],
    ['name' => 'LeBron']
])
->execute(); 
```

<a name="updatequery"></a>
## Update
```php
$queries->update('person', 'id = ?', 2, ['name' => 'Matthew']);
```
or
```php
$queries->update('person', ['id' => 2], ['name' => 'Matthew']);
```
or 
```php
$queries->updateQuery('products')->where('id', 3)->set('price = price + 5')->execute();
```
or
```php
$queries->updateQuery('person')->where('id', 2)->set(['name' => 'Matthew'])->execute();
```


<a name="deletequery"></a>
## Delete 
```php
$queries->delete('person', 'id = ?', 1);
```
or
```php
$queries->delete('person', ['id' => 1]);
```
or
```php
$queries->deleteQuery('person')->where('id', 1)->execute();
```



<a name="profiling"></a>
## Profiling nad logging queries

To track what queries are going to database add `callback` to `IConnection` object.  
```php
$connection = ConnectionFactory::createFromString("...", "...", "...")

$connection->onSuccess(function(string $sql, DateInterval $interval) {
    //log here
});

$connection->onFailre(function(string $sql, DateInterval $interval, Exception $e) {
    //log here
})
```

<a name="notice"></a>
## Notice 
StormQueries uses PDO and databases which support it.\
Tested with PostgreSQL, MySQL, MariaDB, SqlServer and Sqlite.

<a name="tests"></a>
## Tests 

To run tests use `docker composer up` and run one of `run.*.cmd`

<a name="examples"></a>
## Examples 

If you are looking for more use cases take a look at tests in `test` directory of project.

<a name="author"></a>
## Author 

Michał Czerski

If you have any question or ideas you want share with me contact me on GitHub.

<a name="license"></a>
## License 

StormQueries is licensed under MIT licence.