(function($){
    $(document).ready(function(){
        // Tab switching
        $('.gemini-admin-tabs a').on('click', function(e){
            e.preventDefault();
            var tab = $(this).attr('href');
            $('.gemini-admin-tabs a').removeClass('active');
            $(this).addClass('active');
            $('.gemini-admin-tab-content').hide();
            $(tab).show();
        });

        // Field validation (simple required fields)
        $('.gemini-admin-form').on('submit', function(e){
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
        $('.gemini-media-upload').on('click', function(e){
            e.preventDefault();
            var button = $(this);
            var custom_uploader = wp.media({
                title: 'Select Image',
                button: { text: 'Use this image' },
                multiple: false
            })
            .on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                button.prev('.gemini-media-url').val(attachment.url);
                button.prev('.gemini-media-preview').attr('src', attachment.url).show();
            })
            .open();
        });
    });
})(jQuery);
