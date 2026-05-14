<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, provide, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { Chart, registerables } from 'chart.js'
import AppHeader from '../components/AppHeader.vue'
import { useThemePreference } from '../composables/useThemePreference'
import { formatDateOnly } from '../composables/useDateTimeFormat'
import { useI18nStore } from '../stores/i18n'

Chart.register(...registerables)

const route = useRoute()
const router = useRouter()
const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')
const selectedLanguage = ref('LV')
const selectedTheme = useThemePreference()
const i18n = useI18nStore()

provide('theme', selectedTheme)

const currencyFormatter = new Intl.NumberFormat('de-DE', {
  style: 'currency',
  currency: 'EUR',
})

const periodOrder = ['3m', '6m', '1y', 'all']

const selectedPeriod = ref('all')
const chartCanvas = ref(null)
const chartInstance = ref(null)
const isLoading = ref(true)
const loadError = ref('')
const wishlistAdded = ref(false)
const game = ref({
  title: '',
  genre: '',
  developer: '',
  release_date: '',
  playtime_min: 0,
  playtime_max: 0,
  age_rating: '',
  languages: [],
  cover_image: '',
  platforms: [],
  tags: [],
  description: '',
})
const stores = ref([])
const priceHistory = ref({
  '3m': [],
  '6m': [],
  '1y': [],
  all: [],
})

const platformPalette = ['#a78bfa', '#7c3aed', '#c4b5fd', '#8b5cf6', '#ddd6fe']

const currentHistory = computed(() => priceHistory.value[selectedPeriod.value] ?? [])
const allTimeHistory = computed(() => priceHistory.value.all ?? [])
const sortedStores = computed(() => {
  return [...stores.value].sort((left, right) => left.current_price_eur - right.current_price_eur)
})

const bestStorePrice = computed(() => sortedStores.value[0] ?? null)

const minAllTimePrice = computed(() => {
  const values = allTimeHistory.value.map((point) => point.price).filter((price) => Number.isFinite(price))

  if (values.length === 0) {
    return null
  }

  return Math.min(...values)
})

const displayedCurrentPrice = computed(() => {
  const history = currentHistory.value

  if (history.length === 0) {
    return null
  }

  return history[history.length - 1]?.price ?? null
})

