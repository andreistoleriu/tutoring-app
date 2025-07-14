// Create or update frontend/src/stores/student.js

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
      const response = await api.get('student/dashboard')
      dashboard.value = response.data
      return response.data
    } catch (err) {
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

      console.log('📋 Raw API response:', response.data)
      console.log('📋 Bookings count:', response.data.bookings?.length || 0)

      // Store the complete response data structure
      bookings.value = response.data

      return response.data
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
      const response = await api.get('student/profile')
      profile.value = response.data.profile
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
      const response = await api.put('student/profile', profileData)
      profile.value = response.data.profile
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la actualizarea profilului'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Booking Methods
  const createBooking = async (bookingData) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('bookings', bookingData)

      // Update dashboard if available
      if (dashboard.value?.upcoming_bookings) {
        dashboard.value.upcoming_bookings.push(response.data.booking)
      }

      return response.data
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Eroare la crearea rezervării'
      error.value = errorMessage
      throw new Error(errorMessage)
    } finally {
      loading.value = false
    }
  }

  const getBooking = async (bookingId) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get(`bookings/${bookingId}`)
      return response.data.booking
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea rezervării'
      throw err
    } finally {
      loading.value = false
    }
  }

  const cancelBooking = async (bookingId, reason = null) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.patch(`bookings/${bookingId}/cancel`, {
        cancellation_reason: reason || 'Anulată de student',
      })

      // Update local state
      if (dashboard.value?.upcoming_bookings) {
        const bookingIndex = dashboard.value.upcoming_bookings.findIndex((b) => b.id === bookingId)
        if (bookingIndex !== -1) {
          dashboard.value.upcoming_bookings.splice(bookingIndex, 1)
        }
      }

      if (bookings.value?.data) {
        const bookingIndex = bookings.value.data.findIndex((b) => b.id === bookingId)
        if (bookingIndex !== -1) {
          bookings.value.data[bookingIndex].status = 'cancelled'
          bookings.value.data[bookingIndex].cancelled_at = new Date().toISOString()
          bookings.value.data[bookingIndex].cancellation_reason = reason
        }
      }

      return response.data
    } catch (err) {
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

      // Update local reviews if we're storing them
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

  // Tutor-related methods for booking process
  const getTutorAvailability = async (tutorId) => {
    try {
      const response = await api.get(`tutors/${tutorId}/availability`)
      return response.data
    } catch (err) {
      console.error('Error fetching tutor availability:', err)
      throw new Error('Nu am putut încărca disponibilitatea tutorului')
    }
  }

  const getTutorBusySlots = async (tutorId) => {
    try {
      const response = await api.get(`tutors/${tutorId}/busy-slots`)
      return response.data
    } catch (err) {
      console.error('Error fetching tutor busy slots:', err)
      throw new Error('Nu am putut încărca programul tutorului')
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

      const response = await api.get(`student/reviews?${params.toString()}`)

      console.log('✅ Reviews loaded:', response.data)

      reviews.value = response.data.reviews || []
      return response.data
    } catch (err) {
      console.error('❌ Error loading reviews:', err)
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

  // Enhanced bookings method to include review status
  const getBookingsWithReviews = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      console.log('📋 Loading bookings with review status:', filters)

      const params = new URLSearchParams()
      Object.keys(filters).forEach((key) => {
        if (filters[key]) {
          params.append(key, filters[key])
        }
      })

      // Include reviews in the booking data
      params.append('include', 'review')

      const response = await api.get(`student/bookings?${params.toString()}`)

      console.log('✅ Bookings with reviews loaded:', response.data)

      // Enhance bookings with review status
      const enhancedBookings = (response.data.bookings || []).map((booking) => ({
        ...booking,
        can_review: booking.status === 'completed' && !booking.review,
        has_review: !!booking.review,
        needs_review: booking.status === 'completed' && !booking.review,
      }))

      bookings.value = { ...response.data, bookings: enhancedBookings }
      return { ...response.data, bookings: enhancedBookings }
    } catch (err) {
      console.error('❌ Error loading bookings with reviews:', err)
      error.value = err.response?.data?.message || 'Eroare la încărcarea rezervărilor'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateBooking = async (bookingId, updateData) => {
    loading.value = true
    error.value = null

    try {
      console.log('🔄 Updating booking with data:', updateData)
      const response = await api.put(`bookings/${bookingId}`, updateData)
      console.log('✅ Update response:', response.data)

      // Update local state
      if (dashboard.value?.upcoming_bookings) {
        const bookingIndex = dashboard.value.upcoming_bookings.findIndex((b) => b.id === bookingId)
        if (bookingIndex !== -1) {
          dashboard.value.upcoming_bookings[bookingIndex] = response.data.booking
        }
      }

      if (bookings.value?.data) {
        const bookingIndex = bookings.value.data.findIndex((b) => b.id === bookingId)
        if (bookingIndex !== -1) {
          bookings.value.data[bookingIndex] = response.data.booking
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
  // Reset store
  const $reset = () => {
    dashboard.value = null
    bookings.value = null
    profile.value = null
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

    // Actions
    getDashboard,
    getBookings,
    getProfile,
    updateProfile,
    createBooking,
    getBooking,
    cancelBooking,
    updateBooking,
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
