$(function() {
    $(".forum-sidebar .sticky").sticky({topSpacing:45});

    $('.new-thread').on('click', function(e) {
        e.preventDefault();

        $('.forum-container').toggleClass('new-thread');
        if ($('.forum-container').hasClass('new-thread'))
            $(this).html('<i class="icon-caret-left"></i> Thread list');
        else
            $(this).html('<i class="icon-chat"></i> New thread');
    });

    var thread_id = $('div.forum-main').attr('data-thread-id');

    $('.post-watch').on('click', function(e) {
        e.preventDefault();

        watching = $(this).hasClass('post-unwatch');

        $.get('/files/ajax/forum.php', {action: 'watch', thread_id: thread_id, watch: !watching}, function(data) {
            console.log(data);
        }, 'json');

        if (!watching) {
            $(this).addClass('post-unwatch').html('<i class="icon-eye-blocked"></i> Unwatch');
        } else {
            $(this).removeClass('post-unwatch').html('<i class="icon-eye"></i> Watch');
        }
    });


    $('ul.post-list li').each(function() {
        var self = this;

        if ($(self).find('.post_content .bbcode-youtube').length)
            setTimeout(resizePostInfo(self), 5);
        else
            resizePostInfo(self)();
    });

    function resizePostInfo(self) {
        return function() {
            var h = $(self).find('.post_content').innerHeight();
            if (h > $(self).find('.post_header').height()) {
                $(self).find('.post_header').innerHeight(h+1);
            }
        }
    }
});