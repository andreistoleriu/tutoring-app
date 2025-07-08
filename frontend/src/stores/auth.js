// frontend/src/stores/auth.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // Computed
  const isAuthenticated = computed(() => !!token.value)
  const isTutor = computed(() => user.value?.user_type === 'tutor')
  const isStudent = computed(() => user.value?.user_type === 'student')
  const fullName = computed(() => {
    if (!user.value) return ''
    return `${user.value.first_name} ${user.value.last_name}`
  })

  // Actions
  const login = async (credentials) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/auth/login', {
        email: credentials.email,
        password: credentials.password
      })

      token.value = response.data.token
      user.value = response.data.user

      // Save to localStorage
      localStorage.setItem('auth_token', token.value)
      localStorage.setItem('auth_user', JSON.stringify(user.value))

      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const register = async (userData) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/auth/register', userData)

      token.value = response.data.token
      user.value = response.data.user

      // Save to localStorage
      localStorage.setItem('auth_token', token.value)
      localStorage.setItem('auth_user', JSON.stringify(user.value))

      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchUser = async () => {
    if (!token.value) {
      throw new Error('No token available')
    }

    loading.value = true
    error.value = null

    try {
      const response = await api.get('/auth/me')
      user.value = response.data.user

      // Update localStorage
      localStorage.setItem('auth_user', JSON.stringify(user.value))

      return user.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch user'
      throw err
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      if (token.value) {
        await api.post('/auth/logout')
      }
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      // Clear local state regardless
      user.value = null
      token.value = null
      error.value = null

      // Clear localStorage
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')

      // Redirect to home page
      if (typeof window !== 'undefined') {
        window.location.href = '/'
      }
    }
  }

  const updateProfile = async (profileData) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.put('/student/profile', profileData)

      // Update user data
      if (user.value) {
        user.value = { ...user.value, ...response.data.user }
        localStorage.setItem('auth_user', JSON.stringify(user.value))
      }

      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Profile update failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  // Initialize auth state from localStorage
  const initAuth = async () => {
    if (typeof window !== 'undefined') {
      const savedToken = localStorage.getItem('auth_token')
      const savedUser = localStorage.getItem('auth_user')

      if (savedToken && savedUser) {
        token.value = savedToken
        try {
          user.value = JSON.parse(savedUser)
          // Verify token is still valid
          await fetchUser()
        } catch (e) {
          console.error('Token invalid:', e)
          // Clear invalid data
          localStorage.removeItem('auth_token')
          localStorage.removeItem('auth_user')
          token.value = null
          user.value = null
        }
      }
    }
  }

  return {
    // State
    user,
    token,
    loading,
    error,

    // Computed
    isAuthenticated,
    isTutor,
    isStudent,
    fullName,

    // Actions
    login,
    register,
    fetchUser,
    logout,
    updateProfile,
    clearError,
    initAuth
  }
})
