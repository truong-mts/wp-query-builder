<?php
/**
 * The Query Builder
 *
 * @package TheLeague\Database
 */

namespace TheLeague\Database\Traits;

/**
 * Where class.
 */
trait Where {

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

		if ( ! is_array( $column ) && empty( $this->wheres ) ) {
			$type = 'where';
		}

		// when column is an array we assume to make a bulk and where.
		if ( is_array( $column ) ) {
			$subquery = array();
			foreach ( $column as $key => $val ) {
				$subquery[] = $this->generateWhere( $key, $val, null, $type );
			}

			$this->wheres[] = array( 'subquery', $subquery, empty( $this->wheres ) );

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
}
