// frontend/src/stores/student.js - Updated with Real API Calls

import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useStudentStore = defineStore('student', () => {
  // State
  const dashboard = ref(null)
  const bookings = ref(null)
  const profile = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const reviews = ref([])

  // Actions
  const getDashboard = async () => {
    loading.value = true
    error.value = null

    try {
      console.log('🔄 Loading student dashboard from API...')

      const response = await api.get('student/dashboard')

      console.log('📊 Dashboard API response:', response.data)

      dashboard.value = response.data
      return response.data
    } catch (err) {
      console.error('❌ Error loading dashboard:', err)
      error.value = err.response?.data?.message || 'Eroare la încărcarea dashboard-ului'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getBookings = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      console.log('🔍 Loading bookings with filters:', filters)

      const params = new URLSearchParams()
      Object.keys(filters).forEach((key) => {
        if (filters[key]) {
          params.append(key, filters[key])
        }
      })

      const response = await api.get(`student/bookings?${params.toString()}`)

      console.log('📋 Bookings API response:', response.data)

      // Enhance bookings with review status
      const enhancedBookings = (response.data.bookings || []).map((booking) => ({
        ...booking,
        can_review: booking.status === 'completed' && !booking.review,
        needs_review: booking.status === 'completed' && !booking.review,
        has_review: !!booking.review
      }))

      const enhancedResponse = {
        ...response.data,
        bookings: enhancedBookings
      }

      bookings.value = enhancedResponse
      return enhancedResponse
    } catch (err) {
      console.error('❌ Error loading bookings:', err)
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
      console.log('👤 Loading student profile from API...')

      const response = await api.get('student/profile')

      console.log('✅ Profile API response:', response.data)

      profile.value = response.data.profile
      return response.data
    } catch (err) {
      console.error('❌ Error loading profile:', err)
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
      console.log('📝 Updating student profile:', profileData)

      const response = await api.put('student/profile', profileData)

      console.log('✅ Profile update response:', response.data)

      // Update local profile
      if (profile.value) {
        Object.assign(profile.value, response.data.profile)
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

  const createBooking = async (bookingData) => {
    loading.value = true
    error.value = null

    try {
      console.log('📝 Creating booking:', bookingData)

      const response = await api.post('bookings', bookingData)

      console.log('✅ Booking created:', response.data)

      return response.data
    } catch (err) {
      console.error('❌ Error creating booking:', err)
      error.value = err.response?.data?.message || 'Eroare la crearea rezervării'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getBooking = async (bookingId) => {
    loading.value = true
    error.value = null

    try {
      console.log('📋 Loading booking:', bookingId)

      const response = await api.get(`bookings/${bookingId}`)

      console.log('✅ Booking loaded:', response.data)

      return response.data
    } catch (err) {
      console.error('❌ Error loading booking:', err)
      error.value = err.response?.data?.message || 'Eroare la încărcarea rezervării'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateBooking = async (bookingId, updateData) => {
    loading.value = true
    error.value = null

    try {
      console.log('🔄 Updating booking:', bookingId, updateData)

      const response = await api.put(`bookings/${bookingId}`, updateData)

      console.log('✅ Booking updated:', response.data)

      // Update local state
      if (dashboard.value?.upcoming_bookings) {
        const bookingIndex = dashboard.value.upcoming_bookings.findIndex((b) => b.id === bookingId)
        if (bookingIndex !== -1) {
          dashboard.value.upcoming_bookings[bookingIndex] = response.data.booking
        }
      }

      if (bookings.value?.bookings) {
        const bookingIndex = bookings.value.bookings.findIndex((b) => b.id === bookingId)
        if (bookingIndex !== -1) {
          bookings.value.bookings[bookingIndex] = response.data.booking
        }
      }

      return response.data
    } catch (err) {
      console.error('❌ Error updating booking:', err)
      error.value = err.response?.data?.message || 'Eroare la actualizarea rezervării'
      throw err
    } finally {
      loading.value = false
    }
  }

  const cancelBooking = async (bookingId, reason) => {
    loading.value = true
    error.value = null

    try {
      console.log('🚫 Cancelling booking:', bookingId, reason)

      const response = await api.patch(`bookings/${bookingId}/cancel`, {
        cancellation_reason: reason
      })

      console.log('✅ Booking cancelled:', response.data)

      // Update local state - remove from upcoming bookings
      if (dashboard.value?.upcoming_bookings) {
        const bookingIndex = dashboard.value.upcoming_bookings.findIndex((b) => b.id === bookingId)
        if (bookingIndex !== -1) {
          dashboard.value.upcoming_bookings.splice(bookingIndex, 1)
        }
      }

      if (bookings.value?.bookings) {
        const bookingIndex = bookings.value.bookings.findIndex((b) => b.id === bookingId)
        if (bookingIndex !== -1) {
          bookings.value.bookings[bookingIndex].status = 'cancelled'
          bookings.value.bookings[bookingIndex].cancelled_at = new Date().toISOString()
          bookings.value.bookings[bookingIndex].cancellation_reason = reason
        }
      }

      return response.data
    } catch (err) {
      console.error('❌ Error cancelling booking:', err)
      error.value = err.response?.data?.message || 'Eroare la anularea rezervării'
      throw err
    } finally {
      loading.value = false
    }
  }

  const submitReview = async (reviewData) => {
    loading.value = true
    error.value = null

    try {
      console.log('📝 Submitting review:', reviewData)

      const response = await api.post('reviews', {
        booking_id: reviewData.booking_id,
        rating: reviewData.rating,
        comment: reviewData.comment || null,
      })

      console.log('✅ Review submitted successfully:', response.data)

      // Update local state
      if (dashboard.value?.recent_bookings) {
        const bookingIndex = dashboard.value.recent_bookings.findIndex(b => b.id === reviewData.booking_id)
        if (bookingIndex !== -1) {
          dashboard.value.recent_bookings[bookingIndex].review = response.data.review
          dashboard.value.recent_bookings[bookingIndex].can_review = false
          dashboard.value.recent_bookings[bookingIndex].needs_review = false
          dashboard.value.recent_bookings[bookingIndex].has_review = true
        }
      }

      // Update pending reviews count in stats
      if (dashboard.value?.stats?.pending_reviews > 0) {
        dashboard.value.stats.pending_reviews -= 1
      }

      // Update local reviews array if we're storing them
      if (reviews.value) {
        reviews.value.unshift(response.data.review)
      }

      return response.data
    } catch (err) {
      console.error('❌ Error submitting review:', err)
      error.value = err.response?.data?.message || 'Eroare la trimiterea review-ului'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateReview = async (reviewId, reviewData) => {
    loading.value = true
    error.value = null

    try {
      console.log('📝 Updating review:', reviewId, reviewData)

      const response = await api.put(`reviews/${reviewId}`, {
        rating: reviewData.rating,
        comment: reviewData.comment || null,
      })

      console.log('✅ Review updated successfully:', response.data)

      // Update local state in dashboard recent bookings
      if (dashboard.value?.recent_bookings) {
        const booking = dashboard.value.recent_bookings.find(b => b.review?.id === reviewId)
        if (booking && booking.review) {
          booking.review.rating = reviewData.rating
          booking.review.comment = reviewData.comment
          booking.review.updated_at = new Date().toISOString()
        }
      }

      // Update local reviews array
      if (reviews.value) {
        const index = reviews.value.findIndex((r) => r.id === reviewId)
        if (index !== -1) {
          reviews.value[index] = response.data.review
        }
      }

      return response.data
    } catch (err) {
      console.error('❌ Error updating review:', err)
      error.value = err.response?.data?.message || 'Eroare la actualizarea review-ului'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteReview = async (reviewId) => {
    loading.value = true
    error.value = null

    try {
      console.log('🗑️ Deleting review:', reviewId)

      await api.delete(`reviews/${reviewId}`)

      console.log('✅ Review deleted successfully')

      // Update local state in dashboard recent bookings
      if (dashboard.value?.recent_bookings) {
        const booking = dashboard.value.recent_bookings.find(b => b.review?.id === reviewId)
        if (booking) {
          booking.review = null
          booking.can_review = true
          booking.needs_review = true
          booking.has_review = false
        }
      }

      // Increase pending reviews count in stats
      if (dashboard.value?.stats) {
        dashboard.value.stats.pending_reviews = (dashboard.value.stats.pending_reviews || 0) + 1
      }

      // Remove from local reviews array
      if (reviews.value) {
        const index = reviews.value.findIndex((r) => r.id === reviewId)
        if (index !== -1) {
          reviews.value.splice(index, 1)
        }
      }

      return true
    } catch (err) {
      console.error('❌ Error deleting review:', err)
      error.value = err.response?.data?.message || 'Eroare la ștergerea review-ului'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getReviews = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      console.log('📋 Loading student reviews with filters:', filters)

      const params = new URLSearchParams()
      Object.keys(filters).forEach((key) => {
        if (filters[key]) {
          params.append(key, filters[key])
        }
      })

      // Note: You might need to create this endpoint if it doesn't exist
      const response = await api.get(`student/reviews?${params.toString()}`)

      console.log('✅ Reviews loaded:', response.data)

      reviews.value = response.data.reviews || []
      return response.data
    } catch (err) {
      console.error('❌ Error loading reviews:', err)

      // If endpoint doesn't exist, try to extract reviews from bookings
      if (err.response?.status === 404) {
        console.log('📋 Reviews endpoint not found, extracting from bookings...')
        try {
          const bookingsResponse = await getBookings({ status: 'completed' })
          const reviewsFromBookings = bookingsResponse.bookings
            .filter(booking => booking.review)
            .map(booking => ({
              ...booking.review,
              booking: booking,
              tutor: booking.tutor,
              subject: booking.subject
            }))

          reviews.value = reviewsFromBookings
          return { reviews: reviewsFromBookings }
        } catch (bookingsErr) {
          console.error('❌ Error extracting reviews from bookings:', bookingsErr)
          error.value = 'Eroare la încărcarea review-urilor'
          throw bookingsErr
        }
      } else {
        error.value = err.response?.data?.message || 'Eroare la încărcarea review-urilor'
        throw err
      }
    } finally {
      loading.value = false
    }
  }

  const getBookingReview = async (bookingId) => {
    try {
      console.log('📋 Loading review for booking:', bookingId)

      const response = await api.get(`bookings/${bookingId}/review`)

      console.log('✅ Booking review loaded:', response.data)
      return response.data.review
    } catch (err) {
      if (err.response?.status === 404) {
        // No review exists for this booking - this is normal
        console.log('📋 No review found for booking:', bookingId)
        return null
      }

      console.error('❌ Error loading booking review:', err)
      throw err
    }
  }

  // Enhanced method to load dashboard with review information
  const getBookingsWithReviews = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      console.log('📋 Loading bookings with review status:', filters)

      // Use the regular getDashboard method which should include review data
      const dashboardData = await getDashboard()

      // Enhance recent bookings with review status if not already present
      if (dashboardData.recent_bookings) {
        dashboardData.recent_bookings = dashboardData.recent_bookings.map((booking) => ({
          ...booking,
          can_review: booking.status === 'completed' && !booking.review,
          needs_review: booking.status === 'completed' && !booking.review,
          has_review: !!booking.review
        }))
      }

      return dashboardData
    } catch (err) {
      console.error('❌ Error loading bookings with reviews:', err)
      error.value = err.response?.data?.message || 'Eroare la încărcarea rezervărilor'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Tutor-related methods for booking process
  const getTutorAvailability = async (tutorId) => {
    try {
      console.log('📅 Loading tutor availability:', tutorId)

      const response = await api.get(`tutors/${tutorId}/availability`)

      console.log('✅ Tutor availability loaded:', response.data)

      return response.data
    } catch (err) {
      console.error('❌ Error fetching tutor availability:', err)
      throw new Error('Nu am putut încărca disponibilitatea tutorului')
    }
  }

  const getTutorBusySlots = async (tutorId) => {
    try {
      console.log('📅 Loading tutor busy slots:', tutorId)

      const response = await api.get(`tutors/${tutorId}/busy-slots`)

      console.log('✅ Tutor busy slots loaded:', response.data)

      return response.data
    } catch (err) {
      console.error('❌ Error fetching tutor busy slots:', err)
      throw new Error('Nu am putut încărca programul tutorului')
    }
  }

  // Reset store
  const $reset = () => {
    dashboard.value = null
    bookings.value = null
    profile.value = null
    loading.value = false
    error.value = null
    reviews.value = []
  }

  return {
    // State
    dashboard,
    bookings,
    profile,
    loading,
    error,
    reviews,

    // Actions
    getDashboard,
    getBookings,
    getProfile,
    updateProfile,
    createBooking,
    getBooking,
    updateBooking,
    cancelBooking,
    submitReview,
    updateReview,
    deleteReview,
    getReviews,
    getBookingReview,
    getBookingsWithReviews,
    getTutorAvailability,
    getTutorBusySlots,
    $reset,
  }
})
