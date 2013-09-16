$(function() {
    $('a.level-hint').on('click', function(e) {
        e.preventDefault();

        if ($('.info-hint').length) {
            $('.info-hint').fadeToggle();
        } else {
            $.getJSON('?get-hint', function(data) {
                if (data.status) {
                    $hint = $('<div/>', {html: data.hint, class: 'info info-hint'});
                    $('.level-header').parent().append($hint);

                    var $allVideos = $(".bbcode-youtube, .bbcode-vimeo");
                    $allVideos.each(function() {
                        var $el = $(this);
                        $el.removeAttr('height').height($el.width()*0.56);
                    });

                    $hint.hide().fadeIn();
                }
            });
        }
    });
});