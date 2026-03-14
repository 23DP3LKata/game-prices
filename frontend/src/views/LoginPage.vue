<script setup>
import { ref, provide } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const selectedCurrency = ref('EUR')
const selectedLanguage = ref('ENG')
const selectedTheme = ref('light')

provide('theme', selectedTheme)

const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

async function handleLogin() {
  errorMessage.value = ''

  if (!email.value || !password.value) {
    errorMessage.value = 'Please fill in all fields.'
    return
  }

  isLoading.value = true

  try {
    const response = await fetch('/api/login', {
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
</script>

<template>
  <div class="login-page" :class="selectedTheme">
    <AppHeader
      v-model:selectedTheme="selectedTheme"
      v-model:selectedCurrency="selectedCurrency"
      v-model:selectedLanguage="selectedLanguage"
      activePage=""
    />

    <main class="main-content">
      <div class="content-wrapper">
        <header class="page-header">
          <h1>Log In</h1>
          <p class="subtitle">Welcome back to Game Prices</p>
        </header>

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
            <input
              id="password"
              v-model="password"
              type="password"
              placeholder="Enter your password"
              autocomplete="current-password"
            />
          </div>

          <div class="form-options">
            <label class="remember-label">
              <input v-model="rememberMe" type="checkbox" />
              <span class="checkmark"></span>
              <span>Remember me</span>
            </label>
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
}

.login-page.dark {
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

.form-options {
  display: flex;
  align-items: center;
  margin-bottom: 1.5rem;
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
