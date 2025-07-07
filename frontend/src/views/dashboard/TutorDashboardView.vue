<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              Bună, {{ authStore.user?.first_name }}! 🎓
            </h1>
            <p class="text-gray-600 mt-1">Bine ai venit în dashboard-ul tău de tutor</p>
          </div>
          <div class="flex items-center space-x-4">
            <router-link
              :to="{ name: 'tutor-profile' }"
              class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
            >
              Editează profilul
            </router-link>
            <router-link
              :to="{ name: 'tutor-availability' }"
              class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <span class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Disponibilitate</span>
              </span>
            </router-link>
          </div>
        </div>
      </div>

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
              <p class="text-2xl font-bold text-gray-900">{{ stats.total_earnings }} RON</p>
              <p class="text-xs text-green-600 font-medium">+12% luna aceasta</p>
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
              <p class="text-2xl font-bold text-gray-900">{{ stats.total_lessons }}</p>
              <p class="text-xs text-blue-600 font-medium">{{ stats.this_month_bookings }} luna aceasta</p>
            </div>
          </div>
        </div>

        <!-- Average Rating -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-xl">
              <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Rating mediu</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.average_rating }}/5</p>
              <p class="text-xs text-gray-600">{{ stats.total_reviews }} recenzii</p>
            </div>
          </div>
        </div>

        <!-- Pending Payments -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-orange-100 rounded-xl">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Plăți în așteptare</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.pending_payments }} RON</p>
              <p class="text-xs text-orange-600 font-medium">{{ pendingCashPayments.length }} plăți cash</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Subscription Banner -->
      <div v-if="subscription && subscription.plan_type === 'free_trial'" class="mb-8">
        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-yellow-900">Perioada de încercare gratuită</h3>
                <p class="text-yellow-800">
                  Îți mai rămân {{ subscription.trial_days_remaining }} zile din perioada de încercare gratuită.
                </p>
              </div>
            </div>
            <button
              @click="upgradeSubscription"
              class="px-6 py-3 bg-yellow-600 text-white font-semibold rounded-xl hover:bg-yellow-700 transition-colors"
            >
              Upgrade la Premium
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Pending Cash Payments -->
          <div v-if="pendingCashPayments.length > 0" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50">
            <div class="p-6 border-b border-gray-100">
              <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">
                  Plăți cash de confirmat
                  <span class="ml-2 px-2 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">
                    {{ pendingCashPayments.length }}
                  </span>
                </h2>
              </div>
            </div>

            <div class="p-6">
              <div class="space-y-4">
                <div
                  v-for="payment in pendingCashPayments"
                  :key="payment.id"
                  class="flex items-center justify-between p-4 bg-orange-50 border border-orange-200 rounded-xl"
                >
                  <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center text-white font-semibold">
                      {{ getInitials(payment.studentName) }}
                    </div>
                    <div>
                      <h4 class="font-semibold text-gray-900">{{ payment.studentName }}</h4>
                      <p class="text-sm text-gray-600">{{ payment.lessons }} lecții - {{ formatDate(payment.date) }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-3">
                    <span class="text-lg font-bold text-gray-900">{{ payment.amount }} RON</span>
                    <button
                      @click="confirmCashPayment(payment.id)"
                      class="flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
                    >
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                      Confirmă
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pending Bookings -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50">
            <div class="p-6 border-b border-gray-100">
              <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">
                  Cereri de rezervare
                  <span v-if="pendingBookings.length > 0" class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">
                    {{ pendingBookings.length }}
                  </span>
                </h2>
                <router-link
                  :to="{ name: 'tutor-bookings' }"
                  class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors"
                >
                  Vezi toate
                </router-link>
              </div>
            </div>

            <div class="p-6">
              <!-- Loading State -->
              <div v-if="loadingPending" class="space-y-4">
                <div v-for="i in 3" :key="i" class="animate-pulse flex items-center space-x-4">
                  <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nicio cerere în așteptare</h3>
                <p class="text-gray-600">Când studenții îți vor rezerva lecții, le vei vedea aici.</p>
              </div>

              <!-- Pending Bookings List -->
              <div v-else class="space-y-4">
                <div
                  v-for="booking in pendingBookings"
                  :key="booking.id"
                  class="flex items-center justify-between p-4 border border-yellow-200 bg-yellow-50 rounded-xl"
                >
                  <div class="flex items-center space-x-4">
                    <!-- Student Avatar -->
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white font-semibold">
                      {{ getInitials(booking.student.full_name) }}
                    </div>

                    <!-- Booking Info -->
                    <div>
                      <h4 class="font-semibold text-gray-900">{{ booking.subject.name }}</h4>
                      <p class="text-sm text-gray-600">cu {{ booking.student.full_name }}</p>
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
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                          {{ booking.lesson_type === 'online' ? 'Online' : 'Față în față' }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Actions -->
                  <div class="flex items-center space-x-3">
                    <span class="text-lg font-bold text-gray-900">{{ booking.price }} RON</span>
                    <div class="flex space-x-2">
                      <button
                        @click="confirmBooking(booking.id)"
                        :disabled="processing"
                        class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
                      >
                        Acceptă
                      </button>
                      <button
                        @click="rejectBooking(booking.id)"
                        :disabled="processing"
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
                      >
                        Respinge
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
                <p class="text-gray-600">Lecțiile confirmate vor apărea aici.</p>
              </div>

              <!-- Upcoming Lessons List -->
              <div v-else class="space-y-4">
                <div
                  v-for="booking in upcomingBookings"
                  :key="booking.id"
                  class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-blue-200 transition-colors"
                >
                  <div class="flex items-center space-x-4">
                    <!-- Student Avatar -->
                    <div class="relative">
                      <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white font-semibold">
                        {{ getInitials(booking.student.full_name) }}
                      </div>
                      <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
                    </div>

                    <!-- Lesson Info -->
                    <div>
                      <h4 class="font-semibold text-gray-900">{{ booking.subject.name }}</h4>
                      <p class="text-sm text-gray-600">cu {{ booking.student.full_name }}</p>
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
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                          Confirmată
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Actions -->
                  <div class="flex items-center space-x-3">
                    <span class="text-lg font-bold text-gray-900">{{ booking.price }} RON</span>
                    <button
                      @click="startLesson(booking.id)"
                      :disabled="!isLessonStartable(booking.scheduled_at)"
                      class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ isLessonStartable(booking.scheduled_at) ? 'Începe lecția' : 'Prea devreme' }}
                    </button>
                  </div>
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
              <div v-if="recentReviews.length === 0" class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nicio recenzie încă</h3>
                <p class="text-gray-600">Recenziile studenților vor apărea aici după lecții.</p>
              </div>

              <div v-else class="space-y-4">
                <div
                  v-for="review in recentReviews"
                  :key="review.id"
                  class="p-4 border border-gray-200 rounded-xl"
                >
                  <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-white font-semibold text-sm">
                        {{ getInitials(review.student.full_name) }}
                      </div>
                      <div>
                        <h4 class="font-medium text-gray-900">{{ review.student.full_name }}</h4>
                        <p class="text-sm text-gray-600">{{ review.subject }}</p>
                      </div>
                    </div>
                    <div class="flex items-center space-x-1">
                      <div v-for="i in 5" :key="i" class="w-4 h-4">
                        <svg
                          class="w-4 h-4"
                          :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-300'"
                          fill="currentColor"
                          viewBox="0 0 20 20"
                        >
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                      </div>
                    </div>
                  </div>
                  <p class="text-gray-700 mb-2">{{ review.comment }}</p>
                  <p class="text-xs text-gray-500">{{ formatDate(review.date) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Weekly Schedule Overview -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50">
            <div class="p-6 border-b border-gray-100">
              <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Programul săptămânii</h2>
                <router-link
                  :to="{ name: 'tutor-availability' }"
                  class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors"
                >
                  Editează disponibilitatea
                </router-link>
              </div>
            </div>

            <div class="p-6">
              <div class="grid grid-cols-7 gap-2">
                <div
                  v-for="(day, index) in weekDays"
                  :key="index"
                  class="text-center"
                >
                  <div class="text-sm font-medium text-gray-600 mb-2">{{ day.name }}</div>
                  <div class="space-y-1">
                    <div
                      v-for="slot in day.availableSlots"
                      :key="slot"
                      class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded"
                    >
                      {{ slot }}
                    </div>
                    <div
                      v-for="booking in day.bookings"
                      :key="booking.id"
                      class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded"
                    >
                      {{ booking.time }}
                    </div>
                    <div v-if="day.availableSlots.length === 0 && day.bookings.length === 0" class="text-xs text-gray-400">
                      Indisponibil
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="space-y-6">
          <!-- Profile Completion -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Completează profilul</h3>
            <div class="space-y-4">
              <!-- Progress Bar -->
              <div>
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                  <span>Progres completare</span>
                  <span>{{ profileCompletion }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    class="bg-gradient-to-r from-blue-600 to-purple-600 h-3 rounded-full transition-all duration-500"
                    :style="`width: ${profileCompletion}%`"
                  ></div>
                </div>
              </div>

              <!-- Profile Tasks -->
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div
                      class="w-5 h-5 rounded-full border-2"
                      :class="profileTasks.bio ? 'bg-green-500 border-green-500' : 'border-gray-300'"
                    >
                      <svg v-if="profileTasks.bio" class="w-3 h-3 text-white ml-0.5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                      </svg>
                    </div>
                    <span class="text-sm text-gray-700">Adaugă descrierea</span>
                  </div>
                  <router-link :to="{ name: 'tutor-profile' }" class="text-xs text-blue-600 hover:text-blue-800">Editează</router-link>
                </div>

                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div
                      class="w-5 h-5 rounded-full border-2"
                      :class="profileTasks.subjects ? 'bg-green-500 border-green-500' : 'border-gray-300'"
                    >
                      <svg v-if="profileTasks.subjects" class="w-3 h-3 text-white ml-0.5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                      </svg>
                    </div>
                    <span class="text-sm text-gray-700">Alege materiile</span>
                  </div>
                  <router-link :to="{ name: 'tutor-profile' }" class="text-xs text-blue-600 hover:text-blue-800">Editează</router-link>
                </div>

                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div
                      class="w-5 h-5 rounded-full border-2"
                      :class="profileTasks.hourlyRate ? 'bg-green-500 border-green-500' : 'border-gray-300'"
                    >
                      <svg v-if="profileTasks.hourlyRate" class="w-3 h-3 text-white ml-0.5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                      </svg>
                    </div>
                    <span class="text-sm text-gray-700">Setează prețul/oră</span>
                  </div>
                  <router-link :to="{ name: 'tutor-profile' }" class="text-xs text-blue-600 hover:text-blue-800">Editează</router-link>
                </div>

                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div
                      class="w-5 h-5 rounded-full border-2"
                      :class="profileTasks.availability ? 'bg-green-500 border-green-500' : 'border-gray-300'"
                    >
                      <svg v-if="profileTasks.availability" class="w-3 h-3 text-white ml-0.5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                      </svg>
                    </div>
                    <span class="text-sm text-gray-700">Setează disponibilitatea</span>
                  </div>
                  <router-link :to="{ name: 'tutor-availability' }" class="text-xs text-blue-600 hover:text-blue-800">Editează</router-link>
                </div>

                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div
                      class="w-5 h-5 rounded-full border-2"
                      :class="profileTasks.photo ? 'bg-green-500 border-green-500' : 'border-gray-300'"
                    >
                      <svg v-if="profileTasks.photo" class="w-3 h-3 text-white ml-0.5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                      </svg>
                    </div>
                    <span class="text-sm text-gray-700">Încarcă fotografia</span>
                  </div>
                  <router-link :to="{ name: 'tutor-profile' }" class="text-xs text-blue-600 hover:text-blue-800">Editează</router-link>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistici rapide</h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Lecții săptămâna aceasta</span>
                <span class="font-semibold text-gray-900">{{ weeklyStats.lessons }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Câștiguri săptămânale</span>
                <span class="font-semibold text-gray-900">{{ weeklyStats.earnings }} RON</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Studenți noi</span>
                <span class="font-semibold text-gray-900">{{ weeklyStats.newStudents }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Rata de ocupare</span>
                <span class="font-semibold text-green-600">{{ weeklyStats.occupancyRate }}%</span>
              </div>
            </div>
          </div>

          <!-- Performance Tips -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sfaturi pentru performanță</h3>
            <div class="space-y-4">
              <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Completează profilul</h4>
                  <p class="text-xs text-gray-600">Profilurile complete primesc cu 60% mai multe rezervări.</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Răspunde rapid</h4>
                  <p class="text-xs text-gray-600">Răspunsurile în sub 2 ore cresc șansele de rezervare.</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Obține recenzii</h4>
                  <p class="text-xs text-gray-600">Tutorii cu rating peste 4.5 au de 3x mai multe rezervări.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Notifications -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Notificări</h3>
            <div class="space-y-3">
              <div v-for="notification in notifications" :key="notification.id" class="flex items-start space-x-3">
                <div
                  class="w-2 h-2 rounded-full flex-shrink-0 mt-2"
                  :class="notification.read ? 'bg-gray-300' : 'bg-blue-500'"
                ></div>
                <div class="flex-1">
                  <p class="text-sm text-gray-900">{{ notification.message }}</p>
                  <p class="text-xs text-gray-500">{{ formatTimeAgo(notification.created_at) }}</p>
                </div>
              </div>

              <div v-if="notifications.length === 0" class="text-center py-4">
                <p class="text-sm text-gray-500">Nicio notificare nouă</p>
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
  pending_payments: 0
})

const subscription = ref(null)
const pendingBookings = ref([])
const upcomingBookings = ref([])
const recentReviews = ref([])
const pendingCashPayments = ref([])
const notifications = ref([])

const loadingPending = ref(false)
const loadingUpcoming = ref(false)
const processing = ref(false)

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

const weekDays = ref([
  { name: 'Lun', availableSlots: [], bookings: [] },
  { name: 'Mar', availableSlots: [], bookings: [] },
  { name: 'Mie', availableSlots: [], bookings: [] },
  { name: 'Joi', availableSlots: [], bookings: [] },
  { name: 'Vin', availableSlots: [], bookings: [] },
  { name: 'Sâm', availableSlots: [], bookings: [] },
  { name: 'Dum', availableSlots: [], bookings: [] }
])

// Computed properties
const profileCompletion = computed(() => {
  const tasks = Object.values(profileTasks.value)
  const completed = tasks.filter(task => task).length
  return Math.round((completed / tasks.length) * 100)
})

// Methods
const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ro-RO', {
    weekday: 'long',
    month: 'long',
    day: 'numeric'
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
  const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))

  if (diffInHours < 1) return 'Acum'
  if (diffInHours < 24) return `${diffInHours}h în urmă`
  const diffInDays = Math.floor(diffInHours / 24)
  return `${diffInDays} zile în urmă`
}

