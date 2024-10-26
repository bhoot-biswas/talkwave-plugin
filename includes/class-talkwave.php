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
			'sidebar-navigation',
			'podcasts',
			'episodes',
			'tags',
		);

		foreach ( $custom_blocks as $block ) {
			register_block_type( plugin_dir_path( TALKWAVE_PLUGIN_FILE ) . 'build/blocks/' . $block );
		}
	}

	public function audio_player_html() {
		if ( ! ( is_front_page() || is_tax( 'series' ) ) ) {
			return;
		}

		?>
		<div
			class="talkwave-player"
			data-wp-interactive="talkwave"
			data-wp-class--is-loading="state.loading"
			data-wp-class--is-playing="state.playing"
			data-wp-class--is-muted="state.muted"
		>
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
					<button data-skip="-10" class="talkwave-button talkwave-button--rewind" title="<?php echo esc_attr__( 'Rewind 10 seconds', 'talkwave' ); ?>" data-wp-on--click="actions.rewind">
						<?php echo Icon_Display::get_icon( 'arrow-counterclockwise', array( 'aria-hidden' => 'true' ) ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Rewind 10 Seconds', 'talkwave' ); ?></span>
					</button>

					<button title="<?php echo esc_attr__( 'Play', 'talkwave' ); ?>" class="talkwave-button talkwave-button--play" data-wp-on--click="actions.play">
						<?php echo Icon_Display::get_icon( 'play', array( 'aria-hidden' => 'true' ) ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Play Episode', 'talkwave' ); ?></span>
					</button>
					<button title="<?php echo esc_attr__( 'Pause', 'talkwave' ); ?>" class="talkwave-button talkwave-button--pause" data-wp-on--click="actions.pause">
						<?php echo Icon_Display::get_icon( 'pause', array( 'aria-hidden' => 'true' ) ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Pause Episode', 'talkwave' ); ?></span>
					</button>

					<div class="loader"></div>

					<button data-skip="30" class="talkwave-button talkwave-button--fastforward" title="<?php echo esc_attr__( 'Fast Forward 30 seconds', 'talkwave' ); ?>" data-wp-on--click="actions.fastForward">
						<?php echo Icon_Display::get_icon( 'arrow-clockwise', array( 'aria-hidden' => 'true' ) ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Fast Forward 30 seconds', 'talkwave' ); ?></span>
					</button>
				</div>

				<div class="talkwave-player__progress">
					<time class="talkwave-player__timer" data-wp-text="state.timerHTML"></time>
					<div class="talkwave-player__progress-bar" role="progressbar" title="<?php echo esc_attr__( 'Seek', 'talkwave' ); ?>" data-wp-on--click="actions.scrub">
						<span class="talkwave-player__progress-filled" data-wp-style--width="state.progress"></span>
					</div>
					<time class="talkwave-player__duration" data-wp-text="state.durationHTML"></time>
				</div>
			</div>

			<div class="talkwave-player__options">
				<button class="talkwave-button talkwave-button--speed" title="<?php echo esc_attr__( 'Playback Speed', 'talkwave' ); ?>" data-wp-text="state.getRate" data-wp-on--click="actions.handleRate"></button>
				<button class="talkwave-button talkwave-button--mute" title="<?php echo esc_attr__( 'Mute', 'talkwave' ); ?>" data-wp-on--click="actions.toggleMute">
					<?php echo Icon_Display::get_icon( 'volume-mute', array( 'aria-hidden' => 'true' ) ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Mute Episode', 'talkwave' ); ?></span>
				</button>

				<button class="talkwave-button talkwave-button--unmute" title="<?php echo esc_attr__( 'Unmute', 'talkwave' ); ?>" data-wp-on--click="actions.toggleMute">
					<?php echo Icon_Display::get_icon( 'volume-up', array( 'aria-hidden' => 'true' ) ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Unmute Episode', 'talkwave' ); ?></span>
				</button>
			</div>
		</div>
		<?php
	}

	public function enqueue_scripts() {
		wp_enqueue_style(
			'talkwave-frontend',
			plugin_dir_url( TALKWAVE_PLUGIN_FILE ) . 'build/frontend.css',
		);

		if ( is_front_page() || is_tax( 'series' ) ) {
			wp_enqueue_script_module(
				'talkwave-frontend',
				plugin_dir_url( TALKWAVE_PLUGIN_FILE ) . 'build/frontend.js',
				array(
					array(
						'id'     => '@wordpress/interactivity',
						'import' => 'dynamic',
					),
				),
			);
		}
	}
}
