// stores/auth.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref({
    id: 1,
    first_name: 'Maria',
    last_name: 'Popescu',
    email: 'maria.popescu@email.com',
    user_type: 'tutor', // 'tutor' or 'student'
    phone: '+40 123 456 789'
  })

  const token = ref('mock-jwt-token-12345')
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
      // Mock login - replace with actual API call
      await new Promise(resolve => setTimeout(resolve, 1000))

      // Mock response
      token.value = 'mock-jwt-token-' + Date.now()
      user.value = {
        id: 1,
        first_name: credentials.email.includes('tutor') ? 'Maria' : 'Ana',
        last_name: credentials.email.includes('tutor') ? 'Popescu' : 'Student',
        email: credentials.email,
        user_type: credentials.email.includes('tutor') ? 'tutor' : 'student',
        phone: '+40 123 456 789'
      }

      return { success: true }
    } catch (err) {
      error.value = err.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const logout = () => {
    user.value = null
    token.value = null
    error.value = null

    // Redirect to home page
    if (typeof window !== 'undefined') {
      window.location.href = '/'
    }
  }

  const fetchUser = async () => {
    if (!token.value) {
      throw new Error('No token available')
    }

    loading.value = true
    error.value = null

    try {
      // Mock API call - replace with actual API call
      await new Promise(resolve => setTimeout(resolve, 500))

      // Mock user data - in real app this would come from API
      if (!user.value) {
        user.value = {
          id: 1,
          first_name: 'Maria',
          last_name: 'Popescu',
          email: 'maria.popescu@email.com',
          user_type: 'tutor',
          phone: '+40 123 456 789'
        }
      }

      return user.value
    } catch (err) {
      error.value = err.message || 'Failed to fetch user'
      throw err
    } finally {
      loading.value = false
    }
  }

  const register = async (userData) => {
    loading.value = true
    error.value = null

    try {
      // Mock registration - replace with actual API call
      await new Promise(resolve => setTimeout(resolve, 1000))

      // Auto-login after registration
      token.value = 'mock-jwt-token-' + Date.now()
      user.value = {
        id: Date.now(),
        first_name: userData.first_name,
        last_name: userData.last_name,
        email: userData.email,
        user_type: userData.user_type,
        phone: userData.phone || ''
      }

      return { success: true }
    } catch (err) {
      error.value = err.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateProfile = async (profileData) => {
    loading.value = true
    error.value = null

    try {
      // Mock profile update - replace with actual API call
      await new Promise(resolve => setTimeout(resolve, 1000))

      // Update user data
      if (user.value) {
        user.value = { ...user.value, ...profileData }
      }

      return { success: true }
    } catch (err) {
      error.value = err.message || 'Profile update failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  // Initialize auth state from localStorage if available
  const initializeAuth = () => {
    if (typeof window !== 'undefined') {
      const savedToken = localStorage.getItem('auth_token')
      const savedUser = localStorage.getItem('auth_user')

      if (savedToken && savedUser) {
        token.value = savedToken
        try {
          user.value = JSON.parse(savedUser)
        } catch (e) {
          // Invalid user data, clear everything
          localStorage.removeItem('auth_token')
          localStorage.removeItem('auth_user')
        }
      }
    }
  }

  // Save auth state to localStorage
  const saveAuthState = () => {
    if (typeof window !== 'undefined') {
      if (token.value && user.value) {
        localStorage.setItem('auth_token', token.value)
        localStorage.setItem('auth_user', JSON.stringify(user.value))
      } else {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
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
    logout,
    fetchUser,
    register,
    updateProfile,
    clearError,
    initializeAuth,
    saveAuthState
  }
})
