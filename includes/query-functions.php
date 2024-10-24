<?php
namespace Talkwave;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function get_playlist_items( $atts = array(), $page = 1 ) {
	$episode_repository = ssp_episode_controller()->episode_repository;

	$podcast  = get_term_by( 'slug', $atts['series'], 'series' );
	$episodes = $episode_repository->get_episodes( array_merge( $atts, compact( 'page' ) ) );
	$items    = array();

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

	foreach ( $episodes as $episode ) {
		$player_data                  = $episode_repository->get_player_data( $episode->ID );
		$player_data['podcast_title'] = $podcast->name;
		$items[]                      = array_intersect_key( $player_data, array_flip( $allowed_keys ) );
	}

	return $items;
}
