<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	// Define the pages for the sidebar navigation
	$pages = array(
		'home'         => array(
			'title' => __( 'Home' ),
			'url'   => home_url( '/' ),
			'icon'  => 'house-door',
		),
		'all-episodes' => array(
			'title' => __( 'All Episodes' ),
			'url'   => home_url( '/all-episodes/' ),
			'icon'  => 'mic',
		),
		'library'      => array(
			'title' => __( 'Library' ),
			'url'   => home_url( '/library/' ),
			'icon'  => 'collection-play',
		),
		'news'         => array(
			'title' => __( 'News' ),
			'url'   => home_url( '/news/' ),
			'icon'  => 'newspaper',
		),
	);

	// Function to check if a page is active
	function is_page_active( $page_slug ) {
		if ( $page_slug === 'home' && is_front_page() ) {
			return 'active';
		}
		
		return is_page( $page_slug ) ? 'active' : '';
	}
	?>

	<ul class="sidebar-navigation">
		<?php foreach ( $pages as $slug => $page ) : ?>
			<li class="<?php echo is_page_active( $slug ); ?>">
				<a href="<?php echo esc_url( $page['url'] ); ?>">
					<?php
					// Call the function to get the icon for the current page
					echo Talkwave\Icon_Display::get_icon( $page['icon'], array( 'aria-hidden' => 'true' ) );
					?>
					<?php echo esc_html( $page['title'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
