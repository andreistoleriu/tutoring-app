// Update your frontend/src/stores/tutor.js with corrected API paths

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useTutorStore = defineStore('tutor', () => {
  // State
  const dashboard = ref(null)
  const profile = ref(null)
  const bookings = ref([])
  const reviews = ref([])
  const earnings = ref(null)
  const availability = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Computed
  const totalEarnings = computed(() => dashboard.value?.stats?.total_earnings || 0)
  const totalLessons = computed(() => dashboard.value?.stats?.total_lessons || 0)
  const averageRating = computed(() => dashboard.value?.stats?.average_rating || 0)
  const pendingBookingsCount = computed(() => dashboard.value?.pending_bookings?.length || 0)

  // Actions
  const getDashboard = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('tutor/dashboard')
      dashboard.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea dashboard-ului'
      console.error('Dashboard error:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const confirmBooking = async (bookingId) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.patch(`bookings/${bookingId}/confirm`)

      // Update local state
      if (dashboard.value?.pending_bookings) {
        const bookingIndex = dashboard.value.pending_bookings.findIndex(b => b.id === bookingId)
        if (bookingIndex !== -1) {
          const booking = dashboard.value.pending_bookings[bookingIndex]
          booking.status = 'confirmed'

          // Move to upcoming bookings
          dashboard.value.upcoming_bookings = dashboard.value.upcoming_bookings || []
          dashboard.value.upcoming_bookings.push(booking)

          // Remove from pending
          dashboard.value.pending_bookings.splice(bookingIndex, 1)
        }
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la confirmarea rezervării'
      throw err
    } finally {
      loading.value = false
    }
  }

  const rejectBooking = async (bookingId) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.patch(`bookings/${bookingId}/cancel`)

      // Update local state
      if (dashboard.value?.pending_bookings) {
        const bookingIndex = dashboard.value.pending_bookings.findIndex(b => b.id === bookingId)
        if (bookingIndex !== -1) {
          dashboard.value.pending_bookings.splice(bookingIndex, 1)
        }
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la respingerea rezervării'
      throw err
    } finally {
      loading.value = false
    }
  }

  const confirmCashPayment = async (bookingId) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.patch(`bookings/${bookingId}/confirm-payment`)

      // Update local state in dashboard if booking is there
      if (dashboard.value?.pending_cash_payments) {
        dashboard.value.pending_cash_payments = dashboard.value.pending_cash_payments.filter(
          payment => payment.id !== bookingId
        )
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la confirmarea plății cash'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getBookings = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      const params = {
        ...filters,
        per_page: filters.per_page || 15
      }

      const response = await api.get('bookings', { params })
      bookings.value = response.data.bookings
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea rezervărilor'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getProfile = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('tutor/profile')
      profile.value = response.data.tutor || response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea profilului'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateProfile = async (profileData) => {
    loading.value = true
    error.value = null

    try {
      // Determine the content type based on the data
      const config = {}
      if (profileData instanceof FormData) {
        config.headers = {
          'Content-Type': 'multipart/form-data'
        }
      }

      const response = await api.put('tutor/profile', profileData, config)

      // Update local profile data
      profile.value = response.data.tutor || response.data

      // Update dashboard tutor data if available
      if (dashboard.value?.tutor) {
        dashboard.value.tutor = { ...dashboard.value.tutor, ...response.data.tutor }
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la actualizarea profilului'
      throw err
    } finally {
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  // Reset store
  const $reset = () => {
    dashboard.value = null
    profile.value = null
    bookings.value = []
    reviews.value = []
    earnings.value = null
    availability.value = []
    loading.value = false
    error.value = null
  }

  return {
    // State
    dashboard,
    profile,
    bookings,
    reviews,
    earnings,
    availability,
    loading,
    error,

    // Computed
    totalEarnings,
    totalLessons,
    averageRating,
    pendingBookingsCount,

    // Actions
    getDashboard,
    confirmBooking,
    rejectBooking,
    confirmCashPayment,
    getBookings,
    getProfile,
    updateProfile,
    clearError,
    $reset
  }
})
