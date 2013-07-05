$(function() {
    var container = $('.inbox-main');

    if (container.find('.new').length) {
        container.mCustomScrollbar("scrollTo", "ul li.new:first");
    } else {
        //container.mCustomScrollbar("scrollTo", "li:nth-last-child(2):first");
        container.mCustomScrollbar("scrollTo", "bottom");
    } 


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
                container.mCustomScrollbar("scrollTo", $(this).position().top);
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