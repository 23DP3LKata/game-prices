<script setup>
import { computed, onBeforeUnmount, onMounted, provide, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { formatDateTime } from '../composables/useDateTimeFormat'
import { useAuthStore } from '../stores/auth'
import { useThemePreference } from '../composables/useThemePreference'

const router = useRouter()
const authStore = useAuthStore()

const selectedLanguage = ref('ENG')
const selectedTheme = useThemePreference()
const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')

const activeTab = ref('sync')
const users = ref([])
const isLoadingUsers = ref(false)
const isSyncRunning = ref(false)
const commandOutput = ref('')
const selectedLogId = ref(null)
const selectedLogOutput = ref('')
const selectedUserMenuId = ref(null)
const statusType = ref('')
const statusMessage = ref('')
const syncOverview = ref({
  stores_total: 0,
  games_total: 0,
  latest_prices_at: null,
  latest_listings_at: null,
  logs: [],
})

provide('theme', selectedTheme)

const isAdmin = computed(() => authStore.user?.role === 'admin')

function clearStatus() {
  statusType.value = ''
  statusMessage.value = ''
}

function setStatus(type, message) {
  statusType.value = type
  statusMessage.value = message
}

async function loadUsers() {
  isLoadingUsers.value = true

  try {
    const response = await fetch(apiBaseUrl ? `${apiBaseUrl}/admin/users` : '/api/admin/users', {
      method: 'GET',
      credentials: 'include',
      headers: {
        Accept: 'application/json',
      },
    })

    const data = await response.json().catch(() => null)

    if (response.status === 401) {
      authStore.clearUser()
      await router.push('/login')
      return
    }

    if (response.status === 403) {
      await router.push('/')
      return
    }

    if (!response.ok) {
      throw new Error(data?.message || 'Unable to load users.')
    }

    users.value = data?.users ?? []
    syncOverview.value = {
      stores_total: data?.sync_overview?.stores_total ?? 0,
      games_total: data?.sync_overview?.games_total ?? 0,
      latest_prices_at: data?.sync_overview?.latest_prices_at ?? null,
      latest_listings_at: data?.sync_overview?.latest_listings_at ?? null,
      logs: data?.sync_overview?.logs ?? [],
    }
  } catch (err) {
    setStatus('error', err instanceof Error ? err.message : 'Unable to load users.')
  } finally {
    isLoadingUsers.value = false
  }
}

async function runSync(command) {
  clearStatus()
  isSyncRunning.value = true
  commandOutput.value = ''

  try {
    const endpoint = command === 'prices' ? '/admin/sync-prices' : '/admin/sync-listings'
    const response = await fetch(apiBaseUrl ? `${apiBaseUrl}${endpoint}` : `/api${endpoint}`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        Accept: 'application/json',
      },
    })

    const data = await response.json().catch(() => null)

    if (response.status === 401) {
      authStore.clearUser()
      await router.push('/login')
      return
    }

    if (response.status === 403) {
      await router.push('/')
      return
    }

    if (!response.ok) {
      throw new Error(data?.message || 'Sync command failed.')
    }

    commandOutput.value = data?.output || 'No output.'
    syncOverview.value = {
      stores_total: data?.sync_overview?.stores_total ?? syncOverview.value.stores_total,
      games_total: data?.sync_overview?.games_total ?? syncOverview.value.games_total,
      latest_prices_at: data?.sync_overview?.latest_prices_at ?? syncOverview.value.latest_prices_at,
      latest_listings_at: data?.sync_overview?.latest_listings_at ?? syncOverview.value.latest_listings_at,
      logs: data?.sync_overview?.logs ?? syncOverview.value.logs,
    }
    setStatus('success', data?.message || 'Command completed.')

    await loadUsers()
  } catch (err) {
    setStatus('error', err instanceof Error ? err.message : 'Sync command failed.')
  } finally {
    isSyncRunning.value = false
  }
}

