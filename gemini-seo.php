<?php
/**
 * Plugin Name: Gemini SEO
 * Plugin URI: https://yourwebsite.com/gemini-seo
 * Description: All-in-one SEO solution for WordPress
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('GEMINI_SEO_VERSION', '1.0.0');
define('GEMINI_SEO_FILE', __FILE__);
define('GEMINI_SEO_PATH', plugin_dir_path(__FILE__));
define('GEMINI_SEO_URL', plugin_dir_url(__FILE__));

// Main plugin class
class Gemini_SEO {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        // Admin functionality
        if (is_admin()) {
            require_once GEMINI_SEO_PATH . 'includes/admin/class-admin.php';
            require_once GEMINI_SEO_PATH . 'includes/admin/class-meta-boxes.php';
            require_once GEMINI_SEO_PATH . 'includes/admin/class-settings.php';
        }
        
        // Core functionality
        require_once GEMINI_SEO_PATH . 'includes/core/class-sitemap.php';
        require_once GEMINI_SEO_PATH . 'includes/core/class-redirects.php';
        require_once GEMINI_SEO_PATH . 'includes/core/class-404-monitor.php';
        require_once GEMINI_SEO_PATH . 'includes/core/class-robots.php';
        require_once GEMINI_SEO_PATH . 'includes/core/class-analysis.php';
        if (is_admin()) {
            // Ensure analysis AJAX handler is registered
            if (class_exists('Gemini_SEO_Analysis')) {
                new Gemini_SEO_Analysis();
            }
        }
        require_once GEMINI_SEO_PATH . 'includes/core/class-import-export.php';
        
        // Frontend output
        require_once GEMINI_SEO_PATH . 'includes/frontend/class-frontend.php';
        require_once GEMINI_SEO_PATH . 'includes/frontend/class-schema.php';
        require_once GEMINI_SEO_PATH . 'includes/frontend/class-opengraph.php';
        require_once GEMINI_SEO_PATH . 'includes/frontend/class-twitter.php';
        require_once GEMINI_SEO_PATH . 'includes/frontend/class-breadcrumbs.php';
        
        // Utilities
        require_once GEMINI_SEO_PATH . 'includes/utilities/class-helpers.php';
        require_once GEMINI_SEO_PATH . 'includes/utilities/class-validators.php';
        require_once GEMINI_SEO_PATH . 'includes/utilities/class-db.php';
        
        // Integrations
        if (class_exists('WooCommerce')) {
            require_once GEMINI_SEO_PATH . 'includes/integrations/class-woocommerce.php';
        }
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'init_plugin'));
    }
    
    public function activate() {
        // Create necessary database tables
        Gemini_SEO_DB::create_tables();
        
        // Set default options
        $defaults = array(
            'enable_sitemap' => true,
            'enable_analysis' => true,
            // More defaults...
        );
        add_option('gemini_seo_settings', $defaults);
        
        // Flush rewrite rules for sitemap
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Clean up on deactivation
        flush_rewrite_rules();
    }
    
    public function init_plugin() {
        // Initialize all components
        if (is_admin()) {
            Gemini_SEO_Admin::get_instance();
            Gemini_SEO_Meta_Boxes::get_instance();
            Gemini_SEO_Settings::get_instance();
            Gemini_SEO_Redirects::get_instance();
        }
        
        Gemini_SEO_Sitemap::get_instance();
        Gemini_SEO_Redirects::get_instance();
        Gemini_SEO_404_Monitor::get_instance();
        Gemini_SEO_Frontend::get_instance();
        Gemini_SEO_Schema::get_instance();
        
        // Load text domain for translations
        load_plugin_textdomain('gemini-seo', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

// Initialize the plugin
Gemini_SEO::get_instance();