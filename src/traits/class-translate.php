<?php
/**
 * The Query Builder
 *
 * @package TheLeague\Database
 */

namespace TheLeague\Database\Traits;

/**
 * Translate class.
 */
trait Translate {

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

		// Build the selected fields.
		$build[] = ! empty( $this->select ) && is_array( $this->select ) ? join( ', ', $this->select ) : '*';

		// Append the table.
		$build[] = 'from ' . $this->table;

		// Build the where statements.
		if ( ! empty( $this->wheres ) ) {
			$build[] = $this->translateWhere( $this->wheres );
		}

		// Build the order statement.
		if ( ! empty( $this->orders ) ) {
			$build[] = $this->translateOrderBy();
		}

		// Build offset and limit.
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

		// Build the where statements.
		if ( ! empty( $this->wheres ) ) {
			$build[] = $this->translateWhere( $this->wheres );
		}

		// Build offset and limit.
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

		// Build the where statements.
		if ( ! empty( $this->wheres ) ) {
			$build[] = $this->translateWhere( $this->wheres );
		}

		// Build offset and limit.
		if ( ! empty( $this->limit ) ) {
			$build[] = $this->limit;
		}

		return join( ' ', $build );
	}

	/**
	 * Translate the where statements into sql
	 *
	 * @param array $wheres Where statements.
	 *
	 * @return string
	 */
	protected function translateWhere( $wheres ) { // @codingStandardsIgnoreLine
		$build = array();
		foreach ( $wheres as $where ) {

			// To make nested wheres possible you can pass an closure.
			// Wich will create a new query where you can add your nested wheres.
			if ( isset( $where[0] ) && 'subquery' === $where[0] ) {
				unset( $where[1][0][0] );
				if ( true == $where[2] ) {
					$build[] = 'where';
				}
				$build[] = '( ' . $this->translateWhere( $where[1] ) . ' )';
				continue;
			}

			// When we have an array as where values we have to parameterize them.
			if ( is_array( $where[3] ) ) {
				$where[3] = '(' . join( ', ', $this->esc_array( $where[3] ) ) . ')';
			} elseif ( is_scalar( $where[3] ) ) {
				$where[3] = $this->esc_value( $where[3] );
			}

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
