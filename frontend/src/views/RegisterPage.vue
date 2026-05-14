<script setup>
import { reactive, ref, provide } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { useThemePreference } from '../composables/useThemePreference'

const router = useRouter()

const selectedLanguage = ref('ENG')
const selectedTheme = useThemePreference()

provide('theme', selectedTheme)

const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')

const nickname = ref('')
const email = ref('')
const password = ref('')
const passwordConfirm = ref('')
const showPassword = ref(false)
const showPasswordConfirm = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const verifyDialogOpen = ref(false)
const registeredEmail = ref('')

const fieldErrors = reactive({
  nickname: '',
  email: '',
  password: '',
})

const fieldFocused = reactive({
  nickname: false,
  email: false,
  password: false,
})

let nicknameCheckTimeout = null

const nicknameHint = 'This is the name people will know you by. You can always change it later.'
const emailHint = 'We will use this email to send your verification link.'
const passwordHint = 'Use at least 8 characters, uppercase letter, number, special character.'

const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/

function clearFieldErrors() {
  fieldErrors.nickname = ''
  fieldErrors.email = ''
  fieldErrors.password = ''
}

function resetFieldFocus() {
  fieldFocused.nickname = false
  fieldFocused.email = false
  fieldFocused.password = false
}

function validateNickname(value) {
  const trimmedValue = value.trim()

  if (!trimmedValue) {
    return ''
  }

  if (trimmedValue.length < 4 || trimmedValue.length > 100) {
    return 'Usernames must be between 4 and 100 characters.'
  }

  if (!/^[A-Za-z0-9]+$/.test(trimmedValue)) {
    return 'Usernames must only contain alphanumeric characters.'
  }

  return ''
}

async function checkNicknameAvailability(value) {
  const trimmedValue = value.trim()
  const formatError = validateNickname(trimmedValue)

  if (formatError) {
    fieldErrors.nickname = formatError
    return
  }

  if (!trimmedValue) {
    fieldErrors.nickname = ''
    return
  }

  try {
    const response = await fetch(apiBaseUrl ? `${apiBaseUrl}/check-nickname` : '/api/check-nickname', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ nickname: trimmedValue }),
    })

    const data = await response.json().catch(() => null)

    if (!response.ok || data?.available === false) {
      fieldErrors.nickname = 'This username is already taken.'
      return
    }

    fieldErrors.nickname = ''
  } catch (err) {
    fieldErrors.nickname = ''
  }
}

function validateEmail(value) {
  const trimmedValue = value.trim()

  if (!trimmedValue) {
    return ''
  }

  if (/\s/.test(trimmedValue)) {
    return 'Please enter a valid email.'
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmedValue)) {
    return 'Please enter a valid email.'
  }

  return ''
}

function validatePassword(value) {
  if (!value) {
    return ''
  }

  if (!passwordPattern.test(value)) {
    return 'Use at least 8 characters, uppercase letter, number, special character.'
  }

  return ''
}

function setServerFieldErrors(errors) {
  fieldErrors.nickname = errors?.nickname?.[0] || ''
  fieldErrors.email = errors?.email?.[0] || ''
  fieldErrors.password = errors?.password?.[0] || ''
}

function handleFieldFocus(field) {
  fieldFocused[field] = true
}

function handleFieldBlur(field, validator, value) {
  fieldFocused[field] = false
  if (field !== 'nickname') {
    fieldErrors[field] = validator(value)
  }
}

