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
	// Base arguments for retrieving terms from the 'series' taxonomy
	$args = array(
		'taxonomy'   => 'series',
		'hide_empty' => false, // Show even if there are no podcasts
	);

	// Conditionally add meta_query to show only featured items on the homepage
	if ( is_front_page() ) {
		$args['meta_query'] = array(
			array(
				'key'     => 'featured',
				'value'   => '1',
				'compare' => '=',
			),
		);
	}

	// Get all terms from the 'series' taxonomy
	$series_terms = get_terms( $args );

	// Check if terms exist
	if ( ! empty( $series_terms ) && ! is_wp_error( $series_terms ) ) :
		?>
		<div class="podcast-series-list">
			<?php
			foreach ( $series_terms as $term ) :
				// Get term meta values
				$term_id        = $term->term_id;
				$title          = $term->name;
				$description    = term_description( $term_id, 'series' ); // Get the description
				$description    = Talkwave\truncate_description( $description, 20 ); // Limit to 20 words
				$image_url      = ssp_series_repository()->get_image_src( $term, 'full' ); // Get the image URL using your function
				$term_link      = get_term_link( $term ); // Get the term link (single term page URL)
				$playlist_items = Talkwave\get_playlist_items(
					array(
						'series'  => $term->slug,
						'include' => array(),
						'exclude' => array(),
						'order'   => 'desc',
						'orderby' => 'date',
						'limit'   => 10,
					)
				);
				?>
				<div
					class="podcast-series-item"
					data-wp-init="callbacks.setPlaylist"
					data-wp-class--is-loading="state.playlistLoading"
					data-wp-class--is-playing="state.playlistPlaying"
					<?php
					echo wp_interactivity_data_wp_context(
						array(
							'playlist_id' => $term->slug,
							'playlist'    => $playlist_items,
						)
					);
					?>
				>
					<!-- Image link with play icon -->
					<div class="image-wrapper">
						<img class="series-image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>">
						
						<div class="image-overlay">
							<button class="talkwave-button talkwave-button--play" data-wp-on--click="actions.handlePlaylist">
								<?php echo Talkwave\Icon_Display::get_icon( 'play', array( 'aria-hidden' => 'true' ) ); ?>
								<span class="screen-reader-text">Play Podcast</span>
							</button>
							<button class="talkwave-button talkwave-button--pause" data-wp-on--click="actions.handlePlaylist">
								<?php echo Talkwave\Icon_Display::get_icon( 'pause', array( 'aria-hidden' => 'true' ) ); ?>
								<span class="screen-reader-text">Pause Podcast</span>
							</button>

							<div class="loader"></div>
						</div>
					</div>

					<!-- Title link -->
					<a href="<?php echo esc_url( $term_link ); ?>" class="series-title-link">
						<h2 class="series-title"><?php echo esc_html( $title ); ?></h2>
					</a>

					<?php if ( $description ) : ?>
						<div class="series-description">
							<?php echo wp_kses_post( wpautop( $description ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

	<?php else : ?>
		<p>No podcast series found.</p>
	<?php endif; ?>
</div>
