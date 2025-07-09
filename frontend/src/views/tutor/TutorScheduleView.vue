<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Program</h1>
            <p class="text-gray-600 mt-1">Vezi programul tău săptămânal și lecțiile viitoare</p>
          </div>
          <div class="flex items-center space-x-4">
            <router-link
              :to="{ name: 'tutor-dashboard' }"
              class="bg-white border border-gray-300 text-gray-700 font-medium px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors"
            >
              ← Înapoi la dashboard
            </router-link>
            <router-link
              :to="{ name: 'tutor-availability' }"
              class="bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold px-6 py-3 rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              Editează disponibilitatea
            </router-link>
          </div>
        </div>
      </div>

      <!-- Week Navigation -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex items-center justify-between">
          <button
            @click="previousWeek"
            class="flex items-center text-gray-600 hover:text-gray-900 transition-colors"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Săptămâna anterioară
          </button>

          <div class="text-center">
            <h2 class="text-xl font-semibold text-gray-900">{{ weekDateRange }}</h2>
            <p class="text-sm text-gray-600">Săptămâna {{ weekNumber }}</p>
          </div>

          <button
            @click="nextWeek"
            class="flex items-center text-gray-600 hover:text-gray-900 transition-colors"
          >
            Săptămâna viitoare
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
        </div>

        <div class="mt-4 flex justify-center">
          <button
            @click="goToCurrentWeek"
            class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors"
          >
            Mergi la săptămâna curentă
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12">
        <div class="flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span class="ml-3 text-gray-600">Se încarcă programul...</span>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-white rounded-2xl shadow-sm border border-red-200 p-8">
        <div class="text-center">
          <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Eroare la încărcarea programului</h3>
          <p class="text-gray-600 mb-4">{{ error }}</p>
          <button
            @click="loadWeekSchedule"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
          >
            Încearcă din nou
          </button>
        </div>
      </div>

      <!-- Weekly Schedule -->
      <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="grid grid-cols-7 divide-x divide-gray-200">
          <!-- Day Headers -->
          <div
            v-for="day in weekSchedule"
            :key="day.date"
            class="p-4 bg-gray-50 text-center"
          >
            <div class="font-semibold text-gray-900">{{ day.day_name }}</div>
            <div class="text-sm text-gray-600">{{ formatDateShort(day.date) }}</div>
          </div>

          <!-- Day Content -->
          <div
            v-for="day in weekSchedule"
            :key="`content-${day.date}`"
            class="p-4 min-h-[400px] bg-white"
          >
            <!-- Available Slots -->
            <div v-if="day.available_slots.length > 0" class="mb-4">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Disponibil</h4>
              <div class="space-y-2">
                <div
                  v-for="slot in day.available_slots"
                  :key="slot"
                  class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-md"
                >
                  {{ slot }}
                </div>
              </div>
            </div>

            <!-- Bookings -->
            <div v-if="day.bookings.length > 0" class="mb-4">
              <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Lecții</h4>
              <div class="space-y-2">
                <div
                  v-for="booking in day.bookings"
                  :key="booking.id"
                  class="p-3 rounded-lg border cursor-pointer hover:shadow-sm transition-shadow"
                  :class="getBookingClasses(booking.status)"
                  @click="viewBookingDetails(booking)"
                >
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium">{{ booking.time }}</span>
                    <span class="text-xs px-2 py-1 rounded-full" :class="getStatusClasses(booking.status)">
                      {{ getStatusText(booking.status) }}
                    </span>
                  </div>
                  <div class="text-sm text-gray-700 mb-1">{{ booking.subject }}</div>
                  <div class="text-xs text-gray-600">{{ booking.student }}</div>
                  <div class="flex items-center justify-between mt-2">
                    <span class="text-xs text-gray-500">
                      {{ getLessonTypeIcon(booking.type) }} {{ booking.type === 'online' ? 'Online' : 'Față în față' }}
                    </span>
                    <span class="text-xs font-medium text-gray-700">{{ formatDuration(booking.duration) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="day.available_slots.length === 0 && day.bookings.length === 0" class="text-center py-8">
              <div class="text-gray-400 text-sm">Nicio activitate</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Today's Lessons Summary -->
      <div v-if="todaysLessons.length > 0" class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Lecțiile de azi</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="lesson in todaysLessons"
            :key="lesson.id"
            class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow cursor-pointer"
            @click="viewBookingDetails(lesson)"
          >
            <div class="flex items-center justify-between mb-3">
              <span
                class="text-xs font-medium px-2 py-1 rounded-full"
                :class="getStatusClasses(lesson.status)"
              >
                {{ getStatusText(lesson.status) }}
              </span>
              <span class="text-sm font-medium text-gray-900">{{ lesson.time }}</span>
            </div>

            <h4 class="font-semibold text-gray-900 mb-1">{{ lesson.subject }}</h4>
            <p class="text-sm text-gray-600 mb-2">cu {{ lesson.student }}</p>

            <div class="flex items-center justify-between">
              <span
                class="text-xs font-medium px-2 py-1 rounded-full"
                :class="lesson.type === 'online' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
              >
                {{ lesson.type === 'online' ? 'Online' : 'Față în față' }}
              </span>
              <span class="text-xs text-gray-500">{{ formatDuration(lesson.duration) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Booking Details Modal -->
    <div
      v-if="selectedBooking"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click="closeBookingDetails"
    >
      <div
        class="bg-white rounded-2xl max-w-md w-full p-6"
        @click.stop
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Detalii lecție</h3>
          <button
            @click="closeBookingDetails"
            class="text-gray-400 hover:text-gray-600 p-2 rounded-lg transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="text-sm font-medium text-gray-600">Materia</label>
            <p class="text-gray-900">{{ selectedBooking.subject }}</p>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-600">Student</label>
            <p class="text-gray-900">{{ selectedBooking.student }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-600">Data</label>
              <p class="text-gray-900">{{ formatDate(selectedBooking.scheduled_at) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-600">Ora</label>
              <p class="text-gray-900">{{ selectedBooking.time }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-600">Tip</label>
              <p class="text-gray-900">{{ selectedBooking.type === 'online' ? 'Online' : 'Față în față' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-600">Durată</label>
              <p class="text-gray-900">{{ formatDuration(selectedBooking.duration) }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-600">Status</label>
              <p class="text-gray-900">{{ getStatusText(selectedBooking.status) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-600">Plată</label>
              <p class="text-gray-900">{{ selectedBooking.payment_method === 'cash' ? 'Cash' : 'Card' }}</p>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex space-x-3">
          <button
            v-if="selectedBooking.status === 'pending'"
            @click="confirmBooking(selectedBooking.id)"
            class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors"
          >
            Confirmă
          </button>
          <button
            v-if="selectedBooking.status === 'confirmed' && canCompleteBooking(selectedBooking)"
            @click="completeBooking(selectedBooking.id)"
            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors"
          >
            Marchează ca finalizată
          </button>
          <button
            v-if="selectedBooking.status === 'confirmed' && selectedBooking.type === 'online'"
            @click="startOnlineLesson(selectedBooking)"
            class="flex-1 bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors"
          >
            Pornește lecția
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useTutorStore } from '@/stores/tutor'
import api from '@/services/api'

const tutorStore = useTutorStore()

// Reactive state
const currentWeekStart = ref(new Date())
const weekSchedule = ref([])
const selectedBooking = ref(null)
const loading = ref(false)
const error = ref(null)

// Computed properties
const weekDateRange = computed(() => {
  const start = new Date(currentWeekStart.value)
  const end = new Date(start)
  end.setDate(end.getDate() + 6)

  return `${start.toLocaleDateString('ro-RO', { day: 'numeric', month: 'long' })} - ${end.toLocaleDateString('ro-RO', { day: 'numeric', month: 'long', year: 'numeric' })}`
})

const weekNumber = computed(() => {
  const start = new Date(currentWeekStart.value)
  const startOfYear = new Date(start.getFullYear(), 0, 1)
  const pastDaysOfYear = (start - startOfYear) / 86400000
  return Math.ceil((pastDaysOfYear + startOfYear.getDay() + 1) / 7)
})

const todaysLessons = computed(() => {
  const today = new Date().toDateString()
  const todaySchedule = weekSchedule.value.find(day => new Date(day.date).toDateString() === today)
  return todaySchedule ? todaySchedule.bookings : []
})

// Methods
const getCurrentWeekStart = () => {
  const today = new Date()
  const dayOfWeek = today.getDay()
  const diff = today.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1) // Adjust for Monday start
  return new Date(today.setDate(diff))
}

const loadWeekSchedule = async () => {
  loading.value = true
  error.value = null

  try {
    const weekStart = currentWeekStart.value.toISOString().split('T')[0]
    const response = await tutorStore.getWeekSchedule(weekStart)
    weekSchedule.value = response.schedule
  } catch (err) {
    error.value = err.message || 'Eroare la încărcarea programului'
    console.error('Error loading week schedule:', err)
  } finally {
    loading.value = false
  }
}

const previousWeek = () => {
  const newDate = new Date(currentWeekStart.value)
  newDate.setDate(newDate.getDate() - 7)
  currentWeekStart.value = newDate
  loadWeekSchedule()
}

const nextWeek = () => {
  const newDate = new Date(currentWeekStart.value)
  newDate.setDate(newDate.getDate() + 7)
  currentWeekStart.value = newDate
  loadWeekSchedule()
}

const goToCurrentWeek = () => {
  currentWeekStart.value = getCurrentWeekStart()
  loadWeekSchedule()
}

// Booking actions
const viewBookingDetails = (booking) => {
  selectedBooking.value = booking
}

const closeBookingDetails = () => {
  selectedBooking.value = null
}

const confirmBooking = async (bookingId) => {
  try {
    await tutorStore.confirmBooking(bookingId)
    await loadWeekSchedule()
    closeBookingDetails()
    alert('Lecția a fost confirmată!')
  } catch (error) {
    console.error('Error confirming booking:', error)
    alert('Eroare la confirmarea lecției.')
  }
}

const completeBooking = async (bookingId) => {
  try {
    await tutorStore.completeBooking(bookingId)
    await loadWeekSchedule()
    closeBookingDetails()
    alert('Lecția a fost marcată ca finalizată!')
  } catch (error) {
    console.error('Error completing booking:', error)
    alert('Eroare la finalizarea lecției.')
  }
}

const startOnlineLesson = (booking) => {
  alert('Funcționalitatea pentru lecții online va fi disponibilă în curând!')
  // Here you would typically open a video call or redirect to lesson interface
}

const canCompleteBooking = (booking) => {
  const now = new Date()
  const lessonTime = new Date(booking.scheduled_at)
  return now > lessonTime
}

// Utility functions
const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ro-RO', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const formatDateShort = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ro-RO', {
    day: 'numeric',
    month: 'short'
  })
}

const formatDuration = (minutes) => {
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  if (hours > 0) {
    return `${hours}h ${mins > 0 ? mins + 'm' : ''}`
  }
  return `${mins}m`
}

const getStatusText = (status) => {
  const texts = {
    pending: 'În așteptare',
    confirmed: 'Confirmată',
    completed: 'Finalizată',
    cancelled: 'Anulată'
  }
  return texts[status] || status
}

const getStatusClasses = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getBookingClasses = (status) => {
  const classes = {
    pending: 'border-yellow-200 bg-yellow-50',
    confirmed: 'border-green-200 bg-green-50',
    completed: 'border-blue-200 bg-blue-50',
    cancelled: 'border-red-200 bg-red-50'
  }
  return classes[status] || 'border-gray-200 bg-gray-50'
}

const getLessonTypeIcon = (lessonType) => {
  return lessonType === 'online' ? '💻' : '📍'
}

// Lifecycle
onMounted(() => {
  currentWeekStart.value = getCurrentWeekStart()
  loadWeekSchedule()
})
</script>
