<?php
class Gemini_SEO_Sitemap {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
        // Ping search engines when a post is published or updated
        add_action('publish_post', array($this, 'ping_search_engines'));
        add_action('publish_page', array($this, 'ping_search_engines'));
        add_action('save_post', array($this, 'ping_search_engines'));
    }
    
    private function __construct() {
        add_action('init', array($this, 'init_sitemaps'), 10);
        add_filter('rewrite_rules_array', array($this, 'add_sitemap_rewrite_rules'));
        add_action('template_redirect', array($this, 'handle_sitemap_request'), 0);
        add_action('gemini_seo_sitemap_index', array($this, 'build_sitemap_index'));
        add_action('gemini_seo_ping_search_engines', array($this, 'ping_search_engines'));
    }
    
    public function init_sitemaps() {
        $settings = get_option('gemini_seo_settings');
        
        if (isset($settings['enable_sitemap']) && $settings['enable_sitemap']) {
            // Flush rewrite rules if needed
            if (get_option('gemini_seo_flush_rewrite_rules')) {
                flush_rewrite_rules();
                delete_option('gemini_seo_flush_rewrite_rules');
            }
        }
    }
    
    public function add_sitemap_rewrite_rules($rules) {
        $sitemap_rules = array(
            'sitemap\.xml$' => 'index.php?gemini_seo_sitemap=index',
            'sitemap-([a-z]+)?\.xml$' => 'index.php?gemini_seo_sitemap=$matches[1]',
            'sitemap-([a-z]+)-([0-9]+)?\.xml$' => 'index.php?gemini_seo_sitemap=$matches[1]&gemini_seo_paged=$matches[2]',
        );
        
        return array_merge($sitemap_rules, $rules);
    }
    
    public function handle_sitemap_request() {
        global $wp_query;
        
        $sitemap = get_query_var('gemini_seo_sitemap');
        
        if (empty($sitemap)) {
            return;
        }
        
        // Check if sitemaps are enabled
        $settings = get_option('gemini_seo_settings');
        if (!isset($settings['enable_sitemap']) || !$settings['enable_sitemap']) {
            $wp_query->set_404();
            return;
        }
        
        // Set the appropriate headers
        header('Content-Type: application/xml; charset=UTF-8');
        
        // Prevent indexing of the sitemap itself
        header('X-Robots-Tag: noindex, follow', true);
        
        // Output the sitemap
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<?xml-stylesheet type="text/xsl" href="' . esc_url(GEMINI_SEO_URL . 'assets/xsl-sitemap.xsl') . '"?>' . "\n";
        
        if ('index' === $sitemap) {
            $this->output_sitemap_index();
        } else {
            $paged = get_query_var('gemini_seo_paged') ? absint(get_query_var('gemini_seo_paged')) : 1;
            $this->output_sitemap($sitemap, $paged);
        }
        
        exit;
    }
    
    private function output_sitemap_index() {
        $sitemaps = array();
        
        // Get public post types
        $post_types = get_post_types(array('public' => true));
        $excluded_post_types = $this->get_excluded_post_types();
        
        foreach ($post_types as $post_type) {
            if (in_array($post_type, $excluded_post_types)) {
                continue;
            }
            
            $count = wp_count_posts($post_type);
            if ($count->publish > 0) {
                $sitemaps[] = array(
                    'type' => $post_type,
                    'url' => home_url("/sitemap-{$post_type}.xml"),
                    'lastmod' => $this->get_last_modified_date($post_type)
                );
            }
        }
        
        // Get taxonomies
        $taxonomies = get_taxonomies(array('public' => true));
        $excluded_taxonomies = $this->get_excluded_taxonomies();
        
        foreach ($taxonomies as $taxonomy) {
            if (in_array($taxonomy, $excluded_taxonomies)) {
                continue;
            }
            
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => true,
                'fields' => 'count'
            ));
            
            if ($terms > 0) {
                $sitemaps[] = array(
                    'type' => $taxonomy,
                    'url' => home_url("/sitemap-{$taxonomy}.xml"),
                    'lastmod' => current_time('c')
                );
            }
        }
        
        // Output the sitemap index
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($sitemaps as $sitemap) {
            echo "\t<sitemap>\n";
            echo "\t\t<loc>" . esc_url($sitemap['url']) . "</loc>\n";
            echo "\t\t<lastmod>" . esc_html($sitemap['lastmod']) . "</lastmod>\n";
            echo "\t</sitemap>\n";
        }
        
        echo '</sitemapindex>';
    }
    
    private function output_sitemap($type, $paged = 1) {
        $urls = array();
        $items_per_page = 1000; // Sitemap protocol limit
        
        if (post_type_exists($type)) {
            $urls = $this->get_post_type_urls($type, $paged, $items_per_page);
        } elseif (taxonomy_exists($type)) {
            $urls = $this->get_taxonomy_urls($type, $paged, $items_per_page);
        } else {
            // Allow for custom sitemap types via filter
            $urls = apply_filters('gemini_seo_custom_sitemap_urls', array(), $type, $paged, $items_per_page);
        }
        
        // Output the URLs
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";
        
        foreach ($urls as $url) {
            echo "\t<url>\n";
            echo "\t\t<loc>" . esc_url($url['loc']) . "</loc>\n";
            
            if (!empty($url['lastmod'])) {
                echo "\t\t<lastmod>" . esc_html($url['lastmod']) . "</lastmod>\n";
            }
            
            if (!empty($url['changefreq'])) {
                echo "\t\t<changefreq>" . esc_html($url['changefreq']) . "</changefreq>\n";
            }
            
            if (!empty($url['priority'])) {
                echo "\t\t<priority>" . esc_html($url['priority']) . "</priority>\n";
            }
            
            // Image sitemap
            if (!empty($url['images'])) {
                foreach ($url['images'] as $image) {
                    echo "\t\t<image:image>\n";
                    echo "\t\t\t<image:loc>" . esc_url($image['loc']) . "</image:loc>\n";
                    if (!empty($image['title'])) {
                        echo "\t\t\t<image:title>" . esc_html($image['title']) . "</image:title>\n";
                    }
                    if (!empty($image['caption'])) {
                        echo "\t\t\t<image:caption>" . esc_html($image['caption']) . "</image:caption>\n";
                    }
                    echo "\t\t</image:image>\n";
                }
            }
            
            echo "\t</url>\n";
        }
        
        echo '</urlset>';
    }
    
    private function get_post_type_urls($post_type, $paged = 1, $per_page = 1000) {
        $urls = array();
        $offset = ($paged - 1) * $per_page;
        
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'offset' => $offset,
            'orderby' => 'modified',
            'order' => 'DESC',
            'has_password' => false
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $post;
                
                $url = array(
                    'loc' => get_permalink($post->ID),
                    'lastmod' => get_the_modified_time('c', $post->ID),
                    'changefreq' => $this->calculate_change_frequency($post->ID),
                    'priority' => $this->calculate_priority($post->ID, $post_type)
                );
                
                // Add images if available
                $images = $this->get_post_images($post->ID);
                if (!empty($images)) {
                    $url['images'] = $images;
                }
                
                $urls[] = $url;
            }
        }
        
        wp_reset_postdata();
        
        return $urls;
    }
    
    private function get_taxonomy_urls($taxonomy, $paged = 1, $per_page = 1000) {
        $urls = array();
        $offset = ($paged - 1) * $per_page;
        
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
            'number' => $per_page,
            'offset' => $offset,
            'orderby' => 'count',
            'order' => 'DESC'
        ));
        
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $urls[] = array(
                    'loc' => get_term_link($term),
                    'lastmod' => current_time('c'),
                    'changefreq' => 'weekly',
                    'priority' => 0.6
                );
            }
        }
        
        return $urls;
    }
    
    private function calculate_change_frequency($post_id) {
        $post_modified = get_the_modified_time('U', $post_id);
        $current_time = current_time('timestamp');
        $time_diff = $current_time - $post_modified;
        
        if ($time_diff < DAY_IN_SECONDS * 7) {
            return 'daily';
        } elseif ($time_diff < DAY_IN_SECONDS * 30) {
            return 'weekly';
        } elseif ($time_diff < DAY_IN_SECONDS * 365) {
            return 'monthly';
        } else {
            return 'yearly';
        }
    }
    
    private function calculate_priority($post_id, $post_type) {
        // Homepage gets highest priority
        if (get_option('page_on_front') == $post_id) {
            return 1.0;
        }
        
        // Posts and pages get medium priority
        if (in_array($post_type, array('post', 'page'))) {
            return 0.8;
        }
        
        // Other content types get lower priority
        return 0.6;
    }
    
    private function get_post_images($post_id) {
        $images = array();
        
        // Check if the post has a featured image
        if (has_post_thumbnail($post_id)) {
            $thumbnail_id = get_post_thumbnail_id($post_id);
            $image_src = wp_get_attachment_image_src($thumbnail_id, 'full');
            
            if ($image_src) {
                $images[] = array(
                    'loc' => $image_src[0],
                    'title' => get_the_title($thumbnail_id),
                    'caption' => get_post_field('post_excerpt', $thumbnail_id)
                );
            }
        }
        
        // Check for images in the content
        $content = get_post_field('post_content', $post_id);
        
        if (preg_match_all('/<img [^>]*src=["\']([^"\']+)["\']/i', $content, $matches)) {
            foreach ($matches[1] as $image_url) {
                // Skip data URIs
                if (strpos($image_url, 'data:') === 0) {
                    continue;
                }
                
                $images[] = array(
                    'loc' => $image_url
                );
            }
        }
        
        return $images;
    }
    
    private function get_excluded_post_types() {
        $settings = get_option('gemini_seo_settings');
        return isset($settings['sitemap_exclude_post_types']) ? $settings['sitemap_exclude_post_types'] : array();
    }
    
    private function get_excluded_taxonomies() {
        $settings = get_option('gemini_seo_settings');
        return isset($settings['sitemap_exclude_taxonomies']) ? $settings['sitemap_exclude_taxonomies'] : array();
    }
    
    private function get_last_modified_date($post_type) {
        global $wpdb;
        
        $last_modified = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_modified FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish' ORDER BY post_modified DESC LIMIT 1",
                $post_type
            )
        );
        
        return $last_modified ? date('c', strtotime($last_modified)) : current_time('c');
    }
    
    public function ping_search_engines() {
        $settings = get_option('gemini_seo_settings');
        
        if (!isset($settings['enable_sitemap']) || !$settings['enable_sitemap']) {
            return;
        }
        
        $sitemap_url = urlencode(home_url('/sitemap.xml'));
        
        // Ping Google
        if (isset($settings['ping_google']) && $settings['ping_google']) {
            wp_remote_get('https://www.google.com/ping?sitemap=' . $sitemap_url, array('timeout' => 5));
        }
        
        // Ping Bing
        if (isset($settings['ping_bing']) && $settings['ping_bing']) {
            wp_remote_get('https://www.bing.com/ping?sitemap=' . $sitemap_url, array('timeout' => 5));
        }
    }
    
    public function build_sitemap_index() {
        // This function would be called by a scheduled event to build the sitemap index
        // and save it as a static file for better performance
    }
}