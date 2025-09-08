<div class="gemini-seo-metabox">
    <div class="gemini-seo-tabs">
        <ul>
            <li class="active"><a href="#gemini-seo-general"><?php _e('General', 'gemini-seo'); ?></a></li>
            <li><a href="#gemini-seo-social"><?php _e('Social', 'gemini-seo'); ?></a></li>
            <li><a href="#gemini-seo-advanced"><?php _e('Advanced', 'gemini-seo'); ?></a></li>
            <li><a href="#gemini-seo-schema"><?php _e('Schema', 'gemini-seo'); ?></a></li>
        </ul>
    </div>
    
    <div class="gemini-seo-tab-content">
        <div id="gemini-seo-general" class="gemini-seo-tab-pane active">
            <div class="gemini-seo-field">
                <label for="gemini_seo_focus_keyword"><?php _e('Focus Keyword', 'gemini-seo'); ?></label>
                <input type="text" id="gemini_seo_focus_keyword" name="gemini_seo_focus_keyword" value="<?php echo esc_attr($focus_keyword); ?>">
                <p class="description"><?php _e('Enter the main keyword for this content', 'gemini-seo'); ?></p>
            </div>
            
            <div class="gemini-seo-field">
                <label for="gemini_seo_meta_title"><?php _e('Meta Title', 'gemini-seo'); ?></label>
                <input type="text" id="gemini_seo_meta_title" name="gemini_seo_meta_title" value="<?php echo esc_attr($meta_title); ?>">
                <div class="gemini-seo-counter" data-target="gemini_seo_meta_title" data-max="60">0/60</div>
                <p class="description"><?php _e('Title display in search engines (50-60 chars)', 'gemini-seo'); ?></p>
            </div>
            
            <div class="gemini-seo-field">
                <label for="gemini_seo_meta_description"><?php _e('Meta Description', 'gemini-seo'); ?></label>
                <textarea id="gemini_seo_meta_description" name="gemini_seo_meta_description"><?php echo esc_textarea($meta_description); ?></textarea>
                <div class="gemini-seo-counter" data-target="gemini_seo_meta_description" data-max="160">0/160</div>
                <p class="description"><?php _e('Description display in search engines (150-160 chars)', 'gemini-seo'); ?></p>
            </div>
            
            <div class="gemini-seo-preview">
                <h3><?php _e('Search Engine Preview', 'gemini-seo'); ?></h3>
                <div class="gemini-seo-preview-content">
                    <div class="gemini-seo-preview-title"><?php echo esc_html($meta_title ? $meta_title : get_the_title($post->ID)); ?></div>
                    <div class="gemini-seo-preview-url"><?php echo esc_url(get_permalink($post->ID)); ?></div>
                    <div class="gemini-seo-preview-description"><?php echo esc_html($meta_description ? $meta_description : wp_trim_words(get_the_excerpt($post->ID), 30)); ?></div>
                </div>
            </div>

            <div id="gemini-seo-analysis-results" class="gemini-seo-analysis-results"></div>
            
            <div class="gemini-seo-analysis">
                <h3><?php _e('Content Analysis', 'gemini-seo'); ?></h3>
                <div class="gemini-seo-analysis-results">
                    <div class="gemini-seo-analysis-loading"><?php _e('Analyzing content...', 'gemini-seo'); ?></div>
                </div>
            </div>

            <div class="gemini-seo-field">
                <label for="gemini_seo_url_slug"><?php _e('URL Slug', 'gemini-seo'); ?></label>
                <input type="text" id="gemini_seo_url_slug" name="gemini_seo_url_slug" value="<?php echo esc_attr($url_slug); ?>" readonly>
                <div class="gemini-seo-slug-feedback"></div>
                <p class="description"><?php _e('The URL slug for this post. Must be short and include the focus keyword.', 'gemini-seo'); ?></p>
            </div>
            <div class="gemini-seo-field">
                <label for="gemini_seo_related_keywords"><?php _e('Related Keywords', 'gemini-seo'); ?></label>
                <input type="text" id="gemini_seo_related_keywords" name="gemini_seo_related_keywords" value="<?php echo esc_attr($related_keywords); ?>">
                <p class="description"><?php _e('Comma-separated list of related keywords.', 'gemini-seo'); ?></p>
            </div>
            <div class="gemini-seo-field">
                <label for="gemini_seo_synonyms"><?php _e('Focus Keyphrase Synonyms', 'gemini-seo'); ?></label>
                <input type="text" id="gemini_seo_synonyms" name="gemini_seo_synonyms" value="<?php echo esc_attr($synonyms); ?>">
                <p class="description"><?php _e('Comma-separated synonyms for the focus keyphrase.', 'gemini-seo'); ?></p>
            </div>
        </div>
        
        <div id="gemini-seo-social" class="gemini-seo-tab-pane">
            <h3><?php _e('Facebook (Open Graph)', 'gemini-seo'); ?></h3>
            <div class="gemini-seo-field">
                <label for="gemini_seo_og_title"><?php _e('OG Title', 'gemini-seo'); ?></label>
                <input type="text" id="gemini_seo_og_title" name="gemini_seo_og_title" value="<?php echo esc_attr($opengraph_title); ?>">
                <p class="description"><?php _e('Custom title for social sharing (defaults to Meta Title)', 'gemini-seo'); ?></p>
            </div>
            
            <div class="gemini-seo-field">
                <label for="gemini_seo_og_description"><?php _e('OG Description', 'gemini-seo'); ?></label>
                <textarea id="gemini_seo_og_description" name="gemini_seo_og_description"><?php echo esc_textarea($opengraph_description); ?></textarea>
                <p class="description"><?php _e('Custom description for social sharing (defaults to Meta Description)', 'gemini-seo'); ?></p>
            </div>
            
            <div class="gemini-seo-field">
                <label for="gemini_seo_og_image"><?php _e('OG Image', 'gemini-seo'); ?></label>
                <input type="text" id="gemini_seo_og_image" name="gemini_seo_og_image" value="<?php echo esc_attr($opengraph_image); ?>">
                <button type="button" class="button gemini-seo-media-upload" data-target="gemini_seo_og_image"><?php _e('Select Image', 'gemini-seo'); ?></button>
                <p class="description"><?php _e('Custom image for social sharing (recommended: 1200×630px)', 'gemini-seo'); ?></p>
            </div>
            
            <h3><?php _e('Twitter Card', 'gemini-seo'); ?></h3>
            <div class="gemini-seo-field">
                <label for="gemini_seo_twitter_title"><?php _e('Twitter Title', 'gemini-seo'); ?></label>
                <input type="text" id="gemini_seo_twitter_title" name="gemini_seo_twitter_title" value="<?php echo esc_attr($twitter_title); ?>">
                <p class="description"><?php _e('Custom title for Twitter (defaults to OG Title)', 'gemini-seo'); ?></p>
            </div>
            
            <div class="gemini-seo-field">
                <label for="gemini_seo_twitter_description"><?php _e('Twitter Description', 'gemini-seo'); ?></label>
                <textarea id="gemini_seo_twitter_description" name="gemini_seo_twitter_description"><?php echo esc_textarea($twitter_description); ?></textarea>
                <p class="description"><?php _e('Custom description for Twitter (defaults to OG Description)', 'gemini-seo'); ?></p>
            </div>

            <div class="gemini-seo-field">
                <label for="gemini_seo_twitter_image"><?php _e('Twitter Image', 'gemini-seo'); ?></label>
                <input type="text" id="gemini_seo_twitter_image" name="gemini_seo_twitter_image" value="<?php echo esc_attr($twitter_image); ?>">
                <button type="button" class="button gemini-seo-media-upload" data-target="gemini_seo_twitter_image"><?php _e('Select Image', 'gemini-seo'); ?></button>
                <p class="description"><?php _e('Custom image for Twitter (recommended: 1200x630px)', 'gemini-seo'); ?></p>
            </div>
        </div>
        
        <div id="gemini-seo-advanced" class="gemini-seo-tab-pane">
            <div class="gemini-seo-field">
                <label for="gemini_seo_canonical_url"><?php _e('Canonical URL', 'gemini-seo'); ?></label>
                <input type="text" id="gemini_seo_canonical_url" name="gemini_seo_canonical_url" value="<?php echo esc_attr($canonical_url); ?>">
                <p class="description"><?php _e('Advanced: specify a canonical URL to avoid duplicate content', 'gemini-seo'); ?></p>
            </div>
            
            <div class="gemini-seo-field">
                <label for="gemini_seo_meta_robots"><?php _e('Meta Robots', 'gemini-seo'); ?></label>
                <select id="gemini_seo_meta_robots" name="gemini_seo_meta_robots">
                    <option value=""><?php _e('Default', 'gemini-seo'); ?></option>
                    <option value="index, follow" <?php selected($meta_robots, 'index, follow'); ?>><?php _e('Index, Follow', 'gemini-seo'); ?></option>
                    <option value="index, nofollow" <?php selected($meta_robots, 'index, nofollow'); ?>><?php _e('Index, Nofollow', 'gemini-seo'); ?></option>
                    <option value="noindex, follow" <?php selected($meta_robots, 'noindex, follow'); ?>><?php _e('Noindex, Follow', 'gemini-seo'); ?></option>
                    <option value="noindex, nofollow" <?php selected($meta_robots, 'noindex, nofollow'); ?>><?php _e('Noindex, Nofollow', 'gemini-seo'); ?></option>
                </select>
                <p class="description"><?php _e('Advanced: control how search engines index this content', 'gemini-seo'); ?></p>
            </div>
        </div>
        
        <div id="gemini-seo-schema" class="gemini-seo-tab-pane">
            <div class="gemini-seo-field">
                <label for="gemini_seo_schema_type"><?php _e('Schema Type', 'gemini-seo'); ?></label>
                <select id="gemini_seo_schema_type" name="gemini_seo_schema_type">
                    <option value=""><?php _e('Default (Article)', 'gemini-seo'); ?></option>
                    <option value="Article" <?php selected($schema_type, 'Article'); ?>><?php _e('Article', 'gemini-seo'); ?></option>
                    <option value="NewsArticle" <?php selected($schema_type, 'NewsArticle'); ?>><?php _e('News Article', 'gemini-seo'); ?></option>
                    <option value="BlogPosting" <?php selected($schema_type, 'BlogPosting'); ?>><?php _e('Blog Posting', 'gemini-seo'); ?></option>
                    <option value="Recipe" <?php selected($schema_type, 'Recipe'); ?>><?php _e('Recipe', 'gemini-seo'); ?></option>
                    <option value="FAQPage" <?php selected($schema_type, 'FAQPage'); ?>><?php _e('FAQ Page', 'gemini-seo'); ?></option>
                    <option value="HowTo" <?php selected($schema_type, 'HowTo'); ?>><?php _e('HowTo', 'gemini-seo'); ?></option>
                </select>
                <p class="description"><?php _e('Select structured data type for this content', 'gemini-seo'); ?></p>
            </div>
            
            <!-- Schema markup preview would go here -->
        </div>
    </div>
</div>
