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