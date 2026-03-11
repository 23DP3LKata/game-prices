import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../views/HomePage.vue'
import AboutUs from '../views/AboutUs.vue'
import Games from '../views/Games.vue'
import GamePage from '../views/GamePage.vue'
import ProfilePage from '../views/ProfilePage.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomePage
    },
    {
      path: '/about',
      name: 'about',
      component: AboutUs
    },
    {
      path: '/games',
      name: 'games',
      component: Games
    },
    {
      path: '/game/:id',
      name: 'game',
      component: GamePage
    },
    {
      path: '/profile',
      name: 'profile',
      component: ProfilePage
    }
  ],
})

export default router
