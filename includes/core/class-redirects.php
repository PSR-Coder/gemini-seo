<?php
class Gemini_SEO_Redirects {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('template_redirect', array($this, 'handle_redirects'), 1);
        add_action('admin_init', array($this, 'handle_redirect_actions'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_gemini_seo_test_redirect', array($this, 'test_redirect'));
    }
    
    public function handle_redirects() {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }
        
        $current_url = $this->get_current_url();
        $redirect = Gemini_SEO_DB::find_matching_redirect($current_url);
        
        if ($redirect) {
            $destination = $redirect->destination;
            
            // Handle regex captures
            if ($redirect->regex) {
                $pattern = '#' . $redirect->source . '#';
                if (preg_match($pattern, $current_url, $matches)) {
                    foreach ($matches as $key => $value) {
                        if (is_numeric($key)) {
                            $destination = str_replace('$' . $key, $value, $destination);
                        }
                    }
                }
            }
            
            // Make sure the destination is a full URL if it's not a relative path
            if (strpos($destination, '/') === 0) {
                $destination = home_url($destination);
            } elseif (!preg_match('/^https?:\/\//', $destination)) {
                $destination = home_url('/' . $destination);
            }
            
            // Increment the redirect count
            Gemini_SEO_DB::increment_redirect_count($redirect->id);
            
            // Perform the redirect
            wp_redirect($destination, $redirect->type);
            exit;
        }
    }
    
    private function get_current_url() {
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        return $host . $uri;
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'gemini-seo',
            __('Redirect Manager', 'gemini-seo'),
            __('Redirects', 'gemini-seo'),
            'manage_options',
            'gemini-seo-redirects',
            array($this, 'render_redirects_page')
        );
        
