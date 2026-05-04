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
const selectedReleaseSort = ref('newest')
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

const releaseSortOptions = [
  { value: 'newest', label: 'Newest first' },
  { value: 'oldest', label: 'Oldest first' },
]

const hasActiveFilters = computed(() =>
  selectedStores.value.length > 0 ||
  selectedPrice.value !== '' ||
  selectedDiscount.value !== '' ||
  selectedReleaseSort.value !== 'newest'
)

const filteredGames = computed(() => {
  const items = games.value.filter((game) => {
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

    return storeMatches && priceMatches && discountMatches
  })

  return items.sort((left, right) => {
    const leftDate = left.releaseDate || ''
    const rightDate = right.releaseDate || ''

    if (!leftDate && !rightDate) {
      return 0
    }

    if (!leftDate) {
      return 1
    }

    if (!rightDate) {
      return -1
    }

    return selectedReleaseSort.value === 'oldest'
      ? leftDate.localeCompare(rightDate)
      : rightDate.localeCompare(leftDate)
  })
})

const searchedGames = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  if (!query) {
    return games.value
  }

  return games.value.filter((game) => {
    const gameName = (game.name || '').toLowerCase()
    const gameGenre = (game.genre || '').toLowerCase()

    return gameName.includes(query) || gameGenre.includes(query)
  })
})

const visibleGames = computed(() => (searchQuery.value.trim() ? searchedGames.value : filteredGames.value))

function toggleStore(code) {
  if (selectedStores.value.includes(code)) {
    selectedStores.value = selectedStores.value.filter((storeCode) => storeCode !== code)
    return
  }

  selectedStores.value = [...selectedStores.value, code]
}

function resetFilters() {
  selectedStores.value = []
  selectedPrice.value = ''
  selectedDiscount.value = ''
  selectedReleaseSort.value = 'newest'
}

function formatPrice(value) {
  if (value === null || value === undefined || value === '') {
    return 'n/a'
  }

  return `${Number(value).toFixed(2)} €`
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
          <button v-if="hasActiveFilters" type="button" class="reset-button" @click="resetFilters">
            Reset
          </button>
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
              <label
                v-for="option in priceOptions"
                :key="option.value"
                class="choice-row choice-radio"
                :class="{ active: selectedPrice === option.value }"
              >
                <input
                  v-model="selectedPrice"
                  type="radio"
                  name="price-filter"
                  :value="option.value"
                />
                <span>{{ option.label }}</span>
              </label>
            </section>

            <section class="filter-group">
              <h3>Discount</h3>
              <label
                v-for="option in discountOptions"
                :key="option.value"
                class="choice-row choice-radio"
                :class="{ active: selectedDiscount === option.value }"
              >
                <input
                  v-model="selectedDiscount"
                  type="radio"
                  name="discount-filter"
                  :value="option.value"
                />
                <span>{{ option.label }}</span>
              </label>
            </section>

            <section class="filter-group">
              <h3>Release date</h3>
              <label
                v-for="option in releaseSortOptions"
                :key="option.value"
                class="choice-row choice-radio"
                :class="{ active: selectedReleaseSort === option.value }"
              >
                <input
                  v-model="selectedReleaseSort"
                  type="radio"
                  name="release-sort"
                  :value="option.value"
                />
                <span>{{ option.label }}</span>
              </label>
            </section>
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
                    <th class="col-game">Game</th>
                    <th class="col-genre">Genre</th>
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
                            <div class="game-badges">
                              <span v-if="game.releaseDate" class="meta-badge">{{ formatDateOnly(game.releaseDate, undefined, selectedLanguage) || game.releaseDate }}</span>
                              <span v-if="game.bestPrice !== null && game.bestPrice !== undefined" class="meta-badge">{{ formatPrice(game.bestPrice) }}</span>
                              <span v-if="game.bestDiscount !== null && game.bestDiscount !== undefined" class="meta-badge">
                                {{ game.bestDiscount }}% off
                              </span>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="cell-genre">{{ game.genre || 'Unknown' }}</td>
                    </tr>
                  </template>

                  <tr v-if="isLoading">
                    <td colspan="2" class="empty-state">
                      <div class="empty-content">
                        <p>Loading games...</p>
                      </div>
                    </td>
                  </tr>

                  <tr v-else-if="loadError">
                    <td colspan="2" class="empty-state">
                      <div class="empty-content">
                        <p>{{ loadError }}</p>
                      </div>
                    </td>
                  </tr>

                  <tr v-else-if="games.length === 0">
                    <td colspan="2" class="empty-state">
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
                    <td colspan="2" class="empty-state">
                      <div class="empty-content">
                        <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                          <path d="M20 20V14M17 17H23M4 20V4m0 0 4 4m-4-4-4 4" />
                        </svg>
                        <p>{{ searchQuery.trim() ? 'No games match your search' : 'No games match the selected filters' }}</p>
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
  padding: 3rem 2rem;
}

