// frontend/src/stores/tutor.js
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
  const weekSchedule = ref([])
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

  const getWeekSchedule = async (weekStart = null) => {
    loading.value = true
    error.value = null

    try {
      const params = {}
      if (weekStart) {
        params.week_start = weekStart
      }

      const response = await api.get('tutor/schedule', { params })
      weekSchedule.value = response.data.schedule
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea programului'
      console.error('Schedule error:', err)
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
          // Remove from pending and add to upcoming
          const booking = dashboard.value.pending_bookings.splice(bookingIndex, 1)[0]
          booking.status = 'confirmed'

          if (!dashboard.value.upcoming_bookings) {
            dashboard.value.upcoming_bookings = []
          }
          dashboard.value.upcoming_bookings.push(booking)
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
      const response = await api.patch(`bookings/${bookingId}/reject`)

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

  const confirmCashPayment = async (paymentId) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.patch(`payments/${paymentId}/confirm-cash`)

      // Update local state
      if (dashboard.value?.pending_cash_payments) {
        const paymentIndex = dashboard.value.pending_cash_payments.findIndex(p => p.id === paymentId)
        if (paymentIndex !== -1) {
          dashboard.value.pending_cash_payments.splice(paymentIndex, 1)
        }
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la confirmarea plății'
      throw err
    } finally {
      loading.value = false
    }
  }

  const completeBooking = async (bookingId) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.patch(`bookings/${bookingId}/complete`)

      // Update local state
      if (dashboard.value?.upcoming_bookings) {
        const bookingIndex = dashboard.value.upcoming_bookings.findIndex(b => b.id === bookingId)
        if (bookingIndex !== -1) {
          const booking = dashboard.value.upcoming_bookings[bookingIndex]
          booking.status = 'completed'
        }
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la finalizarea lecției'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getBookings = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('tutor/bookings', { params: filters })
      bookings.value = response.data.bookings
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea rezervărilor'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getReviews = async (params = {}) => {
  loading.value = true
  error.value = null

  try {
    const response = await api.get('tutor/reviews', { params })
    reviews.value = response.data.reviews

    console.log('Reviews loaded from API:', response.data)

    return response.data
  } catch (err) {
    error.value = err.response?.data?.message || 'Eroare la încărcarea recenziilor'
    console.error('Error loading reviews:', err)
    throw err
  } finally {
    loading.value = false
  }
}

  const submitReply = async (reviewId, replyText) => {
  loading.value = true
  error.value = null

  try {
    const response = await api.post(`reviews/${reviewId}/reply`, {
      reply: replyText
    })

    // Update local reviews state
    const reviewIndex = reviews.value.findIndex(r => r.id === reviewId)
    if (reviewIndex !== -1) {
      reviews.value[reviewIndex] = {
        ...reviews.value[reviewIndex],
        reply: replyText,
        reply_date: new Date().toISOString()
      }
    }

    console.log('Reply submitted:', response.data)

    return response.data
  } catch (err) {
    error.value = err.response?.data?.message || 'Eroare la trimiterea răspunsului'
    console.error('Error submitting reply:', err)
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
      profile.value = response.data.tutor
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

  const updateAvailability = async (availabilityData) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.put('tutor/availability', availabilityData)
      availability.value = response.data.availability
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la actualizarea disponibilității'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getAvailability = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('tutor/availability')
      availability.value = response.data.availability
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea disponibilității'
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
    weekSchedule.value = []
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
    weekSchedule,
    loading,
    error,

    // Computed
    totalEarnings,
    totalLessons,
    averageRating,
    pendingBookingsCount,

    // Actions
    getDashboard,
    getWeekSchedule,
    confirmBooking,
    rejectBooking,
    confirmCashPayment,
    completeBooking,
    getBookings,
    getReviews,
    submitReply,
    getProfile,
    updateProfile,
    updateAvailability,
    getAvailability,
    clearError,
    $reset
  }
})
