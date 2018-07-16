<?php
/**
 * The Query Builder
 */
defined( 'ABSPATH' ) || exit;

/**
 * Query_Builder class.
 */
class Query_Builder {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table = '';

	/**
	 * Type of query to translate in.
	 *
	 * @var string
	 */
	protected $handle = 'select';

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

	public function __construct( $table ) {
		$this->table = $table;
	}

	/**
	 * Translate the given query object and return the results
	 *
	 * @param string $output (Optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 * @return mixed
	 */
	public function execute( $output = OBJECT ) {
		global $wpdb;

		$translate = 'translate' . \ucwords( $this->handle );
		$query     = $this->$translate();
		$this->reset();

		return $wpdb->get_results( $query, $output ); // WPCS: unprepared SQL ok.
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
	 * Distinct select setter
	 *
	 * @param bool $distinct
	 * @return self The current query builder.
	 */
	public function distinct( $distinct = true ) {
		$this->distinct = $distinct;
		return $this;
	}

	/**
	 * Invoke SQL_CALC_FOUND_ROWS
	 *
	 * @param bool $found_rows
	 * @return self The current query builder.
	 */
	public function found_rows( $found_rows = true ) {
		$this->found_rows = $found_rows;
		return $this;
	}

	/**
	 * Set the selected fields
	 *
	 * @param  array $fields
	 * @return self The current query builder.
	 */
	public function select() {
		$this->handle = 'select';

		$fields = \func_get_args();
		if ( empty( $fields ) ) {
			return $this;
		}

		$this->select = $this->select + $fields;
	}

	/**
	 * Shortcut to add a count function
	 *
	 *     ->selectCount('id')
	 *
	 * @param string                $field
	 * @param string                $alias
	 * @return self The current query builder.
	 */
	public function selectCount( $field, $alias = null ) { // @codingStandardsIgnoreLine
		return $this->selectFunc( 'count', $field, $alias );
	}

	/**
	 * Shortcut to add a sum function
	 *
	 *     ->selectSum('id')
	 *
	 * @param string                $field
	 * @param string                $alias
	 * @return self The current query builder.
	 */
	public function selectSum( $field, $alias = null ) { // @codingStandardsIgnoreLine
		return $this->selectFunc( 'sum', $field, $alias );
	}

	/**
	 * Shortcut to add a avg function
	 *
	 *     ->selectAvg('id')
	 *
	 * @param string                $field
	 * @param string                $alias
	 * @return self The current query builder.
	 */
	public function selectAvg( $field, $alias = null ) { // @codingStandardsIgnoreLine
		return $this->selectFunc( 'avg', $field, $alias );
	}

	/**
	 * Shortcut to add a function
	 *
	 * @param string $func
	 * @param string $field
	 * @param string $alias
	 * @return self The current query builder.
	 */
	public function selectFunc( $func, $field, $alias = null ) { // @codingStandardsIgnoreLine
		$this->handle = 'select';

		$field = "$func({$field})";
		if ( ! is_null( $alias ) ) {
			$field .= " as {$alias}";
		}
		$this->select[] = $field;

		return $this;
	}

	/**
	 * Create a where statement
	 *
	 *     ->where('name', 'ladina')
	 *     ->where('age', '>', 18)
	 *     ->where('name', 'in', array('charles', 'john', 'jeffry'))
	 *
	 * @param string            $column The SQL column
	 * @param mixed             $param1 Operator or value depending if $param2 isset.
	 * @param mixed             $param2 The value if $param1 is an opartor.
	 * @param string            $type the where type ( and, or )
	 *
	 * @return self The current query builder.
	 */
	public function where( $column, $param1 = null, $param2 = null, $type = 'and' ) {

		// check if the where type is valid
		if ( ! in_array( $type, array( 'and', 'or', 'where' ) ) ) {
			throw new Exception( 'Invalid where type "' . $type . '"' );
		}

		if ( empty( $this->wheres ) ) {
			$type = 'where';
		}

		// when column is an array we assume to make a bulk and where.
		if ( is_array( $column ) ) {
			$subquery = array();
			foreach ( $column as $key => $val ) {
				$subquery[] = $this->generateWhere( $key, $val, null, $type );
			}

			$this->wheres[] = array( 'subquery', $subquery );

			return $this;
		}

		$this->wheres[] = $this->generateWhere( $column, $param1, $param2, $type );

		return $this;
	}

	/**
	 * Create an or where statement
	 */
	public function orWhere( $column, $param1 = null, $param2 = null ) { // @codingStandardsIgnoreLine
		return $this->where( $column, $param1, $param2, 'or' );
	}

	/**
	 * Create an and where statement
	 */
	public function andWhere( $column, $param1 = null, $param2 = null ) { // @codingStandardsIgnoreLine
		return $this->where( $column, $param1, $param2, 'and' );
	}

	/**
	 * Creates a where in statement
	 *
	 *     ->whereIn('id', [42, 38, 12])
	 */
	public function whereIn( $column, $options = array() ) { // @codingStandardsIgnoreLine

		// when the options are empty we skip
		if ( empty( $options ) ) {
			return $this;
		}

		return $this->where( $column, 'in', $options );
	}

	/**
	 * Creates a where like statement
	 *
	 *     ->whereIn('id', 'value' )
	 */
	public function whereLike( $column, $value ) { // @codingStandardsIgnoreLine
		global $wpdb;
		return $this->where( $column, 'like', '%' . $wpdb->esc_like( $value ) . '%' );
	}

	/**
	 * Add an order by statement to the current query
	 *
	 *     ->orderBy('created_at')
	 *     ->orderBy('modified_at', 'desc')
	 *
	 *     // multiple order statements
	 *     ->orderBy(['firstname', 'lastname'], 'desc')
	 *
	 *     // muliple order statements with diffrent directions
	 *     ->orderBy(['firstname' => 'asc', 'lastname' => 'desc'])
	 *
	 * @param array|string $columns
	 * @param string       $direction
	 * @return self The current query builder.
	 */
	public function orderBy( $columns, $direction = 'asc' ) { // @codingStandardsIgnoreLine
		if ( is_string( $columns ) ) {
			$columns = $this->argument_to_array( $columns );
		}

		foreach ( $columns as $key => $column ) {
			if ( is_numeric( $key ) ) {
				$this->orders[ $column ] = $direction;
			} else {
				$this->orders[ $key ] = $column;
			}
		}
		return $this;
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

		if ( is_numeric( $offset ) && is_numeric( $limit ) && $offset >= 0 && $limit > 0 ) {
			$this->limit = $wpdb->prepare( 'LIMIT %d OFFSET %d', $limit, $offset );
		}

		return $this;
	}

	/**
	 * Returns an string argument as parsed array if possible
	 *
	 * @param string  $argument
	 * @return array
	 */
	protected function argument_to_array( $argument ) {
		if ( Helper::str_contains( ',', $argument ) ) {
			return array_map( 'trim', explode( ',', $argument ) );
		}

		return array( $argument );
	}

	/**
	 * Generate Where
	 *
	 * @see where()
	 */
	protected function generateWhere( $column, $param1 = null, $param2 = null, $type = 'and' ) { // @codingStandardsIgnoreLine

		// when param2 is null we replace param2 with param one as the
		// value holder and make param1 to the = operator.
		if ( is_null( $param2 ) ) {
			$param2 = $param1;
			$param1 = '=';
		}

		// When param2 is an array we probably
		// have an "in" or "between" statement which has no need for duplicates.
		if ( is_array( $param2 ) ) {
			$param2 = array_unique( $param2 );
		}

		return array( $type, $column, $param1, $param2 );
	}

	private function reset() {
		$this->handle     = 'select';
		$this->distinct   = false;
		$this->found_rows = false;
		$this->select     = array();
		$this->wheres     = array();
		$this->orders     = array();
		$this->limit      = null;
	}

	// Translate API ------------------------------------------------------

	/**
	 * Translate the current query to an SQL select statement
	 *
	 * @return string
	 */
	private function translateSelect() { // @codingStandardsIgnoreLine
		$build = array( 'select' );

		if ( $this->found_rows ) {
			$build[] = 'SQL_CALC_FOUND_ROWS';
		}
		if ( $this->distinct ) {
			$build[] = 'distinct';
		}

		// build the selected fields
		if ( ! empty( $this->select ) ) {
			$columns = array();

			foreach ( $this->select as $key => $field ) {
				list( $column, $alias ) = $field;

				$columns[] = is_null( $alias ) ? $column : "{$column} as {$alias}";
			}

			$build[] = join( ', ', $columns );
		} else {
			$build[] = '*';
		}

		// append the table
		$build[] = 'from ' . $this->table;

		// build the where statements
		if ( ! empty( $this->wheres ) ) {
			$build[] = $this->translateWhere( $this->wheres );
		}

		// build the order statement
		if ( ! empty( $this->orders ) ) {
			$build[] = $this->translateOrderBy();
		}

		// build offset and limit
		if ( ! empty( $this->limit ) ) {
			$build[] = $this->limit;
		}

		return join( ' ', $build );
	}

	/**
	 * Translate the where statements into sql
	 *
	 * @return string
	 */
	protected function translateWhere( $wheres ) { // @codingStandardsIgnoreLine
		$build = array();
		foreach ( $wheres as $where ) {

			// to make nested wheres possible you can pass an closure
			// wich will create a new query where you can add your nested wheres
			if ( ! isset( $where[2] ) && 'subquery' === $where[1] ) {
				$build[] = '( ' . substr( $this->translateWhere( $where[1] ), 6 ) . ' )';
				continue;
			}

			// when we have an array as where values we have to parameterize them
			if ( is_array( $where[3] ) ) {
				$where[3] = '(' . join( ', ', $where[3] ) . ')';
			} elseif ( is_string( $where[3] ) ) {
				global $wpdb;
				$where[3] = $wpdb->prepare( '%s', $where[3] );
			}

			// join the beauty
			$build[] = ' ' . join( ' ', $where );
		}

		return join( ' ', $build );
	}

	/**
	 * Build the order by statement
	 *
	 * @return string
	 */
	protected function translateOrderBy() { // @codingStandardsIgnoreLine
		$build = array( 'order by' );

		foreach ( $this->orders as $column => $direction ) {

			// in case a raw value is given we had to
			// put the column / raw value an direction inside another
			// array because we cannot make objects to array keys.
			if ( is_array( $direction ) ) {
				list( $column, $direction ) = $direction;
			}

			$build[] = $column . ' ' . $direction;
		}
		return join( ' ', $build );
	}
}
