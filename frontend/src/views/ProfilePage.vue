<script setup>
import { computed, onMounted, provide, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { useAuthStore } from '../stores/auth'
import { useThemePreference } from '../composables/useThemePreference'

const router = useRouter()
const authStore = useAuthStore()

const selectedCurrency = ref('EUR')
const selectedLanguage = ref('ENG')
const selectedTheme = useThemePreference()
const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')

const profileData = ref({
  nickname: 'User',
  email: '',
  passwordPreview: '',
})

const activeEditField = ref('')
const isSaving = ref(false)
const fieldError = ref('')
const statusType = ref('')
const statusMessage = ref('')

const isEmailVisible = ref(false)
const isPasswordVisible = ref(false)
const isPasswordRevealPromptOpen = ref(false)
const passwordRevealInput = ref('')
const passwordRevealError = ref('')

const showPasswordRevealInput = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const editDraft = ref({
  nickname: '',
  email: '',
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

provide('theme', selectedTheme)

const displayedEmail = computed(() => {
  if (!profileData.value.email) {
    return 'Email not set'
  }

  return isEmailVisible.value ? profileData.value.email : maskEmail(profileData.value.email)
})

const displayedPassword = computed(() => {
  if (isPasswordVisible.value) {
    return profileData.value.passwordPreview || 'No frontend password preview yet'
  }

  return maskPassword(profileData.value.passwordPreview)
})

function syncProfileDataFromStore() {
  profileData.value = {
    ...profileData.value,
    nickname: authStore.user?.nickname ?? 'User',
    email: authStore.user?.email ?? '',
  }
}

function setStatus(type, message) {
  statusType.value = type
  statusMessage.value = message
}

function clearStatus() {
  statusType.value = ''
  statusMessage.value = ''
}

function clearRowState() {
  fieldError.value = ''
  passwordRevealError.value = ''
  passwordRevealInput.value = ''
  showPasswordRevealInput.value = false
  showCurrentPassword.value = false
  showNewPassword.value = false
  showConfirmPassword.value = false
}

function maskSegment(value, prefixLength, suffixLength = 0) {
  if (!value) {
    return ''
  }

  const safePrefixLength = Math.max(prefixLength, 1)
  const safeSuffixLength = Math.max(suffixLength, 0)

  if (value.length <= safePrefixLength + safeSuffixLength) {
    return `${value.slice(0, 1)}${'*'.repeat(Math.max(value.length - 1, 1))}`
  }

  const visiblePrefix = value.slice(0, safePrefixLength)
  const visibleSuffix = safeSuffixLength > 0 ? value.slice(-safeSuffixLength) : ''
  const hiddenLength = Math.max(value.length - safePrefixLength - safeSuffixLength, 2)

  return `${visiblePrefix}${'*'.repeat(hiddenLength)}${visibleSuffix}`
}

function maskEmail(value) {
  if (!value || !value.includes('@')) {
    return 'Email not set'
  }

  const [localPart, domainPart] = value.split('@')
  const domainSegments = domainPart.split('.')
  const domainName = domainSegments.shift() || ''
  const domainSuffix = domainSegments.length ? `.${domainSegments.join('.')}` : ''

  return `${maskSegment(localPart, 2)}@${maskSegment(domainName, 1)}${domainSuffix}`
}

function maskPassword(value) {
  const hiddenLength = Math.max(value?.length || 0, 10)
  return '*'.repeat(hiddenLength)
}

function isValidEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
}

function startEdit(fieldName) {
  if (activeEditField.value === fieldName) {
    cancelEdit()
    return
  }

  activeEditField.value = fieldName
  clearRowState()
  clearStatus()
  isPasswordRevealPromptOpen.value = false

  if (fieldName === 'nickname') {
    editDraft.value.nickname = profileData.value.nickname
  }

  if (fieldName === 'email') {
    editDraft.value.email = profileData.value.email
  }

  if (fieldName === 'password') {
    editDraft.value.currentPassword = ''
    editDraft.value.newPassword = ''
    editDraft.value.confirmPassword = ''
  }
}

function cancelEdit() {
  activeEditField.value = ''
  clearRowState()
}

function toggleEmailVisibility() {
  clearStatus()
  isEmailVisible.value = !isEmailVisible.value
}

function togglePasswordReveal() {
  clearStatus()

  if (activeEditField.value === 'password') {
    return
  }

  if (isPasswordVisible.value) {
    isPasswordVisible.value = false
    isPasswordRevealPromptOpen.value = false
    clearRowState()
    return
  }

  isPasswordRevealPromptOpen.value = !isPasswordRevealPromptOpen.value
  passwordRevealError.value = ''
}

function unlockPasswordVisibility() {
  if (!passwordRevealInput.value.trim()) {
    passwordRevealError.value = 'Enter your current account password.'
    return
  }

  isPasswordVisible.value = true
  isPasswordRevealPromptOpen.value = false
  passwordRevealError.value = ''
  passwordRevealInput.value = ''
  showPasswordRevealInput.value = false
}

async function saveNickname() {
  const nextNickname = editDraft.value.nickname.trim()

  if (!nextNickname) {
    fieldError.value = 'Nickname cannot be empty.'
    return
  }

  if (nextNickname.length > 100) {
    fieldError.value = 'Nickname is too long.'
    return
  }

  isSaving.value = true
  fieldError.value = ''

  try {
    const response = await fetch(apiBaseUrl ? `${apiBaseUrl}/profile/nickname` : '/api/profile/nickname', {
      method: 'PATCH',
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        nickname: nextNickname,
      }),
    })

    const data = await response.json().catch(() => null)

    if (response.status === 401) {
      authStore.clearUser()
      await router.push('/login')
      return
    }

    if (!response.ok) {
      throw new Error(data?.errors?.nickname?.[0] || data?.message || 'Unable to update nickname.')
    }

    profileData.value.nickname = data.user.nickname
    authStore.setUser({
      ...(authStore.user ?? {}),
      ...data.user,
    })

    cancelEdit()
    setStatus('success', 'Nickname updated successfully.')
  } catch (err) {
    fieldError.value = err instanceof Error ? err.message : 'Unable to update nickname.'
  } finally {
    isSaving.value = false
  }
}

