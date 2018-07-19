<?php
/**
 * BuilderTest class.
 *
 * This file contains test for the library.
 *
 * @package TheLeague\Database
 * @author  Shakeeb Ahmed <me@shakeebahmed.com>
 */

/**
 * Builder Test Class
 */
class BuilderTest extends WP_UnitTestCase {

	/**
	 * MySql grammar tests
	 */
	public function test_instance() {
		$table = $this->create_builder();
		$this->assertInstanceOf( '\TheLeague\Database\Query_Builder', $table );
	}

	/**
	 * MySql grammar tests
	 */
	public function test_select_simple() {

		$this->assertQueryTranslation( 'select * from phpunit', 'Select', function( $table ) {
			$table->select();
		});

		$this->assertQueryTranslation( 'select distinct * from phpunit', 'Select', function( $table ) {
			$table->select()->distinct();
		});

		$this->assertQueryTranslation( 'select SQL_CALC_FOUND_ROWS * from phpunit', 'Select', function( $table ) {
			$table->select()->found_rows();
		});
	}

	/**
	 * MySql grammar tests
	 */
	public function test_select_fields() {

		$this->assertQueryTranslation( 'select id from phpunit', 'Select', function( $table ) {
			$table->select( 'id' );
		});

		// Comma seperated fields.
		$this->assertQueryTranslation( 'select id, foo from phpunit', 'Select', function( $table ) {
			$table->select( 'id, foo' );
		});

		// With array.
		$this->assertQueryTranslation( 'select id, foo from phpunit', 'Select', function( $table ) {
			$table->select( [ 'id', 'foo' ] );
		});

		// With alias as string.
		$this->assertQueryTranslation( 'select id, foo as f from phpunit', 'Select', function( $table ) {
			$table->select( 'id, foo as f' );
		});

		// With array with alias.
		$this->assertQueryTranslation( 'select id as d, foo as f from phpunit', 'Select', function( $table ) {
			$table->select( [
				'id'  => 'd',
				'foo' => 'f',
			] );
		});
	}

	/**
	 * MySql grammar tests
	 */
	public function test_select_count() {
		$this->assertQueryTranslation( 'select count(*), foo as f from phpunit', 'Select', function( $table ) {
			$table->selectCount()
				->select( 'foo as f' );
		});

		$this->assertQueryTranslation( 'select count(id) as count from phpunit', 'Select', function( $table ) {
			$table->selectCount( 'id', 'count' );
		});
	}

	/**
	 * MySql grammar tests
	 */
	public function test_select_others() {

		$this->assertQueryTranslation( 'select sum(id) as count from phpunit', 'Select', function( $table ) {
			$table->selectSum( 'id', 'count' );
		});

		$this->assertQueryTranslation( 'select avg(id) as average from phpunit', 'Select', function( $table ) {
			$table->selectAvg( 'id', 'average' );
		});
	}

	/**
	 * MySql grammar tests
	 */
	public function test_where() {

		// Simple.
		$this->assertQueryTranslation( 'select * from phpunit where id = 2', 'Select', function( $table ) {
			$table->select()
				->where( 'id', 2 );
		});

		// Diffrent expression.
		$this->assertQueryTranslation( 'select * from phpunit where id != 42', 'Select', function( $table ) {
			$table->select()
				->where( 'id', '!=', 42 );
		});

		// 2 wheres AND.
		$this->assertQueryTranslation( 'select * from phpunit where id = 2 and active = 1', 'Select', function( $table ) {
			$table->select()
				->where( 'id', 2 )
				->where( 'active', 1 );
		});

		$this->assertQueryTranslation( 'select * from phpunit where id = 2 and active = 1', 'Select', function( $table ) {
			$table->select()
				->where( 'id', 2 )
				->andWhere( 'active', 1 );
		});

		// 2 wheres OR.
		$this->assertQueryTranslation( 'select * from phpunit where id = 42 or active = 1', 'Select', function( $table ) {
			$table->select()
				->where( 'id', 42 )
				->orWhere( 'active', 1 );
		});

		// Nesting.
		$this->assertQueryTranslation( 'select * from phpunit where ( a = \'b\' or c = \'d\' )', 'Select', function( $table ) {
			$table->select()
				->orWhere( array(
					array( 'a', 'b' ),
					array( 'c', 'd' ),
				) );
		});

		$this->assertQueryTranslation( 'select * from phpunit where ( a > 10 and a < 20 )', 'Select', function( $table ) {
			$table->select()
				->orWhere( array(
					array( 'a', '>', 10 ),
					array( 'a', '<', 20 ),
				), 'and' );
		});

		$this->assertQueryTranslation( 'select * from phpunit where a = 1 or ( a > 10 and a < 20 )', 'Select', function( $table ) {
			$table->select()
				->where( 'a', 1 )
				->orWhere( array(
					array( 'a', '>', 10 ),
					array( 'a', '<', 20 ),
				), 'and' );
		});

		$this->assertQueryTranslation( 'select * from phpunit where a = 1 or ( a > 10 and a < 20 ) and c = 30', 'Select', function( $table ) {
			$table->select()
				->where( 'a', 1 )
				->orWhere( array(
					array( 'a', '>', 10 ),
					array( 'a', '<', 20 ),
				), 'and' )
				->andWhere( 'c', 30 );
		});

		$this->assertQueryTranslation( 'select * from phpunit where id in (23, 25, 30)', 'Select', function( $table ) {
			$table->select()->whereIn( 'id', array( 23, 25, 30 ) );
		});

		$this->assertQueryTranslation( 'select * from phpunit where skills in (\'php\', \'javascript\', \'ruby\')', 'Select', function( $table ) {
			$table->select()->whereIn( 'skills', array( 'php', 'javascript', 'ruby' ) );
		});
	}

