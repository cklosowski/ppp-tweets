<?php

/**
 * Alters the placeholder text on the 'title' field for the ppp_tweets post type
 * @param  string $input The string to translate
 * @return string        The string to display
 */
function ppp_tweets_title_placeholder_text( $input ) {

	global $post_type;
	if( 'ppp_tweet' == $post_type && is_admin() && 'Enter title here' == $input ) {
		return __( 'Enter Tweet text', 'ppp-tweets-txt' );
	}

	if( 'ppp_tweet' == $post_type && is_admin() && 'Set featured image' == $input ) {
		return __( 'Attach an image to this Tweet', 'ppp-tweets-txt' );
	}

	return $input;
}
add_filter( 'gettext','ppp_tweets_title_placeholder_text' );

/**
 * Alters the columsn for the ppp_tweet list table
 * @param  array $columns Array of columns
 * @return array          Array of modified columns
 */
function ppp_tweets_columns( $columns ) {
	unset( $columns['author'] );
	unset( $columns['date'] );
	unset( $columns['likes'] );
	$columns['tweet_status']   = __( 'Status', 'ppp-tweets-txt' );
	$columns['tweet_link']     = __( 'Link', 'ppp-tweets-txt' );
	$columns['image_attached'] = __( 'Image', 'ppp-tweets-txt' );
	$columns['tweet_date']     = __( 'Tweet Date', 'ppp-tweets-txt' );
	return $columns;
}
add_filter( 'manage_ppp_tweet_posts_columns' , 'ppp_tweets_columns' );
