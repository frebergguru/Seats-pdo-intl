document.addEventListener("DOMContentLoaded", function() {
    var configElem = document.getElementById('app-config');
    if (configElem) {
        try {
            var config = JSON.parse(configElem.getAttribute('data-config'));
            window.langArray = config.langArray || {};
            window.illegalChars = config.illegalChars || '';
            window.validName = config.validName || '';
            window.validNickname = config.validNickname || '';
        } catch (e) {
            console.error("Error parsing app config:", e);
        }
    }
});
