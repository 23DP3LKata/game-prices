<script setup>
import { ref, provide } from 'vue'
import AppHeader from '../components/AppHeader.vue'
import { useThemePreference } from '../composables/useThemePreference'

const selectedCurrency = ref('USD')
const selectedLanguage = ref('ENG')
const selectedTheme = useThemePreference()

provide('theme', selectedTheme)

const sections = [
  { id: 'what-is', title: 'What is GamePrices?' },
  { id: 'why', title: 'Why was it created?' },
  { id: 'data', title: 'Data collection' },
  { id: 'reliability', title: 'Reliability' },
  { id: 'audience', title: 'Audience' },
  { id: 'features', title: 'Registered features' },
  { id: 'history', title: 'Price history' },
  { id: 'sync', title: 'Data freshness' },
  { id: 'mission', title: 'Mission' }
]
</script>

<template>
  <div class="about-page" :class="selectedTheme">
    <AppHeader
      v-model:selectedTheme="selectedTheme"
      v-model:selectedCurrency="selectedCurrency"
      v-model:selectedLanguage="selectedLanguage"
      activePage="about"
    />

    <main class="main-content">
      <div class="content-layout">
        <article class="about-article">
          <header class="section-header">
            <h1>About Game Prices</h1>
            <p class="section-subtitle">Discover how the platform works behind the scenes and who it is built for.</p>
          </header>

          <div class="article-content">
            <section id="what-is">
              <h2>What is GamePrices?</h2>
              <p>GamePrices is a data-driven platform that compares game prices across multiple digital stores, providing real-time information, historical analytics, and automated deal tracking.</p>
            </section>

            <section id="why">
              <h2>Why was GamePrices created?</h2>
              <p>The platform was built to solve a common problem: players waste time checking different stores manually, comparing prices, and searching for real discounts.</p>
            </section>

            <section id="data">
              <h2>How does GamePrices collect pricing data?</h2>
              <p>GamePrices integrates with public APIs from Steam, Epic Games, GG.deals, IsThereAnyDeal, and other sources to automatically gather and update accurate pricing information.</p>
            </section>

            <section id="reliability">
              <h2>What makes GamePrices reliable?</h2>
              <p>The system uses a structured ER data model, automated synchronization services, and detailed validation to ensure price accuracy, data consistency, and stable performance.</p>
            </section>

            <section id="audience">
              <h2>Who is GamePrices for?</h2>
              <p>GamePrices is designed for all types of users - guests who want quick price checks, registered users who track wishlists and receive alerts, and administrators who maintain data quality.</p>
            </section>

            <section id="features">
              <h2>What features do registered users get?</h2>
              <p>Registered users can save games to a wishlist, receive automatic notifications about price drops, customize their profile, and report issues directly to administrators.</p>
            </section>

            <section id="history">
              <h2>Does GamePrices show price history?</h2>
              <p>Yes. The platform provides clear visual graphs showing historical price changes, helping users analyze trends and buy at the best moment.</p>
            </section>

            <section id="sync">
              <h2>How does GamePrices ensure data stays up to date?</h2>
              <p>A built-in DataSyncService regularly pulls fresh data from external APIs, updates local records, and logs errors to maintain system accuracy and reliability.</p>
            </section>

            <section id="mission">
              <h2>What is your mission?</h2>
              <p>Our mission is to make the game market transparent, efficient, and easy to understand, helping every player save time, save money, and make smarter purchasing decisions.</p>
            </section>
          </div>
        </article>

        <aside class="sidebar desktop-sidebar">
          <div class="sidebar-content">
            <h3>On this page</h3>
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
        <span class="footer-text">&copy; 2025 Game Prices</span>
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