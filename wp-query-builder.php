<?php
/**
 * Plugin Name:       WordPress Query Builder
 * Version:           1.0.0
 * Plugin URI:        http://thewpleague.com/wp-query-builder/
 * Description:       An expressive query builder for WordPress based on Laravel's Query Builder. Wraps the $wpdb global.
 * Author:            The WordPress League
 * Author URI:        http://thewpleague.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined( 'ABSPATH' ) || exit;
define( 'RANK_MATH_FILE', __FILE__ );

/**
 * PSR-4 Autoload.
 */
include dirname( __FILE__ ) . '/vendor/autoload.php';
