<script setup>
import { computed, provide, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { useThemePreference } from '../composables/useThemePreference'

const route = useRoute()
const router = useRouter()

const selectedLanguage = ref('ENG')
const selectedTheme = useThemePreference()

provide('theme', selectedTheme)

const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')
const token = computed(() => String(route.query.token || ''))
const email = ref(String(route.query.email || ''))
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

function getApiUrl(path) {
  return apiBaseUrl ? `${apiBaseUrl}${path}` : `/api${path}`
}

async function handleResetPassword() {
  errorMessage.value = ''
  successMessage.value = ''

  if (!token.value) {
    errorMessage.value = 'Invalid password reset token.'
    return
  }

  if (!email.value.trim() || !password.value || !passwordConfirmation.value) {
    errorMessage.value = 'Please fill in all fields.'
    return
  }

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'Passwords do not match.'
    return
  }

  isLoading.value = true

  try {
    const response = await fetch(getApiUrl('/reset-password'), {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        token: token.value,
        email: email.value.trim(),
        password: password.value,
        password_confirmation: passwordConfirmation.value,
      }),
    })

    const data = await response.json().catch(() => null)

    if (!response.ok) {
      throw new Error(data?.message || 'Failed to reset password.')
    }

    successMessage.value = data?.message || 'Password was reset successfully.'
    password.value = ''
    passwordConfirmation.value = ''
  } catch (err) {
    errorMessage.value = err instanceof Error
      ? err.message
      : 'Something went wrong. Please try again.'
  } finally {
    isLoading.value = false
  }
}

async function goToLogin() {
  await router.push('/login')
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
          <h1>Set New Password</h1>
          <p class="subtitle">Enter your email and choose a new password</p>
        </header>

        <div v-if="errorMessage" class="error-banner">{{ errorMessage }}</div>
        <div v-if="successMessage" class="info-banner">{{ successMessage }}</div>

        <form class="login-card" @submit.prevent="handleResetPassword">
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
            <label for="password">New password</label>
            <div class="password-input-wrapper">
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="At least 8 characters"
                autocomplete="new-password"
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

          <div class="form-group">
            <label for="password-confirmation">Confirm password</label>
            <div class="password-input-wrapper">
              <input
                id="password-confirmation"
                v-model="passwordConfirmation"
                :type="showPasswordConfirmation ? 'text' : 'password'"
                placeholder="Repeat your new password"
                autocomplete="new-password"
              />
              <button
                type="button"
                class="password-toggle"
                :aria-label="showPasswordConfirmation ? 'Hide password confirmation' : 'Show password confirmation'"
                :aria-pressed="showPasswordConfirmation"
                @click="showPasswordConfirmation = !showPasswordConfirmation"
              >
                <svg v-if="showPasswordConfirmation" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
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

          <button type="submit" class="login-btn" :disabled="isLoading">
            <span v-if="!isLoading">Save Password</span>
            <span v-else class="spinner"></span>
          </button>

          <p class="register-link">
            Back to
            <button type="button" class="inline-link" @click="goToLogin">Log In</button>
          </p>
        </form>
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

.error-banner {
  background: var(--error-bg);
  border: 1px solid var(--error-border);
  color: var(--error-color);
  border-radius: 8px;
  padding: 0.7rem;
  margin-bottom: 0.75rem;
}

.info-banner {
  background: var(--info-bg);
  border: 1px solid var(--info-border);
  color: var(--info-color);
  border-radius: 8px;
  padding: 0.7rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
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

.login-btn {
  width: 100%;
  padding: 0.75rem;
  font-size: 0.9375rem;
  font-weight: 500;
  border: none;
  border-radius: 8px;
  background: var(--accent-color);
  color: #fff;
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

.inline-link {
  border: none;
  background: transparent;
  color: var(--accent-color);
  font-size: inherit;
  font-weight: 500;
  cursor: pointer;
  padding: 0;
}

.inline-link:hover {
  text-decoration: underline;
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
