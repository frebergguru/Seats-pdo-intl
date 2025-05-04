/*
 * This file is part of Seats-pdl-intl.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
*/

// Selectors
const passwordRequirements = document.querySelector("#passwordRequirements");
const bubblePopup = document.querySelector("#bubblePopup");
const closePopup = document.querySelector("#closePopup");

// Function to show the popup
function showPopup() {
    bubblePopup.style.display = "inline-block";
    bubblePopup.setAttribute("aria-hidden", "false");
    bubblePopup.focus();

    // Add keydown listener for closing the popup
    document.addEventListener("keydown", handleEscapeKey);
}

// Function to hide the popup
function hidePopup() {
    bubblePopup.style.display = "none";
    bubblePopup.setAttribute("aria-hidden", "true");
    passwordRequirements.focus();

    // Remove keydown listener
    document.removeEventListener("keydown", handleEscapeKey);
}

// Function to handle the Escape key
function handleEscapeKey(e) {
    if (e.key === "Escape") {
        hidePopup();
    }
}

// Event listeners
passwordRequirements.addEventListener("click", function (e) {
    e.preventDefault();
    showPopup();
});

closePopup.addEventListener("click", hidePopup);