        add_submenu_page(
            'gemini-seo',
            __('404 Monitor', 'gemini-seo'),
            __('404 Monitor', 'gemini-seo'),
            'manage_options',
            'gemini-seo-404',
            array($this, 'render_404_page')
        );
    }
    
    public function render_redirects_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        switch ($action) {
            case 'edit':
            case 'add':
                $redirect = $id ? Gemini_SEO_DB::get_redirect($id) : null;
                include GEMINI_SEO_PATH . 'includes/admin/views/redirects/edit.php';
                break;
            default:
                include GEMINI_SEO_PATH . 'includes/admin/views/redirects/list.php';
                break;
        }
    }
    
    public function render_404_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        include GEMINI_SEO_PATH . 'includes/admin/views/redirects/404.php';
    }
    
    public function handle_redirect_actions() {
        if (!isset($_POST['gemini_seo_redirect_nonce']) || !wp_verify_nonce($_POST['gemini_seo_redirect_nonce'], 'gemini_seo_redirect_action')) {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        switch ($action) {
            case 'save_redirect':
                $this->save_redirect($id);
                break;
            case 'delete_redirect':
                $this->delete_redirect($id);
                break;
            case 'bulk_redirects':
                $this->bulk_redirects_action();
                break;
            case 'delete_404':
                $this->delete_404_log($id);
                break;
            case 'clear_404_logs':
                $this->clear_404_logs();
                break;
            case 'create_redirect_from_404':
                $this->create_redirect_from_404();
                break;
        }
    }
    
    private function save_redirect($id) {
        $data = array(
            'source' => isset($_POST['source']) ? sanitize_text_field($_POST['source']) : '',
            'destination' => isset($_POST['destination']) ? esc_url_raw($_POST['destination']) : '',
            'type' => isset($_POST['type']) ? intval($_POST['type']) : 301,
            'regex' => isset($_POST['regex']) ? 1 : 0,
            'status' => isset($_POST['status']) ? 1 : 0
        );
        
        // Validate the data
        if (empty($data['source']) || empty($data['destination'])) {
            wp_die(__('Source and destination are required.', 'gemini-seo'));
        }
        
        // Check if this is a regex pattern and validate it
        if ($data['regex']) {
            $test_pattern = '#' . $data['source'] . '#';
            if (@preg_match($test_pattern, null) === false) {
                wp_die(__('Invalid regular expression pattern.', 'gemini-seo'));
            }
        }
        
        if ($id) {
            $result = Gemini_SEO_DB::update_redirect($id, $data);
            $message = $result ? 'redirect_updated' : 'redirect_update_failed';
        } else {
            $result = Gemini_SEO_DB::add_redirect($data);
            $message = $result ? 'redirect_added' : 'redirect_add_failed';
        }
        
        wp_redirect(admin_url('admin.php?page=gemini-seo-redirects&message=' . $message));
        exit;
    }
    
    private function delete_redirect($id) {
        if ($id) {
            $result = Gemini_SEO_DB::delete_redirect($id);
            $message = $result ? 'redirect_deleted' : 'redirect_delete_failed';
        } else {
            $message = 'redirect_delete_failed';
        }
        
        wp_redirect(admin_url('admin.php?page=gemini-seo-redirects&message=' . $message));
        exit;
    }
    
    private function bulk_redirects_action() {
        $action = isset($_POST['bulk_action']) ? $_POST['bulk_action'] : '';
        $redirects = isset($_POST['redirects']) ? array_map('intval', $_POST['redirects']) : array();
        
        if (empty($redirects)) {
            wp_redirect(admin_url('admin.php?page=gemini-seo-redirects&message=no_items_selected'));
            exit;
        }
        
        switch ($action) {
            case 'enable':
                foreach ($redirects as $id) {
                    Gemini_SEO_DB::update_redirect($id, array('status' => 1));
                }
                $message = 'redirects_enabled';
                break;
            case 'disable':
                foreach ($redirects as $id) {
                    Gemini_SEO_DB::update_redirect($id, array('status' => 0));
                }
                $message = 'redirects_disabled';
                break;
            case 'delete':
                foreach ($redirects as $id) {
                    Gemini_SEO_DB::delete_redirect($id);
                }
                $message = 'redirects_deleted';
                break;
            default:
                $message = 'no_action_taken';
                break;
        }
        
        wp_redirect(admin_url('admin.php?page=gemini-seo-redirects&message=' . $message));
        exit;
    }
    
    private function delete_404_log($id) {
        if ($id) {
            $result = Gemini_SEO_DB::delete_404_log($id);
            $message = $result ? '404_deleted' : '404_delete_failed';
        } else {
            $message = '404_delete_failed';
        }
        
        wp_redirect(admin_url('admin.php?page=gemini-seo-404&message=' . $message));
        exit;
    }
    
    private function clear_404_logs() {
        $result = Gemini_SEO_DB::clear_404_logs();
        $message = $result ? '404_logs_cleared' : '404_logs_clear_failed';
        
        wp_redirect(admin_url('admin.php?page=gemini-seo-404&message=' . $message));
        exit;
    }
    
    private function create_redirect_from_404() {
        $url = isset($_POST['url']) ? sanitize_text_field($_POST['url']) : '';
        $destination = isset($_POST['destination']) ? esc_url_raw($_POST['destination']) : '';
        $type = isset($_POST['type']) ? intval($_POST['type']) : 301;
        
        if (empty($url) || empty($destination)) {
            wp_die(__('URL and destination are required.', 'gemini-seo'));
        }
        
        $data = array(
            'source' => $url,
            'destination' => $destination,
            'type' => $type,
            'regex' => 0,
            'status' => 1
        );
        
        $result = Gemini_SEO_DB::add_redirect($data);
        $message = $result ? 'redirect_created_from_404' : 'redirect_create_failed';
        
        wp_redirect(admin_url('admin.php?page=gemini-seo-404&message=' . $message));
        exit;
    }
    
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'gemini-seo-redirects') === false && strpos($hook, 'gemini-seo-404') === false) {
            return;
        }
        
        wp_enqueue_style('gemini-seo-redirects', GEMINI_SEO_URL . 'includes/admin/assets/css/redirects.css', array(), GEMINI_SEO_VERSION);
        wp_enqueue_script('gemini-seo-redirects', GEMINI_SEO_URL . 'includes/admin/assets/js/redirects.js', array('jquery'), GEMINI_SEO_VERSION, true);
        
        wp_localize_script('gemini-seo-redirects', 'gemini_seo_redirects', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gemini_seo_redirects_nonce'),
            'confirm_delete' => __('Are you sure you want to delete this redirect?', 'gemini-seo'),
            'confirm_clear_logs' => __('Are you sure you want to clear all 404 logs? This cannot be undone.', 'gemini-seo')
        ));
    }
    
    public function test_redirect() {
        check_ajax_referer('gemini_seo_redirects_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'gemini-seo'));
        }
        $source = isset($_POST['source']) ? sanitize_text_field($_POST['source']) : '';
        $destination = isset($_POST['destination']) ? esc_url_raw($_POST['destination']) : '';
        $regex = isset($_POST['regex']) ? (bool)$_POST['regex'] : false;
        
        if (empty($source) || empty($destination)) {
            wp_send_json_error(__('Source and destination are required.', 'gemini-seo'));
        }
        
        $test_url = 'https://example.com/test-url';
        
        if ($regex) {
            $pattern = '#' . $source . '#';
            if (@preg_match($pattern, $test_url, $matches)) {
                $result = $destination;
                foreach ($matches as $key => $value) {
                    if (is_numeric($key)) {
                        $result = str_replace('$' . $key, $value, $result);
                    }
                }
                wp_send_json_success(array(
                    'message' => __('Regex pattern is valid.', 'gemini-seo'),
                    'example' => sprintf(__('Example: %s would redirect to %s', 'gemini-seo'), $test_url, $result)
                ));
            } else {
                wp_send_json_error(__('Invalid regex pattern.', 'gemini-seo'));
            }
        } else {
            wp_send_json_success(array(
                'message' => __('Simple redirect configured.', 'gemini-seo'),
                'example' => sprintf(__('%s would redirect to %s', 'gemini-seo'), $source, $destination)
            ));
        }
    }
    
    // Check user capability before managing redirects
    private function can_manage_redirects() {
        return current_user_can('manage_options');
    }

    // Validate regex pattern for security and correctness
    private function is_valid_regex($pattern) {
        // Only allow regex starting and ending with delimiters, e.g. #pattern#
        if (!preg_match('/^([\/#~|@%]).+\1[imsxeADSUXJu]*$/', $pattern)) {
            return false;
        }
        // Try to compile the regex
        set_error_handler(function() {}, E_WARNING);
        $is_valid = @preg_match($pattern, '') !== false;
        restore_error_handler();
        return $is_valid;
    }

    // Check for redirect loop or self-redirect
    private function is_redirect_loop($from, $to) {
        $from_url = untrailingslashit(parse_url($from, PHP_URL_PATH));
        $to_url = untrailingslashit(parse_url($to, PHP_URL_PATH));
        // Self-redirect
        if ($from_url === $to_url) {
            return true;
        }
        // Check for existing chain/loop (basic: to matches any from)
        $redirects = $this->get_all_redirects();
        $visited = [$from_url];
        $current = $to_url;
        $max_depth = 10;
        $depth = 0;
        while ($current && $depth < $max_depth) {
            foreach ($redirects as $r) {
                $r_from = untrailingslashit(parse_url($r['from'], PHP_URL_PATH));
                $r_to = untrailingslashit(parse_url($r['to'], PHP_URL_PATH));
                if ($r_from === $current) {
                    if (in_array($r_to, $visited)) {
                        return true; // Loop detected
                    }
                    $visited[] = $r_to;
                    $current = $r_to;
                    break;
                }
            }
            $depth++;
            if ($depth === count($visited)) break;
        }
        return false;
    }

    // Add redirect with validation and error handling
    public function add_redirect($from, $to, $type = 301, $is_regex = false) {
        if (!$this->can_manage_redirects()) {
            return new WP_Error('permission_denied', __('You do not have permission to add redirects.', 'ds-gemini-seo'));
        }
        $from = trim($from);
        $to = esc_url_raw(trim($to));
        $type = intval($type);
        if (empty($from) || empty($to) || !in_array($type, array(301,302,307))) {
            return new WP_Error('invalid_input', __('Invalid redirect data.', 'ds-gemini-seo'));
        }
        if ($is_regex && !$this->is_valid_regex($from)) {
            return new WP_Error('invalid_regex', __('Invalid or unsafe regex pattern.', 'ds-gemini-seo'));
        }
        if ($this->is_redirect_loop($from, $to)) {
            return new WP_Error('redirect_loop', __('Redirect would cause a loop or self-redirect.', 'ds-gemini-seo'));
        }
        // ...add redirect logic...
        return true;
    }
}