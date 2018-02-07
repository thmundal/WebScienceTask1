Api = {
    get: function(url, cb) {
        $.get("api/" + url, function(response) {
            Api.handle.call(this, cb, response);
        });
    },

    post: function(url, data, cb) {
        $.post("api/" + url, data, function(response) {
            Api.handle.call(this, cb, response);
        });
    },

    handle: function(cb, response) {
        if(response.console != "") {
            console.log(JSON.parse(response.console));
        }

        if(response.error) {
            console.error(response.errmsg);
        }

        cb.call(this, response);
    }
}
