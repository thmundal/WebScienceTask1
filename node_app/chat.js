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
            sql: "SELECT chat_messages.id, chat_messages.chat_handle, chat_messages.message, chat_messages.viewed, profiles.first_name as sender FROM chat_messages, profiles WHERE chat_messages.chat_handle=? AND profiles.user=chat_messages.sender;",
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
            values: [this.attributes.chat_handle, this.attributes.sender, this.attributes.message, this.attributes.viewed]
        }, (err, result, fields) => {
            if(err) {
                console.log(err);
            }

            this.attributes.id = result.insertId;
            if(typeof cb == "function") {
                cb.call(this);
            }
        });

        return this;
    }
}

var sessions = {};

var Static = {
    findHandle: function(a, b, cb) {
        connection.query({
            sql: "SELECT id FROM chat_handles WHERE (a=? AND b=?) XOR (a=? AND b=?) LIMIT 1;",
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

    // getPartnerSocket: function(session, handle) {
    //     var partner = -1;
    //     var user_id = user_data[session];
    //     if(handle.attributes.a == user_id) {
    //         partner = handle.attributes.b;
    //     } else {
    //         partner = handle.attributes.a;
    //     }
    //
    //     for(var i in user_data) {
    //         if(user_data[i] == partner && typeof sessions[i] !== "undefined") {
    //             return sessions[i].socket;
    //         }
    //     }
    //
    //     return false;
    // },

    getUserSocket: function(user_id) {
        console.log("Searching user socket for user ", user_id);
        for(var i in sessions) {
            if(i == user_id) {
                return sessions[i].socket;
            }
        }

        return null;
    },

    getSession: function(user_id) {
        if(sessions.hasOwnProperty(user_id)) {
            console.log("session found for ", user_id);
            return sessions[user_id];
        }

        return false;
    },

    setSessionPartner: function(user_id, partner) {
        sessions[user_id].partner = partner;
    },

    createSession: function(user_id, socket) {
        console.log("Creating session with id ", user_id);
        sessions[user_id] = { socket: socket };
        return sessions[user_id];
    },

    endSession: function(user_id) {
        delete sessions[user_id];
    },

    getUserName: function(user_id, cb) {
        connection.query({
            sql:"SELECT first_name FROM profiles WHERE user=?;",
            values:[user_id]
        }, (err, result, fields) => {
            if(result.length > 0) {
                cb.call(result[0].first_name);
            }
        })
    }
}

module.exports = {handle: Handle, static: Static, message: ChatMessage, sessions: sessions};
