<?php
/**
 * The Query Builder
 */
namespace TheLeague\Database\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Select class.
 */
class Select {

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
