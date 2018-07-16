# WordPress Query Builder
A query builder for WordPress

```
global $wpdb;

Database::table( $wpdb->prefix . 'your_custom_table_name' )
  ->select()
  ->where( 'id', 2 )
  ->orderBy( 'id', 'desc' )
  ->limit( 20 )
  ->execute();
```
  
