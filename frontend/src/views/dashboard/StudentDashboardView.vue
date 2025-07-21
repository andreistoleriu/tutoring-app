<!-- Replace frontend/src/views/dashboard/StudentDashboardView.vue with this enhanced version -->

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
            <!-- Enhanced Subscription Status Button -->
            <button @click="showSubscriptionModal = true"
              class="px-4 py-2 rounded-xl font-medium text-sm transition-all duration-200 flex items-center space-x-2"
              :class="subscriptionButtonClass">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ subscriptionButtonText }}</span>
            </button>

            <!-- View All Bookings Button -->
            <router-link to="/student/bookings"
              class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-colors text-sm font-medium flex items-center space-x-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
              </svg>
              <span>Toate rezervările</span>
              <span v-if="dashboardData?.stats?.total_bookings"
                class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                {{ dashboardData.stats.total_bookings }}
              </span>
            </router-link>

            <router-link to="/tutors"
              class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
              <span class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span>Caută tutori</span>
              </span>
            </router-link>
          </div>
        </div>
      </div>

      <!-- Enhanced AdBanner usage -->
      <AdBanner placement="header" type="banner" :limit="1" :auto-refresh="true" :refresh-interval="60000"
        @ad-clicked="handleAdClick" @ad-closed="handleAdClose" />

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="ml-4 text-gray-600">Se încarcă dashboard-ul...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
        <div class="flex items-center">
          <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <h3 class="text-red-800 font-semibold">Eroare la încărcarea datelor</h3>
            <p class="text-red-600">{{ error }}</p>
          </div>
        </div>
        <button @click="loadDashboardData"
          class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
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
              <button @click="loadDashboardData"
                class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                  </path>
                </svg>
                <span>Actualizează</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Total Lessons -->
          <div
            class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6 hover:shadow-lg transition-all duration-300 cursor-pointer group relative"
            @click="navigateToBookings('completed')">
            <div class="flex items-center">
              <div class="p-3 bg-blue-100 rounded-xl group-hover:bg-blue-200 transition-colors">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                  </path>
                </svg>
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-600">Total lecții</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatNumber(dashboardData?.stats?.completed_lessons ||
                  dashboardData?.stats?.total_lessons || 0) }}</p>
                <p class="text-xs text-blue-600 mt-1">
                  {{ getPluralText(dashboardData?.stats?.completed_lessons || dashboardData?.stats?.total_lessons || 0,
                    'lecție finalizată', 'lecții finalizate') }}
                </p>
              </div>
              <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- This Month -->
          <div
            class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6 hover:shadow-lg transition-all duration-300 cursor-pointer group"
            @click="navigateToBookings('this_month')">
            <div class="flex items-center">
              <div class="p-3 bg-green-100 rounded-xl group-hover:bg-green-200 transition-colors">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-600">Luna aceasta</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatNumber(getDisplayedMonthlyCount()) }}</p>
                <p class="text-xs text-green-600 mt-1">
                  {{ getMonthlyGrowthText() }}
                </p>
              </div>
              <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Total Spent -->
          <div
            class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6 hover:shadow-lg transition-all duration-300 cursor-pointer group"
            @click="showSpendingBreakdown">
            <div class="flex items-center">
              <div class="p-3 bg-purple-100 rounded-xl group-hover:bg-purple-200 transition-colors">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                  </path>
                </svg>
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-600">Total investit</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(dashboardData?.stats?.total_spent || 0) }}
                </p>
                <p class="text-xs text-purple-600 mt-1">
                  {{ getAveragePerLessonText() }}
                </p>
              </div>
              <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Pending Reviews -->
          <div
            class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6 hover:shadow-lg transition-all duration-300 cursor-pointer group relative"
            :class="{ 'ring-2 ring-yellow-400 ring-opacity-50': (dashboardData?.stats?.pending_reviews || 0) > 0 }"
            @click="navigateToPendingReviews">
            <div class="flex items-center">
              <div class="p-3 bg-yellow-100 rounded-xl group-hover:bg-yellow-200 transition-colors">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
                  </path>
                </svg>
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-600">Review-uri restante</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatNumber(dashboardData?.stats?.pending_reviews || 0)
                  }}</p>
                <p class="text-xs mt-1"
                  :class="(dashboardData?.stats?.pending_reviews || 0) > 0 ? 'text-yellow-600' : 'text-gray-500'">
                  {{ getPendingReviewsText() }}
                </p>
              </div>
              <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </div>
              <!-- Notification Dot -->
              <div v-if="(dashboardData?.stats?.pending_reviews || 0) > 0"
                class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-500 rounded-full animate-pulse">
              </div>
            </div>
          </div>
        </div>

        <!-- Enhanced AdInline usage -->
        <AdInline placement="feed" :show-metrics="true" :auto-rotate="true" :rotate-interval="30000"
          @clicked="handleInlineAdClick" />

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Left Column - Upcoming and Recent Lessons -->
          <div class="lg:col-span-2 space-y-6">

            <!-- Upcoming Bookings -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                  <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  Lecții programate
                </h2>
                <div class="flex items-center space-x-3">
                  <button @click="loadDashboardData"
                    class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                      </path>
                    </svg>
                    <span>Actualizează</span>
                  </button>
                  <router-link to="/student/bookings"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                      </path>
                    </svg>
                    <span>Vezi toate</span>
                  </router-link>
                </div>
              </div>

              <!-- Empty State when no upcoming bookings -->
              <div v-if="!upcomingBookings?.length" class="text-center py-8">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-gray-500 text-lg font-medium">Nu ai lecții programate</p>
                <p class="text-gray-400 mt-1">Caută un tutor și rezervă prima ta lecție!</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center mt-4">
                  <router-link to="/tutors"
                    class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Caută tutori
                  </router-link>
                  <router-link to="/student/bookings"
                    class="inline-block px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Vezi istoric rezervări
                  </router-link>
                </div>
              </div>

              <!-- Upcoming Bookings List -->
              <div v-else class="space-y-4">
                <div v-for="booking in upcomingBookings" :key="booking.id"
                  class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:shadow-md transition-shadow">
                  <div class="flex items-center space-x-4">
                    <!-- Tutor Avatar -->
                    <div class="relative">
                      <img v-if="booking.tutor?.profile_image" :src="booking.tutor.profile_image"
                        :alt="getTutorName(booking.tutor)"
                        class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm">
                      <div v-else
                        class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white font-semibold shadow-sm">
                        {{ getInitials(getTutorName(booking.tutor)) }}
                      </div>
                      <!-- Status indicator -->
                      <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white" :class="{
                        'bg-yellow-400': booking.status === 'pending',
                        'bg-green-500': booking.status === 'confirmed',
                        'bg-gray-400': booking.status === 'completed'
                      }"></div>
                    </div>

                    <!-- Lesson Info -->
                    <div>
                      <h4 class="font-semibold text-gray-900">{{ booking.subject?.name || 'Necunoscut' }}</h4>
                      <p class="text-sm text-gray-600">cu {{ getTutorName(booking.tutor) }}</p>
                      <div class="flex items-center space-x-4 mt-1 text-sm text-gray-500">
                        <span class="flex items-center space-x-1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                          </svg>
                          <span>{{ formatDate(booking.scheduled_at) }}</span>
                        </span>
                        <span class="flex items-center space-x-1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                          </svg>
                          <span>{{ formatTime(booking.scheduled_at) }}</span>
                        </span>
                        <span class="px-2 py-1 rounded-full text-xs font-medium" :class="{
                          'bg-yellow-100 text-yellow-800': booking.status === 'pending',
                          'bg-green-100 text-green-800': booking.status === 'confirmed',
                          'bg-gray-100 text-gray-800': booking.status === 'completed'
                        }">
                          {{ getStatusLabel(booking.status) }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-900">{{ booking.price || 0 }} RON</span>
                    <button v-if="booking.status === 'pending' || booking.status === 'confirmed'"
                      @click="cancelBooking(booking.id)" class="text-red-600 hover:text-red-700 text-sm">
                      Anulează
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Lessons -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-4 sm:p-6">
              <div class="flex items-center justify-between mb-4 sm:mb-6">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center">
                  <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600 mr-2" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                  </svg>
                  Lecții recente
                </h2>

                <router-link to="/student/bookings"
                  class="text-purple-600 hover:text-purple-700 font-medium text-sm transition-colors hidden sm:block">
                  Vezi toate
                </router-link>
              </div>

              <!-- Empty State for recent lessons -->
              <div v-if="!recentBookings?.length" class="text-center py-8">
                <div
                  class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-purple-100 to-blue-100 rounded-full flex items-center justify-center">
                  <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                    </path>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nicio lecție recentă</h3>
                <p class="text-gray-600 mb-4">Lecțiile finalizate vor apărea aici.</p>
                <router-link to="/tutors"
                  class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-xl hover:from-purple-700 hover:to-blue-700 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Rezervă prima lecție
                </router-link>
              </div>

              <!-- Recent Lessons List -->
              <div v-else class="space-y-3">
                <div v-for="lesson in recentBookings.slice(0, 5)" :key="lesson.id"
                  class="group bg-white border border-gray-200 rounded-xl p-3 sm:p-4 hover:shadow-md hover:border-gray-300 transition-all duration-200 cursor-pointer"
                  @click="handleViewLessonDetails(lesson)">

                  <div class="flex items-start space-x-3">
                    <!-- Tutor Avatar with Status -->
                    <div class="relative flex-shrink-0">
                      <div v-if="lesson.tutor?.profile_image"
                        class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden ring-2 ring-white shadow-md">
                        <img :src="lesson.tutor.profile_image" :alt="getTutorName(lesson.tutor)"
                          class="w-full h-full object-cover" @error="handleImageError">
                      </div>
                      <div v-else
                        class="w-12 h-12 sm:w-14 sm:h-14 rounded-full flex items-center justify-center text-white font-bold text-sm ring-2 ring-white shadow-md"
                        :class="getSubjectColor(lesson.subject?.name)">
                        {{ getInitials(getTutorName(lesson.tutor)) }}
                      </div>

                      <!-- Status Badge -->
                      <div
                        class="absolute -top-1 -right-1 w-5 h-5 rounded-full border-2 border-white flex items-center justify-center text-xs font-bold shadow-sm"
                        :class="getStatusColor(lesson.status)" :title="getStatusLabel(lesson.status)">
                        {{ getStatusIcon(lesson.status) }}
                      </div>
                    </div>

                    <!-- Lesson Content -->
                    <div class="flex-1 min-w-0">
                      <!-- Subject and Badge Row -->
                      <div class="flex items-center space-x-2 mb-2">
                        <h4 class="font-semibold text-gray-900 text-sm sm:text-base truncate flex-1">
                          {{ lesson.subject?.name || 'Necunoscut' }}
                        </h4>
                        <span class="text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-700 font-medium">
                          {{ lesson.subject?.icon || '📚' }}
                        </span>
                      </div>

                      <!-- Tutor Name -->
                      <p class="text-sm text-gray-600 truncate mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        cu {{ getTutorName(lesson.tutor) }}
                      </p>

                      <!-- Lesson Details -->
                      <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 mb-3">
                        <span class="flex items-center">
                          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                          </svg>
                          {{ formatRelativeDate(lesson.completed_at || lesson.scheduled_at) }}
                        </span>

                        <span class="flex items-center">
                          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          {{ lesson.duration_minutes || 60 }} min
                        </span>

                        <span class="flex items-center">
                          {{ lesson.lesson_type === 'online' ? '💻' : '👥' }}
                          {{ lesson.lesson_type === 'online' ? 'Online' : 'Față în față' }}
                        </span>
                      </div>

                      <!-- Review Section -->
                      <div v-if="lesson.review || lesson.has_review" class="flex items-center space-x-2 mb-2">
                        <span class="text-xs text-gray-600">Evaluarea ta:</span>
                        <div class="flex text-yellow-400">
                          <svg v-for="star in 5" :key="star" class="w-3 h-3"
                            :class="star <= (lesson.review?.rating || lesson.review_rating || 0) ? 'text-yellow-400' : 'text-gray-300'"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                          </svg>
                        </div>
                      </div>
                    </div>

                    <!-- Right Side: Price and Actions -->
                    <div class="flex flex-col items-end space-y-2 flex-shrink-0">
                      <!-- Price -->
                      <div class="text-right">
                        <p class="text-lg font-bold text-gray-900">
                          {{ lesson.price || 0 }} <span class="text-sm font-normal text-gray-600">RON</span>
                        </p>
                      </div>

                      <!-- Action Button -->
                      <button v-if="lesson.status === 'completed' && !lesson.review && !lesson.has_review"
                        @click.stop="openReviewModal(lesson)"
                        class="text-xs px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-full hover:bg-yellow-200 transition-colors font-medium">
                        ⭐ Scrie review
                      </button>

                      <div v-else-if="lesson.review || lesson.has_review"
                        class="text-xs text-green-600 font-medium flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Evaluat
                      </div>
                    </div>
                  </div>
                </div>

                <!-- View All Button (Mobile) -->
                <router-link v-if="recentBookings?.length" to="/student/bookings"
                  class="w-full mt-4 py-3 border-2 border-purple-200 text-purple-600 rounded-xl hover:bg-purple-50 transition-colors font-medium sm:hidden flex items-center justify-center space-x-2">
                  <span>Vezi toate lecțiile</span>
                  <span v-if="dashboardData?.stats?.total_lessons"
                    class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs font-bold">
                    {{ dashboardData.stats.total_lessons }}
                  </span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </router-link>
              </div>
              <AdBanner placement="bottom" type="banner" :limit="1" :auto-refresh="false" @ad-clicked="handleAdClick"
                @ad-closed="handleAdClose" />
            </div>
          </div>

          <!-- Right Column - Quick Stats & Tips & Ads -->
          <!-- Right Column - Quick Stats & Tips & Ads -->
          <div class="space-y-6">
            <!-- Subscription Status Widget -->
            <SubscriptionStatus @upgrade="showSubscriptionModal = true" @manage="showSubscriptionModal = true" />

            <AdSidebar placement="sidebar" :limit="2" :show-metrics="false" :auto-refresh="true"
              @ads-loaded="handleAdsLoaded" />


            <!-- Active Reminders Section -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
              <!-- Header with Warning Icon -->
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <!-- Warning triangle icon (indicates active alerts) -->
                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                Reminder-uri active
                <!-- Show count badge if there are reminders -->
                <span v-if="upcomingReminders.length > 0"
                  class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded-full">
                  {{ upcomingReminders.length }}
                </span>
              </h3>

              <!-- Content Area -->
              <div class="space-y-3">
                <!-- Loading State -->
                <div v-if="loadingReminders" class="text-center py-4">
                  <div
                    class="animate-spin w-6 h-6 border-2 border-yellow-600 border-t-transparent rounded-full mx-auto">
                  </div>
                  <p class="text-sm text-gray-500 mt-2">Se încarcă reminder-urile...</p>
                </div>

                <!-- Empty State: When no active reminders -->
                <div v-else-if="upcomingReminders.length === 0" class="text-center py-6">
                  <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <p class="text-gray-500 text-sm">Nu ai reminder-uri active</p>
                  <p class="text-gray-400 text-xs mt-1">Reminder-urile vor apărea aici când ai lecții programate</p>
                </div>

                <!-- Active Reminders List -->
                <div v-else>
                  <div v-for="reminder in upcomingReminders" :key="reminder.id"
                    class="p-3 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg hover:shadow-md transition-all duration-200 cursor-pointer"
                    @click="handleReminderClick(reminder)">

                    <!-- Reminder Content -->
                    <div class="flex items-start justify-between">
                      <div class="flex-1 min-w-0">
                        <!-- Title -->
                        <p class="text-sm font-medium text-yellow-800 truncate">
                          {{ reminder.title }}
                        </p>

                        <!-- Time until reminder -->
                        <div class="flex items-center mt-1 space-x-2">
                          <svg class="w-3 h-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          <p class="text-xs text-yellow-600">{{ formatReminderTime(reminder) }}</p>
                        </div>

                        <!-- Additional info for lesson reminders -->
                        <div v-if="reminder.type && reminder.type.includes('lesson') && reminder.data"
                          class="mt-2 text-xs text-yellow-700">
                          📚 {{ reminder.data.subject }} •
                          {{ reminder.data.lesson_type === 'online' ? '💻 Online' : '👥 Față în față' }}
                        </div>
                      </div>

                      <!-- Reminder Type Icon -->
                      <div class="flex-shrink-0 ml-2">
                        <!-- Lesson reminder icon -->
                        <svg v-if="reminder.type && reminder.type.includes('lesson')" class="w-4 h-4 text-yellow-600"
                          fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <!-- Review reminder icon -->
                        <svg v-else-if="reminder.type && reminder.type.includes('review')"
                          class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        <!-- Default reminder icon -->
                        <svg v-else class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor"
                          viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-3.14 5.86-2.86-5.86zm0 0V12a3 3 0 00-6 0v5l-3 3h12l-3-3z" />
                        </svg>
                      </div>
                    </div>
                  </div>

                  <!-- View All Link (if more than 3 reminders) -->
                  <div v-if="upcomingReminders.length >= 3" class="mt-4 text-center">
                    <button @click="viewAllReminders" class="text-sm text-yellow-700 hover:text-yellow-800 font-medium">
                      Vezi toate reminder-urile ({{ totalRemindersCount }})
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Daily Tip -->
            <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl p-6 text-white">
              <h3 class="text-lg font-bold mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                  </path>
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
                  <span class="font-semibold">{{ dashboardData?.stats?.completed_lessons ||
                    dashboardData?.stats?.total_lessons || 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Review-uri de scris</span>
                  <span class="font-semibold text-yellow-600">{{ dashboardData?.stats?.pending_reviews || 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Abonament</span>
                  <span class="font-semibold" :class="subscriptionStatusClass">{{ subscriptionStatusText }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Spending Modal -->
        <div v-if="showSpendingModal"
          class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
          @click="showSpendingModal = false">
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Detalii cheltuieli</h3>
                <button @click="showSpendingModal = false" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                  </svg>
                </button>
              </div>

              <div class="space-y-4">
                <!-- Total Spent -->
                <div class="bg-purple-50 rounded-xl p-4">
                  <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-700">Total investit</span>
                    <span class="text-xl font-bold text-purple-600">{{ formatCurrency(dashboardData?.stats?.total_spent
                      || 0) }}</span>
                  </div>
                </div>

                <!-- Breakdown -->
                <div class="space-y-3">
                  <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600">Lecții finalizate</span>
                    <span class="font-medium">{{ dashboardData?.stats?.completed_lessons ||
                      dashboardData?.stats?.total_lessons || 0 }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600">Cost mediu/lecție</span>
                    <span class="font-medium">{{ getAveragePerLesson() }}</span>
                  </div>
                </div>

                <!-- Action Button -->
                <button @click="navigateToBookings('all')"
                  class="w-full bg-purple-600 text-white py-3 rounded-xl hover:bg-purple-700 transition-colors font-medium">
                  Vezi toate rezervările
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Subscription Modal -->
  <SubscriptionModal :show="showSubscriptionModal" @close="showSubscriptionModal = false"
    @upgraded="handleSubscriptionUpgraded" @canceled="handleSubscriptionCanceled"
    @renewed="handleSubscriptionRenewed" />

  <!-- Review Modal -->
  <ReviewModal v-if="showReviewModal" :booking="selectedBookingForReview"
    :existing-review="selectedBookingForReview?.review" :is-open="showReviewModal" @close="closeReviewModal"
    @success="handleReviewSuccess" />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useStudentStore } from '@/stores/student'
import { useSubscriptionStore } from '@/stores/subscription'
import { useAdsStore } from '@/stores/ads'
import api from '@/services/api'

// Import existing components
import ReviewModal from '@/components/ReviewModal.vue'
import SubscriptionStatus from '@/components/subscription/SubscriptionStatus.vue'
import SubscriptionModal from '@/components/subscription/SubscriptionModal.vue'
import AdBanner from '@/components/ads/AdBanner.vue'
import AdSidebar from '@/components/ads/AdSidebar.vue'
import AdInline from '@/components/ads/AdInline.vue'

// Composables
const authStore = useAuthStore()
const studentStore = useStudentStore()
const subscriptionStore = useSubscriptionStore()
const adsStore = useAdsStore()
const router = useRouter()

// Reactive data
const loading = ref(false)
const error = ref(null)
const dashboardData = ref(null)
const upcomingBookings = ref([])
const recentBookings = ref([])

// State
const showSpendingModal = ref(false)
const showReviewModal = ref(false)
const selectedBookingForReview = ref(null)
const showSubscriptionModal = ref(false)

// Reminder system state
const upcomingReminders = ref([])
const loadingReminders = ref(false)
const totalRemindersCount = ref(0)

// ===== AD EVENT HANDLERS =====
const handleAdClick = (ad) => {
  console.log('🖱️ Ad clicked:', ad.title)
  // Track analytics if needed
}

const handleAdClose = (adId) => {
  console.log('❌ Ad closed:', adId)
  // Update UI state if needed
}

const handleAdsLoaded = (data) => {
  console.log('📢 Ads loaded:', data)

  // Debug subscription status when ads load
  console.log('Subscription info:', {
    subscription: subscriptionStore.subscription,
    shouldShowAds: subscriptionStore.shouldShowAds,
    isPremium: subscriptionStore.isPremiumUser,
    isInTrial: subscriptionStore.isInTrial,
    hasExpired: subscriptionStore.hasExpired
  })
}

const handleInlineAdClick = (ad) => {
  console.log('🎯 Inline ad clicked:', ad.title)
  // Track conversion if needed
}

// ===== DEBUG FUNCTIONS =====
const debugReloadAds = async () => {
  console.log('🔄 Force reloading ads...')
  await adsStore.getAds()
  console.log('Current ads state:', {
    ads: adsStore.ads,
    shouldShow: adsStore.shouldShowAds,
    bannerAds: adsStore.bannerAds,
    sidebarAds: adsStore.sidebarAds,
    inlineAds: adsStore.inlineAds
  })
}

const debugCheckAdsAPI = async () => {
  try {
    console.log('🔍 Checking ads API directly...')
    const response = await api.get('/ads')
    console.log('Direct API response:', response.data)
  } catch (error) {
    console.error('❌ API error:', error)
  }
}

// Force show ads for testing
const forceShowAds = () => {
  console.log('🎯 Forcing ads to show...')

  // Set subscription to expired trial
  subscriptionStore.subscription = {
    plan_type: 'free_trial',
    status: 'expired',
    shows_ads: true,
    is_in_trial: false,
    trial_days_remaining: 0,
    days_remaining: 0,
    plan_name: 'Trial Expirat'
  }

  console.log('Updated subscription:', subscriptionStore.subscription)
  console.log('Should show ads now:', subscriptionStore.shouldShowAds)

  // Reload ads
  adsStore.getAds()
}

// Expose debug functions globally (for console access)
if (typeof window !== 'undefined') {
  window.debugAds = {
    reloadAds: debugReloadAds,
    checkAPI: debugCheckAdsAPI,
    forceShowAds: forceShowAds,
    subscriptionStore,
    adsStore
  }
}

// Computed properties for subscription and ads
const shouldShowAds = computed(() => subscriptionStore.shouldShowAds)
const randomInlineAd = computed(() => adsStore.getRandomAd('inline'))
const randomSidebarAd = computed(() => adsStore.getRandomAd('sidebar'))
const randomSidebarAd2 = computed(() => {
  const ads = adsStore.getAdsByType('sidebar')
  if (ads.length <= 1) return null
  // Get a different ad than the first one
  return ads[1] || null
})

const subscriptionButtonClass = computed(() => {
  const subscription = subscriptionStore.subscription
  if (!subscription) return 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'

  if (subscription.plan_type === 'premium') {
    return 'bg-green-100 text-green-700 hover:bg-green-200'
  } else if (subscription.is_in_trial) {
    return 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
  } else {
    return 'bg-red-100 text-red-700 hover:bg-red-200'
  }
})

const subscriptionButtonText = computed(() => {
  const subscription = subscriptionStore.subscription
  if (!subscription) return '14 zile trial'

  if (subscription.plan_type === 'premium') {
    return `Premium - ${subscription.days_remaining || 0} zile`
  } else if (subscription.is_in_trial) {
    return `${subscription.trial_days_remaining || 0} zile trial`
  } else {
    return 'Trial expirat'
  }
})

const subscriptionStatusText = computed(() => {
  const subscription = subscriptionStore.subscription
  if (!subscription) return 'Trial'

  if (subscription.plan_type === 'premium') {
    return 'Premium'
  } else if (subscription.is_in_trial) {
    return 'Trial'
  } else {
    return 'Expirat'
  }
})

const subscriptionStatusClass = computed(() => {
  const subscription = subscriptionStore.subscription
  if (!subscription) return 'text-yellow-600'

  if (subscription.plan_type === 'premium') {
    return 'text-green-600'
  } else if (subscription.is_in_trial) {
    return 'text-yellow-600'
  } else {
    return 'text-red-600'
  }
})

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

// Helper functions
const formatNumber = (num) => {
  return new Intl.NumberFormat('ro-RO').format(num || 0)
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('ro-RO', {
    style: 'currency',
    currency: 'RON',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0)
}

const getPluralText = (count, singular, plural) => {
  return count === 1 ? singular : plural
}

const getMonthlyGrowthText = () => {
  const thisMonth = dashboardData.value?.stats?.this_month_bookings || dashboardData.value?.stats?.this_month || 0
  if (thisMonth === 0) return 'Nicio rezervare încă'
  if (thisMonth === 1) return 'Prima rezervare din lună'
  return `+${thisMonth - 1} față de începutul lunii`
}

const getAveragePerLessonText = () => {
  const average = getAveragePerLessonValue()
  if (average === 0) return 'Nicio lecție plătită încă'
  return `~${formatCurrency(average)}/lecție`
}

const getAveragePerLesson = () => {
  const average = getAveragePerLessonValue()
  return formatCurrency(average)
}

const getAveragePerLessonValue = () => {
  const completed = dashboardData.value?.stats?.completed_lessons || dashboardData.value?.stats?.total_lessons || 0
  const spent = dashboardData.value?.stats?.total_spent || 0
  return completed > 0 ? Math.round(spent / completed) : 0
}

const getPendingReviewsText = () => {
  const pending = dashboardData.value?.stats?.pending_reviews || 0
  if (pending === 0) return 'Toate la zi!'
  if (pending === 1) return 'Scrie un review'
  return `${pending} review-uri de scris`
}

const getDisplayedMonthlyCount = () => {
  return dashboardData.value?.stats?.this_month_bookings || dashboardData.value?.stats?.this_month || 0
}

const getInitials = (name) => {
  if (!name || typeof name !== 'string') {
    return 'NA'
  }
  const cleanName = name.trim()
  if (!cleanName) return 'NA'
  const nameParts = cleanName.split(' ').filter(part => part.length > 0)
  if (nameParts.length === 0) return 'NA'
  if (nameParts.length === 1) return nameParts[0][0].toUpperCase()
  return nameParts[0][0].toUpperCase() + nameParts[nameParts.length - 1][0].toUpperCase()
}

const getTutorName = (tutor) => {
  if (!tutor) return 'Tutor necunoscut'
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
    return date.toLocaleDateString('ro-RO', { day: 'numeric', month: 'long' })
  } catch (error) {
    return 'Data necunoscută'
  }
}

const formatTime = (dateString) => {
  if (!dateString) return ''
  try {
    const date = new Date(dateString)
    return date.toLocaleTimeString('ro-RO', { hour: '2-digit', minute: '2-digit' })
  } catch (error) {
    return 'Ora necunoscută'
  }
}

const formatRelativeDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffInDays = Math.floor((now - date) / (1000 * 60 * 60 * 24))

  if (diffInDays === 0) return 'Azi'
  if (diffInDays === 1) return 'Ieri'
  if (diffInDays < 7) return `${diffInDays} zile în urmă`
  if (diffInDays < 30) return `${Math.floor(diffInDays / 7)} săptămâni în urmă`
  return date.toLocaleDateString('ro-RO', { day: 'numeric', month: 'short' })
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

const getSubjectColor = (subject) => {
  const colors = {
    'Matematică': 'bg-gradient-to-br from-blue-500 to-blue-600',
    'Fizică': 'bg-gradient-to-br from-green-500 to-green-600',
    'Chimie': 'bg-gradient-to-br from-red-500 to-red-600',
    'Informatică': 'bg-gradient-to-br from-purple-500 to-purple-600',
    'Română': 'bg-gradient-to-br from-yellow-500 to-yellow-600',
    'Engleză': 'bg-gradient-to-br from-indigo-500 to-indigo-600'
  }
  return colors[subject] || 'bg-gradient-to-br from-gray-500 to-gray-600'
}

const getStatusColor = (status) => {
  const colors = {
    'completed': 'bg-green-500 text-white',
    'confirmed': 'bg-blue-500 text-white',
    'pending': 'bg-yellow-500 text-white',
    'cancelled': 'bg-red-500 text-white'
  }
  return colors[status] || 'bg-gray-500 text-white'
}

const getStatusIcon = (status) => {
  const icons = {
    'completed': '✓',
    'confirmed': '⏳',
    'pending': '⏱',
    'cancelled': '✗'
  }
  return icons[status] || '?'
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
  event.target.parentElement.classList.add('border-2', 'border-gray-300')
}

// Reminder functions
const loadUpcomingReminders = async () => {
  loadingReminders.value = true

  try {
    console.log('🔔 Loading upcoming reminders from API...')

    const response = await api.get('/reminders', {
      params: {
        limit: 5,
        upcoming_only: true
      }
    })

    upcomingReminders.value = response.data.reminders || []
    totalRemindersCount.value = response.data.total_count || 0

    console.log('✅ Loaded reminders:', {
      upcoming: upcomingReminders.value.length,
      total: totalRemindersCount.value,
      data: response.data
    })

  } catch (error) {
    console.error('❌ Error loading reminders:', error)

    if (error.response?.status === 401) {
      console.log('🔒 User not authenticated - need to login')
      upcomingReminders.value = []
    } else if (error.response?.status === 404) {
      console.log('📭 No reminders endpoint available')
      upcomingReminders.value = []
    } else {
      console.error('💥 Server error:', error.response?.data)
      upcomingReminders.value = []
    }

    totalRemindersCount.value = 0
  } finally {
    loadingReminders.value = false
  }
}

const formatReminderTime = (reminder) => {
  const now = new Date()
  const reminderTime = new Date(reminder.scheduled_at)
  const diffInHours = Math.ceil((reminderTime - now) / (1000 * 60 * 60))
  const diffInDays = Math.ceil(diffInHours / 24)

  if (diffInHours < 0) {
    return 'Expirat'
  } else if (diffInHours < 1) {
    const diffInMinutes = Math.ceil((reminderTime - now) / (1000 * 60))
    return `în ${diffInMinutes} ${diffInMinutes === 1 ? 'minut' : 'minute'}`
  } else if (diffInHours === 1) {
    return 'în 1 oră'
  } else if (diffInHours < 24) {
    return `în ${diffInHours} ${diffInHours === 1 ? 'oră' : 'ore'}`
  } else if (diffInHours < 48) {
    return 'mâine'
  } else if (diffInDays === 2) {
    return 'poimâine'
  } else if (diffInDays <= 7) {
    return `în ${diffInDays} zile`
  } else {
    return reminderTime.toLocaleDateString('ro-RO', {
      day: 'numeric',
      month: 'short',
      hour: '2-digit',
      minute: '2-digit'
    })
  }
}

const handleReminderClick = async (reminder) => {
  console.log('📋 Reminder clicked:', reminder)

  if (!reminder.is_read && reminder.id && process.env.NODE_ENV !== 'development') {
    try {
      await api.post(`reminders/${reminder.id}/mark-read`)
    } catch (error) {
      console.error('Error marking reminder as read:', error)
    }
  }

  if (reminder.booking_id) {
    switch (reminder.type) {
      case 'lesson_reminder_student':
        router.push(`/student/bookings/${reminder.booking_id}`)
        break
      case 'review_reminder':
        router.push(`/student/bookings?review=${reminder.booking_id}`)
        break
      default:
        router.push('/student/bookings')
    }
  } else {
    router.push('/notifications')
  }
}

const viewAllReminders = () => {
  router.push('/notifications')
}

// Navigation methods
const navigateToBookings = (filter = 'all') => {
  const query = {}
  switch (filter) {
    case 'completed':
      query.status = 'completed'
      break
    case 'this_month':
      query.month = new Date().getMonth() + 1
      query.year = new Date().getFullYear()
      break
    case 'all':
    default:
      break
  }
  showSpendingModal.value = false
  router.push({ name: 'student-bookings', query })
}

const navigateToPendingReviews = () => {
  const pendingReviews = dashboardData.value?.stats?.pending_reviews || 0
  if (pendingReviews > 0) {
    router.push({ name: 'student-bookings', query: { needsReview: 'true' } })
  } else {
    router.push({ name: 'student-bookings' })
  }
}

const showSpendingBreakdown = () => {
  showSpendingModal.value = true
}

const handleViewLessonDetails = (lesson) => {
  router.push({ name: 'booking-details', params: { id: lesson.id } })
}

// Subscription methods
const handleSubscriptionUpgraded = async () => {
  console.log('🎉 Subscription upgraded successfully')

  // Refresh subscription data
  await subscriptionStore.getSubscription()

  // Refresh ads (should hide them now)
  await adsStore.getAds()

  // Refresh dashboard data
  await loadDashboardData()

  showSubscriptionModal.value = false
}

// Main data loading
const loadDashboardData = async () => {
  loading.value = true
  error.value = null

  try {
    console.log('🔄 Loading dashboard data...')

    let response
    if (studentStore.getDashboard) {
      response = await studentStore.getDashboard()
      dashboardData.value = response
      upcomingBookings.value = response.upcoming_bookings || []
      recentBookings.value = response.recent_bookings || []
    } else {
      response = await api.get('/student/dashboard')
      dashboardData.value = response.data
      upcomingBookings.value = response.data.upcoming_bookings || []
      recentBookings.value = response.data.recent_bookings || []
    }

    // Enhance recent bookings data
    recentBookings.value = recentBookings.value.map(booking => ({
      ...booking,
      tutor: {
        ...booking.tutor,
        user: booking.tutor?.user || {
          id: booking.tutor?.user_id || booking.tutor?.id,
          first_name: booking.tutor?.first_name || booking.tutor?.user?.first_name || 'Tutor',
          last_name: booking.tutor?.last_name || booking.tutor?.user?.last_name || '',
          full_name: getTutorName(booking.tutor)
        }
      },
      subject: booking.subject || { name: 'Necunoscut', icon: '📚' },
      has_review: !!booking.review,
      can_review: booking.status === 'completed' && !booking.review,
      needs_review: booking.status === 'completed' && !booking.review
    }))

    // Load reminders
    await loadUpcomingReminders()

    console.log('✅ Dashboard data loaded:', {
      upcoming: upcomingBookings.value.length,
      recent: recentBookings.value.length,
      reminders: upcomingReminders.value.length,
      stats: dashboardData.value?.stats
    })

  } catch (err) {
    console.error('❌ Error loading dashboard:', err)
    error.value = err.response?.data?.message || 'Eroare la încărcarea dashboard-ului'

    // Set fallback data
    dashboardData.value = {
      stats: {
        total_lessons: 0,
        completed_lessons: 0,
        this_month: 0,
        this_month_bookings: 0,
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

    await loadDashboardData()
    alert('Rezervarea a fost anulată cu succes!')

  } catch (error) {
    console.error('❌ Error cancelling booking:', error)
    alert('Eroare la anularea rezervării. Încearcă din nou.')
  }
}

const openReviewModal = (booking) => {
  console.log('🌟 Opening review modal for booking:', booking)

  selectedBookingForReview.value = {
    id: booking.id,
    tutor_id: booking.tutor_id || booking.tutor?.id,
    subject_id: booking.subject?.id,
    scheduled_at: booking.scheduled_at,
    completed_at: booking.completed_at || booking.scheduled_at,
    status: booking.status,
    price: booking.price,
    duration_minutes: booking.duration_minutes || 60,
    lesson_type: booking.lesson_type,
    tutor: {
      id: booking.tutor_id || booking.tutor?.id,
      first_name: booking.tutor?.first_name || getTutorName(booking.tutor).split(' ')[0] || 'Tutor',
      last_name: booking.tutor?.last_name || getTutorName(booking.tutor).split(' ').slice(1).join(' ') || '',
      full_name: getTutorName(booking.tutor)
    },
    subject: {
      id: booking.subject?.id,
      name: booking.subject?.name || 'Necunoscut',
      icon: booking.subject?.icon || '📚'
    },
    review: booking.review || null,
    has_review: booking.has_review || !!booking.review,
    can_review: booking.status === 'completed' && !booking.review && !booking.has_review
  }

  showReviewModal.value = true
}

const closeReviewModal = () => {
  showReviewModal.value = false
  selectedBookingForReview.value = null
}

const handleReviewSuccess = async (reviewData) => {
  console.log('✅ Review submitted successfully:', reviewData)

  try {
    const bookingId = selectedBookingForReview.value?.id
    if (bookingId) {
      const recentIndex = recentBookings.value.findIndex(b => b.id === bookingId)
      if (recentIndex !== -1) {
        recentBookings.value[recentIndex] = {
          ...recentBookings.value[recentIndex],
          review: reviewData.review,
          has_review: true,
          can_review: false,
          review_rating: reviewData.review?.rating
        }
      }

      if (dashboardData.value?.stats) {
        dashboardData.value.stats.pending_reviews = Math.max(0, (dashboardData.value.stats.pending_reviews || 1) - 1)
      }
    }

    closeReviewModal()
    alert('Review-ul a fost trimis cu succes! Mulțumim pentru feedback.')

  } catch (error) {
    console.error('❌ Error handling review success:', error)
    alert('Review-ul a fost trimis, dar a apărut o eroare la actualizarea interfaței.')
  }
}

// Lifecycle
onMounted(async () => {
  console.log('🚀 Enhanced StudentDashboardView mounted')

  // Load subscription data first
  await subscriptionStore.getSubscription()

  // Load ads data
  await adsStore.getAds()

  // Load dashboard data
  await loadDashboardData()
})
</script>

<style scoped>
/* Enhanced hover effects for cards */
.group:hover .group-hover\:bg-blue-200 {
  background-color: rgb(191 219 254);
}

.group:hover .group-hover\:bg-green-200 {
  background-color: rgb(187 247 208);
}

.group:hover .group-hover\:bg-purple-200 {
  background-color: rgb(221 214 254);
}

.group:hover .group-hover\:bg-yellow-200 {
  background-color: rgb(254 240 138);
}

/* Enhanced focus states for accessibility */
button:focus,
a:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Smooth transitions for all interactive elements */
* {
  transition: all 0.2s ease-in-out;
}

/* Mobile-specific touch optimizations */
@media (hover: none) and (pointer: coarse) {
  .group:hover {
    transform: none;
  }
}

/* Ensure proper spacing for touch targets on mobile */
@media (max-width: 640px) {
  button {
    min-height: 44px;
    min-width: 44px;
  }

  .touch-target {
    padding: 12px;
  }
}
</style>
