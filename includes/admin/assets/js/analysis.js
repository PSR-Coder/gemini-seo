// Gemini SEO Real-time Analysis
jQuery(document).ready(function($) {
	function runAnalysis() {
		var keyword = $('#gemini_seo_focus_keyword').val();
		var post_id = $('#post_ID').val();
		if (!keyword || !post_id) return;

		$('#gemini-seo-analysis-results').html('<em>Analyzing...</em>');

		$.post(gemini_seo_ajax.ajax_url, {
			action: 'gemini_seo_analyze',
			nonce: gemini_seo_ajax.nonce,
			post_id: post_id,
			keyword: keyword
		}, function(response) {
			if (response.success) {
				displayResults(response.data);
			} else {
				$('#gemini-seo-analysis-results').html('<span class="error">Analysis failed.</span>');
			}
		});
	}

	function displayResults(data) {
		var html = '<h4>SEO Analysis</h4><ul>';
		html += '<li><strong>Keyword Density:</strong> ' + data.keyword_density.density + '% (' + data.keyword_density.count + ' of ' + data.keyword_density.total_words + ' words) <span class="status-' + data.keyword_density.status + '">' + data.keyword_density.status + '</span></li>';
		html += '<li><strong>Title:</strong> ' + data.title_analysis.length + ' chars, keyword: ' + (data.title_analysis.has_keyword ? 'yes' : 'no') + ' <span class="status-' + data.title_analysis.status + '">' + data.title_analysis.status + '</span></li>';
		html += '<li><strong>Meta Description:</strong> ' + data.meta_description_analysis.length + ' chars, keyword: ' + (data.meta_description_analysis.has_keyword ? 'yes' : 'no') + ' <span class="status-' + data.meta_description_analysis.status + '">' + data.meta_description_analysis.status + '</span></li>';
		html += '<li><strong>Content Length:</strong> ' + data.content_length.length + ' chars <span class="status-' + data.content_length.status + '">' + data.content_length.status + '</span></li>';
		html += '<li><strong>Headings:</strong> ' + data.heading_analysis.total + ' total, keyword in ' + data.heading_analysis.keyword_in_headings + '</li>';
		html += '<li><strong>Images:</strong> ' + data.image_analysis.total + ' images, ' + data.image_analysis.missing_alt + ' missing alt</li>';
		html += '<li><strong>Links:</strong> ' + data.link_analysis.total + ' total (' + data.link_analysis.internal + ' internal, ' + data.link_analysis.external + ' external)</li>';
		html += '<li><strong>Readability:</strong> ' + data.readability.score + ' <span class="status-' + data.readability.status + '">' + data.readability.status + '</span></li>';
		html += '</ul>';
		$('#gemini-seo-analysis-results').html(html);
	}

	// Trigger analysis on keyword/content/meta changes
	$('#gemini_seo_focus_keyword, #content, #gemini_seo_meta_title, #gemini_seo_meta_description').on('input change', function() {
		runAnalysis();
	});

	// Initial run
	setTimeout(runAnalysis, 1000);
});

