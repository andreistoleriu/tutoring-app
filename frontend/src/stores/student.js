// frontend/src/stores/student.js - Fixed Version
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

  // Dashboard Actions
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

  // Booking Actions
  const getBookings = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      console.log('🔍 Loading bookings with filters:', filters)

      const params = new URLSearchParams()
      Object.keys(filters).forEach((key) => {
        if (filters[key] !== null && filters[key] !== undefined && filters[key] !== '') {
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

  const getBooking = async (bookingId) => {
    loading.value = true
    error.value = null

    try {
      console.log('🔍 Loading booking:', bookingId)
      const response = await api.get(`bookings/${bookingId}`)
      console.log('📋 Booking loaded:', response.data)

      return response.data
    } catch (err) {
      console.error('❌ Error loading booking:', err)
      error.value = err.response?.data?.message || 'Eroare la încărcarea rezervării'
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

  const updateBooking = async (bookingId, updateData) => {
    loading.value = true
    error.value = null

    try {
      console.log('🔄 Updating booking:', bookingId, 'with data:', updateData)
      const response = await api.put(`bookings/${bookingId}`, updateData)
      console.log('✅ Booking updated:', response.data)

      // Update local state
      updateBookingInState(bookingId, response.data.booking)

      return response.data
    } catch (err) {
      console.error('❌ Error updating booking:', err)
      error.value = err.response?.data?.message || 'Eroare la actualizarea rezervării'
      throw err
    } finally {
      loading.value = false
    }
  }

  const cancelBooking = async (bookingId, reason = '') => {
    loading.value = true
    error.value = null

    try {
      console.log('❌ Cancelling booking:', bookingId, 'reason:', reason)
      const response = await api.patch(`bookings/${bookingId}/cancel`, {
        cancellation_reason: reason
      })
      console.log('✅ Booking cancelled:', response.data)

      // Update local state
      updateBookingInState(bookingId, {
        ...response.data.booking,
        status: 'cancelled',
        cancelled_at: new Date().toISOString(),
        cancellation_reason: reason
      })

      return response.data
    } catch (err) {
      console.error('❌ Error cancelling booking:', err)
      error.value = err.response?.data?.message || 'Eroare la anularea rezervării'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Review Actions - FIXED METHODS
  const submitReview = async (reviewData) => {
    loading.value = true
    error.value = null

    try {
      console.log('📝 Submitting review with data:', reviewData)

      // ✅ Ensure all required fields are present and valid
      const requestData = {
        booking_id: parseInt(reviewData.booking_id), // Ensure it's a number
        rating: parseInt(reviewData.rating),         // Ensure it's a number
        comment: reviewData.comment ? reviewData.comment.trim() : null
      }

      console.log('📝 Final request data:', requestData)

      // Validate required fields before sending
      if (!requestData.booking_id) {
        throw new Error('ID-ul rezervării este obligatoriu')
      }
      if (!requestData.rating || requestData.rating < 1 || requestData.rating > 5) {
        throw new Error('Evaluarea trebuie să fie între 1 și 5')
      }

      const response = await api.post('reviews', requestData)
      console.log('✅ Review submitted successfully:', response.data)

      // Update local state
      updateReviewInBookings(requestData.booking_id, response.data.review)

      // Update reviews array if we're storing them
      if (reviews.value) {
        reviews.value.unshift(response.data.review)
      }

      return response.data
    } catch (err) {
      console.error('❌ Error submitting review:', err)

      // Better error handling
      let errorMessage = 'Eroare la trimiterea review-ului'
      if (err.response?.data?.message) {
        errorMessage = err.response.data.message
      } else if (err.response?.data?.errors) {
        // Handle validation errors
        const errors = err.response.data.errors
        errorMessage = Object.values(errors).flat().join(', ')
      } else if (err.message) {
        errorMessage = err.message
      }

      error.value = errorMessage
      throw new Error(errorMessage)
    } finally {
      loading.value = false
    }
  }

  const updateReview = async (reviewId, reviewData) => {
    loading.value = true
    error.value = null

    try {
      console.log('🔄 Updating review:', reviewId, 'with data:', reviewData)

      const requestData = {
        rating: parseInt(reviewData.rating),
        comment: reviewData.comment ? reviewData.comment.trim() : null
      }

      const response = await api.put(`reviews/${reviewId}`, requestData)
      console.log('✅ Review updated:', response.data)

      // Update local state
      updateReviewInBookings(reviewData.booking_id, response.data.review)

      // Update reviews array
      if (reviews.value) {
        const index = reviews.value.findIndex(r => r.id === reviewId)
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

      // Update local state - remove review from all relevant places
      removeReviewFromState(reviewId)

      return true
    } catch (err) {
      console.error('❌ Error deleting review:', err)
      error.value = err.response?.data?.message || 'Eroare la ștergerea review-ului'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Profile Actions
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
      console.log('✅ Profile updated:', response.data)

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

  // Helper Methods for Local State Management
  const updateBookingInState = (bookingId, updatedBooking) => {
    // Update in dashboard
    if (dashboard.value?.upcoming_bookings) {
      const index = dashboard.value.upcoming_bookings.findIndex(b => b.id === bookingId)
      if (index !== -1) {
        dashboard.value.upcoming_bookings[index] = updatedBooking
      }
    }

    if (dashboard.value?.recent_bookings) {
      const index = dashboard.value.recent_bookings.findIndex(b => b.id === bookingId)
      if (index !== -1) {
        dashboard.value.recent_bookings[index] = updatedBooking
      }
    }

    // Update in bookings list
    if (bookings.value?.bookings) {
      const index = bookings.value.bookings.findIndex(b => b.id === bookingId)
      if (index !== -1) {
        bookings.value.bookings[index] = updatedBooking
      }
    }
  }

  const updateReviewInBookings = (bookingId, review) => {
    // Update in dashboard
    if (dashboard.value?.recent_bookings) {
      const booking = dashboard.value.recent_bookings.find(b => b.id === bookingId)
      if (booking) {
        booking.review = review
        booking.can_review = false
        booking.needs_review = false
        booking.has_review = true
      }
    }

    // Update in bookings list
    if (bookings.value?.bookings) {
      const booking = bookings.value.bookings.find(b => b.id === bookingId)
      if (booking) {
        booking.review = review
        booking.can_review = false
        booking.needs_review = false
        booking.has_review = true
      }
    }

    // Update pending reviews count in dashboard stats
    if (dashboard.value?.stats) {
      dashboard.value.stats.pending_reviews = Math.max(0, (dashboard.value.stats.pending_reviews || 1) - 1)
    }
  }

  const removeReviewFromState = (reviewId) => {
    // Find booking that had this review and update it
    let bookingId = null

    // Check dashboard recent bookings
    if (dashboard.value?.recent_bookings) {
      const booking = dashboard.value.recent_bookings.find(b => b.review?.id === reviewId)
      if (booking) {
        bookingId = booking.id
        booking.review = null
        booking.can_review = true
        booking.needs_review = true
        booking.has_review = false
      }
    }

    // Check bookings list
    if (bookings.value?.bookings) {
      const booking = bookings.value.bookings.find(b => b.review?.id === reviewId)
      if (booking) {
        bookingId = booking.id
        booking.review = null
        booking.can_review = true
        booking.needs_review = true
        booking.has_review = false
      }
    }

    // Update pending reviews count
    if (dashboard.value?.stats) {
      dashboard.value.stats.pending_reviews = (dashboard.value.stats.pending_reviews || 0) + 1
    }

    // Remove from reviews array
    if (reviews.value) {
      const index = reviews.value.findIndex(r => r.id === reviewId)
      if (index !== -1) {
        reviews.value.splice(index, 1)
      }
    }
  }

  // Tutor-related methods for booking process
  const getTutorAvailability = async (tutorId) => {
    try {
      console.log('📅 Loading tutor availability for:', tutorId)
      const response = await api.get(`tutors/${tutorId}/availability`)
      return response.data
    } catch (err) {
      console.error('❌ Error fetching tutor availability:', err)
      throw new Error('Nu am putut încărca disponibilitatea tutorului')
    }
  }

  const getTutorBusySlots = async (tutorId) => {
    try {
      console.log('🚫 Loading tutor busy slots for:', tutorId)
      const response = await api.get(`tutors/${tutorId}/busy-slots`)
      return response.data
    } catch (err) {
      console.error('❌ Error fetching tutor busy slots:', err)
      throw new Error('Nu am putut încărca orele ocupate ale tutorului')
    }
  }

  // Reviews management
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
          const reviewsFromBookings = (bookingsResponse.bookings || [])
            .filter(booking => booking.review)
            .map(booking => booking.review)

          reviews.value = reviewsFromBookings
          return { reviews: reviewsFromBookings }
        } catch (bookingsErr) {
          console.error('❌ Error extracting reviews from bookings:', bookingsErr)
        }
      }

      error.value = err.response?.data?.message || 'Eroare la încărcarea review-urilor'
      throw err
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
        return null
      }
      console.error('❌ Error loading booking review:', err)
      throw err
    }
  }

  // Reset store
  const $reset = () => {
    dashboard.value = null
    bookings.value = null
    profile.value = null
    reviews.value = []
    loading.value = false
    error.value = null
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
    getBooking,
    createBooking,
    updateBooking,
    cancelBooking,
    submitReview,
    updateReview,
    deleteReview,
    getProfile,
    updateProfile,
    getTutorAvailability,
    getTutorBusySlots,
    getReviews,
    getBookingReview,
    $reset
  }
})
