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

// Selectors
const passwordInput = $("#password");
const confirmPasswordInput = $("#password2");
const passwordStatus = $("#pwstatus");
const confirmPasswordStatus = $("#pwstatus2");

// Debounce function to limit the frequency of AJAX requests
function debounce(func, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}

// Reusable AJAX function for password validation
function validatePassword(password) {
    const url = "ajax/ajax-pwd.php";
    passwordStatus.html('<img src="./img/loader.gif">' + langArray.checking);

    $.ajax({
        type: "POST",
        url: url,
        data: { password: password },
        success: function (msg) {
            if (msg === "PWDEMPTY") {
                passwordInput.removeClass("green yellow red");
                passwordStatus.html("");
            } else if (msg === "PWDINVALIDCHAR") {
                passwordInput.removeClass("green red").addClass("yellow");
                passwordStatus.html(langArray.the_password_contains_illegal_characters);
            } else if (msg === "PWDSTRONG") {
                passwordInput.removeClass("red yellow").addClass("green");
                passwordStatus.html("");
            } else {
                console.error("Unexpected response: " + msg);
            }
        },
        error: function () {
            console.error("An error occurred during the AJAX request.");
        }
    });
}

// Password strength check
passwordInput.on("keyup focusout", debounce(function () {
    const password = passwordInput.val();

    // Check for illegal characters
    if (password.match(/[<>]/g)) {
        passwordInput.removeClass("green").addClass("red");
        passwordStatus.html(langArray.the_password_contains_illegal_characters);
        return;
    }

    // Perform AJAX validation
    validatePassword(password);
}, 300));

// Password match check
function checkPasswordMatch() {
    const password = passwordInput.val();
    const confirmPassword = confirmPasswordInput.val();

    if (password !== confirmPassword) {
        confirmPasswordInput.removeClass("green").addClass("red");
        confirmPasswordStatus.html(langArray.the_password_dosent_match);
    } else {
        confirmPasswordInput.removeClass("red").addClass("green");
        confirmPasswordStatus.html(langArray.the_passwords_match);
    }
}

// Attach event listener for password confirmation
confirmPasswordInput.on("keyup focusout", debounce(checkPasswordMatch, 300));