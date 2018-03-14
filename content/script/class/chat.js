function Chat(user_id) {
    this.handles = [];
    this.events = {};
    this.connection = null;
    this.user_id = user_id;
}

Chat.Init = function(user_id) {
    return new Chat(user_id);
}

Chat.prototype.connect = function(url, callback) {
    this.connection = io("munso.no:3000");

    this.connection.on("connect", (data) => {
        this.callEvent("connect", data);

        this.connection.emit("register-session", this.user_id);
    });

    this.connection.on("receive-chat-handles", (data) => { this.callEvent("handles-received", data )});

    this.connection.on("receive-message-buffer", (data) => {
        var handle = this.getHandleByID(data.handle);
        handle.addMessage(new ChatMessage(data.message));

        if(handle.view != null) {
            handle.flushMessages(handle.output);
        }
    });

    this.connection.on("receive-message", (data) => {
        var handle = this.getHandleByID(data.handle);

        if(handle) {
            handle.receiveMessage(new ChatMessage(data.message));
        } else {
            throw new Error("Cannot find handle with id ", data.handle);
        }
    });

    this.connection.on("disconnect", () => {
        console.info("Disconnected from server, removing handles");
    })
}

Chat.prototype.getChatHandles = function() {
    this.connection.emit("request-handle-list", { user_id: this.user_id });
}

Chat.prototype.addHandle = function(handle) {
    if(this.getHandleByID(handle.attributes.id) == false) {
        this.handles.push(handle);
        return true;
    } else {
        console.info("Handle already registered");
    }

    return false;
}

Chat.prototype.createHandle = function(a, b, g) {

}

Chat.prototype.getHandleByID = function(id) {
    for(var i in this.handles) {
        if(this.handles[i].attributes.id == id) {
            return this.handles[i];
        }
    }

    return false;
}

Chat.prototype.displayMessage = function(chatMessage) {
    chatMessage.output(this.output);
}

Chat.prototype.on = function(event, callback) {
    if(!this.events.hasOwnProperty(event)) {
        this.events[event] = [];
    }

    var has_callback = false;

    for(var i in this.events[event]) {
        var cbstring = this.events[event][i].toString();
        if(cbstring == callback.toString()) {
            has_callback = true;
            break;
        }
    }

    if(!has_callback)
        this.events[event].push(callback);
    else
        console.info("Callback already registered for", event);
}

Chat.prototype.callEvent = function(event, params) {
    console.log("Should call event", event, this.events);
    if(this.events.hasOwnProperty(event)) {
        for(var i in this.events[event]) {
            this.events[event][i].call(this, params);
        }
    }
}

Chat.prototype.clearEvents = function() {
    this.events = {};
}

/* ------------------------------------------------------- */

function ChatHandle(data, socket, partner_id, user_id, input, output) {
    this.attributes = data;
    this.partner_id = partner_id || null;
    this.user_id = user_id || null;
    this.output = output || null;
    this.input = input || null;
    this.socket = socket || null;

    this.messages = [];
    this.message_buffer = [];
    this.events = {};
    this.view = null;


    this.on("received-message", (message) => {
        if(this.view == null) {
            $("#chatBarWrapper").append(this.createView());
        }
    });
}

ChatHandle.prototype.start = function(socket) {

}

ChatHandle.prototype.on = function(event, callback) {
    if(!this.events.hasOwnProperty(event)) {
        this.events[event] = [];
    }

    var has_callback = false;

    for(var i in this.events[event]) {
        var cbstring = this.events[event][i].toString();

        if(cbstring == callback.toString()) {
            has_callback = true;
            break;
        }
    }

    if(!has_callback)
        this.events[event].push(callback);
    else
        console.info("Callback already registered for", event);
}

ChatHandle.prototype.callEvent = function(event, params) {
    if(this.events.hasOwnProperty(event)) {
        for(var i in this.events[event]) {
            this.events[event][i].call(this, params);
        }
    } else {
        console.info("No event registered for ", event);
    }
}

ChatHandle.prototype.clearEvents = function() {
    this.events = {};
}

ChatHandle.prototype.createView = function() {
    if(this.view === null) {
        console.log("Creating new view");
        var view = $("<div>").addClass("js-chat-view-container")
            .append($("<div>").addClass("js-chat-output").addClass("chat-content"))
            .append($("<input>").attr({type:"text"}).addClass("js-chat-input").addClass("chat-input"));

        this.input = view.find(".js-chat-input");
        this.output = view.find(".js-chat-output");
        this.view = view;
        this.setViewController();
    }

    this.flushMessages(this.output);
    return this.view;
}

ChatHandle.prototype.setViewController = function() {
    var autoscroll_pause = false;
    auto_scroll = () => {
        if(!autoscroll_pause) {
            this.output.scrollTop(this.output.prop("scrollHeight"));
        }
    }

    var scroll_interval = 1000;
    auto_scroller = setInterval(auto_scroll, scroll_interval);

    this.input.on("keypress", (e) => {
        if(e.key == "Enter") {
            e.preventDefault();

            this.sendMessage(this.input.val());
            this.input.val("");

            autoscroll_pause = false;
            return false;
        }
    });

    this.on("received-message", (message) => {
        // this.output.append($("<span>").addClass("js-chat-message").html(message));
        // message.output(this.output);
        this.flushMessages(this.output);
        console.log("received a message");
    });

    this.output.on("scroll", () => {
        var scroll_delta = this.output.prop("scrollHeight") - (this.output.height() + this.output.scrollTop());
        var scroll_limit = 15;  // Find this from line-height property?
        autoscroll_pause = false;

        if(scroll_delta > scroll_limit) {
            autoscroll_pause = true;
        }
    });
}

ChatHandle.prototype.receiveMessage = function(message) {
    if(message instanceof ChatMessage) {
        this.message_buffer.push(message);
        this.callEvent("received-message", message);
    } else {
        throw new Error("message is wrong type, ChatMessage expected, got: ", message.constructor.name);
    }
}

ChatHandle.prototype.sendMessage = function(msg) {
    var message = new ChatMessage({
        sender: this.user_id,
        message: msg,
        chat_handle: this.attributes.id
    });

    this.socket.emit("send-message", {
        handle: this.attributes,
        message: message.attributes,
        partner_id: this.partner_id
    });
}
/*-------------------------------------------------------*/

// ChatHandle.prototype.pollMessage = function(cb) {
//     Api.post("chat-handle-poll", this.attributes, function(response) {
//         cb.call(this, response);
//     });
// }
//
// ChatHandle.prototype.sendMessage = function(message, cb) {
//     Api.post("chat-handle-receive", {handle: this.attributes, message: message}, function(response) {
//         cb.call(this, response);
//     });
// }

ChatHandle.prototype.containsMessage = function(message) {
    for(var i in this.messages) {
        if(this.messages[i].attributes.id == message.attributes.id) {
            return true;
        }
    }

    return false;
}

ChatHandle.prototype.addMessage = function(message) {
    this.message_buffer.push(message);
}

ChatHandle.prototype.flushMessages = function(container) {
    for(var i in this.message_buffer) {
        var message = this.message_buffer[i];
        this.messages.push(message);
        message.output(container);
        message.displayed = true;
    }
    this.message_buffer = [];
}

function ChatMessage(data) {
    this.attributes = data;
    this.displayed = false;
}

ChatMessage.prototype.output = function(container) {
    $(container).append($("<div>").html(this.attributes.sender_name+": "+this.attributes.message));
}
