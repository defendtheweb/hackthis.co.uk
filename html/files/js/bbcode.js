$(function() {
	$('body').on('click', '.bbcode_spoiler_head', function(e) {
		e.preventDefault();

		$(this).toggleClass('active');
	});
});