<?php
/**
 * Gemini SEO Admin Class
 * Handles admin menu, assets, and feature initialization.
 */
if (!class_exists('Gemini_SEO_Admin')) {
    class Gemini_SEO_Admin {
        public function __construct() {
            add_action('admin_menu', array($this, 'register_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        }

        // Register Gemini SEO admin menu
        public function register_admin_menu() {
            add_menu_page(
                __('Gemini SEO', 'ds-gemini-seo'),
                __('Gemini SEO', 'ds-gemini-seo'),
                'manage_options',
                'gemini-seo',
                array($this, 'render_dashboard'),
                'dashicons-admin-site',
                60
            );
            add_submenu_page('gemini-seo', __('Settings', 'ds-gemini-seo'), __('Settings', 'ds-gemini-seo'), 'manage_options', 'gemini-seo-settings', array($this, 'render_settings'));
            add_submenu_page('gemini-seo', __('Redirects', 'ds-gemini-seo'), __('Redirects', 'ds-gemini-seo'), 'manage_options', 'gemini-seo-redirects', array($this, 'render_redirects'));
            add_submenu_page('gemini-seo', __('404 Monitor', 'ds-gemini-seo'), __('404 Monitor', 'ds-gemini-seo'), 'manage_options', 'gemini-seo-404', array($this, 'render_404_monitor'));
        }

        // Enqueue admin JS and CSS
        public function enqueue_admin_assets($hook) {
            if (strpos($hook, 'gemini-seo') !== false) {
                wp_enqueue_style('gemini-seo-admin', plugin_dir_url(__FILE__) . 'assets/css/admin.css', array(), '1.0');
                wp_enqueue_script('gemini-seo-admin', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array('jquery'), '1.0', true);
            }
        }

        // Render dashboard page
        public function render_dashboard() {
            echo '<div class="wrap"><h1>' . esc_html__('Gemini SEO Dashboard', 'ds-gemini-seo') . '</h1></div>';
        }

        // Render settings page
        public function render_settings() {
            echo '<div class="wrap"><h1>' . esc_html__('Gemini SEO Settings', 'ds-gemini-seo') . '</h1></div>';
        }

        // Render redirects page
        public function render_redirects() {
            echo '<div class="wrap"><h1>' . esc_html__('Gemini SEO Redirects', 'ds-gemini-seo') . '</h1></div>';
        }

        // Render 404 monitor page
        public function render_404_monitor() {
            echo '<div class="wrap"><h1>' . esc_html__('Gemini SEO 404 Monitor', 'ds-gemini-seo') . '</h1></div>';
        }
    }
}
// Initialize admin class
if (is_admin() && class_exists('Gemini_SEO_Admin')) {
    new Gemini_SEO_Admin();
}
