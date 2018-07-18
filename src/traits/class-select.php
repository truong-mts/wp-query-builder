<?php
/**
 * The Query Builder
 *
 * @package TheLeague\Database
 */

namespace TheLeague\Database\Traits;

/**
 * Select class.
 */
trait Select {

	/**
	 * Set the selected fields
	 *
	 * @param array $fields Fields to select.
	 *
	 * @return self The current query builder.
	 */
	public function select( $fields = '' ) {
		if ( empty( $fields ) ) {
			return $this;
		}

		if ( is_string( $fields ) ) {
			$this->select[] = $fields;
			return $this;
		}

		foreach ( $fields as $key => $field ) {
			if ( is_string( $key ) ) {
				$this->select[] = "$key as $field";
			} else {
				$this->select[] = $field;
			}
		}

		return $this;
	}

	/**
	 * Shortcut to add a count function
	 *
	 *     ->selectCount('id')
	 *
	 * @param string $field
	 * @param string $alias
	 * @return self The current query builder.
	 */
	public function selectCount( $field = '*', $alias = null ) { // @codingStandardsIgnoreLine
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
		$field = "$func({$field})";
		if ( ! is_null( $alias ) ) {
			$field .= " as {$alias}";
		}
		$this->select[] = $field;

		return $this;
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
	 * SQL_CALC_FOUND_ROWS select setter
	 *
	 * @param bool $found_rows
	 * @return self The current query builder.
	 */
	public function found_rows( $found_rows = true ) {
		$this->found_rows = $found_rows;
		return $this;
	}
}
