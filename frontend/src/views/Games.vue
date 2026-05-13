<script setup>
import { computed, onMounted, provide, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { formatDateOnly } from '../composables/useDateTimeFormat'
import { useThemePreference } from '../composables/useThemePreference'

const router = useRouter()
const selectedLanguage = ref('ENG')
const selectedTheme = useThemePreference()
const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')
const isLoading = ref(false)
const loadError = ref('')
const selectedStores = ref([])
const selectedPrice = ref('')
const selectedDiscount = ref('')
const selectedSortField = ref('releaseDate')
const selectedSortDirection = ref('desc')
const searchQuery = ref('')

provide('theme', selectedTheme)

const games = ref([])

const storeOptions = [
  { code: 'steam', label: 'Steam' },
  { code: 'epic', label: 'Epic Games Store' },
]

const priceOptions = [
  { value: '5', label: 'Below 5 €' },
  { value: '10', label: 'Below 10 €' },
  { value: '30', label: 'Below 30 €' },
]

const discountOptions = [
  { value: '25', label: 'At least 25% off' },
  { value: '50', label: 'At least 50% off' },
  { value: '75', label: 'At least 75% off' },
  { value: '90', label: 'At least 90% off' },
]

const tableHeaders = [
  { key: 'name', label: 'Game', className: 'col-game', sortable: true },
  { key: 'genre', label: 'Genre', className: 'col-genre', sortable: false },
  { key: 'releaseDate', label: 'Release', className: 'col-release', sortable: true },
  { key: 'bestPrice', label: 'Price', className: 'col-price', sortable: true },
  { key: 'bestDiscount', label: 'Discount', className: 'col-discount', sortable: true },
]

const hasActiveFilters = computed(() =>
  searchQuery.value.trim() !== '' ||
  selectedStores.value.length > 0 ||
  selectedPrice.value !== '' ||
  selectedDiscount.value !== '' ||
  selectedSortField.value !== 'releaseDate' ||
  selectedSortDirection.value !== 'desc'
)

const filteredGames = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return games.value.filter((game) => {
    const queryMatches = !query || [
      game.name,
      game.genre,
      game.developer,
      game.publisher,
      game.releaseDate,
      game.bestPrice !== null && game.bestPrice !== undefined ? String(game.bestPrice) : '',
      game.bestDiscount !== null && game.bestDiscount !== undefined ? String(game.bestDiscount) : '',
      ...(Array.isArray(game.stores) ? game.stores.flatMap((store) => [store.code, store.name]) : []),
    ]
      .filter(Boolean)
      .some((value) => String(value).toLowerCase().includes(query))

    const storeMatches =
      selectedStores.value.length === 0 ||
      (Array.isArray(game.stores) && game.stores.some((store) => selectedStores.value.includes(store.code)))

    const priceLimit = selectedPrice.value !== '' ? Number(selectedPrice.value) : null
    const priceMatches =
      priceLimit === null ||
      (typeof game.bestPrice === 'number' && game.bestPrice <= priceLimit)

    const discountLimit = selectedDiscount.value !== '' ? Number(selectedDiscount.value) : null
    const discountMatches =
      discountLimit === null ||
      (typeof game.bestDiscount === 'number' && game.bestDiscount >= discountLimit)

    return queryMatches && storeMatches && priceMatches && discountMatches
  })
})

const sortedGames = computed(() => {
  const direction = selectedSortDirection.value === 'asc' ? 1 : -1

  const compareNullableNumbers = (leftValue, rightValue) => {
    if (leftValue === null || leftValue === undefined) {
      return rightValue === null || rightValue === undefined ? 0 : 1
    }

    if (rightValue === null || rightValue === undefined) {
      return -1
    }

    return Number(leftValue) - Number(rightValue)
  }

  const compareNullableDates = (leftValue, rightValue) => {
    if (!leftValue && !rightValue) {
      return 0
    }

    if (!leftValue) {
      return 1
    }

    if (!rightValue) {
      return -1
    }

    return String(leftValue).localeCompare(String(rightValue))
  }

  const collator = new Intl.Collator(undefined, { sensitivity: 'base', numeric: true })

  return [...filteredGames.value].sort((left, right) => {
    let comparison = 0

    switch (selectedSortField.value) {
      case 'name':
        comparison = collator.compare(left.name || '', right.name || '')
        break
      case 'bestPrice':
        comparison = compareNullableNumbers(left.bestPrice, right.bestPrice)
        break
      case 'bestDiscount':
        comparison = compareNullableNumbers(left.bestDiscount, right.bestDiscount)
        break
      case 'releaseDate':
      default:
        comparison = compareNullableDates(left.releaseDate, right.releaseDate)
        break
    }

    return comparison * direction
  })
})

