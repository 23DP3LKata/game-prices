<script setup>
import { ref, provide, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { useAuthStore } from '../stores/auth'
import { useThemePreference } from '../composables/useThemePreference'
import { useI18nStore } from '../stores/i18n'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const selectedLanguage = ref('LV')
const selectedTheme = useThemePreference()
const i18n = useI18nStore()

provide('theme', selectedTheme)

const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const rememberMe = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const forgotOpen = ref(false)
const forgotEmail = ref('')
const forgotLoading = ref(false)
const forgotError = ref('')
const forgotSuccess = ref('')

const verifyStatus = computed(() => String(route.query.verify || ''))
const verifyMessage = computed(() => {
  if (verifyStatus.value === 'sent') {
    return i18n.t('login.verify.sent')
  }

  if (verifyStatus.value === 'verified') {
    return i18n.t('login.verify.verified')
  }

  if (verifyStatus.value === 'already-verified') {
    return i18n.t('login.verify.already_verified')
  }

  return ''
})

const verifyError = computed(() => {
  if (verifyStatus.value === 'invalid') {
    return i18n.t('login.verify.invalid')
  }

  return ''
})

function validateEmail(value) {
  const trimmedValue = value.trim()

  if (!trimmedValue) {
    return ''
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmedValue)) {
    return i18n.t('messages.valid_email')
  }

  return ''
}

function validatePassword(value) {
  if (!value) {
    return ''
  }

  return ''
}

function getApiUrl(path) {
  return apiBaseUrl ? `${apiBaseUrl}${path}` : `/api${path}`
}

async function handleLogin() {
  errorMessage.value = ''

  if (!email.value.trim() || !password.value) {
    return
  }

  isLoading.value = true

  try {
    const response = await fetch(getApiUrl('/login'), {
      method: 'POST',
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: email.value.trim(),
        password: password.value,
        remember: rememberMe.value,
      }),
    })

    const data = await response.json().catch(() => null)

    if (!response.ok) {
      if (response.status === 401 || response.status === 422) {
        errorMessage.value = i18n.t('login.errors.invalid_credentials')
      } else {
        errorMessage.value = data?.message || i18n.t('login.errors.failed')
      }
      return
    }

    authStore.setUser(data.user)
    await router.push('/profile')
  } catch (err) {
    errorMessage.value = i18n.t('messages.something_went_wrong')
  } finally {
    isLoading.value = false
  }
}

function openForgotPassword() {
  forgotOpen.value = true
  forgotEmail.value = email.value.trim()
  forgotError.value = ''
  forgotSuccess.value = ''
}

function closeForgotPassword() {
  forgotOpen.value = false
  forgotLoading.value = false
  forgotError.value = ''
  forgotSuccess.value = ''
}

async function sendForgotPassword() {
  forgotError.value = ''
  forgotSuccess.value = ''

  if (!forgotEmail.value.trim()) {
    forgotError.value = i18n.t('login.forgot.errors.email_required')
    return
  }

  forgotLoading.value = true

  try {
    const response = await fetch(getApiUrl('/forgot-password'), {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: forgotEmail.value.trim(),
      }),
    })

    const data = await response.json().catch(() => null)

    if (!response.ok) {
      throw new Error(data?.message || i18n.t('login.forgot.errors.failed'))
    }

    forgotSuccess.value = data?.message || i18n.t('login.forgot.success')
  } catch (err) {
    forgotError.value = err instanceof Error
      ? err.message
      : i18n.t('messages.something_went_wrong')
  } finally {
    forgotLoading.value = false
  }
}
</script>

