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


    $('a.remove').click(function(e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        var $elem = $(this).closest('li');

        $.confirm({
            title   : 'Delete post',
            message : 'Are you sure you want to delete this message? <br />It cannot be restored at a later time! Continue?',
            buttons : {
                Yes : {
                    action: function(){
                        // Remove item from feed
                        var uri = '/files/ajax/forum.php?action=post.remove&id=' + id;
                        $.getJSON(uri, function(data) {
                            if (data.status) {
                               $elem.slideUp();
                               numbers =$($('.forum-pagination')[0]).clone().children().remove().end().text().match(/[0-9]+/g);
                               console.log(numbers);
                               $('.forum-pagination').text('Viewing '+(numbers[0]-1)+' replies - '+numbers[1]+' through '+(numbers[2]-1)+' (of '+(numbers[3]-1)+' total)')
                            }
                        });
                    }
                },
                No  : {}
            }
        });
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
            var h = $(self).find('.post_content').height();
            if (h > $(self).find('.post_header').height()) {
                $(self).find('.post_header').height(h-4);
            }
        }
    }
});