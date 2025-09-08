(function($){
    $(document).ready(function(){
        // Dynamic breadcrumbs: highlight current page
        var $breadcrumbs = $('.gemini-breadcrumbs');
        if ($breadcrumbs.length) {
            $breadcrumbs.find('li').last().addClass('gemini-breadcrumb-current');
        }

        // Example: Smooth scroll for anchor links
        $('a[href^="#"]').on('click', function(e) {
            var target = $($(this).attr('href'));
            if(target.length) {
                e.preventDefault();
                $('html, body').animate({ scrollTop: target.offset().top }, 500);
            }
        });

        // Example: Toggle for FAQ schema sections
        $('.gemini-faq-question').on('click', function(){
            $(this).next('.gemini-faq-answer').slideToggle();
            $(this).toggleClass('open');
        });
    });
})(jQuery);
