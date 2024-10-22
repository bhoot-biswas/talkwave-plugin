<?php
namespace Talkwave;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Function to limit description to a maximum of 20 words
function truncate_description( $text, $limit = 20 ) {
	$words = explode( ' ', strip_tags( $text ) ); // Remove HTML tags and split into words
	if ( count( $words ) > $limit ) {
		$text = implode( ' ', array_slice( $words, 0, $limit ) ) . '...'; // Limit to 20 words and add ellipsis
	}
    
	return $text;
}