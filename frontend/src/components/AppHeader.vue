<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const props = defineProps({
  activePage: { type: String, default: '' },
  selectedTheme: { type: String, default: 'light' },
  selectedCurrency: { type: String, default: 'EUR' },
  selectedLanguage: { type: String, default: 'ENG' }
})

const emit = defineEmits([
  'update:selectedTheme',
  'update:selectedCurrency',
  'update:selectedLanguage'
])

const router = useRouter()

const authStore = useAuthStore()
const { isLoggedIn } = storeToRefs(authStore)

const isMobileMenuOpen = ref(false)
const isProfileMenuOpen = ref(false)

const profileWrapperRef = ref(null)

function toggleProfileMenu() {
  isProfileMenuOpen.value = !isProfileMenuOpen.value
}

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

function closeMobileMenu() {
  isMobileMenuOpen.value = false
}

function goToHome() {
  router.push('/')
  closeMobileMenu()
  isProfileMenuOpen.value = false
}

function goToAbout() {
  router.push('/about')
  closeMobileMenu()
  isProfileMenuOpen.value = false
}

function goToGames() {
  router.push('/games')
  closeMobileMenu()
  isProfileMenuOpen.value = false
}

function goToProfile() {
  router.push('/profile')
  closeMobileMenu()
  isProfileMenuOpen.value = false
}

function goToLogin() {
  router.push('/login')
  closeMobileMenu()
  isProfileMenuOpen.value = false
}

function goToRegister() {
  router.push('/register')
  closeMobileMenu()
  isProfileMenuOpen.value = false
}

function handleLogout() {
  authStore.clearUser()
  isProfileMenuOpen.value = false
  closeMobileMenu()
  router.push('/')
}

function handleAuthMenuAction() {
  if (isLoggedIn.value) {
    handleLogout()
    return
  }

  goToLogin()
}

function changeCurrency(c) {
  emit('update:selectedCurrency', c)
}

function changeLanguage(l) {
  emit('update:selectedLanguage', l)
}

function toggleTheme() {
  emit('update:selectedTheme', props.selectedTheme === 'light' ? 'dark' : 'light')
}

