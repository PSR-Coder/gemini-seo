<?php
class Gemini_SEO_Import_Export {
    public function __construct() {
        // No hooks needed, logic is handled in admin UI for now
    }

    // Check user capability before import/export
    private function can_import_export() {
        return current_user_can('manage_options');
    }

    // Import settings with error handling and validation
    public function import_settings($json) {
        if (!$this->can_import_export()) {
            return new WP_Error('permission_denied', __('You do not have permission to import settings.', 'ds-gemini-seo'));
        }
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return new WP_Error('invalid_json', __('Invalid import file.', 'ds-gemini-seo'));
        }
        // Validate expected keys (example: redirects, settings)
        if (!isset($data['settings']) || !is_array($data['settings'])) {
            return new WP_Error('missing_settings', __('Settings data missing or invalid.', 'ds-gemini-seo'));
        }
        update_option('gemini_seo_settings', $data['settings']);
        return true;
    }

    // Export settings with error handling
    public function export_settings() {
        if (!$this->can_import_export()) {
            return new WP_Error('permission_denied', __('You do not have permission to export settings.', 'ds-gemini-seo'));
        }
        return json_encode(array('settings' => get_option('gemini_seo_settings')), JSON_PRETTY_PRINT);
    }
}
