(function($){
    $(document).ready(function(){
        // Metabox tab switching
        $('.gemini-metabox-tabs a').on('click', function(e){
            e.preventDefault();
            var tab = $(this).attr('href');
            $('.gemini-metabox-tabs a').removeClass('active');
            $(this).addClass('active');
            $('.gemini-metabox-tab-content').hide();
            $(tab).show();
        });

        // Field validation (simple required fields)
        $('.gemini-metabox-form').on('submit', function(e){
            var valid = true;
            $(this).find('[required]').each(function(){
                if(!$(this).val()){
                    $(this).addClass('gemini-field-error');
                    valid = false;
                } else {
                    $(this).removeClass('gemini-field-error');
                }
            });
            if(!valid){
                e.preventDefault();
                alert('Please fill all required fields.');
            }
        });

        // Media uploader support
        $('.gemini-metabox-media-upload').on('click', function(e){
            e.preventDefault();
            var button = $(this);
            var custom_uploader = wp.media({
                title: 'Select Image',
                button: { text: 'Use this image' },
                multiple: false
            })
            .on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                button.prev('.gemini-metabox-media-url').val(attachment.url);
                button.prev('.gemini-metabox-media-preview').attr('src', attachment.url).show();
            })
            .open();
        });

        // Slug validation for SEO
        var $slug = $('#gemini_seo_url_slug');
        var $focus = $('#gemini_seo_focus_keyword');
        var $feedback = $('.gemini-seo-slug-feedback');
        function validateSlug() {
            var slug = $slug.val();
            var keyword = $focus.val();
            var min = 3, max = 60;
            var lengthOk = slug.length >= min && slug.length <= max;
            var hasKeyword = keyword && slug.toLowerCase().indexOf(keyword.toLowerCase()) !== -1;
            var msg = '';
            if (!lengthOk) msg += ' ' + geminiMetabox.slugLengthMsg;
            if (!hasKeyword) msg += ' ' + geminiMetabox.slugKeywordMsg;
            $feedback.text(msg.trim()).toggleClass('gemini-field-error', !lengthOk || !hasKeyword);
        }
        $focus.on('input', validateSlug);
        $slug.on('input', validateSlug);
        validateSlug();

        // Meta description validation for SEO
        var $metaDesc = $('#gemini_seo_meta_description');
        var $metaDescCounter = $('.gemini-seo-counter[data-target="gemini_seo_meta_description"]');
        var $metaDescFeedback = $('<div class="gemini-seo-meta-desc-feedback"></div>').insertAfter($metaDescCounter);
        function validateMetaDesc() {
            var desc = $metaDesc.val();
            var keyword = $focus.val();
            var min = 150, max = 160;
            var len = desc.length;
            var lengthOk = len >= min && len <= max;
            var hasKeyword = keyword && desc.toLowerCase().indexOf(keyword.toLowerCase()) !== -1;
            var msg = '';
            if (!lengthOk) msg += ' ' + geminiMetabox.metaDescLengthMsg;
            if (!hasKeyword) msg += ' ' + geminiMetabox.metaDescKeywordMsg;
            $metaDescFeedback.text(msg.trim()).toggleClass('gemini-field-error', !lengthOk || !hasKeyword);
            $metaDescCounter.text(len + '/160');
        }
        $metaDesc.on('input', validateMetaDesc);
        $focus.on('input', validateMetaDesc);
        validateMetaDesc();
    });
})(jQuery);