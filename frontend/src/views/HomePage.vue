<script setup>
import { ref, provide } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isServicesOpen = ref(false)
const isMobileMenuOpen = ref(false)
const isMobileServicesOpen = ref(false)
const selectedCurrency = ref('EUR')
const selectedLanguage = ref('ENG')
const selectedTheme = ref('light')

provide('theme', selectedTheme)

const toggleServices = () => {
  isServicesOpen.value = !isServicesOpen.value
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
  if (!isMobileMenuOpen.value) {
    isMobileServicesOpen.value = false
  }
}

const toggleMobileServices = () => {
  isMobileServicesOpen.value = !isMobileServicesOpen.value
}

const goToHome = () => {
  router.push('/')
  closeMobileMenu()
}

const handleLogin = () => {
  console.log('Login clicked')
  closeMobileMenu()
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
  isMobileServicesOpen.value = false
}

const changeCurrency = (currency) => {
  selectedCurrency.value = currency
}

const changeLanguage = (lang) => {
  selectedLanguage.value = lang
}

const toggleTheme = () => {
  selectedTheme.value = selectedTheme.value === 'light' ? 'dark' : 'light'
}
</script>

<template>
  <div class="home-page" :class="selectedTheme">
    <header class="header">
      <div class="header-container">
        <button class="theme-toggle" @click="toggleTheme" :aria-label="selectedTheme === 'light' ? 'Switch to dark mode' : 'Switch to light mode'">
          <svg v-if="selectedTheme === 'light'" class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
          </svg>
          <svg v-else class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="5"/>
            <line x1="12" y1="1" x2="12" y2="3"/>
            <line x1="12" y1="21" x2="12" y2="23"/>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
            <line x1="1" y1="12" x2="3" y2="12"/>
            <line x1="21" y1="12" x2="23" y2="12"/>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
          </svg>
        </button>

        <div class="center-content">
          <div class="logo" @click="goToHome">
            <svg class="logo-icon" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
              <path fill="currentColor" d="M64 24A40 40 0 1 0 64 104A40 40 0 1 0 64 24ZM44 52L84 52A12 12 0 0 1 84 76L44 76A12 12 0 0 1 44 52ZM52 60A4 4 0 1 0 52 68A4 4 0 1 0 52 60ZM76 60A4 4 0 1 0 76 68A4 4 0 1 0 76 60Z"/>
            </svg>
            <span class="logo-text">Game Prices</span>
          </div>

          <nav class="nav-menu desktop-nav">
            <button class="nav-btn">Games</button>
            <button class="nav-btn">About us</button>
            
            <div class="services-dropdown">
              <button class="nav-btn" @click="toggleServices">
                Services
                <svg class="arrow-icon" :class="{ 'arrow-up': isServicesOpen }" viewBox="0 0 12 8" fill="none">
                  <path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
              </button>
              <div class="dropdown-menu" :class="{ 'dropdown-open': isServicesOpen }">
                <a href="#" class="dropdown-item">API</a>
              </div>
            </div>
          </nav>
        </div>

        <button class="login-btn desktop-login" @click="handleLogin">Login</button>

        <!-- menu -->
        <button class="mobile-menu-btn" @click="toggleMobileMenu" :aria-label="isMobileMenuOpen ? 'Close menu' : 'Open menu'">
          <svg v-if="!isMobileMenuOpen" class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
          <svg v-else class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <div class="mobile-menu" :class="{ 'mobile-menu-open': isMobileMenuOpen }">
        <nav class="mobile-nav">
          <button class="mobile-nav-btn" @click="closeMobileMenu">Games</button>
          <button class="mobile-nav-btn" @click="closeMobileMenu">About us</button>
          
          <div class="mobile-services">
            <button class="mobile-nav-btn" @click="toggleMobileServices">
              <span>Services</span>
              <svg class="arrow-icon" :class="{ 'arrow-up': isMobileServicesOpen }" viewBox="0 0 12 8" fill="none">
                <path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </button>
            <div class="mobile-submenu" :class="{ 'mobile-submenu-open': isMobileServicesOpen }">
              <a href="#" class="mobile-submenu-item" @click="closeMobileMenu">API</a>
            </div>
          </div>

          <button class="mobile-login-btn" @click="handleLogin">Login</button>
        </nav>
      </div>
    </header>

    <main class="main-content">
      <div class="hero-section">
        <h1 class="hero-title">Find the best game deals in one place</h1>
        <p class="hero-subtitle">Game Prices automatically collects prices from leading digital stores, compares them, visualizes price history, and helps you find the best deals without wasting time.</p>
        
        <div class="cta-buttons">
          <button class="cta-primary">Get Started</button>
          <button class="cta-secondary">Learn More</button>
        </div>

        <div class="feature-grid">
          <div class="feature-item">
            <div class="feature-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                <line x1="7" y1="7" x2="7.01" y2="7"/>
              </svg>
            </div>
            <h3>Price Comparison</h3>
            <p>We provide real-time price comparison from major stores so you always know where the best deal is.</p>
          </div>

          <div class="feature-item">
            <div class="feature-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
              </svg>
            </div>
            <h3>Price History & Analytics</h3>
            <p>Track detailed price history to understand trends and choose the perfect moment to buy.</p>
          </div>

          <div class="feature-item">
            <div class="feature-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
              </svg>
            </div>
            <h3>Wishlist & Notifications</h3>
            <p>Save your favorite games to a wishlist and get instant notifications when prices drop.</p>
          </div>
        </div>
      </div>
    </main>

    <footer class="footer">
      <div class="footer-container">
        <div class="footer-section">
          <span class="footer-label">Currency</span>
          <div class="footer-options">
            <button 
              v-for="currency in ['EUR', 'USD', 'GBP']" 
              :key="currency"
              :class="{ 'active': selectedCurrency === currency }"
              @click="changeCurrency(currency)"
              class="footer-btn"
            >
              {{ currency }}
            </button>
          </div>
        </div>

        <div class="footer-divider"></div>

        <div class="footer-section">
          <span class="footer-label">Language</span>
          <div class="footer-options">
            <button 
              v-for="lang in ['ENG', 'LV']" 
              :key="lang"
              :class="{ 'active': selectedLanguage === lang }"
              @click="changeLanguage(lang)"
              class="footer-btn"
            >
              {{ lang }}
            </button>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.home-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  transition: background-color 0.3s ease, color 0.3s ease;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
}

