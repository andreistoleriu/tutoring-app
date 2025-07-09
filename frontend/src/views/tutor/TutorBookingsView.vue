<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Rezervări</h1>
            <p class="text-gray-600 mt-1">Gestionează toate rezervările tale</p>
          </div>
          <router-link :to="{ name: 'tutor-dashboard' }"
            class="bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors py-2 px-4">
            ← Înapoi la dashboard
          </router-link>
        </div>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select v-model="filters.status" @change="applyFilters"
            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Toate</option>
            <option value="pending">În așteptare</option>
            <option value="confirmed">Confirmate</option>
            <option value="completed">Finalizate</option>
            <option value="cancelled">Anulate</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tip lecție</label>
          <select v-model="filters.lesson_type" @change="applyFilters"
            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Toate</option>
            <option value="online">Online</option>
            <option value="in_person">Față în față</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">De la data</label>
          <input type="date" v-model="filters.date_from" @change="applyFilters"
            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Până la data</label>
          <input type="date" v-model="filters.date_to" @change="applyFilters"
            class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-xl">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">În așteptare</p>
              <p class="text-2xl font-bold text-gray-900">{{ getBookingsByStatus('pending').length }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-xl">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Confirmate</p>
              <p class="text-2xl font-bold text-gray-900">{{ getBookingsByStatus('confirmed').length }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-xl">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Finalizate</p>
              <p class="text-2xl font-bold text-gray-900">{{ getBookingsByStatus('completed').length }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-xl">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Anulate</p>
              <p class="text-2xl font-bold text-gray-900">{{ getBookingsByStatus('cancelled').length }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Bookings List -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">
              Lista rezervărilor ({{ filteredBookings.length }})
            </h2>
            <div class="flex items-center space-x-2">
              <button @click="refreshBookings" :disabled="loading"
                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                  </path>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div class="p-6">
          <!-- Loading State -->
          <div v-if="loading" class="space-y-4">
            <div v-for="i in 5" :key="i"
              class="animate-pulse flex items-center space-x-4 p-4 border border-gray-200 rounded-xl">
              <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
              <div class="flex-1">
                <div class="h-4 bg-gray-200 rounded mb-2"></div>
                <div class="h-3 bg-gray-200 rounded w-2/3"></div>
              </div>
            </div>
          </div>

          <!-- No Bookings -->
          <div v-else-if="filteredBookings.length === 0" class="text-center py-12">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nicio rezervare găsită</h3>
            <p class="text-gray-600">Încearcă să schimbi filtrele pentru a vedea rezervările.</p>
          </div>

          <!-- Bookings List -->
          <div v-else class="space-y-4">
            <div v-for="booking in filteredBookings" :key="booking.id"
              class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
              <div class="flex items-start justify-between">
                <div class="flex items-start space-x-4">
                  <!-- Student Avatar -->
                  <div
                    class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl flex items-center justify-center text-white font-semibold">
                    {{ getInitials(booking.student?.full_name || `${booking.student?.first_name}
                    ${booking.student?.last_name}`) }}
                  </div>

                  <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                      <h3 class="text-lg font-semibold text-gray-900">
                        {{ booking.student?.full_name || `${booking.student?.first_name} ${booking.student?.last_name}`
                        }}
                      </h3>
                      <span :class="getStatusClasses(booking.status)"
                        class="px-3 py-1 rounded-full text-xs font-medium">
                        {{ getStatusText(booking.status) }}
                      </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                      <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                          </path>
                        </svg>
                        <span>{{ booking.subject?.name }}</span>
                      </div>

                      <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                          </path>
                        </svg>
                        <span>{{ formatDateTime(booking.scheduled_at) }}</span>
                      </div>

                      <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                          </path>
                        </svg>
                        <span>{{ booking.price }} RON ({{ booking.lesson_type === 'online' ? 'Online' : 'Față în față'
                          }})</span>
                      </div>
                    </div>

                    <!-- Contact Info -->
                    <div v-if="booking.student?.email || booking.student?.phone" class="mt-3 text-sm text-gray-500">
                      <span v-if="booking.student.email">{{ booking.student.email }}</span>
                      <span v-if="booking.student.email && booking.student.phone"> • </span>
                      <span v-if="booking.student.phone">{{ booking.student.phone }}</span>
                    </div>

                    <!-- Student Notes -->
                    <div v-if="booking.student_notes" class="mt-3 p-3 bg-gray-50 rounded-xl">
                      <p class="text-sm text-gray-700">
                        <strong>Notă de la student:</strong> {{ booking.student_notes }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col space-y-2">
                  <!-- Pending actions -->
                  <div v-if="booking.status === 'pending'" class="flex space-x-2">
                    <button @click="confirmBooking(booking.id)" :disabled="processing"
                      class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-xl hover:bg-green-700 transition-colors disabled:opacity-50">
                      Confirmă
                    </button>
                    <button @click="rejectBooking(booking.id)" :disabled="processing"
                      class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-xl hover:bg-red-700 transition-colors disabled:opacity-50">
                      Respinge
                    </button>
                  </div>

                  <!-- Cash payment confirmation -->
                  <div
                    v-if="booking.status === 'completed' && booking.payment_method === 'cash' && booking.payment_status === 'pending'"
                    class="flex space-x-2">
                    <button @click="confirmCashPayment(booking.id)" :disabled="processing"
                      class="px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-xl hover:bg-orange-700 transition-colors disabled:opacity-50">
                      Confirmă plata cash
                    </button>
                  </div>

                  <!-- Complete lesson -->
                  <div v-if="booking.status === 'confirmed' && isPastLesson(booking.scheduled_at)"
                    class="flex space-x-2">
                    <button @click="completeBooking(booking.id)" :disabled="processing"
                      class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors disabled:opacity-50">
                      Marchează ca finalizată
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

// Replace the script section in your TutorBookingsView.vue

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTutorStore } from '@/stores/tutor'
import api from '@/services/api'

const router = useRouter()
const tutorStore = useTutorStore()

// Reactive data
const loading = ref(false)
const processing = ref(false)
const error = ref(null)
const bookings = ref([])
const pagination = ref({})

const filters = ref({
  status: '',
  lesson_type: '', // changed from 'type' to 'lesson_type'
  date_from: '', // changed from 'dateFrom'
  date_to: '', // changed from 'dateTo'
  page: 1
})

// Computed properties
const filteredBookings = computed(() => {
  return bookings.value || []
})

// Methods
const loadBookings = async () => {
  loading.value = true
  error.value = null

  try {
    console.log('Loading bookings with filters:', filters.value)

    // Use the same API pattern as other working calls
    const response = await api.get('bookings', {
      params: {
        ...filters.value,
        per_page: 15
      }
    })

    console.log('Bookings response:', response.data)

    bookings.value = response.data.bookings || []
    pagination.value = response.data.pagination || {}

  } catch (err) {
    console.error('Error loading bookings:', err)
    error.value = 'Eroare la încărcarea rezervărilor. Încearcă din nou.'
    bookings.value = []
  } finally {
    loading.value = false
  }
}

const refreshBookings = () => {
  loadBookings()
}

const applyFilters = () => {
  filters.value.page = 1 // Reset to first page when filtering
  loadBookings()
}

const clearFilters = () => {
  filters.value = {
    status: '',
    lesson_type: '',
    date_from: '',
    date_to: '',
    page: 1
  }
  loadBookings()
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    filters.value.page = page
    loadBookings()
  }
}

// Booking actions
const confirmBooking = async (bookingId) => {
  processing.value = true
  try {
    console.log('Confirming booking:', bookingId)

    // Use the same API pattern
    await api.patch(`bookings/${bookingId}/confirm`)

    // Reload bookings to get updated data
    await loadBookings()

    alert('Rezervarea a fost confirmată cu succes!')
  } catch (error) {
    console.error('Error confirming booking:', error)
    alert('Eroare la confirmarea rezervării. Încearcă din nou.')
  } finally {
    processing.value = false
  }
}

const rejectBooking = async (bookingId) => {
  if (!confirm('Ești sigur că vrei să respingi această rezervare?')) {
    return
  }

  processing.value = true
  try {
    console.log('Rejecting booking:', bookingId)

    // Use the same API pattern
    await api.patch(`bookings/${bookingId}/cancel`)

    // Reload bookings to get updated data
    await loadBookings()

    alert('Rezervarea a fost respinsă.')
  } catch (error) {
    console.error('Error rejecting booking:', error)
    alert('Eroare la respingerea rezervării. Încearcă din nou.')
  } finally {
    processing.value = false
  }
}

const confirmCashPayment = async (bookingId) => {
  processing.value = true
  try {
    console.log('Confirming cash payment for booking:', bookingId)

    // Use the same API pattern
    await api.patch(`bookings/${bookingId}/confirm-payment`)

    // Reload bookings to get updated data
    await loadBookings()

    alert('Plata cash a fost confirmată!')
  } catch (error) {
    console.error('Error confirming cash payment:', error)
    alert('Eroare la confirmarea plății cash. Încearcă din nou.')
  } finally {
    processing.value = false
  }
}

const completeBooking = async (bookingId) => {
  processing.value = true
  try {
    console.log('Completing booking:', bookingId)

    // Use the same API pattern
    await api.patch(`bookings/${bookingId}/complete`)

    // Reload bookings to get updated data
    await loadBookings()

    alert('Lecția a fost marcată ca finalizată!')
  } catch (error) {
    console.error('Error completing booking:', error)
    alert('Eroare la finalizarea lecției. Încearcă din nou.')
  } finally {
    processing.value = false
  }
}

// Helper functions
const getBookingsByStatus = (status) => {
  return bookings.value.filter(booking => booking.status === status)
}

const getInitials = (fullName) => {
  if (!fullName) return 'N/A'
  return fullName.split(' ').map(name => name[0]).join('').toUpperCase()
}

const getStatusText = (status) => {
  const statusMap = {
    pending: 'În așteptare',
    confirmed: 'Confirmată',
    completed: 'Finalizată',
    cancelled: 'Anulată'
  }
  return statusMap[status] || status
}

const getStatusClasses = (status) => {
  const classMap = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classMap[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateTime) => {
  if (!dateTime) return ''
  const date = new Date(dateTime)
  return date.toLocaleDateString('ro-RO', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatTime = (dateTime) => {
  if (!dateTime) return ''
  const date = new Date(dateTime)
  return date.toLocaleTimeString('ro-RO', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatDateTime = (dateTime) => {
  if (!dateTime) return ''
  return `${formatDate(dateTime)} la ${formatTime(dateTime)}`
}

const isPastLesson = (scheduledAt) => {
  return new Date(scheduledAt) < new Date()
}

const isLessonStartable = (scheduledAt) => {
  const lessonTime = new Date(scheduledAt)
  const now = new Date()
  const diffInMinutes = (lessonTime - now) / (1000 * 60)
  return diffInMinutes <= 15 && diffInMinutes >= -60 // Can start 15 min before, up to 1 hour after
}

// Lifecycle
onMounted(() => {
  console.log('TutorBookingsView mounted, loading bookings...')
  loadBookings()
})
</script>