function formatDate(value) {
  return formatDateTime(value, undefined, selectedLanguage.value) || '-'
}

function formatSyncType(value) {
  return value === 'prices' ? 'Prices' : 'Listings'
}

function toggleLogOutput(log) {
  if (selectedLogId.value === log.id) {
    selectedLogId.value = null
    selectedLogOutput.value = ''
    return
  }

  selectedLogId.value = log.id
  selectedLogOutput.value = log.output && String(log.output).trim() !== ''
    ? log.output
    : 'No output for this sync entry.'
}

function toggleUserMenu(userId) {
  selectedUserMenuId.value = selectedUserMenuId.value === userId ? null : userId
}

function closeUserMenu() {
  selectedUserMenuId.value = null
}

function onBlockUserClick(user) {
  closeUserMenu()
  clearStatus()

  if (user.account_status === 'inactive') {
    setStatus('error', `${user.nickname} is already blocked.`)
    return
  }

  fetch(apiBaseUrl ? `${apiBaseUrl}/admin/users/${user.id}/block` : `/api/admin/users/${user.id}/block`, {
    method: 'POST',
    credentials: 'include',
    headers: {
      Accept: 'application/json',
    },
  })
    .then(async (response) => {
      const data = await response.json().catch(() => null)

      if (response.status === 401) {
        authStore.clearUser()
        await router.push('/login')
        return
      }

      if (response.status === 403) {
        await router.push('/')
        return
      }

      if (!response.ok) {
        throw new Error(data?.message || 'Unable to block user.')
      }

      users.value = data?.users ?? users.value.map((item) => {
        if (item.id !== user.id) {
          return item
        }

        return {
          ...item,
          account_status: 'inactive',
        }
      })

      syncOverview.value = {
        stores_total: data?.sync_overview?.stores_total ?? syncOverview.value.stores_total,
        games_total: data?.sync_overview?.games_total ?? syncOverview.value.games_total,
        latest_prices_at: data?.sync_overview?.latest_prices_at ?? syncOverview.value.latest_prices_at,
        latest_listings_at: data?.sync_overview?.latest_listings_at ?? syncOverview.value.latest_listings_at,
        logs: data?.sync_overview?.logs ?? syncOverview.value.logs,
      }

      setStatus('success', data?.message || `${user.nickname} has been blocked.`)
    })
    .catch((err) => {
      setStatus('error', err instanceof Error ? err.message : 'Unable to block user.')
    })
}

function handleDocumentClick(event) {
  const target = event.target

  if (!(target instanceof Element)) {
    return
  }

  if (!target.closest('.user-menu-wrap')) {
    closeUserMenu()
  }
}

onMounted(async () => {
  document.addEventListener('click', handleDocumentClick)

  if (!authStore.isLoggedIn) {
    await router.push('/login')
    return
  }

  if (!isAdmin.value) {
    await router.push('/')
    return
  }

  await loadUsers()
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
})
</script>

