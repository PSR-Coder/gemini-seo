<?php
if (!defined('ABSPATH')) exit;
$settings = get_option('gemini_seo_settings');
$post_types = get_post_types(['public' => true], 'objects');
$taxonomies = get_taxonomies(['public' => true], 'objects');
?>
<div class="wrap">
    <h1><?php _e('Gemini SEO - General Settings', 'gemini-seo'); ?></h1>
    <form method="post" action="options.php">
        <?php settings_fields('gemini_seo_settings'); ?>
        <?php do_settings_sections('gemini-seo'); ?>
        <h2><?php _e('Sitemap Settings', 'gemini-seo'); ?></h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Enable XML Sitemap', 'gemini-seo'); ?></th>
                <td>
                    <input type="checkbox" name="gemini_seo_settings[enable_sitemap]" value="1" <?php checked(isset($settings['enable_sitemap']) && $settings['enable_sitemap']); ?> />
                    <label><?php _e('Enable XML Sitemap functionality', 'gemini-seo'); ?></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Exclude Post Types', 'gemini-seo'); ?></th>
                <td>
                    <?php foreach ($post_types as $type) : ?>
                        <label><input type="checkbox" name="gemini_seo_settings[sitemap_exclude_post_types][]" value="<?php echo esc_attr($type->name); ?>" <?php if (!empty($settings['sitemap_exclude_post_types']) && in_array($type->name, $settings['sitemap_exclude_post_types'])) echo 'checked'; ?> /> <?php echo esc_html($type->labels->singular_name); ?></label><br>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Exclude Taxonomies', 'gemini-seo'); ?></th>
                <td>
                    <?php foreach ($taxonomies as $tax) : ?>
                        <label><input type="checkbox" name="gemini_seo_settings[sitemap_exclude_taxonomies][]" value="<?php echo esc_attr($tax->name); ?>" <?php if (!empty($settings['sitemap_exclude_taxonomies']) && in_array($tax->name, $settings['sitemap_exclude_taxonomies'])) echo 'checked'; ?> /> <?php echo esc_html($tax->labels->singular_name); ?></label><br>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Ping Google?', 'gemini-seo'); ?></th>
                <td>
                    <input type="checkbox" name="gemini_seo_settings[ping_google]" value="1" <?php checked(isset($settings['ping_google']) && $settings['ping_google']); ?> />
                    <label><?php _e('Notify Google when sitemap updates', 'gemini-seo'); ?></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Ping Bing?', 'gemini-seo'); ?></th>
                <td>
                    <input type="checkbox" name="gemini_seo_settings[ping_bing]" value="1" <?php checked(isset($settings['ping_bing']) && $settings['ping_bing']); ?> />
                    <label><?php _e('Notify Bing when sitemap updates', 'gemini-seo'); ?></label>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
