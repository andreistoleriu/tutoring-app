<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              Bună, {{ authStore.user?.first_name || 'Profesor' }}! 👋
            </h1>
            <p class="text-gray-600 mt-1">Bine ai venit în dashboard-ul tău de tutor</p>
          </div>
          <div class="flex items-center space-x-4">
            <router-link
              :to="{ name: 'tutor-profile' }"
              class="bg-white border border-gray-300 text-gray-700 font-medium px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors"
            >
              Editează profilul
            </router-link>
            <router-link
              :to="{ name: 'tutor-availability' }"
              class="bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold px-6 py-3 rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              Disponibilitate
            </router-link>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <span class="ml-3 text-gray-600">Se încarcă dashboard-ul...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Eroare la încărcarea datelor</h3>
            <p class="text-sm text-red-700 mt-1">{{ error }}</p>
          </div>
          <div class="ml-auto">
            <button
              @click="loadDashboardData"
              class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors"
            >
              Încearcă din nou
            </button>
          </div>
        </div>
      </div>

      <!-- Dashboard Content -->
      <div v-else>
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Total Earnings -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <div class="flex items-center">
              <div class="p-3 bg-green-100 rounded-xl">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Venituri totale</p>
                <p class="text-2xl font-bold text-gray-900">{{ safeNumber(stats.total_earnings) }} RON</p>
                <p class="text-xs text-green-600">+{{ safeNumber(stats.this_week_earnings) }} RON această săptămână</p>
              </div>
            </div>
          </div>

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
                <p class="text-2xl font-bold text-gray-900">{{ safeNumber(stats.total_lessons) }}</p>
                <p class="text-xs text-blue-600">+{{ safeNumber(stats.this_week_lessons) }} această săptămână</p>
              </div>
            </div>
          </div>

          <!-- Rating -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <div class="flex items-center">
              <div class="p-3 bg-yellow-100 rounded-xl">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Rating mediu</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatRating(stats.average_rating) }}/5</p>
                <p class="text-xs text-yellow-600">{{ safeNumber(stats.total_reviews) }} recenzii</p>
              </div>
            </div>
          </div>

          <!-- Students -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <div class="flex items-center">
              <div class="p-3 bg-purple-100 rounded-xl">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Studenți</p>
                <p class="text-2xl font-bold text-gray-900">{{ safeNumber(stats.total_students) }}</p>
                <p class="text-xs text-purple-600">Total</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Left Column -->
          <div class="lg:col-span-2 space-y-8">
            <!-- Pending Bookings -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50">
              <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                  <h2 class="text-xl font-semibold text-gray-900">Cereri de rezervare</h2>
                  <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                    {{ pendingBookings.length }}
                  </span>
                </div>
              </div>

              <div class="p-6">
                <!-- Loading State -->
                <div v-if="loadingPending" class="space-y-4">
                  <div v-for="i in 3" :key="i" class="animate-pulse flex items-center space-x-4">
                    <div class="w-8 h-8 bg-gray-200 rounded-full"></div>
                    <div class="flex-1">
                      <div class="h-4 bg-gray-200 rounded mb-2"></div>
                      <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                    </div>
                  </div>
                </div>

                <!-- No Pending Bookings -->
                <div v-else-if="pendingBookings.length === 0" class="text-center py-8">
                  <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </div>
                  <h3 class="text-lg font-medium text-gray-900 mb-2">Nicio cerere de rezervare</h3>
                  <p class="text-gray-600">Toate cererile au fost procesate.</p>
                </div>

                <!-- Compact Pending Bookings List -->
                <div v-else class="space-y-3">
                  <div
                    v-for="booking in pendingBookings"
                    :key="booking.id"
                    class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                  >
                    <div class="flex items-center justify-between">
                      <!-- Left: Student & Booking Info -->
                      <div class="flex items-center space-x-3 flex-1">
                        <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center">
                          <span class="text-white text-sm font-bold">{{ getInitials(getSafeName(booking)) }}</span>
                        </div>
                        <div class="flex-1">
                          <h4 class="font-semibold text-gray-900 text-sm">{{ getSafeName(booking) }}</h4>
                          <div class="flex items-center space-x-4 text-xs text-gray-600">
                            <span>📚 {{ booking.subject?.name || 'Materie necunoscută' }}</span>
                            <span>📅 {{ formatDate(booking.scheduled_at) }}</span>
                            <span>🕒 {{ formatTime(booking.scheduled_at) }}</span>
                            <span class="font-semibold text-green-600">{{ booking.price }} RON</span>
                          </div>
                          <div v-if="booking.student_notes" class="text-xs text-gray-500 mt-1 italic">
                            "{{ booking.student_notes.substring(0, 60) }}{{ booking.student_notes.length > 60 ? '...' : '' }}"
                          </div>
                          <div v-if="booking.student?.email" class="text-xs text-gray-500 mt-1">
                            📧 {{ booking.student.email }}
                          </div>
                        </div>
                      </div>

                      <!-- Right: Action Buttons -->
                      <div class="flex space-x-2">
                        <button
                          @click="confirmBooking(booking.id)"
                          :disabled="processing"
                          class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 transition-colors disabled:opacity-50"
                        >
                          ✅ Acceptă
                        </button>
                        <button
                          @click="rejectBooking(booking.id)"
                          :disabled="processing"
                          class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700 transition-colors disabled:opacity-50"
                        >
                          ❌ Respinge
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Upcoming Lessons -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50">
              <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                  <h2 class="text-xl font-semibold text-gray-900">Lecții viitoare</h2>
                  <router-link
                    :to="{ name: 'tutor-schedule' }"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors"
                  >
                    Vezi programul
                  </router-link>
                </div>
              </div>

              <div class="p-6">
                <!-- Loading State -->
                <div v-if="loadingUpcoming" class="space-y-4">
                  <div v-for="i in 3" :key="i" class="animate-pulse flex items-center space-x-4">
                    <div class="w-8 h-8 bg-gray-200 rounded-full"></div>
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
                  <p class="text-gray-600">Lecțiile confirmate vor apărea aici.</p>
                </div>

                <!-- Compact Upcoming Lessons List -->
                <div v-else class="space-y-3">
                  <div
                    v-for="booking in upcomingBookings"
                    :key="booking.id"
                    class="bg-green-50 border border-green-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                  >
                    <div class="flex items-center justify-between">
                      <!-- Left: Student & Booking Info -->
                      <div class="flex items-center space-x-3 flex-1">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                          <span class="text-white text-sm font-bold">{{ getInitials(getSafeName(booking)) }}</span>
                        </div>
                        <div class="flex-1">
                          <h4 class="font-semibold text-gray-900 text-sm">{{ getSafeName(booking) }}</h4>
                          <div class="flex items-center space-x-4 text-xs text-gray-600">
                            <span>📚 {{ booking.subject?.name || 'Materie necunoscută' }}</span>
                            <span>📅 {{ formatDate(booking.scheduled_at) }}</span>
                            <span>🕒 {{ formatTime(booking.scheduled_at) }}</span>
                            <span>{{ booking.lesson_type === 'online' ? '💻 Online' : '📍 Față în față' }}</span>
                            <span class="font-semibold text-green-600">{{ booking.price }} RON</span>
                          </div>
                          <div v-if="booking.student?.email" class="text-xs text-gray-500 mt-1">
                            📧 {{ booking.student.email }}
                          </div>
                        </div>
                      </div>

                      <!-- Right: Time & Action -->
                      <div class="text-right">
                        <div class="text-xs text-gray-500 mb-1">{{ getTimeUntilLesson(booking.scheduled_at) }}</div>
                        <button
                          v-if="isLessonStartable(booking.scheduled_at)"
                          @click="startLesson(booking.id)"
                          class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700 transition-colors"
                        >
                          🚀 Start
                        </button>
                        <span v-else class="text-xs text-gray-400">În așteptare</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-8">
            <!-- Profile Completion -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50">
              <div class="p-6 border-b border-gray-100">
                <h2 class="text-xl font-semibold text-gray-900">Completează profilul</h2>
                <p class="text-sm text-gray-600 mt-1">{{ profileCompletionPercentage }}% completat</p>
              </div>

              <div class="p-6">
                <div class="mb-4">
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                      :style="{ width: profileCompletionPercentage + '%' }"
                    ></div>
                  </div>
                </div>

                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <div class="w-2 h-2 rounded-full mr-3" :class="profileTasks.bio ? 'bg-green-500' : 'bg-gray-300'"></div>
                      <span class="text-sm text-gray-700">Adaugă descrierea</span>
                    </div>
                    <router-link
                      v-if="!profileTasks.bio"
                      :to="{ name: 'tutor-profile' }"
                      class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                    >
                      Editează
                    </router-link>
                  </div>

                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <div class="w-2 h-2 rounded-full mr-3" :class="profileTasks.subjects ? 'bg-green-500' : 'bg-gray-300'"></div>
                      <span class="text-sm text-gray-700">Alege materiile</span>
                    </div>
                    <router-link
                      v-if="!profileTasks.subjects"
                      :to="{ name: 'tutor-profile' }"
                      class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                    >
                      Editează
                    </router-link>
                  </div>

                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <div class="w-2 h-2 rounded-full mr-3" :class="profileTasks.hourlyRate ? 'bg-green-500' : 'bg-gray-300'"></div>
                      <span class="text-sm text-gray-700">Setează prețul/oră</span>
                    </div>
                    <router-link
                      v-if="!profileTasks.hourlyRate"
                      :to="{ name: 'tutor-profile' }"
                      class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                    >
                      Editează
                    </router-link>
                  </div>

                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <div class="w-2 h-2 rounded-full mr-3" :class="profileTasks.availability ? 'bg-green-500' : 'bg-gray-300'"></div>
                      <span class="text-sm text-gray-700">Setează disponibilitatea</span>
                    </div>
                    <router-link
                      v-if="!profileTasks.availability"
                      :to="{ name: 'tutor-availability' }"
                      class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                    >
                      Editează
                    </router-link>
                  </div>

                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <div class="w-2 h-2 rounded-full mr-3" :class="profileTasks.photo ? 'bg-green-500' : 'bg-gray-300'"></div>
                      <span class="text-sm text-gray-700">Încarcă fotografia</span>
                    </div>
                    <router-link
                      v-if="!profileTasks.photo"
                      :to="{ name: 'tutor-profile' }"
                      class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                    >
                      Editează
                    </router-link>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Reviews -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50">
              <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                  <h2 class="text-xl font-semibold text-gray-900">Recenzii recente</h2>
                  <router-link
                    :to="{ name: 'tutor-reviews' }"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors"
                  >
                    Vezi toate
                  </router-link>
                </div>
              </div>

              <div class="p-6">
                <!-- No Reviews -->
                <div v-if="recentReviews.length === 0" class="text-center py-8">
                  <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                  </div>
                  <h3 class="text-lg font-medium text-gray-900 mb-2">Nicio recenzie</h3>
                  <p class="text-gray-600">Recenziile vor apărea aici după lecții.</p>
                </div>

                <!-- Compact Reviews List -->
                <div v-else class="space-y-3">
                  <div
                    v-for="review in recentReviews"
                    :key="review.id"
                    class="border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow"
                  >
                    <div class="flex items-start space-x-3">
                      <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-xs font-bold">{{ getInitials(getSafeName(review)) }}</span>
                      </div>
                      <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                          <h4 class="font-semibold text-gray-900 text-sm">{{ getSafeName(review) }}</h4>
                          <div class="flex items-center">
                            <span class="text-yellow-400 text-sm">{{ '★'.repeat(review.rating || 0) }}</span>
                            <span class="text-gray-300 text-sm">{{ '★'.repeat(5 - (review.rating || 0)) }}</span>
                            <span class="ml-1 text-xs text-gray-600">{{ review.rating || 0 }}/5</span>
                          </div>
                        </div>
                        <p class="text-xs text-gray-600 mb-1">📚 {{ review.subject || 'Lecție' }}</p>
                        <p class="text-sm text-gray-700 italic">
                          "{{ (review.comment || 'Fără comentariu.').substring(0, 80) }}{{ ((review.comment || '').length > 80) ? '...' : '' }}"
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ formatDate(review.created_at) }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pending Cash Payments -->
            <div v-if="pendingCashPayments.length > 0" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50">
              <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                  <h2 class="text-xl font-semibold text-gray-900">Plăți în așteptare</h2>
                  <span class="bg-orange-100 text-orange-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                    {{ pendingCashPayments.length }}
                  </span>
                </div>
              </div>

              <div class="p-6">
                <div class="space-y-3">
                  <div
                    v-for="payment in pendingCashPayments"
                    :key="payment.id"
                    class="bg-orange-50 border border-orange-200 rounded-lg p-3"
                  >
                    <div class="flex items-center justify-between">
                      <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                          <span class="text-white text-xs font-bold">{{ getInitials(payment.student_name) }}</span>
                        </div>
                        <div>
                          <h4 class="font-semibold text-gray-900 text-sm">{{ payment.student_name }}</h4>
                          <div class="flex items-center space-x-3 text-xs text-gray-600">
                            <span>📚 {{ payment.subject || 'Lecție' }}</span>
                            <span>📅 {{ formatDate(payment.date) }}</span>
                          </div>
                        </div>
                      </div>
                      <div class="text-right">
                        <p class="text-sm font-bold text-gray-900">{{ payment.amount }} RON</p>
                        <button
                          @click="confirmCashPayment(payment.id)"
                          class="text-xs bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition-colors"
                        >
                          ✅ Confirmă
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
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTutorStore } from '@/stores/tutor'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const tutorStore = useTutorStore()
const router = useRouter()

