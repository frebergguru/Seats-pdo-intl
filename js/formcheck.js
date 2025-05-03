/*
    Copyright 2023 Morten Freberg

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 */

//NICKNAME CHECK
$(document).on('keyup focusout', '#nickname', function () {
    var nickname = $("#nickname").val();
    var url = "ajax/ajax-nick.php";
    $.ajax({
        type: "POST",
        url: url,
        data: { nickname: nickname },
        beforeSend: function () {
            $("#status").html('<img src="./img/loader.gif">&nbsp;' + langArray['checking_availability']);
        },
        success: function (msg) {
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
            else {
                console.log(msg);
            }
        }
    });
});

//EMAIL CHECK
$(document).on('keyup focusout', '#email', function () {
    var url = "ajax/ajax-email.php";
    $("#statusemail").html('<img src="./img/loader.gif">&nbsp;' + langArray.checking_availability);
    $.ajax({
        type: "POST",
        url: url,
        data: $("#email").serialize(),
        success: function (msg) {
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
$("#fullname").on("keyup focusout", function () {
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
