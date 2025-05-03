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
const nicknameInput = $("#nickname");
const emailInput = $("#email");
const fullnameInput = $("#fullname");
const statusNickname = $("#status");
const statusEmail = $("#statusemail");
const statusFullname = $("#statusfullname");

// Debounce function to limit the frequency of AJAX requests
function debounce(func, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}

// Reusable AJAX function
function performAjaxCheck(url, data, successCallback) {
    $.ajax({
        type: "POST",
        url: url,
        data: data,
        beforeSend: function () {
            statusNickname.html('<img src="./img/loader.gif">&nbsp;' + langArray['checking_availability']);
        },
        success: successCallback,
        error: function () {
            console.error("An error occurred during the AJAX request.");
        }
    });
}

// Nickname Check
nicknameInput.on("keyup focusout", debounce(function () {
    const nickname = nicknameInput.val();
    if (nickname.length < 4) {
        nicknameInput.removeClass("green").addClass("red");
        statusNickname.html(langArray.nickname_too_short);
        return;
    }
    if (!nickname.match(validNickname)) {
        nicknameInput.removeClass("green").addClass("red");
        statusNickname.html(langArray.nickname_contains_illegal_characters);
        return;
    }
    performAjaxCheck("ajax/ajax-nick.php", { nickname: nickname }, function (msg) {
        if (msg === "NICKOK") {
            nicknameInput.removeClass("red").addClass("green");
            statusNickname.html(langArray.nickname_available);
        } else if (msg === "NICKEXISTS") {
            nicknameInput.removeClass("green").addClass("red");
            statusNickname.html(langArray.nickname_already_exists);
        } else {
            console.error(msg);
        }
    });
}, 300));

// Email Check
emailInput.on("keyup focusout", debounce(function () {
    performAjaxCheck("ajax/ajax-email.php", emailInput.serialize(), function (msg) {
        if (msg === "EMAILOK") {
            emailInput.removeClass("red").addClass("green");
            statusEmail.html("");
        } else if (msg === "EMAILINUSE") {
            emailInput.removeClass("green").addClass("red");
            statusEmail.html(langArray.the_email_address_already_exists);
        } else if (msg === "EMAILFAIL") {
            emailInput.removeClass("green").addClass("red");
            statusEmail.html(langArray.you_must_enter_a_valid_email_address);
        } else {
            console.error(msg);
        }
    });
}, 300));

// Full Name Check
fullnameInput.on("keyup focusout", function () {
    const fullname = fullnameInput.val();
    if (fullname.match(illegalChars)) {
        fullnameInput.removeClass("green").addClass("red");
        statusFullname.html(langArray.fullname_contains_illegal_characters);
    } else if (!fullname || !fullname.match(validName) || fullname.length < 2 || !fullname.match(/\s/)) {
        fullnameInput.removeClass("green").addClass("red");
        statusFullname.html(langArray.you_must_enter_your_full_name);
    } else {
        fullnameInput.removeClass("red").addClass("green");
        statusFullname.html("");
    }
});