// Reactive data
const stats = ref({
  total_earnings: 0,
  total_lessons: 0,
  average_rating: 0,
  total_reviews: 0,
  this_month_bookings: 0,
  pending_payments: 0,
  this_week_earnings: 0,
  this_week_lessons: 0,
  total_students: 0
})

const subscription = ref(null)
const pendingBookings = ref([])
const upcomingBookings = ref([])
const recentReviews = ref([])
const pendingCashPayments = ref([])
const notifications = ref([])

const loading = ref(false)
const loadingPending = ref(false)
const loadingUpcoming = ref(false)
const processing = ref(false)
const error = ref(null)

// Profile completion tracking
const profileTasks = ref({
  bio: false,
  subjects: false,
  hourlyRate: false,
  availability: false,
  photo: false
})

const weeklyStats = ref({
  lessons: 0,
  earnings: 0,
  newStudents: 0,
  occupancyRate: 0
})

const weekDays = ref([])

// Computed properties
const profileCompletionPercentage = computed(() => {
  const completedTasks = Object.values(profileTasks.value).filter(Boolean).length
  return Math.round((completedTasks / Object.keys(profileTasks.value).length) * 100)
})

// Helper Methods
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

const formatTimeAgo = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInMinutes = Math.floor((now - date) / (1000 * 60))

  if (diffInMinutes < 60) {
    return `${diffInMinutes} minute`
  } else if (diffInMinutes < 1440) {
    return `${Math.floor(diffInMinutes / 60)} ore`
  } else {
    return `${Math.floor(diffInMinutes / 1440)} zile`
  }
}