.home-page.light {
  --bg-primary: #ffffff;
  --bg-secondary: #f5f5f7;
  --text-primary: #1d1d1f;
  --text-secondary: #86868b;
  --border-color: #d2d2d7;
  --hover-bg: #f5f5f7;
  --accent-color: #0071e3;
}

.home-page.dark {
  --bg-primary: #000000;
  --bg-secondary: #1d1d1f;
  --text-primary: #f5f5f7;
  --text-secondary: #86868b;
  --border-color: #424245;
  --hover-bg: #1d1d1f;
  --accent-color: #2997ff;
}

.home-page {
  background: var(--bg-primary);
  color: var(--text-primary);
}

.header {
  background: rgba(var(--bg-primary-rgb), 0.8);
  backdrop-filter: saturate(180%) blur(20px);
  -webkit-backdrop-filter: saturate(180%) blur(20px);
  border-bottom: 1px solid var(--border-color);
  padding: 0 1rem;
  position: sticky;
  top: 0;
  z-index: 100;
}

.home-page.light .header {
  --bg-primary-rgb: 255, 255, 255;
}

.home-page.dark .header {
  --bg-primary-rgb: 0, 0, 0;
}

.header-container {
  max-width: 1280px;
  margin: 0 auto;
  height: 48px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.theme-toggle {
  background: transparent;
  border: none;
  color: var(--text-primary);
  width: 40px;
  height: 40px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s ease;
  padding: 0;
}

.theme-toggle:hover {
  opacity: 0.7;
}

.theme-toggle .icon {
  width: 20px;
  height: 20px;
}

.center-content {
  display: flex;
  align-items: center;
  gap: 2rem;
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
}

.logo {
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: opacity 0.2s ease;
}

.logo:hover {
  opacity: 0.7;
}

.logo-icon {
  width: 22px;
  height: 22px;
  color: var(--text-primary);
}

.logo-text {
  font-size: 0.875rem;
  font-weight: 400;
  color: var(--text-primary);
  letter-spacing: 0;
}

.nav-menu {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.nav-btn {
  background: transparent;
  border: none;
  color: var(--text-primary);
  font-size: 0.875rem;
  font-weight: 400;
  cursor: pointer;
  transition: opacity 0.2s ease;
  padding: 0;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  white-space: nowrap;
}

.nav-btn:hover {
  opacity: 0.7;
}

.services-dropdown {
  position: relative;
}

.arrow-icon {
  width: 10px;
  height: 10px;
  transition: transform 0.2s ease;
  margin-left: 0.25rem;
}

.arrow-up {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 12px);
  left: 50%;
  transform: translateX(-50%);
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  min-width: 200px;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.2s ease, visibility 0.2s ease;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  padding: 0.5rem 0;
}

.dropdown-open {
  opacity: 1;
  visibility: visible;
}

.dropdown-item {
  display: block;
  padding: 0.5rem 1rem;
  color: var(--text-primary);
  text-decoration: none;
  font-size: 0.875rem;
  transition: background-color 0.2s ease;
}

.dropdown-item:hover {
  background: var(--hover-bg);
}

.login-btn {
  background: var(--accent-color);
  color: white;
  border: none;
  padding: 0.375rem 1rem;
  border-radius: 6px;
  font-weight: 400;
  cursor: pointer;
  font-size: 0.875rem;
  transition: opacity 0.2s ease;
}

.login-btn:hover {
  opacity: 0.8;
}

/* menu */
.mobile-menu-btn {
  display: none;
  background: transparent;
  border: none;
  color: var(--text-primary);
  width: 40px;
  height: 40px;
  cursor: pointer;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s ease;
  padding: 0;
}

.mobile-menu-btn:hover {
  opacity: 0.7;
}

.mobile-menu-btn .icon {
  width: 24px;
  height: 24px;
}

.mobile-menu {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
  background: var(--bg-primary);
  border-bottom: 1px solid transparent;
}

.mobile-menu-open {
  max-height: 500px;
  border-bottom-color: var(--border-color);
}

.mobile-nav {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.mobile-nav-btn {
  background: transparent;
  border: none;
  color: var(--text-primary);
  font-size: 1rem;
  font-weight: 400;
  cursor: pointer;
  padding: 0.75rem 1rem;
  text-align: left;
  transition: background-color 0.2s ease;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}

.mobile-nav-btn:hover {
  background: var(--hover-bg);
}

.mobile-services {
  display: flex;
  flex-direction: column;
}

.mobile-submenu {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
  padding-left: 1rem;
}

.mobile-submenu-open {
  max-height: 200px;
}

.mobile-submenu-item {
  display: block;
  padding: 0.75rem 1rem;
  color: var(--text-secondary);
  text-decoration: none;
  font-size: 0.9375rem;
  transition: all 0.2s ease;
  border-radius: 8px;
}

.mobile-submenu-item:hover {
  background: var(--hover-bg);
  color: var(--text-primary);
}

.mobile-login-btn {
  background: var(--accent-color);
  color: white;
  border: none;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  font-size: 1rem;
  transition: opacity 0.2s ease;
  margin-top: 1rem;
}

.mobile-login-btn:hover {
  opacity: 0.8;
}

.main-content {
  flex: 1;
  padding: 6rem 2rem 4rem;
}

.hero-section {
  max-width: 980px;
  margin: 0 auto;
  text-align: center;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 600;
  line-height: 1.1;
  letter-spacing: -1.5px;
  margin-bottom: 1rem;
  color: var(--text-primary);
}

.hero-subtitle {
  font-size: 1.25rem;
  color: var(--text-secondary);
  margin-bottom: 2.5rem;
  line-height: 1.5;
  font-weight: 400;
}

.cta-buttons {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 5rem;
}

.cta-primary {
  background: var(--accent-color);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-weight: 400;
  font-size: 1rem;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

.cta-primary:hover {
  opacity: 0.8;
}

.cta-secondary {
  background: transparent;
  color: var(--accent-color);
  border: 1px solid var(--accent-color);
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-weight: 400;
  font-size: 1rem;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

.cta-secondary:hover {
  opacity: 0.7;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  margin-top: 4rem;
}

.feature-item {
  text-align: center;
  padding: 2rem 1.5rem;
}

.feature-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 1.5rem;
  color: var(--text-primary);
}

.feature-icon svg {
  width: 100%;
  height: 100%;
}

.feature-item h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: var(--text-primary);
}

.feature-item p {
  font-size: 0.9375rem;
  color: var(--text-secondary);
  line-height: 1.5;
}

.footer {
  background: var(--bg-secondary);
  border-top: 1px solid var(--border-color);
  padding: 1rem 2rem;
  margin-top: auto;
}

.footer-container {
  max-width: 1280px;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 2rem;
}

.footer-section {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.footer-label {
  font-size: 0.75rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.footer-options {
  display: flex;
  gap: 0.25rem;
}

.footer-btn {
  background: transparent;
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
  padding: 0.375rem 0.75rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8125rem;
  transition: all 0.2s ease;
  font-weight: 400;
}

.footer-btn:hover {
  border-color: var(--text-primary);
  color: var(--text-primary);
}

.footer-btn.active {
  background: var(--text-primary);
  color: var(--bg-primary);
  border-color: var(--text-primary);
}

.footer-divider {
  width: 1px;
  height: 24px;
  background: var(--border-color);
}
/* respons. */
@media (max-width: 980px) {
  .desktop-nav,
  .desktop-login {
    display: none;
  }

  .mobile-menu-btn {
    display: flex;
  }

  .center-content {
    position: static;
    transform: none;
    gap: 1rem;
  }

  .hero-title {
    font-size: 2.5rem;
  }

  .hero-subtitle {
    font-size: 1.125rem;
  }

  .feature-grid {
    grid-template-columns: 1fr;
    gap: 3rem;
  }

  .cta-buttons {
    flex-direction: column;
    align-items: stretch;
    max-width: 300px;
    margin-left: auto;
    margin-right: auto;
  }

  .footer-container {
    flex-direction: column;
    gap: 1rem;
  }

  .footer-divider {
    width: 100%;
    height: 1px;
  }
}

@media (max-width: 640px) {
  .hero-title {
    font-size: 2rem;
  }

  .main-content {
    padding: 4rem 1.5rem 3rem;
  }
}
</style>