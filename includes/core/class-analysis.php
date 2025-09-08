<?php
class Gemini_SEO_Analysis {
    private $keyword;
    private $content;
    private $post;

    public function __construct() {
        add_action('wp_ajax_gemini_seo_analyze', array($this, 'ajax_analyze'));
    }

    public function analyze_content($post_id, $keyword) {
        $this->post = get_post($post_id);
        $this->keyword = strtolower($keyword);
        $this->content = apply_filters('the_content', $this->post->post_content);

        $results = array();

        // Perform various analyses
        $results['keyword_density'] = $this->calculate_keyword_density();
        $results['title_analysis'] = $this->analyze_title();
        $results['meta_description_analysis'] = $this->analyze_meta_description();
        $results['content_length'] = $this->check_content_length();
        $results['heading_analysis'] = $this->analyze_headings();
        $results['image_analysis'] = $this->analyze_images();
        $results['link_analysis'] = $this->analyze_links();
        $results['readability'] = $this->calculate_readability();

        return $results;
    }

    public function ajax_analyze() {
        check_ajax_referer('gemini_seo_analysis_nonce', 'nonce');
        $post_id = intval($_POST['post_id']);
        $keyword = sanitize_text_field($_POST['keyword']);
        $results = $this->analyze_content($post_id, $keyword);
        wp_send_json_success($results);
    }

    private function calculate_keyword_density() {
        $content = strip_tags($this->content);
        $content = preg_replace('/[^\w\s]/', '', $content);
        $words = str_word_count(strtolower($content));

        if ($words === 0) {
            return array('density' => 0, 'count' => 0, 'total_words' => 0, 'status' => 'low');
        }

        $keyword_count = substr_count(strtolower($content), $this->keyword);
        $density = ($keyword_count / $words) * 100;

        return array(
            'density' => round($density, 2),
            'count' => $keyword_count,
            'total_words' => $words,
            'status' => ($density >= 0.5 && $density <= 2.5) ? 'good' : (($density < 0.5) ? 'low' : 'high')
        );
    }
    
    private function analyze_title() {
        $title = get_the_title($this->post->ID);
        $length = mb_strlen($title);
        
        $has_keyword = stripos($title, $this->keyword) !== false;
        
        if ($length < 40) {
            $status = 'short';
        } elseif ($length > 60) {
            $status = 'long';
        } else {
            $status = 'good';
        }
        
        return array(
            'length' => $length,
            'has_keyword' => $has_keyword,
            'status' => $status
        );
    }
    
    private function calculate_readability() {
        $content = strip_tags($this->content);
        $total_sentences = max(1, preg_match_all('/[.!?]+/', $content));
        $total_words = str_word_count($content);
        $total_syllables = $this->count_syllables($content);

        if ($total_words === 0 || $total_sentences === 0) {
            return array('score' => 0, 'status' => 'poor');
        }

        $score = 206.835 - (1.015 * ($total_words / $total_sentences)) - (84.6 * ($total_syllables / $total_words));
        if ($score >= 90) {
            $status = 'very_easy';
        } elseif ($score >= 80) {
            $status = 'easy';
        } elseif ($score >= 70) {
            $status = 'fairly_easy';
        } elseif ($score >= 60) {
            $status = 'standard';
        } elseif ($score >= 50) {
            $status = 'fairly_difficult';
        } elseif ($score >= 30) {
            $status = 'difficult';
        } else {
            $status = 'very_difficult';
        }
        return array('score' => round($score, 1), 'status' => $status);
    }

    private function analyze_meta_description() {
        $meta = get_post_meta($this->post->ID, '_gemini_seo_meta_description', true);
        $length = mb_strlen($meta);
        $has_keyword = stripos($meta, $this->keyword) !== false;
        if ($length < 120) {
            $status = 'short';
        } elseif ($length > 160) {
            $status = 'long';
        } else {
            $status = 'good';
        }
        return array('length' => $length, 'has_keyword' => $has_keyword, 'status' => $status);
    }

    private function check_content_length() {
        $content = strip_tags($this->content);
        $length = mb_strlen($content);
        if ($length < 300) {
            $status = 'short';
        } elseif ($length > 2500) {
            $status = 'long';
        } else {
            $status = 'good';
        }
        return array('length' => $length, 'status' => $status);
    }

    private function analyze_headings() {
        preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h\1>/i', $this->content, $matches);
        $headings = $matches[2];
        $keyword_in_headings = 0;
        foreach ($headings as $heading) {
            if (stripos($heading, $this->keyword) !== false) {
                $keyword_in_headings++;
            }
        }
        return array('total' => count($headings), 'keyword_in_headings' => $keyword_in_headings);
    }

    private function analyze_images() {
        preg_match_all('/<img[^>]+alt=["\"](.*?)["\"]/i', $this->content, $matches);
        $alts = $matches[1];
        $missing_alt = 0;
        foreach ($alts as $alt) {
            if (empty(trim($alt))) {
                $missing_alt++;
            }
        }
        return array('total' => count($alts), 'missing_alt' => $missing_alt);
    }

    private function analyze_links() {
        preg_match_all('/<a [^>]*href=["\"](.*?)["\"]/i', $this->content, $matches);
        $links = $matches[1];
        $internal = $external = 0;
        foreach ($links as $link) {
            if (strpos($link, home_url()) !== false || (strpos($link, '/') === 0 && strpos($link, '//') !== 0)) {
                $internal++;
            } else {
                $external++;
            }
        }
        return array('total' => count($links), 'internal' => $internal, 'external' => $external);
    }

    private function count_syllables($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z\s]/', '', $text);
        $words = explode(' ', $text);
        $syllables = 0;
        foreach ($words as $word) {
            $word = trim($word);
            if ($word === '') continue;
            $word_syllables = preg_match_all('/[aeiouy]+/', $word, $matches);
            $syllables += $word_syllables;
        }
        return $syllables;
    }
}
