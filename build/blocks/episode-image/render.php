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
	?>
	<img src="<?php echo esc_url( $episode_image['src'] ); ?>" alt="<?php echo $episode->post_title ?>">
</figure>
