import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'
import './styles/accessibility.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
await useAuthStore(pinia).bootstrap()
app.use(router)

app.mount('#app')

if ('serviceWorker' in navigator) {
	window.addEventListener('load', () => {
		const isLocalPwaHost = ['localhost', '127.0.0.1', '[::1]'].includes(window.location.hostname)
		const shouldRegisterServiceWorker = Boolean(import.meta.env?.PROD || isLocalPwaHost)

		if (!shouldRegisterServiceWorker) {
			return
		}

		navigator.serviceWorker.register('/sw.js').then(() => {
			console.log('Service worker registered');
		}).catch((err) => {
			console.warn('Service worker registration failed:', err);
		});
	});
}
