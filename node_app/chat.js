class Handle {
    constructor(data) {
        this.attributes = data;
        this.messages = [];
    }

    addMessage(message, cb) {
        this.messages.push(message.save(cb));
    }

    getMessages(cb) {
        connection.query({
            sql: "SELECT chat.*, CONCAT(profile.first_name, ' ', profile.last_name) as sender_name FROM chat_messages as chat JOIN profiles as profile ON chat.sender = profile.user WHERE chat.chat_handle = ?",
            values: [ this.attributes.id ]
        }, (err, result, fields) => {
            cb.call(this, result);
        });
    }
}

class ChatMessage {
    constructor(data) {
        this.attributes = data;
    }

    save(cb) {
        connection.query({
            sql: "INSERT INTO chat_messages SET chat_handle=?, sender=?, message=?, viewed=0;",
            values: [this.attributes.chat_handle, this.attributes.sender, this.attributes.message, this.attributes.viewed, this.attributes.sender]
        }, (err, result, fields) => {
            if(err) {
                console.log(err);
            }

            this.attributes.id = result.insertId;
            Static.getUserName(this.attributes.sender, (name) => {
                this.attributes.sender_name = name;

                if(typeof cb == "function") {
                    cb.call(this);
                }
            })
        });

        return this;
    }
}

var sessions = {};

var Static = {
    findHandle: function(a, b, cb) {
        connection.query({
            sql: "SELECT id FROM chat_handles WHERE (a=? AND b=?) XOR (a=? AND b=?) LIMIT 1;",
            // sql:"SELECT handle.*, message.id as msg_id, message.message FROM chat_handles as handle INNER JOIN chat_messages as message WHERE message.chat_handle = handle.id AND handle.a=? OR handle.b=?;",
            values: [b,a,a,b]
        }, (error, result, fields) => {
            if(result.length > 0) {
                Static.loadHandle(result[0].id, function() {
                    cb.call(this);
                });
            } else {
                // Need to create handle here
                Static.createChatHandle(a, b, function(result) {
                    cb.call(this);
                });
            }
        });
    },

    getAllUserHandles: function(user_id, cb) {
        connection.query({
            sql:"SELECT * FROM chat_handles WHERE a=? OR b=?;",
            values:[user_id, user_id]
        }, (error, result, fields) => {
            cb.call(null, result);
        })
    },

    validateUser: function(session) {
        return user_data.hasOwnProperty(session);
    },

    loadHandle: function(id, cb) {
        connection.query({
            sql: "SELECT * FROM chat_handles WHERE id=? LIMIT 1;",
            values: [id]
        }, (error, result, fields) => {
            var handle = new Handle(result[0]);
            cb.call(handle);
        });
    },

    createChatHandle: function(a, b, cb) {
        connection.query({
            sql: "INSERT INTO chat_handles SET a = ?, b = ?",
            values: [a, b]
        }, function(err, result, fields) {
            if(!err) {
                var handle = new Handle({id: result.insertId, a: a, b: b, group: 0});
                cb.call(handle);
            } else {
                console.log(err);
            }
        });
    },

    getUserSocket: function(user_id) {
        for(var i in sessions) {
            if(i == user_id) {
                return sessions[i].socket;
            }
        }

        return null;
    },

    getSession: function(user_id) {
        if(sessions.hasOwnProperty(user_id)) {
            return sessions[user_id];
        }

        return false;
    },

    setSessionPartner: function(user_id, partner) {
        sessions[user_id].partner = partner;
    },

    createSession: function(user_id, socket) {
        sessions[user_id] = {
            socket: socket,
            user_id: user_id
        };
        return sessions[user_id];
    },

    endSession: function(user_id) {
        delete sessions[user_id];
    },

    getUserName: function(user_id, cb) {
        connection.query({
            sql:"SELECT CONCAT(first_name, ' ', last_name) as name FROM profiles WHERE user=?;",
            values:[user_id]
        }, (err, result, fields) => {
            if(result.length > 0) {
                cb.call(result[0].name, result[0].name);
            }
        })
    }
}

module.exports = {handle: Handle, static: Static, message: ChatMessage, sessions: sessions};
