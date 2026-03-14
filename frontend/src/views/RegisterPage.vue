<script setup>
import { ref, provide } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'

const router = useRouter()

const selectedCurrency = ref('EUR')
const selectedLanguage = ref('ENG')
const selectedTheme = ref('light')

provide('theme', selectedTheme)

const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')

const nickname = ref('')
const email = ref('')
const password = ref('')
const passwordConfirm = ref('')
const isLoading = ref(false)
const errorMessage = ref('')

function getErrorMessage(payload) {
  if (!payload) {
    return 'Registration failed.'
  }

  if (payload.errors) {
    const firstFieldErrors = Object.values(payload.errors).find(
      (fieldErrors) => Array.isArray(fieldErrors) && fieldErrors.length > 0,
    )

    if (firstFieldErrors) {
      return firstFieldErrors[0]
    }
  }

  return payload.message || 'Registration failed.'
}

async function handleRegister() {
  errorMessage.value = ''

  const trimmedNickname = nickname.value.trim()
  const trimmedEmail = email.value.trim()

  if (!trimmedNickname || !trimmedEmail || !password.value || !passwordConfirm.value) {
    errorMessage.value = 'Please fill in all fields.'
    return
  }

  if (password.value !== passwordConfirm.value) {
    errorMessage.value = 'Passwords do not match.'
    return
  }

  if (password.value.length < 8) {
    errorMessage.value = 'Password must be at least 8 characters.'
    return
  }

  isLoading.value = true

  try {
    const response = await fetch(apiBaseUrl ? `${apiBaseUrl}/register` : '/api/register', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        nickname: trimmedNickname,
        email: trimmedEmail,
        password: password.value,
        password_confirmation: passwordConfirm.value,
      }),
    })

    const data = await response.json().catch(() => null)

    if (!response.ok) {
      throw new Error(getErrorMessage(data))
    }

    nickname.value = ''
    email.value = ''
    password.value = ''
    passwordConfirm.value = ''

    await router.push('/login')
  } catch (err) {
    errorMessage.value = err instanceof Error
      ? err.message
      : 'Something went wrong. Please try again.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="register-page" :class="selectedTheme">
    <AppHeader
      v-model:selectedTheme="selectedTheme"
      v-model:selectedCurrency="selectedCurrency"
      v-model:selectedLanguage="selectedLanguage"
      activePage=""
    />

    <main class="main-content">
      <div class="content-wrapper">
        <header class="page-header">
          <h1>Sign Up</h1>
          <p class="subtitle">Create your Game Prices account</p>
        </header>

        <form class="register-card" @submit.prevent="handleRegister">
          <div v-if="errorMessage" class="error-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ errorMessage }}</span>
          </div>

          <div class="form-group">
            <label for="nickname">Nickname</label>
            <input
              id="nickname"
              v-model="nickname"
              type="text"
              placeholder="Your nickname"
              autocomplete="username"
            />
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
            <input
              id="password"
              v-model="password"
              type="password"
              placeholder="At least 8 characters"
              autocomplete="new-password"
            />
          </div>

          <div class="form-group">
            <label for="password-confirm">Confirm Password</label>
            <input
              id="password-confirm"
              v-model="passwordConfirm"
              type="password"
              placeholder="Repeat your password"
              autocomplete="new-password"
            />
          </div>

          <button type="submit" class="register-btn" :disabled="isLoading">
            <span v-if="!isLoading">Create Account</span>
            <span v-else class="spinner"></span>
          </button>

          <p class="login-link">
            Already have an account?
            <router-link to="/login">Log In</router-link>
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
.register-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  transition: background-color 0.3s ease, color 0.3s ease;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
}

.register-page.light {
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
}

.register-page.dark {
  --bg-primary: #000000;
  --bg-secondary: #1d1d1f;
  --text-primary: #f5f5f7;
  --text-secondary: #86868b;
  --border-color: #424245;
  --hover-bg: #1d1d1f;
  --accent-color: #2997ff;
  --accent-hover: #40a9ff;
  --input-bg: #1d1d1f;
  --error-bg: #2d1b1b;
  --error-color: #ff6b6b;
  --error-border: #5c2a2a;
}

.register-page {
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

.register-card {
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

.register-btn {
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

.register-btn:hover:not(:disabled) {
  background: var(--accent-hover);
}

.register-btn:disabled {
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

.login-link {
  text-align: center;
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-top: 1.25rem;
}

.login-link a {
  color: var(--accent-color);
  text-decoration: none;
  font-weight: 500;
}

.login-link a:hover {
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

  .register-card {
    padding: 1.5rem;
  }
}
</style>
