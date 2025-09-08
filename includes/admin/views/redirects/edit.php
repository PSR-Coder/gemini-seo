<?php
$is_edit = isset($redirect);
$title = $is_edit ? __('Edit Redirect', 'gemini-seo') : __('Add New Redirect', 'gemini-seo');
$button_text = $is_edit ? __('Update Redirect', 'gemini-seo') : __('Add Redirect', 'gemini-seo');

// Default values
$source = $is_edit ? $redirect->source : '';
$destination = $is_edit ? $redirect->destination : '';
$type = $is_edit ? $redirect->type : 301;
$regex = $is_edit ? $redirect->regex : 0;
$status = $is_edit ? $redirect->status : 1;
?>

<div class="wrap">
    <h1><?php echo $title; ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin.php?page=gemini-seo-redirects'); ?>">
        <?php wp_nonce_field('gemini_seo_redirect_action', 'gemini_seo_redirect_nonce'); ?>
        <input type="hidden" name="action" value="save_redirect">
        <?php if ($is_edit) : ?>
            <input type="hidden" name="id" value="<?php echo $redirect->id; ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="source"><?php _e('Source URL', 'gemini-seo'); ?></label></th>
                <td>
                    <input type="text" name="source" id="source" value="<?php echo esc_attr($source); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('The URL that should be redirected (e.g. /old-page/ or a regex pattern).', 'gemini-seo'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="destination"><?php _e('Destination URL', 'gemini-seo'); ?></label></th>
                <td>
                    <input type="text" name="destination" id="destination" value="<?php echo esc_attr($destination); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('The target URL where visitors should be redirected.', 'gemini-seo'); ?>
                        <?php if ($regex) : ?>
                            <br><?php _e('For regex patterns, use $1, $2, etc. to capture parts of the source URL.', 'gemini-seo'); ?>
                        <?php endif; ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="type"><?php _e('Redirect Type', 'gemini-seo'); ?></label></th>
                <td>
                    <select name="type" id="type">
                        <option value="301" <?php selected($type, 301); ?>><?php _e('301 Moved Permanently', 'gemini-seo'); ?></option>
                        <option value="302" <?php selected($type, 302); ?>><?php _e('302 Found', 'gemini-seo'); ?></option>
                        <option value="307" <?php selected($type, 307); ?>><?php _e('307 Temporary Redirect', 'gemini-seo'); ?></option>
                    </select>
                    <p class="description">
                        <?php _e('301 redirects are permanent and pass the most SEO value.', 'gemini-seo'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Options', 'gemini-seo'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="regex" value="1" <?php checked($regex, 1); ?>>
                        <?php _e('Regular expression', 'gemini-seo'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Use regex patterns for advanced matching. Example: ^/old-path/(.*)$', 'gemini-seo'); ?>
                    </p>
                    
                    <br>
                    
                    <label>
                        <input type="checkbox" name="status" value="1" <?php checked($status, 1); ?>>
                        <?php _e('Active', 'gemini-seo'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Enable or disable this redirect rule.', 'gemini-seo'); ?>
                    </p>
                </td>
            </tr>
            
            <tr id="test-redirect-row" style="display: none;">
                <th scope="row"><?php _e('Test Redirect', 'gemini-seo'); ?></th>
                <td>
                    <button type="button" id="test-redirect" class="button"><?php _e('Test Redirect Pattern', 'gemini-seo'); ?></button>
                    <div id="test-result" style="margin-top: 10px;"></div>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $button_text; ?>">
            <a href="<?php echo admin_url('admin.php?page=gemini-seo-redirects'); ?>" class="button"><?php _e('Cancel', 'gemini-seo'); ?></a>
        </p>
    </form>
</div>