<script setup>
import { ref, provide } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isServicesOpen = ref(false)
const isMobileMenuOpen = ref(false)
const isMobileServicesOpen = ref(false)
const selectedCurrency = ref('USD')
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

const goToGames = () => {
  router.push('/games')
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

const sections = [
  { id: 'what-is', title: 'What is GamePrices?' },
  { id: 'why', title: 'Why was it created?' },
  { id: 'data', title: 'Data collection' },
  { id: 'reliability', title: 'Reliability' },
  { id: 'audience', title: 'Audience' },
  { id: 'features', title: 'Registered features' },
  { id: 'history', title: 'Price history' },
  { id: 'sync', title: 'Data freshness' },
  { id: 'mission', title: 'Mission' }
]
</script>

<template>
  <div class="about-page" :class="selectedTheme">
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
            <button class="nav-btn" @click="goToGames">Games</button>
            <button class="nav-btn active">About us</button>
            
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
          <button class="mobile-nav-btn" @click="goToGames">Games</button>
          <button class="mobile-nav-btn active" @click="closeMobileMenu">About us</button>
          
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
      <div class="content-layout">
        <article class="about-article">
          <header class="section-header">
            <h1>About Game Prices</h1>
            <p class="section-subtitle">Discover how the platform works behind the scenes and who it is built for.</p>
          </header>

          <div class="article-content">
            <section id="what-is">
              <h2>What is GamePrices?</h2>
              <p>GamePrices is a data-driven platform that compares game prices across multiple digital stores, providing real-time information, historical analytics, and automated deal tracking.</p>
            </section>

            <section id="why">
              <h2>Why was GamePrices created?</h2>
              <p>The platform was built to solve a common problem: players waste time checking different stores manually, comparing prices, and searching for real discounts.</p>
            </section>

            <section id="data">
              <h2>How does GamePrices collect pricing data?</h2>
              <p>GamePrices integrates with public APIs from Steam, Epic Games, GG.deals, IsThereAnyDeal, and other sources to automatically gather and update accurate pricing information.</p>
            </section>

            <section id="reliability">
              <h2>What makes GamePrices reliable?</h2>
              <p>The system uses a structured ER data model, automated synchronization services, and detailed validation to ensure price accuracy, data consistency, and stable performance.</p>
            </section>

            <section id="audience">
              <h2>Who is GamePrices for?</h2>
              <p>GamePrices is designed for all types of users - guests who want quick price checks, registered users who track wishlists and receive alerts, and administrators who maintain data quality.</p>
            </section>

            <section id="features">
              <h2>What features do registered users get?</h2>
              <p>Registered users can save games to a wishlist, receive automatic notifications about price drops, customize their profile, and report issues directly to administrators.</p>
            </section>

            <section id="history">
              <h2>Does GamePrices show price history?</h2>
              <p>Yes. The platform provides clear visual graphs showing historical price changes, helping users analyze trends and buy at the best moment.</p>
            </section>

            <section id="sync">
              <h2>How does GamePrices ensure data stays up to date?</h2>
              <p>A built-in DataSyncService regularly pulls fresh data from external APIs, updates local records, and logs errors to maintain system accuracy and reliability.</p>
            </section>

            <section id="mission">
              <h2>What is your mission?</h2>
              <p>Our mission is to make the game market transparent, efficient, and easy to understand, helping every player save time, save money, and make smarter purchasing decisions.</p>
            </section>
          </div>
        </article>

        <aside class="sidebar desktop-sidebar">
          <div class="sidebar-content">
            <h3>On this page</h3>
            <nav class="sidebar-nav">
              <ul>
                <li v-for="section in sections" :key="section.id">
                  <a :href="`#${section.id}`">{{ section.title }}</a>
                </li>
              </ul>
            </nav>
          </div>
        </aside>
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

.about-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  transition: background-color 0.3s ease, color 0.3s ease;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
}

.about-page.light {
  --bg-primary: #ffffff;
  --bg-secondary: #f5f5f7;
  --text-primary: #1d1d1f;
  --text-secondary: #86868b;
  --border-color: #d2d2d7;
  --hover-bg: #f5f5f7;
  --accent-color: #0071e3;
}

.about-page.dark {
  --bg-primary: #000000;
  --bg-secondary: #1d1d1f;
  --text-primary: #f5f5f7;
  --text-secondary: #86868b;
  --border-color: #424245;
  --hover-bg: #1d1d1f;
  --accent-color: #2997ff;
}

.about-page {
  background: var(--bg-primary);
  color: var(--text-primary);
}

/* header */
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

.about-page.light .header {
  --bg-primary-rgb: 255, 255, 255;
}

.about-page.dark .header {
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

.nav-btn.active {
  font-weight: 500;
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

.mobile-nav-btn.active {
  font-weight: 500;
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

/* main */
.main-content {
  flex: 1;
  padding: 3rem 2rem;
}

.content-layout {
  max-width: 1280px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 4rem;
}

.about-article {
  max-width: 720px;
}

.section-header {
  margin-bottom: 3rem;
}

.section-header h1 {
  font-size: 3rem;
  font-weight: 600;
  line-height: 1.1;
  letter-spacing: -1px;
  margin-bottom: 1rem;
  color: var(--text-primary);
}

.section-subtitle {
  font-size: 1.25rem;
  color: var(--text-secondary);
  line-height: 1.5;
  font-weight: 400;
}

.article-content section {
  margin-bottom: 3rem;
  scroll-margin-top: 80px;
}

.article-content h2 {
  font-size: 1.75rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--text-primary);
  line-height: 1.3;
}

.article-content p {
  font-size: 1.0625rem;
  color: var(--text-secondary);
  line-height: 1.7;
  font-weight: 400;
}

/* sidebar */
.sidebar {
  position: sticky;
  top: 80px;
  align-self: start;
  height: fit-content;
}

.sidebar-content {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1.5rem;
}

.sidebar-content h3 {
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--text-primary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.sidebar-nav ul {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.sidebar-nav a {
  color: var(--text-secondary);
  text-decoration: none;
  font-size: 0.875rem;
  transition: color 0.2s ease;
  display: block;
  padding: 0.25rem 0;
}

.sidebar-nav a:hover {
  color: var(--text-primary);
}

/* footer */
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

/* resposiv */
@media (max-width: 1024px) {
  .content-layout {
    grid-template-columns: 1fr;
    gap: 2rem;
  }

  .desktop-sidebar {
    display: none;
  }
}

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

  .section-header h1 {
    font-size: 2.5rem;
  }

  .section-subtitle {
    font-size: 1.125rem;
  }

  .article-content h2 {
    font-size: 1.5rem;
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
  .main-content {
    padding: 2rem 1.5rem;
  }

  .section-header h1 {
    font-size: 2rem;
  }

  .section-header {
    margin-bottom: 2rem;
  }

  .article-content section {
    margin-bottom: 2rem;
  }

  .article-content h2 {
    font-size: 1.25rem;
  }

  .article-content p {
    font-size: 1rem;
  }
}
</style>