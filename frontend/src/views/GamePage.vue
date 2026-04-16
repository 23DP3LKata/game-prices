<script setup>
import { computed, ref, provide, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { useThemePreference } from '../composables/useThemePreference'

const router = useRouter()
const route = useRoute()

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

const chartLayout = {
  width: 1000,
  height: 320,
  paddingTop: 24,
  paddingRight: 84,
  paddingBottom: 48,
  paddingLeft: 56,
}
const PRICE_CHANGE_EPSILON = 0.0001

const activeChartPointIndex = ref(null)

const toNumber = (value) => {
  if (value === null || value === undefined || value === '') return null
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : null
}

function formatChartPrice(value) {
  return Number(value).toFixed(2)
}

function getNiceIntegerStep(maxValue, targetIntervals = 5) {
  if (!Number.isFinite(maxValue) || maxValue <= 0) {
    return 1
  }

  const roughStep = maxValue / targetIntervals
  const exponent = Math.floor(Math.log10(roughStep))
  const base = 10 ** exponent
  const normalized = roughStep / base

  let multiplier = 10

  if (normalized <= 1) {
    multiplier = 1
  } else if (normalized <= 2) {
    multiplier = 2
  } else if (normalized <= 5) {
    multiplier = 5
  }

  const step = multiplier * base
  return Math.max(1, Math.round(step))
}

function formatTooltipDate(value) {
  if (!value) {
    return 'n/a'
  }

  const parsedDate = new Date(value)
  if (!Number.isNaN(parsedDate.getTime())) {
    return new Intl.DateTimeFormat('en-GB', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    }).format(parsedDate)
  }

  return value
}

function formatDiscountValue(value) {
  if (value === null || value === undefined || Number.isNaN(Number(value))) {
    return '—'
  }

  const parsedValue = Number(value)
  if (parsedValue <= 0) {
    return '—'
  }

  return `-${Math.round(parsedValue)}%`
}

function formatStoreValue(value) {
  if (!value || String(value).trim() === '') {
    return 'Unknown'
  }

  return String(value)
}

const sortedPriceHistory = computed(() => [...priceHistory.value].sort((left, right) => left.date.localeCompare(right.date)))