const visibleGames = computed(() => sortedGames.value)

function toggleStore(code) {
  if (selectedStores.value.includes(code)) {
    selectedStores.value = selectedStores.value.filter((storeCode) => storeCode !== code)
    return
  }

  selectedStores.value = [...selectedStores.value, code]
}

function togglePriceFilter(value) {
  selectedPrice.value = selectedPrice.value === value ? '' : value
}

function toggleDiscountFilter(value) {
  selectedDiscount.value = selectedDiscount.value === value ? '' : value
}

function resetFilters() {
  selectedStores.value = []
  selectedPrice.value = ''
  selectedDiscount.value = ''
  selectedSortField.value = 'releaseDate'
  selectedSortDirection.value = 'desc'
  searchQuery.value = ''
}

function sortBy(field) {
  if (selectedSortField.value === field) {
    selectedSortDirection.value = selectedSortDirection.value === 'asc' ? 'desc' : 'asc'
    return
  }

  selectedSortField.value = field
  selectedSortDirection.value = 'asc'
}

function getSortIndicator(field) {
  if (selectedSortField.value !== field) {
    return '▲'
  }

  return selectedSortDirection.value === 'asc' ? '▲' : '▼'
}

function formatPrice(value) {
  if (value === null || value === undefined || value === '') {
    return 'n/a'
  }

  return `€${Number(value).toFixed(2)}`
}

async function fetchGames() {
  isLoading.value = true
  loadError.value = ''

  try {
    const response = await fetch(apiBaseUrl ? `${apiBaseUrl}/games` : '/api/games', {
      headers: {
        Accept: 'application/json',
      },
    })

    const data = await response.json().catch(() => null)

    if (!response.ok) {
      throw new Error(data?.message || 'Unable to load games right now.')
    }

    games.value = Array.isArray(data?.games) ? data.games : []
  } catch (err) {
    loadError.value = err instanceof Error ? err.message : 'Unable to load games right now.'
    games.value = []
  } finally {
    isLoading.value = false
  }
}

const goToGame = (slug) => {
  router.push({ name: 'game', params: { slug } })
}

onMounted(() => {
  fetchGames()
})
</script>