const getInitials = (fullName) => {
  if (!fullName) return 'N/A'
  return fullName.split(' ').map(name => name[0]).join('').toUpperCase()
}

const isLessonStartable = (scheduledAt) => {
  const lessonTime = new Date(scheduledAt)
  const now = new Date()
  const diffInMinutes = (lessonTime - now) / (1000 * 60)
  return diffInMinutes <= 15 && diffInMinutes >= -60 // Can start 15 min before, up to 1 hour after
}

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

const upgradeSubscription = () => {
  // For now, just show an alert since the subscription view doesn't exist yet
  alert('Funcționalitatea de upgrade va fi disponibilă în curând!')
  // router.push({ name: 'tutor-subscription' })
}

const loadDashboardData = async () => {
  try {
    loadingPending.value = true
    loadingUpcoming.value = true

    const dashboardData = await tutorStore.getDashboard()

    stats.value = dashboardData.stats
    subscription.value = dashboardData.subscription
    pendingBookings.value = dashboardData.pending_bookings || []
    upcomingBookings.value = dashboardData.upcoming_bookings || []
    recentReviews.value = dashboardData.recent_reviews || []
    pendingCashPayments.value = dashboardData.pending_cash_payments || []
    notifications.value = dashboardData.notifications || []

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
    }

    // Update weekly stats
    weeklyStats.value = dashboardData.weekly_stats || weeklyStats.value

    // Update week schedule
    if (dashboardData.week_schedule) {
      weekDays.value = dashboardData.week_schedule
    }

  } catch (error) {
    console.error('Error loading dashboard data:', error)
  } finally {
    loadingPending.value = false
    loadingUpcoming.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadDashboardData()
})
</script>
