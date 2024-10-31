<?php
namespace Talkwave;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function get_playlist_items( $atts = array(), $page = 1 ) {
	$episode_repository = ssp_episode_controller()->episode_repository;

	// If 'series' is provided, get the term by the slug outside the loop
	if ( isset( $atts['series'] ) && ! empty( $atts['series'] ) ) {
		$podcast       = get_term_by( 'slug', $atts['series'], 'series' );
		$podcast_title = $podcast ? $podcast->name : '';
	} else {
		$podcast_title = ''; // Initialize as empty in case we need to find it in the loop
	}

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
		$player_data = $episode_repository->get_player_data( $episode->ID );

		// If 'series' is not provided, fetch the first taxonomy term for this episode
		if ( empty( $atts['series'] ) ) {
			$terms = get_the_terms( $episode->ID, 'series' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$podcast_title = $terms[0]->name; // Use the first available term
			}
		}

		// Set the podcast title in player data
		$player_data['podcast_title'] = $podcast_title;

		$items[] = array_intersect_key( $player_data, array_flip( $allowed_keys ) );
	}

	return $items;
}

/**
 * Retrieve the list of recently played episodes from the cookie.
 *
 * This function checks if the 'recently_played_episodes' cookie is set and,
 * if so, returns an array of episode IDs. If the cookie is not set, it
 * returns an empty array.
 *
 * @return array An array of episode IDs representing recently played episodes.
 */
function get_recently_played_episodes() {
	// Check if the cookie exists
	if ( isset( $_COOKIE['recently_played_episodes'] ) ) {
		// Retrieve and explode the cookie into an array
		return explode( ',', $_COOKIE['recently_played_episodes'] );
	}

	// Return an empty array if no episodes are found
	return array();
}