const getInitials = (fullName) => {
  if (!fullName || typeof fullName !== 'string' || fullName.trim().length === 0) {
    return 'U' // Return 'U' for User instead of 'N/A'
  }

  const cleanName = fullName.trim()
  const nameParts = cleanName.split(' ').filter(part => part.length > 0)

  if (nameParts.length === 0) return 'U'
  if (nameParts.length === 1) return nameParts[0][0].toUpperCase()

  // First letter of first word + first letter of last word
  return nameParts[0][0].toUpperCase() + nameParts[nameParts.length - 1][0].toUpperCase()
}

const getSafeName = (item) => {
  console.log('Getting safe name for:', item) // Keep this for debugging

  // Handle the actual structure from your backend
  if (item?.student) {
    const student = item.student

    // Try to build full name from first + last
    if (student.first_name || student.last_name) {
      const firstName = student.first_name || ''
      const lastName = student.last_name || ''
      const fullName = `${firstName} ${lastName}`.trim()
      if (fullName) return fullName
    }

    // Try other name fields
    if (student.full_name) return student.full_name
    if (student.name) return student.name

    // Use email as last resort
    if (student.email) {
      const emailName = student.email.split('@')[0]
      return emailName.charAt(0).toUpperCase() + emailName.slice(1)
    }
  }

  // Handle direct name fields
  if (item?.student_name) return item.student_name
  if (item?.full_name) return item.full_name
  if (item?.name) return item.name

  return 'Student'
}

