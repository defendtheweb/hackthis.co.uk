// Resize username
while( $('.dashboard h1 a').width() > $('.dashboard').width() ) {
    $('.dashboard h1 a').css('font-size', (parseInt($('.dashboard h1 a').css('font-size')) - 1) + "px" );
}


// Internet defense league
window._idl = {};
_idl.variant = "banner";
(function() {
    var idl = document.createElement('script');
    idl.type = 'text/javascript';
    idl.async = true;
    idl.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'members.internetdefenseleague.org/include/?url=' + (_idl.url || '') + '&campaign=' + (_idl.campaign || '') + '&variant=' + (_idl.variant || 'banner');
    document.getElementsByTagName('body')[0].appendChild(idl);
})();


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
    $('body').on('click', 'a[href^="http://"]:not(.stop-external):not(.fancybox), a[href^="https://"]:not(.stop-external):not(.fancybox)', function(e) {
        ga('send', 'event', 'outbound', 'click', $(this).attr('href'));

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


    // Carousel slider
    var timer;
    $('.slider').each(function() {
        var $this = $(this),
        tallest = 0;

        $this.children('li').each(function(i) {
            $(this).width($(this).width()).css({position: 'absolute', top: 0}).css('left', (110*i)+'%');

            if ($(this).height() > tallest)
                tallest = $(this).height();
        });

        $(this).height(tallest);

        // Add locator
        $ul = $('<ul>', {class: 'locator'});
        $this.children('li').each(function(i) {
            if (i == 0)
                $ul.append($('<li>', {class: 'active'}));
            else
                $ul.append($('<li>'));
        });
        $this.parent().append($ul);

        //
        timer = setTimeout(function(){slide_carousel($this)}, 5000);

        $this.parent().on('mouseover', function(e) {
            clearTimeout(timer);
        }).on('mouseout', function(e) {
            timer = setTimeout(function(){slide_carousel($this)}, 5000);
        });
    });

    function slide_carousel(target) {
        var children = target.children('li'),
        active;
        children.each(function(index) {
            var left = parseFloat($(this)[0].style.left);
            if (left < 0) {
                left = (children.length-2) * 110;
                $(this).css('visibility', 'hidden');
            } else {
                left -= 110;
                $(this).css('visibility', 'visible');
            }
            $(this).css('left', left + '%');

            if (left == 0)
                active = index;
        });

        var $locators = target.siblings('.locator').children('li');
        $locators.removeClass('active');
        $($locators[active]).addClass('active');

        timer = setTimeout(function(){slide_carousel(target)}, 5000);
    }

    // Listen for when tab is focused, so we can throttle requests
    $(window).on("blur focus", function(e) {
        if (e.type === "blur") {
            $(this).data('isInactive', true);
        } else {
            $(this).data('isInactive', false);
        }
    });
});

var thecode = 'getinthere';