function saveEmail() {
  const nextEmail = editDraft.value.email.trim().toLowerCase()

  if (!nextEmail) {
    fieldError.value = 'Email cannot be empty.'
    return
  }

  if (!isValidEmail(nextEmail)) {
    fieldError.value = 'Enter a valid email address.'
    return
  }

  profileData.value.email = nextEmail
  authStore.setUser({
    ...(authStore.user ?? {}),
    email: nextEmail,
  })

  isEmailVisible.value = false
  cancelEdit()
  setStatus('info', 'Email updated on the frontend only.')
}

function savePassword() {
  const currentPassword = editDraft.value.currentPassword.trim()
  const newPassword = editDraft.value.newPassword
  const confirmPassword = editDraft.value.confirmPassword

  if (!currentPassword) {
    fieldError.value = 'Enter your current account password.'
    return
  }

  if (newPassword.length < 8) {
    fieldError.value = 'New password must be at least 8 characters.'
    return
  }

  if (newPassword !== confirmPassword) {
    fieldError.value = 'Passwords do not match.'
    return
  }

  profileData.value.passwordPreview = newPassword
  isPasswordVisible.value = false
  isPasswordRevealPromptOpen.value = false

  cancelEdit()
  setStatus('info', 'Password updated on the frontend only.')
}

function saveField(fieldName) {
  if (fieldName === 'nickname') {
    return saveNickname()
  }

  if (fieldName === 'email') {
    return saveEmail()
  }

  if (fieldName === 'password') {
    return savePassword()
  }

  return undefined
}

watch(
  () => authStore.user,
  () => {
    syncProfileDataFromStore()
  },
  { deep: true },
)

onMounted(() => {
  if (!authStore.isLoggedIn) {
    router.push('/login')
    return
  }

  syncProfileDataFromStore()
})
</script>

