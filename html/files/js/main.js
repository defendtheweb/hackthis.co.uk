$(function() {

    function timeSince(oldD, short) {
        var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

        newD = new Date();
        diff = Math.round((newD.getTime() - oldD.getTime()) / 1000);
        
        isSameDay = (oldD.getDate() == newD.getDate() 
                     && oldD.getMonth() == newD.getMonth()
                     && oldD.getFullYear() == newD.getFullYear());

        if (isSameDay) {
            if (diff < 60) {
                return "secs" + (!short ? " ago" : '');
            } else if (diff < 3600) {
                var n = Math.floor(diff/60);
                return n + " min" + (n==1?'':'s') + (!short ? " ago" : '');
            } else {
                var n = Math.floor(diff/3600);
                return n + " hour" + (n==1?'':'s') + (!short ? " ago" : '');
            }
        } else {
            newD.setDate(newD.getDate() - 1);

            isYesterday = (oldD.getDate() == newD.getDate() 
                         && oldD.getMonth() == newD.getMonth()
                         && oldD.getFullYear() == newD.getFullYear());

            if (isYesterday) {
                return "Yesterday";
            } else {
                newD.setDate(newD.getDate() - 6);
                if (oldD > newD)
                    return days[oldD.getDay()];
                else {
                    var day = oldD.getDate();
                    var month = oldD.getMonth()+1;
                    return ((day < 10) ? '0':'') + day + "/" + ((month < 10) ? '0':'') + month + "/" + (!short ? oldD.getFullYear() : '');
                }
            }
        }
    }

    // Update timestamps
    (function updateTimes() {
        $('time').each(function(index, value) {
            $this = $(this);
            d = new Date($this.attr('datetime'));
            $this.text(timeSince(d, $this.hasClass('short')));
        });

        setTimeout(updateTimes, 10000);
    })();


    // Update notifications
    (function updateTimes() {
        uri = '/files/ajax/notifications.php';
        $.getJSON(uri, function(data) {
            if (data.events > 0) {
                $('.nav-extra-events').addClass('alert');
                $('#event-counter').fadeIn(500).text(data.events);
            } else {
                $('.nav-extra-events').removeClass('alert');
                $('#event-counter').fadeOut(200);
            }

            if (data.pm > 0) {
                $('.nav-extra-pm').addClass('alert');
                $('#pm-counter').fadeIn(500).text(data.pm);
            } else {
                $('.nav-extra-pm').removeClass('alert');
                $('#pm-counter').fadeOut(200);
            }
        });

        setTimeout(updateTimes, 20000);
    })();


    var notificationsTmpl = '<tmpl>'+
                           '{{if seen == 0}}'+
                           '    <li class="new">'+
                           '{{else}}'+
                           '    <li>'+
                           '{{/if}}'+
                           '        <time class="short" datetime="${timestamp}">${time}</time>'+
                           '{{if username}}'+
                           '        <a href="/user/${username}">'+
                           '            <img class="left" width="28" height="28" src="http://www.hackthis.co.uk/users/images/28/1:1/${img}.jpg"/>'+
                           '        </a>'+
                           '{{/if}}'+
                           '{{if pm_id}}'+
                           '            <a class="strong" href="/inbox/${pm_id}">${title}<a/><br/>'+
                           '            ${message}'+
                           '{{else}}'+
                           '    {{if type == 1}}'+
                           '            <a href="/user/${username}">${username}<a/> sent you a friend request'+
                           '            {{if status == 0}}'+
                           '                <a href="#">Accept</a> | <a href="#">Decline</a>'+
                           '            {{/if}}'+
                           '    {{else type == 2}}'+
                           '            <a href="/user/${username}">${username}<a/> accepted your friend request<br/>'+
                           '    {{else type == 3}}'+
                           '            You have been awarded <a href="/medals/"><div class="medal medal-${colour}">${label}</div></a><br/>'+
                           '    {{/if}}'+
                           '{{/if}}'+
                           '    </li>'+
                           '</tmpl>';

    $('.nav-extra').bind('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var dropdown = $('#nav-extra-dropdown');
        var icons = $('.nav-extra').parent();
        var parent = $(this).parent();

        if (dropdown.is(":visible") &&
            (($(this).hasClass('nav-extra-pm') && parent.hasClass('active-pm')) ||
            ($(this).hasClass('nav-extra-events') && parent.hasClass('active-events')))) {
                dropdown.slideUp(200);
                icons.removeClass('active');
                return false;
        }

        var uri = '/files/ajax/notifications.php'

        icons.removeClass('active');
        if ($(this).hasClass('nav-extra-pm')) {
            uri += '?pm';
            icons.removeClass('active-events');
            parent.addClass('active active-pm');
        } else if ($(this).hasClass('nav-extra-events')) {
            uri += '?events';
            icons.removeClass('active-pm');
            $(this).removeClass('alert');
            $('#event-counter').fadeOut(200);
            parent.addClass('active active-events');
        } else {
            return false;
        }

        $.getJSON(uri, function(data) {
            if (data.length) {
                $.each(data, function(index, item) {
                    var d = new Date(item.timestamp*1000);
                    item.time = timeSince(d, true);
                    item.timestamp = d.toISOString();
                });

                var html = $(notificationsTmpl).tmpl(data);
            } else {
                if (parent.hasClass('active-events'))
                    var html = '<div class="center empty"><i class="icon-globe icon-4x"></i>No notifications available</div>';
                else
                    var html = '<div class="center empty"><i class="icon-envelope-alt icon-4x"></i>No messages available</div>';
            }

            dropdown.html(html).slideDown(200);
        });

        
        
        //dropdown.html('<img src="/files/images/icons/loading_bg.gif"/>').show();

        $(document).bind('click.extra-hide', function(e){
           if ($(e.target).closest('#nav-extra-dropdown').length != 0 && $(e.target).not('.nav-extra')) return false;
           dropdown.slideUp(200);
           icons.removeClass('active');
           $(document).unbind('click.extra-hide');
        });
    });

});

if (!Date.prototype.toISOString) {
    Date.prototype.toISOString = function() {
        function pad(n) { return n < 10 ? '0' + n : n }
        return this.getUTCFullYear() + '-'
            + pad(this.getUTCMonth() + 1) + '-'
            + pad(this.getUTCDate()) + 'T'
            + pad(this.getUTCHours()) + ':'
            + pad(this.getUTCMinutes()) + ':'
            + pad(this.getUTCSeconds()) + 'Z';
    };
}
