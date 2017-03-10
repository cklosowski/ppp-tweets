<?php

if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the metaboxe for Post Promoter Pro - Tweets
 * @return void
 */
function ppp_tweets_register_meta_box() {
	global $post;

	if ( $post->post_type !== 'ppp_tweet' ) {
		return;
	}

	add_meta_box( 'ppp_tweets_link_callback', __( 'Add a Link to this Tweet', 'ppp-tweets-txt' ), 'ppp_tweets_link_callback', 'ppp_tweet', 'normal', 'high' );
	add_meta_box( 'ppp_tweets_info_callback', __( 'Tweet Info', 'ppp-tweets-txt' ), 'ppp_tweets_info_callback', 'ppp_tweet', 'side', 'core' );

}
add_action( 'add_meta_boxes', 'ppp_tweets_register_meta_box', 12 );

/**
 * Display the Metabox for Post Promoter Pro - Tweets
 * @return void
 */
function ppp_tweets_link_callback() {
	global $post;
	$current_link = get_post_meta( $post->ID, '_ppp_tweets_link', true );
	if ( empty( $current_link ) ) {
		$current_link = '';
	}
	$current_post_id = get_post_meta( $post->ID, '_ppp_tweets_link_post_id', true );
	?>
	<p id="ppp-tweets-post-link" <?php echo !empty( $current_link ) ? 'style="display: none;"' : ''; ?>>
	<?php
	$args = array(
		'name'     => '_ppp_tweets_link_post_id',
		'id'       => 'ppp_tweets_link',
		'chosen'   => true,
		'selected' => false !== $current_post_id ? $current_post_id : 0
		);
	echo ppp_tweets_post_dropdown( $args );
	?>
	</p>
	<span id="ppp-tweets-ext-notice" <?php echo !empty( $current_link ) ? 'style="display: none;"' : ''; ?>>Or <a href="#" id="ppp-tweets-link-to-post"><?php _e( 'Link to an external URL', 'ppp-tweets-txt' ); ?></a></span>
	<p id="ppp-tweets-ext-link" <?php echo empty( $current_link ) ? 'style="display: none;"' : ''; ?>>
		<input display="none" id="ppp-tweets-ext-link-input" type="text" size="50" placeholder="Insert Link URL" name="_ppp_tweets_link" value="<?php echo $current_link; ?>" />&nbsp;<a class="button secondary" href="#" id="ppp-tweets-cancel-ext">Cancel</a><br />
	</p>
	<?php
}

/**
 * Displays the side bar item of 'Tweet Info'
 * @return void
 */
function ppp_tweets_info_callback() {
	global $post;
	$is_cropped = ppp_tweets_maybe_crop_image( $post->ID );
	?>
	<p>
		<label><?php _e( 'Length ', 'ppp-tweets-txt' ); ?>:</label>&nbsp;<span class="ppp-text-length" id="ppp-tweets-details">0</span>
	</p>
	<p>
		<label><?php _e( 'Crop Image', 'ppp-tweets-txt' ); ?>:</label>&nbsp;<input type="checkbox" name="_ppp_tweets_crop_image" value="1" <?php echo checked( '1', $is_cropped, false ); ?> /><br />
		<small><?php _e( 'When checked, will crop to the optimial Twitter image dimensions', 'ppp-tweets-txt' ); ?></small>
	</p>
	<?php
}
