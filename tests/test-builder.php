<?php
/**
 * BuilderTest class.
 */
class BuilderTest extends WP_UnitTestCase {

	public function test_instance() {
		$table = $this->createBuilder();
		$this->assertInstanceOf( '\TheLeague\Database\Query_Builder', $table );
	}

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

	public function test_select_fields() {

		$this->assertQueryTranslation( 'select id from phpunit', 'Select', function( $table ) {
			$table->select( 'id' );
		});

		// comma seperated fields
		$this->assertQueryTranslation( 'select id, foo from phpunit', 'Select', function( $table ) {
			$table->select( 'id, foo' );
		});

		// with array
		$this->assertQueryTranslation( 'select id, foo from phpunit', 'Select', function( $table ) {
			$table->select( [ 'id', 'foo' ] );
		});

		// with alias as string
		$this->assertQueryTranslation( 'select id, foo as f from phpunit', 'Select', function( $table ) {
			$table->select( 'id, foo as f' );
		});

		// with array with alias
		$this->assertQueryTranslation( 'select id as d, foo as f from phpunit', 'Select', function( $table ) {
			$table->select( [
				'id'  => 'd',
				'foo' => 'f',
			] );
		});
	}

	public function test_select_count() {
		$this->assertQueryTranslation( 'select count(*), foo as f from phpunit', 'Select', function( $table ) {
			$table->selectCount()
				->select( 'foo as f' );
		});

		$this->assertQueryTranslation( 'select count(id) as count from phpunit', 'Select', function( $table ) {
			$table->selectCount( 'id', 'count' );
		});
	}

	public function test_select_others() {

		$this->assertQueryTranslation( 'select sum(id) as count from phpunit', 'Select', function( $table ) {
			$table->selectSum( 'id', 'count' );
		});

		$this->assertQueryTranslation( 'select avg(id) as average from phpunit', 'Select', function( $table ) {
			$table->selectAvg( 'id', 'average' );
		});
	}

	public function test_where() {
		$this->assertQueryTranslation( 'select * from phpunit where id = 2', 'Select', function( $table ) {
			$table->select()
				->where( 'id', 2 );
		});
	}

	// Helpers -----------------------------

	protected function assertQueryTranslation( $expected, $translate, $callback ) {
		$builder = $this->createBuilder();
		call_user_func_array( $callback, array( $builder ) );
		$query = $this->invokeMethod( $builder, 'translate' . $translate );
		$this->assertEquals( $expected, $query );
	}

	protected function createBuilder() {
		return new \TheLeague\Database\Query_Builder( 'phpunit' );
	}

	protected function log( $text ) {
		fwrite( STDERR, $text );
	}

	public function invokeMethod( &$object, $method, $parameters = array() ) {
		$reflection = new \ReflectionClass( get_class( $object ) );
		$method     = $reflection->getMethod( $method );
		$method->setAccessible( true );
		return $method->invokeArgs( $object, $parameters );
	}
}
