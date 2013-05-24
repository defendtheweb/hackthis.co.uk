$(function() {
    var feedTmpl = '<tmpl>'+
                   '    <li>'+
                   '        {{if type == "join"}}'+
                   '            <div class="col span_19"><i class="icon-user"></i><a href="/user/${username}">${username}</a></div>'+
                   '        {{else type == "friend"}}'+
                   '            <div class="col span_19"><i class="icon-addfriend"></i><a href="/user/${username}">${username}</a> <span class="extra">and</span> <a href="/user/${username_2}">${username_2}</a></div>'+
                   '        {{else type == "medal"}}'+
                   '            <div class="col span_19"><i class="icon-trophy colour-${colour}"></i><a href="/user/${username}">${username}</a> <span class="extra">awarded</span> <a href="/settings/medals.php">${label}</a></div>'+
                   '        {{else type == "comment"}}'+
                   '            <div class="col span_19"><i class="icon-comments"></i><a href="${slug}">${title}</a> <span class="extra">by</span> <a href="/user/${username}">${username}</a></div>'+
                   '        {{else type == "favourite"}}'+
                   '            <div class="col span_19"><i class="icon-heart"></i><a href="${slug}">${title}</a> <span class="extra">by</span> <a href="/user/${username}">${username}</a></div>'+
                   '        {{else type == "article"}}'+
                   '            <div class="col span_19"><i class="icon-books"></i><a href="${slug}">${title}</a></div>'+
                   '        {{/if}}'+
                   '        <div class="col span_5"><time class="short" datetime="${timestamp}">${time}</time></div>'+
                   '    </li>'+
                   '</tmpl>';

    // Update notifications
    var lastUpdate = 0;
    (function updateTimes() {
        uri = '/files/ajax/notifications.php';
        $.post(uri, {last: lastUpdate}, function(data) {
            if (data.counts.events > 0) {
                $('.nav-extra-events').addClass('alert');
                $('#event-counter').fadeIn(500).text(data.counts.events);
            } else {
                $('.nav-extra-events').removeClass('alert');
                $('#event-counter').fadeOut(200);
            }

            if (data.counts.pm > 0) {
                $('.nav-extra-pm').addClass('alert');
                $('#pm-counter').fadeIn(500).text(data.counts.pm);
            } else {
                $('.nav-extra-pm').removeClass('alert');
                $('#pm-counter').fadeOut(200);
            }

            if (data.feed.length) {
                lastUpdate = data.feed[0].timestamp;

                $.each(data.feed, function(index, item) {
                    var d = new Date(item.timestamp*1000);
                    item.time = timeSince(d, true);
                    item.timestamp = d.toISOString();
                });

                var items = $(feedTmpl).tmpl(data.feed);
                if ($('sidebar .feed ul').length) {
                    items.hide().prependTo($('sidebar .feed ul')).slideDown();
                } else {
                    var html = $('<ul>').append(items);
                    $('sidebar .feed .feed_loading').replaceWith(html);
                }
            }
        }, 'json');

        setTimeout(updateTimes, 10000);
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
                           '    {{if type == "friend"}}'+
                           '            <a href="/user/${username}">${username}<a/> sent you a friend request'+
                           '            {{if status == 0}}'+
                           '                <a href="#">Accept</a> | <a href="#">Decline</a>'+
                           '            {{/if}}'+
                           '    {{else type == "friend"}}'+
                           '            <a href="/user/${username}">${username}<a/> accepted your friend request<br/>'+
                           '    {{else type == "medal"}}'+
                           '            You have been awarded <a href="/medals/"><div class="medal medal-${colour}">${label}</div></a><br/>'+
                           '    {{else type == "comment_reply"}}'+
                           '            <a href="/user/${username}">${username}<a/> replied to your comment on <a href="${slug}">${title}</a><br/>'+
                           '    {{else type == "comment_mention"}}'+
                           '            <a href="/user/${username}">${username}<a/> mentioned you in a comment on <a href="${slug}">${title}</a><br/>'+
                           '    {{/if}}'+
                           '{{/if}}'+
                           '    </li>'+
                           '</tmpl>';

    $('.nav-extra').on('click', function(e) {
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
            data = data.items;
            if (data.length) {
                $.each(data, function(index, item) {
                    var d = new Date(item.timestamp*1000);
                    item.time = timeSince(d, true);
                    item.timestamp = d.toISOString();
                });

                var items = $(notificationsTmpl).tmpl(data);
                var html = $('<ul>').append(items);
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
           if ($(e.target).closest('#nav-extra-dropdown').length != 0 && $(e.target).not('.nav-extra')) return true;
           dropdown.slideUp(200);
           icons.removeClass('active');
           $(document).unbind('click.extra-hide');
        });
    });
});