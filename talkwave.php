<?php
/**
 * Plugin Name:       Talkwave
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       talkwave
 *
 * @package CreateBlock
 */

 // Declare the namespace
namespace Talkwave;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define a constant for the plugin file
define( 'TALKWAVE_PLUGIN_FILE', __FILE__ );

// Include the plugin class file
require_once plugin_dir_path( TALKWAVE_PLUGIN_FILE ) . 'includes/class-talkwave.php';

// Function to initialize Talkwave and return the singleton instance
function talkwave() {
	// Get the singleton instance of Talkwave
	$instance = Talkwave::get_instance();

	// Return the singleton instance
	return $instance;
}

// Initialize the plugin
talkwave();

