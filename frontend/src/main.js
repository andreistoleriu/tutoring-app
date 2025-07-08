// frontend/src/main.js
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './assets/main.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Initialize auth store after pinia is set up
import { useAuthStore } from './stores/auth'

// Initialize auth on app startup
const authStore = useAuthStore()
authStore.initAuth().then(() => {
  console.log('Auth initialized')
}).catch(err => {
  console.error('Auth initialization failed:', err)
})

app.mount('#app')
