<script setup>
import { onMounted, provide, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { useThemePreference } from '../composables/useThemePreference'

const router = useRouter()
const selectedLanguage = ref('ENG')
const selectedTheme = useThemePreference()
const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')
const isLoading = ref(false)
const loadError = ref('')

provide('theme', selectedTheme)

const games = ref([])

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

const goToGame = (id) => {
  router.push(`/game/${id}`)
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
        <header class="page-header">
          <h1>Games We Track</h1>
        </header>

        <div class="table-container">
          <table class="games-table">
            <thead>
              <tr>
                <th class="col-game">Game</th>
                <th class="col-genre">Genre</th>
              </tr>
            </thead>
            <tbody>
              <template v-if="!isLoading && !loadError && games.length > 0">
                <tr v-for="game in games" :key="game.id" class="game-row" @click="goToGame(game.id)">
                  <td class="cell-game">
                    <div class="game-info">
                      <div v-if="game.logo" class="game-logo-wrap">
                        <img :src="game.logo" :alt="game.name" class="game-logo" />
                      </div>
                      <div v-else class="game-logo-placeholder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                          <circle cx="8.5" cy="8.5" r="1.5"/>
                          <polyline points="21 15 16 10 5 21"/>
                        </svg>
                      </div>
                      <span class="game-name">{{ game.name }}</span>
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
                      <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                      <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                      <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                    <p>No games available yet</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
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

.page-header {
  margin-bottom: 3rem;
}

.page-header h1 {
  text-align: center;
  font-size: 3rem;
  font-weight: 600;
  line-height: 1.1;
  letter-spacing: -1px;
  margin-bottom: 1rem;
  color: var(--text-primary);
}

/* table */
.table-container {
  border: 1px solid var(--border-color);
  border-radius: 12px;
  overflow: hidden;
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
  .page-header h1 {
    font-size: 2.5rem;
  }
}

@media (max-width: 640px) {
  .main-content {
    padding: 2rem 1.5rem;
  }

  .page-header h1 {
    font-size: 2rem;
  }

  .page-header {
    margin-bottom: 2rem;
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
}
</style>