<template>
  <div class="profile-page" :class="selectedTheme">
    <AppHeader
      v-model:selectedTheme="selectedTheme"
      v-model:selectedCurrency="selectedCurrency"
      v-model:selectedLanguage="selectedLanguage"
      activePage="profile"
    />

    <main class="main-content">
      <div class="content-wrapper">
        <header class="page-header">
          <h1>Account</h1>
          <p class="subtitle">
            Manage nickname, email and password in a simple layout.
          </p>
        </header>

        <section class="settings-card">
          <aside class="profile-sidebar">
            <div class="avatar-shell">
              <div class="avatar-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
              </div>
            </div>
          </aside>

          <div class="settings-panel">
            <div v-if="statusMessage" class="status-banner" :class="statusType">
              {{ statusMessage }}
            </div>

            <div class="settings-list">
              <div class="setting-row" :class="{ editing: activeEditField === 'nickname' }">
                <div class="setting-main">
                  <div class="setting-copy">
                    <p class="setting-label">Nickname</p>
                    <p class="setting-hint">Saved to your account</p>
                  </div>

                  <Transition name="setting-swap" mode="out-in">
                    <div v-if="activeEditField !== 'nickname'" key="nickname-display" class="setting-display">
                      <p class="setting-value">{{ profileData.nickname }}</p>
                    </div>

                    <div v-else key="nickname-edit" class="setting-edit">
                      <input
                        v-model="editDraft.nickname"
                        type="text"
                        class="setting-input"
                        placeholder="Enter a new nickname"
                        autocomplete="nickname"
                        @keyup.enter="saveField('nickname')"
                        @keyup.esc="cancelEdit"
                      />

                      <div class="edit-actions">
                        <button type="button" class="action-btn save-btn" :disabled="isSaving" @click="saveField('nickname')">
                          Save
                        </button>
                      </div>

                      <p v-if="fieldError" class="field-error">{{ fieldError }}</p>
                    </div>
                  </Transition>
                </div>

                <div class="setting-actions">
                  <button type="button" class="icon-btn" aria-label="Edit nickname" @click="startEdit('nickname')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 20h9"/>
                      <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>
                    </svg>
                  </button>
                </div>
              </div>

              <div class="setting-row" :class="{ editing: activeEditField === 'email' }">
                <div class="setting-main">
                  <div class="setting-copy">
                    <p class="setting-label">Email</p>
                    <p class="setting-hint">Masked by default, frontend only</p>
                  </div>

                  <Transition name="setting-swap" mode="out-in">
                    <div v-if="activeEditField !== 'email'" key="email-display" class="setting-display">
                      <p class="setting-value sensitive">{{ displayedEmail }}</p>
                    </div>

                    <div v-else key="email-edit" class="setting-edit">
                      <input
                        v-model="editDraft.email"
                        type="email"
                        class="setting-input"
                        placeholder="your@email.com"
                        autocomplete="email"
                        @keyup.enter="saveField('email')"
                        @keyup.esc="cancelEdit"
                      />

                      <div class="edit-actions">
                        <button type="button" class="action-btn save-btn" @click="saveField('email')">
                          Save
                        </button>
                      </div>

                      <p v-if="fieldError" class="field-error">{{ fieldError }}</p>
                    </div>
                  </Transition>
                </div>

                <div class="setting-actions">
                  <button
                    type="button"
                    class="icon-btn"
                    :aria-label="isEmailVisible ? 'Hide email' : 'Show email'"
                    :aria-pressed="isEmailVisible"
                    @click="toggleEmailVisibility"
                  >
                    <svg v-if="isEmailVisible" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/>
                      <circle cx="12" cy="12" r="2.75"/>
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M3 3l18 18"/>
                      <path d="M10.6 6.2A10.7 10.7 0 0 1 12 6c6.4 0 10 6 10 6a17.6 17.6 0 0 1-4.1 4.7"/>
                      <path d="M6.7 6.7C4.2 8.3 2.6 11 2 12c0 0 3.6 6 10 6 1.6 0 3-.4 4.2-1"/>
                      <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                    </svg>
                  </button>

                  <button type="button" class="icon-btn" aria-label="Edit email" @click="startEdit('email')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 20h9"/>
                      <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>
                    </svg>
                  </button>
                </div>
              </div>

              <div class="setting-row" :class="{ editing: activeEditField === 'password' }">
                <div class="setting-main">
                  <div class="setting-copy">
                    <p class="setting-label">Password</p>
                    <p class="setting-hint">Unlock to reveal, edit stays on the frontend</p>
                  </div>

                  <Transition name="setting-swap" mode="out-in">
                    <div v-if="activeEditField !== 'password'" key="password-display" class="setting-display">
                      <p class="setting-value sensitive">{{ displayedPassword }}</p>

                      <Transition name="inline-drop">
                        <div v-if="isPasswordRevealPromptOpen" class="unlock-panel">
                          <label for="password-reveal" class="unlock-label">Current account password</label>
                          <div class="unlock-row">
                            <div class="password-input-wrapper compact">
                              <input
                                id="password-reveal"
                                v-model="passwordRevealInput"
                                :type="showPasswordRevealInput ? 'text' : 'password'"
                                class="setting-input"
                                placeholder="Enter your current password"
                                autocomplete="current-password"
                                @keyup.enter="unlockPasswordVisibility"
                              />

                              <button
                                type="button"
                                class="password-toggle"
                                :aria-label="showPasswordRevealInput ? 'Hide password' : 'Show password'"
                                :aria-pressed="showPasswordRevealInput"
                                @click="showPasswordRevealInput = !showPasswordRevealInput"
                              >
                                <svg v-if="showPasswordRevealInput" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                  <path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/>
                                  <circle cx="12" cy="12" r="2.75"/>
                                </svg>
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                  <path d="M3 3l18 18"/>
                                  <path d="M10.6 6.2A10.7 10.7 0 0 1 12 6c6.4 0 10 6 10 6a17.6 17.6 0 0 1-4.1 4.7"/>
                                  <path d="M6.7 6.7C4.2 8.3 2.6 11 2 12c0 0 3.6 6 10 6 1.6 0 3-.4 4.2-1"/>
                                  <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                                </svg>
                              </button>
                            </div>

                            <button type="button" class="action-btn mini-btn" @click="unlockPasswordVisibility">
                              Unlock
                            </button>
                          </div>

                          <p v-if="passwordRevealError" class="field-error">{{ passwordRevealError }}</p>
                        </div>
                      </Transition>
                    </div>

                    <div v-else key="password-edit" class="setting-edit password-edit">
                      <div class="field-stack">
                        <label class="input-label" for="current-password">Current account password</label>
                        <div class="password-input-wrapper">
                          <input
                            id="current-password"
                            v-model="editDraft.currentPassword"
                            :type="showCurrentPassword ? 'text' : 'password'"
                            class="setting-input"
                            placeholder="Current password"
                            autocomplete="current-password"
                          />

                          <button
                            type="button"
                            class="password-toggle"
                            :aria-label="showCurrentPassword ? 'Hide password' : 'Show password'"
                            :aria-pressed="showCurrentPassword"
                            @click="showCurrentPassword = !showCurrentPassword"
                          >
                            <svg v-if="showCurrentPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/>
                              <circle cx="12" cy="12" r="2.75"/>
                            </svg>
                            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M3 3l18 18"/>
                              <path d="M10.6 6.2A10.7 10.7 0 0 1 12 6c6.4 0 10 6 10 6a17.6 17.6 0 0 1-4.1 4.7"/>
                              <path d="M6.7 6.7C4.2 8.3 2.6 11 2 12c0 0 3.6 6 10 6 1.6 0 3-.4 4.2-1"/>
                              <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                            </svg>
                          </button>
                        </div>
                      </div>

                      <div class="field-stack">
                        <label class="input-label" for="new-password">New password</label>
                        <div class="password-input-wrapper">
                          <input
                            id="new-password"
                            v-model="editDraft.newPassword"
                            :type="showNewPassword ? 'text' : 'password'"
                            class="setting-input"
                            placeholder="At least 8 characters"
                            autocomplete="new-password"
                          />

                          <button
                            type="button"
                            class="password-toggle"
                            :aria-label="showNewPassword ? 'Hide password' : 'Show password'"
                            :aria-pressed="showNewPassword"
                            @click="showNewPassword = !showNewPassword"
                          >
                            <svg v-if="showNewPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/>
                              <circle cx="12" cy="12" r="2.75"/>
                            </svg>
                            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M3 3l18 18"/>
                              <path d="M10.6 6.2A10.7 10.7 0 0 1 12 6c6.4 0 10 6 10 6a17.6 17.6 0 0 1-4.1 4.7"/>
                              <path d="M6.7 6.7C4.2 8.3 2.6 11 2 12c0 0 3.6 6 10 6 1.6 0 3-.4 4.2-1"/>
                              <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                            </svg>
                          </button>
                        </div>
                      </div>

                      <div class="field-stack">
                        <label class="input-label" for="confirm-password">Confirm new password</label>
                        <div class="password-input-wrapper">
                          <input
                            id="confirm-password"
                            v-model="editDraft.confirmPassword"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="setting-input"
                            placeholder="Repeat your new password"
                            autocomplete="new-password"
                            @keyup.enter="saveField('password')"
                            @keyup.esc="cancelEdit"
                          />

                          <button
                            type="button"
                            class="password-toggle"
                            :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                            :aria-pressed="showConfirmPassword"
                            @click="showConfirmPassword = !showConfirmPassword"
                          >
                            <svg v-if="showConfirmPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/>
                              <circle cx="12" cy="12" r="2.75"/>
                            </svg>
                            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M3 3l18 18"/>
                              <path d="M10.6 6.2A10.7 10.7 0 0 1 12 6c6.4 0 10 6 10 6a17.6 17.6 0 0 1-4.1 4.7"/>
                              <path d="M6.7 6.7C4.2 8.3 2.6 11 2 12c0 0 3.6 6 10 6 1.6 0 3-.4 4.2-1"/>
                              <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                            </svg>
                          </button>
                        </div>
                      </div>

                      <div class="edit-actions">
                        <button type="button" class="action-btn save-btn" @click="saveField('password')">
                          Save
                        </button>
                      </div>

                      <p v-if="fieldError" class="field-error">{{ fieldError }}</p>
                    </div>
                  </Transition>
                </div>

                <div class="setting-actions">
                  <button
                    type="button"
                    class="icon-btn"
                    :aria-label="isPasswordVisible ? 'Hide password' : 'Reveal password'"
                    :aria-pressed="isPasswordVisible"
                    @click="togglePasswordReveal"
                  >
                    <svg v-if="isPasswordVisible" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/>
                      <circle cx="12" cy="12" r="2.75"/>
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M3 3l18 18"/>
                      <path d="M10.6 6.2A10.7 10.7 0 0 1 12 6c6.4 0 10 6 10 6a17.6 17.6 0 0 1-4.1 4.7"/>
                      <path d="M6.7 6.7C4.2 8.3 2.6 11 2 12c0 0 3.6 6 10 6 1.6 0 3-.4 4.2-1"/>
                      <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                    </svg>
                  </button>

                  <button type="button" class="icon-btn" aria-label="Edit password" @click="startEdit('password')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 20h9"/>
                      <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>
                    </svg>
                  </button>
                </div>
              </div>
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
.profile-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  transition: background-color 0.3s ease, color 0.3s ease;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
}

