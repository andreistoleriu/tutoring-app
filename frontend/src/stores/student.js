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
    Object.keys(filters).forEach(key => {
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
        cancellation_reason: reason || 'Anulată de student'
      })

      // Update local state
      if (dashboard.value?.upcoming_bookings) {
        const bookingIndex = dashboard.value.upcoming_bookings.findIndex(b => b.id === bookingId)
        if (bookingIndex !== -1) {
          dashboard.value.upcoming_bookings.splice(bookingIndex, 1)
        }
      }

      if (bookings.value?.data) {
        const bookingIndex = bookings.value.data.findIndex(b => b.id === bookingId)
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

  const submitReview = async (bookingId, reviewData) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('reviews', {
        booking_id: bookingId,
        ...reviewData
      })

      // Update local booking state to show review was submitted
      if (bookings.value?.data) {
        const bookingIndex = bookings.value.data.findIndex(b => b.id === bookingId)
        if (bookingIndex !== -1) {
          bookings.value.data[bookingIndex].review = response.data.review
        }
      }

      return response.data
    } catch (err) {
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

const updateBooking = async (bookingId, updateData) => {
  loading.value = true
  error.value = null

  try {
    console.log('🔄 Updating booking with data:', updateData)
    const response = await api.put(`bookings/${bookingId}`, updateData)
    console.log('✅ Update response:', response.data)

    // Update local state
    if (dashboard.value?.upcoming_bookings) {
      const bookingIndex = dashboard.value.upcoming_bookings.findIndex(b => b.id === bookingId)
      if (bookingIndex !== -1) {
        dashboard.value.upcoming_bookings[bookingIndex] = response.data.booking
      }
    }

    if (bookings.value?.data) {
      const bookingIndex = bookings.value.data.findIndex(b => b.id === bookingId)
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
    getTutorAvailability,
    getTutorBusySlots,
    $reset
  }
})
