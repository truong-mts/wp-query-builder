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

$table = new TheLeague\Database\Database::table( $wpdb->prefix . 'users' )

$table->select()
  ->where( 'id', 2 )
  ->orderBy( 'id', 'desc' )
  ->limit( 20 )
	->execute();
```

## Data Sanitisation

The purpose of this library is to provide an **expressive** and **safe*** way
to run queries against your WordPress database (typically involving custom tables).

To this end all **values** provided are escaped, but note that **column and table**
names are not yet escaped. In any case, even if they were you should be whitelisting
any allowed columns/tables: otherwise using user-input, or other untrusted data to
determine the column/table could allow an attacker to retrieve data they shouldn't
or generate a map of your database.

## Querying Results

### Retrieving all rows from a table

```php
$table->select()->get();
//SELECT * FROM wp_users;
```

### Retrieve a single row

```php
$table->select()->where( 'user_email', 'admin@example.com' )->one();
//SELECT * FROM wp_users WHERE user_email = 'admin@example.com' LIMIT 1;
```

### To retrieve a value

```php
$email = $table->select( 'user_email' )->where( 'ID', 123)->var();
```
