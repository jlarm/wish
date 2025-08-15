# Wishlist Chrome Extension

A Chrome extension that allows you to add items from any website to your Laravel wishlist application.

## Features

- 🔍 Auto-extract item information from web pages
- 🔐 Secure authentication with your Laravel app
- ✨ Clean, intuitive interface
- 🛍️ Support for major e-commerce sites
- 📱 Responsive design

## Installation

1. Open Chrome and navigate to `chrome://extensions/`
2. Enable "Developer mode" in the top right
3. Click "Load unpacked" and select the `chrome-extension` folder
4. The extension icon should appear in your toolbar

## Setup

1. Update the API URL in `popup.js`:
   ```javascript
   this.apiUrl = 'https://your-app-domain.com/api';
   ```

2. Make sure your Laravel app has CORS configured to allow requests from Chrome extensions

## Usage

1. Click the extension icon in your toolbar
2. Login with your wishlist account credentials
3. Navigate to any product page
4. Click "Extract Item Info" to automatically detect product details
5. Review and edit the information as needed
6. Click "Add to Wishlist" to save the item

## Development

The extension consists of:
- `manifest.json` - Extension configuration
- `popup.html/js` - Main interface
- `content.js` - Web page content extraction
- `background.js` - Service worker for background tasks

## Browser Support

- Chrome 88+
- Chromium-based browsers (Edge, Brave, etc.)

## Security

- All API communication uses secure HTTPS
- Authentication tokens are stored locally and encrypted
- No sensitive data is transmitted to third parties