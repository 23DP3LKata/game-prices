<script setup>
import { ref, provide } from 'vue'
import AppHeader from '../components/AppHeader.vue'
import { useThemePreference } from '../composables/useThemePreference'

const selectedCurrency = ref('EUR')
const selectedLanguage = ref('ENG')
const selectedTheme = useThemePreference()

provide('theme', selectedTheme)
</script>

<template>
  <div class="home-page" :class="selectedTheme">
    <AppHeader
      v-model:selectedTheme="selectedTheme"
      v-model:selectedCurrency="selectedCurrency"
      v-model:selectedLanguage="selectedLanguage"
      activePage="home"
    />

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
        <span class="footer-text">&copy; 2025 Game Prices</span>
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
  --bg-primary: #1b1d21;
  --bg-secondary: #25282e;
  --text-primary: #f5f5f7;
  --text-secondary: #a6aab3;
  --border-color: #545a65;
  --hover-bg: #2f333b;
  --accent-color: #2997ff;
}

.home-page {
  background: var(--bg-primary);
  color: var(--text-primary);
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
}

.footer-text {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

/* respons. */
@media (max-width: 980px) {
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