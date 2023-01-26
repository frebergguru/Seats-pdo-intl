$(document).on('keyup focusout', '#password', function() {
    var password = $("#password").val();
    if (password.match(/[<>]/g) != null) {
        $("#password").removeClass("green").addClass("red");
        $("#pwstatus").html(langArray.the_password_contains_illegal_characters);
    } else {
        var url = "ajax/ajax-pwd.php";
        $("#pwstatus").html('<img src="./img/loader.gif">' + langArray.checking);
        $.ajax({
            type: "POST",
            url: url,
            data: $("#password").serialize(),
            success: function(msg) {
                if (msg == 'PWDEMPTY') {
                    $("#password").removeClass("green").removeClass("yellow").removeClass("red");
                    $("#pwstatus").html('');
                } else if (msg == 'PWDINVALIDCHAR') {
                    $("#password").removeClass("green").removeClass("red").addClass("yellow");
                    $("#pwstatus").html(langArray.the_password_contains_illegal_characters);
                } else if (msg == 'PWDSTRONG') {
                    $("#password").removeClass("red").removeClass("yellow").addClass("green");
                    $("#pwstatus").html('');
                }
		else {
		     console.log(msg);
		}
            }
        });
        return false;
    }
});

function checkPasswordMatch() {
    var password = $("#password").val();
    var password2 = $("#password2").val();

    if (password != password2) {
        $("#password2").removeClass("green").addClass("red");
        $("#pwstatus2").html(langArray.the_password_dosent_match);
    } else {
        $("#password2").removeClass("red").addClass("green");
        $("#pwstatus2").html(langArray.the_passwords_match);
    }
}
$(document).ready(function() {
	$("#password2").on("keyup focusout", checkPasswordMatch);
});
