import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token'),
    loading: false
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    isStudent: (state) => state.user?.user_type === 'student',
    isTutor: (state) => state.user?.user_type === 'tutor',
    userName: (state) => state.user?.full_name || '',
    userInitials: (state) => {
      if (!state.user) return ''
      return `${state.user.first_name[0]}${state.user.last_name[0]}`.toUpperCase()
    }
  },

  actions: {
    async login(credentials) {
      this.loading = true
      try {
        const response = await api.post('/auth/login', credentials)
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('auth_token', this.token)
        return response.data
      } catch (error) {
        throw error
      } finally {
        this.loading = false
      }
    },

    async register(userData) {
      this.loading = true
      try {
        const response = await api.post('/auth/register', userData)
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('auth_token', this.token)
        return response.data
      } catch (error) {
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        if (this.token) {
          await api.post('/auth/logout')
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('auth_token')
      }
    },

    async fetchUser() {
      if (!this.token) return null

      try {
        const response = await api.get('/auth/me')
        this.user = response.data.user
        return response.data.user
      } catch (error) {
        console.error('Fetch user error:', error)
        this.logout()
        return null
      }
    },

    // Initialize auth state on app start
    async initAuth() {
      if (this.token) {
        await this.fetchUser()
      }
    }
  }
})
