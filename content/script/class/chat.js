function Chat() {
    this.handle = null;
    this.output = null;
    this.input = null;
}

Chat.Init = function(handle, input, output) {
    var c = new Chat();
    c.handle = handle;
    c.output = output;
    c.input = input;
}

Chat.prototype.setHandle = function(handle) {
    this.handle = handle;
}

Chat.prototype.displayMessage = function(chatMessage) {
    chatMessage.output(this.output);
}
/* ------------------------------------------------------- */

function ChatHandle(data) {
    this.attributes = data;
    this.messages = [];
    this.message_buffer = [];
    this.socket = null;
}

ChatHandle.prototype.pollMessage = function(cb) {
    Api.post("chat-handle-poll", this.attributes, function(response) {
        cb.call(this, response);
    });
}

ChatHandle.prototype.sendMessage = function(message, cb) {
    Api.post("chat-handle-receive", {handle: this.attributes, message: message}, function(response) {
        cb.call(this, response);
    });
}

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

        this.message_buffer.splice(i, 1);
        auto_scroll();
    }
}

function ChatMessage(data) {
    this.attributes = data;
    this.displayed = false;
}

ChatMessage.prototype.output = function(container) {
    $(container).append($("<div>").html(this.attributes.sender_name+": "+this.attributes.message));
}