function handleFieldInput(field, validator, value) {
  fieldErrors[field] = validator(value)

  if (field === 'nickname') {
    clearTimeout(nicknameCheckTimeout)
    nicknameCheckTimeout = setTimeout(() => {
      checkNicknameAvailability(value)
    }, 600)
  }
}

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
  clearFieldErrors()

  const trimmedNickname = nickname.value.trim()
  const trimmedEmail = email.value.trim()

  if (!trimmedNickname || !trimmedEmail || !password.value || !passwordConfirm.value) {
    errorMessage.value = 'Please fill in all fields.'
    return
  }

  fieldFocused.nickname = true
  fieldFocused.email = true
  fieldFocused.password = true

  fieldErrors.nickname = validateNickname(trimmedNickname)
  fieldErrors.email = validateEmail(trimmedEmail)
  fieldErrors.password = validatePassword(password.value)

  if (fieldErrors.nickname || fieldErrors.email || fieldErrors.password) {
    return
  }

  if (password.value !== passwordConfirm.value) {
    errorMessage.value = 'Passwords do not match.'
    return
  }

  if (!passwordPattern.test(password.value)) {
    errorMessage.value = 'Password must be at least 8 characters and include one uppercase letter, one number, and one special character.'
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
      if (data?.errors) {
        setServerFieldErrors(data.errors)
        errorMessage.value = ''
        return
      }

      throw new Error(getErrorMessage(data))
    }

    nickname.value = ''
    email.value = ''
    password.value = ''
    passwordConfirm.value = ''
    clearFieldErrors()
    resetFieldFocus()
    registeredEmail.value = trimmedEmail
    verifyDialogOpen.value = true
  } catch (err) {
    errorMessage.value = err instanceof Error
      ? err.message
      : 'Something went wrong. Please try again.'
  } finally {
    isLoading.value = false
  }
}

async function goToLogin() {
  verifyDialogOpen.value = false

  await router.push({
    path: '/login',
    query: {
      verify: 'sent',
      email: registeredEmail.value,
    },
  })
}
</script>

<template>
  <div class="register-page" :class="selectedTheme">
    <AppHeader
      v-model:selectedTheme="selectedTheme"
      v-model:selectedLanguage="selectedLanguage"
      activePage=""
    />

    <main class="main-content">
      <div class="content-wrapper">
        <section class="form-panel">
          <form class="register-card" novalidate @submit.prevent="handleRegister">
            <header class="page-header compact-header">
              <h2>Sign up</h2>
              <p class="subtitle">Sign up to start tracking game prices</p>
            </header>

            <div v-if="errorMessage" class="error-banner" role="alert">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              <span>{{ errorMessage }}</span>
            </div>

            <div class="form-group">
              <label for="nickname">Username</label>
              <input
                id="nickname"
                v-model="nickname"
                type="text"
                autocomplete="username"
                :aria-invalid="Boolean(fieldErrors.nickname)"
                @focus="handleFieldFocus('nickname')"
                @blur="fieldFocused.nickname = false"
                @input="handleFieldInput('nickname', validateNickname, nickname)"
              />

              <transition name="field-message">
                <p v-if="fieldErrors.nickname" class="field-error">{{ fieldErrors.nickname }}</p>
                <p v-else-if="fieldFocused.nickname" class="field-hint">{{ nicknameHint }}</p>
              </transition>
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input
                id="email"
                v-model="email"
                type="text"
                autocomplete="email"
                inputmode="email"
                spellcheck="false"
                :aria-invalid="Boolean(fieldErrors.email)"
                @focus="handleFieldFocus('email')"
                @blur="handleFieldBlur('email', validateEmail, email)"
                @input="handleFieldInput('email', validateEmail, email)"
              />

              <transition name="field-message">
                <p v-if="fieldErrors.email" class="field-error">{{ fieldErrors.email }}</p>
                <p v-else-if="fieldFocused.email" class="field-hint">{{ emailHint }}</p>
              </transition>
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <div class="password-input-wrapper">
                <input
                  id="password"
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="new-password"
                  :aria-invalid="Boolean(fieldErrors.password)"
                  @focus="handleFieldFocus('password')"
                  @blur="handleFieldBlur('password', validatePassword, password)"
                  @input="handleFieldInput('password', validatePassword, password)"
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

              <transition name="field-message">
                <p v-if="fieldErrors.password" class="field-error">{{ fieldErrors.password }}</p>
                <p v-else-if="fieldFocused.password" class="field-hint">{{ passwordHint }}</p>
              </transition>
            </div>

            <div class="form-group">
              <label for="password-confirm">Confirm Password</label>
              <div class="password-input-wrapper">
                <input
                  id="password-confirm"
                  v-model="passwordConfirm"
                  :type="showPasswordConfirm ? 'text' : 'password'"
                  autocomplete="new-password"
                />
                <button
                  type="button"
                  class="password-toggle"
                  :aria-label="showPasswordConfirm ? 'Hide password confirmation' : 'Show password confirmation'"
                  :aria-pressed="showPasswordConfirm"
                  @click="showPasswordConfirm = !showPasswordConfirm"
                >
                  <svg v-if="showPasswordConfirm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
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

            <button type="submit" class="register-btn" :disabled="isLoading">
              <span v-if="!isLoading">Sign up</span>
              <span v-else class="spinner"></span>
            </button>

            <button type="button" class="login-link-btn" @click="$router.push('/login')">
              Have an account? Log In
            </button>
          </form>
        </section>
      </div>
    </main>

    <div v-if="verifyDialogOpen" class="modal-backdrop" @click.self="goToLogin">
      <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="verify-title">
        <h2 id="verify-title">Confirm your email</h2>
        <p class="modal-subtitle">
          We sent a verification email to <strong>{{ registeredEmail }}</strong>.
          Please open the message and click the link. Login will be available only after confirmation.
        </p>

        <div class="modal-actions">
          <button type="button" class="register-btn" @click="goToLogin">
            Go to login
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
  --accent-color: #7c3aed;
  --accent-hover: #6d28d9;
  --input-bg: #ffffff;
  --error-bg: #fef2f2;
  --error-color: #dc3545;
  --error-border: #fecaca;
}

