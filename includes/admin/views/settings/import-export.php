<?php
if (!defined('ABSPATH')) exit;

if (isset($_POST['gemini_seo_export_settings'])) {
    check_admin_referer('gemini_seo_import_export');
    $settings = get_option('gemini_seo_settings');
    $filename = 'gemini-seo-settings-' . date('Ymd-His') . '.json';
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename=' . $filename);
    echo json_encode($settings, JSON_PRETTY_PRINT);
    exit;
}

if (isset($_POST['gemini_seo_import_settings'])) {
    check_admin_referer('gemini_seo_import_export');
    if (!empty($_FILES['import_file']['tmp_name'])) {
        $import = json_decode(file_get_contents($_FILES['import_file']['tmp_name']), true);
        if (is_array($import)) {
            update_option('gemini_seo_settings', $import);
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings imported successfully.', 'gemini-seo') . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Invalid import file.', 'gemini-seo') . '</p></div>';
        }
    }
}
?>
<div class="wrap">
    <h1><?php _e('Import & Export Gemini SEO Settings', 'gemini-seo'); ?></h1>
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('gemini_seo_import_export'); ?>
        <h2><?php _e('Export Settings', 'gemini-seo'); ?></h2>
        <p><?php _e('Download your current Gemini SEO settings as a backup or to migrate to another site.', 'gemini-seo'); ?></p>
        <button type="submit" name="gemini_seo_export_settings" class="button button-primary"><?php _e('Export Settings', 'gemini-seo'); ?></button>
        <hr>
        <h2><?php _e('Import Settings', 'gemini-seo'); ?></h2>
        <p><?php _e('Import Gemini SEO settings from a backup file.', 'gemini-seo'); ?></p>
        <input type="file" name="import_file" accept="application/json">
        <button type="submit" name="gemini_seo_import_settings" class="button"><?php _e('Import Settings', 'gemini-seo'); ?></button>
    </form>
</div>
