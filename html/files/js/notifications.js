$(function() {
    var favcounter = new FavCounter();
    var counter_chat = 0;
    var counter_notifications = 0;

    var username = $('body').attr('data-username');
    var key = $('body').attr('data-key');

    var socket = null;
    if (typeof io !== 'undefined') {
        // socket = io.connect('http://192.168.1.66:8080/');
        socket = io.connect('http://hackthis.co.uk:8080/');
    }

    var feedTmpl = '<tmpl>'+
    '    <li>'+
    '      <div class="col span_18">'+
    '        {{if type == "join"}}'+
    '            <i class="icon-user"></i><a href="/user/${username}">${username}</a>'+
    '        {{else type == "friend"}}'+
    '            <i class="icon-addfriend"></i><a href="/user/${username}">${username}</a> <span class="dark">and</span> <a href="/user/${username_2}">${username_2}</a>'+
    '        {{else type == "medal"}}'+
    '            <i class="icon-trophy colour-${colour}"></i><a href="/user/${username}">${username}</a> <span class="dark">awarded</span> <a href="/settings/medals.php#${label.toLowerCase()}">${label}</a>'+
    '        {{else type == "comment"}}'+
    '            <i class="icon-comments"></i><a href="${uri}">${title}</a> <span class="dark">by</span> <a href="/user/${username}">${username}</a>'+
    '        {{else type == "forum_post"}}'+
    '            <i class="icon-chat"></i><a href="${uri}">${title}</a> <span class="dark">by</span> <a href="/user/${username}">${username}</a>'+
    '        {{else type == "favourite"}}'+
    '            <i class="icon-heart"></i><a href="${uri}">${title}</a> <span class="dark">by</span> <a href="/user/${username}">${username}</a>'+
    '        {{else type == "article"}}'+
    '            <i class="icon-books"></i><a href="${uri}">${title}</a>'+
    '        {{else type == "news"}}'+
    '            <i class="icon-article"></i><a href="${uri}">${title}</a>'+
    '        {{else type == "level"}}'+
    '            <i class="icon-good"></i><a href="/user/${username}">${username}</a> <span class="dark">·</span> <a href="${uri}">${title}</a>'+
    '        {{/if}}'+
    '        </div>'+
    '        <div class="col span_6 time right"><time class="short" datetime="${timestamp}"></time></div>'+
    '    </li>'+
    '</tmpl>';

    /* ACTUAL STUFF */
    if (socket) {
        socket.on('feed', function (data) {
            var item = $(feedTmpl).tmpl(data);

            if ($('sidebar .feed ul').length) {
                item.hide().prependTo($('sidebar .feed ul')).slideDown();
            } else {
                var html = $('<ul>').append(item);
                $('sidebar .feed .feed_loading').replaceWith(html);
            }
        });
    } else {
        $('sidebar .feed .feed_loading').html('<strong>Feed offline</strong>');
    }







    /* CHAT */
    var chat_topic = 'Global chat';
    var chat_names = {};

    function connectChat() {
        if (connected)
            return;

        connected = true;
        socket.emit('chat_register', { nick: username, key: key });
        $chat.addClass('connected');

        // Keep them connected for another day
        createCookie("chat-connected", "true", 1);
    }

    if (socket) {
        var connected = false;
        var unread = 0;
        var chat_main = $('#chat-main').length;

        if (!chat_main) {
            chat_bar = '   <div id="chat-bar">\
                                <div class="chat-bar-title">\
                                    <i class="mobile-hide right icon-new-tab"></i>\
                                    <span class="unread hide">0</span>\
                                    <span class="topic">Global chat</span>\
                                </div>\
                                <div class="chat-container scroll">\
                                    <a href="#" class="left button chat-connect">Connect</a>\
                                    <ul>\
                                    </ul>\
                                </div>\
                                <input type="text"/>\
                            </div>';

            $('body').append(chat_bar);

            $chat = $('#chat-bar');

            // Is chat open
            result = new RegExp('(?:^|; )chat=([^;]*)').exec(document.cookie);
            if (result && result[1] == 'open') {
                $chat.addClass('show');
            }
            result = new RegExp('(?:^|; )chat-connected=([^;]*)').exec(document.cookie);
            if (result && result[1] == 'true') {
                connectChat();
            }

            $chat.find('.chat-connect').on('click', function(e) {
                e.preventDefault();
                connectChat();
            });

        } else {
            $chat = $('#chat-main');

            connectChat();
        }

        $unread = $chat.find('.unread');
        $chat.find('.scroll').mCustomScrollbar();

        $chat.find('.chat-bar-title').on('click', function(e) {
            e.stopPropagation();

            if ($chat.hasClass('show')) {
                $chat.removeClass('show');
                createCookie("chat", "closed");
            } else {
                $chat.addClass('show');
                createCookie("chat", "open");
                unread = 0;
                $unread.text(0).addClass('hide');

                counter_chat = unread;
                favcounter.set(counter_notifications + counter_chat);
            }
        });

        $chat.find('.chat-bar-title .icon-new-tab').on('click', function(e) {
            e.stopPropagation();
            $chat.removeClass('show');
            createCookie("chat", "closed");
            window.open('/chat.php', 'hackthis-chat', "height=400, width=600, scrollbars=no");
        });

        $chat.find('input').keypress(function(event) {
            if (event.keyCode == 13) {
                msg = $(this).val();
                socket.emit('chat', { msg: msg });
                $(this).val('');
                
                renderMsg({nick: username, msg: msg});
            }
        });

        socket.on('chat', function (data) {
            if (data instanceof Array) {
                for (var i = 0; i < data.length; i++) {
                    renderMsg(data[i]);
                }
            } else {
                if (data.info === 'registered') {

                } else if (data.info === 'topic') {
                    chat_topic = data.topic;
                    $chat.find('.chat-bar-title .topic').text(chat_topic + ' [' + Object.keys(chat_names).length + ' online]');
                } else if (data.info === 'names') {
                    chat_names = data.names;
                    renderMsg(data);
                    $chat.find('.chat-bar-title .topic').text(chat_topic + ' [' + Object.keys(chat_names).length + ' online]');

                    if (chat_main) {
                        $.each(data.names, function(key, val) {
                            c = stringToColour(key);
                            span = $('<span/>', {style: 'color: '+c}).text(val + key);
                            data = $('<li>').text(data.msg).prepend(span);
                            $chat.find('.chat-names ul').append(data);
                        });
                    }

                } else {
                    renderMsg(data);

                    if (!data.info) {                                
                        // Unread message
                        if (!$chat.hasClass('show')) {
                            unread++;
                            $unread.text(unread).removeClass('hide');

                            counter_chat = unread;
                            favcounter.set(counter_notifications + counter_chat);
                        }
                    }
                }
            }
        });


        function renderMsg(data) {
            if (data.info) {
                if (data.info == 'join')
                    data = $('<li>', {class: 'info'}).text('* '+data.nick+' has joined '+data.chan);
                else if (data.info == 'names') {
                    var online = '';
                    $.each(data.names, function(key, val) {
                        online += val+key+', ';
                    });
                    data = $('<li>', {class: 'info'}).text('* Users online: ' + online.substring(0, online.length-2));
                }
                else
                    data = $('<li>', {class: 'info'}).text('* '+data.nick+' has left');
                $chat.find('.chat-container ul').append(data);
            } else {
                // Get users colour
                c = stringToColour(data.nick);

                span = $('<span/>', {style: 'color: '+c}).text(data.nick + ' ');
                data = $('<li>').text(data.msg).prepend(span);
                $chat.find('.chat-container ul').append(data);
            }

            $chat.find('.scroll').mCustomScrollbar("scrollTo", "li:nth-last-child(2)");
        }

        var stringToColour = function(str) {
            for (var i = 0, hash = 0; i < str.length; hash = str.charCodeAt(i++) + ((hash << 5) - hash));
            for (var i = 0, colour = "#"; i < 3; colour += ("00" + ((hash >> i++ * 8) & 0xFF).toString(16)).slice(-2));
            return colour;
        }
    }




    /* NOTIFICATIONS */
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
            //     if ($('sidebar .feed ul').length) {
            //         items.hide().prependTo($('sidebar .feed ul')).slideDown();
            //     } else {
            //         var html = $('<ul>').append(items);
            //         $('sidebar .feed .feed_loading').replaceWith(html);
            //     }
            // }
        }, 'json');

        setTimeout(updateTimes, 10000);
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
            var data = data.items;
            if (data.length || data.items.length) {
                if (parent.hasClass('active-events')) {
                    var items = $(notificationsTmpl).tmpl(data.items);

                    var more = $("<li>", {class: "more"});
                    $('<a>', {text: "View More", href: "/alerts.php"}).appendTo(more); 
                    var html = $('<ul>').append(items).append(more);
                } else {
                    var items = $(inboxTmpl).tmpl(data);

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
        uri += $(this).attr('data-uid');

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