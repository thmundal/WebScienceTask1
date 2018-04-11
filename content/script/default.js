$(function() {
    $("form.validate, .js-register-form").on("submit", function() {
        var form = $(this);
        var valid = true;
        var errors = [];

        $(this).find("input").each(function() {
            if($(this).data("required") == "yes") {
                $(this).css("outline", "0");
            }

            if($(this).data("required") == "yes" && $(this).val() == "") {
                valid = false;
                errors.push($(this).attr("name"));
                $(this).css("outline", "1px solid #F00");
            }

            var match = $(this).data("matchto");
            if(match) {
                if($(form).find("input[name="+match+"]").val() != $(this).val()) {
                    valid = false;
                    errors.push($(this).attr("name"));
                    $(this).css("outline", "1px solid #F00");
                }
            }
        });

        if(!valid) {
            console.log("invalid input. Missing:", errors);
            $(".js-register-error").show().html("Registrering feilet, vennligst sjekk input");
            return false;
        }

        return true;
    });

    $(".js-user-search").on("keypress", function(e) {
        if(e.key == "Enter") {
            console.log("Enter pressed");

            var search_form = $("<form>").attr({
                action: "search",
                method: "post"
            }).append($("<input>").attr({
                type:"hidden",
                name:"keyword",
                value:$(this).val()
            }));

            $("body").append(search_form);
            search_form.trigger("submit");

            //$(this).val("");
        }
    });

    $(".js-file-upload").on("change", function() {
        var filelist = $(this).prop("files");

        var filereader = new FileReader();
        filereader.onload = function() {
            $(".js-profile-image-preview").attr("src", this.result);
        }
        filereader.readAsDataURL(filelist[0]);

    });

    $(".js-profile-form").on("submit", function() {
        console.log($(this).serialize());
        var profile_image = $(this).find(".js-file-upload");
        var filelist = profile_image.prop("files");
        var valid_mime = ["image/png", "image/jpg", "image/jpeg"];
        var valid = true;
        var errors = [];



        if(filelist.length > 0) {
            var file = filelist[0];

            if(valid_mime.indexOf(file.type) < 0) {
                valid = false;
                errors.push("Filformatet støttes ikke");
            }
        }

        if(!valid) {
            $(".alert").html(errors.join(",")).show();
        }

        return valid;
    });
});


function queryParams() {
    var p = location.search.split("?")[1];
    var d = {};

    if(typeof p != "undefined") {
        var split = p.split("&");

        for(var i in split) {
            var psplit = split[i].split("=");
            d[psplit[0]] = psplit[1];
        }
    }

    return d;
}

function getCookie(key) {
    var cookies = document.cookie.split("; ");

    for(var i in cookies) {
        var split = cookies[i].split("=");
        if(split[0] == key) {
            return split[1];
        }
    }
}
