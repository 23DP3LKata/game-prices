<script setup>
import { ref, provide } from 'vue'
import AppHeader from '../components/AppHeader.vue'

const selectedCurrency = ref('EUR')
const selectedLanguage = ref('ENG')
const selectedTheme = ref('light')

provide('theme', selectedTheme)
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
          <h1>Profile</h1>
        </header>

        <div class="profile-card">
          <div class="avatar-section">
            <div class="avatar-placeholder">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </div>
            <div class="user-info">
              <h2>User Name</h2>
              <p>user@example.com</p>
            </div>
          </div>
        </div>

        <div class="profile-section">
          <h3>Account Settings</h3>
          <p class="section-note">Profile management features will be available soon.</p>
        </div>
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
  --text-primary: #1d1d1f;
  --text-secondary: #86868b;
  --border-color: #d2d2d7;
  --hover-bg: #f5f5f7;
  --accent-color: #0071e3;
}

.profile-page.dark {
  --bg-primary: #000000;
  --bg-secondary: #1d1d1f;
  --text-primary: #f5f5f7;
  --text-secondary: #86868b;
  --border-color: #424245;
  --hover-bg: #1d1d1f;
  --accent-color: #2997ff;
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
  max-width: 680px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 3rem;
}

.page-header h1 {
  text-align: center;
  font-size: 3rem;
  font-weight: 600;
  line-height: 1.1;
  letter-spacing: -1px;
  color: var(--text-primary);
}

.profile-card {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
}

.avatar-section {
  display: flex;
  align-items: center;
  gap: 1.5rem;
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
  flex-shrink: 0;
}

.avatar-placeholder svg {
  width: 36px;
  height: 36px;
}

.user-info h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
}

.user-info p {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.profile-section {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1.5rem 2rem;
}

.profile-section h3 {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.section-note {
  font-size: 0.875rem;
  color: var(--text-secondary);
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

  .avatar-section {
    flex-direction: column;
    text-align: center;
  }
}
</style>
