<?php
class Gemini_SEO_Validators {
    public static function is_valid_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    public static function is_valid_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    public static function is_non_empty($value) {
        return !empty($value);
    }
}
