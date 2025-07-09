<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-7xl mx-auto py-8" style="padding-left: 1rem; padding-right: 1rem;">
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
              class="bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
              style="padding: 0.5rem 1rem;"
            >
              ← Înapoi la dashboard
            </router-link>
            <router-link
              :to="{ name: 'tutor-availability' }"
              class="bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
              style="padding: 0.75rem 1.5rem;"
            >
              Editează disponibilitatea
            </router-link>
          </div>
        </div>
      </div>

      <!-- Week Navigation -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8" style="padding: 1.5rem;">
        <div class="flex items-center justify-between">
          <button
            @click="previousWeek"
            class="flex items-center text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
            style="padding: 0.5rem 1rem;"
          >
            <svg class="w-5 h-5" style="margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Săptămâna trecută
          </button>

          <div class="text-center">
            <h2 class="text-xl font-semibold text-gray-900">{{ weekTitle }}</h2>
            <p class="text-sm text-gray-600">{{ weekRange }}</p>
          </div>

          <button
            @click="nextWeek"
            class="flex items-center text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
            style="padding: 0.5rem 1rem;"
          >
            Săptămâna viitoare
            <svg class="w-5 h-5" style="margin-left: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Today's Lessons -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8" style="padding: 1.5rem;">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Lecțiile de azi</h3>

        <div v-if="todaysLessons.length === 0" class="text-center" style="padding: 2rem 0;">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
          </div>
          <h4 class="text-lg font-medium text-gray-900 mb-2">Nicio lecție azi</h4>
          <p class="text-gray-600">Poți să te relaxezi sau să îți planifici lecțiile pentru mâine.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="lesson in todaysLessons"
            :key="lesson.id"
            class="border border-gray-200 rounded-xl hover:shadow-md transition-shadow cursor-pointer"
            style="padding: 1rem;"
            @click="openLessonDetails(lesson)"
          >
            <div class="flex items-center justify-between mb-3">
              <span
                class="text-xs font-medium rounded-full"
                style="padding: 0.25rem 0.5rem;"
                :class="getLessonClasses(lesson.status)"
              >
                {{ getStatusText(lesson.status) }}
              </span>
              <span class="text-sm font-medium text-gray-900">{{ lesson.time }}</span>
            </div>

            <h4 class="font-semibold text-gray-900 mb-1">{{ lesson.subject }}</h4>
            <p class="text-sm text-gray-600 mb-2">cu {{ lesson.student }}</p>

            <div class="flex items-center justify-between">
              <span
                class="text-xs font-medium rounded-full"
                style="padding: 0.25rem 0.5rem;"
                :class="lesson.type === 'online' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
              >
                {{ lesson.type === 'online' ? 'Online' : 'Față în față' }}
              </span>
              <span class="text-sm font-medium text-gray-900">{{ lesson.price }} RON</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Weekly Calendar Simple View -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="border-b border-gray-100" style="padding: 1.5rem;">
          <h3 class="text-lg font-semibold text-gray-900">Vedere săptămânală</h3>
        </div>

        <div style="padding: 1.5rem;">
          <div class="grid grid-cols-7 gap-4">
            <div
              v-for="day in weekDays"
              :key="day.date"
              class="text-center"
              :class="{ 'bg-blue-50 rounded-lg': isToday(day.date) }"
              style="padding: 0.5rem;"
            >
              <div class="font-medium text-gray-900 mb-2">{{ day.name }}</div>
              <div class="text-sm text-gray-600 mb-3">{{ formatDayDate(day.date) }}</div>
              <div v-if="isToday(day.date)" class="text-xs text-blue-600 font-medium mb-3">Azi</div>

              <!-- Lessons for this day -->
              <div class="space-y-2">
                <div
                  v-for="lesson in getLessonsForDay(day.date)"
                  :key="lesson.id"
                  class="text-xs rounded cursor-pointer"
                  style="padding: 0.5rem;"
                  :class="getLessonClasses(lesson.status)"
                  @click="openLessonDetails(lesson)"
                >
                  <div class="font-medium">{{ lesson.time }}</div>
                  <div>{{ lesson.subject }}</div>
                  <div>{{ lesson.student }}</div>
                </div>
              </div>

              <!-- Available slots indicator -->
              <div v-if="getAvailableSlotsForDay(day.date).length > 0" style="margin-top: 0.5rem;">
                <div class="text-xs text-green-600 bg-green-50 rounded" style="padding: 0.25rem;">
                  {{ getAvailableSlotsForDay(day.date).length }} sloturi disponibile
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Week Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200" style="padding: 1.5rem;">
          <div class="text-center">
            <div class="text-2xl font-bold text-blue-600 mb-2">{{ weekStats.totalLessons }}</div>
            <p class="text-sm text-gray-600">Lecții săptămâna aceasta</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200" style="padding: 1.5rem;">
          <div class="text-center">
            <div class="text-2xl font-bold text-green-600 mb-2">{{ weekStats.totalHours }}h</div>
            <p class="text-sm text-gray-600">Ore de predare</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200" style="padding: 1.5rem;">
          <div class="text-center">
            <div class="text-2xl font-bold text-purple-600 mb-2">{{ weekStats.totalEarnings }} RON</div>
            <p class="text-sm text-gray-600">Venituri estimate</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200" style="padding: 1.5rem;">
          <div class="text-center">
            <div class="text-2xl font-bold text-orange-600 mb-2">{{ weekStats.occupancyRate }}%</div>
            <p class="text-sm text-gray-600">Rata de ocupare</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Lesson Details Modal -->
    <div
      v-if="selectedLesson"
      class="fixed inset-0 flex items-center justify-center z-50"
      style="background-color: rgba(0, 0, 0, 0.5); padding: 1rem;"
      @click="closeLessonDetails"
    >
      <div
        class="bg-white rounded-2xl max-w-md w-full"
        style="padding: 1.5rem;"
        @click.stop
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Detalii lecție</h3>
          <button
            @click="closeLessonDetails"
            class="text-gray-400 hover:text-gray-600 rounded-lg"
            style="padding: 0.5rem;"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="text-sm font-medium text-gray-600">Materia</label>
            <p class="text-gray-900">{{ selectedLesson.subject }}</p>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-600">Student</label>
            <p class="text-gray-900">{{ selectedLesson.student }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-600">Data</label>
              <p class="text-gray-900">{{ formatDate(selectedLesson.date) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-600">Ora</label>
              <p class="text-gray-900">{{ selectedLesson.time }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-600">Tip</label>
              <p class="text-gray-900">{{ selectedLesson.type === 'online' ? 'Online' : 'Față în față' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-600">Preț</label>
              <p class="text-gray-900">{{ selectedLesson.price }} RON</p>
            </div>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-600">Status</label>
            <span
              class="inline-block text-xs font-medium rounded-full mt-1"
              style="padding: 0.25rem 0.5rem;"
              :class="getLessonClasses(selectedLesson.status)"
            >
              {{ getStatusText(selectedLesson.status) }}
            </span>
          </div>

          <div v-if="selectedLesson.notes" class="border-t pt-4">
            <label class="text-sm font-medium text-gray-600">Note</label>
            <p class="text-gray-900 text-sm">{{ selectedLesson.notes }}</p>
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button
            @click="closeLessonDetails"
            class="text-gray-600 hover:text-gray-800"
            style="padding: 0.5rem 1rem;"
          >
            Închide
          </button>
          <button
            v-if="selectedLesson.status === 'confirmed' && isLessonStartable(selectedLesson)"
            @click="startLesson(selectedLesson.id)"
            class="bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            style="padding: 0.5rem 1rem;"
          >
            Începe lecția
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

// Reactive data
const currentWeekStart = ref(new Date())
const selectedLesson = ref(null)
const lessons = ref([])
const loading = ref(false)
const error = ref(null)

// Get current week start (Monday)
const getCurrentWeekStart = () => {
  const today = new Date()
  const day = today.getDay()
  const diff = today.getDate() - day + (day === 0 ? -6 : 1) // Adjust when day is Sunday
  return new Date(today.setDate(diff))
}

// Load lessons for the current week
const loadWeekLessons = async () => {
  loading.value = true
  error.value = null

  try {
    console.log('Loading lessons for week starting:', currentWeekStart.value)

    // Calculate week range
    const weekStart = new Date(currentWeekStart.value)
    const weekEnd = new Date(weekStart)
    weekEnd.setDate(weekStart.getDate() + 6)

    // Format dates for API
    const startDate = weekStart.toISOString().split('T')[0]
    const endDate = weekEnd.toISOString().split('T')[0]

    console.log('Date range:', startDate, 'to', endDate)

    const response = await api.get('bookings', {
      params: {
        date_from: startDate,
        date_to: endDate,
        status: 'confirmed,pending,completed', // Get all relevant statuses
        per_page: 50 // Get more results for the week view
      }
    })

    console.log('Lessons response:', response.data)

    lessons.value = response.data.bookings || []

  } catch (err) {
    console.error('Error loading lessons:', err)
    error.value = 'Eroare la încărcarea lecțiilor'
    lessons.value = []
  } finally {
    loading.value = false
  }
}

// Computed properties
const weekDays = computed(() => {
  const days = []
  const start = new Date(currentWeekStart.value)

  for (let i = 0; i < 7; i++) {
    const date = new Date(start)
    date.setDate(start.getDate() + i)

    const dayLessons = lessons.value.filter(lesson => {
      const lessonDate = new Date(lesson.scheduled_at).toDateString()
      return lessonDate === date.toDateString()
    })

    days.push({
      name: ['Lun', 'Mar', 'Mie', 'Joi', 'Vin', 'Sâm', 'Dum'][i],
      date: date.toISOString().split('T')[0],
      lessons: dayLessons.sort((a, b) => new Date(a.scheduled_at) - new Date(b.scheduled_at))
    })
  }

  return days
})

const weekTitle = computed(() => {
  return `Săptămâna ${getWeekNumber(currentWeekStart.value)}`
})

const weekRange = computed(() => {
  const start = new Date(currentWeekStart.value)
  const end = new Date(start)
  end.setDate(start.getDate() + 6)

  return `${formatDate(start)} - ${formatDate(end)}`
})

// Navigation methods
const previousWeek = () => {
  const newDate = new Date(currentWeekStart.value)
  newDate.setDate(newDate.getDate() - 7)
  currentWeekStart.value = newDate
  loadWeekLessons()
}

const nextWeek = () => {
  const newDate = new Date(currentWeekStart.value)
  newDate.setDate(newDate.getDate() + 7)
  currentWeekStart.value = newDate
  loadWeekLessons()
}

const goToCurrentWeek = () => {
  currentWeekStart.value = getCurrentWeekStart()
  loadWeekLessons()
}

// Helper functions
const getWeekNumber = (date) => {
  const startDate = new Date(date.getFullYear(), 0, 1)
  const days = Math.floor((date - startDate) / (24 * 60 * 60 * 1000))
  return Math.ceil(days / 7)
}

const formatDate = (date) => {
  return date.toLocaleDateString('ro-RO', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
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

const formatDuration = (minutes) => {
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  if (hours > 0) {
    return `${hours}h ${mins > 0 ? mins + 'm' : ''}`
  }
  return `${mins}m`
}

const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    confirmed: 'bg-green-100 text-green-800 border-green-200',
    completed: 'bg-blue-100 text-blue-800 border-blue-200',
    cancelled: 'bg-red-100 text-red-800 border-red-200'
  }
  return colors[status] || 'bg-gray-100 text-gray-800 border-gray-200'
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

const getLessonTypeIcon = (lessonType) => {
  return lessonType === 'online' ? '💻' : '📍'
}

const getInitials = (fullName) => {
  if (!fullName) return 'N/A'
  return fullName.split(' ').map(name => name[0]).join('').toUpperCase()
}

// Lesson actions
const viewLessonDetails = (lesson) => {
  selectedLesson.value = lesson
}

const closeLessonDetails = () => {
  selectedLesson.value = null
}

const confirmLesson = async (lessonId) => {
  try {
    await api.patch(`bookings/${lessonId}/confirm`)
    await loadWeekLessons()
    alert('Lecția a fost confirmată!')
  } catch (error) {
    console.error('Error confirming lesson:', error)
    alert('Eroare la confirmarea lecției.')
  }
}

const completeLesson = async (lessonId) => {
  try {
    await api.patch(`bookings/${lessonId}/complete`)
    await loadWeekLessons()
    alert('Lecția a fost marcată ca finalizată!')
  } catch (error) {
    console.error('Error completing lesson:', error)
    alert('Eroare la finalizarea lecției.')
  }
}

const startLesson = (lesson) => {
  if (lesson.lesson_type === 'online') {
    // For online lessons, you might want to open a video call link
    alert('Funcționalitatea pentru lecții online va fi disponibilă în curând!')
  } else {
    // For in-person lessons, just show details
    viewLessonDetails(lesson)
  }
}

// Lifecycle
onMounted(() => {
  console.log('TutorScheduleView mounted')
  currentWeekStart.value = getCurrentWeekStart()
  loadWeekLessons()
})
</script>