const genreList = computed(() => {
  const rawGenre = String(game.value.genre || '').trim()

  if (!rawGenre) {
    return []
  }

  return rawGenre
    .split(/,|\//)
    .map((item) => item.trim())
    .filter(Boolean)
})

const overviewTags = computed(() => {
  const customTags = Array.isArray(game.value.tags) ? game.value.tags.filter(Boolean) : []
  if (customTags.length > 0) {
    return customTags
  }

  return genreList.value
})

function formatPrice(value) {
  return currencyFormatter.format(Number(value) || 0)
}

function formatReleaseYear(value) {
  if (!value) {
    return i18n.t('na')
  }

  const parsedDate = new Date(value)
  if (!Number.isNaN(parsedDate.getTime())) {
    return String(parsedDate.getFullYear())
  }

  return String(value).slice(0, 4)
}

function formatPlaytime(minValue, maxValue) {
  if (!Number.isFinite(minValue) && !Number.isFinite(maxValue)) {
    return i18n.t('na')
  }

  const formatHours = (minutes) => `${Math.max(1, Math.round(minutes / 60))}h`

  if (!Number.isFinite(maxValue) || maxValue <= 0) {
    return formatHours(minValue)
  }

  if (!Number.isFinite(minValue) || minValue <= 0) {
    return formatHours(maxValue)
  }

  return `${formatHours(minValue)} - ${formatHours(maxValue)}`
}

function formatDate(value) {
  if (!value) {
    return i18n.t('na')
  }

  const formatted = formatDateOnly(value, undefined, selectedLanguage.value)
  return formatted || String(value)
}

function getPlatformColor(index) {
  return platformPalette[index % platformPalette.length]
}

function getStoreInitial(storeName) {
  const firstCharacter = String(storeName || '?').trim().charAt(0)
  return firstCharacter ? firstCharacter.toUpperCase() : '?'
}

function getDiscountTone(discount) {
  if (discount >= 25) {
    return 'strong'
  }

  if (discount >= 10) {
    return 'medium'
  }

  return 'neutral'
}

function openStore(url) {
  if (!url) {
    return
  }

  window.open(url, '_blank', 'noopener,noreferrer')
}

function normalizePeriodHistory(history) {
  const sortedHistory = [...history].sort((left, right) => left.date.localeCompare(right.date))

  if (sortedHistory.length === 0) {
    return {
      '3m': [],
      '6m': [],
      '1y': [],
      all: [],
    }
  }

  const latestDate = new Date(sortedHistory[sortedHistory.length - 1].date)

  const filterSince = (months) => {
    const cutoffDate = new Date(latestDate)
    cutoffDate.setMonth(cutoffDate.getMonth() - months)

    return sortedHistory.filter((point) => new Date(point.date) >= cutoffDate)
  }

  return {
    '3m': filterSince(3),
    '6m': filterSince(6),
    '1y': filterSince(12),
    all: sortedHistory,
  }
}

function getRouteSlug() {
  const slug = route.params.slug
  return Array.isArray(slug) ? slug[0] : slug
}

async function fetchGameData(slug) {
  isLoading.value = true
  loadError.value = ''

  try {
    const response = await fetch(apiBaseUrl ? `${apiBaseUrl}/games/${encodeURIComponent(slug)}` : `/api/games/${encodeURIComponent(slug)}`, {
      headers: {
        Accept: 'application/json',
      },
    })

    const data = await response.json().catch(() => null)

    if (response.status === 404) {
      throw new Error(i18n.t('game.errors.not_found'))
    }

    if (!response.ok) {
      throw new Error(data?.message || i18n.t('game.errors.load_failed'))
    }

    const payload = data?.game ?? {}
    game.value = {
      title: payload.name ?? i18n.t('game.untitled'),
      genre: payload.genre ?? '',
      developer: payload.developer ?? '',
      release_date: payload.releaseDate ?? '',
      playtime_min: 0,
      playtime_max: 0,
      age_rating: '',
      languages: [],
      cover_image: payload.image ?? '',
      platforms: [],
      tags: [],
      description: payload.description ?? '',
    }

    // initialize wishlist state if backend provides it
    wishlistAdded.value = Boolean(data?.game?.is_wishlisted ?? data?.game?.wishlisted ?? data?.is_wishlisted ?? data?.wishlisted ?? false)

    const rawPrices = Array.isArray(data?.prices) ? data.prices : []
    stores.value = rawPrices.map((item) => ({
      store_name: item?.store?.name || item?.store?.code || i18n.t('game.unknown_store'),
      store_slug: String(item?.store?.code || item?.store?.name || 'store').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, ''),
      base_price_eur: Number(item?.originalPrice ?? item?.currentPrice ?? 0),
      current_price_eur: Number(item?.currentPrice ?? 0),
      discount_percent: Number(item?.discountPercent ?? 0),
      url: item?.storeUrl || '',
    })).filter((item) => Number.isFinite(item.current_price_eur))

    const rawHistory = Array.isArray(data?.priceHistory) ? data.priceHistory : []
    const normalizedHistory = rawHistory
      .map((point) => ({
        date: point?.recordedAt || '',
        price: Number(point?.price),
        store: point?.store?.name || point?.store?.code || 'Unknown',
      }))
      .filter((point) => point.date && Number.isFinite(point.price))

    priceHistory.value = normalizePeriodHistory(normalizedHistory)

    await nextTick()
    buildChart()
  } catch (err) {
    loadError.value = err instanceof Error ? err.message : i18n.t('game.errors.load_failed')
    game.value = {
      title: '',
      genre: '',
      developer: '',
      release_date: '',
      playtime_min: 0,
      playtime_max: 0,
      age_rating: '',
      languages: [],
      cover_image: '',
      platforms: [],
      tags: [],
      description: '',
    }
    stores.value = []
    priceHistory.value = {
      '3m': [],
      '6m': [],
      '1y': [],
      all: [],
    }
  } finally {
    isLoading.value = false
  }
}

