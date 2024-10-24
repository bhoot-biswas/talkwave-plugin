<?php
// Declare the namespace
namespace Talkwave;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Icon_Display {

	// Path to the folder where SVG files are stored.
	private static $icon_path = '';

	/**
	 * Get the path to the icons folder.
	 *
	 * This method initializes the icon path if it hasn't been set yet.
	 */
	private static function get_icon_path() {
		// If the path is not set, initialize it.
		if ( empty( self::$icon_path ) ) {
			self::$icon_path = plugin_dir_path( TALKWAVE_PLUGIN_FILE ) . 'includes/icons/';
		}
		return self::$icon_path;
	}

	/**
	 * Render the SVG icon
	 *
	 * @param string $icon_name The name of the SVG file (without extension).
	 * @param array  $attributes Optional. Attributes for the SVG element, like class, title, etc.
	 * @return string The SVG icon or a placeholder if the icon doesn't exist.
	 */
	public static function get_icon( $icon_name, $attributes = array() ) {
		// Get the icon path (initializes if not done).
		$icon_file = self::get_icon_path() . $icon_name . '.svg';

		// Check if the SVG file exists.
		if ( file_exists( $icon_file ) ) {
			// Load the SVG content.
			$svg = file_get_contents( $icon_file );

			// Prepare additional attributes.
			$attr_string = '';
			if ( ! empty( $attributes ) ) {
				foreach ( $attributes as $key => $value ) {
					$attr_string .= esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
				}
			}

			// Inject the attributes into the <svg> tag.
			$svg = preg_replace( '/<svg /', '<svg ' . $attr_string, $svg, 1 );

			return $svg;
		}

		// Return a placeholder icon or error message if the file is not found.
		return '<!-- Icon ' . esc_html( $icon_name ) . ' not found -->';
	}
}
