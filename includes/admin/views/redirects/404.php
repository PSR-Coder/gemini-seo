<?php
// Get 404 logs
$logs = Gemini_SEO_DB::get_404_logs(array('orderby' => 'last_accessed', 'order' => 'DESC', 'limit' => 50));

// Handle messages
$messages = array(
    '404_deleted' => __('404 log deleted successfully.', 'gemini-seo'),
    '404_logs_cleared' => __('404 logs cleared successfully.', 'gemini-seo'),
    'redirect_created_from_404' => __('Redirect created successfully.', 'gemini-seo'),
    '404_delete_failed' => __('Failed to delete 404 log.', 'gemini-seo'),
    '404_logs_clear_failed' => __('Failed to clear 404 logs.', 'gemini-seo'),
    'redirect_create_failed' => __('Failed to create redirect.', 'gemini-seo')
);

if (isset($_GET['message']) && isset($messages[$_GET['message']])) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($messages[$_GET['message']]) . '</p></div>';
}
?>

<div class="wrap">
    <h1><?php _e('404 Error Monitor', 'gemini-seo'); ?></h1>
    
    <?php if (!empty($logs)) : ?>
        <form method="post" action="<?php echo admin_url('admin.php?page=gemini-seo-404'); ?>">
            <?php wp_nonce_field('gemini_seo_redirect_action', 'gemini_seo_redirect_nonce'); ?>
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <input type="submit" name="action" value="clear_404_logs" class="button" onclick="return confirm('<?php _e('Are you sure you want to clear all 404 logs? This cannot be undone.', 'gemini-seo'); ?>')">
                </div>
                <br class="clear">
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="manage-column"><?php _e('URL', 'gemini-seo'); ?></th>
                        <th class="manage-column"><?php _e('Referrer', 'gemini-seo'); ?></th>
                        <th class="manage-column"><?php _e('IP Address', 'gemini-seo'); ?></th>
                        <th class="manage-column"><?php _e('Count', 'gemini-seo'); ?></th>
                        <th class="manage-column"><?php _e('First Seen', 'gemini-seo'); ?></th>
                        <th class="manage-column"><?php _e('Last Seen', 'gemini-seo'); ?></th>
                        <th class="manage-column"><?php _e('Actions', 'gemini-seo'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log) : ?>
                        <tr>
                            <td><?php echo esc_html($log->url); ?></td>
                            <td><?php echo $log->referrer ? '<a href="' . esc_url($log->referrer) . '" target="_blank">' . esc_html($log->referrer) . '</a>' : __('Direct', 'gemini-seo'); ?></td>
                            <td><?php echo esc_html($log->ip_address); ?></td>
                            <td><?php echo intval($log->count); ?></td>
                            <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->created)); ?></td>
                            <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->last_accessed)); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=gemini-seo-404&action=delete&id=' . $log->id); ?>" class="gemini-seo-delete-404"><?php _e('Delete', 'gemini-seo'); ?></a> |
                                <a href="#" class="gemini-seo-create-redirect" data-url="<?php echo esc_attr($log->url); ?>"><?php _e('Create Redirect', 'gemini-seo'); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
        <?php else : ?>
            <p><?php _e('No 404 errors logged yet.', 'gemini-seo'); ?></p>
        <?php endif; ?>

        <!-- Modal for creating redirect from 404 log -->
        <div id="gemini-seo-create-redirect-modal" style="display:none;position:fixed;top:10%;left:50%;transform:translateX(-50%);background:#fff;z-index:9999;padding:30px 40px;border:1px solid #ccc;box-shadow:0 2px 10px rgba(0,0,0,0.2);">
            <h2><?php _e('Create Redirect', 'gemini-seo'); ?></h2>
            <form method="post" action="<?php echo admin_url('admin.php?page=gemini-seo-404'); ?>">
                <?php wp_nonce_field('gemini_seo_redirect_action', 'gemini_seo_redirect_nonce'); ?>
                <input type="hidden" name="action" value="create_redirect_from_404">
                <table class="form-table">
                    <tr>
                        <th><label for="redirect-source-url"><?php _e('Source URL', 'gemini-seo'); ?></label></th>
                        <td><input type="text" id="redirect-source-url" name="url" value="" readonly style="width:100%"></td>
                    </tr>
                    <tr>
                        <th><label for="redirect-destination-url"><?php _e('Destination URL', 'gemini-seo'); ?></label></th>
                        <td><input type="text" id="redirect-destination-url" name="destination" value="" style="width:100%"></td>
                    </tr>
                    <tr>
                        <th><label for="redirect-type"><?php _e('Redirect Type', 'gemini-seo'); ?></label></th>
                        <td>
                            <select id="redirect-type" name="type">
                                <option value="301">301 (Permanent)</option>
                                <option value="302">302 (Temporary)</option>
                                <option value="307">307 (Temporary Redirect)</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p>
                    <button type="submit" class="button button-primary"><?php _e('Create Redirect', 'gemini-seo'); ?></button>
                    <button type="button" class="button gemini-seo-close-modal"><?php _e('Cancel', 'gemini-seo'); ?></button>
                </p>
            </form>
        </div>
    </div>
    <?php else : ?>
        <p><?php _e('No 404 errors logged yet.', 'gemini-seo'); ?></p>
    <?php endif; ?>
</div>

<!-- Create Redirect Modal -->
<div id="gemini-seo-create-redirect-modal" style="display: none;">
    <div class="gemini-seo-modal-content">
        <h2><?php _e('Create Redirect from 404', 'gemini-seo'); ?></h2>
        
        <form method="post" action="<?php echo admin_url('admin.php?page=gemini-seo-404'); ?>">
            <?php wp_nonce_field('gemini_seo_redirect_action', 'gemini_seo_redirect_nonce'); ?>
            <input type="hidden" name="action" value="create_redirect_from_404">
            <input type="hidden" name="url" id="redirect-source-url" value="">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="redirect-destination"><?php _e('Destination URL', 'gemini-seo'); ?></label></th>
                    <td>
                        <input type="text" name="destination" id="redirect-destination" class="regular-text" required>
                        <p class="description"><?php _e('Enter the URL where this 404 should redirect to.', 'gemini-seo'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="redirect-type"><?php _e('Redirect Type', 'gemini-seo'); ?></label></th>
                    <td>
                        <select name="type" id="redirect-type">
                            <option value="301"><?php _e('301 Moved Permanently', 'gemini-seo'); ?></option>
                            <option value="302"><?php _e('302 Found', 'gemini-seo'); ?></option>
                            <option value="307"><?php _e('307 Temporary Redirect', 'gemini-seo'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" class="button button-primary" value="<?php _e('Create Redirect', 'gemini-seo'); ?>">
                <button type="button" class="button gemini-seo-close-modal"><?php _e('Cancel', 'gemini-seo'); ?></button>
            </p>
        </form>
    </div>
</div>