const authStore = useAuthStore()

async function toggleWishlist() {
  const slug = getRouteSlug()

  if (!authStore.isLoggedIn) {
    router.push('/login')
    return
  }

  try {
    const response = await fetch(apiBaseUrl ? `${apiBaseUrl}/wishlist/${encodeURIComponent(slug)}` : `/api/wishlist/${encodeURIComponent(slug)}`, {
      method: 'POST',
      headers: { Accept: 'application/json' },
      credentials: 'include',
    })

    if (response.status === 401) {
      router.push('/login')
      return
    }

    if (!response.ok) {
      const json = await response.json().catch(() => null)
      console.error('Wishlist error', json)
      return
    }

    // Toggle local state on success (backend toggles)
    wishlistAdded.value = !wishlistAdded.value
  } catch (err) {
    console.error('Wishlist network error', err)
  }
}

let buildChartTimeout = null

function buildChart() {
  if (buildChartTimeout) {
    clearTimeout(buildChartTimeout)
  }

  buildChartTimeout = setTimeout(() => {
    if (chartInstance.value) {
      chartInstance.value.destroy()
      chartInstance.value = null
    }

    if (!chartCanvas.value) {
      return
    }

    const history = currentHistory.value

    if (history.length === 0) {
      return
    }

    const ctx = chartCanvas.value?.getContext('2d')
    if (!ctx) {
      return
    }

    const gradient = ctx.createLinearGradient(0, 0, 0, 200)
    gradient.addColorStop(0, 'rgba(167, 139, 250, 0.2)')
    gradient.addColorStop(1, 'rgba(167, 139, 250, 0)')
    const isDarkTheme = selectedTheme.value === 'dark'
    const tickColor = isDarkTheme ? '#a1a1aa' : '#6b7280'
    const gridColor = isDarkTheme ? 'rgba(255, 255, 255, 0.08)' : 'rgba(17, 24, 39, 0.08)'
    const tooltipBackground = isDarkTheme ? '#18181b' : '#ffffff'
    const tooltipBorder = isDarkTheme ? 'rgba(255, 255, 255, 0.12)' : 'rgba(17, 24, 39, 0.12)'
    const tooltipTitle = isDarkTheme ? '#f4f4f5' : '#111827'
    const tooltipBody = isDarkTheme ? '#f4f4f5' : '#111827'

    chartInstance.value = new Chart(ctx, {
      type: 'line',
      data: {
        labels: history.map((point) => formatDate(point.date)),
        datasets: [
          {
            data: history.map((point) => point.price),
            borderColor: '#a78bfa',
            backgroundColor: gradient,
            borderWidth: 2,
            fill: true,
            tension: 0.35,
            pointRadius: 4,
            pointHoverRadius: 5,
            pointBackgroundColor: '#a78bfa',
            pointBorderColor: '#a78bfa',
            pointBorderWidth: 0,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 0,
        },
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            enabled: true,
            backgroundColor: tooltipBackground,
            borderColor: tooltipBorder,
            borderWidth: 1,
            titleColor: tooltipTitle,
            bodyColor: tooltipBody,
            displayColors: false,
            padding: 12,
            callbacks: {
              label(context) {
                return formatPrice(context.parsed.y)
              },
              afterBody(items) {
                const point = history[items?.[0]?.dataIndex]
                return point?.store ? `Store: ${point.store}` : ''
              },
            },
          },
        },
        scales: {
          x: {
            grid: {
              display: false,
              drawBorder: false,
            },
            ticks: {
              color: tickColor,
              font: {
                size: 12,
              },
            },
          },
          y: {
            grid: {
              color: gridColor,
              drawBorder: false,
            },
            ticks: {
              color: tickColor,
              font: {
                size: 12,
              },
              callback(value) {
                return formatPrice(value)
              },
            },
          },
        },
      },
    })
  }, 0)
}