const getTimeUntilLesson = (scheduledAt) => {
  const now = new Date()
  const lessonTime = new Date(scheduledAt)
  const diffInMinutes = (lessonTime - now) / (1000 * 60)

  if (diffInMinutes > 60) {
    const hours = Math.floor(diffInMinutes / 60)
    return `în ${hours}h`
  } else if (diffInMinutes > 0) {
    return `în ${Math.floor(diffInMinutes)}min`
  } else {
    return 'acum'
  }
}

const isLessonStartable = (scheduledAt) => {
  const lessonTime = new Date(scheduledAt)
  const now = new Date()
  const diffInMinutes = (lessonTime - now) / (1000 * 60)
  return diffInMinutes <= 15 && diffInMinutes >= -60 // Can start 15 min before, up to 1 hour after
}

const formatRating = (rating) => {
  if (rating === null || rating === undefined || isNaN(rating)) {
    return '0.0'
  }
  return parseFloat(rating).toFixed(1)
}

const safeNumber = (value) => {
  return value || 0
}

const getPaymentStatusClass = (status) => {
  const classes = {
    paid: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    failed: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusText = (status) => {
  const texts = {
    paid: 'Plătit',
    pending: 'În așteptare',
    failed: 'Eșuat'
  }
  return texts[status] || 'Necunoscut'
}

// Actions
const confirmBooking = async (bookingId) => {
  processing.value = true
  try {
    await tutorStore.confirmBooking(bookingId)
    await loadDashboardData()
  } catch (error) {
    console.error('Error confirming booking:', error)
  } finally {
    processing.value = false
  }
}

const rejectBooking = async (bookingId) => {
  processing.value = true
  try {
    await tutorStore.rejectBooking(bookingId)
    await loadDashboardData()
  } catch (error) {
    console.error('Error rejecting booking:', error)
  } finally {
    processing.value = false
  }
}

const confirmCashPayment = async (paymentId) => {
  try {
    await tutorStore.confirmCashPayment(paymentId)
    await loadDashboardData()
  } catch (error) {
    console.error('Error confirming cash payment:', error)
  }
}

const startLesson = (bookingId) => {
  // For now, just show an alert since the lesson view doesn't exist yet
  alert(`Funcționalitatea pentru lecția ${bookingId} va fi disponibilă în curând!`)
  // router.push({ name: 'tutor-lesson', params: { id: bookingId } })
}

const loadDashboardData = async () => {
  loading.value = true
  loadingPending.value = true
  loadingUpcoming.value = true
  error.value = null

  try {
    console.log('🔄 Loading dashboard data...')

    const dashboardData = await tutorStore.getDashboard()
    console.log('📊 Dashboard data received:', dashboardData)

    // Debug: Log the exact structure
    console.log('Stats:', dashboardData.stats)
    console.log('Pending bookings:', dashboardData.pending_bookings)
    console.log('Upcoming bookings:', dashboardData.upcoming_bookings)

    // Make sure we're setting the data correctly
    if (dashboardData.stats) {
      stats.value = {
        total_earnings: dashboardData.stats.total_earnings || 0,
        total_lessons: dashboardData.stats.total_lessons || 0,
        average_rating: dashboardData.stats.average_rating || 0,
        total_reviews: dashboardData.stats.total_reviews || 0,
        this_month_bookings: dashboardData.stats.this_month_bookings || 0,
        pending_payments: dashboardData.stats.pending_payments || 0,
        this_week_earnings: dashboardData.stats.this_week_earnings || 0,
        this_week_lessons: dashboardData.stats.this_week_lessons || 0,
        total_students: dashboardData.stats.total_students || 0
      }
      console.log('✅ Stats updated:', stats.value)
    }

    subscription.value = dashboardData.subscription || null
    pendingBookings.value = dashboardData.pending_bookings || []
    upcomingBookings.value = dashboardData.upcoming_bookings || []
    recentReviews.value = dashboardData.recent_reviews || []
    pendingCashPayments.value = dashboardData.pending_cash_payments || []
    notifications.value = dashboardData.notifications || []

    console.log('📋 Arrays updated:', {
      pendingBookings: pendingBookings.value.length,
      upcomingBookings: upcomingBookings.value.length,
      recentReviews: recentReviews.value.length,
      pendingCashPayments: pendingCashPayments.value.length
    })

    // Update profile completion
    const tutor = dashboardData.tutor
    if (tutor) {
      profileTasks.value = {
        bio: !!tutor.bio,
        subjects: tutor.subjects && tutor.subjects.length > 0,
        hourlyRate: !!tutor.hourly_rate,
        availability: tutor.availabilities && tutor.availabilities.length > 0,
        photo: !!tutor.photo
      }
      console.log('👤 Profile tasks updated:', profileTasks.value)
    }

    // Update weekly stats
    weeklyStats.value = dashboardData.weekly_stats || {
      lessons: 0,
      earnings: 0,
      newStudents: 0,
      occupancyRate: 0
    }

    // Update week schedule
    if (dashboardData.week_schedule) {
      weekDays.value = dashboardData.week_schedule
      console.log('📅 Week schedule updated:', weekDays.value.length, 'days')
    }

    console.log('✅ Dashboard data loaded successfully!')

  } catch (err) {
    console.error('❌ Error loading dashboard data:', err)
    console.error('Error details:', {
      message: err.message,
      response: err.response?.data,
      status: err.response?.status
    })
    error.value = err.response?.data?.message || err.message || 'Eroare la încărcarea datelor'
  } finally {
    loading.value = false
    loadingPending.value = false
    loadingUpcoming.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadDashboardData()
})
</script>
