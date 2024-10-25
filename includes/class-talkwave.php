<?php
// Declare namespace
namespace Talkwave;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
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
		require_once plugin_dir_path( TALKWAVE_PLUGIN_FILE ) . 'includes/query-functions.php';
		require_once plugin_dir_path( TALKWAVE_PLUGIN_FILE ) . 'includes/class-icon-display.php';
	}

	// Initialize the plugin.
	private function init() {
		// Plugin initialization code here.
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'wp_footer', array( $this, 'audio_player_html' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers also all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	public function register_blocks() {
		$custom_blocks = array(
			'layout',
			'podcasts',
			'episodes',
			'tags',
			'episode-image',
			'sidebar-navigation',
		);

		foreach ( $custom_blocks as $block ) {
			register_block_type( plugin_dir_path( TALKWAVE_PLUGIN_FILE ) . 'build/blocks/' . $block );
		}
	}

    public function audio_player_html() {
        ?>
        <div
			class="talkwave-player"
            data-wp-interactive="talkwave"
			data-wp-class--is-loading="state.loading"
			data-wp-class--is-playing="state.playing"
			data-wp-class--is-muted="state.muted"
        >
            <div class="talkwave-player__wrap">
				<div class="talkwave-player__current">
					<div class="talkwave-player__artwork talkwave-player__artwork--218">
						<img decoding="async" data-wp-bind--src="state.episodeImage" alt="" title="">
					</div>
					<div class="talkwave-player__details">
						<div class="talkwave-player__podcast-title" data-wp-text="state.podcastTitle"></div>
						<div class="talkwave-player__episode-title" data-wp-text="state.episodeTitle"></div>
					</div>
				</div>
				
				<div class="talkwave-player__controls">
					<div class="talkwave-player__playback-controls">
						<button data-skip="-10" class="talkwave-button talkwave-button--rewind" title="Rewind 10 seconds" data-wp-on--click="actions.rewind">
							<?php echo Icon_Display::get_icon( 'arrow-counterclockwise', array( 'aria-hidden' => 'true' ) ); ?>
							<span class="screen-reader-text">Rewind 10 Seconds</span>
						</button>

						<button title="Play" class="talkwave-button talkwave-button--play" data-wp-on--click="actions.play">
							<?php echo Icon_Display::get_icon( 'play', array( 'aria-hidden' => 'true' ) ); ?>
							<span class="screen-reader-text">Play Episode</span>
						</button>
						<button title="Pause" class="talkwave-button talkwave-button--pause" data-wp-on--click="actions.pause">
							<?php echo Icon_Display::get_icon( 'pause', array( 'aria-hidden' => 'true' ) ); ?>
							<span class="screen-reader-text">Pause Episode</span>
						</button>

						<div class="loader"></div>

						<button data-skip="30" class="talkwave-button talkwave-button--fastforward" title="Fast Forward 30 seconds" data-wp-on--click="actions.fastForward">
							<?php echo Icon_Display::get_icon( 'arrow-clockwise', array( 'aria-hidden' => 'true' ) ); ?>
							<span class="screen-reader-text">Fast Forward 30 seconds</span>
						</button>
					</div>

					<div class="talkwave-player__progress">
						<time class="talkwave-player__timer" data-wp-text="state.timerHTML"></time>
						<div class="talkwave-player__progress-bar" role="progressbar" title="Seek" data-wp-on--click="actions.scrub">
							<span class="talkwave-player__progress-filled" style="flex-basis: 13.8504%;"></span>
						</div>
						<time class="talkwave-player__duration" data-wp-text="state.durationHTML"></time>
					</div>
				</div>

				<div class="talkwave-player__options">
					<button class="talkwave-button talkwave-button--speed" title="Playback Speed" data-wp-text="state.getRate" data-wp-on--click="actions.handleRate"></button>
					<button class="talkwave-button talkwave-button--mute" title="Mute" data-wp-on--click="actions.toggleMute">
						<?php echo Icon_Display::get_icon( 'volume-mute', array( 'aria-hidden' => 'true' ) ); ?>
						<span class="screen-reader-text">Mute Episode</span>
					</button>

					<button class="talkwave-button talkwave-button--unmute" title="Unmute" data-wp-on--click="actions.toggleMute">
						<?php echo Icon_Display::get_icon( 'volume-up', array( 'aria-hidden' => 'true' ) ); ?>
						<span class="screen-reader-text">Unmute Episode</span>
					</button>
				</div>
			</div>
        </div>
        <?php
    }

	public function enqueue_scripts() {
		wp_enqueue_style(
			'talkwave-frontend',
			plugin_dir_url( TALKWAVE_PLUGIN_FILE ) . 'build/frontend.css',
		);

		wp_enqueue_script_module(
			'talkwave-frontend',
			plugin_dir_url( TALKWAVE_PLUGIN_FILE ) . 'build/frontend.js',
			array(
				array(
					'id'     => '@wordpress/interactivity',
					'import' => 'dynamic',
				)
			),
		);
	}
}