watch([selectedPeriod, currentHistory, selectedTheme], async () => {
  await nextTick()
  buildChart()
})

onMounted(async () => {
  const slug = getRouteSlug()

  if (typeof slug !== 'string' || slug.trim() === '') {
    loadError.value = i18n.t('game.errors.missing_slug')
    isLoading.value = false
    return
  }

  await fetchGameData(slug)
})

watch(getRouteSlug, async (slug, previousSlug) => {
  if (slug && slug !== previousSlug) {
    await fetchGameData(slug)
  }
})

onBeforeUnmount(() => {
  if (buildChartTimeout) {
    clearTimeout(buildChartTimeout)
  }
  if (chartInstance.value) {
    chartInstance.value.destroy()
    chartInstance.value = null
  }
})
</script>

<template>
  <div class="game-page" :class="selectedTheme">
    <AppHeader
      v-model:selectedTheme="selectedTheme"
      v-model:selectedLanguage="selectedLanguage"
      activePage=""
    />

    <main class="page-shell">
      <div v-if="isLoading" class="loading-state">
        <div class="skeleton-chart"></div>
        <div class="skeleton-row"></div>
      </div>

      <div v-else-if="loadError" class="empty-state">
        <div class="skeleton-row"></div>
        <button type="button" class="store-button primary" @click="router.push('/games')">{{ i18n.t('back_to_games') }}</button>
      </div>

      <template v-else>
        <section class="hero-card">
          <template v-if="game.cover_image">
            <img :src="game.cover_image" :alt="game.title" class="hero-image" />

            <button
              type="button"
              class="wishlist-toggle"
              :class="{ active: wishlistAdded }"
              @click="toggleWishlist"
              :aria-pressed="wishlistAdded"
              :title="wishlistAdded ? i18n.t('game.remove_wishlist') : i18n.t('game.add_wishlist')"
            >
              <span class="heart">{{ wishlistAdded ? '♥' : '♡' }}</span>
            </button>

            <div class="hero-content">
              <h1 class="game-title">{{ game?.title || i18n.t('game.untitled') }}</h1>
              <p class="hero-meta">
                <span>{{ formatPlaytime(game?.playtime_min, game?.playtime_max) }}</span>
                <span>{{ game?.developer || i18n.t('na') }}</span>
                <span>{{ formatReleaseYear(game?.release_date) }}</span>
              </p>
            </div>
          </template>

          <div v-else class="hero-skeleton">
            <div class="skeleton-line skeleton-title"></div>
            <div class="skeleton-line skeleton-subtitle"></div>
          </div>
        </section>

        <section class="details-grid">
          <article class="card description-card">
            <span class="section-label">{{ i18n.t('overview') }}</span>
            <p v-if="game?.description" class="description-text">{{ game.description }}</p>
            <div v-else class="skeleton-blocks">
              <div class="skeleton-line"></div>
              <div class="skeleton-line"></div>
              <div class="skeleton-line short"></div>
            </div>

            <div class="tag-list" v-if="overviewTags.length">
              <span v-for="tag in overviewTags" :key="tag" class="tag-pill">{{ tag }}</span>
            </div>
            <div v-else class="tag-list tag-list-empty">
              <span class="tag-pill skeleton-tag"></span>
              <span class="tag-pill skeleton-tag"></span>
              <span class="tag-pill skeleton-tag"></span>
            </div>
          </article>

          <aside class="card facts-card">
            <span class="section-label">{{ i18n.t('details') }}</span>

            <div class="fact-row">
              <span class="fact-key">{{ i18n.t('game.developer') }}</span>
              <span class="fact-value">{{ game?.developer || i18n.t('na') }}</span>
            </div>
            <div class="fact-row">
              <span class="fact-key">{{ i18n.t('game.release_date') }}</span>
              <span class="fact-value">{{ formatDate(game?.release_date) }}</span>
            </div>
            <div class="fact-row">
              <span class="fact-key">{{ i18n.t('game.platforms') }}</span>
              <span class="fact-value platform-value">
                <span v-if="game?.platforms?.length" class="platform-list">
                  <span v-for="(platform, index) in game.platforms" :key="platform" class="platform-item">
                    <span class="platform-dot" :style="{ backgroundColor: getPlatformColor(index) }"></span>
                    {{ platform }}
                  </span>
                </span>
                <span v-else>{{ i18n.t('na') }}</span>
              </span>
            </div>
            <div class="fact-row">
              <span class="fact-key">{{ i18n.t('game.age_rating') }}</span>
              <span class="fact-value">{{ game?.age_rating || i18n.t('na') }}</span>
            </div>
            <div class="fact-row">
              <span class="fact-key">{{ i18n.t('game.languages') }}</span>
              <span class="fact-value">{{ game?.languages?.length ? game.languages.join(', ') : i18n.t('na') }}</span>
            </div>
          </aside>
        </section>

        <section class="card stores-card">
          <div class="section-head">
            <div>
              <span class="section-label">{{ i18n.t('game.stores') }}</span>
              <h2 class="section-title">{{ i18n.t('where_to_buy') }}</h2>
            </div>
            <div class="price-highlight" v-if="bestStorePrice">
              <span>{{ i18n.t('best_current_price') }}</span>
              <strong>{{ formatPrice(bestStorePrice.current_price_eur) }}</strong>
            </div>
          </div>

          <div v-if="stores?.length" class="table-wrap">
            <table class="stores-table">
              <thead>
                <tr>
                  <th>{{ i18n.t('game.store') }}</th>
                  <th>{{ i18n.t('game.base_price') }}</th>
                  <th>{{ i18n.t('game.discount') }}</th>
                  <th>{{ i18n.t('game.final_price') }}</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="store in sortedStores"
                  :key="store.store_slug"
                  :class="{ 'best-row': bestStorePrice && Math.abs(store.current_price_eur - bestStorePrice.current_price_eur) < 0.0001 }"
                >
                  <td>
                    <div class="store-cell">
                      <span class="store-icon">{{ getStoreInitial(store.store_name) }}</span>
                      <div>
                        <div class="store-name">{{ store.store_name }}</div>
                        <div class="store-subtitle">{{ store.store_slug }}</div>
                      </div>
                    </div>
                  </td>
                  <td>{{ formatPrice(store.base_price_eur) }}</td>
                  <td>
                    <span class="discount-pill" :class="getDiscountTone(store.discount_percent)">
                      {{ store.discount_percent > 0 ? `-${store.discount_percent}%` : i18n.t('game.no_discount') }}
                    </span>
                  </td>
                  <td class="final-price">{{ formatPrice(store.current_price_eur) }}</td>
                  <td class="store-action-cell">
                    <button
                      type="button"
                      class="store-button"
                      :class="{ primary: bestStorePrice && Math.abs(store.current_price_eur - bestStorePrice.current_price_eur) < 0.0001 }"
                      @click="openStore(store.url)"
                    >
                      {{ i18n.t('buttons.visit') }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="empty-state">
            <div class="skeleton-row"></div>
            <div class="skeleton-row"></div>
            <div class="skeleton-row short"></div>
          </div>
        </section>

        <section class="card chart-card">
          <div class="chart-head">
            <div>
              <span class="section-label">{{ i18n.t('game.history') }}</span>
              <div class="chart-pricing">
                <strong>{{ displayedCurrentPrice !== null ? formatPrice(displayedCurrentPrice) : i18n.t('na') }}</strong>
                <span v-if="minAllTimePrice !== null">{{ i18n.t('game.all_time_low') }}: {{ formatPrice(minAllTimePrice) }}</span>
              </div>
            </div>

            <div class="period-switcher">
              <button
                v-for="period in periodOrder"
                :key="period"
                type="button"
                class="period-button"
                :class="{ active: selectedPeriod === period }"
                @click="selectedPeriod = period"
              >
                {{ period }}
              </button>
            </div>
          </div>

          <div class="chart-frame">
            <div v-if="currentHistory.length" class="chart-canvas-wrap">
              <canvas ref="chartCanvas" class="price-chart"></canvas>
            </div>

            <div v-else class="chart-empty-state">
              <div class="skeleton-chart"></div>
            </div>
          </div>
        </section>
      </template>
    </main>

    <footer class="footer">
      <div class="footer-container">
        <span class="footer-text">{{ i18n.t('footer.brand') }}</span>
      </div>
    </footer>
  </div>
</template>

<style scoped>
* {
  box-sizing: border-box;
}

.game-page {
  --bg-primary: #f4f4f5;
  --bg-secondary: #ececf0;
  --text-primary: #111827;
  --text-secondary: #6b7280;
  --border-color: rgba(15, 23, 42, 0.08);
  --hover-bg: rgba(99, 102, 241, 0.05);
  --accent-color: #a78bfa;
  --page-bg: #f4f4f5;
  --card-bg: #ffffff;
  --card-border: rgba(15, 23, 42, 0.08);
  --text-primary: #111827;
  --text-secondary: #6b7280;
  --muted-bg: #f3f4f6;
  --table-head-bg: #f9fafb;
  --table-hover-bg: rgba(99, 102, 241, 0.05);
  --skeleton-bg: #e5e7eb;
  --footer-bg: #ececf0;
  --footer-border: rgba(24, 24, 27, 0.08);
  --accent: #a78bfa;
  --accent-strong: #7c3aed;

  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--page-bg);
  color: var(--text-primary);
  font-family: -apple-system, BlinkMacSystemFont, sans-serif;
}

