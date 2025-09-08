<?php
class Gemini_SEO_Twitter {
	public function __construct() {
		add_action('wp_head', array($this, 'output_twitter_tags'), 6);
	}

	public function output_twitter_tags() {
		if (!is_singular()) return;
		global $post;
		$twitter_title = get_post_meta($post->ID, '_gemini_seo_twitter_title', true);
		$twitter_description = get_post_meta($post->ID, '_gemini_seo_twitter_description', true);
		$twitter_image = get_post_meta($post->ID, '_gemini_seo_twitter_image', true);

		$og_title = get_post_meta($post->ID, '_gemini_seo_og_title', true);
		$og_description = get_post_meta($post->ID, '_gemini_seo_og_description', true);
		$og_image = get_post_meta($post->ID, '_gemini_seo_og_image', true);

		$meta_title = get_post_meta($post->ID, '_gemini_seo_meta_title', true);
		$meta_description = get_post_meta($post->ID, '_gemini_seo_meta_description', true);

		$title = $twitter_title ? $twitter_title : ($og_title ? $og_title : ($meta_title ? $meta_title : get_the_title($post)));
		$description = $twitter_description ? $twitter_description : ($og_description ? $og_description : ($meta_description ? $meta_description : get_the_excerpt($post)));
		$image = $twitter_image ? $twitter_image : ($og_image ? $og_image : (has_post_thumbnail($post) ? wp_get_attachment_url(get_post_thumbnail_id($post)) : ''));

		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr($description) . '" />' . "\n";
		if ($image) {
			echo '<meta name="twitter:image" content="' . esc_url($image) . '" />' . "\n";
		}
	}
}
// Instantiate
if (class_exists('Gemini_SEO_Twitter')) new Gemini_SEO_Twitter();
