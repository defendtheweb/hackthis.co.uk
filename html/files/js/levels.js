$(function() {
    $('.level-hint a').on('click', function(e) {
        e.preventDefault();
        $(this).off('click');

        $.getJSON('?get-hint', function(data) {
            if (data.status) {
                $hint = $('<div/>', {html: data.hint, class: 'info'});
                $('.level-header').parent().append($hint);

                var $allVideos = $(".bbcode-youtube, .bbcode-vimeo");
                $allVideos.each(function() {
                    var $el = $(this);
                    $el.removeAttr('height').height($el.width()*0.56);
                });
            }
        });
    });
});