.game-page.dark {
  --bg-primary: #111218;
  --bg-secondary: #18181b;
  --text-primary: #f4f4f5;
  --text-secondary: #a1a1aa;
  --border-color: rgba(255, 255, 255, 0.08);
  --hover-bg: rgba(255, 255, 255, 0.03);
  --accent-color: #8b5cf6;
  --page-bg: #111218;
  --card-bg: #18181b;
  --card-border: rgba(255, 255, 255, 0.08);
  --text-primary: #f4f4f5;
  --text-secondary: #a1a1aa;
  --muted-bg: #27272a;
  --table-head-bg: #111113;
  --table-hover-bg: rgba(255, 255, 255, 0.03);
  --skeleton-bg: #27272a;
  --footer-bg: #101217;
  --footer-border: rgba(255, 255, 255, 0.08);
}

.page-shell {
  flex: 1;
  width: min(1180px, 100%);
  margin: 0 auto;
  padding: 24px 20px 48px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.card,
.hero-card {
  background: var(--card-bg);
  border: 0.5px solid var(--card-border);
  border-radius: 20px;
}

.hero-card {
  position: relative;
  overflow: hidden;
}

  .hero-image {
  display: block;
  width: 100%;
  aspect-ratio: 16 / 9;
  max-height: 420px;
  object-fit: cover;
  object-position: center center;
  image-rendering: auto;
  transform: translateZ(0);
}

  .wishlist-toggle {
    position: absolute;
    top: 16px;
    right: 16px;
    z-index: 3;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.9);
    color: #ef4444;
    font-size: 20px;
    cursor: pointer;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.18);
    transition: background-color 0.15s ease, transform 0.12s ease;
  }

  .wishlist-toggle:hover {
    transform: translateY(-2px);
  }

  .wishlist-toggle.active {
    background: linear-gradient(90deg, var(--accent), var(--accent-strong));
    color: #ffffff;
  }

  .wishlist-toggle .heart {
    line-height: 1;
    pointer-events: none;
  }