	/**
	 * MySql grammar tests
	 */
	public function test_limit() {

		// Simple.
		$this->assertQueryTranslation( 'select * from phpunit limit 0, 1', 'Select', function( $table ) {
			$table->select()->limit( 1 );
		});

		// With offset.
		$this->assertQueryTranslation( 'select * from phpunit limit 20, 10', 'Select', function( $table ) {
			$table->select()->limit( 10, 20 );
		});

		// Pagination.
		$this->assertQueryTranslation( 'select * from phpunit limit 20, 10', 'Select', function( $table ) {
			$table->select()->page( 2, 10 );
		});
	}

	/**
	 * MySql grammar tests
	 */
	public function test_orderby() {

		// Simple.
		$this->assertQueryTranslation( 'select * from phpunit order by id asc', 'Select', function( $table ) {
			$table->select()->orderBy( 'id' );
		});

		// Other direction.
		$this->assertQueryTranslation( 'select * from phpunit order by id desc', 'Select', function( $table ) {
			$table->select()->orderBy( 'id', 'desc' );
		});

		// More keys comma separated.
		$this->assertQueryTranslation( 'select * from phpunit order by firstname desc, lastname desc', 'Select', function( $table ) {
			$table->select()->orderBy( 'firstname, lastname', 'desc' );
		});

		// Multipe sortings diffrent direction.
		$this->assertQueryTranslation( 'select * from phpunit order by firstname asc, lastname desc', 'Select', function( $table ) {
			$table->select()->orderBy( array(
				'firstname' => 'asc',
				'lastname'  => 'desc',
			) );
		});

		// Raw sorting.
		$this->assertQueryTranslation( 'select * from phpunit order by firstname <> nick', 'Select', function( $table ) {
			$table->select()->orderBy( 'firstname <> nick', null );
		});
	}

	/**
	 * MySql grammar tests
	 */
	public function test_update() {

		// Simple.
		$this->assertQueryTranslation( 'update phpunit set foo = \'bar\'', 'Update', function( $table ) {
			$table->set( 'foo', 'bar' );
		});

		// Multiple.
		$this->assertQueryTranslation( 'update phpunit set foo = \'bar\', bar = \'foo\'', 'Update', function( $table ) {
			$table
				->set( 'foo', 'bar' )
				->set( 'bar', 'foo' );
		});

		// With where and limit.
		$this->assertQueryTranslation( 'update phpunit set foo = \'bar\', bar = \'foo\' where id = 1 limit 0, 1', 'Update', function( $table ) {
			$table
				->set( 'foo', 'bar' )
				->set( 'bar', 'foo' )
				->where( 'id', 1 )
				->limit( 1 );
		});
	}

	/**
	 * MySql grammar tests
	 */
	public function test_delete() {

		// Simple.
		$this->assertQueryTranslation( 'delete from phpunit where id = 1 limit 0, 1', 'Delete', function( $table ) {
			$table->where( 'id', 1 )->limit( 1 );
		});
	}

	/**
	 * Assert SQL Query.
	 *
	 * @param  [type] $expected  [description].
	 * @param  [type] $translate [description].
	 * @param  [type] $callback  [description].
	 */
	protected function assertQueryTranslation( $expected, $translate, $callback ) {
		$builder = $this->create_builder();
		call_user_func_array( $callback, array( $builder ) );
		$query = $this->invoke_method( $builder, 'translate' . $translate );
		$this->assertEquals( $expected, $query );
	}

	/**
	 * [create_builder description]
	 *
	 * @return [type] [description]
	 */
	protected function create_builder() {
		return new \TheLeague\Database\Query_Builder( 'phpunit' );
	}

	/**
	 * [log description]
	 *
	 * @param  [type] $text [description].
	 */
	protected function log( $text ) {
		fwrite( STDERR, $text );
	}

	/**
	 * [invoke_method description]
	 *
	 * @param  [type] $object     [description].
	 * @param  [type] $method     [description].
	 * @param  array  $parameters [description].
	 *
	 * @return [type]             [description]
	 */
	public function invoke_method( &$object, $method, $parameters = array() ) {
		$reflection = new \ReflectionClass( get_class( $object ) );
		$method     = $reflection->getMethod( $method );
		$method->setAccessible( true );
		return $method->invokeArgs( $object, $parameters );
	}
}
