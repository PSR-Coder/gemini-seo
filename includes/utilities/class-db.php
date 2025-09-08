<?php
class Gemini_SEO_DB {
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'gemini_seo_redirects';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            source varchar(255) NOT NULL,
            destination varchar(255) NOT NULL,
            type smallint(3) NOT NULL DEFAULT 301,
            regex tinyint(1) NOT NULL DEFAULT 0,
            status tinyint(1) NOT NULL DEFAULT 1,
            count mediumint(9) NOT NULL DEFAULT 0,
            created datetime DEFAULT CURRENT_TIMESTAMP,
            last_accessed datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY source (source),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Create 404 monitoring table
        $table_name_404 = $wpdb->prefix . 'gemini_seo_404_logs';
        
        $sql_404 = "CREATE TABLE $table_name_404 (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            url varchar(255) NOT NULL,
            referrer varchar(255) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent varchar(255) DEFAULT NULL,
            count mediumint(9) NOT NULL DEFAULT 1,
            created datetime DEFAULT CURRENT_TIMESTAMP,
            last_accessed datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY url (url),
            KEY created (created)
        ) $charset_collate;";
        
        dbDelta($sql_404);
    }
    
    public static function get_redirects($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => 1,
            'orderby' => 'id',
            'order' => 'ASC',
            'limit' => -1,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'gemini_seo_redirects';
        $sql = "SELECT * FROM $table_name WHERE 1=1";
        
        if ($args['status'] !== 'all') {
            $sql .= $wpdb->prepare(" AND status = %d", $args['status']);
        }
        
        if (!empty($args['search'])) {
            $sql .= $wpdb->prepare(" AND (source LIKE %s OR destination LIKE %s)", 
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%'
            );
        }
        
        $sql .= " ORDER BY " . sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        if ($args['limit'] > 0) {
            $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }
        
        return $wpdb->get_results($sql);
    }
    
    public static function get_redirect($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gemini_seo_redirects';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
    }
    
    public static function add_redirect($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gemini_seo_redirects';
        
        $defaults = array(
            'source' => '',
            'destination' => '',
            'type' => 301,
            'regex' => 0,
            'status' => 1
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'source' => sanitize_text_field($data['source']),
                'destination' => sanitize_text_field($data['destination']),
                'type' => intval($data['type']),
                'regex' => intval($data['regex']),
                'status' => intval($data['status'])
            ),
            array('%s', '%s', '%d', '%d', '%d')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    public static function update_redirect($id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gemini_seo_redirects';
        
        $fields = array();
        $formats = array();
        
        if (isset($data['source'])) {
            $fields['source'] = sanitize_text_field($data['source']);
            $formats[] = '%s';
        }
        
        if (isset($data['destination'])) {
            $fields['destination'] = sanitize_text_field($data['destination']);
            $formats[] = '%s';
        }
        
        if (isset($data['type'])) {
            $fields['type'] = intval($data['type']);
            $formats[] = '%d';
        }
        
        if (isset($data['regex'])) {
            $fields['regex'] = intval($data['regex']);
            $formats[] = '%d';
        }
        
        if (isset($data['status'])) {
            $fields['status'] = intval($data['status']);
            $formats[] = '%d';
        }
        
        if (empty($fields)) {
            return false;
        }
        
        return $wpdb->update(
            $table_name,
            $fields,
            array('id' => $id),
            $formats,
            array('%d')
        );
    }
    
    public static function delete_redirect($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gemini_seo_redirects';
        return $wpdb->delete($table_name, array('id' => $id), array('%d'));
    }
    
    public static function increment_redirect_count($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gemini_seo_redirects';
        
        return $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET count = count + 1, last_accessed = %s WHERE id = %d",
            current_time('mysql'),
            $id
        ));
    }
    
    public static function find_matching_redirect($url) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gemini_seo_redirects';
        
        // First try exact matches
        $redirect = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE source = %s AND status = 1",
            $url
        ));
        
        if ($redirect) {
            return $redirect;
        }
        
        // Then try regex matches
        $redirects = $wpdb->get_results("SELECT * FROM $table_name WHERE regex = 1 AND status = 1");
        
        foreach ($redirects as $redirect) {
            $pattern = '#' . $redirect->source . '#';
            if (preg_match($pattern, $url)) {
                return $redirect;
            }
        }
        
        return false;
    }
    
    // 404 monitoring methods
    public static function log_404($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gemini_seo_404_logs';
        
        $defaults = array(
            'url' => '',
            'referrer' => '',
            'ip_address' => '',
            'user_agent' => ''
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Check if this URL already exists in the logs
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE url = %s",
            $data['url']
        ));
        
        if ($existing) {
            // Update the existing record
            return $wpdb->update(
                $table_name,
                array(
                    'count' => $existing->count + 1,
                    'last_accessed' => current_time('mysql')
                ),
                array('id' => $existing->id),
                array('%d', '%s'),
                array('%d')
            );
        } else {
            // Insert a new record
            return $wpdb->insert(
                $table_name,
                array(
                    'url' => sanitize_text_field($data['url']),
                    'referrer' => sanitize_text_field($data['referrer']),
                    'ip_address' => sanitize_text_field($data['ip_address']),
                    'user_agent' => sanitize_text_field($data['user_agent'])
                ),
                array('%s', '%s', '%s', '%s')
            );
        }
    }
    
    public static function get_404_logs($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'orderby' => 'last_accessed',
            'order' => 'DESC',
            'limit' => 50,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'gemini_seo_404_logs';
        $sql = "SELECT * FROM $table_name WHERE 1=1";
        
        if (!empty($args['search'])) {
            $sql .= $wpdb->prepare(" AND url LIKE %s", '%' . $wpdb->esc_like($args['search']) . '%');
        }
        
        if (!empty($args['days'])) {
            $date = date('Y-m-d H:i:s', strtotime('-' . $args['days'] . ' days'));
            $sql .= $wpdb->prepare(" AND last_accessed >= %s", $date);
        }
        
        $sql .= " ORDER BY " . sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        if ($args['limit'] > 0) {
            $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }
        
        return $wpdb->get_results($sql);
    }
    
    public static function delete_404_log($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gemini_seo_404_logs';
        return $wpdb->delete($table_name, array('id' => $id), array('%d'));
    }
    
    public static function clear_404_logs() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gemini_seo_404_logs';
        return $wpdb->query("TRUNCATE TABLE $table_name");
    }
}
