<?php
/**
 * The Query Builder
 */
namespace TheLeague\Database\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Escape class.
 */
class Escape {

	/**
	 * Escape array values for sql
	 *
	 * @param  array $arr
	 * @return array
	 */
	public function esc_array( $arr ) {
		return array_map( array( $this, 'esc_value' ), $arr );
	}

	/**
	 * Escape value for sql
	 *
	 * @param  mixed $value
	 * @return mixed
	 */
	public function esc_value( $value ) {
		global $wpdb;

		if ( is_numeric( $value ) ) {
			return $wpdb->prepare( '%d', $value );
		}

		if ( is_string( $value ) ) {
			return $wpdb->prepare( '%s', $value );
		}

		return $value;
	}
}
