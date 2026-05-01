import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

export function useProfileApi() {
  const router = useRouter()
  const authStore = useAuthStore()
  const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').trim().replace(/\/$/, '')

  async function request(path, options = {}) {
    const response = await fetch(apiBaseUrl ? `${apiBaseUrl}${path}` : `/api${path}`, {
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        ...(options.body ? { 'Content-Type': 'application/json' } : {}),
        ...(options.headers || {}),
      },
      ...options,
    })

    const data = await response.json().catch(() => null)

    if (response.status === 401) {
      authStore.clearUser()
      await router.push('/login')
      return null
    }

    return { response, data }
  }

  return {
    request,
  }
}