<template>
  <div class="games-page" :class="selectedTheme">
    <AppHeader
      v-model:selectedTheme="selectedTheme"
      v-model:selectedLanguage="selectedLanguage"
      activePage="games"
    />

    <main class="main-content">
      <div class="content-wrapper">
        <div class="layout-actions">
          <label class="search-shell" for="game-search">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
              <circle cx="11" cy="11" r="7" />
              <line x1="16.65" y1="16.65" x2="21" y2="21" />
            </svg>
            <input
              id="game-search"
              v-model="searchQuery"
              type="search"
              placeholder="Search games"
              autocomplete="off"
            />
          </label>
        </div>

        <div class="layout-shell">
          <aside class="filters-panel" aria-label="Game filters">


            <section class="filter-group">
              <h3>Store</h3>
              <label
                v-for="store in storeOptions"
                :key="store.code"
                class="choice-row choice-checkbox"
                :class="{ active: selectedStores.includes(store.code) }"
              >
                <input
                  type="checkbox"
                  :checked="selectedStores.includes(store.code)"
                  @change="toggleStore(store.code)"
                />
                <span>{{ store.label }}</span>
              </label>
            </section>

            <section class="filter-group">
              <h3>Price</h3>
              <button
                v-for="option in priceOptions"
                :key="option.value"
                type="button"
                class="choice-row choice-toggle"
                :class="{ active: selectedPrice === option.value }"
                @click="togglePriceFilter(option.value)"
                :aria-pressed="selectedPrice === option.value"
              >
                <span>{{ option.label }}</span>
              </button>
            </section>

            <section class="filter-group">
              <h3>Discount</h3>
              <button
                v-for="option in discountOptions"
                :key="option.value"
                type="button"
                class="choice-row choice-toggle"
                :class="{ active: selectedDiscount === option.value }"
                @click="toggleDiscountFilter(option.value)"
                :aria-pressed="selectedDiscount === option.value"
              >
                <span>{{ option.label }}</span>
              </button>
            </section>

            <button
              type="button"
              class="reset-button-panel"
              :disabled="!hasActiveFilters"
              @click="resetFilters"
            >
              Reset
            </button>
          </aside>

          <section class="results-panel">
            <div class="results-summary">
              <span>{{ visibleGames.length }} games shown</span>
              <span v-if="games.length > 0">{{ games.length }} total</span>
            </div>

            <div class="table-container">
              <table class="games-table">
                <thead>
                  <tr>
                    <th
                      v-for="header in tableHeaders"
                      :key="header.key"
                      :class="[header.className, { sortable: header.sortable }]"
                      @click="header.sortable && sortBy(header.key)"
                    >
                      {{ header.label }}
                      <span
                        v-if="header.sortable"
                        class="sort-indicator"
                        :class="{ active: selectedSortField === header.key }"
                      >{{ getSortIndicator(header.key) }}</span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <template v-if="!isLoading && !loadError && visibleGames.length > 0">
                    <tr
                      v-for="game in visibleGames"
                      :key="game.id"
                      class="game-row"
                      @click="goToGame(game.slug)"
                    >
                      <td class="cell-game">
                        <div class="game-info">
                          <div v-if="game.logo" class="game-logo-wrap">
                            <img :src="game.logo" :alt="game.name" class="game-logo" />
                          </div>
                          <div v-else class="game-logo-placeholder">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                              <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                              <circle cx="8.5" cy="8.5" r="1.5" />
                              <polyline points="21 15 16 10 5 21" />
                            </svg>
                          </div>
                          <div class="game-meta">
                            <span class="game-name">{{ game.name }}</span>
                          </div>
                        </div>
                      </td>
                        <td class="cell-genre">{{ game.genre || 'Unknown' }}</td>
                        <td class="cell-release">{{ game.releaseDate ? formatDateOnly(game.releaseDate, undefined, selectedLanguage) : 'n/a' }}</td>
                        <td class="cell-price">{{ game.bestPrice !== null && game.bestPrice !== undefined ? formatPrice(game.bestPrice) : 'n/a' }}</td>
                        <td class="cell-discount">
                          <span
                            class="discount-pill"
                            :class="{
                              strong: Number(game.bestDiscount) >= 25,
                              medium: Number(game.bestDiscount) >= 10 && Number(game.bestDiscount) < 25,
                              neutral: !Number(game.bestDiscount) || Number(game.bestDiscount) < 10,
                            }"
                          >
                            {{ game.bestDiscount !== null && game.bestDiscount !== undefined ? `${game.bestDiscount}%` : '—' }}
                          </span>
                        </td>
                    </tr>
                  </template>

                  <tr v-if="isLoading">
                    <td colspan="5" class="empty-state">
                      <div class="empty-content">
                        <p>Loading games...</p>
                      </div>
                    </td>
                  </tr>

                  <tr v-else-if="loadError">
                    <td colspan="5" class="empty-state">
                      <div class="empty-content">
                        <p>{{ loadError }}</p>
                      </div>
                    </td>
                  </tr>

                  <tr v-else-if="games.length === 0">
                    <td colspan="5" class="empty-state">
                      <div class="empty-content">
                        <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                          <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                          <line x1="12" y1="22.08" x2="12" y2="12" />
                        </svg>
                        <p>No games available yet</p>
                      </div>
                    </td>
                  </tr>

                  <tr v-else-if="visibleGames.length === 0">
                    <td colspan="5" class="empty-state">
                      <div class="empty-content">
                        <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                          <path d="M20 20V14M17 17H23M4 20V4m0 0 4 4m-4-4-4 4" />
                        </svg>
                        <p>{{ searchQuery.trim() ? 'No games match your search and filters' : 'No games match the selected filters' }}</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
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

.games-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  transition: background-color 0.3s ease, color 0.3s ease;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
}

.games-page.light {
  --bg-primary: #ffffff;
  --bg-secondary: #f5f5f7;
  --text-primary: #1d1d1f;
  --text-secondary: #86868b;
  --border-color: #d2d2d7;
  --hover-bg: #f5f5f7;
  --accent-color: #0071e3;
}

.games-page.dark {
  --bg-primary: #1b1d21;
  --bg-secondary: #25282e;
  --text-primary: #f5f5f7;
  --text-secondary: #a6aab3;
  --border-color: #545a65;
  --hover-bg: #2f333b;
  --accent-color: #2997ff;
}

.games-page {
  background: var(--bg-primary);
  color: var(--text-primary);
}

