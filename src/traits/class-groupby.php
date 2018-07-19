<?php
/**
 * The Query Builder
 *
 * @package TheLeague\Database
 */

namespace TheLeague\Database\Traits;

/**
 * GroupBy class.
 */
trait GroupBy {

	/**
	 * Add an group by statement to the current query
	 *
	 *     ->groupBy('created_at')
	 *
	 * @param array|string $columns Columns.
	 *
	 * @return self The current query builder.
	 */
	public function groupBy( $columns ) { // @codingStandardsIgnoreLine
		if ( is_string( $columns ) ) {
			$columns = $this->argument_to_array( $columns );
		}

		$this->groups = $this->groups + $columns;

		return $this;
	}
}
