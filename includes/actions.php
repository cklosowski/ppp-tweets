<?php

function ppp_tweets_change_image_box() {
	remove_meta_box( 'postimagediv', 'ppp_tweet', 'side' );
	add_meta_box( 'postimagediv', __( 'Add Media', 'ppp-tweets-txt' ), 'post_thumbnail_meta_box', 'ppp_tweet', 'normal', 'high' );
}
add_action( 'do_meta_boxes', 'ppp_tweets_change_image_box' );

function ppp_tweets_save_tweet( $post_id ) {
	if ( 'ppp_tweet' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( !isset( $_POST['_ppp_tweets_link'] ) && !isset( $_POST['_ppp_tweets_link_post_id'] ) ) {
		return;
	}

	$validated_url = wp_http_validate_url( $_POST['_ppp_tweets_link'] );
	if ( false === $validated_url || ( isset( $_POST['_ppp_tweets_link_post_id'] ) && !empty( $_POST['_ppp_tweets_link_post_id'] ) ) ) {
		update_post_meta( $post_id, '_ppp_tweets_link', '' );
	} else {
		update_post_meta( $post_id, '_ppp_tweets_link', $validated_url );
	}

	if ( isset( $_POST['_ppp_tweets_link_post_id'] ) ) {
		update_post_meta( $post_id, '_ppp_tweets_link_post_id', (int)$_POST['_ppp_tweets_link_post_id'] );
	}

}
add_action( 'save_post', 'ppp_tweets_save_tweet', 99, 1 );

function ppp_tweets_custom_columns( $column, $post_id ) {
	if ( 'ppp_tweet' !== get_post_type( $post_id ) ) {
		return;
	}

	switch ( $column ) {
		case 'tweet_status':
			$status = get_post_status( $post_id );
			if ( $status === 'draft' ) {
				$class = 'lightbulb';
			} elseif ( $status === 'future' ) {
				$class = 'clock';
			} elseif ( $status === 'publish' ) {
				$tweet_status = get_post_meta( $post_id, '_ppp_tweets_status', true );
				// Check for legacy nested status (from first push)
				$tweet_status = is_array( $tweet_status ) && isset( $tweet_status['twitter'] ) ? $tweet_status['twitter'] : $tweet_status;
				if ( isset( $tweet_status->id_str ) ) {
					$class = 'yes';
					$link  = 'https://twitter.com/' . $tweet_status->user->screen_name . '/status/' . $tweet_status->id_str;
				} elseif( isset( $tweet_status->errors ) ) {
					$class   = 'no';
					$message = $tweet_status->errors[0]->message;
				}
			} elseif ( $status === 'pending' ) {
				$class = 'editor-help';
			}

			if ( isset( $link ) ) {
				echo '<span class="dashicons dashicons-' . $class . '"></span>';
				echo '&nbsp;<a title="' . __( 'View Tweet', 'ppp-tweets-txt' ) . '" href="' . $link . '" target="_blank"><span class="dashicons dashicons-external"></span></a>';
			} elseif ( isset( $message ) ) {
				echo '<span title="' . $message . '" class="dashicons dashicons-' . $class . '"></span>';
			} else {
				echo '<span class="dashicons dashicons-' . $class . '"></span>';
			}
			break;

		case 'tweet_link':
			$link = get_post_meta( $post_id, '_ppp_tweets_link', true );
			$post = (int)get_post_meta( $post_id, '_ppp_tweets_link_post_id', true );
			if ( !empty( $link ) ) {
				echo '<a href="' . $link . '" target="_blank">' . $link . '</a>';
			} elseif ( !empty( $post ) ) {
				$permalink = get_permalink( $post );
				echo '<a href="' . $permalink . '" target="_blank">' . get_the_title( $post ) . '</a>';
			} else {
				echo '<em>' . __( 'No Link', 'ppp-tweets-txt' ) . '</em>';
			}
			break;

		case 'image_attached':
			if ( has_post_thumbnail( $post_id ) ) {
				echo '<span class="dashicons dashicons-yes"></span>';
			} else {
				echo '<em>' . __( 'No Image Attached', 'ppp-tweets-txt' ) . '</em>';
			}
			break;

		case 'tweet_date':
			$date = get_the_date( get_option( 'date_format', $post_id ) );
			$time = get_the_time( get_option( 'time_format', $post_id ) );
			echo $date . ' ' . $time;
			break;
	}

}
add_action( 'manage_posts_custom_column' , 'ppp_tweets_custom_columns', 10, 2 );
