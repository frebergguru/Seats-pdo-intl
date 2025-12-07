
/*
 * This file is part of Seats-pdl-intl.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$(document).on('keyup focusout', '#nickname', function () {
    const nickname = $("#nickname").val();
    const url = "ajax/ajax-nick.php";

    $.ajax({
        type: "POST",
        url: url,
        data: { nickname },
        beforeSend: function () {
            $("#status").html('<img src="./img/loader.gif">&nbsp;' + langArray.checking_availability);
        },
        success: function (response) {
            try {
                const res = typeof response === "string" ? JSON.parse(response) : response;

                switch (res.status) {
                    case 'OK':
                        $("#nickname").removeClass("red").addClass("green");
                        $("#status").html(langArray.nickname_available);
                        break;
                    case 'EXISTS':
                        $("#nickname").removeClass("green").addClass("red");
                        $("#status").html(langArray.nickname_already_exists);
                        break;
                    case 'TOO_SHORT':
                        $("#nickname").removeClass("green").addClass("red");
                        $("#status").html(langArray.nickname_too_short);
                        break;
                    case 'INVALID_CHARS':
                        $("#nickname").removeClass("green").addClass("red");
                        $("#status").html(langArray.nickname_contains_illegal_characters);
                        break;
                    default:
                        $("#nickname").removeClass("green").addClass("red");
                        $("#status").html(langArray.error);
                        break;
                }
            } catch (e) {
                console.error("Invalid JSON:", response);
            }
        }
    });
});

// EMAIL CHECK
$(document).on('keyup focusout', '#email', function () {
    var email = $('#email').val();
    var url = 'ajax/ajax-email.php';

    $("#statusemail").html('<img src="./img/loader.gif">&nbsp;' + langArray.checking_availability);

    $.ajax({
        type: "POST",
        url: url,
        data: { email: email },
        success: function (response) {
            const sanitizedMessage = $("<div>").text(response.message || '').html(); // Sanitize the message

            switch (response.status) {
                case 'EMAILOK':
                    $("#email").removeClass("red").addClass("green");
                    $("#statusemail").html(''); // Clear the status message
                    break;
                case 'EMAILINUSE':
                    $("#email").removeClass("green").addClass("red");
                    $("#statusemail").html(sanitizedMessage); // Use sanitized message
                    break;
                case 'EMAILFAIL':
                default:
                    $("#email").removeClass("green").addClass("red");
                    $("#statusemail").html(sanitizedMessage || 'Unknown error.'); // Use sanitized message
                    break;
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', status, error);
            $("#statusemail").html('An unexpected error occurred.');
        }
    });

    return false;
});

