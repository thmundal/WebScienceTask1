$(function() {
    $(".js-register-form").on("submit", function() {
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
})
