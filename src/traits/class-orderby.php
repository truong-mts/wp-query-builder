<?php
/**
 * The Query Builder
 */
namespace TheLeague\Database\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * OrderBy class.
 */
class OrderBy {

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
	 * Returns an string argument as parsed array if possible
	 *
	 * @param string  $argument
	 * @return array
	 */
	protected function argument_to_array( $argument ) {
		if ( false !== strpos( $argument, ',' ) ) {
			return array_map( 'trim', explode( ',', $argument ) );
		}

		return array( $argument );
	}
}
