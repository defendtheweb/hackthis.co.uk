$(function() {
    if (socket) {
        var username = $('body').attr('data-username');
        var key = $('body').attr('data-key');

        /* CHAT */
        var chat_topic = 'Global chat';
        var chat_names = {};
        var connected = false,
            reconnect = false;

        function connectChat() {
            if (connected)
                return;

            connected = true;

            if (reconnect !== true)
                socket.emit('chat_register', { nick: username, key: key });
            $chat.addClass('connected');

            // Keep them connected for another day
            createCookie("chat-connected", "true", 1);
        }

        function disconnectChat() {
            if (!connected)
                return;

            reconnect = true;
            connected = false;
            socket.emit('disconnect');
            $chat.removeClass('connected');

            $chat.removeClass('show');
            createCookie("chat", "closed");

            // Keep them connected for another day
            createCookie("chat-connected", "false", 1);

            // Remove old messages 
            $chat.find('.chat-container ul li').remove();

            $chat.find('.chat-bar-title .topic').text('Global chat');
        }

        if (socket) {
            var connected = false;
            var unread = 0;
            var chat_main = $('#chat-main').length;

            if (!chat_main) {
                chat_bar = '   <div id="chat-bar">\
                                    <div class="chat-bar-title">\
                                        <i class="icon-off right"></i>\
                                        <span class="unread hide">0</span>\
                                        <span class="topic">Global chat</span>\
                                    </div>\
                                    <div class="chat-container scroll">\
                                        <div class="connect-msg">\
                                            Direct requests for answers will not be tollerated with in the chat (or anywhere else on the site). Do NOT give any personal details to ANY user!!<br/><br/><br/>\
                                            <a href="#" class="button chat-connect">Connect to IRC</a>\
                                        </div>\
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
                $chat.find('.icon-off').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    disconnectChat();
                });

            } else {
                $chat = $('#chat-main');

                connectChat();
            }$chat.find('.chat-bar-title .topic').text(chat_topic);

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

            // $chat.find('.chat-bar-title .icon-new-tab').on('click', function(e) {
            //     e.stopPropagation();
            //     $chat.removeClass('show');
            //     createCookie("chat", "closed");
            //     window.open('/chat.php', 'hackthis-chat', "height=400, width=600, scrollbars=no");
            // });

            $chat.find('input').keypress(function(event) {
                if (event.keyCode == 13 && connected) {
                    msg = $(this).val();
                    $(this).val('');

                    if (msg.length == 0)
                        return false;

                    if (msg.substring(0, 1) == "/" && msg.substring(0, 4) != "/me ") {
                        renderMsg({info: 'command'});
                        return false;
                    }

                    socket.emit('chat', { msg: msg });
                    
                    if (msg.substring(0, 4) != "/me ")
                        renderMsg({nick: username, msg: msg});
                    else
                        renderMsg({info: 'action', nick: username, msg: msg.substring(4)});
                }
            });

            socket.on('chat', function (data) {
                if (data instanceof Array) {
                    for (var i = 0; i < data.length; i++) {
                        if (!data[i].info || data[i].info == 'names')
                            renderMsg(data[i]);
                    }
                } else {
                    if (data.info === 'registered') {
                        // console.log(data);
                    } else if (data.info === 'topic') {
                        chat_topic = data.topic;
                        $chat.find('.chat-bar-title .topic').text(chat_topic);
                        // $chat.find('.chat-bar-title .topic').text(chat_topic + ' [' + Object.keys(chat_names).length + ' online]');
                    } else if (data.info === 'names') {
                        chat_names = data.names;
                        renderMsg(data);
                        $chat.find('.chat-bar-title .topic').text(chat_topic);
                        // $chat.find('.chat-bar-title .topic').text(chat_topic + ' [' + Object.keys(chat_names).length + ' online]');

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
                if (!connected)
                    return false;

                if (data.info) {
                    if (data.info == 'join')
                        data = $('<li>', {class: 'info'}).text('* '+data.nick+' has joined '+data.chan);
                    else if (data.info == 'names') {
                        var online = $('<span/>');
                        $.each(data.names, function(key, val) {
                            c = stringToColour(key);
                            span = $('<span/>', {style: 'color: '+c}).text(val+key);
                            online.append(span).append(' ');
                        });
                        data = $('<li>', {class: 'info'}).text('* Users online: ').append(online);
                    } else if (data.info == 'action') {
                        data = $('<li>', {class: 'info'}).text('* ' + data.nick + ' ' + data.msg);
                    } else if (data.info == 'command') {
                        data = $('<li>', {class: 'info'}).text('* This command is not yet supported');
                    } else { 
                        data = $('<li>', {class: 'info'}).text('* '+data.nick+' has left');
                    }
                    $chat.find('.chat-container ul').append(data);
                } else {
                    // Get users colour
                    c = stringToColour(data.nick);

                    span = $('<span/>', {style: 'color: '+c}).text(data.nick + ' ');
                    data = $('<li>').text(data.msg).prepend(span);
                    $chat.find('.chat-container ul').append(data);
                }

                $chat.find('.scroll').mCustomScrollbar("scrollTo", "bottom");
            }

            var stringToColour = function(str) {
                for (var i = 0, hash = 0; i < str.length; hash = str.charCodeAt(i++) + ((hash << 5) - hash));
                for (var i = 0, colour = "#"; i < 3; colour += ("00" + ((hash >> i++ * 8) & 0xFF).toString(16)).slice(-2));
                return colour;
            }
        }
    }
});