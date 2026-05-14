<script setup>
import { computed, onBeforeUnmount, onMounted, provide, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { formatDateTime } from '../composables/useDateTimeFormat'
import { useAuthStore } from '../stores/auth'
import { useThemePreference } from '../composables/useThemePreference'
import { useI18nStore } from '../stores/i18n'

const router = useRouter()
const authStore = useAuthStore()

const selectedLanguage = ref('LV')
const selectedTheme = useThemePreference()
const i18n = useI18nStore()
const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')

const activeTab = ref('sync')
const users = ref([])
const isLoadingUsers = ref(false)
const userIdFilter = ref('')
const userNicknameFilter = ref('')
const userEmailFilter = ref('')
const userRoleFilter = ref('')
const userStatusFilter = ref('')
const userVerifiedFilter = ref('')
const userCreatedFilter = ref('')
const userUpdatedFilter = ref('')
const isSyncRunning = ref(false)
const commandOutput = ref('')
const selectedLogId = ref(null)
const selectedLogOutput = ref('')
const selectedUserMenuId = ref(null)
const selectedUserMenuUser = ref(null)
const selectedUserMenuPosition = ref({ top: 0, left: 0 })
const actionDialogOpen = ref(false)
const actionDialogMode = ref('')
const actionDialogUser = ref(null)
const actionNickname = ref('')
const actionLoading = ref(false)
const actionError = ref('')
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

const filteredUsers = computed(() => {
  const idQuery = userIdFilter.value.trim()
  const nicknameQuery = userNicknameFilter.value.trim().toLowerCase()
  const emailQuery = userEmailFilter.value.trim().toLowerCase()
  const roleQuery = userRoleFilter.value
  const statusQuery = userStatusFilter.value
  const verifiedQuery = userVerifiedFilter.value
  const createdQuery = userCreatedFilter.value.trim()
  const updatedQuery = userUpdatedFilter.value.trim()

  return users.value.filter((item) => {
    const idMatches = !idQuery || String(item.id).includes(idQuery)
    const nicknameMatches = !nicknameQuery || String(item.nickname || '').toLowerCase().includes(nicknameQuery)
    const emailMatches = !emailQuery || String(item.email || '').toLowerCase().includes(emailQuery)
    const roleMatches = !roleQuery || item.role === roleQuery
    const statusMatches = !statusQuery || item.account_status === statusQuery
    const verifiedMatches =
      !verifiedQuery ||
      (verifiedQuery === 'yes' && Boolean(item.email_verified_at)) ||
      (verifiedQuery === 'no' && !item.email_verified_at)
    const createdMatches = !createdQuery || String(item.created_at || '').slice(0, 10).includes(createdQuery)
    const updatedMatches = !updatedQuery || String(item.updated_at || '').slice(0, 10).includes(updatedQuery)

    return idMatches && nicknameMatches && emailMatches && roleMatches && statusMatches && verifiedMatches && createdMatches && updatedMatches
  })
})

const hasActiveUserFilters = computed(() =>
  userIdFilter.value.trim() !== '' ||
  userNicknameFilter.value.trim() !== '' ||
  userEmailFilter.value.trim() !== '' ||
  userRoleFilter.value !== '' ||
  userStatusFilter.value !== '' ||
  userVerifiedFilter.value !== '' ||
  userCreatedFilter.value.trim() !== '' ||
  userUpdatedFilter.value.trim() !== ''
)

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
      throw new Error(data?.message || i18n.t('admin.errors.load_users'))
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
    setStatus('error', err instanceof Error ? err.message : i18n.t('admin.errors.load_users'))
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
      throw new Error(data?.message || i18n.t('admin.errors.sync_failed'))
    }

    commandOutput.value = data?.output || i18n.t('admin.no_output')
    syncOverview.value = {
      stores_total: data?.sync_overview?.stores_total ?? syncOverview.value.stores_total,
      games_total: data?.sync_overview?.games_total ?? syncOverview.value.games_total,
      latest_prices_at: data?.sync_overview?.latest_prices_at ?? syncOverview.value.latest_prices_at,
      latest_listings_at: data?.sync_overview?.latest_listings_at ?? syncOverview.value.latest_listings_at,
      logs: data?.sync_overview?.logs ?? syncOverview.value.logs,
    }
    setStatus('success', data?.message || i18n.t('admin.command_completed'))

    await loadUsers()
  } catch (err) {
    setStatus('error', err instanceof Error ? err.message : i18n.t('admin.errors.sync_failed'))
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

function resetUserFilters() {
  userIdFilter.value = ''
  userNicknameFilter.value = ''
  userEmailFilter.value = ''
  userRoleFilter.value = ''
  userStatusFilter.value = ''
  userVerifiedFilter.value = ''
  userCreatedFilter.value = ''
  userUpdatedFilter.value = ''
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
    : i18n.t('admin.no_sync_output')
}

function positionUserMenu(triggerElement) {
  const menuWidth = 260
  const gap = 30
  const viewportPadding = 12
  const triggerRect = triggerElement.getBoundingClientRect()

  let nextLeft = triggerRect.right + gap
  if (nextLeft + menuWidth > window.innerWidth - viewportPadding) {
    nextLeft = Math.max(viewportPadding, triggerRect.left - menuWidth - gap)
  }

  selectedUserMenuPosition.value = {
    top: Math.max(viewportPadding, triggerRect.top),
    left: nextLeft,
  }
}

function toggleUserMenu(user, event) {
  if (selectedUserMenuId.value === user.id) {
    closeUserMenu()
    return
  }

  selectedUserMenuId.value = user.id
  selectedUserMenuUser.value = user
  positionUserMenu(event.currentTarget)
}

function closeUserMenu() {
  selectedUserMenuId.value = null
  selectedUserMenuUser.value = null
}

function closeActionDialog() {
  actionDialogOpen.value = false
  actionDialogMode.value = ''
  actionDialogUser.value = null
  actionNickname.value = ''
  actionLoading.value = false
  actionError.value = ''
}

function openActionDialog(mode, user) {
  closeUserMenu()
  clearStatus()

  actionDialogMode.value = mode
  actionDialogUser.value = user
  actionNickname.value = user.nickname
  actionError.value = ''
  actionLoading.value = false
  actionDialogOpen.value = true
}

function onRenameUserClick(user) {
  openActionDialog('rename', user)
}

function onToggleUserStatusClick(user) {
  openActionDialog(user.account_status === 'inactive' ? 'unblock' : 'block', user)
}

function onDeleteUserClick(user) {
  openActionDialog('delete', user)
}

function actionDialogTitle() {
  if (actionDialogMode.value === 'rename') {
    return i18n.t('admin.modal.rename_title')
  }

  if (actionDialogMode.value === 'delete') {
    return i18n.t('admin.modal.delete_title')
  }

  return actionDialogMode.value === 'unblock'
    ? i18n.t('admin.modal.unblock_title')
    : i18n.t('admin.modal.block_title')
}

function actionDialogSubtitle() {
  if (!actionDialogUser.value) {
    return ''
  }

  if (actionDialogMode.value === 'rename') {
    return i18n.t('admin.modal.rename_subtitle').replace('{{name}}', actionDialogUser.value.nickname)
  }

  if (actionDialogMode.value === 'delete') {
    return i18n.t('admin.confirm.delete_user').replace('{{name}}', actionDialogUser.value.nickname)
  }

  return actionDialogMode.value === 'unblock'
    ? i18n.t('admin.modal.unblock_subtitle').replace('{{name}}', actionDialogUser.value.nickname)
    : i18n.t('admin.modal.block_subtitle').replace('{{name}}', actionDialogUser.value.nickname)
}

function actionDialogConfirmLabel() {
  if (actionDialogMode.value === 'rename') {
    return i18n.t('admin.confirm.rename_user')
  }

  if (actionDialogMode.value === 'delete') {
    return i18n.t('admin.delete_user')
  }

  return actionDialogMode.value === 'unblock'
    ? i18n.t('admin.unblock_user')
    : i18n.t('admin.block_user')
}

function actionDialogConfirmClass() {
  return actionDialogMode.value === 'delete' ? 'danger' : 'primary'
}

function actionDialogRequireInput() {
  return actionDialogMode.value === 'rename'
}

async function submitActionDialog() {
  if (!actionDialogUser.value) {
    return
  }

  const user = actionDialogUser.value
  const mode = actionDialogMode.value
  const trimmedNickname = actionNickname.value.trim()

  if (mode === 'rename' && (trimmedNickname === '' || trimmedNickname === user.nickname)) {
    actionError.value = i18n.t('admin.errors.rename_user')
    return
  }

  actionLoading.value = true
  actionError.value = ''

  try {
    let response

    if (mode === 'rename') {
      response = await fetch(apiBaseUrl ? `${apiBaseUrl}/admin/users/${user.id}/rename` : `/api/admin/users/${user.id}/rename`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ nickname: trimmedNickname }),
      })
    } else if (mode === 'delete') {
      response = await fetch(apiBaseUrl ? `${apiBaseUrl}/admin/users/${user.id}` : `/api/admin/users/${user.id}`, {
        method: 'DELETE',
        credentials: 'include',
        headers: {
          Accept: 'application/json',
        },
      })
    } else {
      const endpoint = mode === 'unblock' ? '/unblock' : '/block'
      response = await fetch(apiBaseUrl ? `${apiBaseUrl}/admin/users/${user.id}${endpoint}` : `/api/admin/users/${user.id}${endpoint}`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          Accept: 'application/json',
        },
      })
    }

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
      throw new Error(data?.message || i18n.t(`admin.errors.${mode === 'delete' ? 'delete_user' : mode === 'rename' ? 'rename_user' : 'block_user'}`))
    }

    if (Array.isArray(data?.users)) {
      users.value = data.users
    } else if (mode === 'rename') {
      users.value = users.value.map((item) => (item.id === user.id ? { ...item, nickname: data?.user?.nickname || trimmedNickname } : item))
    } else if (mode === 'delete') {
      users.value = users.value.filter((item) => item.id !== user.id)
    } else {
      users.value = users.value.map((item) => {
        if (item.id !== user.id) {
          return item
        }

        return {
          ...item,
          account_status: mode === 'unblock' ? 'active' : 'inactive',
        }
      })
    }

    if (data?.sync_overview) {
      syncOverview.value = {
        stores_total: data.sync_overview.stores_total ?? syncOverview.value.stores_total,
        games_total: data.sync_overview.games_total ?? syncOverview.value.games_total,
        latest_prices_at: data.sync_overview.latest_prices_at ?? syncOverview.value.latest_prices_at,
        latest_listings_at: data.sync_overview.latest_listings_at ?? syncOverview.value.latest_listings_at,
        logs: data.sync_overview.logs ?? syncOverview.value.logs,
      }
    }

    const fallbackName = mode === 'rename' ? trimmedNickname : user.nickname
    const successKey = mode === 'rename'
      ? 'admin.success.renamed'
      : mode === 'delete'
        ? 'admin.success.deleted'
        : mode === 'unblock'
          ? 'admin.success.unblocked'
          : 'admin.success.blocked'

    setStatus('success', data?.message || i18n.t(successKey).replace('{{name}}', fallbackName))
    closeActionDialog()
  } catch (err) {
    actionError.value = err instanceof Error ? err.message : i18n.t('admin.errors.sync_failed')
  } finally {
    actionLoading.value = false
  }
}

