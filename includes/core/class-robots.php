<?php
class Gemini_SEO_Robots {
    public function __construct() {
        // No hooks needed, logic is handled in admin UI for now
    }

    public static function get_file($type = 'robots') {
        $file = ($type === 'robots') ? ABSPATH . 'robots.txt' : ABSPATH . '.htaccess';
        return file_exists($file) ? file_get_contents($file) : '';
    }

    public static function save_file($type, $content) {
        $file = ($type === 'robots') ? ABSPATH . 'robots.txt' : ABSPATH . '.htaccess';
        $backup = $file . '-gemini-backup';
        if (file_exists($file)) copy($file, $backup);
        file_put_contents($file, $content);
    }

    public static function restore_file($type) {
        $file = ($type === 'robots') ? ABSPATH . 'robots.txt' : ABSPATH . '.htaccess';
        $backup = $file . '-gemini-backup';
        if (file_exists($backup)) copy($backup, $file);
    }

    // Check user capability before file operations
    private function can_edit_robots() {
        return current_user_can('manage_options');
    }

    // Save robots.txt content with error handling, validation, and rollback
    public function save_robots_txt($content) {
        if (!$this->can_edit_robots()) {
            return new WP_Error('permission_denied', __('You do not have permission to edit robots.txt.', 'ds-gemini-seo'));
        }
        $content = sanitize_textarea_field($content);
        $file = ABSPATH . 'robots.txt';
        $backup = $file . '.bak';
        // Backup current file
        if (file_exists($file)) {
            if (!@copy($file, $backup)) {
                return new WP_Error('backup_failed', __('Failed to create backup of robots.txt.', 'ds-gemini-seo'));
            }
        }
        if (!is_writable(ABSPATH)) {
            return new WP_Error('not_writable', __('The root directory is not writable.', 'ds-gemini-seo'));
        }
        // Attempt to write new content
        if (file_put_contents($file, $content) === false) {
            // Rollback: restore backup
            if (file_exists($backup)) {
                @copy($backup, $file);
            }
            return new WP_Error('write_failed', __('Failed to write robots.txt. Rolled back to previous version.', 'ds-gemini-seo'));
        }
        // Remove backup on success
        if (file_exists($backup)) {
            @unlink($backup);
        }
        return true;
    }

    // Save .htaccess content with error handling, validation, and rollback
    public function save_htaccess($content) {
        if (!$this->can_edit_robots()) {
            return new WP_Error('permission_denied', __('You do not have permission to edit .htaccess.', 'ds-gemini-seo'));
        }
        $content = sanitize_textarea_field($content);
        $file = ABSPATH . '.htaccess';
        $backup = $file . '.bak';
        // Backup current file
        if (file_exists($file)) {
            if (!@copy($file, $backup)) {
                return new WP_Error('backup_failed', __('Failed to create backup of .htaccess.', 'ds-gemini-seo'));
            }
        }
        if (!is_writable(ABSPATH)) {
            return new WP_Error('not_writable', __('The root directory is not writable.', 'ds-gemini-seo'));
        }
        // Attempt to write new content
        if (file_put_contents($file, $content) === false) {
            // Rollback: restore backup
            if (file_exists($backup)) {
                @copy($backup, $file);
            }
            return new WP_Error('write_failed', __('Failed to write .htaccess. Rolled back to previous version.', 'ds-gemini-seo'));
        }
        // Remove backup on success
        if (file_exists($backup)) {
            @unlink($backup);
        }
        return true;
    }
}
