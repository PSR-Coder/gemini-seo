<?php
class Gemini_SEO_Meta_Boxes {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_data'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function add_meta_boxes() {
        $post_types = get_post_types(array('public' => true));
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'gemini_seo_meta_box',
                __('Gemini SEO', 'gemini-seo'),
                array($this, 'render_meta_box'),
                $post_type,
                'normal',
                'high'
            );
        }
    }
    
    public function render_meta_box($post) {
        wp_nonce_field('gemini_seo_meta_nonce', 'gemini_seo_nonce');
        
        // Get existing values
        $focus_keyword = get_post_meta($post->ID, '_gemini_seo_focus_keyword', true);
        $meta_title = get_post_meta($post->ID, '_gemini_seo_meta_title', true);
        $meta_description = get_post_meta($post->ID, '_gemini_seo_meta_description', true);
        $canonical_url = get_post_meta($post->ID, '_gemini_seo_canonical_url', true);
        $meta_robots = get_post_meta($post->ID, '_gemini_seo_meta_robots', true);
        $opengraph_title = get_post_meta($post->ID, '_gemini_seo_og_title', true);
        $opengraph_description = get_post_meta($post->ID, '_gemini_seo_og_description', true);
        $opengraph_image = get_post_meta($post->ID, '_gemini_seo_og_image', true);
        $twitter_title = get_post_meta($post->ID, '_gemini_seo_twitter_title', true);
        $twitter_description = get_post_meta($post->ID, '_gemini_seo_twitter_description', true);
        $twitter_image = get_post_meta($post->ID, '_gemini_seo_twitter_image', true);
        $schema_type = get_post_meta($post->ID, '_gemini_seo_schema_type', true);
        $related_keywords = get_post_meta($post->ID, '_gemini_seo_related_keywords', true);
        $synonyms = get_post_meta($post->ID, '_gemini_seo_synonyms', true);
        $url_slug = basename(get_permalink($post->ID));
        
        // Include the meta box template
        include GEMINI_SEO_PATH . 'includes/admin/views/meta-boxes/main.php';
    }
    
    public function save_meta_data($post_id, $post) {
        // Verify nonce, permissions, and autosave
        if (!isset($_POST['gemini_seo_nonce']) || 
            !wp_verify_nonce($_POST['gemini_seo_nonce'], 'gemini_seo_meta_nonce') ||
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
            !current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save all meta fields
        $fields = array(
            'focus_keyword',
            'meta_title',
            'meta_description',
            'canonical_url',
            'meta_robots',
            'og_title',
            'og_description',
            'og_image',
            'twitter_title',
            'twitter_description',
            'twitter_image',
            'schema_type',
            'related_keywords',
            'synonyms'
        );
        
        foreach ($fields as $field) {
            if (isset($_POST['gemini_seo_' . $field])) {
                update_post_meta($post_id, '_gemini_seo_' . $field, sanitize_text_field($_POST['gemini_seo_' . $field]));
            }
        }
    }
    
    public function enqueue_scripts($hook) {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }
        
        wp_enqueue_style('gemini-seo-metabox', GEMINI_SEO_URL . 'includes/admin/assets/css/metabox.css', array(), GEMINI_SEO_VERSION);
        wp_enqueue_script('gemini-seo-metabox', GEMINI_SEO_URL . 'includes/admin/assets/js/metabox.js', array('jquery'), GEMINI_SEO_VERSION, true);
        wp_enqueue_script('gemini-seo-analysis', GEMINI_SEO_URL . 'includes/admin/assets/js/analysis.js', array('jquery'), GEMINI_SEO_VERSION, true);
        
        // Localize script for AJAX
        wp_localize_script('gemini-seo-analysis', 'gemini_seo_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gemini_seo_analysis_nonce')
        ));
        
        wp_localize_script('gemini-seo-metabox', 'geminiMetabox', array(
            'slugLengthMsg' => __('Slug should be 3-60 characters.', 'gemini-seo'),
            'slugKeywordMsg' => __('Slug should include the focus keyword.', 'gemini-seo'),
            'metaDescLengthMsg' => __('Meta description should be 150-160 characters.', 'gemini-seo'),
            'metaDescKeywordMsg' => __('Meta description should include the focus keyword.', 'gemini-seo'),
        ));
    }
    
    // Slug validation for SEO: length and focus keyphrase inclusion
    public static function validate_slug($slug, $focus_keyword) {
        $min = 3; $max = 60;
        $length_ok = (strlen($slug) >= $min && strlen($slug) <= $max);
        $has_keyword = (stripos($slug, $focus_keyword) !== false);
        return array('length_ok' => $length_ok, 'has_keyword' => $has_keyword);
    }
}