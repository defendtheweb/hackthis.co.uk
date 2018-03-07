var socket = null;
if (typeof io !== 'undefined') {
    socket = io.connect('https://hackthis.co.uk:8080/', { secure: true });
}
var favcounter = new FavCounter();
var counter_chat = 0;
var counter_notifications = 0;

$(function() {
    var username = $('body').attr('data-username');
    var key = $('body').attr('data-key');

    var feedTmpl = '<tmpl>'+
    '    <li>'+
    '      <div class="col span_18">'+
    '        {{if type == "join"}}'+
    '            <i class="icon-user"></i><a href="/user/${username}">${username}</a>'+
    '        {{else type == "friend"}}'+
    '            <i class="icon-addfriend"></i><a href="/user/${username}">${username}</a> <span class="dark">and</span> <a href="/user/${username_2}">${username_2}</a>'+
    '        {{else type == "medal"}}'+
    '            <i class="icon-trophy colour-${uri}"></i><a href="/medals.php#${title.toLowerCase()}">${title}</a> <span class="dark">to</span> <a href="/user/${username}">${username}</a>'+
    '        {{else type == "comment"}}'+
    '            <i class="icon-comments"></i><a href="${uri}">${title}</a> <span class="dark">by</span> <a href="/user/${username}">${username}</a>'+
    '        {{else type == "forum_post"}}'+
    '            <i class="icon-chat"></i><a href="${uri}">${title}</a> <span class="dark">by</span> <a href="/user/${username}">${username}</a>'+
    '        {{else type == "favourite"}}'+
    '            <i class="icon-heart"></i><a href="${uri}">${title}</a> <span class="dark">by</span> <a href="/user/${username}">${username}</a>'+
    '        {{else type == "article"}}'+
    '            <i class="icon-books"></i><a href="${uri}">${title}</a> <span class="dark">by</span> <a href="/user/${username}">${username}</a>'+
    '        {{else type == "news"}}'+
    '            <i class="icon-article"></i><a href="${uri}">${title}</a> <span class="dark">by</span> <a href="/user/${username}">${username}</a>'+
    '        {{else type == "level"}}'+
    '            <i class="icon-good"></i><a href="${uri}">${title}</a> <span class="dark">by</span> <a href="/user/${username}">${username}</a>'+
    '        {{/if}}'+
    '        </div>'+
    '        <div class="col span_6 time right"><time class="short" datetime="${timestamp}"></time></div>'+
    '    </li>'+
    '</tmpl>';

    /* ACTUAL STUFF */
    if (socket) {
        socket.on('feed', function (data) {
            var item = $(feedTmpl).tmpl(data);

            if ($('.sidebar .feed ul').length) {
                item.hide().prependTo($('.sidebar .feed ul')).slideDown();
            } else {
                var html = $('<ul>').append(item);
                $('.sidebar .feed .feed_loading').replaceWith(html);
            }
        });
    } else {
        $('.sidebar .feed .feed_loading').html('<strong>Feed offline</strong>');
    }


    /* NOTIFICATIONS */
    // Update notifications
    var lastUpdate = 0;
    var UPDATE_INTERVAL_ACTIVE = 10000;
    var UPDATE_INTERVAL_INACTIVE = 30000;
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

            counter_notifications = data.counts.events + data.counts.pm;
            favcounter.set(counter_notifications + counter_chat);

            // if (data.feed.length) {
            //     lastUpdate = data.feed[0].timestamp;

            //     $.each(data.feed, function(index, item) {
            //         var d = new Date(item.timestamp);
            //         item.time = timeSince(d, true);
            //         item.timestamp = d.toISOString();
            //     });

            //     var items = $(feedTmpl).tmpl(data.feed);
            //     if ($('.sidebar .feed ul').length) {
            //         items.hide().prependTo($('.sidebar .feed ul')).slideDown();
            //     } else {
            //         var html = $('<ul>').append(items);
            //         $('.sidebar .feed .feed_loading').replaceWith(html);
            //     }
            // }
        }, 'json');
        
        if ($(window).data('isInactive') === true) {
            // Throttle updates if tab is not focused
            setTimeout(updateTimes, UPDATE_INTERVAL_INACTIVE);
        } else {
            setTimeout(updateTimes, UPDATE_INTERVAL_ACTIVE);
        }
    })();

    var notificationsTmpl = '<tmpl>'+
                            '{{if seen == 0}}'+
                            '    <li class="new">'+
                            '{{else}}'+
                            '    <li>'+
                            '{{/if}}'+
                            '        <time class="short" datetime="${timestamp}"></time>'+
                            '{{if username}}'+
                            '        <a href="/user/${username}">'+
                            '            <img class="left" width="28" height="28" src="${img}"/>'+
                            '        </a>'+
                            '{{/if}}'+
                            '    {{if type == "friend"}}'+
                            '            {{if status == 1}}'+
                            '                You accepted a friend request from <a href="/user/${username}">${username}<a/>'+
                            '            {{else}}'+
                            '                <a href="/user/${username}">${username}<a/> sent you a friend request'+
                            '                <a href="#" class="addfriend" data-uid="${uid}">Accept</a> | <a href="#" class="removefriend" data-uid="${uid}">Decline</a>'+
                            '            {{/if}}'+
                            '    {{else type == "friend_accepted"}}'+
                            '            <a href="/user/${username}">${username}<a/> accepted your friend request'+
                            '    {{else type == "medal"}}'+
                            '            You have been awarded <a href="/medals.php#${label.toLowerCase()}"><div class="medal medal-${colour}">${label}</div></a><br/>'+
                            '    {{else type == "comment_reply"}}'+
                            '            <a href="/user/${username}">${username}<a/> replied to your comment on <a href="${slug}">${title}</a><br/>'+
                            '    {{else type == "comment_mention"}}'+
                            '            <a href="/user/${username}">${username}<a/> mentioned you in a comment on <a href="${slug}">${title}</a><br/>'+
                            '    {{else type == "forum_post"}}'+
                            '            <a href="/user/${username}">${username}<a/> posted in <a href="${slug}">${title}</a><br/>'+
                            '    {{else type == "forum_mention"}}'+
                            '            <a href="/user/${username}">${username}<a/> mentioned you in <a href="${slug}">${title}</a><br/>'+
                            '    {{else type == "article"}}'+
                            '            Your article has been published <a href="${slug}">${title}</a><br/>'+
                            '    {{else type == "mod_contact"}}'+
                            '            <a href="/user/${username}">${username}</a> replied to your <a href="/contact?view=${ticket}">ticket</a><br/>'+
                            '    {{else type == "mod_report"}}'+
                            '            <a href="/user/${username}">${username}</a> created a new report, <a href="/contact?report=${report}">view report</a><br/>'+
                            '    {{/if}}'+
                            '    </li>'+
                            '</tmpl>';

    var inboxTmpl = '<tmpl>'+
                    '{{if seen == 0}}'+
                    '    <li class="new">'+
                    '{{else}}'+
                    '    <li>'+
                    '{{/if}}'+
                    '        <a class="show-conversation" data-conversation="${pm_id}" href="/inbox/${pm_id}">'+
                    '            <time class="short" datetime="${timestamp}"></time>'+
                    '            {{each(i,user) users}}'+
                    '                ${user.username}{{if users.length-1 != i}},{{/if}}'+
                    '            {{/each}}'+
                    '            <br/>'+
                    '            <span class="dark">{{html message}}</span>'+
                    '        </a>'+
                    '    </li>'+
                    '</tmpl>';


    var composeForm =   '<form class="send"><label for="to">To:</label>'+
                        '<input name="to" class="suggest hide-shadow" data-suggest-at="false" data-suggest-max="2" id="to" autocomplete="off"/><br/>'+
                        '<label for="message">Message:</label><br/>'+
                        '<textarea class="hide-shadow"></textarea>'+
                        '<input type="submit" class="button" value="Send"/>'+
                        '<span class="error"></span>';

    var replyForm =     '<form class="send">'+
                        '<label for="message">Reply:</label><br/>'+
                        '<textarea class="hide-shadow"></textarea>'+
                        '<input type="submit" class="button" value="Send"/>'+
                        '<span class="error"></span>';

    var messagesTmpl =  '<tmpl>'+
                        '{{if seen == 0}}'+
                        '    <li class="new">'+
                        '{{else}}'+
                        '    <li>'+
                        '{{/if}}'+
                        '        <time class="short" datetime="${timestamp}"></time>'+
                        '{{if username}}'+
                        '        <a href="/user/${username}">'+
                        '            <img class="left" width="28" height="28" src="${img}"/>'+
                        '            ${username}'+
                        '        </a><br/>'+
                        '{{else}}'+
                        '        <img class="left" width="28" height="28"/>'+
                        '        <span class="white">You</span><br/>'+
                        '{{/if}}'+
                        '        {{html message}}'+
                        '    </li>'+
                        '</tmpl>';

    var dropdown = $('#nav-extra-dropdown');
    var icons = $('.nav-extra').parent();

    $('.nav-extra').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var parent = $(this).parent();

        if (dropdown.is(":visible") &&
            (($(this).hasClass('nav-extra-pm') && parent.hasClass('active-pm')) ||
             ($(this).hasClass('nav-extra-events') && parent.hasClass('active-events')))) {
                    dropdown.slideUp(200);
                    icons.removeClass('active');
                    return false;
        }

        icons.removeClass('active');
        if ($(this).hasClass('nav-extra-pm')) {
            var uri = '/files/ajax/inbox.php?list';
            icons.removeClass('active-events');
            parent.addClass('active active-pm');
        } else if ($(this).hasClass('nav-extra-events')) {
            var uri = '/files/ajax/notifications.php?events';
            icons.removeClass('active-pm');
            $(this).removeClass('alert');
            $('#event-counter').fadeOut(200);
            parent.addClass('active active-events');
        } else {
            return false;
        }

        $.getJSON(uri, function(data) {
            var items = data.items;
            if (items.length || (items.items && items.items.length)) {
                if (parent.hasClass('active-events')) {
                    var html = $('<ul>');

                    var count = data.friends.length - 1;
                    $.each(data.friends, function (index) {
                        tmpli = $(notificationsTmpl).tmpl(this);
                        if (count == index) {
                            tmpli.addClass('last-request');
                        }
                        html.append(tmpli);
                    });
                    var items = $(notificationsTmpl).tmpl(items.items);

                    var more = $("<li>", {class: "more"});
                    $('<a>', {text: "View More", href: "/alerts.php"}).appendTo(more); 
                    html.append(items).append(more);
                } else {
                    var items = $(inboxTmpl).tmpl(items);

                    var messagesHTML = $('<div>', {class: "messages"});
                    $('<a>', {text: "New Message", class: "toggle-compose more", href: "/inbox/compose"}).appendTo(messagesHTML);

                    var list = $('<ul>').append(items);
                    list.appendTo(messagesHTML);

                    $('<a>', {text: "Full View", class: "more", href: "/inbox/"}).appendTo(messagesHTML);


                    var extraHTML = $('<div>', {class: "extra"});


                    html = $('<div>', {class: "message-container"}).append(messagesHTML);
                    html.append(extraHTML)
                }
            } else {
                if (parent.hasClass('active-events'))
                    var html = '<div class="center empty"><i class="icon-globe icon-4x"></i>No notifications available</div>';
                else {
                    var messagesHTML = $('<div>', {class: "messages"});
                    $('<a>', {text: "New Message", class: "toggle-compose more", href: "/inbox/compose"}).appendTo(messagesHTML);

                    var list = $('<div class="center empty"><i class="icon-envelope-alt icon-4x"></i>No messages available</div>');
                    list.appendTo(messagesHTML);

                    $('<a>', {text: "Full View", class: "more", href: "/inbox/"}).appendTo(messagesHTML);


                    var extraHTML = $('<div>', {class: "extra"});


                    html = $('<div>', {class: "message-container"}).append(messagesHTML);
                    html.append(extraHTML)
                }
            }

            dropdown.html(html).slideDown(200);
        });

        //dropdown.html('<img src="/files/images/icons/loading_bg.gif"/>').show();

        bindCloseNotifications();
    });


    $('#global-nav').on('click', '.addfriend, .removefriend', function(e) {
        e.preventDefault();
        var $this = $(this);

        if ($this.hasClass('addfriend'))
            var uri = '/files/ajax/user.php?action=friend.add&uid=';
        else
            var uri = '/files/ajax/user.php?action=friend.remove&uid=';
        uri += $this.attr('data-uid');
        uri += "&token=" + $('body').attr('data-key');

        $.getJSON(uri, function(data) {
            if (data.status)
                $this.closest('li').slideUp();
        });
    }).on('click', '.toggle-compose', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var container = $('#global-nav .message-container');

        if (container.hasClass('show-extra')) {
            container.removeClass('show-extra');
        } else {
            var composeHTML = container.children('.extra');
            composeHTML.html('');

            $('<a>', {text: "Back to Inbox", class: "toggle-compose more", href: "/inbox"}).appendTo(composeHTML);
            composeHTML.append(composeForm);
            $('<a>', {text: "Full View", class: "more", href: "/inbox/compose"}).appendTo(composeHTML);

            container.addClass('show-extra');

            $('#nav-extra-dropdown .suggest').autosuggest();
        }
    }).on('click', '.show-conversation', function(e) {
        e.preventDefault();
        var $this = $(this);

        var id = $this.attr('data-conversation');
        var uri = '/files/ajax/inbox.php?view&id=' + id;
        $.getJSON(uri, function(data) {
            data = data.items;
            if (data.length) {
                var container = $('#global-nav .message-container');

                items = $('<ul>', {class: 'scroll'}).append($(messagesTmpl).tmpl(data));

                items.append($('<li>').append(replyForm));
                items.find('form').attr('data-conversation', id);

                var messagesHTML = container.children('.extra');
                messagesHTML.html('');

                $('<a>', {text: "Back to Inbox", class: "toggle-compose more", href: "/inbox"}).appendTo(messagesHTML);
                messagesHTML.append(items);
                $('<a>', {text: "Full View", class: "more", href: "/inbox/"+id}).appendTo(messagesHTML);

                container.addClass('show-extra');

                $('#global-nav .scroll').mCustomScrollbar();hideNotifications

                if (container.find('.new').length) {
                    $('#global-nav .scroll').mCustomScrollbar("scrollTo", "li.new:first");
                } else {
                    $('#global-nav .scroll').mCustomScrollbar("scrollTo", "li:nth-last-child(2)");
                }

                $this.parent().removeClass('new');
            }
        });
    }).on('click', 'form.send input.button', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var data = {};

        $form = $(this).closest('form');
        $error = $form.find('span.error');
        data.body = $form.find('textarea').val();

        $error.text('');

        if ($form.find('#to').length) {
            data.to = $form.find('#to').val();

            if (!data.to) {
                $error.text("Missing recipient");
                return;
            }
        } else if ($form.attr('data-conversation')) {
            data.pm_id = $form.attr('data-conversation');
        } else {
            return;
        }

        if (!data.body) {
            $error.text("Missing body");
            return;
        }

        var uri = '/files/ajax/inbox.php?send';
        $.post(uri, data, function(data) {
            if (data.status) {
                if (data.message) {
                    //Add message to conversation
                    data.seen = true;
                    data.img = "";
                    var $msg = $(messagesTmpl).tmpl(data);
                    $msg.hide();
                    $form.closest('li').before($msg);
                    $msg.slideDown(function() {
                        $form.closest('.scroll').mCustomScrollbar("scrollTo", "bottom");
                    });

                    //Clear reply textarea
                    $form.find('textarea').val('');

                    //Update conversation list
                    var pm_id = $form.attr('data-conversation');
                    $item = $('#nav-extra-dropdown .messages ul li > a[data-conversation="'+pm_id+'"]').parent();
                    $item.detach();

                    $item.find('span.dark').html('<i class="icon-reply"></i> ' + data.message);

                    var date = new Date();
                    $item.find('time').attr('datetime', date.toISOString()).text('secs');

                    $item.prependTo($('#nav-extra-dropdown .messages ul'));
                } else {
                    $sent = $('<div class="center empty fill"><i class="icon-ok-sign icon-4x"></i>Message Sent</div>').hide();
                    $form.replaceWith($sent);
                    $sent.fadeIn();
                }
            } else
                $error.text("Error sending message");
        }, 'json');
    });

    $('body').on('click', '.messages-new', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var to = $(this).attr('data-to');

        var composeHTML = $('<div>', {class: "compose"});

        composeHTML.append(composeForm);
        $('<a>', {text: "Full View", class: "more", href: "/inbox/compose?to="+to}).appendTo(composeHTML);

        dropdown.html(composeHTML).slideDown(200);

        $('#nav-extra-dropdown .suggest').val(to).autosuggest();

        $('#nav-extra-dropdown textarea').focus();

        icons.removeClass('active active-events active-pm');
        bindCloseNotifications();
    });

    function bindCloseNotifications() {
        $(document).bind('click.extra-hide', function(e) {
            if ($(e.target).closest('#nav-extra-dropdown').length != 0 && $(e.target).not('.nav-extra')) return true;
            hideNotifications();
        });
    }

    function hideNotifications() {
        dropdown.slideUp(200);
        icons.removeClass('active active-events active-pm');
        $(document).unbind('click.extra-hide');        
    }
});
