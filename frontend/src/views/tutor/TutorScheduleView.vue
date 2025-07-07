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

const router = useRouter()

// Reactive data
const currentWeekStart = ref(new Date())
const selectedLesson = ref(null)

// Sample lessons data
const lessons = ref([
  {
    id: 1,
    subject: 'Matematică',
    student: 'Maria Popescu',
    date: '2025-07-08',
    time: '10:00',
    duration: 60,
    type: 'online',
    status: 'confirmed',
    price: 75,
    notes: 'Pregătire pentru testul de săptămâna viitoare'
  },
  {
    id: 2,
    subject: 'Fizică',
    student: 'Ion Ionescu',
    date: '2025-07-08',
    time: '15:00',
    duration: 60,
    type: 'in-person',
    status: 'confirmed',
    price: 80,
    notes: 'Cinematica - probleme cu mișcarea uniformă'
  },
  {
    id: 3,
    subject: 'Chimie',
    student: 'Ana Georgescu',
    date: '2025-07-09',
    time: '11:00',
    duration: 60,
    type: 'online',
    status: 'pending',
    price: 70,
    notes: ''
  }
])

// Computed properties
const weekDays = computed(() => {
  const days = []
  const start = new Date(currentWeekStart.value)

  for (let i = 0; i < 7; i++) {
    const date = new Date(start)
    date.setDate(start.getDate() + i)
    days.push({
      name: ['Lun', 'Mar', 'Mie', 'Joi', 'Vin', 'Sâm', 'Dum'][i],
      date: date.toISOString().split('T')[0]
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

  return `${start.getDate()} ${getMonthName(start)} - ${end.getDate()} ${getMonthName(end)} ${end.getFullYear()}`
})

const todaysLessons = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return lessons.value
    .filter(lesson => lesson.date === today)
    .sort((a, b) => a.time.localeCompare(b.time))
})

const weekStats = computed(() => {
  return {
    totalLessons: 5,
    totalHours: 8,
    totalEarnings: 375,
    occupancyRate: 75
  }
})

// Methods
const getWeekNumber = (date) => {
  const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()))
  const dayNum = d.getUTCDay() || 7
  d.setUTCDate(d.getUTCDate() + 4 - dayNum)
  const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1))
  return Math.ceil((((d - yearStart) / 86400000) + 1) / 7)
}

const getMonthName = (date) => {
  const months = [
    'ianuarie', 'februarie', 'martie', 'aprilie', 'mai', 'iunie',
    'iulie', 'august', 'septembrie', 'octombrie', 'noiembrie', 'decembrie'
  ]
  return months[date.getMonth()]
}

const isToday = (dateString) => {
  const today = new Date().toISOString().split('T')[0]
  return dateString === today
}

const formatDayDate = (dateString) => {
  const date = new Date(dateString)
  return date.getDate()
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ro-RO', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getLessonsForDay = (dateString) => {
  return lessons.value.filter(lesson => lesson.date === dateString)
}

const getAvailableSlotsForDay = (dateString) => {
  const date = new Date(dateString)
  const dayOfWeek = date.getDay()
  if (dayOfWeek === 0 || dayOfWeek === 6) return []
  return ['09:00', '14:00', '16:00']
}

const getLessonClasses = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
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

const isLessonStartable = (lesson) => {
  return lesson.status === 'confirmed'
}

const previousWeek = () => {
  const newDate = new Date(currentWeekStart.value)
  newDate.setDate(newDate.getDate() - 7)
  currentWeekStart.value = newDate
}

const nextWeek = () => {
  const newDate = new Date(currentWeekStart.value)
  newDate.setDate(newDate.getDate() + 7)
  currentWeekStart.value = newDate
}

const openLessonDetails = (lesson) => {
  selectedLesson.value = lesson
}

const closeLessonDetails = () => {
  selectedLesson.value = null
}

const startLesson = (lessonId) => {
  closeLessonDetails()
  alert(`Funcționalitatea pentru lecția ${lessonId} va fi disponibilă în curând!`)
}

// Set current week start to Monday
const setWeekStart = (date) => {
  const d = new Date(date)
  const day = d.getDay()
  const diff = d.getDate() - day + (day === 0 ? -6 : 1)
  return new Date(d.setDate(diff))
}

// Lifecycle
onMounted(() => {
  currentWeekStart.value = setWeekStart(new Date())
})
</script>