<template>
  <div class="admin-page" :class="selectedTheme">
    <AppHeader
      v-model:selectedTheme="selectedTheme"
      v-model:selectedLanguage="selectedLanguage"
      activePage=""
    />

    <nav class="tabs">
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'sync' }"
        @click="activeTab = 'sync'"
      >
        Sync
      </button>
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'users' }"
        @click="activeTab = 'users'"
      >
        Users
      </button>
    </nav>

    <main class="main-content">
      <section class="admin-shell">
        <div v-if="statusMessage" class="status" :class="statusType">
          {{ statusMessage }}
        </div>

        <template v-if="activeTab === 'sync'">
          <div class="sync-layout">
            <section class="panel-card sync-run-card">
              <div class="sync-container">
                <div class="sync-block">
                  <button class="action-btn primary" :disabled="isSyncRunning" @click="runSync('prices')">
                    {{ isSyncRunning ? 'Running...' : 'Run prices sync' }}
                  </button>
                  <div class="sync-meta">
                    <span class="sync-meta-label">Last prices sync</span>
                    <span class="sync-meta-value">{{ formatDate(syncOverview.latest_prices_at) }}</span>
                  </div>
                </div>

                <div class="sync-block">
                  <button class="action-btn" :disabled="isSyncRunning" @click="runSync('listings')">
                    {{ isSyncRunning ? 'Running...' : 'Run listings sync' }}
                  </button>
                  <div class="sync-meta">
                    <span class="sync-meta-label">Last listings sync</span>
                    <span class="sync-meta-value">{{ formatDate(syncOverview.latest_listings_at) }}</span>
                  </div>
                </div>
              </div>

              <div v-if="commandOutput" class="output-wrap">
                <p class="output-title">Command output</p>
                <pre class="command-output">{{ commandOutput }}</pre>
              </div>
            </section>

            <section class="panel-card sync-in-card">
              <table class="sync-table">
                <thead>
                  <tr>
                    <th>In sync</th>
                    <th>Value</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Stores</td>
                    <td>{{ syncOverview.stores_total }} total</td>
                  </tr>
                  <tr>
                    <td>Games</td>
                    <td>{{ syncOverview.games_total }} total</td>
                  </tr>
                </tbody>
              </table>
            </section>
          </div>

          <section class="panel-card logs-card">
            <div class="logs-wrap">
              <p class="logs-title">Sync logs</p>

              <div class="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Type</th>
                      <th>Status</th>
                      <th>Stores</th>
                      <th>Games</th>
                        <th class="logs-actions-col"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="log in syncOverview.logs" :key="`log-${log.id}`">
                      <td>{{ formatDate(log.finished_at || log.created_at) }}</td>
                      <td>{{ formatSyncType(log.sync_type) }}</td>
                      <td>
                        <span class="status-pill" :class="log.status">{{ log.status }}</span>
                      </td>
                      <td>{{ log.stores_total }}</td>
                      <td>{{ log.games_total }}</td>
                      <td class="logs-actions-cell">
                        <button
                          class="log-menu-btn"
                          :class="{ active: selectedLogId === log.id }"
                          @click="toggleLogOutput(log)"
                          aria-label="Show sync output"
                        >
                          <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                            <circle cx="8" cy="3" r="1.25" />
                            <circle cx="8" cy="8" r="1.25" />
                            <circle cx="8" cy="13" r="1.25" />
                          </svg>
                        </button>
                      </td>
                    </tr>
                    <tr v-if="!syncOverview.logs.length">
                      <td colspan="6" class="empty">No sync logs yet.</td>
                    </tr>
                  </tbody>
                </table>

                <div v-if="selectedLogId !== null" class="log-output-wrap">
                  <p class="output-title">Log output</p>
                  <pre class="command-output">{{ selectedLogOutput }}</pre>
                </div>
              </div>
            </div>
          </section>
        </template>

        <section v-else class="panel-card">
          <div v-if="isLoadingUsers" class="loading">Loading users...</div>
          <div v-else class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Verified</th>
                  <th>Created</th>
                  <th>Updated</th>
                  <th class="users-actions-col"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in users" :key="item.id">
                  <td>{{ item.id }}</td>
                  <td>{{ item.nickname }}</td>
                  <td>{{ item.email }}</td>
                  <td>
                    <span class="role-pill" :class="{ admin: item.role === 'admin' }">{{ item.role }}</span>
                  </td>
                  <td>
                    <span class="status-pill" :class="item.account_status === 'inactive' ? 'failed' : 'success'">
                      {{ item.account_status === 'inactive' ? 'blocked' : 'active' }}
                    </span>
                  </td>
                  <td>{{ item.email_verified_at ? 'Yes' : 'No' }}</td>
                  <td>{{ formatDate(item.created_at) }}</td>
                  <td>{{ formatDate(item.updated_at) }}</td>
                  <td class="users-actions-cell">
                    <div class="user-menu-wrap">
                      <button
                        type="button"
                        class="log-menu-btn"
                        :class="{ active: selectedUserMenuId === item.id }"
                        aria-label="Open user actions"
                        @click.stop="toggleUserMenu(item.id)"
                      >
                        <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                          <circle cx="8" cy="3" r="1.25" />
                          <circle cx="8" cy="8" r="1.25" />
                          <circle cx="8" cy="13" r="1.25" />
                        </svg>
                      </button>

                      <div v-if="selectedUserMenuId === item.id" class="user-menu" @click.stop>
                        <button
                          type="button"
                          class="user-menu-action"
                          @click="onBlockUserClick(item)"
                        >
                          Block user
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr v-if="users.length === 0">
                  <td colspan="9" class="empty">No users found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>
    </main>

    <footer class="footer">
      <div class="footer-container">
        <span class="footer-text">&copy; 2025 Game Prices</span>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.admin-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.admin-page.light {
  --bg-primary: #ffffff;
  --bg-secondary: #f5f5f7;
  --text-primary: #1d1d1f;
  --text-secondary: #86868b;
  --border-color: #d2d2d7;
  --hover-bg: #f5f5f7;
  --accent-color: #0071e3;
  --sync-btn-bg: #ffffff;
  --sync-btn-text: #1d1d1f;
}

