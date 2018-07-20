<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Wp_Query_Builder
 */

// Disable xdebug backtrace.
if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

echo 'Welcome to the WordPress Query Builder Test Suite' . PHP_EOL;
echo 'Version: 1.0' . PHP_EOL . PHP_EOL;

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/wp-query-builder.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
