import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../views/HomePage.vue'
import AboutUs from '../views/AboutUs.vue'
import Games from '../views/Games.vue'
import GamePage from '../views/GamePage.vue'
import ProfilePage from '../views/ProfilePage.vue'
import LoginPage from '../views/LoginPage.vue'
import RegisterPage from '../views/RegisterPage.vue'
import AdminPage from '../views/AdminPage.vue'
import ResetPasswordPage from '../views/ResetPasswordPage.vue'

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
      path: '/game/:slug',
      name: 'game',
      component: GamePage
    },
    {
      path: '/profile',
      name: 'profile',
      component: ProfilePage
    },
    {
      path: '/login',
      name: 'login',
      component: LoginPage
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterPage
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: ResetPasswordPage
    },
    {
      path: '/admin',
      name: 'admin',
      component: AdminPage
    }
  ],
})

export default router