.profile-page.light {
  --bg-primary: #ffffff;
  --bg-secondary: #f5f5f7;
  --surface-muted: #f5f5f7;
  --text-primary: #1d1d1f;
  --text-secondary: #86868b;
  --border-color: #d2d2d7;
  --hover-bg: #efeff2;
  --accent-color: #0071e3;
  --accent-hover: #0077ed;
  --accent-soft: rgba(0, 113, 227, 0.1);
  --input-bg: #ffffff;
  --status-info-bg: rgba(0, 113, 227, 0.08);
  --status-info-text: #005bb5;
  --status-success-bg: rgba(22, 163, 74, 0.1);
  --status-success-text: #18753a;
  --error-color: #c53636;
}

.profile-page.dark {
  --bg-primary: #1b1d21;
  --bg-secondary: #25282e;
  --surface-muted: #25282e;
  --text-primary: #f5f5f7;
  --text-secondary: #a6aab3;
  --border-color: #545a65;
  --hover-bg: #2f333b;
  --accent-color: #2997ff;
  --accent-hover: #40a9ff;
  --accent-soft: rgba(41, 151, 255, 0.14);
  --input-bg: #2a2f37;
  --status-info-bg: rgba(41, 151, 255, 0.14);
  --status-info-text: #b8ddff;
  --status-success-bg: rgba(34, 197, 94, 0.14);
  --status-success-text: #9ae6b4;
  --error-color: #ff8b8b;
}