/* main */
.main-content {
  flex: 1;
  padding: 24px 20px 48px;
}

.content-wrapper {
  max-width: 1180px;
  margin: 0 auto;
}

.layout-shell {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  gap: 24px;
  align-items: start;
}

.layout-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 24px;
  min-height: 2.4rem;
}

.search-shell {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
  border-radius: 999px;
  padding: 0.5rem 0.8rem;
  min-width: 230px;
  background: color-mix(in srgb, var(--bg-primary) 92%, var(--bg-secondary));
  transition: background-color 0.2s ease;
}

.search-icon {
  width: 16px;
  height: 16px;
  color: var(--text-secondary);
  flex-shrink: 0;
}

.search-shell input {
  width: min(360px, 45vw);
  min-width: 140px;
  border: none;
  background: transparent;
  color: var(--text-primary);
  font-size: 0.9rem;
  outline: none;
}

.search-shell input::placeholder {
  color: color-mix(in srgb, var(--text-secondary) 88%, var(--bg-primary));
}

.search-shell input[type='search']::-webkit-search-cancel-button {
  -webkit-appearance: none;
  appearance: none;
  width: 14px;
  height: 14px;
  border-radius: 999px;
  cursor: pointer;
  background-color: color-mix(in srgb, var(--text-secondary) 72%, var(--bg-primary));
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3E%3Cpath d='M3 3l6 6M9 3l-6 6' stroke='%23ffffff' stroke-width='1.3' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: center;
  background-size: 8px 8px;
  opacity: 0.75;
  transition: opacity 0.15s ease;
}

.search-shell input[type='search']::-webkit-search-cancel-button:hover {
  opacity: 1;
}

.filters-panel {
  position: sticky;
  top: 1.25rem;
  padding: 24px;
  border: 0.5px solid var(--border-color);
  border-radius: 20px;
  background: var(--bg-primary);
  box-shadow: none;
}

.reset-button {
  border: 0.5px solid var(--border-color);
  background: var(--bg-secondary);
  color: var(--text-primary);
  border-radius: 12px;
  padding: 10px 12px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease, border-color 0.2s ease;
}

.reset-button:hover {
  background: color-mix(in srgb, var(--bg-secondary) 82%, var(--accent-color));
  border-color: color-mix(in srgb, var(--border-color) 70%, var(--accent-color));
}

.reset-button-panel {
  width: 100%;
  margin-top: 10px;
  border: 0.5px solid var(--border-color);
  background: var(--bg-secondary);
  color: var(--text-primary);
  border-radius: 10px;
  padding: 8px 10px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease, border-color 0.2s ease;
}

.reset-button-panel:hover {
  background: color-mix(in srgb, var(--bg-secondary) 82%, var(--accent-color));
  border-color: color-mix(in srgb, var(--border-color) 70%, var(--accent-color));
}

.reset-button-panel:disabled {
  cursor: not-allowed;
  background: color-mix(in srgb, var(--bg-secondary) 92%, var(--text-secondary));
  color: var(--text-secondary);
  border-color: color-mix(in srgb, var(--border-color) 85%, var(--text-secondary));
  opacity: 0.7;
}

.reset-button-panel:disabled:hover {
  background: color-mix(in srgb, var(--bg-secondary) 92%, var(--text-secondary));
  border-color: color-mix(in srgb, var(--border-color) 85%, var(--text-secondary));
}

.filter-group {
  padding: 12px 0;
  border-top: 0.5px solid var(--border-color);
}

.filter-group:first-of-type {
  border-top: none;
  padding-top: 0;
}

.filter-group h3 {
  margin-bottom: 12px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--text-secondary);
}

.field-label {
  display: block;
  margin: 8px 0 6px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--text-secondary);
}

.sort-select {
  width: 100%;
  padding: 8px 10px;
  border: 0.5px solid var(--border-color);
  border-radius: 12px;
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 13px;
  font: inherit;
}

.sort-select:focus {
  outline: 2px solid color-mix(in srgb, var(--accent-color) 35%, transparent);
  outline-offset: 2px;
}

.choice-row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 8px;
  border-radius: 12px;
  border: none;
  width: 100%;
  background: transparent;
  text-align: left;
  font: inherit;
  color: var(--text-primary);
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-size: 13px;
}

.choice-row:hover {
  background: var(--bg-secondary);
}

.choice-row.active {
  background: color-mix(in srgb, var(--accent-color) 12%, transparent);
}