.content-wrapper {
  max-width: 1280px;
  margin: 0 auto;
}

.layout-shell {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  gap: 1.5rem;
  align-items: start;
}

.layout-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
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
  padding: 1.25rem;
  border: 1px solid var(--border-color);
  border-radius: 18px;
  background: color-mix(in srgb, var(--bg-primary) 88%, var(--bg-secondary));
  box-shadow: 0 18px 45px rgba(0, 0, 0, 0.06);
}

.reset-button {
  border: 1px solid var(--border-color);
  background: transparent;
  color: var(--text-primary);
  border-radius: 999px;
  padding: 0.55rem 0.85rem;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s ease, border-color 0.2s ease, background-color 0.2s ease;
}

.reset-button:hover {
  transform: translateY(-1px);
  border-color: var(--accent-color);
  background: color-mix(in srgb, var(--accent-color) 10%, transparent);
}

.filter-group {
  padding: 1rem 0;
  border-top: 1px solid var(--border-color);
}

.filter-group:first-of-type {
  border-top: none;
  padding-top: 0;
}

.filter-group h3 {
  margin-bottom: 0.85rem;
  font-size: 0.9rem;
  font-weight: 700;
}

.choice-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.75rem;
  border-radius: 12px;
  color: var(--text-primary);
  cursor: pointer;
  transition: background-color 0.2s ease, transform 0.2s ease;
}

.choice-row:hover {
  background: var(--hover-bg);
  transform: translateX(2px);
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
  gap: 1rem;
  margin-bottom: 0.85rem;
  padding: 0 0.25rem;
  color: var(--text-secondary);
  font-size: 0.85rem;
}

/* table */
.table-container {
  border: 1px solid var(--border-color);
  border-radius: 18px;
  overflow: hidden;
  background: color-mix(in srgb, var(--bg-primary) 96%, var(--bg-secondary));
}

.games-table {
  width: 100%;
  border-collapse: collapse;
}

.games-table thead {
  background: var(--bg-secondary);
}

.games-table th {
  padding: 0.875rem 1.5rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid var(--border-color);
}

.games-table td {
  padding: 1rem 1.5rem;
  font-size: 0.9375rem;
  color: var(--text-primary);
  border-bottom: 1px solid var(--border-color);
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
  width: 40%;
}

.game-info {
  display: flex;
  align-items: center;
  gap: 1rem;
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
  border: 1px solid var(--border-color);
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
  padding: 0.24rem 0.55rem;
  font-size: 0.72rem;
  line-height: 1;
  color: var(--text-secondary);
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
}

.cell-genre {
  color: var(--text-secondary);
}

.empty-state {
  text-align: center;
  padding: 4rem 1.5rem !important;
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
  .layout-shell {
    grid-template-columns: 1fr;
  }

  .filters-panel {
    position: static;
  }
}

@media (max-width: 640px) {
  .main-content {
    padding: 2rem 1.5rem;
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
    padding: 0.75rem 1rem;
  }

  .game-logo {
    width: 36px;
    height: 36px;
    border-radius: 6px;
  }

  .game-info {
    gap: 0.75rem;
  }

  .results-summary {
    flex-direction: column;
    gap: 0.35rem;
  }
}
</style>