// --- Gemini SEO Enhanced Content Analysis ---
(function($){
    function icon(status) {
        if (status === 'good') return '<span class="gemini-seo-icon status-good" title="Good">'+svgIcon('check')+'</span>';
        if (status === 'bad') return '<span class="gemini-seo-icon status-bad" title="Needs improvement">'+svgIcon('warn')+'</span>';
        return '';
    }
    function svgIcon(type) {
        if (type === 'check') return '<svg width="18" height="18" viewBox="0 0 20 20" fill="#34c759"><path d="M7.629 15.314a1 1 0 0 1-1.414 0l-4.243-4.243a1 1 0 1 1 1.414-1.414l3.536 3.535 7.778-7.778a1 1 0 1 1 1.414 1.415l-8.485 8.485z"/></svg>';
        if (type === 'warn') return '<svg width="18" height="18" viewBox="0 0 20 20" fill="#e74c3c"><circle cx="10" cy="10" r="9" stroke="#e74c3c" stroke-width="2" fill="none"/><rect x="9" y="5" width="2" height="7" fill="#e74c3c"/><rect x="9" y="13.5" width="2" height="2" fill="#e74c3c"/></svg>';
        return '';
    }
    function tooltip(text) {
        return '<span class="gemini-seo-tooltip" title="' + text.replace(/"/g, '&quot;') + '">🛈</span>';
    }
    function progressBar(percent) {
        return '<div class="gemini-seo-progress-bar"><div class="bar" style="width:' + percent + '%"></div></div>';
    }
    function countWords(text) {
        return text.trim().split(/\s+/).filter(Boolean).length;
    }
    function getSections(content) {
        var sections = [];
        var regex = /<h([2-4])[^>]*>(.*?)<\/h\1>/gi;
        var lastIndex = 0, match;
        var matches = [];
        while ((match = regex.exec(content)) !== null) {
            matches.push({index: match.index, tag: match[1], title: match[2]});
        }
        for (var i = 0; i < matches.length; i++) {
            var start = matches[i].index;
            var end = (i + 1 < matches.length) ? matches[i+1].index : content.length;
            var sectionHtml = content.substring(start, end);
            var sectionText = $('<div>').html(sectionHtml).text();
            sections.push({heading: matches[i].title, tag: matches[i].tag, text: sectionText, wordCount: countWords(sectionText)});
        }
        return sections;
    }
    function getSentences(text) {
        return text.match(/[^.!?]+[.!?]+/g) || [];
    }
    function countLongSentences(sentences, limit) {
        return sentences.filter(function(s){ return countWords(s) > limit; }).length;
    }
    function detectPassiveVoice(text) {
        // Simple passive voice detection (heuristic)
        var passiveRegex = /\b(is|are|was|were|be|been|being|am|has been|have been|had been|will be|shall be|should be|would be|can be|could be|may be|might be|must be)\b\s+\w+ed\b/gi;
        var matches = text.match(passiveRegex) || [];
        return matches.length;
    }
    function analyzeContent() {
        var content = tinymce.activeEditor ? tinymce.activeEditor.getContent({format:'html'}) : $('#content').val();
        var plainText = $('<div>').html(content).text();
        var wordCount = countWords(plainText);
        var sentences = getSentences(plainText);
        var longSentences = countLongSentences(sentences, 30);
        var passiveCount = detectPassiveVoice(plainText);
        var passivePercent = wordCount ? (passiveCount / wordCount * 100) : 0;
        var sections = getSections(content);
        var sectionWarnings = sections.filter(function(s){ return s.wordCount > 300; });
        // Progress calculation (out of 4 checks)
        var checks = [wordCount >= 300, sectionWarnings.length === 0, longSentences === 0, passivePercent <= 5];
        var passed = checks.filter(Boolean).length;
        var percent = Math.round((passed / checks.length) * 100);
        var html = '<div class="gemini-seo-dashboard collapsible-panel">';
        html += '<div class="collapsible-header"><h4>SEO Content Checks ' + tooltip('These checks help you optimize your content for search engines.') + '</h4><span class="collapsible-toggle">▼</span></div>';
        html += '<div class="collapsible-body">';
        html += progressBar(percent);
        html += '<ul>';
        html += '<li class="' + (wordCount >= 300 ? 'status-good' : 'status-bad') + '"><strong>Total Words:</strong> ' + wordCount + ' ' + icon(wordCount >= 300 ? 'good' : 'bad') + tooltip('Aim for at least 300 words for SEO.') + '</li>';
        html += '<li class="' + (sectionWarnings.length === 0 ? 'status-good' : 'status-bad') + '"><strong>Sections &gt; 300 words (H2-H4):</strong> ' + (sectionWarnings.length ? sectionWarnings.map(function(s){return s.heading + ' ('+s.wordCount+' words)';}).join(', ') : 'None') + ' ' + icon(sectionWarnings.length === 0 ? 'good' : 'bad') + tooltip('Break up long sections for better readability.') + '</li>';
        html += '<li class="' + (longSentences === 0 ? 'status-good' : 'status-bad') + '"><strong>Long Sentences (&gt;30 words):</strong> ' + longSentences + (longSentences > 0 ? ' <span class="status-bad">Shorten long sentences for better readability.</span>' : ' <span class="status-good">Good</span>') + ' ' + icon(longSentences === 0 ? 'good' : 'bad') + tooltip('Keep sentences under 30 words.') + '</li>';
        html += '<li class="' + (passivePercent <= 5 ? 'status-good' : 'status-bad') + '"><strong>Passive Voice Usage:</strong> ' + passiveCount + ' (' + passivePercent.toFixed(1) + '%)' + (passivePercent > 5 ? ' <span class="status-bad">Reduce passive voice below 5%.</span>' : ' <span class="status-good">Good</span>') + ' ' + icon(passivePercent <= 5 ? 'good' : 'bad') + tooltip('Aim for less than 5% passive voice.') + '</li>';
        html += '</ul></div></div>';
        $('#gemini-seo-analysis-results .gemini-seo-dashboard').remove();
        $('#gemini-seo-analysis-results').prepend(html);
        // Collapsible panel logic with animation
        $('.collapsible-header').off('click').on('click', function(){
            $(this).next('.collapsible-body').slideToggle(250);
            $(this).find('.collapsible-toggle').toggleClass('open');
        });
        $('.collapsible-body').hide();
    }
    $(document).ready(function(){
        $('#content, #gemini_seo_focus_keyword').on('input change', function(){
            setTimeout(analyzeContent, 500);
        });
        analyzeContent();
    });
})(jQuery);