.choice-row.active:hover {
  background: color-mix(in srgb, var(--accent-color) 16%, transparent);
}

.choice-row input {
  accent-color: var(--accent-color);
  flex-shrink: 0;
}

.choice-radio input {
  accent-color: var(--accent-color);
}

.choice-clear {
  margin-top: 0.25rem;
  color: var(--text-secondary);
}

.results-panel {
  min-width: 0;
}

.results-summary {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
  padding: 0;
  color: var(--text-secondary);
  font-size: 12px;
}

/* table */
.table-container {
  border: 0.5px solid var(--border-color);
  border-radius: 20px;
  overflow: hidden;
  background: var(--bg-primary);
}

.games-table {
  width: 100%;
  border-collapse: collapse;
}

.games-table thead {
  background: var(--bg-secondary);
}

.games-table th {
  padding: 14px 18px;
  text-align: left;
  font-size: 11px;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.14em;
  border-bottom: 0.5px solid var(--border-color);
  background: var(--bg-secondary);
  white-space: nowrap;
}

.games-table th.sortable {
  cursor: pointer;
  user-select: none;
}

.sort-indicator {
  display: inline-block;
  width: 12px;
  font-size: 11px;
  margin-left: 8px;
  color: var(--text-secondary);
  text-align: center;
  visibility: hidden;
}

.sort-indicator.active {
  visibility: visible;
}

.games-table td {
  padding: 14px 18px;
  font-size: 13px;
  color: var(--text-primary);
  border-bottom: 0.5px solid var(--border-color);
}

.games-table tbody tr:last-child td {
  border-bottom: none;
}

.games-table tbody tr:hover {
  background: var(--hover-bg);
}

.game-row {
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.col-game {
  width: 60%;
}

.col-genre {
  width: 34%;
}

.col-release {
  width: 10%;
  padding: 14px 8px;
}

.col-price {
  width: 8%;
  text-align: right;
  padding: 14px 8px;
}

.col-discount {
  width: 8%;
  text-align: center;
  padding: 14px 8px;
}

.game-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.game-meta {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.game-logo {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  object-fit: cover;
  background: var(--bg-secondary);
}

.game-logo-placeholder {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  background: var(--bg-secondary);
  border: 0.5px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
  opacity: 0.5;
  flex-shrink: 0;
}

.game-logo-placeholder svg {
  width: 24px;
  height: 24px;
}

.game-name {
  font-weight: 500;
  color: var(--text-primary);
}

.game-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}

.meta-badge {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 6px 10px;
  font-size: 12px;
  line-height: 1;
  color: var(--text-secondary);
  background: var(--bg-secondary);
  border: 0.5px solid var(--border-color);
}

.discount-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 8px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.discount-pill.strong {
  background: rgba(34, 197, 94, 0.08);
  color: #16a34a;
}

.discount-pill.medium {
  background: rgba(249, 115, 22, 0.08);
  color: #ea580c;
}

.discount-pill.neutral {
  background: var(--bg-secondary);
  color: var(--text-secondary);
}

.cell-genre {
  color: var(--text-secondary);
}

.empty-state {
  text-align: center;
  padding: 48px 18px !important;
}

.empty-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.empty-icon {
  width: 48px;
  height: 48px;
  color: var(--text-secondary);
  opacity: 0.5;
}

.empty-content p {
  font-size: 13px;
  color: var(--text-secondary);
}

/* footer */
.footer {
  background: var(--bg-secondary);
  border-top: 0.5px solid var(--border-color);
  padding: 1rem 2rem;
  margin-top: auto;
}

.footer-container {
  max-width: 1180px;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
}

.footer-text {
  font-size: 12px;
  color: var(--text-secondary);
}

/* responsive */
@media (max-width: 980px) {
  .layout-shell {
    grid-template-columns: 1fr;
  }

  .filters-panel {
    position: static;
  }
}

@media (max-width: 640px) {
  .main-content {
    padding: 18px 14px 40px;
  }

  .layout-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .search-shell {
    width: 100%;
    min-width: 0;
  }

  .search-shell input {
    width: 100%;
    min-width: 0;
  }

  .games-table th,
  .games-table td {
    padding: 12px;
  }

  .game-logo {
    width: 36px;
    height: 36px;
    border-radius: 6px;
  }

  .game-info {
    gap: 10px;
  }

  .results-summary {
    flex-direction: column;
    gap: 4px;
  }
}
</style>
