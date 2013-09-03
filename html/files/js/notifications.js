$(function() {
    var username = $('body').attr('data-username');
    var key = $('body').attr('data-key');

    var socket = null;
    if (typeof io !== 'undefined') {
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

            console.log(item);

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
    if (socket) {
        socket.emit('chat_register', { nick: username, key: key });

        chat_bar = '   <div id="chat-bar">\
                            <div class="chat-bar-title">Global chat <i class="mobile-hide right icon-new-tab"></i></div>\
                            <div class="chat-container scroll">\
                                <ul>\
                                </ul>\
                            </div>\
                            <input type="text"/>\
                        </div>';

        $('body').append(chat_bar);
        $chat_bar = $('#chat-bar');
        $chat_bar.find('.scroll').mCustomScrollbar();

        // Is chat open
        result = new RegExp('(?:^|; )chat=([^;]*)').exec(document.cookie);
        if (result && result[1] == 'open')
            $chat_bar.addClass('show');

        $chat_bar.find('.chat-bar-title').on('click', function(e) {
            e.stopPropagation();

            if ($chat_bar.hasClass('show')) {
                $chat_bar.removeClass('show');
                createCookie("chat", "closed");
            } else {
                $chat_bar.addClass('show');
                createCookie("chat", "open");
            }
        });

        $chat_bar.find('input').keypress(function(event) {
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
                if (data.info === 'register') {
                    console.log(data);
                } else {
                    renderMsg(data);
                }
            }
        });


        function renderMsg(data) {
            if (data.info) {
                if (data.info == 'join')
                    data = $('<li>', {class: 'info'}).text('* '+data.nick+' has joined '+data.chan);
                else
                    data = $('<li>', {class: 'info'}).text('* '+data.nick+' has left');
                $chat_bar.find('.chat-container ul').append(data);
            } else {
                // Get users colour
                c = stringToColour(data.nick);

                span = $('<span/>', {style: 'color: '+c}).text(data.nick + ' ');
                data = $('<li>').text(data.msg).prepend(span);
                $chat_bar.find('.chat-container ul').append(data);
            }

            $chat_bar.find('.scroll').mCustomScrollbar("scrollTo", "li:nth-last-child(2)");
        }

        var stringToColour = function(str) {
            for (var i = 0, hash = 0; i < str.length; hash = str.charCodeAt(i++) + ((hash << 5) - hash));
            for (var i = 0, colour = "#"; i < 3; colour += ("00" + ((hash >> i++ * 8) & 0xFF).toString(16)).slice(-2));
            return colour;
        }
    }
});