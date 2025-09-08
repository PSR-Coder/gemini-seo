<?php
class Gemini_SEO_WooCommerce {
    public function __construct() {
        if (!class_exists('WooCommerce')) return;
        add_action('wp_head', array($this, 'output_product_schema'), 20);
    }
    public function output_product_schema() {
        if (!is_product()) return;
        global $product, $post;
        $schema = array(
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => get_the_title($post),
            'image' => wp_get_attachment_url(get_post_thumbnail_id($post)),
            'description' => get_the_excerpt($post),
            'sku' => $product->get_sku(),
            'offers' => array(
                '@type' => 'Offer',
                'priceCurrency' => get_woocommerce_currency(),
                'price' => $product->get_price(),
                'availability' => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'url' => get_permalink($post)
            )
        );
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
    }
}
if (class_exists('Gemini_SEO_WooCommerce')) new Gemini_SEO_WooCommerce();
