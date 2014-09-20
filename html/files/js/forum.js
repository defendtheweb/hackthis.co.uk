$(function() {
    $(".forum-sidebar .sticky").css('width', $(".forum-sidebar .sticky").width()-2);
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
            // console.log(data);
        }, 'json');

        if (!watching) {
            $(this).addClass('post-unwatch').html('<i class="icon-eye-blocked"></i> Unwatch');
        } else {
            $(this).removeClass('post-unwatch').html('<i class="icon-eye"></i> Watch');
        }
    });

    // Admin menu
    $('.post-admin-container > a.button').on('click', function(e) {
        e.preventDefault();

        $(this).parent().toggleClass('open');
    });


    $('a.remove').click(function(e) {
        e.preventDefault();
        var $elem = $(this).closest('li');
        var id = $elem.attr('data-id');

        $.confirm({
            title   : 'Delete post',
            message : 'Are you sure you want to delete this message? <br />It cannot be restored at a later time! Continue?',
            buttons : {
                Cancel  : {
                    class: 'cancel'
                },
                Confirm : {
                    action: function(){
                        // Remove item from feed
                        var uri = '/files/ajax/forum.php?action=post.remove&id=' + id;
                        $.getJSON(uri, function(data) {
                            if (data.status) {
                               $elem.slideUp();
                               numbers =$($('.forum-pagination')[0]).clone().children().remove().end().text().match(/[0-9]+/g);
                               // console.log(numbers);
                               $('.forum-pagination').text('Viewing '+(numbers[0]-1)+' replies - '+numbers[1]+' through '+(numbers[2]-1)+' (of '+(numbers[3]-1)+' total)')
                            }
                        });
                    }
                }
            }
        });
    });

    var flagTemplate = '<form>\
                            <ul class="reasons plain">\
                                <li>\
                                    <input type="radio" name="reason" id="reason1" value="1"/>\
                                    <h3><label for="reason1">It is off-topic</label></h3>\
                                    This post is not useful or relevant to the current thread, or the thread itself is not appropriate to this section.\
                                </li>\
                                <li>\
                                    <input type="radio" name="reason" id="reason2" value="2"/>\
                                    <h3><label for="reason2">It is a spoiler</label></h3>\
                                    This post contains answers or more detailed information than is necessary.\
                                </li>\
                                <li>\
                                    <input type="radio" name="reason" id="reason3" value="3"/>\
                                    <h3><label for="reason3">It is spam</label></h3>\
                                    This post is effectively an advertisement with no disclosure. It is not useful or relevant, but promotional.\
                                </li>\
                                <li>\
                                    <input type="radio" name="reason" id="reason4" value="4"/>\
                                    <h3><label for="reason4">It is very low quality</label></h3>\
                                    This post has severe formatting or content problems. The post is unlikely to be salvageable through editing.\
                                </li>\
                                <li>\
                                    <input type="radio" name="reason" id="reason5" value="5"/>\
                                    <h3><label for="reason5">It is not English</label></h3>\
                                    The HackThis!! community’s first language is English.\
                                </li>\
                                <li>\
                                    <input type="radio" name="reason" id="reason6" value="6"/>\
                                    <h3><label for="reason6">Other</label></h3>\
                                    This post needs a moderator\'s attention. Please describe exactly what\'s wrong.\
                                    <div class="modal-reason-other hide">\
                                        <textarea name="other" placeholder="Explain reason"/>\
                                    </div>\
                                </li>\
                            </ul>\
                            <input type="submit" class="button left" value="Submit flag"/>\
                        </form>'

    $('a.flag').click(function(e) {
        e.preventDefault();
        var $this = $(this),
        $elem = $(this).closest('li'),
        id = $elem.attr('data-id');

        $.createModal('Flag post', flagTemplate, function() {
            var $modal = this;
            $modal.find('.reasons input[type=radio]').on('change', function() {
                if ($modal.find('#reason5:checked').length) {
                    $modal.find('.modal-reason-other').slideDown('fast');
                } else {
                    $modal.find('.modal-reason-other').slideUp('fast');
                }
            });

            $modal.find('input[type=submit]').on('click', function(e) {
                e.preventDefault();

                console.log($modal.find(':checked').length);

                if (!$modal.find(':checked').length)
                    return false;

                var reason = $modal.find(':checked').attr('value'),
                    other = $modal.find('textarea').val();

                data = {
                    reason: reason,
                    extra: other,
                    id: id
                }

                $modal.height($modal.height());
                $modal.find('.modal-content').fadeOut('fast', function() {
                    $.get('/files/ajax/forum.php?action=post.flag', data, function() {
                        $modal.find('.modal-content').html($('<div>', {'html': "<i class='icon-good'></i>Thank you", 'class': 'thanks'})).fadeIn();
                    });
                });
            });
        });
    });



    $('a.karma').on('click', function(e) {
        e.preventDefault();

        var $this = $(this);
        var id = $this.closest('li').attr('data-id');
        var $value = $this.siblings('span');
        var value;

        if ($this.hasClass('karma-up')) {
            var uri = '/files/ajax/forum.php?action=karma.positive&id=' + id;
            value = parseInt($value.text())+1;
        } else {
            var uri = '/files/ajax/forum.php?action=karma.negative&id=' + id;
            value = parseInt($value.text())-1;
        }

        if ($this.hasClass('karma-cancel')) {
            uri += '&cancel';
            if ($this.hasClass('karma-up')) {
                value -= 2;
            } else {
                value += 2;
            }
        } else if ($this.siblings().hasClass('karma-cancel')) {
            if ($this.hasClass('karma-up')) {
                value += 1;
            } else {
                value -= 1;
            }            
        }

        $.getJSON(uri, function(data) {
            if (data['status'] == false)
                return false;

            $value.text(value);
            
            $this.siblings('a').removeClass('karma-cancel');
            $this.toggleClass('karma-cancel');
        });
    });



    $('ul.post-list li').each(function() {
        var self = this;

        if ($(self).find('.post_content .bbcode-youtube').length)
            setTimeout(resizePostInfo(self), 5);
        else if ($(self).find('.post_content .bbcode-vimeo').length)
            setTimeout(resizePostInfo(self), 5);
        else
            resizePostInfo(self)();
    });


    $('.show-post').on('click', function(e) {
        e.preventDefault();

        var $this = $(this).parent(),
            $container = $this.closest('.removed-karma');

        $container.slideUp(function() {
            $this.hide();

            $this.siblings('.post_body').show();
            $container.find('.post_header > *').show();
            $container.find('.post_header a img').show();
            $container.css({backgroundColor: '#1E1E1E'});
            $container.find('.post_header a').removeClass('strong').removeClass('dark');

            resizePostInfo();
            $container.slideDown();
        });

        // $this.fadeOut(function() {
        //     $this.siblings('.post_body').slideDown();
        //     $container.find('.post_header > *').slideDown(function() {
        //         resizePostInfo();
        //     });
        //     $container.find('.post_header a').removeClass('strong').removeClass('dark');
        //     $container.find('.post_header a img').slideDown();
        //     $container.css({backgroundColor: '#1E1E1E'});
        // });
    })


    function resizePostInfo(self) {
        return function() {
            var h = $(self).find('.post_content').height();
            if (h > $(self).find('.post_header').height()) {
                $(self).find('.post_header').height(h-4);
            }
        }
    }
});