.hero-skeleton {
  min-height: 340px;
  padding: 28px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  gap: 12px;
}

.description-card,
.facts-card,
.stores-card,
.chart-card {
  padding: 24px;
}

.game-title {
  margin: 0;
  font-size: 34px;
  line-height: 1.1;
  color: #ffffff;
  letter-spacing: -0.03em;
}

.hero-content {
  position: absolute;
  left: 24px;
  right: 24px;
  bottom: 20px;
  z-index: 1;
}

.hero-meta {
  margin-top: 12px;
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
  color: rgba(255, 255, 255, 0.86);
  font-size: 13px;
  text-shadow: 0 1px 10px rgba(0, 0, 0, 0.45);
}

.hero-meta span:not(:last-child)::after {
  content: '•';
  margin-left: 14px;
  color: rgba(255, 255, 255, 0.6);
}

.details-grid {
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 1.5rem;
}

.section-label {
  display: inline-block;
  margin-bottom: 12px;
  font-size: 11px;
  font-weight: 500;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--text-secondary);
}

.description-text {
  color: var(--text-secondary);
  font-size: 14px;
  line-height: 1.75;
}

.tag-list {
  margin-top: 18px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tag-pill {
  display: inline-flex;
  align-items: center;
  padding: 8px 10px;
  border-radius: 10px;
  background: var(--muted-bg);
  color: var(--text-secondary);
  font-size: 12px;
  line-height: 1;
}

.tag-list-empty .tag-pill,
.skeleton-tag {
  width: 72px;
  height: 30px;
}

.facts-card {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.fact-row {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding-bottom: 12px;
  border-bottom: 0.5px solid var(--card-border);
}

.fact-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.fact-key {
  color: var(--text-secondary);
  font-size: 13px;
  flex: 0 0 auto;
}

.fact-value {
  color: var(--text-primary);
  font-size: 13px;
  text-align: right;
}

.platform-value {
  display: flex;
  justify-content: flex-end;
}

.platform-list {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 8px;
}

.platform-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--text-primary);
}

