<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<div 
	<?php echo get_block_wrapper_attributes(); ?>
	data-wp-interactive="talkwave"
>
	<?php
	// Arguments for querying latest episodes
	$args = array(
		'post_type'      => 'podcast',
		'posts_per_page' => 8,         // Adjust to the number of episodes you want to display
		'order'          => 'DESC',    // Show latest first
	);

	// The query
	$latest_episodes = get_posts( $args );

	// Check if there are any episodes
	if ( $latest_episodes ) :
		$allowed_keys = array(
			'episode_id',
			'album_art',
			'podcast_title',
			'title',
			'date',
			'duration',
			'excerpt',
			'audio_file',
		);

		$playlist_items = array();

		foreach ( $latest_episodes as $episode ) {
			$player_data = ssp_episode_controller()->episode_repository->get_player_data( $episode->ID );
			$terms       = get_the_terms( $episode->ID, 'series' );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$podcast_title = $terms[0]->name; // Use the first available term
			}

			// Set the podcast title in player data
			$player_data['podcast_title'] = $podcast_title;

			$playlist_items[] = array_intersect_key( $player_data, array_flip( $allowed_keys ) );
		}
		?>
		<div 
			class="episode-grid"
			data-wp-init="callbacks.setPlaylist"
			<?php
			echo wp_interactivity_data_wp_context(
				array(
					'playlist_id' => 'latest-episodes',
					'playlist'    => $playlist_items,
				)
			);
			?>
		>
			<?php
			global $post;

			foreach ( $latest_episodes as $index => $post ) :
				setup_postdata( $post );
				?>
				<div 
					class="episode-item"
                    data-wp-class--is-loading="state.isLoading"
					data-wp-class--is-playing="state.isPlaying"
					<?php
					echo wp_interactivity_data_wp_context(
						array(
							'playlist_id' => 'latest-episodes',
							'index'       => $index,
						)
					);
					?>
				>
					<!-- Image link with play icon -->
					<div class="image-wrapper">
						<?php echo ssp_episode_image( get_the_ID(), 'medium' ); ?>

						<div class="image-overlay">
							<button class="talkwave-button talkwave-button--play" data-wp-on--click="actions.play">
								<?php echo Talkwave\Icon_Display::get_icon( 'play', array( 'aria-hidden' => 'true' ) ); ?>
								<span class="screen-reader-text">Play Episode</span>
							</button>
							<button class="talkwave-button talkwave-button--pause" data-wp-on--click="actions.pause">
								<?php echo Talkwave\Icon_Display::get_icon( 'pause', array( 'aria-hidden' => 'true' ) ); ?>
								<span class="screen-reader-text">Pause Episode</span>
							</button>

							<div class="loader"></div>
						</div>
					</div>

					<?php
						the_title(
							sprintf( '<h2 class="episode-title"><a href="%s" rel="bookmark">', esc_attr( esc_url( get_permalink() ) ) ),
							'</a></h2>'
						);
					?>
				</div>
				<?php
			endforeach;
			wp_reset_postdata();
			?>
		</div>
	<?php else : ?>
		<p><?php _e( 'No episodes found.' ); ?></p>
		<?php
	endif;
	?>
</div>
