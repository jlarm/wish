// Background service worker for the Wishlist extension

// Handle extension installation
chrome.runtime.onInstalled.addListener(() => {
  console.log('Wishlist Extension installed');
});

// Handle messages from content scripts or popup
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
  if (request.action === 'notification') {
    // Handle notifications if needed
    chrome.notifications.create({
      type: 'basic',
      iconUrl: 'icons/icon48.png',
      title: 'Wishlist Extension',
      message: request.message
    });
  }
});

// Context menu integration (optional)
chrome.contextMenus.create({
  id: 'add-to-wishlist',
  title: 'Add to Wishlist',
  contexts: ['page', 'selection', 'image', 'link']
});

chrome.contextMenus.onClicked.addListener((info, tab) => {
  if (info.menuItemId === 'add-to-wishlist') {
    // Open popup or inject content script
    chrome.action.openPopup();
  }
});