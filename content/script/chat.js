var autoscroll_pause = false;

function auto_scroll() {
    if(!autoscroll_pause) {
        $(".js-chat-content").scrollTop($(".js-chat-content").prop("scrollHeight"));
    }
}
$(function() {
    var user_id = $("meta[name=user]").attr("value");

    if(typeof io != "undefined") {
        //Api.post("chat-session", {partner: queryParams()["user"]}, function(response) {
        var socket = io("munso.no:3000");

        if(typeof queryParams()["user"] !== "undefined") {
            socket.emit("request-session", {session: getCookie("PHPSESSID"), partner: queryParams()["user"], user_id: user_id});
        }

        socket.on("receive-session", function(data) {
            console.log(data);
        });
    } else {
        $(".js-chat-content").append($("<div>").html("Node.js server is not running. Contact admin"));
        console.error("Node.js server is not running. Contact admin");
    }

//    Api.post("chat-session", {partner: queryParams()["user"]}, function(response) {
    socket.on("receive-session", function(response) {
        console.log(response, "session received");

        handle = new ChatHandle(response.handle);

        for(var i in response.messages) {
            handle.addMessage(new ChatMessage(response.messages[i]));
        }
        handle.flushMessages(".js-chat-content");

        socket.on("receive-message", function(data) {
            console.log("message received", data);
            if(data.sender == queryParams()["user"]) {
                // This method is probably not needed anymore
                handle.addMessage(new ChatMessage(data));
                handle.flushMessages(".js-chat-content");
            }
        });

        // var poll_interval = 500;
        // chat_poller = setInterval(function() {
        //     handle.pollMessage(function(response) {
        //         for(var i in response.body) {
        //             handle.addMessage(new ChatMessage(response.body[i]));
        //         }
        //
        //         handle.flushMessages(".js-chat-content");
        //     })}, poll_interval);
        //
        // clearInterval(chat_poller);
        //var scroll_interval = 1000;
        //auto_scroller = setInterval(auto_scroll, scroll_interval);

        $(".js-chat-content").on("scroll", function() {
            var scroll_delta = $(".js-chat-content").prop("scrollHeight") - ($(".js-chat-content").height() + $(".js-chat-content").scrollTop());
            var scroll_limit = 15;  // Find this from line-height property?
            autoscroll_pause = false;

            if(scroll_delta > scroll_limit) {
                autoscroll_pause = true;
            }
        });

        $(".js-chat-input").on("keypress", function(event) {
            if(event.key == "Enter") {
                var input = $(this);
                var message = new ChatMessage({
                    message: input.val(),
                    sender: user_id,
                    viewed: 0,
                    chat_handle: handle.attributes.id
                });

                socket.emit("send-message", { handle: handle.attributes, message: message.attributes });
                //handle.addMessage(message);
                //handle.flushMessages(".js-chat-content");
                autoscroll_pause = false;
                input.val("");

                //handle.sendMessage($(this).val(), function(response) {
                //});
            }
        });
    });
});

function ChatHandle(data) {
    this.attributes = data;
    this.messages = [];
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
    if(!this.containsMessage(message))
        this.messages.push(message);
}

ChatHandle.prototype.flushMessages = function(container) {
    for(var i in this.messages) {
        if(!this.messages[i].displayed) {
            $(container).append($("<div>").html(this.messages[i].attributes.sender_name+": "+this.messages[i].attributes.message));
            this.messages[i].displayed = true;
            auto_scroll();
        }
    }
}

function ChatMessage(data) {
    this.attributes = data;
    this.displayed = false;
}
