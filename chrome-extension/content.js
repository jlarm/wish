// Content script to extract item information from web pages
class ItemExtractor {
  constructor() {
    this.extractedData = null;
  }

  // Extract item information from the current page
  extractItemInfo() {
    const itemData = {
      name: this.extractName(),
      price: this.extractPrice(),
      image: this.extractImage(),
      link: window.location.href,
      store: this.extractStore()
    };

    // Clean up the data
    Object.keys(itemData).forEach(key => {
      if (itemData[key] === null || itemData[key] === undefined || itemData[key] === '') {
        delete itemData[key];
      }
    });

    this.extractedData = itemData;
    return itemData;
  }

  // Extract product name using common selectors
  extractName() {
    const selectors = [
      'h1[data-testid*="product-title"]',
      'h1[class*="product-title"]',
      'h1[class*="product-name"]',
      'h1[id*="product-title"]',
      'h1[id*="product-name"]',
      '[data-testid*="product-title"]',
      '[class*="product-title"]',
      '[class*="product-name"]',
      '[class*="item-title"]',
      '[class*="item-name"]',
      'h1',
      '.product-title',
      '.product-name',
      '.item-title',
      '.item-name',
      '#product-title',
      '#product-name'
    ];

    for (const selector of selectors) {
      const element = document.querySelector(selector);
      if (element && element.textContent.trim()) {
        return this.cleanText(element.textContent);
      }
    }

    // Fallback to page title
    const title = document.title;
    if (title && !title.includes('404') && !title.includes('Error')) {
      return this.cleanText(title);
    }

    return null;
  }

  // Extract price using common selectors
  extractPrice() {
    const selectors = [
      '[data-testid*="price"]',
      '[class*="price"]',
      '[id*="price"]',
      '.price-current',
      '.price-now',
      '.price-final',
      '.current-price',
      '.sale-price',
      '.product-price',
      '.item-price',
      '[data-price]'
    ];

    for (const selector of selectors) {
      const element = document.querySelector(selector);
      if (element) {
        const priceText = element.textContent || element.getAttribute('data-price') || '';
        const price = this.extractPriceFromText(priceText);
        if (price !== null) {
          return price;
        }
      }
    }

    // Look for currency symbols in text
    const bodyText = document.body.textContent;
    const priceMatch = bodyText.match(/\$[\d,]+\.?\d*/);
    if (priceMatch) {
      return this.extractPriceFromText(priceMatch[0]);
    }

    return null;
  }

  // Extract price number from text
  extractPriceFromText(text) {
    if (!text) return null;
    
    const cleaned = text.replace(/[^\d.,]/g, '');
    const priceMatch = cleaned.match(/\d+\.?\d*/);
    
    if (priceMatch) {
      const price = parseFloat(priceMatch[0]);
      return isNaN(price) ? null : price;
    }
    
    return null;
  }

  // Extract main product image
  extractImage() {
    const selectors = [
      'img[data-testid*="product-image"]',
      'img[class*="product-image"]',
      'img[class*="product-photo"]',
      'img[class*="main-image"]',
      'img[class*="hero-image"]',
      'img[id*="product-image"]',
      'img[alt*="product"]',
      '.product-image img',
      '.product-photo img',
      '.main-image img',
      '.hero-image img'
    ];

    for (const selector of selectors) {
      const img = document.querySelector(selector);
      if (img && img.src && this.isValidImageUrl(img.src)) {
        return this.getAbsoluteUrl(img.src);
      }
    }

    // Fallback to largest image on page
    const images = Array.from(document.querySelectorAll('img'));
    const validImages = images.filter(img => 
      img.src && 
      this.isValidImageUrl(img.src) && 
      img.naturalWidth > 100 && 
      img.naturalHeight > 100
    );

    if (validImages.length > 0) {
      // Sort by size and take the largest
      validImages.sort((a, b) => (b.naturalWidth * b.naturalHeight) - (a.naturalWidth * a.naturalHeight));
      return this.getAbsoluteUrl(validImages[0].src);
    }

    return null;
  }

  // Extract store name from domain or page
  extractStore() {
    // Try to find store name in meta tags
    const storeSelectors = [
      'meta[property="og:site_name"]',
      'meta[name="application-name"]',
      'meta[name="site-name"]'
    ];

    for (const selector of storeSelectors) {
      const meta = document.querySelector(selector);
      if (meta && meta.content) {
        return this.cleanText(meta.content);
      }
    }

    // Extract from domain
    const hostname = window.location.hostname;
    let storeName = hostname.replace('www.', '').split('.')[0];
    
    // Capitalize first letter
    storeName = storeName.charAt(0).toUpperCase() + storeName.slice(1);
    
    return storeName;
  }

  // Helper methods
  cleanText(text) {
    return text.trim().replace(/\s+/g, ' ').substring(0, 255);
  }

  isValidImageUrl(url) {
    return url && 
           !url.includes('data:') && 
           !url.includes('blob:') && 
           /\.(jpg|jpeg|png|gif|webp)(\?|$)/i.test(url);
  }

  getAbsoluteUrl(url) {
    if (url.startsWith('http')) {
      return url;
    }
    return new URL(url, window.location.origin).href;
  }
}

// Create global extractor instance
window.itemExtractor = new ItemExtractor();

// Listen for messages from popup
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
  if (request.action === 'extractItem') {
    const itemData = window.itemExtractor.extractItemInfo();
    sendResponse({ success: true, data: itemData });
  }
});

// Auto-extract on page load (optional)
document.addEventListener('DOMContentLoaded', () => {
  // Store extracted data for quick access
  window.itemExtractor.extractItemInfo();
});