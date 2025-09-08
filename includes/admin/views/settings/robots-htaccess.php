<?php
if (!defined('ABSPATH')) exit;
$robots_content = '';
$htaccess_content = '';
$robots_file = ABSPATH . 'robots.txt';
$htaccess_file = ABSPATH . '.htaccess';
$robots_backup = ABSPATH . 'robots-gemini-backup.txt';
$htaccess_backup = ABSPATH . '.htaccess-gemini-backup';
$robots_error = '';
$htaccess_error = '';

if (file_exists($robots_file)) {
    $robots_content = file_get_contents($robots_file);
}
if (file_exists($htaccess_file)) {
    $htaccess_content = file_get_contents($htaccess_file);
}

if (isset($_POST['gemini_seo_edit_files'])) {
    check_admin_referer('gemini_seo_edit_files');
    // Save robots.txt with rollback
    if (isset($_POST['robots_txt'])) {
        $result = apply_filters('gemini_seo_save_robots_txt', stripslashes($_POST['robots_txt']));
        if (is_wp_error($result)) {
            $robots_error = $result->get_error_message();
        } else {
            $robots_content = file_get_contents($robots_file);
        }
    }
    // Save .htaccess with rollback
    if (isset($_POST['htaccess'])) {
        $result = apply_filters('gemini_seo_save_htaccess', stripslashes($_POST['htaccess']));
        if (is_wp_error($result)) {
            $htaccess_error = $result->get_error_message();
        } else {
            $htaccess_content = file_get_contents($htaccess_file);
        }
    }
    if (!$robots_error && !$htaccess_error) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Files updated and backup created.', 'gemini-seo') . '</p></div>';
    } else {
        if ($robots_error) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($robots_error) . '</p></div>';
        }
        if ($htaccess_error) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($htaccess_error) . '</p></div>';
        }
    }
}
if (isset($_POST['gemini_seo_restore_files'])) {
    check_admin_referer('gemini_seo_edit_files');
    if (file_exists($robots_backup)) {
        copy($robots_backup, $robots_file);
        $robots_content = file_get_contents($robots_file);
    }
    if (file_exists($htaccess_backup)) {
        copy($htaccess_backup, $htaccess_file);
        $htaccess_content = file_get_contents($htaccess_file);
    }
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Backup restored.', 'gemini-seo') . '</p></div>';
}
add_filter('gemini_seo_save_robots_txt', function($content) {
    if (!class_exists('Gemini_SEO_Robots')) return $content;
    $robots = new Gemini_SEO_Robots();
    $result = $robots->save_robots_txt($content);
    return $result;
});
add_filter('gemini_seo_save_htaccess', function($content) {
    if (!class_exists('Gemini_SEO_Robots')) return $content;
    $robots = new Gemini_SEO_Robots();
    $result = $robots->save_htaccess($content);
    return $result;
});
?>
<div class="wrap">
    <h1><?php _e('Edit robots.txt & .htaccess', 'gemini-seo'); ?></h1>
    <form method="post">
        <?php wp_nonce_field('gemini_seo_edit_files'); ?>
        <h2>robots.txt</h2>
        <textarea name="robots_txt" rows="10" style="width:100%" spellcheck="false"><?php echo esc_textarea($robots_content); ?></textarea>
        <h2>.htaccess</h2>
        <textarea name="htaccess" rows="12" style="width:100%" spellcheck="false"><?php echo esc_textarea($htaccess_content); ?></textarea>
        <p>
            <button type="submit" name="gemini_seo_edit_files" class="button button-primary"><?php _e('Save & Backup', 'gemini-seo'); ?></button>
            <button type="submit" name="gemini_seo_restore_files" class="button"><?php _e('Restore Backup', 'gemini-seo'); ?></button>
        </p>
    </form>
</div>
