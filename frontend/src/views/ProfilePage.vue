<script setup>
import { computed, onMounted, provide, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import { useAuthStore } from '../stores/auth'
import { useProfileApi } from '../composables/useProfileApi'
import { useThemePreference } from '../composables/useThemePreference'
import { useI18nStore } from '../stores/i18n'

const router = useRouter()
const authStore = useAuthStore()
const { request } = useProfileApi()
const i18n = useI18nStore()

const selectedLanguage = ref('LV')
const selectedTheme = useThemePreference()

const profileData = ref({
  nickname: i18n.t('profile.default_user'),
  email: '',
})

const activeEditField = ref('')
const isSaving = ref(false)
const fieldErrors = ref({
  nickname: '',
  email: '',
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
  deleteAccountPassword: '',
})
const fieldFocused = ref({
  nickname: false,
  email: false,
  currentPassword: false,
  newPassword: false,
  confirmPassword: false,
  deleteAccountPassword: false,
})
const statusType = ref('')
const statusMessage = ref('')

const isEmailVisible = ref(false)

const editDraft = ref({
  nickname: '',
  email: '',
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
  deleteAccountPassword: '',
})

provide('theme', selectedTheme)

const displayedEmail = computed(() => {
  if (!profileData.value.email) {
    return i18n.t('profile.email_not_set')
  }

  return isEmailVisible.value ? profileData.value.email : maskEmail(profileData.value.email)
})

const displayedPassword = computed(() => maskPassword())
const isEmailChangeAllowed = computed(() => Boolean(authStore.user?.email_verified_at))

const nicknameHint = computed(() => i18n.t('profile.hint.update_nickname'))
const emailHint = computed(() => i18n.t('profile.hint.update_email'))
const emailVerificationHint = computed(() => i18n.t('profile.hint.email_verification_required'))
const currentPasswordHint = computed(() => i18n.t('profile.hint.current_password'))
const newPasswordHint = computed(() => i18n.t('profile.hint.new_password'))
const confirmPasswordHint = computed(() => i18n.t('profile.hint.confirm_password'))
const deleteAccountHint = computed(() => i18n.t('profile.hint.delete_account_password'))

const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/

function syncProfileDataFromStore() {
  profileData.value = {
    ...profileData.value,
    nickname: authStore.user?.nickname ?? i18n.t('profile.default_user'),
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

function clearFieldErrors() {
  fieldErrors.value = {
    nickname: '',
    email: '',
    currentPassword: '',
    newPassword: '',
    confirmPassword: '',
    deleteAccountPassword: '',
  }
}

function resetFieldFocus() {
  fieldFocused.value = {
    nickname: false,
    email: false,
    currentPassword: false,
    newPassword: false,
    confirmPassword: false,
    deleteAccountPassword: false,
  }
}

function clearRowState() {
  clearFieldErrors()
  resetFieldFocus()
}

function setFieldError(fieldName, message) {
  fieldErrors.value = {
    ...fieldErrors.value,
    [fieldName]: message,
  }
}

function setFieldFocus(fieldName, isFocused) {
  fieldFocused.value = {
    ...fieldFocused.value,
    [fieldName]: isFocused,
  }
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
    return i18n.t('profile.email_not_set')
  }

  const [localPart, domainPart] = value.split('@')
  const domainSegments = domainPart.split('.')
  const domainName = domainSegments.shift() || ''
  const domainSuffix = domainSegments.length ? `.${domainSegments.join('.')}` : ''

  return `${maskSegment(localPart, 2)}@${maskSegment(domainName, 1)}${domainSuffix}`
}

function maskPassword() {
  const hiddenLength = 10
  return '*'.repeat(hiddenLength)
}

function isValidEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
}

function validateNickname(value) {
  const nextNickname = value.trim()

  if (!nextNickname) {
    return ''
  }

  if (nextNickname.length < 4 || nextNickname.length > 100) {
    return i18n.t('register.errors.nickname_length')
  }

  if (!/^[A-Za-z0-9]+$/.test(nextNickname)) {
    return i18n.t('register.errors.nickname_format')
  }

  return ''
}

function validateEmail(value) {
  const nextEmail = value.trim()

  if (!nextEmail) {
    return ''
  }

  if (!isValidEmail(nextEmail)) {
    return i18n.t('messages.valid_email')
  }

  return ''
}

function validateCurrentPassword(value) {
  if (!value.trim()) {
    return ''
  }

  return ''
}

function validateNewPassword(value) {
  if (!value) {
    return ''
  }

  if (!passwordPattern.test(value)) {
    return i18n.t('register.hint.password')
  }

  return ''
}

function validateConfirmPassword(value) {
  if (!value) {
    return ''
  }

  if (value !== editDraft.value.newPassword) {
    return i18n.t('profile.errors.passwords_no_match')
  }

  return ''
}

function validateDeleteAccountPassword(value) {
  if (!value.trim()) {
    return ''
  }

  return ''
}

function handleFieldFocus(fieldName) {
  setFieldFocus(fieldName, true)
}

function handleFieldBlur(fieldName, validator, value) {
  setFieldFocus(fieldName, false)
  setFieldError(fieldName, validator(value))
}

function handleFieldInput(fieldName, validator, value) {
  setFieldError(fieldName, validator(value))

  if (fieldName === 'newPassword') {
    setFieldError('confirmPassword', validateConfirmPassword(editDraft.value.confirmPassword))
  }
}

function startEdit(fieldName) {
  if (activeEditField.value === fieldName) {
    cancelEdit()
    return
  }

  activeEditField.value = fieldName
  clearRowState()
  clearStatus()

  if (fieldName === 'nickname') {
    editDraft.value.nickname = profileData.value.nickname
  }

  if (fieldName === 'email') {
    editDraft.value.email = profileData.value.email
    editDraft.value.currentPassword = ''
  }

  if (fieldName === 'password') {
    editDraft.value.currentPassword = ''
    editDraft.value.newPassword = ''
    editDraft.value.confirmPassword = ''
  }

  if (fieldName === 'delete-account') {
    editDraft.value.deleteAccountPassword = ''
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

async function saveNickname() {
  const nextNickname = editDraft.value.nickname.trim()

  if (!nextNickname) {
    setFieldError('nickname', i18n.t('profile.errors.nickname_empty'))
    return
  }

  const nicknameValidation = validateNickname(nextNickname)

  if (nicknameValidation) {
    setFieldError('nickname', nicknameValidation)
    return
  }

  isSaving.value = true
  setFieldError('nickname', '')

  try {
    const result = await request('/profile/nickname', {
      method: 'PATCH',
      body: JSON.stringify({
        nickname: nextNickname,
      }),
    })

    if (!result) {
      return
    }

    const { response, data } = result

    if (!response.ok) {
      throw new Error(data?.errors?.nickname?.[0] || data?.message || i18n.t('profile.errors.unable_update_nickname'))
    }

    profileData.value.nickname = data.user.nickname
    authStore.setUser({
      ...(authStore.user ?? {}),
      ...data.user,
    })

    cancelEdit()
    setStatus('success', i18n.t('profile.success.nickname_updated'))
  } catch (err) {
    setFieldError('nickname', err instanceof Error ? err.message : i18n.t('profile.errors.unable_update_nickname'))
  } finally {
    isSaving.value = false
  }
}

async function saveEmail() {
  const nextEmail = editDraft.value.email.trim().toLowerCase()
  const currentPassword = editDraft.value.currentPassword.trim()

  if (!isEmailChangeAllowed.value) {
    setFieldError('currentPassword', i18n.t('profile.errors.email_change_requires_verified_account'))
    return
  }

  if (!nextEmail) {
    setFieldError('email', i18n.t('profile.errors.email_empty'))
    return
  }

  if (!currentPassword) {
    setFieldError('currentPassword', i18n.t('profile.errors.enter_current_password'))
    return
  }

  if (!isValidEmail(nextEmail)) {
    setFieldError('email', i18n.t('profile.errors.invalid_email'))
    return
  }

  isSaving.value = true
  setFieldError('email', '')
  setFieldError('currentPassword', '')

  try {
    const result = await request('/profile/email', {
      method: 'PATCH',
      body: JSON.stringify({
        email: nextEmail,
        current_password: currentPassword,
      }),
    })

    if (!result) {
      return
    }

    const { response, data } = result

    if (!response.ok) {
      const emailError = data?.errors?.email?.[0]
      const currentPasswordError = data?.errors?.current_password?.[0]

      if (currentPasswordError) {
        throw new Error(currentPasswordError)
      }

      throw new Error(emailError || data?.message || i18n.t('profile.errors.unable_update_email'))
    }

    profileData.value.email = data.user.email
    authStore.setUser({
      ...(authStore.user ?? {}),
      ...data.user,
    })

    isEmailVisible.value = false
    cancelEdit()
    setStatus('success', i18n.t('profile.success.email_updated'))
  } catch (err) {
    setFieldError('email', err instanceof Error ? err.message : i18n.t('profile.errors.unable_update_email'))
  } finally {
    isSaving.value = false
  }
}

async function savePassword() {
  const currentPassword = editDraft.value.currentPassword.trim()
  const newPassword = editDraft.value.newPassword
  const confirmPassword = editDraft.value.confirmPassword

  if (!currentPassword) {
    setFieldError('currentPassword', i18n.t('profile.errors.enter_current_password'))
    return
  }

  const passwordValidation = validateNewPassword(newPassword)

  if (passwordValidation) {
    setFieldError('newPassword', passwordValidation)
    return
  }

  if (newPassword !== confirmPassword) {
    setFieldError('confirmPassword', i18n.t('profile.errors.passwords_no_match'))
    return
  }

  isSaving.value = true
  setFieldError('currentPassword', '')
  setFieldError('newPassword', '')
  setFieldError('confirmPassword', '')

  try {
    const result = await request('/profile/password', {
      method: 'PATCH',
      body: JSON.stringify({
        current_password: currentPassword,
        new_password: newPassword,
        new_password_confirmation: confirmPassword,
      }),
    })

    if (!result) {
      return
    }

    const { response, data } = result

    if (!response.ok) {
      throw new Error(
        data?.errors?.current_password?.[0]
          || data?.errors?.new_password?.[0]
          || data?.message
          || 'Unable to update password.',
      )
    }

    cancelEdit()
    setStatus('success', i18n.t('profile.success.password_updated'))
  } catch (err) {
    setFieldError('currentPassword', err instanceof Error ? err.message : i18n.t('profile.errors.unable_update_password'))
  } finally {
    isSaving.value = false
  }
}

async function deleteAccount() {
  const currentPassword = editDraft.value.deleteAccountPassword.trim()

  if (!currentPassword) {
    setFieldError('deleteAccountPassword', i18n.t('profile.errors.enter_current_password'))
    return
  }

  isSaving.value = true
  setFieldError('deleteAccountPassword', '')

  try {
    const result = await request('/profile/account', {
      method: 'DELETE',
      body: JSON.stringify({
        current_password: currentPassword,
      }),
    })

    if (!result) {
      return
    }

    const { response, data } = result

    if (!response.ok) {
      throw new Error(data?.errors?.current_password?.[0] || data?.message || i18n.t('profile.errors.unable_delete_account'))
    }

    authStore.clearUser()

    await router.push('/login')
  } catch (err) {
    setFieldError('deleteAccountPassword', err instanceof Error ? err.message : i18n.t('profile.errors.unable_delete_account'))
  } finally {
    isSaving.value = false
  }
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

  if (fieldName === 'delete-account') {
    return deleteAccount()
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
      v-model:selectedLanguage="selectedLanguage"
      activePage="profile"
    />

    <main class="main-content">
      <div class="content-wrapper">
        <header class="page-header">
          <h1>{{ i18n.t('profile.settings') }}</h1>
          <p class="subtitle">
            {{ i18n.t('profile.subtitle') }}
          </p>
        </header>

        <section class="settings-card">
          <div class="avatar-top">
            <div class="avatar-shell">
              <div class="avatar-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
              </div>
            </div>
          </div>

          <div class="settings-panel">
            <div v-if="statusMessage" class="status-banner" :class="statusType">
              {{ statusMessage }}
            </div>

            <div class="settings-list">
              <div class="setting-row" :class="{ editing: activeEditField === 'nickname' }">
                <div class="setting-main">
                  <div class="setting-copy">
                    <p class="setting-label">{{ i18n.t('profile.label.nickname') }}</p>
                  </div>

                  <Transition name="setting-swap" mode="out-in">
                    <div v-if="activeEditField !== 'nickname'" key="nickname-display" class="setting-display">
                      <p class="setting-value">{{ profileData.nickname }}</p>
                      <p class="setting-hint">{{ i18n.t('profile.hint.update_nickname') }}</p>
                    </div>

                    <div v-else key="nickname-edit" class="setting-edit">
                      <input
                        v-model="editDraft.nickname"
                        type="text"
                        class="setting-input"
                        autocomplete="nickname"
                        :aria-invalid="Boolean(fieldErrors.nickname)"
                        @focus="handleFieldFocus('nickname')"
                        @blur="handleFieldBlur('nickname', validateNickname, editDraft.nickname)"
                        @input="handleFieldInput('nickname', validateNickname, editDraft.nickname)"
                        @keyup.enter="saveField('nickname')"
                        @keyup.esc="cancelEdit"
                      />

                      <transition name="field-message">
                        <p v-if="fieldErrors.nickname" class="field-error">{{ fieldErrors.nickname }}</p>
                        <p v-else-if="fieldFocused.nickname" class="field-hint">{{ nicknameHint }}</p>
                      </transition>

                      <div class="edit-actions">
                        <button type="button" class="action-btn save-btn" :disabled="isSaving" @click="saveField('nickname')">
                          {{ i18n.t('profile.save') }}
                        </button>
                        <button type="button" class="action-btn cancel-btn" :disabled="isSaving" @click="cancelEdit">
                          {{ i18n.t('buttons.cancel') }}
                        </button>
                      </div>

                    </div>
                  </Transition>
                </div>

                <div v-if="activeEditField !== 'nickname'" class="setting-actions">
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
                    <p class="setting-label">{{ i18n.t('profile.label.email') }}</p>
                  </div>

                  <Transition name="setting-swap" mode="out-in">
                    <div v-if="activeEditField !== 'email'" key="email-display" class="setting-display">
                      <p class="setting-value sensitive">{{ displayedEmail }}</p>
                      <p class="setting-hint">{{ i18n.t('profile.hint.email_linked') }}</p>
                    </div>

                    <div v-else key="email-edit" class="setting-edit">
                      <input
                        v-model="editDraft.email"
                        type="email"
                        class="setting-input"
                        autocomplete="email"
                        :aria-invalid="Boolean(fieldErrors.email)"
                        @focus="handleFieldFocus('email')"
                        @blur="handleFieldBlur('email', validateEmail, editDraft.email)"
                        @input="handleFieldInput('email', validateEmail, editDraft.email)"
                        @keyup.esc="cancelEdit"
                      />

                      <transition name="field-message">
                        <p v-if="fieldErrors.email" class="field-error">{{ fieldErrors.email }}</p>
                        <p v-else-if="fieldFocused.email" class="field-hint">{{ emailHint }}</p>
                      </transition>

                      <div class="field-stack">
                        <label class="input-label" for="email-current-password">{{ i18n.t('profile.label.enter_current_password') }}</label>
                        <div class="password-input-wrapper">
                          <input
                            id="email-current-password"
                            v-model="editDraft.currentPassword"
                            type="password"
                            class="setting-input"
                            autocomplete="current-password"
                            :aria-invalid="Boolean(fieldErrors.currentPassword)"
                            @focus="handleFieldFocus('currentPassword')"
                            @blur="handleFieldBlur('currentPassword', validateCurrentPassword, editDraft.currentPassword)"
                            @input="handleFieldInput('currentPassword', validateCurrentPassword, editDraft.currentPassword)"
                            @keyup.enter="saveField('email')"
                            @keyup.esc="cancelEdit"
                          />
                        </div>

                        <transition name="field-message">
                          <p v-if="fieldErrors.currentPassword" class="field-error">{{ fieldErrors.currentPassword }}</p>
                          <p v-else-if="fieldFocused.currentPassword" class="field-hint">{{ currentPasswordHint }}</p>
                          <p v-else-if="!isEmailChangeAllowed" class="field-hint">{{ emailVerificationHint }}</p>
                        </transition>
                      </div>

                      <div class="edit-actions">
                        <button
                          type="button"
                          class="action-btn save-btn"
                          :disabled="isSaving || !isEmailChangeAllowed || Boolean(fieldErrors.email) || Boolean(fieldErrors.currentPassword) || !editDraft.email.trim() || !editDraft.currentPassword.trim()"
                          @click="saveField('email')"
                        >
                          {{ i18n.t('profile.save') }}
                        </button>
                        <button type="button" class="action-btn cancel-btn" :disabled="isSaving" @click="cancelEdit">
                          {{ i18n.t('buttons.cancel') }}
                        </button>
                      </div>

                    </div>
                  </Transition>
                </div>

                <div v-if="activeEditField !== 'email'" class="setting-actions">
                  <button
                    type="button"
                    class="icon-btn"
                    :aria-label="isEmailVisible ? i18n.t('profile.aria.hide_email') : i18n.t('profile.aria.show_email')"
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

                    <button type="button" class="icon-btn" :aria-label="i18n.t('profile.aria.edit_email')" @click="startEdit('email')">
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
                    <p class="setting-label">{{ i18n.t('profile.label.password') }}</p>
                  </div>

                  <Transition name="setting-swap" mode="out-in">
                    <div v-if="activeEditField !== 'password'" key="password-display" class="setting-display">
                      <p class="setting-value sensitive">{{ displayedPassword }}</p>
                      <p class="setting-hint">{{ i18n.t('profile.hint.password_strength') }}</p>
                    </div>

                    <div v-else key="password-edit" class="setting-edit password-edit">
                      <div class="field-stack">
                        <label class="input-label" for="current-password">{{ i18n.t('profile.label.enter_current_password') }}</label>
                        <div class="password-input-wrapper">
                          <input
                            id="current-password"
                            v-model="editDraft.currentPassword"
                            type="password"
                            class="setting-input"
                            autocomplete="current-password"
                            :aria-invalid="Boolean(fieldErrors.currentPassword)"
                            @focus="handleFieldFocus('currentPassword')"
                            @blur="handleFieldBlur('currentPassword', validateCurrentPassword, editDraft.currentPassword)"
                            @input="handleFieldInput('currentPassword', validateCurrentPassword, editDraft.currentPassword)"
                          />
                        </div>

                        <transition name="field-message">
                          <p v-if="fieldErrors.currentPassword" class="field-error">{{ fieldErrors.currentPassword }}</p>
                          <p v-else-if="fieldFocused.currentPassword" class="field-hint">{{ currentPasswordHint }}</p>
                        </transition>
                      </div>

                      <div class="field-stack">
                        <label class="input-label" for="new-password">{{ i18n.t('profile.new_password') }}</label>
                        <div class="password-input-wrapper">
                          <input
                            id="new-password"
                            v-model="editDraft.newPassword"
                            type="password"
                            class="setting-input"
                            autocomplete="new-password"
                            :aria-invalid="Boolean(fieldErrors.newPassword)"
                            @focus="handleFieldFocus('newPassword')"
                            @blur="handleFieldBlur('newPassword', validateNewPassword, editDraft.newPassword)"
                            @input="handleFieldInput('newPassword', validateNewPassword, editDraft.newPassword)"
                          />
                        </div>

                        <transition name="field-message">
                          <p v-if="fieldErrors.newPassword" class="field-error">{{ fieldErrors.newPassword }}</p>
                          <p v-else-if="fieldFocused.newPassword" class="field-hint">{{ newPasswordHint }}</p>
                        </transition>
                      </div>

                      <div class="field-stack">
                        <label class="input-label" for="confirm-password">{{ i18n.t('profile.confirm_new_password') }}</label>
                        <div class="password-input-wrapper">
                          <input
                            id="confirm-password"
                            v-model="editDraft.confirmPassword"
                            type="password"
                            class="setting-input"
                            autocomplete="new-password"
                            :aria-invalid="Boolean(fieldErrors.confirmPassword)"
                            @focus="handleFieldFocus('confirmPassword')"
                            @blur="handleFieldBlur('confirmPassword', validateConfirmPassword, editDraft.confirmPassword)"
                            @input="handleFieldInput('confirmPassword', validateConfirmPassword, editDraft.confirmPassword)"
                            @keyup.enter="saveField('password')"
                            @keyup.esc="cancelEdit"
                          />
                        </div>

                        <transition name="field-message">
                          <p v-if="fieldErrors.confirmPassword" class="field-error">{{ fieldErrors.confirmPassword }}</p>
                          <p v-else-if="fieldFocused.confirmPassword" class="field-hint">{{ confirmPasswordHint }}</p>
                        </transition>
                      </div>

                      <div class="edit-actions">
                        <button
                          type="button"
                          class="action-btn save-btn"
                          :disabled="isSaving || Boolean(fieldErrors.currentPassword) || Boolean(fieldErrors.newPassword) || Boolean(fieldErrors.confirmPassword) || !editDraft.currentPassword.trim() || !editDraft.newPassword || !editDraft.confirmPassword"
                          @click="saveField('password')"
                        >
                          {{ i18n.t('profile.save') }}
                        </button>
                        <button type="button" class="action-btn cancel-btn" :disabled="isSaving" @click="cancelEdit">
                          {{ i18n.t('buttons.cancel') }}
                        </button>
                      </div>
                    </div>
                  </Transition>
                </div>

                <div v-if="activeEditField !== 'password'" class="setting-actions">
                    <button type="button" class="icon-btn" :aria-label="i18n.t('profile.aria.edit_password')" @click="startEdit('password')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 20h9"/>
                      <path d= "M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>
                    </svg>
                  </button>
                </div>
              </div>

              <div class="setting-row danger-row" :class="{ editing: activeEditField === 'delete-account' }">
                <div class="setting-main">
                  <div class="setting-copy">
                    <p class="setting-label">{{ i18n.t('profile.label.delete_account') }}</p>
                  </div>

                  <Transition name="setting-swap" mode="out-in">
                    <div v-if="activeEditField !== 'delete-account'" key="delete-account-display" class="setting-display">
                      <p class="setting-value">{{ i18n.t('profile.delete_account_notice') }}</p>
                      <p class="setting-hint">{{ i18n.t('profile.delete_account_hint') }}</p>
                    </div>

                    <div v-else key="delete-account-edit" class="setting-edit danger-edit">
                      <div class="password-input-wrapper">
                        <input
                          v-model="editDraft.deleteAccountPassword"
                          type="password"
                          class="setting-input"
                          autocomplete="current-password"
                          :aria-invalid="Boolean(fieldErrors.deleteAccountPassword)"
                          @focus="handleFieldFocus('deleteAccountPassword')"
                          @blur="handleFieldBlur('deleteAccountPassword', validateDeleteAccountPassword, editDraft.deleteAccountPassword)"
                          @input="handleFieldInput('deleteAccountPassword', validateDeleteAccountPassword, editDraft.deleteAccountPassword)"
                          @keyup.enter="saveField('delete-account')"
                          @keyup.esc="cancelEdit"
                        />
                      </div>

                      <transition name="field-message">
                        <p v-if="fieldErrors.deleteAccountPassword" class="field-error">{{ fieldErrors.deleteAccountPassword }}</p>
                        <p v-else-if="fieldFocused.deleteAccountPassword" class="field-hint">{{ deleteAccountHint }}</p>
                      </transition>

                      <div class="edit-actions">
                        <button
                          type="button"
                          class="action-btn danger-btn"
                          :disabled="isSaving || Boolean(fieldErrors.deleteAccountPassword) || !editDraft.deleteAccountPassword.trim()"
                          @click="saveField('delete-account')"
                        >
                          {{ i18n.t('profile.delete_account_action') }}
                        </button>
                        <button type="button" class="action-btn cancel-btn" :disabled="isSaving" @click="cancelEdit">
                          {{ i18n.t('buttons.cancel') }}
                        </button>
                      </div>
                    </div>
                  </Transition>
                </div>

                <div v-if="activeEditField !== 'delete-account'" class="setting-actions">
                  <button type="button" class="action-btn danger-btn compact-danger-btn" @click="startEdit('delete-account')">
                    {{ i18n.t('buttons.delete') }}
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
        <span class="footer-text">{{ i18n.t('footer.brand') }}</span>
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
  --hover-bg: #f5f5f7;
  --accent-color: #7c3aed;
  --accent-hover: #6d28d9;
  --accent-soft: rgba(124, 58, 237, 0.1);
  --input-bg: #ffffff;
  --status-info-bg: rgba(124, 58, 237, 0.08);
  --status-info-text: #5b21b6;
  --status-success-bg: rgba(22, 163, 74, 0.1);
  --status-success-text: #18753a;
  --error-color: #c53636;
  --danger-bg: #c53636;
  --danger-hover: #a72d2d;
  --danger-soft: rgba(197, 54, 54, 0.12);
}

.profile-page.dark {
  --bg-primary: #1b1d21;
  --bg-secondary: #25282e;
  --surface-muted: #25282e;
  --text-primary: #f5f5f7;
  --text-secondary: #a6aab3;
  --border-color: #545a65;
  --hover-bg: #2f333b;
  --accent-color: #a78bfa;
  --accent-hover: #8b5cf6;
  --accent-soft: rgba(167, 139, 250, 0.14);
  --input-bg: #2a2f37;
  --status-info-bg: rgba(167, 139, 250, 0.14);
  --status-info-text: #e9d5ff;
  --status-success-bg: rgba(34, 197, 94, 0.14);
  --status-success-text: #9ae6b4;
  --error-color: #ff8b8b;
  --danger-bg: #d44f4f;
  --danger-hover: #c64242;
  --danger-soft: rgba(212, 79, 79, 0.2);
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
  display: flex;
  flex-direction: column;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1.5rem;
}

.avatar-top {
  display: flex;
  justify-content: center;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
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
  grid-template-columns: 1fr;
}

.setting-row.editing .setting-actions {
  display: none;
}

.setting-main {
  display: grid;
  grid-template-columns: 170px minmax(0, 1fr);
  gap: 1.25rem;
  min-width: 0;
}

.setting-copy {
  margin: 0;
}

.setting-label {
  margin: 0.2rem 0 0;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--text-secondary);
}

.setting-row.editing .setting-label {
  color: var(--accent-color);
}

.danger-row.editing .setting-label {
  color: var(--danger-bg);
}

.setting-hint {
  margin: 0.1rem 0 0;
  font-size: 0.82rem;
  color: var(--text-secondary);
}

.setting-display {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.setting-value {
  margin: 0.15rem 0 0;
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
  border-radius: 8px;
  background: transparent;
  width: 100%;
}

.password-edit {
  gap: 0.9rem;
}

.danger-edit {
  border: 1px solid var(--danger-soft);
}

.field-stack {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.input-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.setting-input {
  width: 100%;
  padding: 0.45rem 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--input-bg);
  color: var(--text-primary);
  font-size: 0.9rem;
  font-family: inherit;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.setting-input::placeholder {
  color: var(--text-secondary);
  opacity: 0.75;
}

.setting-input[aria-invalid='true'] {
  border-color: var(--error-color);
}

.field-hint,
.field-error {
  margin: 0;
  font-size: 0.8rem;
  line-height: 1.45;
}

.field-hint {
  color: var(--text-secondary);
}

.field-error {
  color: var(--error-color);
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

.password-input-wrapper {
  position: relative;
}

.icon-btn:hover {
  color: var(--text-primary);
  background: var(--accent-soft);
}

.icon-btn:focus-visible,
.action-btn:focus-visible {
  outline: 2px solid var(--accent-color);
  outline-offset: 2px;
}

.icon-btn svg {
  width: 17px;
  height: 17px;
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

.save-btn {
  background: var(--accent-color);
  color: #ffffff;
}

.save-btn,
.cancel-btn {
  border-radius: 999px;
}

.cancel-btn {
  background: transparent;
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.cancel-btn:hover {
  background: var(--hover-bg);
}

.save-btn:hover {
  background: var(--accent-hover);
}

.danger-btn {
  background: var(--danger-bg);
  color: #ffffff;
}

.danger-btn:hover {
  background: var(--danger-hover);
}

.compact-danger-btn {
  border-radius: 999px;
  padding-inline: 0.9rem;
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
    padding: 1.25rem;
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

  .setting-main {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }

  .setting-actions {
    justify-content: flex-end;
  }

}
</style>
