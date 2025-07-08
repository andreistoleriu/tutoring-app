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
      const response = await api.get('/tutor/dashboard')
      dashboard.value = response.data

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea dashboard-ului'
      console.error('Dashboard error:', err)

      // Fallback to mock data for development
      if (err.response?.status === 404 || err.code === 'ECONNREFUSED') {
        console.warn('API not available, using mock data')
        dashboard.value = getMockDashboardData()
        return dashboard.value
      }

      throw err
    } finally {
      loading.value = false
    }
  }

  const confirmBooking = async (bookingId) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.patch(`/bookings/${bookingId}/confirm`)

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
      const response = await api.patch(`/bookings/${bookingId}/cancel`)

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
      const response = await api.patch(`/bookings/${bookingId}/confirm-payment`)

      // Refresh dashboard data
      await getDashboard()

      return response.data
    } catch (err) {
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
      const response = await api.get('/bookings', { params: filters })
      bookings.value = response.data.bookings
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea rezervărilor'

      // Fallback to mock data
      if (err.response?.status === 404 || err.code === 'ECONNREFUSED') {
        const mockData = getMockBookingsData()
        bookings.value = mockData
        return { bookings: mockData }
      }

      throw err
    } finally {
      loading.value = false
    }
  }

  const getReviews = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('/reviews')
      reviews.value = response.data.reviews
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea recenziilor'

      // Fallback to mock data
      if (err.response?.status === 404 || err.code === 'ECONNREFUSED') {
        const mockData = getMockReviewsData()
        reviews.value = mockData
        return { reviews: mockData }
      }

      throw err
    } finally {
      loading.value = false
    }
  }

  const updateProfile = async (profileData) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.put('/tutor/profile', profileData)

      // Update local profile data
      if (dashboard.value?.tutor) {
        dashboard.value.tutor = { ...dashboard.value.tutor, ...profileData }
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
      const response = await api.put('/tutor/availability', availabilityData)
      availability.value = response.data.availability
      return response.data
    } catch (err) {
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

  // Mock data functions for fallback
  const getMockDashboardData = () => ({
    stats: {
      total_earnings: 2450,
      total_lessons: 127,
      average_rating: 4.8,
      total_reviews: 89,
      this_month_bookings: 23,
      pending_payments: 350
    },
    subscription: {
      plan_type: 'free_trial',
      trial_days_remaining: 7,
      expires_at: '2025-07-21',
      features: ['Up to 5 bookings per month', 'Basic profile', 'Email support']
    },
    pending_bookings: [
      {
        id: 1,
        student: { full_name: 'Maria Popescu', email: 'maria@email.com' },
        subject: { name: 'Matematică' },
        scheduled_at: '2025-07-08T10:00:00',
        lesson_type: 'online',
        price: 75,
        status: 'pending'
      },
      {
        id: 2,
        student: { full_name: 'Ion Ionescu', email: 'ion@email.com' },
        subject: { name: 'Fizică' },
        scheduled_at: '2025-07-08T14:30:00',
        lesson_type: 'in_person',
        price: 80,
        status: 'pending'
      }
    ],
    upcoming_bookings: [
      {
        id: 3,
        student: { full_name: 'Ana Georgescu', email: 'ana@email.com' },
        subject: { name: 'Chimie' },
        scheduled_at: '2025-07-09T09:00:00',
        lesson_type: 'online',
        price: 70,
        status: 'confirmed'
      }
    ],
    recent_reviews: [
      {
        id: 1,
        student: { full_name: 'Alexandra M.' },
        rating: 5,
        comment: 'Explicații foarte clare și răbdare multă. Recomand!',
        subject: 'Matematică',
        date: '2025-07-05',
        created_at: '2025-07-05T14:30:00'
      }
    ]
  })

  const getMockBookingsData = () => [
    {
      id: 1,
      student: { full_name: 'Maria Popescu', email: 'maria@email.com' },
      subject: { name: 'Matematică' },
      scheduled_at: '2025-07-08T10:00:00',
      lesson_type: 'online',
      price: 75,
      status: 'pending'
    }
  ]

  const getMockReviewsData = () => [
    {
      id: 1,
      student: { full_name: 'Alexandra M.' },
      rating: 5,
      comment: 'Explicații foarte clare și răbdare multă. Recomand!',
      subject: 'Matematică',
      date: '2025-07-05'
    }
  ]

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