function handleClickOutside(e) {
  if (profileWrapperRef.value && !profileWrapperRef.value.contains(e.target)) {
    isProfileMenuOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
</script>

<template>
  <header class="header" :data-theme="selectedTheme">
    <div class="header-container">
      <div class="header-spacer"></div>

      <div class="center-content">
        <div class="logo" @click="goToHome">
          <svg class="logo-icon" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
            <path fill="currentColor" d="M64 24A40 40 0 1 0 64 104A40 40 0 1 0 64 24ZM44 52L84 52A12 12 0 0 1 84 76L44 76A12 12 0 0 1 44 52ZM52 60A4 4 0 1 0 52 68A4 4 0 1 0 52 60ZM76 60A4 4 0 1 0 76 68A4 4 0 1 0 76 60Z"/>
          </svg>
          <span class="logo-text">Game Prices</span>
        </div>

        <nav class="nav-menu desktop-nav">
          <button class="nav-btn" :class="{ active: activePage === 'games' }" @click="goToGames">Games</button>
          <button class="nav-btn" :class="{ active: activePage === 'about' }" @click="goToAbout">About us</button>
        </nav>
      </div>

      <!-- Auth buttons + Profile icon (desktop) -->
      <div class="header-right desktop-profile">
        <template v-if="!isLoggedIn">
          <button class="auth-btn login-btn" @click="goToLogin">Log In</button>
          <button class="auth-btn signup-btn" @click="goToRegister">Sign Up</button>
        </template>

        <div class="profile-wrapper" ref="profileWrapperRef">
          <button class="profile-btn" @click="toggleProfileMenu" aria-label="Profile menu">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </button>

          <div class="profile-dropdown" :class="{ 'profile-dropdown-open': isProfileMenuOpen }">
          <template v-if="isLoggedIn">
            <button class="profile-menu-item" @click="goToProfile">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              <span>Profile</span>
            </button>

            <div class="profile-menu-divider"></div>
          </template>

          <!-- Language -->
          <div class="profile-menu-setting">
            <div class="setting-label">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
              </svg>
              <span>Language</span>
            </div>
            <div class="setting-options">
              <button
                v-for="lang in ['ENG', 'LV']"
                :key="lang"
                class="setting-btn"
                :class="{ active: selectedLanguage === lang }"
                @click="changeLanguage(lang)"
              >{{ lang }}</button>
            </div>
          </div>

          <!-- Currency -->
          <div class="profile-menu-setting">
            <div class="setting-label">
              <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
              <span>Currency</span>
            </div>
            <div class="setting-options">
              <button
                v-for="currency in ['EUR', 'USD', 'GBP']"
                :key="currency"
                class="setting-btn"
                :class="{ active: selectedCurrency === currency }"
                @click="changeCurrency(currency)"
              >{{ currency }}</button>
            </div>
          </div>

          <!-- Dark theme -->
          <button class="profile-menu-item" @click="toggleTheme">
            <svg v-if="selectedTheme === 'light'" class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
            <svg v-else class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="5"/>
              <line x1="12" y1="1" x2="12" y2="3"/>
              <line x1="12" y1="21" x2="12" y2="23"/>
              <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
              <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
              <line x1="1" y1="12" x2="3" y2="12"/>
              <line x1="21" y1="12" x2="23" y2="12"/>
              <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
              <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <span>Dark theme</span>
            <span class="theme-status">{{ selectedTheme === 'dark' ? 'On' : 'Off' }}</span>
          </button>

          <div class="profile-menu-divider"></div>

          <button
            class="profile-menu-item"
            :class="{ 'logout-item': isLoggedIn }"
            @click="handleAuthMenuAction"
          >
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>{{ isLoggedIn ? 'Log out' : 'Log in' }}</span>
          </button>
          </div>
        </div>
      </div>

      <!-- Mobile menu button -->
      <button class="mobile-menu-btn" @click="toggleMobileMenu" :aria-label="isMobileMenuOpen ? 'Close menu' : 'Open menu'">
        <svg v-if="!isMobileMenuOpen" class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="3" y1="6" x2="21" y2="6"/>
          <line x1="3" y1="12" x2="21" y2="12"/>
          <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
        <svg v-else class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    <!-- Mobile menu -->
    <div class="mobile-menu" :class="{ 'mobile-menu-open': isMobileMenuOpen }">
      <nav class="mobile-nav">
        <button class="mobile-nav-btn" :class="{ active: activePage === 'games' }" @click="goToGames">Games</button>
        <button class="mobile-nav-btn" :class="{ active: activePage === 'about' }" @click="goToAbout">About us</button>

        <div class="mobile-divider"></div>

        <template v-if="!isLoggedIn">
          <button class="mobile-auth-btn mobile-login-btn" @click="goToLogin">Log In</button>
          <button class="mobile-auth-btn mobile-signup-btn" @click="goToRegister">Sign Up</button>
        </template>

        <div class="mobile-divider"></div>

        <button v-if="isLoggedIn" class="mobile-nav-btn" @click="goToProfile">
          <span>Profile</span>
          <svg class="mobile-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        </button>

        <div class="mobile-setting">
          <span class="mobile-setting-label">Language</span>
          <div class="mobile-setting-options">
            <button
              v-for="lang in ['ENG', 'LV']"
              :key="lang"
              class="mobile-setting-btn"
              :class="{ active: selectedLanguage === lang }"
              @click="changeLanguage(lang)"
            >{{ lang }}</button>
          </div>
        </div>

        <div class="mobile-setting">
          <span class="mobile-setting-label">Currency</span>
          <div class="mobile-setting-options">
            <button
              v-for="currency in ['EUR', 'USD', 'GBP']"
              :key="currency"
              class="mobile-setting-btn"
              :class="{ active: selectedCurrency === currency }"
              @click="changeCurrency(currency)"
            >{{ currency }}</button>
          </div>
        </div>

        <button class="mobile-nav-btn" @click="toggleTheme">
          <span>Dark theme</span>
          <span class="theme-status-mobile">{{ selectedTheme === 'dark' ? 'On' : 'Off' }}</span>
        </button>

        <div class="mobile-divider"></div>

        <button
          class="mobile-nav-btn"
          :class="{ 'logout-mobile': isLoggedIn }"
          @click="handleAuthMenuAction"
        >
          <span>{{ isLoggedIn ? 'Log out' : 'Log in' }}</span>
          <svg class="mobile-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </button>
      </nav>
    </div>
  </header>
</template>

<style scoped>
.header {
  background: rgba(var(--bg-primary-rgb), 0.8);
  backdrop-filter: saturate(180%) blur(20px);
  -webkit-backdrop-filter: saturate(180%) blur(20px);
  border-bottom: 1px solid var(--border-color);
  padding: 0 1rem;
  position: sticky;
  top: 0;
  z-index: 100;
}

.header[data-theme="light"] {
  --bg-primary-rgb: 255, 255, 255;
}

.header[data-theme="dark"] {
  --bg-primary-rgb: 0, 0, 0;
}

.header-container {
  max-width: 1280px;
  margin: 0 auto;
  height: 48px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-spacer {
  width: 40px;
}

/* Center: Logo + Nav */
.center-content {
  display: flex;
  align-items: center;
  gap: 2rem;
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
}

.logo {
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: opacity 0.2s ease;
}

.logo:hover {
  opacity: 0.7;
}

.logo-icon {
  width: 22px;
  height: 22px;
  color: var(--text-primary);
}

.logo-text {
  font-size: 0.875rem;
  font-weight: 400;
  color: var(--text-primary);
  letter-spacing: 0;
}

/* Navigation */
.nav-menu {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.nav-btn {
  background: transparent;
  border: none;
  color: var(--text-primary);
  font-size: 0.875rem;
  font-weight: 400;
  cursor: pointer;
  transition: opacity 0.2s ease;
  padding: 0;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  white-space: nowrap;
}

.nav-btn:hover {
  opacity: 0.7;
}

.nav-btn.active {
  font-weight: 500;
}

/* Profile button & auth */
.header-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.auth-btn {
  border: none;
  padding: 0.375rem 0.875rem;
  border-radius: 6px;
  font-size: 0.8125rem;
  font-weight: 500;
  cursor: pointer;
  transition: opacity 0.2s ease;
  white-space: nowrap;
}

.signup-btn {
  background: var(--accent-color);
  color: #ffffff;
}

.signup-btn:hover {
  opacity: 0.85;
}

.login-btn {
  background: transparent;
  color: var(--text-primary);
}

.login-btn:hover {
  opacity: 0.7;
}

.profile-wrapper {
  position: relative;
}

.profile-btn {
  background: transparent;
  border: none;
  color: var(--text-primary);
  width: 40px;
  height: 40px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s ease;
  padding: 0;
  border-radius: 50%;
}

.profile-btn:hover {
  opacity: 0.7;
}

.profile-btn .icon {
  width: 22px;
  height: 22px;
}

/* Profile dropdown */
.profile-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  min-width: 260px;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.2s ease, visibility 0.2s ease;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
  padding: 0.5rem 0;
  z-index: 200;
}

.profile-dropdown-open {
  opacity: 1;
  visibility: visible;
}

.profile-menu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.625rem 1rem;
  background: transparent;
  border: none;
  color: var(--text-primary);
  font-size: 0.875rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
  text-align: left;
}

.profile-menu-item:hover {
  background: var(--hover-bg);
}

.menu-icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  color: var(--text-secondary);
}

.profile-menu-divider {
  height: 1px;
  background: var(--border-color);
  margin: 0.375rem 0;
}

.profile-menu-setting {
  padding: 0.5rem 1rem;
}

.setting-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.setting-options {
  display: flex;
  gap: 0.25rem;
  padding-left: 2rem;
}

.setting-btn {
  background: transparent;
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
  padding: 0.25rem 0.625rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.75rem;
  transition: all 0.2s ease;
  font-weight: 400;
}

.setting-btn:hover {
  border-color: var(--text-primary);
  color: var(--text-primary);
}

.setting-btn.active {
  background: var(--text-primary);
  color: var(--bg-primary);
  border-color: var(--text-primary);
}

.theme-status {
  margin-left: auto;
  font-size: 0.75rem;
  color: var(--text-secondary);
  padding: 0.125rem 0.5rem;
  background: var(--hover-bg);
  border-radius: 4px;
}

.logout-item {
  color: #ff3b30;
}

.logout-item .menu-icon {
  color: #ff3b30;
}

/* Mobile */
.mobile-menu-btn {
  display: none;
  background: transparent;
  border: none;
  color: var(--text-primary);
  width: 40px;
  height: 40px;
  cursor: pointer;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s ease;
  padding: 0;
}

.mobile-menu-btn:hover {
  opacity: 0.7;
}

.mobile-menu-btn .icon {
  width: 24px;
  height: 24px;
}

.mobile-menu {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
  background: var(--bg-primary);
  border-bottom: 1px solid transparent;
}

.mobile-menu-open {
  max-height: 600px;
  border-bottom-color: var(--border-color);
}

.mobile-nav {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.mobile-nav-btn {
  background: transparent;
  border: none;
  color: var(--text-primary);
  font-size: 1rem;
  font-weight: 400;
  cursor: pointer;
  padding: 0.75rem 1rem;
  text-align: left;
  transition: background-color 0.2s ease;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}

.mobile-nav-btn:hover {
  background: var(--hover-bg);
}

.mobile-nav-btn.active {
  font-weight: 500;
  background: var(--hover-bg);
}

.mobile-divider {
  height: 1px;
  background: var(--border-color);
  margin: 0.5rem 0;
}

.mobile-auth-btn {
  border: none;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: opacity 0.2s ease;
  text-align: center;
}

.mobile-signup-btn {
  background: var(--accent-color);
  color: #ffffff;
}

.mobile-signup-btn:hover {
  opacity: 0.85;
}

.mobile-login-btn {
  background: transparent;
  border: 1px solid var(--border-color);
  color: var(--text-primary);
}

.mobile-login-btn:hover {
  opacity: 0.7;
}

.mobile-item-icon {
  width: 20px;
  height: 20px;
  color: var(--text-secondary);
}

.mobile-setting {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
}

.mobile-setting-label {
  font-size: 0.875rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.mobile-setting-options {
  display: flex;
  gap: 0.25rem;
}

.mobile-setting-btn {
  background: transparent;
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
  padding: 0.375rem 0.75rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8125rem;
  transition: all 0.2s ease;
  font-weight: 400;
}

.mobile-setting-btn:hover {
  border-color: var(--text-primary);
  color: var(--text-primary);
}

.mobile-setting-btn.active {
  background: var(--text-primary);
  color: var(--bg-primary);
  border-color: var(--text-primary);
}

.theme-status-mobile {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  padding: 0.125rem 0.5rem;
  background: var(--hover-bg);
  border-radius: 4px;
  border: 1px solid var(--border-color);
}

.logout-mobile {
  color: #ff3b30;
}

.logout-mobile .mobile-item-icon {
  color: #ff3b30;
}

/* Responsive */
@media (max-width: 980px) {
  .desktop-nav,
  .desktop-profile {
    display: none;
  }

  .mobile-menu-btn {
    display: flex;
  }

  .header-spacer {
    display: none;
  }

  .center-content {
    position: static;
    transform: none;
    gap: 1rem;
  }
}
</style>
