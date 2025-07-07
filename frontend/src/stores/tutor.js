// stores/tutor.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
// import api from '@/services/api'

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

  // Mock data for development
  const mockDashboardData = {
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
        lesson_type: 'in-person',
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
      },
      {
        id: 4,
        student: { full_name: 'Cristian Pavel', email: 'cristian@email.com' },
        subject: { name: 'Matematică' },
        scheduled_at: '2025-07-10T16:00:00',
        lesson_type: 'online',
        price: 75,
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
      },
      {
        id: 2,
        student: { full_name: 'Cristian P.' },
        rating: 4,
        comment: 'Profesor foarte bun, m-a ajutat mult la pregătirea pentru BAC.',
        subject: 'Fizică',
        date: '2025-07-03',
        created_at: '2025-07-03T16:20:00'
      }
    ],
    pending_cash_payments: [
      {
        id: 1,
        studentName: 'Elena Dumitrescu',
        amount: 150,
        date: '2025-07-05',
        lessons: 2
      },
      {
        id: 2,
        studentName: 'Mihai Radu',
        amount: 200,
        date: '2025-07-04',
        lessons: 2
      }
    ],
    notifications: [
      {
        id: 1,
        message: 'Ai o nouă rezervare pentru mâine la 10:00',
        created_at: '2025-07-07T09:00:00',
        read: false,
        type: 'booking'
      },
      {
        id: 2,
        message: 'Ai primit o recenzie nouă de 5 stele!',
        created_at: '2025-07-06T15:30:00',
        read: false,
        type: 'review'
      }
    ],
    tutor: {
      bio: 'Profesor de matematică cu experiență de 10 ani',
      subjects: ['Matematică', 'Fizică'],
      hourly_rate: 75,
      availabilities: ['09:00-17:00'],
      photo: null,
      location: 'București',
      experience: 'Licență în Matematică, Universitatea București',
      education: 'Master în Didactica Matematicii'
    },
    weekly_stats: {
      lessons: 8,
      earnings: 600,
      newStudents: 3,
      occupancyRate: 85
    },
    week_schedule: [
      { name: 'Lun', availableSlots: ['09:00', '11:00'], bookings: [] },
      { name: 'Mar', availableSlots: ['10:00'], bookings: [{ id: 1, time: '14:00' }] },
      { name: 'Mie', availableSlots: ['09:00', '15:00'], bookings: [] },
      { name: 'Joi', availableSlots: [], bookings: [] },
      { name: 'Vin', availableSlots: ['13:00', '16:00'], bookings: [] },
      { name: 'Sâm', availableSlots: [], bookings: [] },
      { name: 'Dum', availableSlots: [], bookings: [] }
    ]
  }

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
      // For now, return mock data
      // When your API is ready, uncomment this:
      // const response = await api.get('/tutor/dashboard')
      // dashboard.value = response.data
      // return response.data

      // Simulate API delay
      await new Promise(resolve => setTimeout(resolve, 800))
      dashboard.value = mockDashboardData
      return mockDashboardData
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
      // Mock confirmation
      console.log(`Booking ${bookingId} confirmed`)

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

      return { success: true }

      // When your API is ready, uncomment this:
      // const response = await api.post(`/bookings/${bookingId}/confirm`)
      // return response.data
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
      // Mock rejection
      console.log(`Booking ${bookingId} rejected`)

      // Update local state
      if (dashboard.value?.pending_bookings) {
        const bookingIndex = dashboard.value.pending_bookings.findIndex(b => b.id === bookingId)
        if (bookingIndex !== -1) {
          dashboard.value.pending_bookings.splice(bookingIndex, 1)
        }
      }

      return { success: true }

      // When your API is ready, uncomment this:
      // const response = await api.post(`/bookings/${bookingId}/reject`)
      // return response.data
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
      // Mock cash payment confirmation
      console.log(`Cash payment ${paymentId} confirmed`)

      // Update local state
      if (dashboard.value?.pending_cash_payments) {
        const paymentIndex = dashboard.value.pending_cash_payments.findIndex(p => p.id === paymentId)
        if (paymentIndex !== -1) {
          const payment = dashboard.value.pending_cash_payments[paymentIndex]

          // Update stats
          dashboard.value.stats.pending_payments -= payment.amount
          dashboard.value.stats.total_earnings += payment.amount

          // Remove from pending payments
          dashboard.value.pending_cash_payments.splice(paymentIndex, 1)
        }
      }

      return { success: true }

      // When your API is ready, uncomment this:
      // const response = await api.post(`/payments/${paymentId}/confirm-cash`)
      // return response.data
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
      // Mock bookings data
      await new Promise(resolve => setTimeout(resolve, 500))

      const mockBookings = [
        ...mockDashboardData.pending_bookings,
        ...mockDashboardData.upcoming_bookings,
        {
          id: 5,
          student: { full_name: 'Elena Dumitrescu', email: 'elena@email.com' },
          subject: { name: 'Fizică' },
          scheduled_at: '2025-07-05T16:00:00',
          lesson_type: 'in-person',
          price: 80,
          status: 'completed',
          payment_method: 'cash',
          payment_status: 'pending'
        }
      ]

      bookings.value = mockBookings
      return { bookings: mockBookings }

      // When your API is ready, uncomment this:
      // const response = await api.get('/tutor/bookings', { params: filters })
      // bookings.value = response.data.bookings
      // return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea rezervărilor'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getReviews = async () => {
    loading.value = true
    error.value = null

    try {
      // Mock reviews data
      await new Promise(resolve => setTimeout(resolve, 500))

      reviews.value = mockDashboardData.recent_reviews
      return { reviews: mockDashboardData.recent_reviews }

      // When your API is ready, uncomment this:
      // const response = await api.get('/tutor/reviews')
      // reviews.value = response.data.reviews
      // return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea recenziilor'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateProfile = async (profileData) => {
    loading.value = true
    error.value = null

    try {
      // Mock profile update
      await new Promise(resolve => setTimeout(resolve, 1000))

      // Update local profile data
      if (dashboard.value?.tutor) {
        dashboard.value.tutor = { ...dashboard.value.tutor, ...profileData }
      }

      return { success: true }

      // When your API is ready, uncomment this:
      // const response = await api.put('/tutor/profile', profileData)
      // return response.data
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
      // Mock availability update
      await new Promise(resolve => setTimeout(resolve, 1000))

      availability.value = availabilityData
      return { success: true }

      // When your API is ready, uncomment this:
      // const response = await api.put('/tutor/availability', availabilityData)
      // availability.value = response.data.availability
      // return response.data
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
