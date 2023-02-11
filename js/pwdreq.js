var passwordRequirements = document.getElementById("passwordRequirements");
var bubblePopup = document.getElementById("bubblePopup");
var closePopup = document.getElementById("closePopup");

passwordRequirements.addEventListener("click", function (e) {
    e.preventDefault();
    bubblePopup.style.display = "inline-block";
});

closePopup.addEventListener("click", function () {
    bubblePopup.style.display = "none";
});

document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
        bubblePopup.style.display = "none";
    }
});