<template>
  <div class="login-page" :class="selectedTheme">
    <AppHeader
      v-model:selectedTheme="selectedTheme"
      v-model:selectedLanguage="selectedLanguage"
      activePage=""
    />

    <main class="main-content">
      <div class="content-wrapper">
        <section class="form-panel">
          <form class="login-card" @submit.prevent="handleLogin">
            <header class="page-header compact-header">
              <h2>{{ i18n.t('login.title') }}</h2>
              <p class="subtitle">{{ i18n.t('login.subtitle') }}</p>
            </header>

            <div v-if="verifyMessage" class="info-banner" role="status">
              {{ verifyMessage }}
            </div>

            <div v-if="verifyError" class="error-banner" role="alert">
              <span>{{ verifyError }}</span>
            </div>

            <div v-if="errorMessage" class="error-banner">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              <span>{{ errorMessage }}</span>
            </div>

            <div class="form-group">
              <label for="email">{{ i18n.t('login.email') }}</label>
              <input
                id="email"
                v-model="email"
                type="email"
                autocomplete="email"
              />
            </div>

            <div class="form-group">
              <label for="password">{{ i18n.t('login.password') }}</label>
              <div class="password-input-wrapper">
                <input
                  id="password"
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="current-password"
                />
                <button
                  type="button"
                  class="password-toggle"
                  :aria-label="showPassword ? i18n.t('password.hide') : i18n.t('password.show')"
                  :aria-pressed="showPassword"
                  @click="showPassword = !showPassword"
                >
                  <svg v-if="showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
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

            <div class="form-options">
              <label class="remember-label">
                <input v-model="rememberMe" type="checkbox" />
                <span class="checkmark"></span>
                <span>{{ i18n.t('login.remember') }}</span>
              </label>
              <button type="button" class="forgot-link" @click="openForgotPassword">
                {{ i18n.t('login.forgot') }}
              </button>
            </div>

            <button type="submit" class="login-btn" :disabled="isLoading || !email.trim() || !password">
              <span v-if="!isLoading">{{ i18n.t('login.button') }}</span>
              <span v-else class="spinner"></span>
            </button>

            <p class="register-link">
              {{ i18n.t('login.no_account') }}
              <router-link to="/register">{{ i18n.t('sign_up') }}</router-link>
            </p>
          </form>
        </section>
      </div>
    </main>

    <div v-if="forgotOpen" class="modal-backdrop" @click.self="closeForgotPassword">
      <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="forgot-title">
        <h2 id="forgot-title">{{ i18n.t('login.forgot.title') }}</h2>
        <p class="modal-subtitle">{{ i18n.t('login.forgot.subtitle') }}</p>

        <div v-if="forgotError" class="error-banner modal-banner">
          <span>{{ forgotError }}</span>
        </div>

        <div v-if="forgotSuccess" class="info-banner modal-banner" role="status">
          {{ forgotSuccess }}
        </div>

        <div class="form-group">
          <label for="forgot-email">{{ i18n.t('login.email') }}</label>
          <input
            id="forgot-email"
            v-model="forgotEmail"
            type="email"
            autocomplete="email"
          />
        </div>

        <div class="modal-actions">
          <button type="button" class="secondary-btn" @click="closeForgotPassword">{{ i18n.t('buttons.close') }}</button>
          <button type="button" class="login-btn" :disabled="forgotLoading" @click="sendForgotPassword">
            <span v-if="!forgotLoading">{{ i18n.t('login.forgot.send') }}</span>
            <span v-else class="spinner"></span>
          </button>
        </div>
      </div>
    </div>

    <footer class="footer">
      <div class="footer-container">
        <span class="footer-text">{{ i18n.t('footer.brand') }}</span>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  transition: background-color 0.3s ease, color 0.3s ease;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
}

.login-page.light {
  --bg-primary: #ffffff;
  --bg-secondary: #f5f5f7;
  --text-primary: #1d1d1f;
  --text-secondary: #86868b;
  --border-color: #d2d2d7;
  --hover-bg: #f5f5f7;
  --accent-color: #7c3aed;
  --accent-hover: #6d28d9;
  --input-bg: #ffffff;
  --error-bg: #fef2f2;
  --error-color: #dc3545;
  --error-border: #fecaca;
  --info-bg: #eef6ff;
  --info-color: #1459a6;
  --info-border: #c7def8;
}

.login-page.dark {
  --bg-primary: #1b1d21;
  --bg-secondary: #25282e;
  --text-primary: #f5f5f7;
  --text-secondary: #a6aab3;
  --border-color: #545a65;
  --hover-bg: #2f333b;
  --accent-color: #a78bfa;
  --accent-hover: #8b5cf6;
  --input-bg: #2a2f37;
  --error-bg: #442d2d;
  --error-color: #ff8b8b;
  --error-border: #7f5151;
  --info-bg: #1e2d3d;
  --info-color: #9fd0ff;
  --info-border: #39556f;
}

.login-page {
  background: var(--bg-primary);
  color: var(--text-primary);
}

