<?php
// Breadcrumbs Class
class Gemini_SEO_Breadcrumbs {
	private $defaults = array(
		'separator' => ' &raquo; ',
		'home_label' => 'Home',
		'container' => 'nav',
		'container_class' => 'gemini-seo-breadcrumbs',
		'item_class' => 'breadcrumb-item',
		'show_home' => true,
	);

	public function __construct() {
		add_shortcode('gemini_breadcrumbs', array($this, 'shortcode'));
		add_action('gemini_seo_breadcrumbs', array($this, 'display'));
	}

	public function display($args = array()) {
		echo $this->get_breadcrumbs($args);
	}

	public function shortcode($atts = array()) {
		return $this->get_breadcrumbs($atts);
	}

	public function get_breadcrumbs($args = array()) {
		$args = wp_parse_args($args, $this->defaults);
		$breadcrumbs = array();
		if ($args['show_home']) {
			$breadcrumbs[] = '<a class="' . esc_attr($args['item_class']) . ' home" href="' . esc_url(home_url('/')) . '">' . esc_html($args['home_label']) . '</a>';
		}

		if (is_singular()) {
			global $post;
			$ancestors = get_post_ancestors($post);
			$ancestors = array_reverse($ancestors);
			foreach ($ancestors as $ancestor_id) {
				$breadcrumbs[] = '<a class="' . esc_attr($args['item_class']) . ' parent" href="' . esc_url(get_permalink($ancestor_id)) . '">' . esc_html(get_the_title($ancestor_id)) . '</a>';
			}
			$breadcrumbs[] = '<span class="' . esc_attr($args['item_class']) . ' current">' . esc_html(get_the_title($post)) . '</span>';
		} elseif (is_category() || is_tag() || is_tax()) {
			$term = get_queried_object();
			if ($term && $term->parent) {
				$ancestors = get_ancestors($term->term_id, $term->taxonomy);
				$ancestors = array_reverse($ancestors);
				foreach ($ancestors as $ancestor_id) {
					$ancestor = get_term($ancestor_id, $term->taxonomy);
					$breadcrumbs[] = '<a class="' . esc_attr($args['item_class']) . ' parent" href="' . esc_url(get_term_link($ancestor)) . '">' . esc_html($ancestor->name) . '</a>';
				}
			}
			$breadcrumbs[] = '<span class="' . esc_attr($args['item_class']) . ' current">' . esc_html(single_term_title('', false)) . '</span>';
		} elseif (is_search()) {
			$breadcrumbs[] = '<span class="' . esc_attr($args['item_class']) . ' current">' . sprintf(__('Search results for "%s"', 'gemini-seo'), get_search_query()) . '</span>';
		} elseif (is_404()) {
			$breadcrumbs[] = '<span class="' . esc_attr($args['item_class']) . ' current">' . __('404 Not Found', 'gemini-seo') . '</span>';
		} elseif (is_home() || is_front_page()) {
			$breadcrumbs[] = '<span class="' . esc_attr($args['item_class']) . ' current">' . esc_html($args['home_label']) . '</span>';
		}

		$output = '<' . esc_attr($args['container']) . ' class="' . esc_attr($args['container_class']) . '">';
		$output .= implode($args['separator'], $breadcrumbs);
		$output .= '</' . esc_attr($args['container']) . '>';
		return $output;
	}
}
// Instantiate
if (class_exists('Gemini_SEO_Breadcrumbs')) new Gemini_SEO_Breadcrumbs();