function handleDocumentClick(event) {
  const target = event.target

  if (!(target instanceof Element)) {
    return
  }

  if (!target.closest('.user-menu-wrap') && !target.closest('.user-menu-panel')) {
    closeUserMenu()
  }

  if (actionDialogOpen.value && !target.closest('.action-modal-card')) {
    closeActionDialog()
  }
}

onMounted(async () => {
  document.addEventListener('click', handleDocumentClick)
  window.addEventListener('scroll', closeUserMenu, true)
  window.addEventListener('resize', closeUserMenu)

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
  window.removeEventListener('scroll', closeUserMenu, true)
  window.removeEventListener('resize', closeUserMenu)
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
        {{ i18n.t('admin.tabs.sync') }}
      </button>
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'users' }"
        @click="activeTab = 'users'"
      >
        {{ i18n.t('admin.tabs.users') }}
      </button>
    </nav>

    <div v-if="actionDialogOpen" class="modal-backdrop action-modal-backdrop" @click.self="closeActionDialog">
      <div class="modal-card action-modal-card" role="dialog" aria-modal="true" :aria-labelledby="'admin-action-title'">
        <h2 id="admin-action-title">{{ actionDialogTitle() }}</h2>
        <p class="modal-subtitle">{{ actionDialogSubtitle() }}</p>

        <div v-if="actionError" class="error-banner modal-banner" role="alert">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
          </svg>
          <span>{{ actionError }}</span>
        </div>

        <div v-if="actionDialogRequireInput()" class="form-group">
          <label for="admin-action-nickname">{{ i18n.t('admin.nickname') }}</label>
          <input
            id="admin-action-nickname"
            v-model="actionNickname"
            type="text"
            autocomplete="off"
          />
        </div>

        <div class="modal-actions">
          <button type="button" class="secondary-btn" @click="closeActionDialog">
            {{ i18n.t('buttons.close') }}
          </button>
          <button
            type="button"
            class="action-btn"
            :class="actionDialogConfirmClass()"
            :disabled="actionLoading"
            @click="submitActionDialog"
          >
            <span v-if="!actionLoading">{{ actionDialogConfirmLabel() }}</span>
            <span v-else class="spinner"></span>
          </button>
        </div>
      </div>
    </div>

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
                    {{ isSyncRunning ? i18n.t('admin.running') : i18n.t('admin.run_prices') }}
                  </button>
                  <div class="sync-meta">
                    <span class="sync-meta-label">{{ i18n.t('admin.last_prices_sync') }}</span>
                    <span class="sync-meta-value">{{ formatDate(syncOverview.latest_prices_at) }}</span>
                  </div>
                </div>

                <div class="sync-block">
                  <button class="action-btn" :disabled="isSyncRunning" @click="runSync('listings')">
                    {{ isSyncRunning ? i18n.t('admin.running') : i18n.t('admin.run_listings') }}
                  </button>
                  <div class="sync-meta">
                    <span class="sync-meta-label">{{ i18n.t('admin.last_listings_sync') }}</span>
                    <span class="sync-meta-value">{{ formatDate(syncOverview.latest_listings_at) }}</span>
                  </div>
                </div>
              </div>

              <div v-if="commandOutput" class="output-wrap">
                <p class="output-title">{{ i18n.t('admin.command_output') }}</p>
                <pre class="command-output">{{ commandOutput }}</pre>
              </div>
            </section>

            <section class="panel-card sync-in-card">
              <table class="sync-table">
                <thead>
                  <tr>
                    <th>{{ i18n.t('admin.in_sync') }}</th>
                    <th>{{ i18n.t('admin.value') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>{{ i18n.t('admin.stores') }}</td>
                    <td>{{ syncOverview.stores_total }} {{ i18n.t('admin.total') }}</td>
                  </tr>
                  <tr>
                    <td>{{ i18n.t('admin.games') }}</td>
                    <td>{{ syncOverview.games_total }} {{ i18n.t('admin.total') }}</td>
                  </tr>
                </tbody>
              </table>
            </section>
          </div>

          <section class="panel-card logs-card">
            <div class="logs-wrap">
              <p class="logs-title">{{ i18n.t('admin.sync_logs') }}</p>

              <div class="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th>{{ i18n.t('admin.date') }}</th>
                      <th>{{ i18n.t('admin.type') }}</th>
                      <th>{{ i18n.t('admin.status') }}</th>
                      <th>{{ i18n.t('admin.stores') }}</th>
                      <th>{{ i18n.t('admin.games') }}</th>
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
                          :aria-label="i18n.t('admin.show_sync_output')"
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
                      <td colspan="6" class="empty">{{ i18n.t('admin.no_sync_logs') }}</td>
                    </tr>
                  </tbody>
                </table>

                <div v-if="selectedLogId !== null" class="log-output-wrap">
                  <p class="output-title">{{ i18n.t('admin.log_output') }}</p>
                  <pre class="command-output">{{ selectedLogOutput }}</pre>
                </div>
              </div>
            </div>
          </section>
        </template>

        <section v-else class="panel-card users-panel">
          <div v-if="isLoadingUsers" class="loading">{{ i18n.t('admin.loading_users') }}</div>
          <div v-else class="users-layout">
            <aside class="filters-panel users-filters-panel" :aria-label="i18n.t('admin.user_filters')">
              <section class="filter-group">
                <h3>{{ i18n.t('admin.filter_group.search') }}</h3>
                <label class="field-label" for="user-id-filter">{{ i18n.t('admin.user_id') }}</label>
                <input id="user-id-filter" v-model="userIdFilter" class="filter-input" type="text" :placeholder="i18n.t('admin.filter_placeholder.id')" autocomplete="off" />

                <label class="field-label" for="user-nickname-filter">{{ i18n.t('admin.username') }}</label>
                <input id="user-nickname-filter" v-model="userNicknameFilter" class="filter-input" type="search" :placeholder="i18n.t('admin.filter_placeholder.username')" autocomplete="off" />

                <label class="field-label" for="user-email-filter">{{ i18n.t('admin.email') }}</label>
                <input id="user-email-filter" v-model="userEmailFilter" class="filter-input" type="search" :placeholder="i18n.t('admin.filter_placeholder.email')" autocomplete="off" />
              </section>

              <section class="filter-group">
                <h3>{{ i18n.t('admin.filter_group.status') }}</h3>
                <label class="field-label" for="user-role-filter">{{ i18n.t('admin.role') }}</label>
                <select id="user-role-filter" v-model="userRoleFilter" class="filter-select">
                  <option value="">{{ i18n.t('admin.filter_option.all') }}</option>
                  <option value="admin">admin</option>
                  <option value="user">user</option>
                </select>

                <label class="field-label" for="user-status-filter">{{ i18n.t('admin.status') }}</label>
                <select id="user-status-filter" v-model="userStatusFilter" class="filter-select">
                  <option value="">{{ i18n.t('admin.filter_option.all') }}</option>
                  <option value="active">{{ i18n.t('admin.active') }}</option>
                  <option value="inactive">{{ i18n.t('admin.blocked') }}</option>
                </select>

                <label class="field-label" for="user-verified-filter">{{ i18n.t('admin.verified') }}</label>
                <select id="user-verified-filter" v-model="userVerifiedFilter" class="filter-select">
                  <option value="">{{ i18n.t('admin.filter_option.all') }}</option>
                  <option value="yes">{{ i18n.t('admin.yes') }}</option>
                  <option value="no">{{ i18n.t('admin.no') }}</option>
                </select>
              </section>

              <section class="filter-group">
                <h3>{{ i18n.t('admin.filter_group.dates') }}</h3>
                <label class="field-label" for="user-created-filter">{{ i18n.t('admin.created') }}</label>
                <input id="user-created-filter" v-model="userCreatedFilter" class="filter-input" type="date" />

                <label class="field-label" for="user-updated-filter">{{ i18n.t('admin.updated') }}</label>
                <input id="user-updated-filter" v-model="userUpdatedFilter" class="filter-input" type="date" />
              </section>

              <button
                type="button"
                class="reset-button-panel"
                :disabled="!hasActiveUserFilters"
                @click="resetUserFilters"
              >
                {{ i18n.t('admin.reset_filters') }}
              </button>
            </aside>

            <section class="results-panel users-results-panel">
              <div class="results-summary users-summary">
                <span>{{ filteredUsers.length }} {{ i18n.t('admin.shown') }}</span>
                <span v-if="users.length > 0">{{ users.length }} {{ i18n.t('admin.total') }}</span>
              </div>

              <div class="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>{{ i18n.t('admin.username') }}</th>
                      <th>{{ i18n.t('admin.email') }}</th>
                      <th>{{ i18n.t('admin.role') }}</th>
                      <th>{{ i18n.t('admin.status') }}</th>
                      <th>{{ i18n.t('admin.verified') }}</th>
                      <th>{{ i18n.t('admin.created') }}</th>
                      <th>{{ i18n.t('admin.updated') }}</th>
                      <th class="users-actions-col"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in filteredUsers" :key="item.id">
                      <td>{{ item.id }}</td>
                      <td>{{ item.nickname }}</td>
                      <td>{{ item.email }}</td>
                      <td>
                        <span class="role-pill" :class="{ admin: item.role === 'admin' }">{{ item.role }}</span>
                      </td>
                      <td>
                        <span class="status-pill" :class="item.account_status === 'inactive' ? 'failed' : 'success'">
                          {{ item.account_status === 'inactive' ? i18n.t('admin.blocked') : i18n.t('admin.active') }}
                        </span>
                      </td>
                      <td>{{ item.email_verified_at ? i18n.t('admin.yes') : i18n.t('admin.no') }}</td>
                      <td>{{ formatDate(item.created_at) }}</td>
                      <td>{{ formatDate(item.updated_at) }}</td>
                      <td class="users-actions-cell">
                        <div class="user-menu-wrap">
                          <button
                            type="button"
                            class="log-menu-btn"
                            :class="{ active: selectedUserMenuId === item.id }"
                            :aria-label="i18n.t('admin.open_user_actions')"
                            @click.stop="toggleUserMenu(item, $event)"
                          >
                            <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                              <circle cx="8" cy="3" r="1.25" />
                              <circle cx="8" cy="8" r="1.25" />
                              <circle cx="8" cy="13" r="1.25" />
                            </svg>
                          </button>

                        </div>
                      </td>
                    </tr>
                    <tr v-if="users.length === 0">
                      <td colspan="9" class="empty">{{ i18n.t('admin.no_users_found') }}</td>
                    </tr>
                    <tr v-else-if="filteredUsers.length === 0">
                      <td colspan="9" class="empty">{{ i18n.t('admin.no_users_match') }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </section>
          </div>
        </section>
      </section>
    </main>

    <Teleport to="body">
      <div
        v-if="selectedUserMenuId !== null && selectedUserMenuUser"
        class="user-menu user-menu-panel user-menu-open"
        :style="{
          top: `${selectedUserMenuPosition.top}px`,
          left: `${selectedUserMenuPosition.left}px`,
        }"
        @click.stop
      >
        <button
          type="button"
          class="user-menu-action"
          @click="onRenameUserClick(selectedUserMenuUser)"
        >
          {{ i18n.t('admin.rename_user') }}
        </button>

        <button
          type="button"
          class="user-menu-action"
          @click="onToggleUserStatusClick(selectedUserMenuUser)"
        >
          {{ selectedUserMenuUser.account_status === 'inactive' ? i18n.t('admin.unblock_user') : i18n.t('admin.block_user') }}
        </button>

        <button
          type="button"
          class="user-menu-action"
          @click="onDeleteUserClick(selectedUserMenuUser)"
        >
          {{ i18n.t('admin.delete_user') }}
        </button>
      </div>
    </Teleport>

    <footer class="footer">
      <div class="footer-container">
        <span class="footer-text">{{ i18n.t('footer.brand') }}</span>
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

.users-panel {
  min-height: calc(100vh - 310px);
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
  position: fixed;
  min-width: 200px;
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 0.35rem 0;
  z-index: 100;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.2s ease, visibility 0.2s ease;
}

.user-menu-open {
  opacity: 1;
  visibility: visible;
}

.user-menu-action {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  border: none;
  background: transparent;
  color: var(--text-primary);
  text-align: left;
  padding: 0.45rem 1rem;
  font-size: 0.85rem;
  min-height: 34px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.user-menu-action:hover {
  background: rgba(128, 128, 128, 0.14);
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 60;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
  background: transparent;
  backdrop-filter: none;
}

.modal-card {
  width: min(100%, 360px);
  border-radius: 12px;
  padding: 1.25rem;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  box-shadow: none;
}

.action-modal-card {
  /* Match RegisterPage modal shape and spacing */
  width: 100%;
  max-width: 430px;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 18px;
  padding: 1.5rem;
  box-shadow: none;
  --accent-color: rgb(124, 58, 237);
  --accent-hover: rgb(109, 40, 217);
}

.admin-page.dark .action-modal-card {
  --accent-color: #a78bfa;
  --accent-hover: #8b5cf6;
}

.modal-card h2 {
  font-size: 1.5rem;
  font-weight: 600;
  line-height: 1.1;
  letter-spacing: -0.5px;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.modal-subtitle {
  margin-bottom: 0.9rem;
  color: var(--text-secondary);
  font-size: 0.875rem;
  line-height: 1.45;
}

.modal-banner {
  margin-bottom: 0.75rem;
}

.action-modal-card .form-group {
  margin-bottom: 0.75rem;
}

.action-modal-card .form-group label {
  display: block;
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--text-secondary);
  margin-bottom: 0.375rem;
}

.action-modal-card .form-group input {
  width: 100%;
  padding: 0.5rem 0.875rem;
  font-size: 0.9375rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--bg-primary);
  color: var(--text-primary);
  outline: none;
  transition: border-color 0.2s ease;
  font-family: inherit;
  box-sizing: border-box;
}

.action-modal-card .form-group input:focus {
  border-color: var(--accent-color);
}

.modal-actions {
  display: flex;
  flex-direction: row;
  gap: 0.5rem;
  margin-top: 0.9rem;
}

.modal-actions .secondary-btn {
  border: 1px solid var(--border-color);
  background: var(--bg-secondary);
  color: var(--text-primary);
  border-radius: 10px;
  padding: 0.45rem 0.75rem;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  transition: background-color 0.14s ease, color 0.14s ease;
  font-family: inherit;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 36px;
  flex: 1;
}

.modal-actions .secondary-btn:hover {
  background: color-mix(in srgb, var(--bg-secondary) 86%, var(--accent-color));
  border-color: color-mix(in srgb, var(--border-color) 70%, var(--accent-color));
}

.modal-actions .action-btn {
  width: auto;
  flex: 1;
  padding: 0.45rem 0.9rem;
  font-size: 0.95rem;
  font-weight: 600;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: background-color 0.14s ease, color 0.14s ease;
  font-family: inherit;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 36px;
  margin-top: 0;
  box-shadow: none;
}

.modal-actions .action-btn.primary {
  background: rgb(124, 58, 237);
  border-color: rgb(124, 58, 237);
  color: #ffffff;
}

.modal-actions .action-btn.primary:hover:not(:disabled) {
  background: rgb(109, 40, 217);
  border-color: rgb(109, 40, 217);
  color: #ffffff;
}

.action-btn.danger {
  background: #dc3545;
  color: #ffffff;
}

.action-btn.danger:hover:not(:disabled) {
  background: #c82333;
  color: #ffffff;
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

.modal-actions .action-btn.primary {
  background: rgb(124, 58, 237);
  border-color: rgb(124, 58, 237);
  color: #ffffff;
}

.modal-actions .action-btn.primary:hover:not(:disabled) {
  background: rgb(109, 40, 217);
  border-color: rgb(109, 40, 217);
  color: #ffffff;
}

.modal-actions .secondary-btn {
  background: var(--bg-primary);
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
  max-height: 520px;
  overflow: auto;
  padding-right: 0.25rem;
}

.users-layout {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  gap: 1rem;
  align-items: start;
}

.users-panel {
  min-height: calc(100vh - 310px);
}

.users-filters-panel {
  position: sticky;
  top: 1.25rem;
  padding: 1rem;
}

.users-results-panel {
  min-width: 0;
}

.users-summary {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
  color: var(--text-secondary);
  font-size: 0.75rem;
}

.filter-group {
  padding: 0.85rem 0;
  border-top: 1px solid var(--border-color);
}

.filter-group:first-of-type {
  border-top: none;
  padding-top: 0;
}

.filter-group h3 {
  margin-bottom: 0.75rem;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--text-secondary);
}

.field-label {
  display: block;
  margin: 0.55rem 0 0.35rem;
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-secondary);
}

.filter-input,
.filter-select {
  width: 100%;
  padding: 0.5rem 0.7rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--bg-primary);
  color: var(--text-primary);
  font: inherit;
  font-size: 0.875rem;
  outline: none;
  transition: border-color 0.2s ease, background-color 0.2s ease;
}

.filter-input:focus,
.filter-select:focus {
  border-color: var(--accent-color);
}

.filter-select {
  appearance: none;
}

.reset-button-panel {
  width: 100%;
  margin-top: 0.85rem;
  border: 1px solid var(--border-color);
  background: var(--bg-secondary);
  color: var(--text-primary);
  border-radius: 8px;
  padding: 0.6rem 0.75rem;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.reset-button-panel:hover:not(:disabled) {
  background: color-mix(in srgb, var(--bg-secondary) 82%, var(--accent-color));
  border-color: color-mix(in srgb, var(--border-color) 70%, var(--accent-color));
}

.reset-button-panel:disabled {
  cursor: not-allowed;
  opacity: 0.7;
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

  .users-layout {
    grid-template-columns: 1fr;
  }

  .users-filters-panel {
    position: static;
  }
}
</style>
