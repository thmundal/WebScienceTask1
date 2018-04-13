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
