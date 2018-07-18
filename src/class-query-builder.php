<?php
/**
 * The Query Builder
 */
namespace TheLeague\Database;

/**
 * Query_Builder class.
 */
class Query_Builder {

	use \TheLeague\Database\Traits\Escape;
	use \TheLeague\Database\Traits\Select;
	use \TheLeague\Database\Traits\Where;
	use \TheLeague\Database\Traits\OrderBy;
	use \TheLeague\Database\Traits\Translate;

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table = '';

	/**
	 * make a distinct selection
	 *
	 * @var bool
	 */
	protected $distinct = false;

	/**
	 * make SQL_CALC_FOUND_ROWS in selection
	 *
	 * @var bool
	 */
	protected $found_rows = false;

	/**
	 * The query select statements
	 *
	 * @var array
	 */
	protected $select = array();

	/**
	 * The query where statements
	 *
	 * @var array
	 */
	protected $wheres = array();

	/**
	 * order by container
	 *
	 * @var array
	 */
	protected $orders = array();

	/**
	 * the query limit
	 *
	 * @var int
	 */
	protected $limit = null;

	/**
	 * values container for insert/update
	 *
	 * @var array
	 */
	protected $values = array();

	/**
	 * Constructor
	 */
	public function __construct( $table ) {
		$this->table = $table;
	}

	/**
	 * Translate the given query object and return the results
	 *
	 * @param string $output (Optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 * @return mixed
	 */
	public function get( $output = OBJECT ) {
		global $wpdb;

		$query = $this->translateSelect();
		$this->reset();

		return $wpdb->get_results( $query, $output ); // WPCS: unprepared SQL ok.
	}

	/**
	 * Translate the given query object and return the results
	 *
	 * @param string $output (Optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 * @return mixed
	 */
	public function one( $output = OBJECT ) {
		global $wpdb;

		$this->limit( 1 );
		$query = $this->translateSelect();
		$this->reset();

		return $wpdb->get_row( $query, $output ); // WPCS: unprepared SQL ok.
	}

	/**
	 * Translate the given query object and return one variable from the database
	 *
	 * @return mixed
	 */
	public function var() {
		$row = $this->one( ARRAY_A );

		return current( $row );
	}

	/**
	 * Insert a row into a table
	 *
	 * @see wpdb::insert()
	 */
	public function insert( $data, $format = null ) {
		global $wpdb;

		return $wpdb->insert( $this->table, $data, $format );
	}

	/**
	 * Update a row into a table
	 *
	 * @see wpdb::update()
	 */
	public function update() {

		$query = $this->translateUpdate();
		$this->reset();

		return $this->query( $query );
	}

	/**
	 * Delete data from table
	 *
	 * @return mixed
	 */
	public function delete() {

		$query = $this->translateDelete();
		$this->reset();

		return $this->query( $query );
	}

	/**
	 * Truncate table.
	 *
	 * @return mixed
	 */
	public function truncate() {
		return $this->query( "truncate table {$this->table};" );
	}

	/**
	 * Get found rows.
	 * @return int
	 */
	public function get_found_rows() {
		global $wpdb;

		return $wpdb->get_var( 'SELECT FOUND_ROWS();' );
	}

	/**
	 * Perform a MySQL database query, using current database connection.
	 *
	 * @see wpdb::query
	 */
	public function query( $query ) {
		global $wpdb;

		return $wpdb->query( $query ); // WPCS: unprepared SQL ok.
	}

	/**
	 * Set the limit clause.
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return self The current query builder.
	 */
	public function limit( $limit, $offset = 0 ) {
		global $wpdb;
		$limit  = absint( $limit );
		$offset = absint( $offset );

		$this->limit = $wpdb->prepare( 'LIMIT %d OFFSET %d', $limit, $offset );

		return $this;
	}

	/**
	 * Create an query limit based on a page and a page size
	 *
	 * @param int        $page
	 * @param int         $size
	 *
	 * @return self The current query builder.
	 */
	public function page( $page, $size = 25 ) {
		$size   = absint( $size );
		$offset = $size * absint( $page );

		$this->limit( $size, $offset );

		return $this;
	}

	/**
	 * Set values for insert/update
	 *
	 * @param string|array $name
	 * @param string|array $value
	 */
	public function set( $name, $value ) {

		if ( is_array( $name ) ) {
			$this->values = $this->values + $value;
		} else {
			$this->values[ $name ] = $value;
		}

		return $this;
	}

	private function reset() {
		$this->distinct   = false;
		$this->found_rows = false;
		$this->select     = array();
		$this->wheres     = array();
		$this->orders     = array();
		$this->values     = array();
		$this->limit      = null;
	}
}
