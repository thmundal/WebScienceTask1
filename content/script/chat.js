var autoscroll_pause = false;

function auto_scroll() {
    if(!autoscroll_pause) {
        $(".js-chat-content").scrollTop($(".js-chat-content").prop("scrollHeight"));
    }
}
$(function() {
    Api.post("chat-session", {partner: queryParams()["user"]}, function(response) {
        if(response.body == null) {
            return;
        }


        handle = new ChatHandle(response.body);

        var poll_interval = 500;
        chat_poller = setInterval(function() {
            handle.pollMessage(function(response) {
                for(var i in response.body) {
                    handle.addMessage(new ChatMessage(response.body[i]));
                }

                handle.flushMessages(".js-chat-content");
            })}, poll_interval);

        var scroll_interval = 1000;
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
                var input = $(this)
                handle.sendMessage($(this).val(), function(response) {
                    handle.addMessage(new ChatMessage(response.body.message));
                    autoscroll_pause = false;
                    input.val("");
                });
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
            $(container).append($("<div>").html(this.messages[i].attributes.sender+": "+this.messages[i].attributes.message));
            this.messages[i].displayed = true;
            auto_scroll();
        }
    }
}

function ChatMessage(data) {
    this.attributes = data;
    this.displayed = false;
}
