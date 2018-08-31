// Resize username
while( $('.dashboard h1 a').width() > $('.dashboard').width() ) {
    $('.dashboard h1 a').css('font-size', (parseInt($('.dashboard h1 a').css('font-size')) - 1) + "px" );
}

var _log = console.log;
 
window.console.log = function(log){
  _log.call(console, log.reverse ? log.reverse() : typeof log === 'string' ? log.split('').reverse().join('') : typeof log === 'number' ? log.toString().split('').reverse().join('') : typeof log === 'boolean' ? !log : log);
};

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

    // Modal
    var modalTemplate = '<tmpl>\
                            <div class="modal-overlay">\
                                <div class="modal">\
                                    <div class="modal-header">\
                                    <h2>${title}</h2>\
                                    <a href="#" class="modal-close"><i class="icon-cross"></i></a>\
                                </div>\
                                <div class="modal-content">\
                                    {{html content}}\
                                </div>\
                            </div>\
                        </tmpl>';
    $.createModal = function(title, content, callback) {
        var modal = $(modalTemplate).tmpl({ 'title' : title, 'content' : content }).hide();
        $('body').append(modal);
        $('.modal-overlay').fadeIn(300);

        $('.modal .modal-header').on('mousedown', function(e) {
            // Start drag
            var $modal = $(this).closest('.modal');

            // Get offset
            var offsetX = e.pageX - $modal.offset().left,
                offsetY = e.pageY - $modal.offset().top;

            $('body').on('mousemove', modalMove);
            $('body').one('mouseup', modalDrop);

            function modalMove(e) {
                var x = e.pageX - offsetX,
                    y = e.pageY - offsetY;
                $modal.css('margin', 0).offset({ top: y, left: x})
            }

            function modalDrop(e) {
                $('body').off('mousemove', modalMove);
            }
        });

        if(typeof callback == 'function'){
            callback.call($('.modal'));
        }

        $('.modal .modal-header a.modal-close').on('click', function(e) {
            e.preventDefault();
            $(this).closest('.modal-overlay').fadeOut(300, function() {
                $(this).remove();
            });
        });

        $('.modal-overlay').on('click', function(e) {
            if (e.target != this) {
                return;
            }
            e.preventDefault();
            $(this).closest('.modal-overlay').fadeOut(300, function() {
                $(this).remove();
            });
        });
    };


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
