import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useStudentStore = defineStore('student', () => {
  // State
  const dashboard = ref(null)
  const profile = ref(null)
  const bookings = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Computed
  const totalLessons = computed(() => dashboard.value?.stats?.completed_lessons || 0)
  const totalSpent = computed(() => dashboard.value?.stats?.total_spent || 0)
  const upcomingLessonsCount = computed(() => dashboard.value?.stats?.upcoming_lessons || 0)
  const pendingReviewsCount = computed(() => dashboard.value?.stats?.pending_reviews || 0)

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
      console.error('Dashboard error:', err)
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
    loading.value = false
    error.value = null
  }

  return {
    // State
    dashboard,
    profile,
    bookings,
    loading,
    error,

    // Computed
    totalLessons,
    totalSpent,
    upcomingLessonsCount,
    pendingReviewsCount,

    // Actions
    getDashboard,
    clearError,
    $reset
  }
})
