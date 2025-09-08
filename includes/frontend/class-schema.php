<?php
class Gemini_SEO_Schema {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_head', array($this, 'output_schema_markup'), 1);
        add_filter('gemini_seo_schema_output', array($this, 'filter_schema_output'), 10, 2);
    }
    
    public function output_schema_markup() {
        $schema = array();
        
        if (is_front_page() || is_home()) {
            $schema[] = $this->get_website_schema();
            $schema[] = $this->get_organization_schema();
        } elseif (is_singular()) {
            global $post;
            $schema_type = get_post_meta($post->ID, '_gemini_seo_schema_type', true);
            switch ($schema_type) {
                case 'NewsArticle':
                    $schema[] = $this->get_news_article_schema($post);
                    break;
                case 'BlogPosting':
                    $schema[] = $this->get_blog_posting_schema($post);
                    break;
                case 'Recipe':
                    $schema[] = $this->get_recipe_schema($post);
                    break;
                case 'FAQPage':
                    $schema[] = $this->get_faq_schema($post);
                    break;
                case 'HowTo':
                    $schema[] = $this->get_howto_schema($post);
                    break;
                case 'Article':
                default:
                    $schema[] = $this->get_article_schema($post);
                    break;
            }
        } elseif (is_author()) {
            $schema[] = $this->get_author_schema();
        }

        // Filter the schema array
        $schema = apply_filters('gemini_seo_schema', $schema);

        // Output the schema
        if (!empty($schema)) {
            echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
        }
    }
    // BlogPosting schema
    private function get_blog_posting_schema($post) {
        $author = get_the_author_meta('display_name', $post->post_author);
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => get_the_title($post),
            'datePublished' => get_the_date('c', $post),
            'dateModified' => get_the_modified_date('c', $post),
            'author' => array(
                '@type' => 'Person',
                'name' => $author
            ),
            'mainEntityOfPage' => get_permalink($post),
            'image' => wp_get_attachment_url(get_post_thumbnail_id($post)),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => get_site_icon_url()
                )
            ),
            'description' => get_the_excerpt($post)
        );
    }

    // NewsArticle schema
    private function get_news_article_schema($post) {
        $author = get_the_author_meta('display_name', $post->post_author);
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => get_the_title($post),
            'datePublished' => get_the_date('c', $post),
            'dateModified' => get_the_modified_date('c', $post),
            'author' => array(
                '@type' => 'Person',
                'name' => $author
            ),
            'mainEntityOfPage' => get_permalink($post),
            'image' => wp_get_attachment_url(get_post_thumbnail_id($post)),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => get_site_icon_url()
                )
            ),
            'description' => get_the_excerpt($post)
        );
    }

    // HowTo schema (basic)
    private function get_howto_schema($post) {
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => get_the_title($post),
            'description' => get_the_excerpt($post),
            'step' => array(
                array('name' => __('Step 1', 'gemini-seo')),
                array('name' => __('Step 2', 'gemini-seo'))
            ),
            'mainEntityOfPage' => get_permalink($post)
        );
    }

    // FAQPage schema (basic)
    private function get_faq_schema($post) {
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array(
                array(
                    '@type' => 'Question',
                    'name' => __('Sample Question', 'gemini-seo'),
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text' => __('Sample Answer', 'gemini-seo')
    private function get_recipe_schema($post) {
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'Recipe',
            'name' => get_the_title($post),
            'description' => get_the_excerpt($post),
            'recipeIngredient' => array(__('Ingredient 1', 'gemini-seo'), __('Ingredient 2', 'gemini-seo')),
            'recipeInstructions' => array(__('Step 1', 'gemini-seo'), __('Step 2', 'gemini-seo')),
            'mainEntityOfPage' => get_permalink($post)
        );
    }
        
        // Filter the schema array
        $schema = apply_filters('gemini_seo_schema', $schema);
        
        // Output the schema
        if (!empty($schema)) {
            echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
        }
    }
    
    private function get_website_schema() {
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => home_url('/#website'),
            'url' => home_url('/'),
            'name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'potentialAction' => array(
                array(
                    '@type' => 'SearchAction',
                    'target' => array(
                        '@type' => 'EntryPoint',
                        'urlTemplate' => home_url('/?s={search_term_string}')
                    ),
                    'query-input' => 'required name=search_term_string'
                )
            ),
            'inLanguage' => get_bloginfo('language')
        );
    }
    
    private function get_organization_schema() {
        $settings = get_option('gemini_seo_settings');
        
        $logo_id = isset($settings['organization_logo']) ? $settings['organization_logo'] : '';
        $logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
        
        $social_profiles = array();
        if (isset($settings['social_profiles'])) {
            foreach ($settings['social_profiles'] as $profile_url) {
                if (!empty($profile_url)) {
                    $social_profiles[] = $profile_url;
                }
            }
        }
        
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => home_url('/#organization'),
            'name' => isset($settings['organization_name']) ? $settings['organization_name'] : get_bloginfo('name'),
            'url' => home_url('/'),
            'logo' => array(
                '@type' => 'ImageObject',
                '@id' => home_url('/#logo'),
                'url' => $logo_url,
                'width' => 600,
                'height' => 60,
                'caption' => isset($settings['organization_name']) ? $settings['organization_name'] : get_bloginfo('name')
            ),
            'sameAs' => $social_profiles,
            'contactPoint' => array(
                array(
                    '@type' => 'ContactPoint',
                    'telephone' => isset($settings['organization_phone']) ? $settings['organization_phone'] : '',
                    'contactType' => 'customer service'
                )
            )
        );
    }
    
    private function get_article_schema($post) {
        $author = get_the_author_meta('display_name', $post->post_author);
        $published_time = get_the_date('c', $post->ID);
        $modified_time = get_the_modified_time('c', $post->ID);
        
        $image = '';
        if (has_post_thumbnail($post->ID)) {
            $image_data = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
            $image = $image_data[0];
        }
        
        $publisher = array(
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'logo' => array(
                '@type' => 'ImageObject',
                'url' => '' // Would get from settings
            )
        );
        
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            '@id' => get_permalink($post->ID) . '#article',
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id' => get_permalink($post->ID)
            ),
            'headline' => get_the_title($post->ID),
            'description' => wp_strip_all_tags(get_the_excerpt($post->ID)),
            'image' => array(
                '@type' => 'ImageObject',
                '@id' => get_permalink($post->ID) . '#primaryimage',
                'url' => $image,
                'width' => 1200,
                'height' => 800
            ),
            'datePublished' => $published_time,
            'dateModified' => $modified_time,
            'author' => array(
                '@type' => 'Person',
                '@id' => get_author_posts_url($post->post_author) . '#author',
                'name' => $author
            ),
            'publisher' => $publisher,
            'articleSection' => $this->get_post_categories($post->ID),
            'inLanguage' => get_bloginfo('language'),
            'potentialAction' => array(
                array(
                    '@type' => 'CommentAction',
                    'name' => 'Comment',
                    'target' => array(
                        get_permalink($post->ID) . '#respond'
                    )
                )
            )
        );
    }
    
    private function get_faq_schema($post) {
        $faq_content = get_post_meta($post->ID, '_gemini_seo_faq_content', true);
        
        if (empty($faq_content)) {
            return array();
        }
        
        $faq_items = array();
        
        foreach ($faq_content as $faq) {
            if (!empty($faq['question']) && !empty($faq['answer'])) {
                $faq_items[] = array(
                    '@type' => 'Question',
                    'name' => sanitize_text_field($faq['question']),
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text' => wp_kses_post($faq['answer'])
                    )
                );
            }
        }
        
        if (empty($faq_items)) {
            return array();
        }
        
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            '@id' => get_permalink($post->ID) . '#faqpage',
            'mainEntity' => $faq_items
        );
    }
    
    private function get_recipe_schema($post) {
        $recipe_data = get_post_meta($post->ID, '_gemini_seo_recipe_data', true);
        
        if (empty($recipe_data)) {
            return array();
        }
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Recipe',
            '@id' => get_permalink($post->ID) . '#recipe',
            'name' => get_the_title($post->ID),
            'description' => wp_strip_all_tags(get_the_excerpt($post->ID)),
            'author' => array(
                '@type' => 'Person',
                'name' => get_the_author_meta('display_name', $post->post_author)
            ),
            'datePublished' => get_the_date('c', $post->ID),
            'prepTime' => isset($recipe_data['prep_time']) ? $recipe_data['prep_time'] : '',
            'cookTime' => isset($recipe_data['cook_time']) ? $recipe_data['cook_time'] : '',
            'totalTime' => isset($recipe_data['total_time']) ? $recipe_data['total_time'] : '',
            'recipeYield' => isset($recipe_data['yield']) ? $recipe_data['yield'] : '',
            'recipeCategory' => isset($recipe_data['category']) ? $recipe_data['category'] : '',
            'recipeCuisine' => isset($recipe_data['cuisine']) ? $recipe_data['cuisine'] : '',
            'keywords' => isset($recipe_data['keywords']) ? $recipe_data['keywords'] : '',
            'nutrition' => array(
                '@type' => 'NutritionInformation',
                'calories' => isset($recipe_data['calories']) ? $recipe_data['calories'] : ''
            )
        );
        
        // Add image if available
        if (has_post_thumbnail($post->ID)) {
            $image_data = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
            $schema['image'] = array(
                '@type' => 'ImageObject',
                'url' => $image_data[0],
                'width' => $image_data[1],
                'height' => $image_data[2]
            );
        }
        
        // Add ingredients
        if (!empty($recipe_data['ingredients'])) {
            $schema['recipeIngredient'] = array_map('sanitize_text_field', $recipe_data['ingredients']);
        }
        
        // Add instructions
        if (!empty($recipe_data['instructions'])) {
            $instructions = array();
            $step_count = 1;
            
            foreach ($recipe_data['instructions'] as $instruction) {
                if (!empty($instruction)) {
                    $instructions[] = array(
                        '@type' => 'HowToStep',
                        'position' => $step_count,
                        'text' => sanitize_text_field($instruction)
                    );
                    $step_count++;
                }
            }
            
            if (!empty($instructions)) {
                $schema['recipeInstructions'] = $instructions;
            }
        }
        
        // Add aggregate rating if available
        if (!empty($recipe_data['rating_value']) && !empty($recipe_data['rating_count'])) {
            $schema['aggregateRating'] = array(
                '@type' => 'AggregateRating',
                'ratingValue' => $recipe_data['rating_value'],
                'ratingCount' => $recipe_data['rating_count']
            );
        }
        
        return $schema;
    }
    
    private function get_howto_schema($post) {
        $howto_data = get_post_meta($post->ID, '_gemini_seo_howto_data', true);
        
        if (empty($howto_data)) {
            return array();
        }
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            '@id' => get_permalink($post->ID) . '#howto',
            'name' => get_the_title($post->ID),
            'description' => wp_strip_all_tags(get_the_excerpt($post->ID)),
            'totalTime' => isset($howto_data['total_time']) ? $howto_data['total_time'] : ''
        );
        
        // Add image if available
        if (has_post_thumbnail($post->ID)) {
            $image_data = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
            $schema['image'] = array(
                '@type' => 'ImageObject',
                'url' => $image_data[0],
                'width' => $image_data[1],
                'height' => $image_data[2]
            );
        }
        
        // Add supplies
        if (!empty($howto_data['supplies'])) {
            $schema['supply'] = array_map('sanitize_text_field', $howto_data['supplies']);
        }
        
        // Add tools
        if (!empty($howto_data['tools'])) {
            $schema['tool'] = array_map('sanitize_text_field', $howto_data['tools']);
        }
        
        // Add steps
        if (!empty($howto_data['steps'])) {
            $steps = array();
            $step_count = 1;
            
            foreach ($howto_data['steps'] as $step) {
                if (!empty($step['description'])) {
                    $step_schema = array(
                        '@type' => 'HowToStep',
                        'position' => $step_count,
                        'text' => sanitize_text_field($step['description'])
                    );
                    
                    // Add image to step if available
                    if (!empty($step['image'])) {
                        $step_schema['image'] = array(
                            '@type' => 'ImageObject',
                            'url' => $step['image']
                        );
                    }
                    
                    $steps[] = $step_schema;
                    $step_count++;
                }
            }
            
            if (!empty($steps)) {
                $schema['step'] = $steps;
            }
        }
        
        // Add estimated cost
        if (!empty($howto_data['estimated_cost'])) {
            $schema['estimatedCost'] = array(
                '@type' => 'MonetaryAmount',
                'currency' => isset($howto_data['currency']) ? $howto_data['currency'] : 'USD',
                'value' => $howto_data['estimated_cost']
            );
        }
        
        return $schema;
    }
    
    private function get_author_schema() {
        $author_id = get_query_var('author');
        $author_name = get_the_author_meta('display_name', $author_id);
        $author_description = get_the_author_meta('description', $author_id);
        
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            '@id' => get_author_posts_url($author_id) . '#author',
            'name' => $author_name,
            'description' => $author_description,
            'url' => get_author_posts_url($author_id)
        );
    }
    
    private function get_post_categories($post_id) {
        $categories = get_the_category($post_id);
        $category_names = array();
        
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $category_names[] = $category->name;
            }
        }
        
        return $category_names;
    }
    
    public function filter_schema_output($schema, $context) {
        // Allow other plugins to modify the schema output
        return $schema;
    }
}