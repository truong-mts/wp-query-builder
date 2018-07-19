[![Build Status](https://travis-ci.org/meshakeeb/wp-query-builder.svg?branch=master)](https://travis-ci.org/meshakeeb/wp-query-builder)

# WordPress Query Builder

An expressive query builder for WordPress based on Laravel's Query Builder. Wraps the `$wpdb` global.

## How to use in managed environments

If you wish to use this extension in a managed environment, simply install using `composer`:

```
composer require thewpleague/wp-query-builder
```

To use the Query builder

```php
include('vendor/autoload.php');

global $wpdb;

$table = new TheLeague\Database\Database::table( $wpdb->prefix . 'users' );

$table->select()
  ->where( 'id', 2 )
  ->orderBy( 'id', 'desc' )
  ->limit( 20 )
  ->get();
```

## Data Sanitisation

The purpose of this library is to provide an **expressive** and **safe*** way
to run queries against your WordPress database (typically involving custom tables).

To this end all **values** provided are escaped, but note that **column and table**
names are not yet escaped. In any case, even if they were you should be whitelisting
any allowed columns/tables: otherwise using user-input, or other untrusted data to
determine the column/table could allow an attacker to retrieve data they shouldn't
or generate a map of your database.

## Examples

### Select statement

```php
global $wpdb;

$table = new TheLeague\Database\Database::table( $wpdb->prefix . 'users' )

// select * from wp_users
$table->select()->get();

// select distinct * from wp_users
$table->select()->distinct()->get();

// select SQL_CALC_FOUND_ROWS * from wp_users
$table->select()->found_rows()->get();
```

#### Specify columns for Select statement

```php
// select id from wp_users
$table->select( 'id' )->get();

// select id, user_login from wp_users
$table->select( 'id, user_login' )->get();

// select id, user_login from wp_users
$table->select( array( 'id', 'user_login' ) )->get();

// select id, user_login as username from wp_users
$table->select( array( 'id', 'user_login as username' ) )->get();

// select id, user_login as username from wp_users
$table->select( array(
	'id',
	'user_login' => 'username'
) )->get();
```

#### Select statement with count, sum, avg

```php
// select count(*) from wp_users
$table->selectCount()->get();

// select count( id ) as count from wp_users
$table->selectCount( 'id', 'count' )->get();

// select sum( id ) as total from wp_users
$table->selectSum( 'id', 'total' )->get();

// select avg( id ) as average from wp_users
$table->selectAvg( 'id', 'average' )->get();
```

#### Select a single row

```php
// select * from wp_users WHERE user_email = 'admin@example.com' LIMIT 0, 1;
$table->select()->where( 'user_email', 'admin@example.com' )->one();
```

#### To retrieve a value

```php
$email = $table->select( 'user_email' )->where( 'ID', 123 )->getVar();
```

---

### Insert statement

```php
// insert into wp_users columnA, columbB values(`value`, `value`)
$table->insert(array(
	'columnA' => 'value',
	'columnB' => 'value',
), array( '%s', '%s' ) );
```

---

### Update statement

```php
// update wp_users set foo = `bar`
$table->set( 'foo', 'bar' );

// update wp_users set foo = `bar`, bar = `foo`
$table->set( 'foo', 'bar' )
	->set( 'bar', 'foo' );

// update wp_users set foo = `bar` where id = 1 limit 0,1
$table->set( 'foo', 'bar' )
	->where( 'id', 1 )
	->limit( 1 );
```

---

### Delete statement

```php
// delete from wp_users where id = 1 limit 0,1
$table->where( 'id', 1 )->limit( 1 );
```

---

### Where statement

```php
// select * from wp_users where id = 2
$table->select()->where( 'id', 2 )->get();

// select * from wp_users where id != 42
$table->select()->where( 'id', '!=', 42 )->get();

// select * from wp_users where id = 2 and active = 1
$table->select()->where( 'id', 2 )->where( 'active', 1 )->get();
$table->select()->where( 'id', 2 )->andWhere( 'active', 1 )->get();

// select * from wp_users where id = 2 or active = 1
$table->select()->where( 'id', 2 )->orWhere( 'active', 1 )->get();

// select * from wp_users where ( a = 'b' or c = 'd' )
$table->select()
	->orWhere( array(
		array( 'a', 'b' ),
		array( 'c', 'd' ),
	) )->get();

// select * from wp_users where a = 1 or ( a > 10 and a < 20 )
$table->select()
	->where( 'a', 1 )
	->orWhere( array(
		array( 'a', '>', 10 ),
		array( 'a', '<', 20 ),
	), 'and' )->get();

// select * from wp_users where a = 1 or ( a > 10 and a < 20 ) and c = 30
$table->select()
	->where( 'a', 1 )
	->orWhere( array(
		array( 'a', '>', 10 ),
		array( 'a', '<', 20 ),
	), 'and' )
	->andWhere( 'c', 30 )->get();

// select * from wp_users where id in (23, 25, 30)
$table->select()->whereIn( 'id', array( 23, 25, 30 ) );

// select * from wp_users where skills in ('php', 'javascript', 'ruby')
$table->select()->whereIn( 'skills', array( 'php', 'javascript', 'ruby' ) );
```

---

### Groupby statement

```php
// select * from wp_users group by id
$table->select()->groupBy( 'id' )->get();
```

---

### Orderby statement

```php
// select * from wp_users order by id asc
$table->select()->orderBy( 'id' )->get();

// select * from wp_users order by id desc
$table->select()->orderBy( 'id', 'desc' )->get();

// select * from wp_users order by firstname desc, lastname desc
$table->select()->orderBy( 'firstname, lastname', 'desc' )->get();

// select * from wp_users order by firstname asc, lastname desc
$table->select()->orderBy(array(
	'firstname' => 'asc',
	'lastname'  => 'desc',
) )->get();

// select * from wp_users order by firstname <> nick
$table->select()->orderBy( 'firstname <> nick', null )->get();
```

---

### Limit statement

```php
// select * from wp_users limit 0, 1
$table->select()->limit( 1 )->get();

// select * from wp_users limit 20, 10
$table->select()->limit( 10, 20 )->get();

// select * from wp_users limit 20, 10
$table->select()->page( 2, 10 )->get();
```