function normalizeSyncKey(value) {
  if (!value) {
    return ''
  }

  const parsedDate = new Date(value)
  if (!Number.isNaN(parsedDate.getTime())) {
    const year = parsedDate.getUTCFullYear()
    const month = String(parsedDate.getUTCMonth() + 1).padStart(2, '0')
    const day = String(parsedDate.getUTCDate()).padStart(2, '0')
    const hours = String(parsedDate.getUTCHours()).padStart(2, '0')
    const minutes = String(parsedDate.getUTCMinutes()).padStart(2, '0')
    const seconds = String(parsedDate.getUTCSeconds()).padStart(2, '0')

    return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}Z`
  }

  return String(value)
}

const minPricePerSyncHistory = computed(() => {
  const history = sortedPriceHistory.value

  if (history.length === 0) {
    return []
  }

  const syncGroupsMap = new Map()
  const syncOrder = []

  history.forEach((point) => {
    const syncKey = point.syncKey || normalizeSyncKey(point.date)

    if (!syncGroupsMap.has(syncKey)) {
      syncGroupsMap.set(syncKey, {
        syncKey,
        syncDate: point.date,
        points: [],
      })
      syncOrder.push(syncKey)
    }

    const group = syncGroupsMap.get(syncKey)
    group.points.push(point)

    if (point.date > group.syncDate) {
      group.syncDate = point.date
    }
  })

  const syncGroups = syncOrder
    .map((syncKey) => syncGroupsMap.get(syncKey))
    .sort((left, right) => left.syncDate.localeCompare(right.syncDate))

  const latestStoreState = new Map()
  const result = []

  syncGroups.forEach((group) => {
    group.points.forEach((point) => {
      const storeKey = String(point.store || 'unknown').trim().toLowerCase()
      const previousPoint = latestStoreState.get(storeKey)

      if (!previousPoint || point.date >= previousPoint.date) {
        latestStoreState.set(storeKey, point)
      }
    })

    let minPoint = null

    latestStoreState.forEach((point) => {
      if (!minPoint || point.price < minPoint.price - PRICE_CHANGE_EPSILON) {
        minPoint = point
        return
      }

      if (Math.abs(point.price - minPoint.price) <= PRICE_CHANGE_EPSILON && point.date > minPoint.date) {
        minPoint = point
      }
    })

    if (!minPoint) {
      return
    }

    result.push({
      ...minPoint,
      date: group.syncDate,
      syncKey: group.syncKey,
    })
  })

  return result
})

const chartState = computed(() => {
  const points = minPricePerSyncHistory.value

  if (points.length === 0) {
    return {
      points: [],
      ticks: [],
      path: '',
      minPrice: 0,
      maxPrice: 0,
    }
  }

  const prices = points.map((point) => point.price)
  const rawMaxPrice = Math.max(...prices)
  const minPrice = 0
  const tickStep = getNiceIntegerStep(rawMaxPrice)
  const maxPrice = Math.max(tickStep, Math.ceil(rawMaxPrice / tickStep) * tickStep)
  const chartWidth = chartLayout.width - chartLayout.paddingLeft - chartLayout.paddingRight
  const chartHeight = chartLayout.height - chartLayout.paddingTop - chartLayout.paddingBottom
  const denominator = Math.max(maxPrice - minPrice, 1)
  const stepCount = Math.max(points.length - 1, 1)

  const chartPoints = points.map((point, index) => {
    const x = chartLayout.paddingLeft + (chartWidth * index) / stepCount
    const y = chartLayout.paddingTop + chartHeight - ((point.price - minPrice) / denominator) * chartHeight

    return {
      ...point,
      x,
      y,
    }
  })

  let path = ''

  chartPoints.forEach((point, index) => {
    if (index === 0) {
      path = `M ${point.x} ${point.y}`
      return
    }

    path += ` H ${point.x} V ${point.y}`
  })

  const changeMarkers = chartPoints
    .map((point, index) => {
      if (index === 0) {
        return null
      }

      const previousPoint = chartPoints[index - 1]
      const hasPriceChange = Math.abs(previousPoint.price - point.price) > PRICE_CHANGE_EPSILON

      if (!hasPriceChange) {
        return null
      }

      return {
        key: `${point.date}-${point.price}-${index}`,
        x: point.x,
        y1: previousPoint.y,
        y2: point.y,
      }
    })
    .filter(Boolean)

  const tickCount = Math.max(1, Math.round(maxPrice / tickStep))
  const ticks = Array.from({ length: tickCount + 1 }, (_, index) => {
    const value = maxPrice - tickStep * index
    const ratio = 1 - value / Math.max(maxPrice, 1)

    return {
      value: Math.round(value),
      y: chartLayout.paddingTop + chartHeight * ratio,
    }
  })

  return {
    points: chartPoints,
    ticks,
    path,
    minPrice,
    maxPrice,
    chartAreaRight: chartLayout.width - chartLayout.paddingRight,
    changeMarkers,
  }
})

const activeChartPoint = computed(() => {
  if (activeChartPointIndex.value === null) {
    return null
  }

  return chartState.value.points[activeChartPointIndex.value] || null
})

const activeTooltipStyle = computed(() => {
  const point = activeChartPoint.value

  if (!point) {
    return {}
  }

  const leftPercent = Math.min(92, Math.max(8, (point.x / chartLayout.width) * 100))
  const topPercent = Math.min(90, Math.max(8, (point.y / chartLayout.height) * 100))

  return {
    left: `${leftPercent}%`,
    top: `${topPercent}%`,
  }
})

function handleChartHover(event) {
  const points = chartState.value.points

  if (points.length === 0) {
    activeChartPointIndex.value = null
    return
  }

  const target = event.currentTarget
  if (!(target instanceof Element)) {
    return
  }

  const rect = target.getBoundingClientRect()
  if (rect.width <= 0) {
    return
  }

  const relativeX = Math.min(Math.max(event.clientX - rect.left, 0), rect.width)
  const viewBoxX = (relativeX / rect.width) * chartLayout.width

  let nearestIndex = 0
  let nearestDistance = Number.POSITIVE_INFINITY

  points.forEach((point, index) => {
    const distance = Math.abs(point.x - viewBoxX)
    if (distance < nearestDistance) {
      nearestDistance = distance
      nearestIndex = index
    }
  })

  activeChartPointIndex.value = nearestIndex
}

function clearChartHover() {
  activeChartPointIndex.value = null
}

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

    const rawPrices = Array.isArray(data?.prices) ? data.prices : []
    prices.value = rawPrices.map((item) => ({
      store: item?.store?.name || item?.store?.code || 'Unknown',
      price: toNumber(item?.currentPrice ?? item?.price),
      originalPrice: toNumber(item?.originalPrice),
      discount: toNumber(item?.discountPercent ?? item?.discount) ?? 0,
      onSale: Boolean(item?.isOnSale ?? item?.onSale),
      url: item?.storeUrl || item?.url || '#',
      currency: item?.currency || 'EUR',
    })).filter((item) => item.price !== null)

    const rawHistory = Array.isArray(data?.priceHistory) ? data.priceHistory : []
    priceHistory.value = rawHistory.map((point) => ({
      price: toNumber(point?.price),
      date: point?.recordedAt || point?.date || '',
      store: point?.store?.name || point?.store?.code || 'Unknown',
      discount: toNumber(point?.discountPercent ?? point?.discount),
      syncKey: point?.syncLogId
        || point?.sync_log_id
        || point?.syncId
        || point?.sync_id
        || normalizeSyncKey(point?.recordedAt || point?.date || ''),
    })).filter((point) => point.price !== null && point.date)
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
    activeChartPointIndex.value = null
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
            <div v-if="chartState.points.length > 0" class="chart-placeholder">
              <div class="chart-svg-wrap">
                <svg
                  class="price-chart"
                  viewBox="0 0 1000 320"
                  role="img"
                  aria-label="Price history chart"
                  preserveAspectRatio="none"
                  @mousemove="handleChartHover"
                  @mouseleave="clearChartHover"
                >
                  <g v-for="tick in chartState.ticks" :key="tick.value">
                    <line
                      class="chart-grid-line"
                      :x1="chartLayout.paddingLeft"
                      :x2="chartState.chartAreaRight"
                      :y1="tick.y"
                      :y2="tick.y"
                    />
                    <text class="chart-grid-label" :x="chartState.chartAreaRight + 8" :y="tick.y + 4" text-anchor="start">
                      {{ tick.value }}
                    </text>
                  </g>

                  <path class="chart-line" :d="chartState.path" />

                  <line
                    v-for="marker in chartState.changeMarkers"
                    :key="marker.key"
                    class="chart-change-marker"
                    :x1="marker.x"
                    :x2="marker.x"
                    :y1="marker.y1"
                    :y2="marker.y2"
                  />

                  <line
                    v-if="activeChartPoint"
                    class="chart-hover-line"
                    :x1="activeChartPoint.x"
                    :x2="activeChartPoint.x"
                    :y1="chartLayout.paddingTop"
                    :y2="chartLayout.height - chartLayout.paddingBottom"
                  />

                  <circle
                    v-for="point in chartState.points"
                    :key="`${point.date}-${point.price}`"
                    class="chart-point"
                    :cx="point.x"
                    :cy="point.y"
                    r="4.5"
                  />

                  <circle
                    v-if="activeChartPoint"
                    class="chart-point-active"
                    :cx="activeChartPoint.x"
                    :cy="activeChartPoint.y"
                    r="7"
                  />
                </svg>

                <div v-if="activeChartPoint" class="chart-tooltip" :style="activeTooltipStyle">
                  <div class="chart-tooltip-row">
                    <span class="chart-tooltip-label">Store</span>
                    <span>{{ formatStoreValue(activeChartPoint.store) }}</span>
                  </div>
                  <div class="chart-tooltip-row">
                    <span class="chart-tooltip-label">Date</span>
                    <span>{{ formatTooltipDate(activeChartPoint.date) }}</span>
                  </div>
                  <div class="chart-tooltip-row">
                    <span class="chart-tooltip-label">Price</span>
                    <span>€{{ formatChartPrice(activeChartPoint.price) }}</span>
                  </div>
                  <div class="chart-tooltip-row">
                    <span class="chart-tooltip-label">Discount</span>
                    <span>{{ formatDiscountValue(activeChartPoint.discount) }}</span>
                  </div>
                </div>
              </div>

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
  gap: 1rem;
}

.chart-svg-wrap {
  position: relative;
  width: 100%;
  height: 320px;
}

.price-chart {
  width: 100%;
  height: 100%;
  overflow: visible;
}

.chart-grid-line {
  stroke: color-mix(in srgb, var(--text-secondary) 25%, transparent);
  stroke-width: 1;
  stroke-dasharray: 5 6;
}

.chart-grid-label {
  fill: var(--text-secondary);
  font-size: 12px;
  letter-spacing: 0.01em;
}

.chart-line {
  fill: none;
  stroke: var(--accent-color);
  stroke-width: 3.5;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.chart-change-marker {
  stroke: color-mix(in srgb, var(--accent-color) 82%, #ff7a59);
  stroke-width: 4;
  stroke-linecap: round;
}

.chart-hover-line {
  stroke: color-mix(in srgb, var(--accent-color) 55%, transparent);
  stroke-width: 1.5;
  stroke-dasharray: 4 5;
}

.chart-point {
  fill: var(--bg-secondary);
  stroke: var(--accent-color);
  stroke-width: 3;
}

.chart-point-active {
  fill: color-mix(in srgb, var(--accent-color) 25%, var(--bg-secondary));
  stroke: var(--accent-color);
  stroke-width: 3;
}

.chart-tooltip {
  position: absolute;
  transform: translate(-50%, calc(-100% - 14px));
  background: color-mix(in srgb, var(--bg-primary) 84%, var(--bg-secondary));
  border: 1px solid var(--border-color);
  border-radius: 10px;
  padding: 0.6rem 0.7rem;
  min-width: 180px;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
  pointer-events: none;
  z-index: 3;
}

.chart-tooltip-row {
  display: flex;
  justify-content: space-between;
  gap: 0.8rem;
  font-size: 0.75rem;
  color: var(--text-primary);
}

.chart-tooltip-row + .chart-tooltip-row {
  margin-top: 0.35rem;
}

.chart-tooltip-label {
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
}
</style>