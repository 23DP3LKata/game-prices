<script setup>
import { computed, ref, provide } from 'vue'
import AppHeader from '../components/AppHeader.vue'
import { useThemePreference } from '../composables/useThemePreference'
import { useI18nStore } from '../stores/i18n'

const selectedLanguage = ref('LV')
const selectedTheme = useThemePreference()
const i18n = useI18nStore()

provide('theme', selectedTheme)

const sections = computed(() => [
  { id: 'what-is', title: i18n.t('about.sections.what_is') },
  { id: 'why', title: i18n.t('about.sections.why') },
  { id: 'data', title: i18n.t('about.sections.data') },
  { id: 'safety', title: i18n.t('about.sections.safety') },
  { id: 'audience', title: i18n.t('about.sections.audience') },
  { id: 'features', title: i18n.t('about.sections.features') },
  { id: 'history', title: i18n.t('about.sections.history') },
  { id: 'sync', title: i18n.t('about.sections.sync') },
  { id: 'mission', title: i18n.t('about.sections.mission') },
])
</script>

<template>
  <div class="about-page" :class="selectedTheme">
    <AppHeader
      v-model:selectedTheme="selectedTheme"
      v-model:selectedLanguage="selectedLanguage"
      activePage="about"
    />

    <main class="main-content">
      <div class="content-layout">
        <article class="about-article">
          <header class="section-header">
            <h1>{{ i18n.t('about.title') }}</h1>
            <p class="section-subtitle">{{ i18n.t('about.subtitle') }}</p>
            <div class="project-note">
              <p>{{ i18n.t('about.note') }}</p>
            </div>
          </header>

          <div class="article-content">
            <section id="what-is">
              <h2>{{ i18n.t('about.what_is.title') }}</h2>
              <p>{{ i18n.t('about.what_is.body') }}</p>
            </section>

            <section id="why">
              <h2>{{ i18n.t('about.why.title') }}</h2>
              <p>{{ i18n.t('about.why.body') }}</p>
            </section>

            <section id="data">
              <h2>{{ i18n.t('about.data.title') }}</h2>
              <p>{{ i18n.t('about.data.body') }}</p>
            </section>

            <section id="safety">
              <h2>{{ i18n.t('about.safety.title') }}</h2>
              <p>{{ i18n.t('about.safety.body') }}</p>
            </section>

            <section id="audience">
              <h2>{{ i18n.t('about.audience.title') }}</h2>
              <p>{{ i18n.t('about.audience.body') }}</p>
            </section>

            <section id="features">
              <h2>{{ i18n.t('about.features.title') }}</h2>
              <p>{{ i18n.t('about.features.body') }}</p>
            </section>

            <section id="history">
              <h2>{{ i18n.t('about.history.title') }}</h2>
              <p>{{ i18n.t('about.history.body') }}</p>
            </section>

            <section id="sync">
              <h2>{{ i18n.t('about.sync.title') }}</h2>
              <p>{{ i18n.t('about.sync.body') }}</p>
            </section>

            <section id="mission">
              <h2>{{ i18n.t('about.mission.title') }}</h2>
              <p>{{ i18n.t('about.mission.body') }}</p>
            </section>
          </div>
        </article>

        <aside class="sidebar desktop-sidebar">
          <div class="sidebar-content">
            <h3>{{ i18n.t('about.sidebar_title') }}</h3>
            <nav class="sidebar-nav">
              <ul>
                <li v-for="section in sections" :key="section.id">
                  <a :href="`#${section.id}`">{{ section.title }}</a>
                </li>
              </ul>
            </nav>
          </div>
        </aside>
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
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.about-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  transition: background-color 0.3s ease, color 0.3s ease;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
}

.about-page.light {
  --bg-primary: #ffffff;
  --bg-secondary: #f5f5f7;
  --text-primary: #1d1d1f;
  --text-secondary: #86868b;
  --border-color: #d2d2d7;
  --hover-bg: #f5f5f7;
  --accent-color: #0071e3;
}

.about-page.dark {
  --bg-primary: #1b1d21;
  --bg-secondary: #25282e;
  --text-primary: #f5f5f7;
  --text-secondary: #a6aab3;
  --border-color: #545a65;
  --hover-bg: #2f333b;
  --accent-color: #2997ff;
}

.about-page {
  background: var(--bg-primary);
  color: var(--text-primary);
}

/* main */
.main-content {
  flex: 1;
  padding: 3rem 2rem;
}

.content-layout {
  max-width: 1280px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 4rem;
}

.about-article {
  max-width: 720px;
}

.section-header {
  margin-bottom: 3rem;
}

.section-header h1 {
  font-size: 3rem;
  font-weight: 600;
  line-height: 1.1;
  letter-spacing: -1px;
  margin-bottom: 1rem;
  color: var(--text-primary);
}

.section-subtitle {
  font-size: 1.25rem;
  color: var(--text-secondary);
  line-height: 1.5;
  font-weight: 400;
}

.project-note {
  margin-top: 1.5rem;
  padding: 1rem 1.25rem;
  border-left: 3px solid var(--accent-color);
  background: color-mix(in srgb, var(--bg-secondary) 65%, transparent);
  border-radius: 0 12px 12px 0;
}

.project-note p {
  font-size: 0.975rem;
  color: var(--text-secondary);
  line-height: 1.7;
}

.article-content section {
  margin-bottom: 3rem;
  scroll-margin-top: 80px;
}

.article-content h2 {
  font-size: 1.75rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--text-primary);
  line-height: 1.3;
}

.article-content p {
  font-size: 1.0625rem;
  color: var(--text-secondary);
  line-height: 1.7;
  font-weight: 400;
}

.article-content a {
  color: var(--accent-color);
  text-decoration: none;
  border-bottom: 1px solid transparent;
  transition: border-color 0.2s ease, color 0.2s ease;
}

.article-content a:hover {
  border-color: currentColor;
}

/* sidebar */
.sidebar {
  position: sticky;
  top: 80px;
  align-self: start;
  height: fit-content;
}

.sidebar-content {
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1.5rem;
}

.sidebar-content h3 {
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--text-primary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.sidebar-nav ul {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.sidebar-nav a {
  color: var(--text-secondary);
  text-decoration: none;
  font-size: 0.875rem;
  transition: color 0.2s ease;
  display: block;
  padding: 0.25rem 0;
}

.sidebar-nav a:hover {
  color: var(--text-primary);
}

/* footer */
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

/* responsive */
@media (max-width: 1024px) {
  .content-layout {
    grid-template-columns: 1fr;
    gap: 2rem;
  }

  .desktop-sidebar {
    display: none;
  }
}

@media (max-width: 980px) {
  .section-header h1 {
    font-size: 2.5rem;
  }

  .section-subtitle {
    font-size: 1.125rem;
  }

  .article-content h2 {
    font-size: 1.5rem;
  }
}

@media (max-width: 640px) {
  .main-content {
    padding: 2rem 1.5rem;
  }

  .section-header h1 {
    font-size: 2rem;
  }

  .section-header {
    margin-bottom: 2rem;
  }

  .project-note {
    margin-top: 1rem;
    padding: 0.875rem 1rem;
  }

  .article-content section {
    margin-bottom: 2rem;
  }

  .article-content h2 {
    font-size: 1.25rem;
  }

  .article-content p {
    font-size: 1rem;
  }
}
</style>