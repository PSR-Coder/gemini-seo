jQuery(document).ready(function($) {
    // Test redirect functionality
    $('#test-redirect').on('click', function() {
        var source = $('#source').val();
        var destination = $('#destination').val();
        var regex = $('input[name="regex"]').is(':checked');
        
        if (!source || !destination) {
            $('#test-result').html('<p class="error">Please enter both source and destination.</p>').addClass('error').show();
            return;
        }
        
        $.post(gemini_seo_redirects.ajax_url, {
            action: 'gemini_seo_test_redirect',
            nonce: gemini_seo_redirects.nonce,
            source: source,
            destination: destination,
            regex: regex
        }, function(response) {
            if (response.success) {
                $('#test-result').html('<p class="success">' + response.data.message + '</p><p>' + response.data.example + '</p>').removeClass('error').addClass('success').show();
            } else {
                $('#test-result').html('<p class="error">' + response.data + '</p>').removeClass('success').addClass('error').show();
            }
        }).fail(function() {
            $('#test-result').html('<p class="error">An error occurred while testing the redirect.</p>').removeClass('success').addClass('error').show();
        });
    });
    
    // Show/hide test button based on regex checkbox
    $('input[name="regex"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('#test-redirect-row').show();
        } else {
            $('#test-redirect-row').hide();
            $('#test-result').hide();
        }
    }).trigger('change');
    
    // Delete redirect confirmation
    $('.gemini-seo-delete-redirect, .gemini-seo-delete-404').on('click', function(e) {
        if (!confirm(gemini_seo_redirects.confirm_delete)) {
            e.preventDefault();
        }
    });
    
    // Create redirect from 404 modal
    $('.gemini-seo-create-redirect').on('click', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        $('#redirect-source-url').val(url);
        $('#gemini-seo-create-redirect-modal').show();
    });
    
    // Close modal
    $('.gemini-seo-close-modal').on('click', function() {
        $('#gemini-seo-create-redirect-modal').hide();
    });
    
    // Live regex validation
    (function($){
        $(document).ready(function(){
            var $form = $('.gemini-redirects-form');
            var $from = $form.find('input[name="redirect_from"]');
            var $to = $form.find('input[name="redirect_to"]');
            var $isRegex = $form.find('input[name="redirect_is_regex"]');
            var $feedback = $('<div class="gemini-redirects-regex-feedback"></div>').insertAfter($from);
            var $loopFeedback = $('<div class="gemini-redirects-loop-feedback"></div>').insertAfter($to);
            // Optional: window.geminiRedirects = [{from: '/a', to: '/b'}, ...]
            function normalize(url) {
                return url.replace(/\/$/, '').toLowerCase();
            }
            function detectLoop(from, to, redirects) {
                var visited = [normalize(from)];
                var current = normalize(to);
                var maxDepth = 10, depth = 0;
                while (current && depth < maxDepth) {
                    var found = false;
                    for (var i = 0; i < redirects.length; i++) {
                        if (normalize(redirects[i].from) === current) {
                            if (visited.indexOf(normalize(redirects[i].to)) !== -1) {
                                return true; // Loop detected
                            }
                            visited.push(normalize(redirects[i].to));
                            current = normalize(redirects[i].to);
                            found = true;
                            break;
                        }
                    }
                    if (!found) break;
                    depth++;
                }
                return false;
            }
            function validateRegex() {
                var val = $from.val();
                var isRegex = $isRegex.is(':checked');
                if (!isRegex) {
                    $feedback.text('').removeClass('error success');
                    return true;
                }
                // Basic delimiter and syntax check
                var valid = /^([\/#~|@%]).+\1[imsxeADSUXJu]*$/.test(val);
                if (!valid) {
                    $feedback.text('Invalid regex: must use proper delimiters and syntax.').addClass('error').removeClass('success');
                    return false;
                }
                try {
                    new RegExp(val.replace(/^([\/#~|@%])|\1[imsxeADSUXJu]*$/g, ''));
                    $feedback.text('Regex looks valid.').addClass('success').removeClass('error');
                    return true;
                } catch(e) {
                    $feedback.text('Invalid regex: ' + e.message).addClass('error').removeClass('success');
                    return false;
                }
            }
            function validateLoop() {
                var from = $from.val().replace(/\/$/, '');
                var to = $to.val().replace(/\/$/, '');
                if (from && to && from === to) {
                    $loopFeedback.text('Error: Source and target URLs are the same (self-redirect).').addClass('error').removeClass('success');
                    $to.addClass('gemini-field-error');
                    return false;
                }
                // Advanced: check for loop using window.geminiRedirects
                if (window.geminiRedirects && Array.isArray(window.geminiRedirects)) {
                    if (detectLoop(from, to, window.geminiRedirects)) {
                        $loopFeedback.text('Error: This redirect would cause a loop with existing redirects.').addClass('error').removeClass('success');
                        $to.addClass('gemini-field-error');
                        return false;
                    }
                }
                $loopFeedback.text('').removeClass('error success');
                $to.removeClass('gemini-field-error');
                return true;
            }
            $from.on('input', function(){ validateRegex(); validateLoop(); });
            $to.on('input', validateLoop);
            $isRegex.on('change', validateRegex);
            $form.on('submit', function(e){
                var validRegex = validateRegex();
                var validLoop = validateLoop();
                if (!validRegex || !validLoop) {
                    e.preventDefault();
                    if (!validLoop) $to.focus();
                    else $from.focus();
                }
            });
        });
    })(jQuery);
});