.profile-page {
  background: var(--bg-primary);
  color: var(--text-primary);
}

.main-content {
  flex: 1;
  padding: 3rem 2rem;
}

.content-wrapper {
  max-width: 920px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 1.5rem;
}

.page-header h1 {
  margin: 0;
  font-size: 2.5rem;
  font-weight: 600;
  line-height: 1.1;
  letter-spacing: -1px;
  color: var(--text-primary);
}

.subtitle {
  max-width: 520px;
  margin: 0.6rem 0 0;
  font-size: 0.95rem;
  line-height: 1.5;
  color: var(--text-secondary);
}

.settings-card {
  display: grid;
  grid-template-columns: 220px minmax(0, 1fr);
  gap: 1.5rem;
  align-items: start;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1.5rem;
}

.profile-sidebar {
  display: flex;
  align-items: center;
  justify-content: center;
  align-self: stretch;
  border-right: 1px solid var(--border-color);
  padding: 0.75rem;
}

.avatar-shell {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.avatar-placeholder {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
}

.avatar-placeholder svg {
  width: 34px;
  height: 34px;
}

.settings-panel {
  padding: 0;
}

.status-banner {
  margin-bottom: 0.75rem;
  padding: 0.65rem 0.8rem;
  border-radius: 8px;
  font-size: 0.875rem;
}

.status-banner.info {
  background: var(--status-info-bg);
  color: var(--status-info-text);
}

.status-banner.success {
  background: var(--status-success-bg);
  color: var(--status-success-text);
}

.settings-list {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.setting-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid var(--border-color);
  background: transparent;
  transition: background-color 0.2s ease;
}

.setting-row:last-child {
  border-bottom: none;
}

.setting-row.editing {
  background: transparent;
}

.setting-main {
  min-width: 0;
}

.setting-copy {
  margin-bottom: 0.45rem;
}

.setting-label {
  margin: 0;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--text-secondary);
}

