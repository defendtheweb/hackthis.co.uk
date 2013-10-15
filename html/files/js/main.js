var loggedIn = true; // overwritten in guest.js

$(function() {
    $('textarea').autosize();
    $('.scroll').mCustomScrollbar();
    $("#global-nav-sticky").sticky({topSpacing:0});
    $('.select-menu').selectMenu();

    // Show/hide navigation
    $('#global-nav .show-nav').on('click', function(e) {
        e.preventDefault();
        $('#global-nav nav > ul').toggleClass('show');
    });

    // Open external links in new tab
    $('body').on('click', 'a[href^="http://"]:not(.stop-external), a[href^="https://"]:not(.stop-external)', function(e) {
        window.open($(this).attr('href'));
        return false;
    });

    $('a[href^="http://"]:has(img), a[href^="https://"]:has(img)').addClass('hide-external');

    // Confirmation box
    $('.confirmation').on('click', function(e) {
        if ($(this).attr('data-confirm'))
            return confirm($(this).attr('data-confirm') + ", are you sure?");
        else
            return confirm("Are you sure?");
    });

    // Update timestamps
    (function updateTimes() {
        $('time:not([data-timesince="false"])').each(function(index, value) {
            $this = $(this);
            d = new Date($this.attr('datetime'));
            $this.text(timeSince(d, $this.hasClass('short'), $this.hasClass('forceSince')));
            if (!$this.attr('title'))
                $this.attr('title', timeString(d));
        });

        setTimeout(updateTimes, 1000);
    })();
    // $('time:not([data-timesince="false"])').each(function(index, value) {
    //     $this = $(this);
    //     d = new Date($this.attr('datetime'));
    //     $this.addClass('hint--top').attr('data-hint', timeString(d, $this.hasClass('short')));
    // });


    // Hide facebook connect link
    $('.facebook-connect .remove').on('click', function(e) {
        var uri = '/files/ajax/user.php?action=connect.hide';
        var $elem = $(this).parent();
        $.getJSON(uri, function(data) {
            if (data.status) {
                $elem.slideUp();
            }
        });
        e.preventDefault();
    });
});

var thecode = 'getinthere';