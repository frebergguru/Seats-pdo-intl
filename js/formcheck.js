//NICKNAME CHECK
$(document).on('keyup focusout', '#nickname', function() {
    var nickname = $("#nickname").val();
    var url = "ajax/ajax-nick.php";
    $.ajax({
        type: "POST",
        url: url,
        data: {nickname: nickname},
        beforeSend: function(){
            $("#status").html('<img src="./img/loader.gif">&nbsp;' + langArray['checking_availability']);
        },
        success: function(msg) {
            if (nickname.length < 4) {
                $("#nickname").removeClass("green").addClass("red");
                $("#status").html(langArray.nickname_too_short);
            }
            else if (!nickname.match(validNickname)) {
                $("#nickname").removeClass("green").addClass("red");
                $("#status").html(langArray.nickname_contains_illegal_characters);
            }
            else if (msg == 'NICKOK') {
                $("#nickname").removeClass("red").addClass("green");
                $("#status").html(langArray.nickname_available);
            } else if (msg == 'NICKEXISTS') {
                $("#nickname").removeClass("green").addClass("red");
                $("#status").html(langArray.nickname_already_exists);
            }
            else{
                console.log(msg);
            }
        }
    });
});

//EMAIL CHECK
$(document).on('keyup focusout', '#email', function() {
    var url = "ajax/ajax-email.php";
    $("#statusemail").html('<img src="./img/loader.gif">&nbsp;' + langArray.checking_availability);
    $.ajax({
        type: "POST",
        url: url,
        data: $("#email").serialize(),
        success: function(msg) {
            if (msg == 'EMAILOK') {
                $("#email").removeClass("red").addClass("green");
                $("#statusemail").html('');
            } else if (msg == 'EMAILINUSE') {
                $("#email").removeClass("green").addClass("red");
                $("#statusemail").html(langArray.the_email_address_already_exists);
            } else if (msg == 'EMAILFAIL') {
                $("#email").removeClass("green").addClass("red");
                $("#statusemail").html(langArray.you_must_enter_a_valid_email_address);
            }
	    else {
		 console.log(msg);
	    }
        }
    });
    return false;
});

//FULLNAME CHECK
$("#fullname").on("keyup focusout", function() {
    var fullname = $(this).val();

    if (fullname.match(illegalChars)) {
        $(this).removeClass("green").addClass("red");
        $("#statusfullname").html(langArray.fullname_contains_illegal_characters);
    } else if (!fullname || !fullname.match(validName) || fullname.length < 2 || !fullname.match(/\s/)) {
        $(this).removeClass("green").addClass("red");
        $("#statusfullname").html(langArray.you_must_enter_your_full_name);
    } else {
        $(this).removeClass("red").addClass("green");
        $("#statusfullname").html("");
    }
});
