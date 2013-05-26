var loggedIn = true; // overwritten in guest.js

$(function() {
    $('textarea').autosize();

    // Open external links in new tab
    $('body').on('click', 'a[href^="http://"], a[href^="https://"]', function(e) {
        window.open($(this).attr('href'));
        return false;
    });

    // Confirmation box
    $('.confirmation').on('click', function(e) {
        if ($(this).attr('data-confirm'))
            return confirm($(this).attr('data-confirm') + ", are you sure?");
        else
            return confirm("Are you sure?");
    });

    // Update timestamps
    (function updateTimes() {
        $('time').each(function(index, value) {
            $this = $(this);
            d = new Date($this.attr('datetime'));
            $this.text(timeSince(d, $this.hasClass('short')));
        });

        setTimeout(updateTimes, 10000);
    })();
});