.admin-page.dark {
  --bg-primary: #1b1d21;
  --bg-secondary: #25282e;
  --text-primary: #f5f5f7;
  --text-secondary: #a6aab3;
  --border-color: #545a65;
  --hover-bg: #2f333b;
  --accent-color: #2997ff;
  --sync-btn-bg: #2b2f36;
  --sync-btn-text: #f5f5f7;
}

.admin-page {
  background: var(--bg-primary);
  color: var(--text-primary);
}

.main-content {
  flex: 1;
  padding: 1.25rem 2rem 3rem;
}

.admin-shell {
  max-width: 1180px;
  margin: 0 auto;
}

.tabs {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem 1rem 0;
}

.tab-btn {
  border: 1px solid var(--border-color);
  background: transparent;
  color: var(--text-primary);
  border-radius: 8px;
  padding: 0.5rem 1rem;
  cursor: pointer;
  transition: border-color 0.2s ease, color 0.2s ease, background-color 0.2s ease;
}

.tab-btn:hover {
  border-color: var(--text-primary);
  color: var(--text-primary);
}

.tab-btn.active {
  background: var(--text-primary);
  color: var(--bg-primary);
  border-color: var(--text-primary);
}

.panel-card {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1rem;
}

.sync-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
  gap: 1rem;
}

.sync-run-card,
.sync-in-card {
  min-height: 100%;
}

.logs-card {
  margin-top: 1rem;
}

.panel-card h2 {
  font-size: 1.125rem;
  margin-bottom: 0.3rem;
}

.muted {
  color: var(--text-secondary);
  margin-bottom: 1rem;
}

.sync-block {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.4rem;
  flex: 1;
}

.sync-container {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.sync-meta {
  width: 100%;
  padding: 0.15rem 0 0;
}

.sync-summary {
  margin-top: 0;
  width: 100%;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 0.65rem 0.75rem;
  background: var(--bg-primary);
}

.sync-meta-label {
  display: block;
  font-size: 0.72rem;
  color: var(--text-secondary);
}

.sync-meta-value {
  display: block;
  margin-top: 0.1rem;
  font-size: 0.84rem;
  color: var(--text-primary);
}

.sync-table {
  width: 100%;
  border-collapse: collapse;
}

.sync-table th,
.sync-table td {
  border-bottom: 1px solid var(--border-color);
  text-align: left;
  padding: 0.75rem 0.4rem;
}

.sync-table th {
  font-weight: 600;
  font-size: 0.78rem;
  color: var(--text-secondary);
}

.sync-table td:last-child {
  color: var(--text-primary);
}

.logs-actions-col,
.logs-actions-cell {
  width: 2.25rem;
  min-width: 2.25rem;
  text-align: center;
  padding-left: 0.2rem;
  padding-right: 0.2rem;
}

.log-menu-btn {
  width: 1.5rem;
  height: 1.5rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 0;
  background: transparent;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 0;
  transition: color 0.2s ease, opacity 0.2s ease;
}

.log-menu-btn svg {
  width: 0.8rem;
  height: 0.8rem;
}

.log-menu-btn:hover,
.log-menu-btn.active {
  color: var(--text-primary);
  background: transparent;
}

.users-actions-col,
.users-actions-cell {
  width: 2.25rem;
  min-width: 2.25rem;
  text-align: center;
  padding-left: 0.2rem;
  padding-right: 0.2rem;
}

.user-menu-wrap {
  position: relative;
  display: inline-flex;
}

.user-menu {
  position: absolute;
  top: calc(100% + 0.25rem);
  right: 0;
  min-width: 180px;
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 0.3rem;
  z-index: 20;
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.16);
}

