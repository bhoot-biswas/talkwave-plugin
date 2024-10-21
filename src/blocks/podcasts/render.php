<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	// Get all terms from the 'series' taxonomy
	$series_terms = get_terms( array(
		'taxonomy'   => 'series',
		'hide_empty' => false, // Show even if there are no podcasts
	) );

	// Check if terms exist
	if ( ! empty( $series_terms ) && ! is_wp_error( $series_terms ) ) : ?>
		<div class="podcast-series-list">
			<?php foreach ( $series_terms as $term ) :
				// Get term meta values
				$term_id = $term->term_id;
				$title = $term->name;
				$description = term_description( $term_id, 'series' ); // Get the description
				$description = truncate_description( $description, 20 ); // Limit to 20 words
				$image_url = ssp_series_repository()->get_image_src( $term, 'full' ); // Get the image URL using your function
				$term_link = get_term_link( $term ); // Get the term link (single term page URL)
			?>
				<div class="podcast-series-item">
					<!-- Image link with play icon -->
					<a href="<?php echo esc_url( $term_link ); ?>" class="series-image-link">
						<div class="image-wrapper">
							<img class="series-image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>">
							<div class="play-icon">
								<span>▶️</span> <!-- You can replace this with a play icon using an icon library or SVG -->
							</div>
						</div>
					</a>

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
