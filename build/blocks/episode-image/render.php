<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<figure <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	if ( ! isset( $block->context['postId'] ) ) {
		return '';
	}
	$episode_ID = $block->context['postId'];
	$episode = get_post( $episode_ID );

	$ssp_episode_controller = ssp_episode_controller();

	$episode_image = $ssp_episode_controller->get_album_art( $episode_ID, 'medium' );

	if ( ! $episode_image ) {
		return '';
	}

	$link_target    = '_self';
	$rel 		  = '';
	$height = '';
	
	// Image markup
    $episode_image = sprintf(
        '<img src="%1$s" alt="%2$s" class="podcast-episode-thumbnail">',
        esc_url( $episode_image['src'] ),
        esc_attr( $episode->post_title )
    );
	
	// Overlay markup with SVG play button
    $overlay_markup = '
        <div class="play-icon-overlay">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="36" height="36">
                <path d="M8 5v14l11-7z"/>
            </svg>
        </div>';
		
	printf(
		'<a href="%1$s" target="%2$s" %3$s %4$s>%5$s%6$s</a>',
		get_the_permalink( $episode_ID ),
		esc_attr( $link_target ),
		$rel,
		$height,
		$episode_image,
		$overlay_markup
	);
	?>
</figure>
