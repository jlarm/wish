class WishlistExtension {
  constructor() {
    this.apiUrl = 'https://wishlist.joelohr.com/api'; // Change this to your Laravel app URL
    this.token = null;
    this.user = null;

    this.init();
  }

  async init() {
    // Load stored authentication
    await this.loadAuth();

    // Setup event listeners
    this.setupEventListeners();

    // Check authentication status
    if (this.token) {
      await this.checkAuth();
    } else {
      this.showAuthSection();
    }
  }

  setupEventListeners() {
    // Login form
    document.getElementById('login-btn').addEventListener('click', () => this.login());
    document.getElementById('password').addEventListener('keypress', (e) => {
      if (e.key === 'Enter') this.login();
    });

    // Logout
    document.getElementById('logout-btn').addEventListener('click', () => this.logout());

    // Extract item info
    document.getElementById('extract-btn').addEventListener('click', () => this.extractItemInfo());

    // Form submission
    document.getElementById('item-form').addEventListener('submit', (e) => {
      e.preventDefault();
      this.addItem();
    });
  }

  async loadAuth() {
    return new Promise((resolve) => {
      chrome.storage.local.get(['token', 'user'], (result) => {
        this.token = result.token;
        this.user = result.user;
        resolve();
      });
    });
  }

  async saveAuth(token, user) {
    this.token = token;
    this.user = user;

    return new Promise((resolve) => {
      chrome.storage.local.set({ token, user }, resolve);
    });
  }

  async clearAuth() {
    this.token = null;
    this.user = null;

    return new Promise((resolve) => {
      chrome.storage.local.remove(['token', 'user'], resolve);
    });
  }

  async login() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    if (!email || !password) {
      this.showMessage('Please enter both email and password', 'error');
      return;
    }

    this.setLoading(true);

    try {
      const response = await fetch(`${this.apiUrl}/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ email, password })
      });

      const data = await response.json();

      if (response.ok) {
        await this.saveAuth(data.token, data.user);
        this.showItemSection();
        this.showMessage('Login successful!', 'success');

        // Auto-extract item info on login
        setTimeout(() => this.extractItemInfo(), 1000);
      } else {
        this.showMessage(data.message || 'Login failed', 'error');
      }
    } catch (error) {
      this.showMessage('Connection error. Please check your network.', 'error');
      console.error('Login error:', error);
    }

    this.setLoading(false);
  }

  async logout() {
    await this.clearAuth();
    this.showAuthSection();
    this.showMessage('Logged out successfully', 'success');
  }

  async checkAuth() {
    if (!this.token) {
      this.showAuthSection();
      return;
    }

    try {
      const response = await fetch(`${this.apiUrl}/user`, {
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Accept': 'application/json',
        }
      });

      if (response.ok) {
        const user = await response.json();
        this.user = user;
        this.showItemSection();

        // Auto-extract item info
        setTimeout(() => this.extractItemInfo(), 500);
      } else {
        await this.clearAuth();
        this.showAuthSection();
      }
    } catch (error) {
      console.error('Auth check error:', error);
      this.showAuthSection();
    }
  }

  async extractItemInfo() {
    this.setLoading(true);

    try {
      // Get current tab
      const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });

      // Send message to content script
      const response = await chrome.tabs.sendMessage(tab.id, { action: 'extractItem' });

      if (response && response.success && response.data) {
        this.populateForm(response.data);
        this.showPreview(response.data);
        this.showMessage('Item information extracted!', 'success');
      } else {
        this.showMessage('Could not extract item info from this page', 'error');
      }
    } catch (error) {
      console.error('Extract error:', error);
      this.showMessage('Error extracting item information', 'error');
    }

    this.setLoading(false);
  }

  populateForm(data) {
    document.getElementById('item-name').value = data.name || '';
    document.getElementById('item-price').value = data.price || '';
    document.getElementById('item-store').value = data.store || '';
    document.getElementById('item-link').value = data.link || '';
    document.getElementById('item-image').value = data.image || '';
  }

  showPreview(data) {
    const preview = document.getElementById('item-preview');
    const nameEl = document.getElementById('preview-name');
    const priceEl = document.getElementById('preview-price');
    const storeEl = document.getElementById('preview-store');

    nameEl.textContent = data.name || 'No name detected';
    priceEl.textContent = data.price ? `$${data.price}` : 'No price detected';
    storeEl.textContent = data.store || 'No store detected';

    preview.classList.remove('hidden');
  }

  async addItem() {
    if (!this.token) {
      this.showMessage('Please login first', 'error');
      return;
    }

    const formData = {
      name: document.getElementById('item-name').value,
      price: document.getElementById('item-price').value,
      store: document.getElementById('item-store').value,
      size: document.getElementById('item-size').value,
      color: document.getElementById('item-color').value,
      link: document.getElementById('item-link').value,
      image: document.getElementById('item-image').value,
    };

    // Remove empty fields
    Object.keys(formData).forEach(key => {
      if (!formData[key]) delete formData[key];
    });

    if (!formData.name) {
      this.showMessage('Item name is required', 'error');
      return;
    }

    this.setLoading(true);

    try {
      const response = await fetch(`${this.apiUrl}/items`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${this.token}`,
        },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (response.ok) {
        this.showMessage('Item added to wishlist!', 'success');
        this.clearForm();
        document.getElementById('item-preview').classList.add('hidden');
      } else {
        this.showMessage(data.message || 'Failed to add item', 'error');
      }
    } catch (error) {
      this.showMessage('Connection error. Please try again.', 'error');
      console.error('Add item error:', error);
    }

    this.setLoading(false);
  }

  clearForm() {
    document.getElementById('item-form').reset();
  }

  showAuthSection() {
    document.getElementById('auth-section').classList.remove('hidden');
    document.getElementById('item-section').classList.add('hidden');
    document.getElementById('login-form').classList.remove('hidden');
    document.getElementById('user-info').classList.add('hidden');
  }

  showItemSection() {
    document.getElementById('auth-section').classList.remove('hidden');
    document.getElementById('item-section').classList.remove('hidden');
    document.getElementById('login-form').classList.add('hidden');
    document.getElementById('user-info').classList.remove('hidden');

    if (this.user) {
      document.getElementById('user-name').textContent = this.user.name || this.user.email;
    }
  }

  showMessage(message, type) {
    const messageEl = document.getElementById('status-message');
    messageEl.textContent = message;
    messageEl.className = `status-message status-${type}`;
    messageEl.classList.remove('hidden');

    setTimeout(() => {
      messageEl.classList.add('hidden');
    }, 5000);
  }

  setLoading(loading) {
    const body = document.body;
    if (loading) {
      body.classList.add('loading');
    } else {
      body.classList.remove('loading');
    }
  }
}

// Initialize the extension when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  new WishlistExtension();
});
