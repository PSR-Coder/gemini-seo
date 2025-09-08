<?php
class Gemini_SEO_Helpers {
    public static function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    public static function get_excerpt($content, $length = 30) {
        $words = explode(' ', strip_tags($content));
        return implode(' ', array_slice($words, 0, $length));
    }
    public static function get_post_thumbnail_url($post_id) {
        return has_post_thumbnail($post_id) ? wp_get_attachment_url(get_post_thumbnail_id($post_id)) : '';
    }
}
