<script setup>
import { ref, provide, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { useAuthStore } from '../stores/auth'
import { useThemePreference } from '../composables/useThemePreference'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const selectedLanguage = ref('ENG')
const selectedTheme = useThemePreference()

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
    return 'Account created. Please check your email and confirm it before login.'
  }

  if (verifyStatus.value === 'verified') {
    return 'Email confirmed. You can now log in.'
  }

  if (verifyStatus.value === 'already-verified') {
    return 'Email is already confirmed. You can log in now.'
  }

  return ''
})

const verifyError = computed(() => {
  if (verifyStatus.value === 'invalid') {
    return 'Verification link is invalid or expired. Please request a new verification email.'
  }

  return ''
})

function getApiUrl(path) {
  return apiBaseUrl ? `${apiBaseUrl}${path}` : `/api${path}`
}

async function handleLogin() {
  errorMessage.value = ''

  if (!email.value || !password.value) {
    errorMessage.value = 'Please fill in all fields.'
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
      throw new Error(data?.message || 'Login failed.')
    }

    authStore.setUser(data.user)
    await router.push('/profile')
  } catch (err) {
    errorMessage.value = err.message || 'Something went wrong. Please try again.'
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
    forgotError.value = 'Please enter your email.'
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
      throw new Error(data?.message || 'Failed to send reset link.')
    }

    forgotSuccess.value = data?.message || 'Password reset link sent.'
  } catch (err) {
    forgotError.value = err instanceof Error
      ? err.message
      : 'Something went wrong. Please try again.'
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
        <header class="page-header">
          <h1>Log In</h1>
          <p class="subtitle">Welcome back to Game Prices</p>
        </header>

        <div v-if="verifyMessage" class="info-banner" role="status">
          {{ verifyMessage }}
        </div>

        <div v-if="verifyError" class="error-banner" role="alert">
          <span>{{ verifyError }}</span>
        </div>

        <form class="login-card" @submit.prevent="handleLogin">
          <div v-if="errorMessage" class="error-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ errorMessage }}</span>
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="your@email.com"
              autocomplete="email"
            />
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="password-input-wrapper">
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Enter your password"
                autocomplete="current-password"
              />
              <button
                type="button"
                class="password-toggle"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
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
              <span>Remember me</span>
            </label>
            <button type="button" class="forgot-link" @click="openForgotPassword">
              Forgot password?
            </button>
          </div>

          <button type="submit" class="login-btn" :disabled="isLoading">
            <span v-if="!isLoading">Log In</span>
            <span v-else class="spinner"></span>
          </button>

          <p class="register-link">
            Don't have an account?
            <router-link to="/register">Sign Up</router-link>
          </p>
        </form>
      </div>
    </main>

    <div v-if="forgotOpen" class="modal-backdrop" @click.self="closeForgotPassword">
      <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="forgot-title">
        <h2 id="forgot-title">Reset password</h2>
        <p class="modal-subtitle">Enter your email and we will send a reset link.</p>

        <div v-if="forgotError" class="error-banner modal-banner">
          <span>{{ forgotError }}</span>
        </div>

        <div v-if="forgotSuccess" class="info-banner modal-banner" role="status">
          {{ forgotSuccess }}
        </div>

        <div class="form-group">
          <label for="forgot-email">Email</label>
          <input
            id="forgot-email"
            v-model="forgotEmail"
            type="email"
            placeholder="your@email.com"
            autocomplete="email"
          />
        </div>

        <div class="modal-actions">
          <button type="button" class="secondary-btn" @click="closeForgotPassword">Close</button>
          <button type="button" class="login-btn" :disabled="forgotLoading" @click="sendForgotPassword">
            <span v-if="!forgotLoading">Send link</span>
            <span v-else class="spinner"></span>
          </button>
        </div>
      </div>
    </div>

    <footer class="footer">
      <div class="footer-container">
        <span class="footer-text">&copy; 2025 Game Prices</span>
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
  --accent-color: #0071e3;
  --accent-hover: #0077ed;
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
  --accent-color: #2997ff;
  --accent-hover: #40a9ff;
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
  max-width: 420px;
  width: 100%;
}

.page-header {
  margin-bottom: 2rem;
  text-align: center;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 600;
  line-height: 1.1;
  letter-spacing: -1px;
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
  padding: 0.625rem 0.875rem;
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
  padding-right: 3rem;
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
  background: rgba(134, 134, 139, 0.08);
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
  padding: 0.75rem;
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
  min-height: 44px;
}

.login-btn:hover:not(:disabled) {
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
  margin-top: 1.25rem;
}

.register-link a {
  color: var(--accent-color);
  text-decoration: none;
  font-weight: 500;
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
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: transparent;
  color: var(--text-primary);
  cursor: pointer;
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
