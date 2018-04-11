var autoscroll_pause = false;
var chat;

function auto_scroll() {
    if(!autoscroll_pause) {
        $(".js-chat-content").scrollTop($(".js-chat-content").prop("scrollHeight"));
    }
}
$(function() {
    var user_id = $("meta[name=user]").attr("value");

    if(typeof io != "undefined") {
        chat = Chat.Init(user_id);
        chat.on("connect", function(data) {
            this.on("handles-received", (data) => {
                $("#chatBarWrapper").css("visibility", "visible");
                for(var i in data) {
                    var partner = (data[i].a==user_id?data[i].b:data[i].a);
                    var handle = new ChatHandle(data[i], this.connection, partner, user_id);
                    chat.addHandle(handle);
                }

                $(".js-chat-view-container").each(function() {
                    var h = $(this).data("handle");
                    var handle = chat.getHandleByID(h);

                    if(handle.view === null) {
                        $(this).append(handle.createView());
                    }

                    if(h == "") {
                        console.log("* View has no handle defined, finding based on partner");
                        var p = $(this).data("partner");
                        for(var i in chat.handles) {
                            if(chat.handles[i].attributes.a == p || chat.handles[i].attributes.b == p) {
                                $(this).append(chat.handles[i].createView());
                                $(this).data("handle", chat.handles[i].attributes.id);
                            }
                        }
                    }
                });
            });

            this.getChatHandles();
        });

        chat.connect("munso.no:3000");


        $(".js-chat-view-container").each(function() {
            if($(this).data("handle") == "") {
                chat.createHandle(user_id, $(this).data("partner"));
            }
        });

    }
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
