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
    password: "yuRoKXc2bufyfCBy"
});

Chat = require("./chat");

connection.connect(function(err) {
    if(err) throw err;

    Chat.static.findHandle(1, 2, function() {

    });

    io.on('connection', function(socket){
        var session;
        var handle;
        console.log('a user connected');

        memcached.get("usn:php:user", function(err, data) {
            if(err) {
                console.log(err);
            } else {
                user_data = JSON.parse(data);
                console.log(user_data);
            }
        });

        socket.on("request-session", function(data) {
            session = Chat.static.getSession(data.user_id);
            if(Chat.static.validateUser(data.session)) {
                if(!session) {
                    Chat.static.createSession(data.user_id, socket);
                }
                
                session.socket = socket;

                console.log("session request received, data:", data);

                Chat.static.findHandle(user_data[data.session], data.partner, function(result) {
                    var partner = Chat.static.getUserSocket(data.partner);
                    var handle_data = data;
                    //Chat.static.setSessionPartner(session, partner);

                    console.log("Handle identified. Partner is: ", data.partner);
                    handle = this;

                    this.getMessages(messages => {
                        socket.emit("receive-session", {handle: this.attributes, messages: messages});
                    });

                    socket.on("send-message", data => {
                        var message = new Chat.message(data.message);
                        this.addMessage(message, () => {
                            Chat.static.getUserName(message.attributes.sender, function() {
                                message.attributes.sender = this;
                                socket.emit("receive-message", message.attributes);

                                partner = Chat.static.getUserSocket(handle_data.partner);
                                if(partner !== null) {
                                    console.log("partner socket id:", partner.id);
                                    partner.emit("receive-message", message.attributes);
                                } else {
                                    console.log("Partner not found. Partner id: ", handle_data.partner);
                                }
                            })
                        });
                    });
                });
            } else {
                console.log("user is not valid");
                console.log("user data:");
                console.log(user_data);
            }
        });

        socket.on("disconnect", function() {

        });
    });
});

http.listen(3000, function(){
  console.log('listening on *:3000');
});