.user-menu-action {
  width: 100%;
  border: none;
  background: transparent;
  color: var(--text-primary);
  text-align: left;
  padding: 0.45rem 0.55rem;
  border-radius: 6px;
  cursor: pointer;
}

.user-menu-action:hover {
  background: var(--hover-bg);
}

.log-output-wrap {
  margin-top: 0.75rem;
}

.action-btn {
  border: 1px solid var(--border-color);
  background: var(--sync-btn-bg);
  color: var(--sync-btn-text);
  border-radius: 8px;
  padding: 0.9rem 1.25rem;
  cursor: pointer;
  font-size: 1rem;
  width: 100%;
  transition: border-color 0.2s ease, color 0.2s ease, background-color 0.2s ease;
}

.action-btn.primary {
  background: var(--sync-btn-bg);
  border-color: var(--border-color);
  color: var(--sync-btn-text);
}

.action-btn:hover:not(:disabled) {
  border-color: var(--text-primary);
  color: var(--text-primary);
}

.action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.output-wrap {
  margin-top: 1rem;
}

.output-title {
  font-size: 0.9rem;
  margin-bottom: 0.4rem;
}

.command-output {
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 0.75rem;
  white-space: pre-wrap;
  background: var(--bg-primary);
  max-height: 320px;
  overflow: auto;
}

.status {
  border-radius: 8px;
  padding: 0.6rem 0.8rem;
  margin-bottom: 1rem;
  border: 1px solid transparent;
}

.status.success {
  border-color: #2ea043;
  color: #2ea043;
}

.status.error {
  border-color: #d1242f;
  color: #d1242f;
}

.table-wrap {
  overflow-x: auto;
}

.logs-wrap {
  margin-top: 1rem;
}

.logs-title {
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
  color: var(--text-secondary);
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

th,
td {
  border-bottom: 1px solid var(--border-color);
  text-align: left;
  padding: 0.55rem 0.4rem;
}

th {
  font-weight: 600;
}

.role-pill {
  border: 1px solid var(--border-color);
  border-radius: 999px;
  padding: 0.15rem 0.55rem;
  font-size: 0.75rem;
}

.role-pill.admin {
  border-color: var(--accent-color);
  color: var(--accent-color);
}

.status-pill {
  display: inline-flex;
  border: 1px solid var(--border-color);
  border-radius: 999px;
  padding: 0.15rem 0.55rem;
  font-size: 0.75rem;
  text-transform: capitalize;
}

.status-pill.success {
  color: #2ea043;
  border-color: #2ea043;
}

.status-pill.failed {
  color: #d1242f;
  border-color: #d1242f;
}

.empty,
.loading {
  color: var(--text-secondary);
  text-align: center;
  padding: 1rem;
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

@media (max-width: 768px) {
  .main-content {
    padding: 1rem 1rem 2rem;
  }

  .sync-layout {
    grid-template-columns: 1fr;
  }

  .sync-container {
    grid-template-columns: 1fr;
  }
}
</style>
