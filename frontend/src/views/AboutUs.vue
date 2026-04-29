<script setup>
import { ref, provide } from 'vue'
import AppHeader from '../components/AppHeader.vue'
import { useThemePreference } from '../composables/useThemePreference'

const selectedLanguage = ref('ENG')
const selectedTheme = useThemePreference()

provide('theme', selectedTheme)

const sections = [
  { id: 'what-is', title: 'What is GamePrices?' },
  { id: 'why', title: 'Why this project exists' },
  { id: 'data', title: 'Where the data comes from' },
  { id: 'safety', title: 'Are the shops safe?' },
  { id: 'audience', title: 'Who it is for' },
  { id: 'features', title: 'What registered users get' },
  { id: 'history', title: 'Why price history matters' },
  { id: 'sync', title: 'How often data updates' },
  { id: 'mission', title: 'What we are learning here' }
]
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
            <h1>About Game Prices</h1>
            <p class="section-subtitle">A temporary educational project built to explore APIs, databases, background syncs, and the small product decisions that make data feel useful.</p>
            <div class="project-note">
              <p>
                This is not a commercial store. It is a learning project focused on building a realistic price-tracking experience while keeping the codebase simple, testable, and honest about what it does.
              </p>
            </div>
          </header>

          <div class="article-content">
            <section id="what-is">
              <h2>What is GamePrices?</h2>
              <p>GamePrices is a small price-comparison demo for games. It gathers store offers, shows current pricing, and keeps a record of how those prices change over time so the interface feels like a real product rather than a static mockup.</p>
            </section>

            <section id="why">
              <h2>Why does this project exist?</h2>
              <p>The main goal was learning. GamePrices was created as a temporary non-commercial project to practice working with external APIs, a relational database, scheduled updates, notifications, and the kind of data modeling that turns raw responses into something readable.</p>
            </section>

            <section id="data">
              <h2>Where does the data come from?</h2>
              <p>One of the main sources is <a href="https://isthereanydeal.com/" target="_blank" rel="noreferrer">IsThereAnyDeal</a>. Their API helps provide store and price information that can be normalized into a single place, which is ideal for this project because it lets me focus on integration logic instead of inventing fake data.</p>
            </section>

            <section id="safety">
              <h2>Are the shops safe?</h2>
              <p>The platform only highlights stores that are considered authorized or otherwise trustworthy sources. The point is not to sell keys directly, but to help users see where a game is offered and what the price looks like before they decide where to buy it.</p>
            </section>

            <section id="audience">
              <h2>Who is GamePrices for?</h2>
              <p>It is useful for visitors who just want a quick comparison, for registered users who like saving games and following price drops, and for anyone who wants to study how a small product can be built around live data without turning into a full e-commerce platform.</p>
            </section>

            <section id="features">
              <h2>What do registered users get?</h2>
              <p>Registered users can bookmark games, keep a wishlist, receive alerts when prices change, and use a more personalized view of the platform. That makes the app feel closer to a real service while still staying inside the scope of a learning project.</p>
            </section>

            <section id="history">
              <h2>Why keep price history?</h2>
              <p>Current prices are useful, but price history is what makes patterns visible. By keeping past values, the project can show whether a discount is new, repeated, or just part of a normal cycle.</p>
            </section>

            <section id="sync">
              <h2>How often does the data update?</h2>
              <p>The sync runs every 12 hours once the app is deployed on the server. Right now, during development, updates are triggered manually.</p>
            </section>

            <section id="mission">
              <h2>What are we trying to learn here?</h2>
              <p>The mission is to make the app useful as a study project: learn how APIs are shaped, how data is stored, how relations between games and stores are modeled, and how a simple interface can still communicate something credible.</p>
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