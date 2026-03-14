<script setup>
import { ref, provide, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { useThemePreference } from '../composables/useThemePreference'

const router = useRouter()
const route = useRoute()

const selectedCurrency = ref('EUR')
const selectedLanguage = ref('ENG')
const selectedTheme = useThemePreference()
const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')
const isLoading = ref(true)
const loadError = ref('')

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
  loadError.value = ''

  try {
    const response = await fetch(apiBaseUrl ? `${apiBaseUrl}/games/${encodeURIComponent(id)}` : `/api/games/${encodeURIComponent(id)}`, {
      headers: {
        Accept: 'application/json',
      },
    })

    const data = await response.json().catch(() => null)

    if (response.status === 404) {
      throw new Error('Game not found.')
    }

    if (!response.ok) {
      throw new Error(data?.message || 'Unable to load game data right now.')
    }

    const payload = data?.game ?? {}

    game.value = {
      id: payload.id ?? null,
      name: payload.name ?? 'Unknown game',
      description: payload.description ?? '',
      image: payload.image ?? '',
      genre: payload.genre ?? '',
      releaseDate: payload.releaseDate ?? '',
      developer: payload.developer ?? '',
      publisher: payload.publisher ?? '',
    }

    // Prices will be connected to external providers in a later iteration.
    prices.value = Array.isArray(data?.prices) ? data.prices : []
    priceHistory.value = Array.isArray(data?.priceHistory) ? data.priceHistory : []
  } catch (err) {
    loadError.value = err instanceof Error ? err.message : 'Unable to load game data right now.'
    game.value = {
      id: null,
      name: '',
      description: '',
      image: '',
      genre: '',
      releaseDate: '',
      developer: '',
      publisher: '',
    }
    prices.value = []
    priceHistory.value = []
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchGameData(route.params.id)
})

watch(
  () => route.params.id,
  (gameId) => {
    if (gameId) {
      fetchGameData(gameId)
    }
  },
)

const goToGames = () => {
  router.push('/games')
}

const getBestPrice = () => {
  if (prices.value.length === 0) return null
  return prices.value.reduce((min, p) => p.price < min.price ? p : min, prices.value[0])
}
</script>

<template>
  <div class="game-page" :class="selectedTheme">
    <AppHeader
      v-model:selectedTheme="selectedTheme"
      v-model:selectedCurrency="selectedCurrency"
      v-model:selectedLanguage="selectedLanguage"
      activePage=""
    />

    <main class="main-content">
      <!-- Loading state -->
      <div v-if="isLoading" class="loading-state">
        <div class="loading-spinner"></div>
        <p>Loading game data...</p>
      </div>

      <div v-else-if="loadError" class="loading-state">
        <p>{{ loadError }}</p>
        <button class="breadcrumb-link" @click="goToGames">Back to games</button>
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
  --bg-primary: #1b1d21;
  --bg-secondary: #25282e;
  --text-primary: #f5f5f7;
  --text-secondary: #a6aab3;
  --border-color: #545a65;
  --hover-bg: #2f333b;
  --accent-color: #2997ff;
}

.game-page {
  background: var(--bg-primary);
  color: var(--text-primary);
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
}

.footer-text {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

/* responsive */
@media (max-width: 980px) {
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