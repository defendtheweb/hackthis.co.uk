var http = require('http');
var app = require('http').createServer(handler)
, io = require('socket.io').listen(app, { log: false })
//, io = require('socket.io').listen(app)
, url = require('url')
, qs = require('querystring');

var feed_log = [];

var _irc = require('irc');
var connections = [];
var irc_log = [];
var irc_clients = [];
var global_irc;

var irc_topic = "Global chat";
var irc_names = {};

app.listen(8080);


var api_key = 'PML758e0UW4oqT8js9vAg5SZY3w6JgkJ';

io.sockets.on('connection', function (socket) {
    //Send feed history
    socket.emit('feed', feed_log.slice(-10));

    socket.on('chat_register', function (data) {
        if (data.nick && data.key)
            connectIRC(socket, data.nick, data.key);
    });

    socket.on('disconnect', function() {
        disconnectSocket(socket);
    });
});

function handler(req, res) {
    if (req.method == 'POST') {
        var query = url.parse(req.url, true).query;
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
    io.sockets.emit('feed', data);
}



global_irc = new _irc.Client('irc.hackthis.co.uk', 'ChatBot', {
    userName: 'ChatBot',
    realName: 'ChatBot',
    port: 6697,
    secure: true,
    selfSigned: true,
    certExpired: true,
    channels: ['#nukeland']
});

global_irc.setMaxListeners(0);
global_irc.addListener('message', function (nick, chan, message) {
    irc_log.push({nick: nick, chan: chan, msg: message});
}).addListener('join', function (chan, nick, message) {
    irc_log.push({nick: nick, chan: chan, info: 'join'});
}).addListener('part', function (chan, nick, reason, message) {
    irc_log.push({nick: nick, info: 'part'});
}).addListener('quit', function (nick, reason, channels, message) {
    irc_log.push({nick: nick, info: 'part'});
}).addListener('topic', function (chan, topic, nick) {
    irc_topic = topic;
}).addListener('names', function (chan, names) {
    irc_names = names;
});


function connectIRC(socket, nick, key) {
    console.log(key + ' - New connection');
    socket.nick = nick;
    socket.key = key;

    //lookup existing connection
    if (socket.key in irc_clients) {
        irc_clients[socket.key].connections++;
        socket.irc = irc_clients[socket.key].client;
    } else {
        console.log(key + ' - Creating IRC connection');
        socket.irc = new _irc.Client('irc.hackthis.co.uk', nick, {
            userName: nick,
            realName: nick,
            channels: ['#nukeland']
        });

        irc_clients[socket.key] = {connections: 1, client: socket.irc};
    }

    // for (var i = 0; i < connections.length; i++) {
    //     if (connections[i].key == socket.key && connections[i].nick == socket.nick) {
    //         console.log('IRC user already active');
    //         socket.irc = connections[i].irc;
    //         break;
    //     }
    // }

    // if (!socket.irc) {
    //     console.log('Creating new IRC user');
    //     socket.irc = new _irc.Client('irc.hackthis.co.uk', nick, {
    //         userName: nick,
    //         realName: nick,
    //         channels: ['#nukeland']
    //     });
    // }

    //redefine handler
    socket.irc.setMaxListeners(0);
    socket.irc.addListener('message', function (nick, chan, message) {
        // Check for ctcp
        var action = message.match(/^\u0001ACTION (.*)\u0001$/);
        if (action) {
            socket.emit('chat', {nick: nick, chan: chan, msg: action[1], info: 'action'});
        } else {
            socket.emit('chat', {nick: nick, chan: chan, msg: message});
        }
    }).addListener('join', function (chan, nick, message) {
        socket.emit('chat', {nick: nick, chan: chan, msg: message, info: 'join'});
    }).addListener('part', function (chan, nick, reason, message) {
        socket.emit('chat', {nick: nick, chan: chan, msg: message, info: 'part'});
    }).addListener('quit', function (nick, reason, channels, message) {
        socket.emit('chat', {nick: nick, info: 'part'});
    }).addListener('registered', function (message) {
        socket.emit('chat', {info: 'registered'});
    }).addListener('topic', function (chan, topic, nick) {
        socket.emit('chat', {nick: nick, topic: topic, info: 'topic'});
    });

    socket.on('chat', function (data) {
        if (data.msg.substring(0, 4) == "/me ")
            socket.irc.action('#nukeland', data.msg.substring(4));
        else
            socket.irc.say('#nukeland', data.msg);
    });

    connections.push(socket);

    //Send history
    socket.emit('chat', irc_log.slice(-25));
    socket.emit('chat', {topic: irc_topic, info: 'topic'});
    socket.emit('chat', {names: irc_names, info: 'names'});
}

function disconnectSocket(socket) {
    key = socket.key;
    if (key in irc_clients) {
        console.log(key + ' - Client disconnected: ' + irc_clients[key].connections + ' connections');
    } else {
        console.log(key + ' - Client disconnected');
    }

    (function(key) {
        setTimeout(function(){disconnectIRC(key)}, 15000);
    })(key);
    /*setTimeout(function() {
        // console.log('Deleting connection...');

        // irc = socket.irc;
        // connections.splice(connections.indexOf(socket), 1);

        // if (!irc)
        //     return;

        // if (connections.length > 0) {
        //     var n = 0;
        //     connections.forEach(function(item) {
        //         if (item.irc === irc)
        //             n++;
        //     });

        //     if (n === 0) {
        //         console.log('Disconnecting from IRC');
        //         irc.disconnect();
        //     } else {
        //         console.log('IRC connection in use');
        //     }
        // } else {
        //     console.log('No other connections');
        //     irc.disconnect();
        // }            

        console.log(key + ' - Timeout');
        if (key in irc_clients) {
            irc_clients[key].connections--;
            console.log(key + ' - ' + irc_clients[key].connections + ' connections');
            
            if (irc_clients[key].connections == 0) {
                irc_clients[key].client.disconnect();
                delete irc_clients[key];
            }
        }
    }, 15000, key);*/

    connections.splice(connections.indexOf(socket), 1);
}

function disconnectIRC(key) {
    if (key in irc_clients) {
        irc_clients[key].connections--;
        console.log(key + ' - Timeout: ' + irc_clients[key].connections + ' connections');
        
        if (irc_clients[key].connections == 0) {
            irc_clients[key].client.disconnect();
            delete irc_clients[key];
        }
    } else {
        console.log(key + ' - Timeout');
    }
}