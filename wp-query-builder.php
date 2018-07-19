<?php
/**
 * WordPress Query Builder
 *
 * @package      TheLeague\Database
 * @copyright    Copyright (C) 2018, The WordPress League - info@thewpleague.com
 * @link         http://thewpleague.com
 * @since        1.0.0
 *
 * @wordpress-plugin
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

/**
 * PSR-4 Autoload.
 */
include dirname( __FILE__ ) . '/vendor/autoload.php';
