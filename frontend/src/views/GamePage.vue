<script setup>
import { ref, provide, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const isServicesOpen = ref(false)
const isMobileMenuOpen = ref(false)
const isMobileServicesOpen = ref(false)
const selectedCurrency = ref('EUR')
const selectedLanguage = ref('ENG')
const selectedTheme = ref('light')
const isLoading = ref(true)

provide('theme', selectedTheme)

const game = ref({
  id: null,
  name: '',
  description: '',
  image: '',
  genre: '',
  releaseDate: '',
  developer: '',
  publisher: '',
})

const prices = ref([])

const priceHistory = ref([])

async function fetchGameData(id) {
  isLoading.value = true
  // TODO: Replace with real API call, e.g.:
  // const response = await fetch(`/api/games/${id}`)
  // const data = await response.json()
  // game.value = data.game
  // prices.value = data.prices
  // priceHistory.value = data.priceHistory

  // Placeholder data for development
  game.value = {
    id: id,
    name: 'Game Title Placeholder',
    description: 'This is a placeholder description for the game. It will be replaced with real data from the database when the API is connected. The game offers an immersive experience with stunning visuals and engaging gameplay mechanics.',
    image: '',
    genre: 'Action, Adventure',
    releaseDate: '2025-01-15',
    developer: 'Studio Placeholder',
    publisher: 'Publisher Placeholder',
  }

  prices.value = [
    { store: 'Steam', price: 59.99, currency: 'EUR', url: '#', onSale: false, discount: 0 },
    { store: 'Epic Games', price: 54.99, currency: 'EUR', url: '#', onSale: true, discount: 8 },
    { store: 'GOG', price: 59.99, currency: 'EUR', url: '#', onSale: false, discount: 0 },
    { store: 'PlayStation Store', price: 69.99, currency: 'EUR', url: '#', onSale: false, discount: 0 },
    { store: 'Xbox Store', price: 69.99, currency: 'EUR', url: '#', onSale: false, discount: 0 },
  ]

  priceHistory.value = [
    { date: '2025-01-15', price: 59.99 },
    { date: '2025-02-10', price: 49.99 },
    { date: '2025-03-05', price: 59.99 },
    { date: '2025-04-20', price: 39.99 },
    { date: '2025-05-15', price: 59.99 },
  ]

  isLoading.value = false
}

onMounted(() => {
  const gameId = route.params.id
  fetchGameData(gameId)
})

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

const goToAbout = () => {
  router.push('/about')
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

const getBestPrice = () => {
  if (prices.value.length === 0) return null
  return prices.value.reduce((min, p) => p.price < min.price ? p : min, prices.value[0])
}
</script>

<template>
  <div class="game-page" :class="selectedTheme">
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
            <button class="nav-btn" @click="goToAbout">About us</button>
            
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
          <button class="mobile-nav-btn" @click="goToAbout">About us</button>
          
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
      <!-- Loading state -->
      <div v-if="isLoading" class="loading-state">
        <div class="loading-spinner"></div>
        <p>Loading game data...</p>
      </div>

      <!-- Game content -->
      <div v-else class="content-wrapper">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
          <button class="breadcrumb-link" @click="goToGames">Games</button>
          <span class="breadcrumb-separator">/</span>
          <span class="breadcrumb-current">{{ game.name }}</span>
        </nav>

        <!-- Game Hero Section -->
        <div class="game-hero">
          <div class="game-image-container">
            <img 
              v-if="game.image" 
              :src="game.image" 
              :alt="game.name" 
              class="game-image" 
            />
            <div v-else class="game-image-placeholder">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
            </div>
          </div>

          <div class="game-details">
            <h1 class="game-title">{{ game.name }}</h1>
            
            <div class="game-meta">
              <div class="meta-item" v-if="game.genre">
                <span class="meta-label">Genre</span>
                <span class="meta-value">{{ game.genre }}</span>
              </div>
              <div class="meta-item" v-if="game.developer">
                <span class="meta-label">Developer</span>
                <span class="meta-value">{{ game.developer }}</span>
              </div>
              <div class="meta-item" v-if="game.publisher">
                <span class="meta-label">Publisher</span>
                <span class="meta-value">{{ game.publisher }}</span>
              </div>
              <div class="meta-item" v-if="game.releaseDate">
                <span class="meta-label">Release Date</span>
                <span class="meta-value">{{ game.releaseDate }}</span>
              </div>
            </div>

            <div class="best-price-badge" v-if="getBestPrice()">
              <span class="best-price-label">Best price</span>
              <span class="best-price-value">€{{ getBestPrice().price.toFixed(2) }}</span>
              <span class="best-price-store">on {{ getBestPrice().store }}</span>
            </div>
          </div>
        </div>

        <!-- Description -->
        <section class="section">
          <h2 class="section-title">About this game</h2>
          <p class="game-description">{{ game.description }}</p>
        </section>

        <!-- Price Comparison Table -->
        <section class="section">
          <h2 class="section-title">Price Comparison</h2>
          <div class="table-container">
            <table class="prices-table">
              <thead>
                <tr>
                  <th class="col-store">Store</th>
                  <th class="col-price">Price</th>
                  <th class="col-discount">Discount</th>
                  <th class="col-action">Link</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in prices" :key="index" :class="{ 'best-row': getBestPrice() && item.price === getBestPrice().price }">
                  <td class="cell-store">{{ item.store }}</td>
                  <td class="cell-price" :class="{ 'on-sale': item.onSale }">
                    €{{ item.price.toFixed(2) }}
                  </td>
                  <td class="cell-discount">
                    <span v-if="item.discount > 0" class="discount-badge">-{{ item.discount }}%</span>
                    <span v-else class="no-discount">—</span>
                  </td>
                  <td class="cell-action">
                    <a :href="item.url" target="_blank" rel="noopener noreferrer" class="store-link">Visit Store</a>
                  </td>
                </tr>
                <tr v-if="prices.length === 0">
                  <td colspan="4" class="empty-state">No price data available</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Price History Chart -->
        <section class="section">
          <h2 class="section-title">Price History</h2>
          <div class="chart-container">
            <div v-if="priceHistory.length > 0" class="chart-placeholder">
              <div class="chart-bars">
                <div 
                  v-for="(point, index) in priceHistory" 
                  :key="index" 
                  class="chart-bar-group"
                >
                  <div class="chart-bar" :style="{ height: (point.price / 70 * 100) + '%' }">
                    <span class="chart-bar-value">€{{ point.price.toFixed(2) }}</span>
                  </div>
                  <span class="chart-bar-label">{{ point.date.slice(5) }}</span>
                </div>
              </div>
              <p class="chart-note">A full interactive chart will be available when connected to the database.</p>
            </div>
            <div v-else class="chart-empty">
              <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
              </svg>
              <p>No price history available yet</p>
            </div>
          </div>
        </section>
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

.game-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  transition: background-color 0.3s ease, color 0.3s ease;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
}

.game-page.light {
  --bg-primary: #ffffff;
  --bg-secondary: #f5f5f7;
  --text-primary: #1d1d1f;
  --text-secondary: #86868b;
  --border-color: #d2d2d7;
  --hover-bg: #f5f5f7;
  --accent-color: #0071e3;
}

.game-page.dark {
  --bg-primary: #000000;
  --bg-secondary: #1d1d1f;
  --text-primary: #f5f5f7;
  --text-secondary: #86868b;
  --border-color: #424245;
  --hover-bg: #1d1d1f;
  --accent-color: #2997ff;
}

.game-page {
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

.game-page.light .header {
  --bg-primary-rgb: 255, 255, 255;
}

.game-page.dark .header {
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

/* mobile menu */
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

/* loading */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 6rem 2rem;
  gap: 1.5rem;
}

.loading-spinner {
  width: 36px;
  height: 36px;
  border: 3px solid var(--border-color);
  border-top-color: var(--accent-color);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-state p {
  font-size: 1rem;
  color: var(--text-secondary);
}

/* main */
.main-content {
  flex: 1;
  padding: 2rem 2rem 4rem;
}

.content-wrapper {
  max-width: 980px;
  margin: 0 auto;
}

/* breadcrumb */
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 2rem;
  font-size: 0.875rem;
}

.breadcrumb-link {
  background: none;
  border: none;
  color: var(--accent-color);
  cursor: pointer;
  font-size: 0.875rem;
  padding: 0;
  transition: opacity 0.2s ease;
}

.breadcrumb-link:hover {
  opacity: 0.7;
}

.breadcrumb-separator {
  color: var(--text-secondary);
}

.breadcrumb-current {
  color: var(--text-secondary);
}

/* game hero */
.game-hero {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 2.5rem;
  margin-bottom: 3rem;
}

.game-image-container {
  aspect-ratio: 3 / 4;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid var(--border-color);
  background: var(--bg-secondary);
}

.game-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.game-image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
  opacity: 0.4;
}

.game-image-placeholder svg {
  width: 64px;
  height: 64px;
}

.game-details {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  gap: 1.5rem;
}

.game-title {
  font-size: 2.5rem;
  font-weight: 600;
  line-height: 1.15;
  letter-spacing: -0.5px;
  color: var(--text-primary);
}

.game-meta {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.meta-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.meta-value {
  font-size: 0.9375rem;
  color: var(--text-primary);
}

.best-price-badge {
  display: inline-flex;
  align-items: baseline;
  gap: 0.5rem;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 0.75rem 1rem;
  width: fit-content;
}

.best-price-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.best-price-value {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--accent-color);
}

.best-price-store {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

/* sections */
.section {
  margin-bottom: 3rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1.25rem;
  color: var(--text-primary);
  letter-spacing: -0.25px;
}

.game-description {
  font-size: 1.0625rem;
  color: var(--text-secondary);
  line-height: 1.7;
}

/* price table */
.table-container {
  border: 1px solid var(--border-color);
  border-radius: 12px;
  overflow: hidden;
}

.prices-table {
  width: 100%;
  border-collapse: collapse;
}

.prices-table thead {
  background: var(--bg-secondary);
}

.prices-table th {
  padding: 0.875rem 1.5rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid var(--border-color);
}

.prices-table td {
  padding: 1rem 1.5rem;
  font-size: 0.9375rem;
  color: var(--text-primary);
  border-bottom: 1px solid var(--border-color);
}

.prices-table tbody tr:last-child td {
  border-bottom: none;
}

.prices-table tbody tr:hover {
  background: var(--hover-bg);
}

.best-row {
  background: var(--hover-bg);
}

.col-store {
  width: 35%;
}

.col-price {
  width: 25%;
}

.col-discount {
  width: 20%;
}

.col-action {
  width: 20%;
}

.cell-store {
  font-weight: 500;
}

.cell-price.on-sale {
  color: var(--accent-color);
  font-weight: 600;
}

.discount-badge {
  display: inline-block;
  background: var(--accent-color);
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
}

.no-discount {
  color: var(--text-secondary);
}

.store-link {
  color: var(--accent-color);
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  transition: opacity 0.2s ease;
}

.store-link:hover {
  opacity: 0.7;
}

.empty-state {
  text-align: center;
  padding: 3rem 1.5rem !important;
  color: var(--text-secondary);
}

/* price history chart */
.chart-container {
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 2rem;
  background: var(--bg-secondary);
}

.chart-placeholder {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.chart-bars {
  display: flex;
  align-items: flex-end;
  justify-content: space-around;
  height: 200px;
  gap: 1rem;
  padding-bottom: 0.5rem;
}

.chart-bar-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
  height: 100%;
  justify-content: flex-end;
}

.chart-bar {
  width: 100%;
  max-width: 60px;
  background: var(--accent-color);
  border-radius: 6px 6px 0 0;
  opacity: 0.8;
  position: relative;
  min-height: 20px;
  transition: opacity 0.2s ease;
  display: flex;
  align-items: flex-start;
  justify-content: center;
}

.chart-bar:hover {
  opacity: 1;
}

.chart-bar-value {
  font-size: 0.6875rem;
  font-weight: 600;
  color: white;
  padding-top: 0.375rem;
  white-space: nowrap;
}

.chart-bar-label {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.chart-note {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  text-align: center;
  font-style: italic;
}

.chart-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 3rem;
}

.chart-empty .empty-icon {
  width: 48px;
  height: 48px;
  color: var(--text-secondary);
  opacity: 0.5;
}

.chart-empty p {
  font-size: 1rem;
  color: var(--text-secondary);
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

/* responsive */
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

  .game-hero {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .game-image-container {
    max-width: 280px;
    margin: 0 auto;
  }

  .game-title {
    font-size: 2rem;
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
    padding: 1.5rem 1.5rem 3rem;
  }

  .game-title {
    font-size: 1.75rem;
  }

  .game-meta {
    grid-template-columns: 1fr;
  }

  .section-title {
    font-size: 1.25rem;
  }

  .prices-table th,
  .prices-table td {
    padding: 0.75rem 1rem;
    font-size: 0.8125rem;
  }

  .chart-bars {
    height: 150px;
  }

  .chart-bar-value {
    font-size: 0.625rem;
  }
}
</style>