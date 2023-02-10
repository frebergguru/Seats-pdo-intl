var popup = document.getElementById("requirementsPopup");
var link = document.getElementById("passwordRequirements");

link.addEventListener("click", function () {
    if (popup.style.display === "block") {
        popup.style.display = "none";
    } else {
        popup.style.display = "block";
    }
});

document.getElementById("closePopup").addEventListener("click", function () {
    popup.style.display = "none";
});