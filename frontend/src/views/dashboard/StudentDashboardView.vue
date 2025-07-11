<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              Bună, {{ authStore.user?.first_name || 'Student' }}! 👋
            </h1>
            <p class="text-gray-600 mt-1">Bine ai venit în dashboard-ul tău de student</p>
          </div>
          <div class="flex items-center space-x-4">
            <router-link
              to="/tutors"
              class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <span class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span>Caută tutori</span>
              </span>
            </router-link>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="ml-4 text-gray-600">Se încarcă dashboard-ul...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
        <div class="flex items-center">
          <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <h3 class="text-red-800 font-semibold">Eroare la încărcarea datelor</h3>
            <p class="text-red-600">{{ error }}</p>
          </div>
        </div>
        <button
          @click="loadDashboardData"
          class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
        >
          Încearcă din nou
        </button>
      </div>

      <!-- Main Content -->
      <div v-else>
        <!-- Quick Actions -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6 mb-8">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 mb-1">Acțiuni rapide</h2>
              <p class="text-gray-600 text-sm">Gestionează-ți lecțiile și rezervările</p>
            </div>
            <div class="flex items-center space-x-3">
              <!-- View All Bookings Button -->
              <router-link
                to="/student/bookings"
                class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-colors text-sm font-medium flex items-center space-x-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Toate rezervările</span>
                <span v-if="dashboardData?.stats?.total_bookings" class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                  {{ dashboardData.stats.total_bookings }}
                </span>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Total Lessons -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <div class="flex items-center">
              <div class="p-3 bg-blue-100 rounded-xl">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total lecții</p>
                <p class="text-2xl font-bold text-gray-900">{{ dashboardData?.stats?.total_lessons || 0 }}</p>
              </div>
            </div>
          </div>

          <!-- This Month -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <div class="flex items-center">
              <div class="p-3 bg-green-100 rounded-xl">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Luna aceasta</p>
                <p class="text-2xl font-bold text-gray-900">{{ dashboardData?.stats?.this_month || 0 }}</p>
              </div>
            </div>
          </div>

          <!-- Total Spent -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <div class="flex items-center">
              <div class="p-3 bg-purple-100 rounded-xl">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total investit</p>
                <p class="text-2xl font-bold text-gray-900">{{ dashboardData?.stats?.total_spent || 0 }} RON</p>
              </div>
            </div>
          </div>

          <!-- Pending Reviews -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <div class="flex items-center">
              <div class="p-3 bg-yellow-100 rounded-xl">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Review-uri restante</p>
                <p class="text-2xl font-bold text-gray-900">{{ dashboardData?.stats?.pending_reviews || 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Left Column - Upcoming Lessons -->
          <div class="lg:col-span-2 space-y-6">

            <!-- Upcoming Bookings -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                  <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  Lecții programate
                </h2>
                <div class="flex items-center space-x-3">
                  <button
                    @click="loadDashboardData"
                    class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center space-x-1"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Actualizează</span>
                  </button>
                  <router-link
                    to="/student/bookings"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex items-center space-x-1"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Vezi toate</span>
                  </router-link>
                </div>
              </div>

              <!-- Empty State when no upcoming bookings -->
              <div v-if="!upcomingBookings?.length" class="text-center py-8">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-gray-500 text-lg font-medium">Nu ai lecții programate</p>
                <p class="text-gray-400 mt-1">Caută un tutor și rezervă prima ta lecție!</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center mt-4">
                  <router-link
                    to="/tutors"
                    class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                  >
                    Caută tutori
                  </router-link>
                  <router-link
                    to="/student/bookings"
                    class="inline-block px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                  >
                    Vezi istoric rezervări
                  </router-link>
                </div>
              </div>

              <!-- Upcoming Bookings List -->
              <div v-else class="space-y-4">
                <div
                  v-for="booking in upcomingBookings"
                  :key="booking.id"
                  class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:shadow-md transition-shadow"
                >
                  <div class="flex items-center space-x-4">
                    <!-- Tutor Avatar -->
                    <div class="relative">
                      <img
                        v-if="booking.tutor?.profile_image"
                        :src="booking.tutor.profile_image"
                        :alt="getTutorName(booking.tutor)"
                        class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm"
                      >
                      <div
                        v-else
                        class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white font-semibold shadow-sm"
                      >
                        {{ getInitials(getTutorName(booking.tutor)) }}
                      </div>
                      <!-- Status indicator -->
                      <div
                        class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white"
                        :class="{
                          'bg-yellow-400': booking.status === 'pending',
                          'bg-green-500': booking.status === 'confirmed',
                          'bg-gray-400': booking.status === 'completed'
                        }"
                      ></div>
                    </div>

                    <!-- Lesson Info -->
                    <div>
                      <h4 class="font-semibold text-gray-900">{{ booking.subject?.name || 'Necunoscut' }}</h4>
                      <p class="text-sm text-gray-600">cu {{ getTutorName(booking.tutor) }}</p>
                      <div class="flex items-center space-x-4 mt-1 text-sm text-gray-500">
                        <span class="flex items-center space-x-1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                          </svg>
                          <span>{{ formatDate(booking.scheduled_at) }}</span>
                        </span>
                        <span class="flex items-center space-x-1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                          </svg>
                          <span>{{ formatTime(booking.scheduled_at) }}</span>
                        </span>
                        <span
                          class="px-2 py-1 rounded-full text-xs font-medium"
                          :class="{
                            'bg-yellow-100 text-yellow-800': booking.status === 'pending',
                            'bg-green-100 text-green-800': booking.status === 'confirmed',
                            'bg-gray-100 text-gray-800': booking.status === 'completed'
                          }"
                        >
                          {{ getStatusLabel(booking.status) }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-900">{{ booking.price || 0 }} RON</span>
                    <button
                      v-if="booking.status === 'pending' || booking.status === 'confirmed'"
                      @click="cancelBooking(booking.id)"
                      class="text-red-600 hover:text-red-700 text-sm"
                    >
                      Anulează
                    </button>
                  </div>
                </div>

                <!-- View All Link (when there are bookings) -->
                <div v-if="upcomingBookings.length >= 3" class="text-center pt-4 border-t border-gray-100">
                  <router-link
                    to="/student/bookings"
                    class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center justify-center space-x-1"
                  >
                    <span>Vezi toate rezervările</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  </router-link>
                </div>
              </div>
            </div>

            <!-- Recent Lessons -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
              <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Lecții recente
              </h2>

              <!-- Empty State for recent lessons -->
              <div v-if="!recentBookings?.length" class="text-center py-8">
                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nicio lecție finalizată</h3>
                <p class="text-gray-600">Când vei finaliza primele lecții, le vei vedea aici.</p>
              </div>

              <!-- Recent Lessons List -->
              <div v-else class="space-y-4">
                <div
                  v-for="booking in recentBookings"
                  :key="booking.id"
                  class="flex items-center justify-between p-4 border border-gray-200 rounded-xl"
                >
                  <div class="flex items-center space-x-4">
                    <!-- Tutor Avatar -->
                    <div>
                      <img
                        v-if="booking.tutor?.profile_image"
                        :src="booking.tutor.profile_image"
                        :alt="getTutorName(booking.tutor)"
                        class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm"
                      >
                      <div
                        v-else
                        class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white font-semibold text-sm shadow-sm"
                      >
                        {{ getInitials(getTutorName(booking.tutor)) }}
                      </div>
                    </div>

                    <!-- Lesson Info -->
                    <div>
                      <h4 class="font-medium text-gray-900">{{ booking.subject?.name || 'Necunoscut' }}</h4>
                      <p class="text-sm text-gray-600">cu {{ getTutorName(booking.tutor) }}</p>
                      <p class="text-xs text-gray-500">{{ formatDate(booking.completed_at || booking.scheduled_at) }}</p>
                    </div>
                  </div>

                  <!-- Review Action -->
                  <div class="flex items-center space-x-3">
                    <span class="text-sm font-medium text-gray-900">{{ booking.price || 0 }} RON</span>
                    <button
                      v-if="booking.can_review"
                      class="px-3 py-1 bg-yellow-500 text-white text-xs rounded-lg hover:bg-yellow-600 transition-colors"
                    >
                      Evaluează
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Tips & Activity -->
          <div class="space-y-6">
            <!-- Daily Tip -->
            <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl p-6 text-white">
              <h3 class="text-lg font-bold mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                Sfat pentru învățare
              </h3>
              <p class="text-blue-100 leading-relaxed">{{ currentTip }}</p>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistici rapide</h3>
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Lecții programate</span>
                  <span class="font-semibold">{{ upcomingBookings?.length || 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Lecții finalizate</span>
                  <span class="font-semibold">{{ recentBookings?.length || 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Review-uri de scris</span>
                  <span class="font-semibold text-yellow-600">{{ dashboardData?.stats?.pending_reviews || 0 }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useStudentStore } from '@/stores/student'
import api from '@/services/api'

// Composables
const authStore = useAuthStore()
const studentStore = useStudentStore()

// Reactive data
const loading = ref(false)
const error = ref(null)
const dashboardData = ref(null)
const upcomingBookings = ref([])
const recentBookings = ref([])

// Learning tips
const tips = [
  "Pregătește-te pentru lecții citind materialul în avans.",
  "Nu ezita să pui întrebări tutorului - acesta este motivul pentru care ești aici!",
  "Fă notițe în timpul lecțiilor pentru a reține informațiile importante.",
  "Exersează ceea ce ai învățat între lecții pentru a consolida cunoștințele.",
  "Lasă review-uri pentru tutorii tăi - îi ajută și pe alți studenți să aleagă.",
  "Încearcă să programezi lecții la aceeași oră pentru a crea o rutină.",
  "Revizuiește notițele tale după fiecare lecție pentru o mai bună reținere."
]

const currentTip = computed(() => {
  const today = new Date()
  const tipIndex = today.getDay() % tips.length
  return tips[tipIndex]
})

// Safe helper functions
const getInitials = (name) => {
  // Safe function to get initials
  if (!name || typeof name !== 'string') {
    return 'NA'
  }

  const cleanName = name.trim()
  if (!cleanName) {
    return 'NA'
  }

  const nameParts = cleanName.split(' ').filter(part => part.length > 0)

  if (nameParts.length === 0) {
    return 'NA'
  }

  if (nameParts.length === 1) {
    return nameParts[0][0].toUpperCase()
  }

  // First letter of first word + first letter of last word
  return nameParts[0][0].toUpperCase() + nameParts[nameParts.length - 1][0].toUpperCase()
}

const getTutorName = (tutor) => {
  // Safe function to get tutor name
  if (!tutor) {
    return 'Tutor necunoscut'
  }

  // Try different name combinations
  if (tutor.first_name || tutor.last_name) {
    const firstName = tutor.first_name || ''
    const lastName = tutor.last_name || ''
    const fullName = `${firstName} ${lastName}`.trim()
    if (fullName) return fullName
  }

  if (tutor.full_name) return tutor.full_name
  if (tutor.name) return tutor.name

  return 'Tutor necunoscut'
}

const formatDate = (dateString) => {
  if (!dateString) return ''

  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('ro-RO', {
      day: 'numeric',
      month: 'long'
    })
  } catch (error) {
    console.error('Error formatting date:', error)
    return 'Data necunoscută'
  }
}

const formatTime = (dateString) => {
  if (!dateString) return ''

  try {
    const date = new Date(dateString)
    return date.toLocaleTimeString('ro-RO', {
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (error) {
    console.error('Error formatting time:', error)
    return 'Ora necunoscută'
  }
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'În așteptare',
    confirmed: 'Confirmată',
    completed: 'Finalizată',
    cancelled: 'Anulată'
  }
  return labels[status] || status
}

// Methods
const loadDashboardData = async () => {
  loading.value = true
  error.value = null

  try {
    console.log('🔄 Loading dashboard data...')

    // Try to use studentStore first
    if (studentStore.getDashboard) {
      const response = await studentStore.getDashboard()
      dashboardData.value = response
      upcomingBookings.value = response.upcoming_bookings || []
      recentBookings.value = response.recent_bookings || []
    } else {
      // Fallback to direct API call
      const response = await api.get('student/dashboard')
      dashboardData.value = response.data
      upcomingBookings.value = response.data.upcoming_bookings || []
      recentBookings.value = response.data.recent_bookings || []
    }

    console.log('✅ Dashboard data loaded:', {
      upcoming: upcomingBookings.value.length,
      recent: recentBookings.value.length,
      stats: dashboardData.value?.stats
    })

  } catch (err) {
    console.error('❌ Error loading dashboard:', err)
    error.value = err.response?.data?.message || 'Eroare la încărcarea dashboard-ului'

    // Set empty fallback data
    dashboardData.value = {
      stats: {
        total_lessons: 0,
        this_month: 0,
        total_spent: 0,
        pending_reviews: 0,
        total_bookings: 0
      },
      upcoming_bookings: [],
      recent_bookings: []
    }
    upcomingBookings.value = []
    recentBookings.value = []
  } finally {
    loading.value = false
  }
}

const cancelBooking = async (bookingId) => {
  if (!confirm('Ești sigur că vrei să anulezi această rezervare?')) {
    return
  }

  try {
    console.log('🚫 Cancelling booking:', bookingId)

    if (studentStore.cancelBooking) {
      await studentStore.cancelBooking(bookingId, 'Anulată de student')
    } else {
      await api.patch(`bookings/${bookingId}/cancel`, {
        cancellation_reason: 'Anulată de student'
      })
    }

    // Reload dashboard data
    await loadDashboardData()
    alert('Rezervarea a fost anulată cu succes!')

  } catch (error) {
    console.error('❌ Error cancelling booking:', error)
    alert('Eroare la anularea rezervării. Încearcă din nou.')
  }
}

// Lifecycle
onMounted(async () => {
  console.log('🚀 StudentDashboardView mounted')
  await loadDashboardData()
})
</script>
