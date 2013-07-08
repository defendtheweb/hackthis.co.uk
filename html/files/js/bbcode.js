$(function() {
	$('pre.bbcode_code_body').each(function(i, e) {hljs.highlightBlock(e)});

	$('body').on('click', '.bbcode_spoiler_head', function(e) {
		e.preventDefault();

		$(this).toggleClass('active');
	});
});