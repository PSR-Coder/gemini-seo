<?php
class Gemini_SEO_Settings {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Gemini SEO', 'gemini-seo'),
            __('Gemini SEO', 'gemini-seo'),
            'manage_options',
            'gemini-seo',
            array($this, 'render_settings_page'),
            'dashicons-chart-area',
            80
        );
        
        add_submenu_page(
            'gemini-seo',
            __('General Settings', 'gemini-seo'),
            __('General', 'gemini-seo'),
            'manage_options',
            'gemini-seo',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'gemini-seo',
            __('Social Media', 'gemini-seo'),
            __('Social Media', 'gemini-seo'),
            'manage_options',
            'gemini-seo-social',
            array($this, 'render_social_settings_page')
        );
        
        add_submenu_page(
            'gemini-seo',
            __('Advanced Settings', 'gemini-seo'),
            __('Advanced', 'gemini-seo'),
            'manage_options',
            'gemini-seo-advanced',
            array($this, 'render_advanced_settings_page')
        );

        add_submenu_page(
            'gemini-seo',
            __('robots.txt & .htaccess', 'gemini-seo'),
            __('robots.txt & .htaccess', 'gemini-seo'),
            'manage_options',
            'gemini-seo-robots',
            array($this, 'render_robots_htaccess_page')
        );

        add_submenu_page(
            'gemini-seo',
            __('Import & Export', 'gemini-seo'),
            __('Import & Export', 'gemini-seo'),
            'manage_options',
            'gemini-seo-import-export',
            array($this, 'render_import_export_page')
        );
    }
    public function render_import_export_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        include GEMINI_SEO_PATH . 'includes/admin/views/settings/import-export.php';
    }
    public function render_robots_htaccess_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        include GEMINI_SEO_PATH . 'includes/admin/views/settings/robots-htaccess.php';
    }
    
    
    public function register_settings() {
        register_setting('gemini_seo_settings', 'gemini_seo_settings', array($this, 'sanitize_settings'));
        
        // General Settings Section
        add_settings_section(
            'gemini_seo_general',
            __('General Settings', 'gemini-seo'),
            array($this, 'render_general_section'),
            'gemini-seo'
        );
        
        add_settings_field(
            'enable_analysis',
            __('Enable Content Analysis', 'gemini-seo'),
            array($this, 'render_checkbox_field'),
            'gemini-seo',
            'gemini_seo_general',
            array(
                'label_for' => 'enable_analysis',
                'description' => __('Enable the real-time content analysis in the post editor.', 'gemini-seo')
            )
        );
        
        add_settings_field(
            'enable_sitemap',
            __('Enable XML Sitemap', 'gemini-seo'),
            array($this, 'render_checkbox_field'),
            'gemini-seo',
            'gemini_seo_general',
            array(
                'label_for' => 'enable_sitemap',
                'description' => __('Enable the XML sitemap functionality.', 'gemini-seo')
            )
        );
        
        add_settings_field(
            'sitemap_exclude_post_types',
            __('Exclude Post Types from Sitemap', 'gemini-seo'),
            array($this, 'render_post_types_field'),
            'gemini-seo',
            'gemini_seo_general',
            array(
                'label_for' => 'sitemap_exclude_post_types',
                'description' => __('Select post types to exclude from the sitemap.', 'gemini-seo')
            )
        );
        
        add_settings_field(
            'sitemap_exclude_taxonomies',
            __('Exclude Taxonomies from Sitemap', 'gemini-seo'),
            array($this, 'render_taxonomies_field'),
            'gemini-seo',
            'gemini_seo_general',
            array(
                'label_for' => 'sitemap_exclude_taxonomies',
                'description' => __('Select taxonomies to exclude from the sitemap.', 'gemini-seo')
            )
        );
        
        // Knowledge Graph Settings
        add_settings_section(
            'gemini_seo_knowledge_graph',
            __('Knowledge Graph & Schema', 'gemini-seo'),
            array($this, 'render_knowledge_graph_section'),
            'gemini-seo'
        );
        
        add_settings_field(
            'organization_name',
            __('Organization Name', 'gemini-seo'),
            array($this, 'render_text_field'),
            'gemini-seo',
            'gemini_seo_knowledge_graph',
            array(
                'label_for' => 'organization_name',
                'description' => __('The name of your organization.', 'gemini-seo')
            )
        );
        
        add_settings_field(
            'organization_logo',
            __('Organization Logo', 'gemini-seo'),
            array($this, 'render_media_field'),
            'gemini-seo',
            'gemini_seo_knowledge_graph',
            array(
                'label_for' => 'organization_logo',
                'description' => __('The logo of your organization (recommended: 600x60px).', 'gemini-seo')
            )
        );
        
        // Social Media Settings Section
        add_settings_section(
            'gemini_seo_social_media',
            __('Social Media Profiles', 'gemini-seo'),
            array($this, 'render_social_media_section'),
            'gemini-seo-social'
        );
        
        add_settings_field(
            'social_profiles',
            __('Social Profiles', 'gemini-seo'),
            array($this, 'render_social_profiles_field'),
            'gemini-seo-social',
            'gemini_seo_social_media',
            array(
                'label_for' => 'social_profiles',
                'description' => __('Add your social media profiles for schema markup.', 'gemini-seo')
            )
        );
        
        // Advanced Settings Section
        add_settings_section(
            'gemini_seo_advanced',
            __('Advanced Settings', 'gemini-seo'),
            array($this, 'render_advanced_section'),
            'gemini-seo-advanced'
        );
        
        add_settings_field(
            'webmaster_tools',
            __('Webmaster Tools Verification', 'gemini-seo'),
            array($this, 'render_webmaster_tools_field'),
            'gemini-seo-advanced',
            'gemini_seo_advanced',
            array(
                'label_for' => 'webmaster_tools',
                'description' => __('Verification codes for Google, Bing, and other webmaster tools.', 'gemini-seo')
            )
        );
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        include GEMINI_SEO_PATH . 'includes/admin/views/settings/general.php';
    }
    
    public function render_social_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        include GEMINI_SEO_PATH . 'includes/admin/views/settings/social.php';
    }
    
    public function render_advanced_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        include GEMINI_SEO_PATH . 'includes/admin/views/settings/advanced.php';
    }
    
    public function render_general_section() {
        echo '<p>' . __('General settings for the Gemini SEO plugin.', 'gemini-seo') . '</p>';
    }
    
    public function render_knowledge_graph_section() {
        echo '<p>' . __('Configure your Knowledge Graph and Schema markup settings.', 'gemini-seo') . '</p>';
    }
    
    public function render_social_media_section() {
        echo '<p>' . __('Add your social media profiles for rich snippets and knowledge graph.', 'gemini-seo') . '</p>';
    }
    
    public function render_advanced_section() {
        echo '<p>' . __('Advanced settings for the Gemini SEO plugin.', 'gemini-seo') . '</p>';
    }
    
    public function render_checkbox_field($args) {
        $options = get_option('gemini_seo_settings');
        $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : false;
        
        echo '<input type="checkbox" id="' . esc_attr($args['label_for']) . '" name="gemini_seo_settings[' . esc_attr($args['label_for']) . ']" value="1" ' . checked(1, $value, false) . '>';
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    public function render_text_field($args) {
        $options = get_option('gemini_seo_settings');
        $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : '';
        
        echo '<input type="text" id="' . esc_attr($args['label_for']) . '" name="gemini_seo_settings[' . esc_attr($args['label_for']) . ']" value="' . esc_attr($value) . '" class="regular-text">';
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    public function render_media_field($args) {
        $options = get_option('gemini_seo_settings');
        $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : '';
        
        echo '<input type="text" id="' . esc_attr($args['label_for']) . '" name="gemini_seo_settings[' . esc_attr($args['label_for']) . ']" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<button type="button" class="button gemini-seo-media-upload" data-target="' . esc_attr($args['label_for']) . '">' . __('Select Image', 'gemini-seo') . '</button>';
        
        if (!empty($value)) {
            echo '<p class="description"><img src="' . esc_url($value) . '" style="max-width: 200px; height: auto;"></p>';
        }
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    public function render_post_types_field($args) {
        $options = get_option('gemini_seo_settings');
        $selected = isset($options[$args['label_for']]) ? $options[$args['label_for']] : array();
        
        $post_types = get_post_types(array('public' => true), 'objects');
        
        foreach ($post_types as $post_type) {
            echo '<label><input type="checkbox" name="gemini_seo_settings[' . esc_attr($args['label_for']) . '][]" value="' . esc_attr($post_type->name) . '" ' . checked(in_array($post_type->name, $selected), true, false) . '> ' . esc_html($post_type->label) . '</label><br>';
        }
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    public function render_taxonomies_field($args) {
        $options = get_option('gemini_seo_settings');
        $selected = isset($options[$args['label_for']]) ? $options[$args['label_for']] : array();
        
        $taxonomies = get_taxonomies(array('public' => true), 'objects');
        
        foreach ($taxonomies as $taxonomy) {
            echo '<label><input type="checkbox" name="gemini_seo_settings[' . esc_attr($args['label_for']) . '][]" value="' . esc_attr($taxonomy->name) . '" ' . checked(in_array($taxonomy->name, $selected), true, false) . '> ' . esc_html($taxonomy->label) . '</label><br>';
        }
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    public function render_social_profiles_field($args) {
        $options = get_option('gemini_seo_settings');
        $profiles = isset($options[$args['label_for']]) ? $options[$args['label_for']] : array();
        
        $social_networks = array(
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'instagram' => 'Instagram',
            'linkedin' => 'LinkedIn',
            'pinterest' => 'Pinterest',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok'
        );
        
        echo '<div class="gemini-seo-social-profiles">';
        
        foreach ($social_networks as $key => $label) {
            $value = isset($profiles[$key]) ? $profiles[$key] : '';
            
            echo '<div class="gemini-seo-social-profile">';
            echo '<label for="' . esc_attr($args['label_for'] . '_' . $key) . '">' . esc_html($label) . '</label>';
            echo '<input type="url" id="' . esc_attr($args['label_for'] . '_' . $key) . '" name="gemini_seo_settings[' . esc_attr($args['label_for']) . '][' . $key . ']" value="' . esc_attr($value) . '" class="regular-text" placeholder="https://">';
            echo '</div>';
        }
        
        echo '</div>';
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    public function render_webmaster_tools_field($args) {
        $options = get_option('gemini_seo_settings');
        $webmaster_tools = isset($options[$args['label_for']]) ? $options[$args['label_for']] : array();
        
        $services = array(
            'google' => 'Google Search Console',
            'bing' => 'Bing Webmaster Tools',
            'yandex' => 'Yandex Webmaster',
            'baidu' => 'Baidu Webmaster Tools'
        );
        
        echo '<div class="gemini-seo-webmaster-tools">';
        
        foreach ($services as $key => $label) {
            $value = isset($webmaster_tools[$key]) ? $webmaster_tools[$key] : '';
            
            echo '<div class="gemini-seo-webmaster-tool">';
            echo '<label for="' . esc_attr($args['label_for'] . '_' . $key) . '">' . esc_html($label) . '</label>';
            echo '<input type="text" id="' . esc_attr($args['label_for'] . '_' . $key) . '" name="gemini_seo_settings[' . esc_attr($args['label_for']) . '][' . $key . ']" value="' . esc_attr($value) . '" class="regular-text">';
            echo '</div>';
        }
        
        echo '</div>';
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Sanitize checkbox fields
        $checkbox_fields = array('enable_analysis', 'enable_sitemap');
        foreach ($checkbox_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? 1 : 0;
        }
        
        // Sanitize text fields
        $text_fields = array('organization_name');
        foreach ($text_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = sanitize_text_field($input[$field]);
            }
        }
        
        // Sanitize media fields
        $media_fields = array('organization_logo');
        foreach ($media_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = esc_url_raw($input[$field]);
            }
        }
        
        // Sanitize array fields
        $array_fields = array('sitemap_exclude_post_types', 'sitemap_exclude_taxonomies');
        foreach ($array_fields as $field) {
            if (isset($input[$field]) && is_array($input[$field])) {
                $sanitized[$field] = array_map('sanitize_text_field', $input[$field]);
            } else {
                $sanitized[$field] = array();
            }
        }
        
        // Sanitize social profiles
        if (isset($input['social_profiles']) && is_array($input['social_profiles'])) {
            foreach ($input['social_profiles'] as $key => $value) {
                $sanitized['social_profiles'][$key] = esc_url_raw($value);
            }
        }
        
        // Sanitize webmaster tools
        if (isset($input['webmaster_tools']) && is_array($input['webmaster_tools'])) {
            foreach ($input['webmaster_tools'] as $key => $value) {
                $sanitized['webmaster_tools'][$key] = sanitize_text_field($value);
            }
        }
        
        return $sanitized;
    }
    
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'gemini-seo') === false) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_style('gemini-seo-admin', GEMINI_SEO_URL . 'includes/admin/assets/css/admin.css', array(), GEMINI_SEO_VERSION);
        wp_enqueue_script('gemini-seo-admin', GEMINI_SEO_URL . 'includes/admin/assets/js/admin.js', array('jquery'), GEMINI_SEO_VERSION, true);
    }
}