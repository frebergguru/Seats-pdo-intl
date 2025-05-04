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

$(document).on('keyup focusout', '#password', function () {
    const password = $(this).val();

    $.ajax({
        type: "POST",
        url: "ajax/ajax-pwd.php",
        data: { password },
        success: function (response) {
            try {
                const res = typeof response === "string" ? JSON.parse(response) : response;

                switch (res.status) {
                    case 'STRONG':
                        $("#password").removeClass("red").addClass("green");
                        $("#statuspassword").html(langArray.password_strong);
                        break;
                    case 'INVALID_CHARACTERS':
                        $("#password").removeClass("green").addClass("red");
                        $("#statuspassword").html(langArray.password_contains_illegal_characters);
                        break;
                    case 'EMPTY':
                        $("#password").removeClass("green").addClass("red");
                        $("#statuspassword").html(langArray.password_cannot_be_empty);
                        break;
                    default:
                        $("#password").removeClass("green").addClass("red");
                        $("#statuspassword").html(langArray.error);
                        break;
                }
            } catch (e) {
                console.error("Invalid JSON response:", response);
            }
        }
    });
});
