$(function() {
	$('pre.bbcode_code_body').each(function(i, e) {hljs.highlightBlock(e)});

	$('body').on('click', '.bbcode_spoiler_head', function(e) {
		e.preventDefault();

		$(this).toggleClass('active');
	});



    // Video resize
    // Find all YouTube videos
    var $allVideos = $(".bbcode-youtube, .bbcode-vimeo");
    function resizeVideos() {
        $allVideos.each(function() {
            var $el = $(this);
            $el.removeAttr('height').height($el.width()*0.56);
        });
    }

    $(window).resize(resizeVideos).resize();
});