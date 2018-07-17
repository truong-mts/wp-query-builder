<?php
/**
 * The Query Builder
 */
namespace TheLeague\Database\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Translate class.
 */
class Translate {

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
				if ( is_array( $field ) ) {
					list( $column, $alias ) = $field;

					$columns[] = is_null( $alias ) ? $column : "{$column} as {$alias}";
				} else {
					$columns[] = $field;
				}
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
	 * Translate the current query to an SQL update statement
	 *
	 * @return string
	 */
	private function translateUpdate() { // @codingStandardsIgnoreLine
		$build = array( "update {$this->table} SET" );

		// add the values.
		foreach ( $this->values as $key => $value ) {
			$build[] = $key . ' = ' . $this->esc_value( $value );
		}

		// build the where statements
		if ( ! empty( $this->wheres ) ) {
			$build[] = $this->translateWhere( $this->wheres );
		}

		// build offset and limit
		if ( ! empty( $this->limit ) ) {
			$build[] = $this->limit;
		}

		return join( ' ', $build );
	}

	/**
	 * Translate the current query to an SQL delete statement
	 *
	 * @return string
	 */
	private function translateDelete() { // @codingStandardsIgnoreLine
		$build = array( "delete from {$this->table}" );

		// build the where statements
		if ( ! empty( $this->wheres ) ) {
			$build[] = $this->translateWhere( $this->wheres );
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
				$where[3] = '(' . join( ', ', $this->esc_array( $where[3] ) ) . ')';
			} elseif ( is_scalar( $where[3] ) ) {
				$where[3] = $this->esc_value( $where[3] );
			}

			// join the beauty
			$build[] = join( ' ', $where );
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
