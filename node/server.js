var http = require('http');
var app = require('http').createServer(handler)
//, io = require('socket.io').listen(app, { log: false })
, io = require('socket.io').listen(app)
, url = require('url')
, qs = require('querystring');

var feed_log = [];

var _irc = require('irc');
var connections = [];
var irc_log = [];
var global_irc;


app.listen(8080);


var api_key = 'PML758e0UW4oqT8js9vAg5SZY3w6JgkJ';

var socket = null;
io.sockets.on('connection', function (connection) {
    socket = connection;

    //Send feed history
    socket.emit('feed', feed_log.slice(-10));

    socket.on('chat_register', function (data) {
        if (data.nick && data.key)
            connectIRC(socket, data.nick, data.key);
    });

    socket.on('disconnect', function() {
        disconnectIRC(socket);
    });
});

function handler(req, res) {
    if (req.method == 'POST') {
        var query = url.parse(req.url,true).query;
        if (query.api == api_key) {
            var body = '';
            req.on('data', function (data) {
                body += data;
            });
            req.on('end',function(){
                var POST =  qs.parse(body);
                console.log(POST);
                feed(POST);
                res.writeHead(200);
            });
        }
    }

    res.end();
}


function feed(data) {
    feed_log.push(data);
    if (socket) {
        socket.broadcast.emit('feed', data);
    }
}



global_irc = new _irc.Client('irc.hackthis.co.uk', 'NexBotv2', {
    channels: ['#nukeland']
});

global_irc.addListener('registered', function (message) {
    irc_log.push({ msg: message, info: 'regiser'});
}).addListener('message', function (nick, chan, message) {
    irc_log.push({nick: nick, chan: chan, msg: message});
}).addListener('join', function (chan, nick, message) {
    irc_log.push({nick: nick, chan: chan, info: 'join'});
}).addListener('part', function (chan, nick, reason, message) {
    irc_log.push({nick: nick, chan: chan, info: 'part'});
});


function connectIRC(socket, nick, key) {
    socket.nick = nick;
    socket.key = key;

    //lookup existing connection
    for (var i = 0; i < connections.length; i++) {
        if (connections[i].key == socket.key && connections[i].nick == socket.nick) {
            socket.irc = connections[i].irc;
            break;
        }
    }

    if (!socket.irc) {
        socket.irc = new _irc.Client('irc.hackthis.co.uk', nick, {
            channels: ['#nukeland']
        });
    }

    //redefine handler
    socket.irc.addListener('message', function (nick, chan, message) {
        socket.emit('chat', {nick: nick, chan: chan, msg: message});
    }).addListener('join', function (chan, nick, message) {
        socket.emit('chat', {nick: nick, chan: chan, msg: message, info: 'join'});
    }).addListener('part', function (chan, nick, reason, message) {
        socket.emit('chat', {nick: nick, chan: chan, msg: message, info: 'part'});
    });

    socket.on('chat', function (data) {
        socket.irc.say('#nukeland', data.msg);
    });

    connections.push(socket);

    //Send history
    socket.emit('chat', irc_log.slice(-25));
}

function disconnectIRC(socket) {
    setTimeout(function() {
        irc = socket.irc;
        connections.splice(connections.indexOf(socket), 1);

        if (!irc)
            return;

        if (connections.length > 0) {
            var n = 0;
            connections.forEach(function(item) {
                if (item.irc === irc)
                    n++;
            });

            if (n === 0)
                irc.disconnect();
        } else {
            irc.disconnect();
        }            
    }, 15000);
}