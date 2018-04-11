var http = require('http').createServer((req, res) => {
    res.end();
});
var io = require('socket.io')(http);
var mysql = require('mysql');
var Memcached = require("memcached");
var memcached = new Memcached("localhost:11211");
var interactive = require("interactive");
interactive.start("NODE> ");

user_data = [];

connection = mysql.createConnection({
    host: "localhost",
    user: "usn",
    database: "usn",
    password: "yuRoKXc2bufyfCBy",
    multipleStatements:true
});

Chat = require("./chat");

connection.connect(function(err) {
    // Keep connection alive (HACK)
    setInterval(function () {
        connection.query('SELECT 1');
    }, 5000);

    if(err) throw err;

    io.on('connection', function(socket){
        var session;
        var handle;

        console.log('* A user connected from', socket.request.connection.remoteAddress);

        memcached.get("usn:php:user", function(err, data) {
            if(err) {
                console.log(err);
            } else {
                user_data = JSON.parse(data);
            }
        });

        socket.on("register-session", function(user_id) {
            console.log("* Registering session for user", user_id, "socket id:", socket.id);
            session = Chat.static.createSession(user_id, socket);
        });

        socket.on("request-handle-list", function(data) {
            Chat.static.getAllUserHandles(data.user_id, function(result) {
                console.log("* Serving chat handles...");
                socket.emit("receive-chat-handles", result);

                for(var i in result) {
                    var h = new Chat.handle(result[i]);
                    h.getMessages((result) => {
                        for(var j in result) {
                            socket.emit("receive-message-buffer", { message: result[j], handle:result[j].chat_handle} );
                        }
                    });
                }
            });
        });

        socket.on("request-user-profile", function(data) {
            console.log("* Serving user profile for user", data.user_id);
            var user_id = data.user_id;

            Chat.static.getUserProfile(data.user_id, (result) => {
                this.emit("receive-user-profile", result);
            });
        });

        socket.on("create-handle", function(data) {
            var a = data.a || 0;
            var b = data.b || 0;
            var g = data.g || 0;
            Chat.static.createChatHandle(a, b, g, (handle) => {
                this.emit("receive-chat-handles", [ handle.attributes ]);
            })
        });

        socket.on("send-message", function(data) {
            var handle = data.handle;
            var message = new Chat.message(data.message);
            var partner_id = data.partner_id;

            message.save(() => {
                var partner_socket = Chat.static.getUserSocket(partner_id);

                var response = {
                    message: message.attributes,
                    handle: handle.id
                }

                console.log("* Delivering messages...");

                if(partner_socket) {
                    partner_socket.emit("receive-message", response);
                } else {
                    console.log("+ Partner is not online, not able to deliver");
                }

                this.emit("receive-message", response);
            });
        });

        socket.on("disconnect", function() {
            if(session) {
                console.log("* User disconnected. Removing session for user", session.user_id);
                delete Chat.sessions[session.user_id];
            }
        });
    });
});

http.listen(3000, function(){
  console.log('listening on *:3000');
});
