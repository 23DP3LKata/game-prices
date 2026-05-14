<script setup>
import { computed, provide, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { useThemePreference } from '../composables/useThemePreference'
import { useI18nStore } from '../stores/i18n'

const route = useRoute()
const router = useRouter()

const selectedLanguage = ref('LV')
const selectedTheme = useThemePreference()
const i18n = useI18nStore()

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

const fieldErrors = reactive({
  password: '',
})

const fieldFocused = reactive({
  password: false,
})

const passwordHint = i18n.t('register.hint.password')
const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/

function getApiUrl(path) {
  return apiBaseUrl ? `${apiBaseUrl}${path}` : `/api${path}`
}

function validatePassword(value) {
  if (!value) {
    return ''
  }

  if (!passwordPattern.test(value)) {
    return i18n.t('register.hint.password')
  }

  return ''
}

function handleFieldFocus(field) {
  fieldFocused[field] = true
}

function handleFieldBlur(field, validator, value) {
  fieldFocused[field] = false
  fieldErrors[field] = validator(value)
}

function handleFieldInput(field, validator, value) {
  fieldErrors[field] = validator(value)
}

async function handleResetPassword() {
  errorMessage.value = ''
  successMessage.value = ''
  fieldErrors.password = validatePassword(password.value)

  if (!token.value) {
    errorMessage.value = i18n.t('messages.invalid_token')
    return
  }

  if (!email.value.trim() || !password.value || !passwordConfirmation.value) {
    errorMessage.value = i18n.t('messages.fill_all_fields')
    return
  }

  if (fieldErrors.password) {
    errorMessage.value = fieldErrors.password
    return
  }

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = i18n.t('messages.passwords_no_match')
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
      throw new Error(data?.message || i18n.t('reset.errors.failed'))
    }

    successMessage.value = data?.message || i18n.t('reset.success')
    password.value = ''
    passwordConfirmation.value = ''
  } catch (err) {
    errorMessage.value = err instanceof Error
      ? err.message
      : i18n.t('messages.something_went_wrong')
  } finally {
    isLoading.value = false
  }
}

async function goToLogin() {
  await router.push('/login')
}
</script>

<template>
  <div class="reset-page" :class="selectedTheme">
    <AppHeader
      v-model:selectedTheme="selectedTheme"
      v-model:selectedLanguage="selectedLanguage"
      activePage=""
    />

    <main class="main-content">
      <div class="content-wrapper">
        <section class="form-panel">
          <form class="reset-card" @submit.prevent="handleResetPassword">
            <header class="page-header compact-header">
              <h2>{{ i18n.t('reset.title') }}</h2>
              <p class="subtitle">{{ i18n.t('reset.subtitle') }}</p>
            </header>

            <div v-if="errorMessage" class="error-banner" role="alert">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              <span>{{ errorMessage }}</span>
            </div>

            <div v-if="successMessage" class="info-banner" role="status">
              {{ successMessage }}
            </div>

            <div class="form-group">
              <label for="password">{{ i18n.t('reset.new_password') }}</label>
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

              <transition name="field-message">
                <p v-if="fieldErrors.password" class="field-error">{{ fieldErrors.password }}</p>
                <p v-else-if="fieldFocused.password" class="field-hint">{{ passwordHint }}</p>
              </transition>
            </div>

            <div class="form-group">
              <label for="password-confirmation">{{ i18n.t('reset.confirm_password') }}</label>
              <div class="password-input-wrapper">
                <input
                  id="password-confirmation"
                  v-model="passwordConfirmation"
                  :type="showPasswordConfirmation ? 'text' : 'password'"
                  autocomplete="new-password"
                />
                <button
                  type="button"
                  class="password-toggle"
                  :aria-label="showPasswordConfirmation ? i18n.t('password.hide_confirmation') : i18n.t('password.show_confirmation')"
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

            <button type="submit" class="login-btn" :disabled="isLoading || !password || !passwordConfirmation">
              <span v-if="!isLoading">{{ i18n.t('reset.save') }}</span>
              <span v-else class="spinner"></span>
            </button>

            <button type="button" class="login-link-btn" @click="goToLogin">
              {{ i18n.t('reset.back_to_login') }}
            </button>
          </form>
        </section>
      </div>
    </main>

    <footer class="footer">
      <div class="footer-container">
        <span class="footer-text">{{ i18n.t('footer.brand') }}</span>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.reset-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  transition: background-color 0.3s ease, color 0.3s ease;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
}

.reset-page.light {
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

.reset-page.dark {
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

.reset-page {
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

.reset-card {
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

.login-link-btn {
  width: 100%;
  padding: 0.625rem;
  font-size: 0.9375rem;
  font-weight: 500;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: var(--accent-color);
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
  max-height: 50px;
}

.register-link {
  text-align: center;
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-top: 1rem;
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

  .compact-header h2 {
    font-size: 2rem;
  }

  .reset-card {
    padding: 1.5rem;
  }
}
</style>
