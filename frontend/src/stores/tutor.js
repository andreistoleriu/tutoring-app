// frontend/src/stores/tutor.js
// Complete replacement to remove ALL mock data

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
      console.log('🔄 Loading dashboard data from API...')
      const response = await api.get('/tutor/dashboard')
      dashboard.value = response.data
      console.log('✅ Dashboard loaded successfully')
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea dashboard-ului'
      console.error('❌ Dashboard error:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const confirmBooking = async (bookingId) => {
    loading.value = true
    error.value = null

    try {
      console.log(`🔄 Confirming booking ${bookingId}...`)
      const response = await api.patch(`/bookings/${bookingId}/confirm`)
      console.log(`✅ Booking ${bookingId} confirmed successfully`)

      // Update local state - move booking from pending to upcoming
      if (dashboard.value?.pending_bookings) {
        const bookingIndex = dashboard.value.pending_bookings.findIndex(b => b.id === bookingId)
        if (bookingIndex !== -1) {
          const booking = dashboard.value.pending_bookings[bookingIndex]
          booking.status = 'confirmed'
          booking.confirmed_at = new Date().toISOString()

          // Move to upcoming bookings
          dashboard.value.upcoming_bookings = dashboard.value.upcoming_bookings || []
          dashboard.value.upcoming_bookings.push(booking)

          // Remove from pending
          dashboard.value.pending_bookings.splice(bookingIndex, 1)
        }
      }

      return response.data
    } catch (err) {
      console.error(`❌ Error confirming booking ${bookingId}:`, err)
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
      console.log(`🔄 Rejecting booking ${bookingId}...`)
      const response = await api.patch(`/bookings/${bookingId}/cancel`, {
        cancellation_reason: 'Respinsă de tutor'
      })
      console.log(`✅ Booking ${bookingId} rejected successfully`)

      // Update local state - remove from pending bookings
      if (dashboard.value?.pending_bookings) {
        const bookingIndex = dashboard.value.pending_bookings.findIndex(b => b.id === bookingId)
        if (bookingIndex !== -1) {
          dashboard.value.pending_bookings.splice(bookingIndex, 1)
        }
      }

      return response.data
    } catch (err) {
      console.error(`❌ Error rejecting booking ${bookingId}:`, err)
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
      console.log(`🔄 Confirming cash payment for booking ${bookingId}...`)
      const response = await api.patch(`/bookings/${bookingId}/confirm-payment`)
      console.log(`✅ Cash payment confirmed for booking ${bookingId}`)

      // Refresh dashboard data to update pending payments
      await getDashboard()

      return response.data
    } catch (err) {
      console.error(`❌ Error confirming cash payment for booking ${bookingId}:`, err)
      error.value = err.response?.data?.message || 'Eroare la confirmarea plății'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getBookings = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      console.log('🔄 Loading bookings from API...')
      const response = await api.get('/bookings', { params: filters })
      bookings.value = response.data.bookings
      console.log('✅ Bookings loaded successfully')
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea rezervărilor'
      console.error('❌ Bookings error:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const getReviews = async () => {
    loading.value = true
    error.value = null

    try {
      console.log('🔄 Loading reviews from API...')
      const response = await api.get('/reviews')
      reviews.value = response.data.reviews
      console.log('✅ Reviews loaded successfully')
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea recenziilor'
      console.error('❌ Reviews error:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateProfile = async (profileData) => {
    loading.value = true
    error.value = null

    try {
      console.log('🔄 Updating tutor profile...')
      const response = await api.put('/tutor/profile', profileData)
      console.log('✅ Profile updated successfully')

      // Update local profile data
      if (dashboard.value?.tutor) {
        dashboard.value.tutor = { ...dashboard.value.tutor, ...profileData }
      }

      return response.data
    } catch (err) {
      console.error('❌ Error updating profile:', err)
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
      console.log('🔄 Updating tutor availability...')
      const response = await api.put('/tutor/availability', availabilityData)
      console.log('✅ Availability updated successfully')

      availability.value = response.data.availability
      return response.data
    } catch (err) {
      console.error('❌ Error updating availability:', err)
      error.value = err.response?.data?.message || 'Eroare la actualizarea disponibilității'
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
    getReviews,
    updateProfile,
    updateAvailability,
    clearError,
    $reset
  }
})
