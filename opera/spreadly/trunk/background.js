// Add a button to Opera's toolbar when the extension loads.
window.addEventListener("load", function() {
    var theButton;
    var toolbar = opera.contexts.toolbar;
    var ToolbarUIItemProperties = {
        title: "first choice for social sharing",
        icon: "icons/button-icon.png",

        popup: {
            width: 580,
            height: 450
        },

        onclick: function() {
            var extension = window.opera.extension;
            var tab = extension.tabs.getFocused();

            if (tab) {
                var url = encodeURIComponent(tab.url);
                var title = encodeURIComponent(tab.title);
                // New variant with popup
                theButton.popup.href = 'http://spread.ly/?url=' + url + '&title=' + title;
            }
        }
    };
    theButton = toolbar.createItem(ToolbarUIItemProperties);
    toolbar.addItem(theButton);
}, false);