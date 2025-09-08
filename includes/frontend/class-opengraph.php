<?php
class Gemini_SEO_OpenGraph {
	public function __construct() {
		add_action('wp_head', array($this, 'output_opengraph_tags'), 5);
	}

	public function output_opengraph_tags() {
		if (!is_singular()) return;
		global $post;
		$og_title = get_post_meta($post->ID, '_gemini_seo_og_title', true);
		$og_description = get_post_meta($post->ID, '_gemini_seo_og_description', true);
		$og_image = get_post_meta($post->ID, '_gemini_seo_og_image', true);

		$meta_title = get_post_meta($post->ID, '_gemini_seo_meta_title', true);
		$meta_description = get_post_meta($post->ID, '_gemini_seo_meta_description', true);

		$title = $og_title ? $og_title : ($meta_title ? $meta_title : get_the_title($post));
		$description = $og_description ? $og_description : ($meta_description ? $meta_description : get_the_excerpt($post));
		$image = $og_image ? $og_image : (has_post_thumbnail($post) ? wp_get_attachment_url(get_post_thumbnail_id($post)) : '');

		echo '<meta property="og:type" content="article" />' . "\n";
		echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
		echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";
		echo '<meta property="og:url" content="' . esc_url(get_permalink($post)) . '" />' . "\n";
		if ($image) {
			echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
		}
		echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
	}
}
// Instantiate
if (class_exists('Gemini_SEO_OpenGraph')) new Gemini_SEO_OpenGraph();
