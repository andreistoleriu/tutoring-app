<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              Bună, {{ authStore.user?.first_name }}! 👋
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
              <p class="text-sm font-medium text-gray-600">Lecții totale</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.total_lessons }}</p>
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
              <p class="text-2xl font-bold text-gray-900">{{ stats.this_month }}</p>
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
              <p class="text-2xl font-bold text-gray-900">{{ stats.total_spent }} RON</p>
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
              <p class="text-2xl font-bold text-gray-900">{{ stats.pending_reviews }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Upcoming Lessons -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Upcoming Lessons -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50">
            <div class="p-6 border-b border-gray-100">
              <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Lecții viitoare</h2>
                <router-link
                  to="/bookings"
                  class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors"
                >
                  Vezi toate
                </router-link>
              </div>
            </div>

            <div class="p-6">
              <!-- Loading State -->
              <div v-if="loadingBookings" class="space-y-4">
                <div v-for="i in 3" :key="i" class="animate-pulse flex items-center space-x-4">
                  <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                  <div class="flex-1">
                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                    <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                  </div>
                </div>
              </div>

              <!-- No Upcoming Lessons -->
              <div v-else-if="upcomingBookings.length === 0" class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nicio lecție programată</h3>
                <p class="text-gray-600 mb-4">Începe să înveți găsind tutorul perfect pentru tine!</p>
                <router-link
                  to="/tutors"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                  Caută tutori
                </router-link>
              </div>

              <!-- Upcoming Lessons List -->
              <div v-else class="space-y-4">
                <div
                  v-for="booking in upcomingBookings"
                  :key="booking.id"
                  class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-blue-200 transition-colors"
                >
                  <div class="flex items-center space-x-4">
                    <!-- Tutor Avatar -->
                    <div class="relative">
                      <img
                        v-if="booking.tutor.profile_image"
                        :src="booking.tutor.profile_image"
                        :alt="booking.tutor.full_name"
                        class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm"
                      >
                      <div
                        v-else
                        class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white font-semibold shadow-sm"
                      >
                        {{ getInitials(booking.tutor.full_name) }}
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
                      <h4 class="font-semibold text-gray-900">{{ booking.subject.name }}</h4>
                      <p class="text-sm text-gray-600">cu {{ booking.tutor.full_name }}</p>
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

                  <!-- Actions -->
                  <div class="flex items-center space-x-2">
                    <span class="text-lg font-bold text-gray-900">{{ booking.price }} RON</span>
                    <button
                      v-if="booking.status === 'pending'"
                      @click="cancelBooking(booking.id)"
                      class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                      title="Anulează"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Lessons -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50">
            <div class="p-6 border-b border-gray-100">
              <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Lecții recente</h2>
                <router-link
                  to="/history"
                  class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors"
                >
                  Vezi istoricul
                </router-link>
              </div>
            </div>

            <div class="p-6">
              <!-- Loading State -->
              <div v-if="loadingHistory" class="space-y-4">
                <div v-for="i in 3" :key="i" class="animate-pulse flex items-center space-x-4">
                  <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                  <div class="flex-1">
                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                    <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                  </div>
                </div>
              </div>

              <!-- No Recent Lessons -->
              <div v-else-if="recentBookings.length === 0" class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
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
                        v-if="booking.tutor.profile_image"
                        :src="booking.tutor.profile_image"
                        :alt="booking.tutor.full_name"
                        class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm"
                      >
                      <div
                        v-else
                        class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white font-semibold text-sm shadow-sm"
                      >
                        {{ getInitials(booking.tutor.full_name) }}
                      </div>
                    </div>

                    <!-- Lesson Info -->
                    <div>
                      <h4 class="font-medium text-gray-900">{{ booking.subject.name }}</h4>
                      <p class="text-sm text-gray-600">cu {{ booking.tutor.full_name }}</p>
                      <p class="text-xs text-gray-500">{{ formatDate(booking.completed_at || booking.scheduled_at) }}</p>
                    </div>
                  </div>

                  <!-- Review Action -->
                  <div class="flex items-center space-x-3">
                    <span class="text-sm font-medium text-gray-900">{{ booking.price }} RON</span>
                    <button
                      v-if="booking.can_be_reviewed"
                      @click="openReviewModal(booking)"
                      class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-medium rounded-full hover:bg-yellow-200 transition-colors"
                    >
                      Lasă review
                    </button>
                    <div
                      v-else-if="booking.review"
                      class="flex items-center space-x-1 text-yellow-500"
                    >
                      <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                      <span class="text-sm">{{ booking.review.rating }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="space-y-6">
          <!-- Quick Actions -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Acțiuni rapide</h3>
            <div class="space-y-3">
              <router-link
                to="/tutors"
                class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Caută tutori
              </router-link>

              <router-link
                to="/bookings"
                class="w-full flex items-center justify-center px-4 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Lecțiile mele
              </router-link>

              <router-link
                to="/favorites"
                class="w-full flex items-center justify-center px-4 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                Tutori favoriți
              </router-link>
            </div>
          </div>

          <!-- Favorite Subjects -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Materiile tale preferate</h3>
            <div v-if="favoriteSubjects.length === 0" class="text-center py-4">
              <p class="text-gray-600 text-sm">Când vei rezerva lecții, vei vedea aici materiile tale preferate.</p>
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="subject in favoriteSubjects"
                :key="subject.id"
                class="flex items-center justify-between p-3 bg-blue-50 rounded-lg"
              >
                <span class="font-medium text-blue-900">{{ subject.name }}</span>
                <span class="text-sm text-blue-600">{{ subject.lessons_count }} lecții</span>
              </div>
            </div>
          </div>

          <!-- Learning Progress -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Progresul tău</h3>
            <div class="space-y-4">
              <!-- This Month Progress -->
              <div>
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                  <span>Lecții luna aceasta</span>
                  <span>{{ stats.this_month }}/10</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-gradient-to-r from-blue-600 to-purple-600 h-2 rounded-full transition-all duration-300"
                    :style="`width: ${Math.min((stats.this_month / 10) * 100, 100)}%`"
                  ></div>
                </div>
              </div>

              <!-- Learning Streak -->
              <div>
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                  <span>Progres săptămânal</span>
                  <span>{{ stats.weekly_streak }} zile</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full transition-all duration-300"
                    :style="`width: ${Math.min((stats.weekly_streak / 7) * 100, 100)}%`"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tips -->
          <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl border border-yellow-200 p-6">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                  </svg>
                </div>
              </div>
              <div>
                <h4 class="font-semibold text-yellow-900 mb-2">Sfat pentru învățare</h4>
                <p class="text-sm text-yellow-800">
                  {{ currentTip }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

export default {
  name: 'StudentDashboardView',
  setup() {
    const authStore = useAuthStore()

    const loadingBookings = ref(false)
    const loadingHistory = ref(false)
    const upcomingBookings = ref([])
    const recentBookings = ref([])
    const favoriteSubjects = ref([])

    const stats = reactive({
      total_lessons: 0,
      this_month: 0,
      total_spent: 0,
      pending_reviews: 0,
      weekly_streak: 3
    })

    const tips = [
     "Rezervă lecții în mod regulat pentru a menține continuitatea în învățare.",
     "Pregătește-te pentru lecții citind materialul în avans.",
     "Nu ezita să pui întrebări tutorului - acesta este motivul pentru care ești aici!",
     "Fă notițe în timpul lecțiilor pentru a reține informațiile importante.",
     "Exersează ceea ce ai învățat între lecții pentru a consolida cunoștințele.",
     "Lasă review-uri pentru tutorii tăi - îi ajută și pe alți studenți să aleagă.",
     "Încearcă să programezi lecții la aceeași oră pentru a crea o rutină.",
   ]

   const currentTip = computed(() => {
     const today = new Date()
     const tipIndex = today.getDay() % tips.length
     return tips[tipIndex]
   })

   const loadDashboardData = async () => {
     try {
       const response = await api.get('/student/dashboard')

       Object.assign(stats, response.data.stats)
       upcomingBookings.value = response.data.upcoming_bookings || []
       recentBookings.value = response.data.recent_bookings || []
       favoriteSubjects.value = response.data.favorite_subjects || []
     } catch (error) {
       console.error('Error loading dashboard data:', error)
       // Set some mock data for now
       stats.total_lessons = 0
       stats.this_month = 0
       stats.total_spent = 0
       stats.pending_reviews = 0
     }
   }

   const loadUpcomingBookings = async () => {
     loadingBookings.value = true
     try {
       const response = await api.get('/bookings?type=upcoming&per_page=5')
       upcomingBookings.value = response.data.bookings || []
     } catch (error) {
       console.error('Error loading upcoming bookings:', error)
       upcomingBookings.value = []
     } finally {
       loadingBookings.value = false
     }
   }

   const loadRecentBookings = async () => {
     loadingHistory.value = true
     try {
       const response = await api.get('/bookings?type=completed&per_page=5')
       recentBookings.value = response.data.bookings || []
     } catch (error) {
       console.error('Error loading recent bookings:', error)
       recentBookings.value = []
     } finally {
       loadingHistory.value = false
     }
   }

   const getInitials = (name) => {
     return name.split(' ').map(n => n[0]).join('').toUpperCase()
   }

   const formatDate = (dateString) => {
     const date = new Date(dateString)
     return date.toLocaleDateString('ro-RO', {
       day: 'numeric',
       month: 'long',
       year: 'numeric'
     })
   }

   const formatTime = (dateString) => {
     const date = new Date(dateString)
     return date.toLocaleTimeString('ro-RO', {
       hour: '2-digit',
       minute: '2-digit'
     })
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

   const cancelBooking = async (bookingId) => {
     if (!confirm('Ești sigur că vrei să anulezi această rezervare?')) {
       return
     }

     try {
       await api.patch(`/bookings/${bookingId}/cancel`, {
         cancellation_reason: 'Anulată de student'
       })

       // Remove from upcoming bookings
       const index = upcomingBookings.value.findIndex(b => b.id === bookingId)
       if (index !== -1) {
         upcomingBookings.value.splice(index, 1)
       }

       // Show success message (you can implement a toast notification here)
       alert('Rezervarea a fost anulată cu succes.')
     } catch (error) {
       console.error('Error cancelling booking:', error)
       alert('A apărut o eroare la anularea rezervării.')
     }
   }

   const openReviewModal = (booking) => {
     // This will be implemented when we create the review modal
     console.log('Open review modal for booking:', booking.id)
     alert(`Funcționalitatea de review va fi implementată curând pentru lecția de ${booking.subject.name}.`)
   }

   onMounted(() => {
     loadDashboardData()
     loadUpcomingBookings()
     loadRecentBookings()
   })

   return {
     authStore,
     stats,
     upcomingBookings,
     recentBookings,
     favoriteSubjects,
     loadingBookings,
     loadingHistory,
     currentTip,
     getInitials,
     formatDate,
     formatTime,
     getStatusLabel,
     cancelBooking,
     openReviewModal
   }
 }
}
</script>
