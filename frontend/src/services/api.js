import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import router from '@/router'

// FIXED: Correct base URL with /api/v1 prefix
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
      console.log('✅ Auth token added to request')
    } else {
      console.log('❌ No auth token found in localStorage')
    }

    // Debug: Log the final URL being called
    console.log('API Request:', {
      method: config.method?.toUpperCase(),
      url: config.url,
      baseURL: config.baseURL,
      fullURL: config.baseURL + '/' + config.url.replace(/^\/+/, ''), // Remove leading slashes
      hasAuth: !!token
    })

    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle auth errors
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    console.error('API Error:', {
      status: error.response?.status,
      statusText: error.response?.statusText,
      url: error.config?.url,
      method: error.config?.method,
      data: error.response?.data
    })

    if (error.response?.status === 401) {
      console.log('🔒 Authentication failed - redirecting to login')
      // Token expired or invalid
      const authStore = useAuthStore()
      authStore.logout()
      router.push('/login')
    } else if (error.response?.status === 403) {
      // Access denied
      console.error('Access denied:', error.response.data)
    } else if (error.response?.status === 422) {
      // Validation errors
      console.error('Validation errors:', error.response.data)
    } else if (error.response?.status >= 500) {
      // Server errors
      console.error('Server error:', error.response.data)
    }

    return Promise.reject(error)
  }
)

export default api
