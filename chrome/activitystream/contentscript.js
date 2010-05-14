findActivityStreamLinks();

// See if the document contains a <link> tag within the <head> and
// whether that points to an RSS feed.
function findActivityStreamLinks() {
  // Find all the RSS link elements.
  var result = document.evaluate('//*[local-name()="link"][contains(@rel, "activitystream")]', document, null, 0, null);

  var feeds = [];
  var item;
  var count = 0;
  while (item = result.iterateNext()) {
    feeds.push({"href": item.href, "title": item.title});
    ++count;
  }

  if (count > 0) {
    // Notify the extension needs to show the RSS page action icon.
    chrome.extension.sendRequest({msg: "activityIcon", feeds: feeds});
  }
}