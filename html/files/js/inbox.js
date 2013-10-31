$(function() {
    var container = $('.inbox-main');

    $(".inbox .sticky").css('width', $(".inbox .sticky").width()-2);
    $(".inbox .sticky").sticky({topSpacing:45});


    if ($(".inbox-main ul").length) {
        if (container.find('.new').length) {
            offset = $(".inbox-main ul li.new:first").offset().top;
        } else {
            offset = $(document).height() - $(".inbox-main textarea").offset().top;
        }
        $('html, body').animate({
            scrollTop: offset
        }, 800);
    }

    $('.delete-convo').click(function(e) {
        e.preventDefault();

        var $this = $(this);
        $.confirm({
            title   : 'Delete Confirmation',
            message : 'Are you sure you want to remove this activity from your feed? <br />It cannot be restored at a later time! Continue?',
            buttons : {
                Cancel  : {
                    class: 'cancel'
                },
                Confirm : { 
                    action: function() {
                        window.location = $this.attr('href');
                    }
                }
            }
        });
    });

    var conversationSkip = 0;
    $('#conversation-search input').on('keyup', function(e) {
        container.find('.conversation .body span.highlight').contents().unwrap();

        if (e.which == 27 || e.keyCode == 27) {
            $(this).val("");
        }

        var term = $(this).val().replace(/[^a-zA-Z 0-9]+/g,'');;
        if (term.length <= 1)
            return false;

        conversationFind(container, term);
    }).on('keypress', function(e) {
        if (e.which == 13) {
            conversationSkip++;
        } else {
            conversationSkip = 0;
        }
    });

    function conversationFind(container, term) {
        var n = 0;
        var success = false;
        $(container.find('.conversation .body').get().reverse()).each(function() {
            var rx = new RegExp('(?![^<]+>)'+term, "gi");
            var before = $(this).html();
            var after = before.replace(rx, '<span class="highlight">$&</span>');
            if (before !== after) {
                if (conversationSkip >= ++n)
                    return;

                success = true;

                $(this).html(after);
                $('html, body').stop().animate({
                    scrollTop: $(this).offset().top - 100
                }, 400);

                return false;
            }
        });

        if (!success && conversationSkip > 0) {
            //if no matches return skip to 0
            conversationSkip = 0;
            conversationFind(container, term);
        }
    }
});