.register-page.dark {
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
  width: min(480px, 100%);
}

.form-panel {
  padding: 0;
}

.register-card {
  height: 100%;
  border-radius: 12px;
  padding: 2rem;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  box-shadow: none;
  backdrop-filter: none;
}

.page-header {
  margin-bottom: 2rem;
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

.field-hint,
.field-error {
  margin-top: 0.4rem;
  font-size: 0.8rem;
  line-height: 1.45;
}

.field-hint {
  color: var(--text-secondary);
}

.field-error {
  color: var(--error-color);
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

.form-group input[aria-invalid='true'] {
  border-color: var(--error-color);
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

.register-btn {
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

.register-btn:hover:not(:disabled) {
  background: var(--accent-hover);
}

.register-btn:active:not(:disabled) {
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
  margin-top: 1rem;
}

.login-link a {
  color: var(--accent-color);
  text-decoration: none;
  font-weight: 600;
}

.login-link a:hover {
  text-decoration: underline;
}

.login-link-btn {
  width: 100%;
  padding: 0.625rem;
  font-size: 0.9375rem;
  font-weight: 500;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: var(--text-secondary);
  cursor: pointer;
  transition: background-color 0.2s ease, color 0.2s ease;
  font-family: inherit;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
  margin-top: 0.5rem;
  box-shadow: none;
}

.login-link-btn:hover {
  background: var(--hover-bg);
  color: var(--text-primary);
}

.field-message-enter-active {
  transition: opacity 1s ease, transform 1s ease, max-height 1s ease;
}

.field-message-leave-active {
  transition: opacity 0s ease, transform 0s ease, max-height 0s ease;
}

.field-message-enter-from,
.field-message-leave-to {
  opacity: 0;
  transform: translateY(-6px);
  max-height: 0;
}

.field-message-enter-to,
.field-message-leave-from {
  opacity: 1;
  transform: translateY(0);
  max-height: 64px;
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
  border-radius: 18px;
  padding: 1.5rem;
}

.modal-card h2 {
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
  color: var(--text-primary);
}

.modal-subtitle {
  color: var(--text-secondary);
  margin-bottom: 1rem;
  font-size: 0.9rem;
  line-height: 1.45;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
}

@media (max-width: 960px) {
  .content-wrapper {
    width: min(760px, 100%);
  }
}

@media (max-width: 640px) {
  .main-content {
    padding: 1.25rem 0.85rem 2rem;
  }

  .form-panel {
    border-radius: 20px;
  }

  .register-card {
    padding: 1.1rem;
  }

  .compact-header h2 {
    font-size: 1.75rem;
  }
}
</style>
