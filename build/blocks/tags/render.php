<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	// Get all terms from the 'post_tag' taxonomy
	$tags = get_tags( array(
		'hide_empty' => false, // Show even if there are no posts with the tag
	) );

	// Check if there are any tags
	if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) : ?>

		<ul class="tags-list">
			<?php foreach ( $tags as $tag ) : 
				$tag_link = get_tag_link( $tag->term_id ); // Get the tag link
				$tag_name = $tag->name; // Tag name
				?>
				<li class="tag-item">
					<a href="<?php echo esc_url( $tag_link ); ?>" class="tag-link">
						<?php echo esc_html( $tag_name ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

	<?php else : ?>
		<p>No tags found.</p>
	<?php endif; ?>
</div>
