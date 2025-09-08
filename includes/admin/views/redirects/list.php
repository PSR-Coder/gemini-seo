<?php
// Get redirects
$redirects = Gemini_SEO_DB::get_redirects(array('status' => 'all', 'orderby' => 'id', 'order' => 'DESC'));

// Handle messages
$messages = array(
    'redirect_added' => __('Redirect added successfully.', 'gemini-seo'),
    'redirect_updated' => __('Redirect updated successfully.', 'gemini-seo'),
    'redirect_deleted' => __('Redirect deleted successfully.', 'gemini-seo'),
    'redirects_enabled' => __('Redirects enabled successfully.', 'gemini-seo'),
    'redirects_disabled' => __('Redirects disabled successfully.', 'gemini-seo'),
    'redirects_deleted' => __('Redirects deleted successfully.', 'gemini-seo'),
    'redirect_add_failed' => __('Failed to add redirect.', 'gemini-seo'),
    'redirect_update_failed' => __('Failed to update redirect.', 'gemini-seo'),
    'redirect_delete_failed' => __('Failed to delete redirect.', 'gemini-seo'),
    'no_items_selected' => __('No items selected.', 'gemini-seo'),
    'no_action_taken' => __('No action taken.', 'gemini-seo')
);

if (isset($_GET['message']) && isset($messages[$_GET['message']])) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($messages[$_GET['message']]) . '</p></div>';
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Redirect Manager', 'gemini-seo'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=gemini-seo-redirects&action=add'); ?>" class="page-title-action"><?php _e('Add New', 'gemini-seo'); ?></a>
    
    <hr class="wp-header-end">
    
    <form method="post" action="<?php echo admin_url('admin.php?page=gemini-seo-redirects'); ?>">
        <?php wp_nonce_field('gemini_seo_redirect_action', 'gemini_seo_redirect_nonce'); ?>
        <input type="hidden" name="action" value="bulk_redirects">
        
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="bulk_action">
                    <option value=""><?php _e('Bulk Actions', 'gemini-seo'); ?></option>
                    <option value="enable"><?php _e('Enable', 'gemini-seo'); ?></option>
                    <option value="disable"><?php _e('Disable', 'gemini-seo'); ?></option>
                    <option value="delete"><?php _e('Delete', 'gemini-seo'); ?></option>
                </select>
                <input type="submit" class="button action" value="<?php _e('Apply', 'gemini-seo'); ?>">
            </div>
            <br class="clear">
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all"></td>
                    <th class="manage-column"><?php _e('Source', 'gemini-seo'); ?></th>
                    <th class="manage-column"><?php _e('Destination', 'gemini-seo'); ?></th>
                    <th class="manage-column"><?php _e('Type', 'gemini-seo'); ?></th>
                    <th class="manage-column"><?php _e('Regex', 'gemini-seo'); ?></th>
                    <th class="manage-column"><?php _e('Status', 'gemini-seo'); ?></th>
                    <th class="manage-column"><?php _e('Count', 'gemini-seo'); ?></th>
                    <th class="manage-column"><?php _e('Last Accessed', 'gemini-seo'); ?></th>
                    <th class="manage-column"><?php _e('Actions', 'gemini-seo'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($redirects)) : ?>
                    <tr>
                        <td colspan="9"><?php _e('No redirects found.', 'gemini-seo'); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($redirects as $redirect) : ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="redirects[]" value="<?php echo $redirect->id; ?>">
                            </th>
                            <td><?php echo esc_html($redirect->source); ?></td>
                            <td><?php echo esc_html($redirect->destination); ?></td>
                            <td>
                                <?php 
                                $types = array(
                                    301 => '301 Moved Permanently',
                                    302 => '302 Found',
                                    307 => '307 Temporary Redirect'
                                );
                                echo isset($types[$redirect->type]) ? $types[$redirect->type] : $redirect->type;
                                ?>
                            </td>
                            <td><?php echo $redirect->regex ? __('Yes', 'gemini-seo') : __('No', 'gemini-seo'); ?></td>
                            <td>
                                <span class="gemini-seo-status <?php echo $redirect->status ? 'active' : 'inactive'; ?>">
                                    <?php echo $redirect->status ? __('Active', 'gemini-seo') : __('Inactive', 'gemini-seo'); ?>
                                </span>
                            </td>
                            <td><?php echo intval($redirect->count); ?></td>
                            <td><?php echo $redirect->last_accessed ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($redirect->last_accessed)) : __('Never', 'gemini-seo'); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=gemini-seo-redirects&action=edit&id=' . $redirect->id); ?>"><?php _e('Edit', 'gemini-seo'); ?></a> |
                                <a href="<?php echo admin_url('admin.php?page=gemini-seo-redirects&action=delete&id=' . $redirect->id); ?>" class="gemini-seo-delete-redirect"><?php _e('Delete', 'gemini-seo'); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>