.setting-row.editing .setting-label {
  color: var(--accent-color);
}

.setting-hint {
  margin: 0.2rem 0 0;
  font-size: 0.82rem;
  color: var(--text-secondary);
}

.setting-display {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.setting-value {
  margin: 0;
  font-size: 1rem;
  line-height: 1.5;
  color: var(--text-primary);
  word-break: break-word;
}

.setting-value.sensitive {
  letter-spacing: 0.01em;
}

.setting-edit {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: transparent;
}

.password-edit {
  gap: 0.9rem;
}

.field-stack {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.input-label,
.unlock-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.setting-input {
  width: 100%;
  padding: 0.65rem 0.85rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--input-bg);
  color: var(--text-primary);
  font-size: 0.9375rem;
  font-family: inherit;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.setting-input::placeholder {
  color: var(--text-secondary);
  opacity: 0.75;
}

.password-input-wrapper {
  position: relative;
}

.password-input-wrapper .setting-input {
  padding-right: 3rem;
}

.password-input-wrapper.compact {
  flex: 1;
}

.password-toggle {
  position: absolute;
  top: 50%;
  right: 0.75rem;
  transform: translateY(-50%);
  width: 28px;
  height: 28px;
  border: none;
  padding: 0;
  border-radius: 8px;
  background: transparent;
  color: var(--text-secondary);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: color 0.2s ease, background-color 0.2s ease;
}

.password-toggle:hover,
.icon-btn:hover {
  color: var(--text-primary);
  background: var(--accent-soft);
}

.password-toggle:focus-visible,
.icon-btn:focus-visible,
.action-btn:focus-visible {
  outline: 2px solid var(--accent-color);
  outline-offset: 2px;
}

.password-toggle svg,
.icon-btn svg {
  width: 17px;
  height: 17px;
}

.unlock-panel {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: transparent;
}

.unlock-row {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-top: 0.45rem;
}

.edit-actions {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  flex-wrap: wrap;
}

.action-btn {
  border: none;
  border-radius: 8px;
  padding: 0.5rem 0.8rem;
  font-size: 0.8125rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: background-color 0.2s ease, color 0.2s ease;
}

.save-btn,
.mini-btn {
  background: var(--accent-color);
  color: #ffffff;
}

.save-btn:hover,
.mini-btn:hover {
  background: var(--accent-hover);
}

.action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.setting-actions {
  display: flex;
  align-items: flex-start;
  /* align-items: center; */
  /* align-self: center; */
  gap: 0.55rem;
}

.icon-btn {
  width: 30px;
  height: 30px;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: var(--text-secondary);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
}

.icon-btn:hover {
  color: var(--accent-color);
}

.icon-btn:active {
  transform: scale(0.96);
}

.field-error {
  margin: 0;
  font-size: 0.84rem;
  color: var(--error-color);
}

.setting-swap-enter-active,
.setting-swap-leave-active,
.inline-drop-enter-active,
.inline-drop-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}

.setting-swap-enter-from,
.setting-swap-leave-to,
.inline-drop-enter-from,
.inline-drop-leave-to {
  opacity: 0;
  transform: translateY(4px);
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

@media (max-width: 880px) {
  .settings-card {
    grid-template-columns: 1fr;
    padding: 1.25rem;
  }

  .profile-sidebar {
    border-right: none;
    border-bottom: 1px solid var(--border-color);
    padding: 1rem 0;
  }
}

@media (max-width: 640px) {
  .main-content {
    padding: 2rem 1rem 3rem;
  }

  .page-header h1 {
    font-size: 2.25rem;
  }

  .settings-panel,
  .profile-sidebar {
    border-radius: 0;
  }

  .setting-row {
    grid-template-columns: 1fr;
  }

  .setting-actions {
    justify-content: flex-end;
  }

  .unlock-row {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
