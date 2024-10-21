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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Function to limit description to a maximum of 20 words
function truncate_description( $text, $limit = 20 ) {
	$words = explode( ' ', strip_tags( $text ) ); // Remove HTML tags and split into words
	if ( count( $words ) > $limit ) {
		$text = implode( ' ', array_slice( $words, 0, $limit ) ) . '...'; // Limit to 20 words and add ellipsis
	}
	return $text;
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_talkwave_block_init() {
	$custom_blocks = array (
		'podcasts',
		'episodes',
		'tags'
	);
	
	foreach ( $custom_blocks as $block ) {
		register_block_type( __DIR__ . '/build/blocks/' . $block );
	}
}
add_action( 'init', 'create_block_talkwave_block_init' );

function multiblock_enqueue_block_assets() {
	wp_enqueue_script(
		'talkwave-editor-js',
		plugin_dir_url( __FILE__ ) . 'build/block-editor.js',
		array('wp-blocks', 'wp-components', 'wp-data', 'wp-dom-ready', 'wp-edit-post', 'wp-element', 'wp-i18n', 'wp-plugins'),
		null,
		false
	);
	
	wp_enqueue_style(
		'talkwave-editor-css',
		plugin_dir_url( __FILE__ ) . 'build/block-editor.css',
		array(),
		null
	);
}
add_action( 'enqueue_block_editor_assets', 'multiblock_enqueue_block_assets' );

function multiblock_enqueue_frontend_assets() {
	wp_enqueue_style(
		'talkwave-frontend-css',
		plugin_dir_url( __FILE__ ) . 'build/style-block-editor.css',
	);

	wp_enqueue_script(
		'talkwave-frontend-js',
		plugin_dir_url( __FILE__ ) . 'build/block-frontend.js',
		array(),
		null,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'multiblock_enqueue_frontend_assets' );