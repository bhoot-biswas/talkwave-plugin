<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

// Arguments for querying latest episodes
$args = array(
	'post_type'      => 'podcast',
	'posts_per_page' => 8,
	'order'          => 'DESC',
);

if ( is_tax( 'series' ) ) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => 'series',
			'field'    => 'slug',
			'terms'    => get_queried_object()->slug,
		),
	);
}

// The query
$latest_episodes = get_posts( $args );

if ( ! $latest_episodes ) :
	?>
	<p><?php _e( 'No episodes found.' ); ?></p>
	<?php
else :
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

	$playlist_id    = 'latest-episodes';
	$playlist_items = array();

	if ( is_tax( 'series' ) ) {
		$term        = get_queried_object();
		$playlist_id = $term->slug;
	}

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
		<?php echo get_block_wrapper_attributes(); ?>
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
		if ( is_tax( 'series' ) ) :
			$term        = get_queried_object();
			$title       = $term->name;
			$description = get_the_archive_description();
			$image_url   = ssp_series_repository()->get_image_src( $term, 'full' );
			?>
			<div
				class="podcast-item"
				data-wp-class--is-loading="state.playlistLoading"
				data-wp-class--is-playing="state.playlistPlaying"
				<?php
				echo wp_interactivity_data_wp_context(
					array(
						'playlist_id' => $term->slug,
					)
				);
				?>
			>
				<div class="podcast-image-wrapper">
					<img class="podcast-image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>">
				</div>

				<div class="podcast-content">
					<h2 class="podcast-title"><?php echo esc_html( $title ); ?></h2>

					<?php if ( $description ) : ?>
						<div class="podcast-description">
							<?php echo wp_kses_post( wpautop( $description ) ); ?>
						</div>
					<?php endif; ?>

					<div class="talkwave-buttons">
						<button class="talkwave-button talkwave-button--play" data-wp-on--click="actions.handlePlaylist" data-wp-bind--disabled="state.playlistLoading">
							<?php echo Talkwave\Icon_Display::get_icon( 'play', array( 'aria-hidden' => 'true' ) ); ?>
							<span>Play</span>
						</button>
						<button class="talkwave-button talkwave-button--pause" data-wp-on--click="actions.handlePlaylist">
							<?php echo Talkwave\Icon_Display::get_icon( 'pause', array( 'aria-hidden' => 'true' ) ); ?>
							<span>Pause</span>
						</button>
					</div>
				</div>
			</div>
		<?php endif; ?>
		
		<div class="<?php echo is_archive() ? 'episode-list' : 'episode-grid'; ?>">
			<?php
			global $post;

			foreach ( $latest_episodes as $index => $post ) :
				setup_postdata( $post );
				$album_art = ssp_episode_controller()->get_album_art( get_the_ID(), 'medium' );
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
						
						<?php if ( is_archive() ) : ?>
							<!-- Episode meta -->
							<div class="episode-meta">
								<?php
								$episode_date = get_the_date( 'F j, Y' );
								$episode_time = get_the_date( 'g:i A' );
								?>
								<span class="episode-date"><?php echo esc_html( $episode_date ); ?></span>
								<span class="episode-time"><?php echo esc_html( $episode_time ); ?></span>
							</div>

							<!-- Episode excerpt -->
							<div class="episode-excerpt">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php
			endforeach;
			wp_reset_postdata();
			?>
		</div>
	</div>
	<?php
endif;
