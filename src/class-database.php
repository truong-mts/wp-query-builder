<?php
/**
 * The Database
 */
namespace TheLeague\Database;

/**
 * Database class.
 */
class Database {

	/**
	 * Array of all databases objects.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * Retrieve a Database instance by table name.
	 *
	 * @param string $table_name A Database instance id.
	 *
	 * @return Database|bool False or Database object instance.
	 */
	public static function table( $table_name ) {

		if ( empty( self::$instances ) || empty( self::$instances[ $table_name ] ) ) {
			self::$instances[ $table_name ] = new Query_Builder( $table_name );
		}

		return self::$instances[ $table_name ];
	}
}
