import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))
  const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')

  const isLoggedIn = computed(() => user.value !== null)

  function setUser(userData) {
    user.value = userData
    localStorage.setItem('auth_user', JSON.stringify(userData))
  }

  function clearUser() {
    user.value = null
    localStorage.removeItem('auth_user')
  }

  async function bootstrap() {
    const cachedUser = user.value

    try {
      const response = await fetch(apiBaseUrl ? `${apiBaseUrl}/me` : '/api/me', {
        headers: {
          Accept: 'application/json',
        },
        credentials: 'include',
      })

      const data = await response.json().catch(() => null)

      if (!response.ok || !data?.user) {
        clearUser()
        return null
      }

      setUser(data.user)
      return data.user
    } catch {
      return cachedUser
    }
  }

  async function logout() {
    try {
      await fetch(apiBaseUrl ? `${apiBaseUrl}/logout` : '/api/logout', {
        method: 'POST',
        headers: {
          Accept: 'application/json',
        },
        credentials: 'include',
      })
    } catch {
      // Ignore network errors and clear local state below.
    } finally {
      clearUser()
    }
  }

  return { user, isLoggedIn, setUser, clearUser, bootstrap, logout }
})
