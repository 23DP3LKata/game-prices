<script setup>
import { computed, onMounted, provide, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
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
const statusType = ref('')
const statusMessage = ref('')

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
    setStatus('success', data?.message || 'Command completed.')

    await loadUsers()
  } catch (err) {
    setStatus('error', err instanceof Error ? err.message : 'Sync command failed.')
  } finally {
    isSyncRunning.value = false
  }
}

function formatDate(value) {
  if (!value) {
    return '-'
  }

  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? '-' : date.toLocaleString()
}

onMounted(async () => {
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

        <section v-if="activeTab === 'sync'" class="panel-card">
          <div class="actions">
            <button class="action-btn primary" :disabled="isSyncRunning" @click="runSync('prices')">
              {{ isSyncRunning ? 'Running...' : 'Run prices sync' }}
            </button>
            <button class="action-btn" :disabled="isSyncRunning" @click="runSync('listings')">
              {{ isSyncRunning ? 'Running...' : 'Run listings sync' }}
            </button>
          </div>

          <div v-if="commandOutput" class="output-wrap">
            <p class="output-title">Command output</p>
            <pre class="command-output">{{ commandOutput }}</pre>
          </div>
        </section>

        <section v-else class="panel-card">
          <div v-if="isLoadingUsers" class="loading">Loading users...</div>
          <div v-else class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nickname</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Verified</th>
                  <th>Created</th>
                  <th>Updated</th>
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
                  <td>{{ item.email_verified_at ? 'Yes' : 'No' }}</td>
                  <td>{{ formatDate(item.created_at) }}</td>
                  <td>{{ formatDate(item.updated_at) }}</td>
                </tr>
                <tr v-if="users.length === 0">
                  <td colspan="7" class="empty">No users found.</td>
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
}

.admin-page.dark {
  --bg-primary: #1b1d21;
  --bg-secondary: #25282e;
  --text-primary: #f5f5f7;
  --text-secondary: #a6aab3;
  --border-color: #545a65;
  --hover-bg: #2f333b;
  --accent-color: #2997ff;
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

.panel-card h2 {
  font-size: 1.125rem;
  margin-bottom: 0.3rem;
}

.muted {
  color: var(--text-secondary);
  margin-bottom: 1rem;
}

.actions {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.75rem;
}

.action-btn {
  border: 1px solid var(--border-color);
  background: transparent;
  color: var(--text-primary);
  border-radius: 8px;
  padding: 0.6rem 0.9rem;
  cursor: pointer;
}

.action-btn.primary {
  background: var(--accent-color);
  border-color: var(--accent-color);
  color: #fff;
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
}
</style>
