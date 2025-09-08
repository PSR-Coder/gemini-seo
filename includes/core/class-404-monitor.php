<?php
class Gemini_SEO_404_Monitor {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('template_redirect', array($this, 'track_404'), 1);
    }
    
    public function track_404() {
        if (is_404() && !is_admin() && !wp_doing_ajax() && !wp_doing_cron()) {
            $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $ip_address = $this->get_client_ip();
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            
            $data = array(
                'url' => $url,
                'referrer' => $referrer,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent
            );
            
            Gemini_SEO_DB::log_404($data);
        }
    }
    
    private function get_client_ip() {
        $ip_address = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip_address = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip_address;
    }

    // Simple rate limiting: 1 log per IP per 5 minutes
    private function can_log_404($ip) {
        $transient_key = 'gemini_404_last_' . md5($ip);
        if (get_transient($transient_key)) {
            return false;
        }
        set_transient($transient_key, 1, 5 * MINUTE_IN_SECONDS);
        return true;
    }

    public function log_404($url) {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!$this->can_log_404($ip)) {
            return false; // Rate limited
        }

        // Example 404 logging logic: store in an option
        $logs = get_option('gemini_404_logs', []);
        $logs[] = [
            'url' => $url,
            'ip' => $ip,
            'timestamp' => current_time('mysql'),
        ];
        update_option('gemini_404_logs', $logs);

        return true;
    }
}
