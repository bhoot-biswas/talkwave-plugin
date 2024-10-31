<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	$recent_episode_ids = Talkwave\get_recently_played_episodes();

	if ( empty( $recent_episode_ids ) ) :
		?>
		<p><?php _e( 'No recently played episodes.' ); ?></p>
		<?php
	else :
		// Prepare a query to fetch the episodes using get_posts
		$args = array(
			'post_type'      => 'podcast', // The post type
			'post__in'       => $recent_episode_ids, // Use the IDs from the cookie
			'orderby'        => 'post__in', // Preserve the order of IDs from the cookie
			'posts_per_page' => -1, // Get all episodes
		);

		// The query
		$recently_played_episodes = get_posts( $args );

		// Display the episodes if any exist
		if ( empty( $recently_played_episodes ) ) {
			?>
			<p><?php _e( 'No recently played episodes.' ); ?></p>
			<?php
		} else {
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

			$playlist_id    = 'recently-played-episodes';
			$playlist_items = array();

			foreach ( $recently_played_episodes as $episode ) {
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
				class="recently-played-episodes"
				data-wp-interactive="talkwave"
				data-wp-init="callbacks.setPlaylist"
				<?php
					echo wp_interactivity_data_wp_context(
						array(
							'playlist_id' => $playlist_id,
							'playlist'    => $playlist_items,
						)
					);
				?>
			>
				<?php
				global $post;

				foreach ( $recently_played_episodes as $index => $post ) :
					setup_postdata( $post );
					$album_art = ssp_episode_controller()->get_album_art( get_the_ID(), 'medium' );
					?>
					<div 
						class="recently-played-episode"
						data-wp-class--is-loading="state.isLoading"
						data-wp-class--is-playing="state.isPlaying"
						<?php
						echo wp_interactivity_data_wp_context(
							array(
								'playlist_id' => $playlist_id,
								'index'       => $index,
							)
						);
						?>
					>
						<!-- Image link with play icon -->
						<div class="image-wrapper">
							<img 
								src="<?php echo esc_attr( apply_filters( 'ssp_album_art_cover', $album_art['src'], get_the_ID() ) ); ?>"
								alt="<?php echo ! empty( $album_art['alt'] ) ? esc_attr( $album_art['alt'] ) : esc_attr( strip_tags( $post->post_title ) ); ?>"
								title="<?php echo esc_attr( strip_tags( $post->post_title ) ); ?>"
							>
	
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
	
						<div class="episode-content">
							<?php
								the_title(
									sprintf( '<h2 class="episode-title"><a href="%s" rel="bookmark">', esc_attr( esc_url( get_permalink() ) ) ),
									'</a></h2>'
								);
							?>
						</div>
					</div>
					<?php
				endforeach;
				wp_reset_postdata();
				?>
			</div>
			<?php
		}
	endif;
	?>
</div>
