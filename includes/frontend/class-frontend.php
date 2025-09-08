<?php
class Gemini_SEO_Frontend {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_head', array($this, 'output_meta_tags'), 1);
        add_action('wp_head', array($this, 'output_canonical_tag'), 2);
        add_action('wp_head', array($this, 'output_robots_meta'), 3);
        add_action('wp_head', array($this, 'output_sitewide_meta'), 99);
        add_action('wp_footer', array($this, 'output_sitewide_footer_meta'), 99);
        add_filter('pre_get_document_title', array($this, 'filter_document_title'), 15);
        add_filter('wp_title', array($this, 'filter_wp_title'), 15, 3);
    }
    
    public function output_meta_tags() {
        if (is_singular()) {
            global $post;
            // Output meta title and description
            $this->output_standard_meta($post);
            // Output Open Graph tags
            if (method_exists($this, 'output_opengraph_tags')) {
                $this->output_opengraph_tags($post);
            }
            // Output Twitter Cards
            if (method_exists($this, 'output_twitter_tags')) {
                $this->output_twitter_tags($post);
            }
        } else {
            // Output for archives, homepage, etc.
            $this->output_general_meta();
        }
    }
    // Output canonical tag for current page
    public function output_canonical_tag() {
        if (is_singular()) {
            global $post;
            $canonical = get_post_meta($post->ID, '_gemini_seo_canonical_url', true);
            $url = $canonical ? $canonical : get_permalink($post);
            echo '<link rel="canonical" href="' . esc_url($url) . '" />' . "\n";
        } elseif (is_home() || is_front_page()) {
            echo '<link rel="canonical" href="' . esc_url(home_url('/')) . '" />' . "\n";
        }
    }

    // Output robots meta tag for current page
    public function output_robots_meta() {
        if (is_singular()) {
            global $post;
            $robots = get_post_meta($post->ID, '_gemini_seo_meta_robots', true);
            if ($robots) {
                echo '<meta name="robots" content="' . esc_attr($robots) . '" />' . "\n";
            }
        }
    }

    // Output sitewide meta tags (e.g., generator, viewport, etc.)
    public function output_sitewide_meta() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">\n';
        echo '<meta name="generator" content="Gemini SEO Plugin">\n';
    }

    // Output sitewide meta tags in footer if needed
    public function output_sitewide_footer_meta() {
        // Reserved for future use (e.g., analytics, structured data)
    }
    
    private function output_standard_meta($post) {
        $meta_title = get_post_meta($post->ID, '_gemini_seo_meta_title', true);
        $meta_description = get_post_meta($post->ID, '_gemini_seo_meta_description', true);
        
        if (!empty($meta_title)) {
            echo '<meta name="title" content="' . esc_attr($meta_title) . '">' . "\n";
        }
        
        if (!empty($meta_description)) {
            echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
        }
    }
    
    private function output_opengraph_tags($post) {
        $og_title = get_post_meta($post->ID, '_gemini_seo_og_title', true);
        $og_description = get_post_meta($post->ID, '_gemini_seo_og_description', true);
        $og_image = get_post_meta($post->ID, '_gemini_seo_og_image', true);
        
        if (empty($og_title)) {
            $og_title = get_the_title($post->ID);
        }
        
        if (empty($og_description)) {
            $og_description = wp_trim_words(get_the_excerpt($post->ID), 30);
        }
        
        if (empty($og_image) && has_post_thumbnail($post->ID)) {
            $og_image = get_the_post_thumbnail_url($post->ID, 'large');
        }
        
        echo '<meta property="og:type" content="article">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($og_title) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($og_description) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink($post->ID)) . '">' . "\n";
        
        if (!empty($og_image)) {
            echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
        }
        
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    }
    
    // Additional methods for Twitter, canonical, robots, etc.
    
    public function filter_document_title($title) {
        if (is_singular()) {
            global $post;
            $meta_title = get_post_meta($post->ID, '_gemini_seo_meta_title', true);
            
            if (!empty($meta_title)) {
                return $meta_title;
            }
        }
        
        return $title;
    }
    
    public function filter_wp_title($title, $sep, $seplocation) {
        if (is_singular()) {
            global $post;
            $meta_title = get_post_meta($post->ID, '_gemini_seo_meta_title', true);
            
            if (!empty($meta_title)) {
                return $meta_title;
            }
        }
        
        return $title;
    }
}