.main-content {
  flex: 1;
  padding: 3rem 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.content-wrapper {
  width: min(480px, 100%);
}

.form-panel {
  padding: 0;
}

.login-card {
  height: 100%;
  border-radius: 12px;
  padding: 2rem;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  box-shadow: none;
  backdrop-filter: none;
}

.page-header {
  margin-bottom: 1.5rem;
  text-align: center;
}

.compact-header h2 {
  font-size: 2rem;
  font-weight: 600;
  line-height: 1.1;
  letter-spacing: -0.5px;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.subtitle {
  font-size: 1rem;
  color: var(--text-secondary);
}

.login-card {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 2rem;
}

.error-banner {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--error-bg);
  border: 1px solid var(--error-border);
  border-radius: 8px;
  padding: 0.75rem 1rem;
  margin-bottom: 1.25rem;
  color: var(--error-color);
  font-size: 0.875rem;
}

.error-banner svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  stroke: var(--error-color);
}

.info-banner {
  background: var(--info-bg);
  border: 1px solid var(--info-border);
  color: var(--info-color);
  border-radius: 8px;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--text-secondary);
  margin-bottom: 0.375rem;
}

.form-group input {
  width: 100%;
  padding: 0.5rem 0.875rem;
  font-size: 0.9375rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--input-bg);
  color: var(--text-primary);
  outline: none;
  transition: border-color 0.2s ease;
  font-family: inherit;
  box-sizing: border-box;
}

.form-group input::placeholder {
  color: var(--text-secondary);
  opacity: 0.6;
}

.form-group input:focus {
  border-color: var(--accent-color);
}

.password-input-wrapper {
  position: relative;
}

.password-input-wrapper input {
  padding-right: 3.2rem;
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
  border-radius: 999px;
  background: transparent;
  color: var(--text-secondary);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: color 0.2s ease, background-color 0.2s ease;
}

.password-toggle:hover {
  color: var(--text-primary);
  background: rgba(134, 134, 139, 0.12);
}

.password-toggle:focus-visible {
  outline: 2px solid var(--accent-color);
  outline-offset: 2px;
}

.password-toggle svg {
  width: 17px;
  height: 17px;
}

.form-options {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.forgot-link {
  border: none;
  background: transparent;
  color: var(--accent-color);
  font-size: 0.875rem;
  cursor: pointer;
  padding: 0;
}

.forgot-link:hover {
  text-decoration: underline;
}

.remember-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: var(--text-secondary);
  cursor: pointer;
  user-select: none;
  position: relative;
  padding-left: 1.625rem;
}

.remember-label input[type="checkbox"] {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.checkmark {
  position: absolute;
  left: 0;
  width: 16px;
  height: 16px;
  border: 1.5px solid var(--border-color);
  border-radius: 4px;
  background: var(--input-bg);
  transition: all 0.2s ease;
}

.remember-label input[type="checkbox"]:checked ~ .checkmark {
  background: var(--accent-color);
  border-color: var(--accent-color);
}

.remember-label input[type="checkbox"]:checked ~ .checkmark::after {
  content: '';
  position: absolute;
  left: 4.5px;
  top: 1.5px;
  width: 4px;
  height: 8px;
  border: solid #ffffff;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.login-btn {
  width: 100%;
  padding: 0.625rem;
  font-size: 0.9375rem;
  font-weight: 500;
  border: none;
  border-radius: 8px;
  background: var(--accent-color);
  color: #ffffff;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-family: inherit;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
  margin-top: 0.5rem;
  box-shadow: none;
}

.login-btn:hover:not(:disabled) {
  background: var(--accent-hover);
}

.login-btn:active:not(:disabled) {
  background: var(--accent-hover);
}

.login-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.register-link {
  text-align: center;
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-top: 1rem;
}

.register-link a {
  color: var(--accent-color);
  text-decoration: none;
  font-weight: 600;
}

.register-link a:hover {
  text-decoration: underline;
}

.footer {
  background: var(--bg-secondary);
  border-top: 1px solid var(--border-color);
  padding: 1rem 2rem;
  margin-top: auto;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  z-index: 1000;
}

.modal-card {
  width: 100%;
  max-width: 430px;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1.5rem;
}

.modal-card h2 {
  font-size: 1.25rem;
  margin-bottom: 0.35rem;
}

.modal-subtitle {
  color: var(--text-secondary);
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.modal-banner {
  margin-bottom: 1rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.secondary-btn {
  padding: 0.625rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: transparent;
  color: var(--text-primary);
  cursor: pointer;
  transition: background-color 0.2s ease, color 0.2s ease;
  font-size: 0.9375rem;
  font-weight: 500;
}

.secondary-btn:hover {
  background: var(--hover-bg);
  color: var(--text-primary);
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

@media (max-width: 640px) {
  .main-content {
    padding: 2rem 1.5rem;
  }

  .page-header h1 {
    font-size: 2rem;
  }

  .login-card {
    padding: 1.5rem;
  }
}
</style>
