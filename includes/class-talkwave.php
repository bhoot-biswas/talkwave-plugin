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
		add_action( 'wp', array( $this, 'track_recently_played_episodes' ), -99 );
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'series_add_form_fields', array( $this, 'series_add_featured_field' ) );
		add_action( 'series_edit_form_fields', array( $this, 'series_edit_featured_field' ) );
		add_action( 'edited_series', array( $this, 'save_series_meta' ) );
		add_action( 'created_series', array( $this, 'save_series_meta' ) );
		add_action( 'wp_footer', array( $this, 'audio_player_html' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Track recently played episodes.
	 *
	 * This function is hooked into the 'wp' action with a priority of -99 to ensure
	 * it runs before the main query is executed. It checks if the current request
	 * is for a podcast episode download and, if so, it tracks the episode ID in a
	 * cookie.
	 */
	public function track_recently_played_episodes() {
		if ( ! ssp_is_podcast_download() ) {
			return;
		}

		global $wp_query;

		// Get requested episode ID
		$episode_id = intval( $wp_query->query_vars['podcast_episode'] );

		if ( isset( $episode_id ) && $episode_id ) {
			// Get episode post object
			$episode = get_post( $episode_id );

			// Make sure we have a valid episode post object
			if ( ! $episode || ! is_object( $episode ) || is_wp_error( $episode ) || ! isset( $episode->ID ) ) {
				return;
			}

			// Retrieve existing played episodes from the cookie if it exists
			$recent_episodes = isset( $_COOKIE['recently_played_episodes'] ) ? explode( ',', $_COOKIE['recently_played_episodes'] ) : array();

			// Add the new episode ID at the beginning of the array
			array_unshift( $recent_episodes, $episode_id );

			// Remove duplicates and limit the array to the last 5 episodes
			$recent_episodes = array_unique( $recent_episodes );
			$recent_episodes = array_slice( $recent_episodes, 0, 5 );

			// Set the updated array as a cookie, imploding it into a string
			setcookie( 'recently_played_episodes', implode( ',', $recent_episodes ), time() + ( 30 * 24 * 60 * 60 ), '/' ); // Expires in 1 month (30 days)
		}
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
			'recently-played',
		);

		foreach ( $custom_blocks as $block ) {
			register_block_type( plugin_dir_path( TALKWAVE_PLUGIN_FILE ) . 'build/blocks/' . $block );
		}
	}

	public function series_add_featured_field() {
		?>
		<fieldset>
			<legend class="screen-reader-text"><span><?php echo __( 'Featured' ); ?></span></legend>
			<label for="term_meta_featured">
				<input name="term_meta[featured]" type="checkbox" id="term_meta_featured" value="1">
				<?php echo __( 'Mark as featured' ); ?>
			</label>
			<p class="description"><?php echo __( 'Check this box to mark as featured.' ); ?></p>
		</fieldset>
		<?php
	}

	public function series_edit_featured_field( $term ) {
		$term_id  = $term->term_id;
		$featured = get_term_meta( $term_id, 'featured', true );
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta_featured"><?php echo __( 'Featured' ); ?></label>
			</th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo __( 'Featured' ); ?></span></legend>
					<label for="term_meta_featured">
						<input name="term_meta[featured]" type="checkbox" id="term_meta_featured" value="1" <?php checked( $featured, 1 ); ?>>
						<?php echo __( 'Mark as featured' ); ?>
					</label>
					<p class="description"><?php echo __( 'Check this box to mark as featured.' ); ?></p>
				</fieldset>
			</td>
		</tr>
		<?php
	}

	public function save_series_meta( $term_id ) {
		$is_featured = isset( $_POST['term_meta']['featured'] ) ? 1 : 0;
		update_term_meta( $term_id, 'featured', $is_featured );
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
					<button class="talkwave-button talkwave-button--rewind" title="<?php echo esc_attr__( 'Rewind 10 seconds', 'talkwave' ); ?>" data-skip="-10" data-wp-on--click="actions.rewind">
						<?php echo Icon_Display::get_icon( 'arrow-counterclockwise', array( 'aria-hidden' => 'true' ) ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Rewind 10 Seconds', 'talkwave' ); ?></span>
					</button>

					<button class="talkwave-button talkwave-button--prev" title="<?php echo esc_attr__( 'Previous Episode', 'talkwave' ); ?>" data-direction="prev" data-wp-on--click="actions.skip">
						<?php echo Icon_Display::get_icon( 'skip-start', array( 'aria-hidden' => 'true' ) ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Previous Episode', 'talkwave' ); ?></span>
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

					<button class="talkwave-button talkwave-button--next" title="<?php echo esc_attr__( 'Next Episode', 'talkwave' ); ?>" data-direction="next" data-wp-on--click="actions.skip">
						<?php echo Icon_Display::get_icon( 'skip-end', array( 'aria-hidden' => 'true' ) ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Next Episode', 'talkwave' ); ?></span>
					</button>

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
