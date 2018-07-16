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
  
```
function get_logs( $args ) {
  $table = Database::table( $wpdb->prefix . 'your_custom_table_name' )
	$args  = wp_parse_args( $args, array(
		'orderby' => 'id',
		'order'   => 'DESC',
		'limit'   => 10,
		'paged'   => 0,
		'search'  => '',
	) );

	$offset = $args['paged'] ? $args['limit'] * ( $args['paged'] - 1 ) : 0;
	$table->found_rows()
		->select()
		->limit( $args['limit'], $offset );

	if ( ! empty( $args['search'] ) ) {
		$table->whereLike( 'uri', $args['search'] );
	}

	if ( ! empty( $args['orderby'] ) && in_array( $args['orderby'], array( 'id', 'uri', 'accessed', 'times_accessed' ) ) ) {
		$table->orderBy( $args['orderby'], $args['order'] );
	}

	$logs  = $table->execute( ARRAY_A );
	$count = $table->get_found_rows();

	return compact( 'logs', 'count' );
}
  ```
