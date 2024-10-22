<?php
// Declare namespace
namespace Talkwave;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Talkwave {
	// Holds the singleton instance.
	private static $instance = null;

	// Private constructor to prevent multiple instances.
	private function __construct() {
		$this->includes();
		// Initialize your plugin here.
		$this->init();
	}

	// Prevent instance from being cloned.
	private function __clone() {
	}

	// Prevent instance from being unserialized.
	public function __wakeup() {
	}

	// Returns the singleton instance.
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function includes() {
		require_once plugin_dir_path( TALKWAVE_PLUGIN_FILE ) . 'includes/functions.php';
	}

	// Initialize the plugin.
	private function init() {
		// Plugin initialization code here.
        add_action( 'init', [$this, 'register_blocks'] );
        add_action( 'enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets'] );
		add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'] );
	}

    /**
     * Registers the block using the metadata loaded from the `block.json` file.
     * Behind the scenes, it registers also all assets so they can be enqueued
     * through the block editor in the corresponding context.
     *
     * @see https://developer.wordpress.org/reference/functions/register_block_type/
     */
    public function register_blocks() {
        $custom_blocks = array (
            'podcasts',
            'episodes',
            'tags',
            'episode-image'
        );
        
        foreach ( $custom_blocks as $block ) {
            register_block_type( plugin_dir_path( TALKWAVE_PLUGIN_FILE ) . 'build/blocks/' . $block );
        }
    }

    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'talkwave-editor-js',
            plugin_dir_url( TALKWAVE_PLUGIN_FILE ) . 'build/block-editor.js',
            array('wp-blocks', 'wp-components', 'wp-data', 'wp-dom-ready', 'wp-edit-post', 'wp-element', 'wp-i18n', 'wp-plugins'),
            null,
            false
        );
        
        wp_enqueue_style(
            'talkwave-editor-css',
            plugin_dir_url( TALKWAVE_PLUGIN_FILE ) . 'build/block-editor.css',
            array(),
            null
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_style(
            'talkwave-frontend-css',
            plugin_dir_url( TALKWAVE_PLUGIN_FILE ) . 'build/style-block-editor.css',
        );
    
        wp_register_script(
            'howler',
            'https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.4/howler.core.min.js',
        );
    
        wp_enqueue_script_module(
            'talkwave-frontend-js',
            plugin_dir_url( TALKWAVE_PLUGIN_FILE ) . 'src/block-frontend.js',
            array(array(
                'id' => '@wordpress/interactivity',
                'import' => 'dynamic' // Optional.
            ), 'howler'),
        );
    }
}