.platform-dot {
  width: 8px;
  height: 8px;
  border-radius: 999px;
}

.section-head,
.chart-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
}

.section-title {
  font-size: 20px;
  color: var(--text-primary);
  letter-spacing: -0.02em;
}

.price-highlight {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
  color: var(--text-secondary);
  font-size: 12px;
}

.price-highlight strong {
  color: #22c55e;
  font-size: 18px;
}

.table-wrap {
  margin-top: 18px;
  overflow: auto;
  border-radius: 18px;
  border: 0.5px solid var(--card-border);
}

.stores-table {
  width: 100%;
  min-width: 720px;
  border-collapse: collapse;
  color: var(--text-primary);
}

.stores-table thead {
  background: var(--table-head-bg);
}

.stores-table th,
.stores-table td {
  padding: 16px 18px;
  text-align: left;
  border-bottom: 0.5px solid var(--card-border);
}

.stores-table th {
  font-size: 11px;
  font-weight: 500;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--text-secondary);
}

.stores-table tbody tr:hover {
  background: var(--table-hover-bg);
}

.stores-table tbody tr:last-child td {
  border-bottom: none;
}

.store-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.store-icon {
  width: 34px;
  height: 34px;
  border-radius: 12px;
  background: color-mix(in srgb, var(--accent) 18%, transparent);
  border: 1px solid color-mix(in srgb, var(--accent) 30%, transparent);
  color: var(--accent-strong);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.store-name {
  color: var(--text-primary);
  font-size: 14px;
}

.store-subtitle {
  color: var(--text-secondary);
  font-size: 12px;
  margin-top: 2px;
}

.discount-pill {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 6px 10px;
  font-size: 12px;
  font-weight: 600;
}

.discount-pill.strong {
  background: rgba(34, 197, 94, 0.1);
  color: #16a34a;
}

.discount-pill.medium {
  background: rgba(249, 115, 22, 0.12);
  color: #ea580c;
}

.discount-pill.neutral {
  background: var(--muted-bg);
  color: var(--text-secondary);
}

.final-price {
  color: var(--text-primary);
  font-weight: 600;
}

.store-action-cell {
  width: 92px;
}

.store-button {
  width: 100%;
  border: none;
  border-radius: 12px;
  padding: 10px 12px;
  background: var(--muted-bg);
  color: var(--text-primary);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease, color 0.2s ease;
}

.store-button.primary {
  background: var(--accent);
  color: #fff;
}

.store-button:hover {
  background: color-mix(in srgb, var(--muted-bg) 82%, #4b5563);
}

.store-button.primary:hover {
  background: var(--accent-strong);
}

.chart-pricing {
  margin-top: 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.chart-pricing strong {
  font-size: 28px;
  line-height: 1;
  color: var(--text-primary);
}

.chart-pricing span {
  color: #16a34a;
  font-size: 13px;
}

.period-switcher {
  display: inline-flex;
  gap: 6px;
  padding: 4px;
  background: var(--muted-bg);
  border-radius: 12px;
  border: 0.5px solid var(--card-border);
}

.period-button {
  border: none;
  border-radius: 8px;
  background: transparent;
  color: var(--text-secondary);
  font-size: 13px;
  padding: 8px 12px;
  cursor: pointer;
  transition: color 0.2s ease, background-color 0.2s ease;
}

.period-button:hover {
  color: var(--text-primary);
}

.period-button.active {
  background: var(--card-bg);
  color: var(--text-primary);
}

.chart-frame {
  margin-top: 18px;
}

.chart-canvas-wrap {
  height: 200px;
}

.price-chart {
  width: 100%;
  height: 100%;
}

.chart-empty-state,
.empty-state,
.loading-state {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.skeleton-line,
.skeleton-row,
.skeleton-chart,
.skeleton-title,
.skeleton-subtitle {
  background: var(--skeleton-bg);
  border-radius: 12px;
  animation: pulse 1.4s ease-in-out infinite;
}

.skeleton-line {
  width: 100%;
  height: 14px;
}

.skeleton-line.short,
.skeleton-row.short {
  width: 60%;
}

.skeleton-blocks {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.skeleton-row {
  width: 100%;
  height: 20px;
}

.skeleton-chart {
  width: 100%;
  height: 200px;
}

.skeleton-title {
  width: 180px;
  height: 30px;
}

.skeleton-subtitle {
  width: 240px;
  height: 18px;
}

.footer {
  background: var(--footer-bg);
  border-top: 1px solid var(--footer-border);
  padding: 1rem 2rem;
}

.footer-container {
  max-width: 1180px;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
}

.footer-text {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

@keyframes pulse {
  0%,
  100% {
    opacity: 0.5;
  }

  50% {
    opacity: 0.9;
  }
}

@media (max-width: 960px) {
  .details-grid {
    grid-template-columns: 1fr;
  }

  .section-head,
  .chart-head {
    flex-direction: column;
  }
}

@media (max-width: 720px) {
  .page-shell {
    padding: 18px 14px 40px;
    gap: 16px;
  }

  .description-card,
  .facts-card,
  .stores-card,
  .chart-card {
    padding: 16px;
  }

  .hero-skeleton {
    min-height: 230px;
  }

  .hero-image {
    aspect-ratio: 16 / 10;
  }

  .game-title {
    font-size: 28px;
  }

  .hero-content {
    left: 14px;
    right: 14px;
    bottom: 12px;
  }

  .hero-meta {
    gap: 10px;
  }

  .stores-table {
    min-width: 640px;
  }

  .stores-table th,
  .stores-table td {
    padding: 12px;
  }

  .store-action-cell {
    width: 78px;
  }

  .footer {
    padding: 0.9rem 1rem;
  }
}
</style>