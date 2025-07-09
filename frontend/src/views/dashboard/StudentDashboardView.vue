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

      <!-- Loading State -->
      <div v-if="studentStore.loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="studentStore.error" class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
        <div class="flex items-center">
          <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <h3 class="text-red-800 font-semibold">Eroare la încărcarea datelor</h3>
            <p class="text-red-600">{{ studentStore.error }}</p>
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
                <p class="text-sm font-medium text-gray-600">Lecții finalizate</p>
                <p class="text-2xl font-bold text-gray-900">{{ studentStore.dashboard?.stats?.completed_lessons || 0 }}</p>
              </div>
            </div>
          </div>

          <!-- This Month Bookings -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
            <div class="flex items-center">
              <div class="p-3 bg-green-100 rounded-xl">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Luna aceasta</p>
                <p class="text-2xl font-bold text-gray-900">{{ studentStore.dashboard?.stats?.this_month_bookings || 0 }}</p>
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
                <p class="text-2xl font-bold text-gray-900">{{ studentStore.dashboard?.stats?.total_spent || 0 }} RON</p>
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
                <p class="text-2xl font-bold text-gray-900">{{ studentStore.dashboard?.stats?.pending_reviews || 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Left Column - Main Content -->
          <div class="lg:col-span-2 space-y-6">

            <!-- Weekly Stats Overview -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
              <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <svg class="w-6 h-6 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 00-2-2z"></path>
                </svg>
                Progresul săptămânii
              </h2>

              <!-- Weekly Stats Grid -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Weekly Lessons -->
                <div class="text-center">
                  <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                  </div>
                  <p class="text-2xl font-bold text-gray-900 mb-1">{{ weeklyStats.lessons || 0 }}</p>
                  <p class="text-sm text-gray-600">Lecții</p>
                </div>

                <!-- Weekly Spending -->
                <div class="text-center">
                  <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                  </div>
                  <p class="text-2xl font-bold text-gray-900 mb-1">{{ weeklyStats.spent || 0 }}</p>
                  <p class="text-sm text-gray-600">RON investit</p>
                </div>

                <!-- New Tutors -->
                <div class="text-center">
                  <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                  </div>
                  <p class="text-2xl font-bold text-gray-900 mb-1">{{ weeklyStats.new_tutors || 0 }}</p>
                  <p class="text-sm text-gray-600">Tutori noi</p>
                </div>

                <!-- Subjects Studied -->
                <div class="text-center">
                  <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                  </div>
                  <p class="text-2xl font-bold text-gray-900 mb-1">{{ weeklyStats.subjects_studied || 0 }}</p>
                  <p class="text-sm text-gray-600">Materii</p>
                </div>
              </div>

              <!-- Weekly Progress Bar (Optional Visual Enhancement) -->
              <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-700">Progres săptămânal</span>
                  <span class="text-sm text-gray-600">{{ weeklyProgress }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-gradient-to-r from-blue-600 to-purple-600 h-2 rounded-full transition-all duration-500"
                    :style="{ width: weeklyProgress + '%' }"
                  ></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Bazat pe lecțiile programate și finalizate</p>
              </div>
            </div>

            <!-- Upcoming Bookings -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                  <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  Lecții programate
                </h2>
                <button
                  @click="loadDashboardData"
                  class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                >
                  Actualizează
                </button>
              </div>

              <div v-if="!studentStore.dashboard?.upcoming_bookings?.length" class="text-center py-8">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-gray-500 text-lg font-medium">Nu ai lecții programate</p>
                <p class="text-gray-400 mt-1">Caută un tutor și rezervă prima ta lecție!</p>
                <router-link
                  to="/tutors"
                  class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                  Caută tutori
                </router-link>
              </div>

              <div v-else class="space-y-4">
                <div
                  v-for="booking in studentStore.dashboard.upcoming_bookings"
                  :key="booking.id"
                  class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow"
                >
                  <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-4">
                      <!-- Tutor Avatar -->
                      <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                        {{ getInitials(booking.tutor.first_name + ' ' + booking.tutor.last_name) }}
                      </div>

                      <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ booking.subject.name }}</h3>
                        <p class="text-gray-600 text-sm">cu {{ booking.tutor.first_name }} {{ booking.tutor.last_name }}</p>
                        <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                          <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ formatDate(booking.scheduled_at) }}
                          </span>
                          <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ formatTime(booking.scheduled_at) }}
                          </span>
                          <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            {{ booking.price }} RON
                          </span>
                        </div>
                      </div>
                    </div>

                    <!-- Status Badge -->
                    <span
                      :class="{
                        'bg-yellow-100 text-yellow-800': booking.status === 'pending',
                        'bg-green-100 text-green-800': booking.status === 'confirmed'
                      }"
                      class="px-3 py-1 rounded-full text-xs font-medium"
                    >
                      {{ getStatusLabel(booking.status) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Recent Activity & Learning Tips -->
          <div class="space-y-6">

            <!-- Recent Activity -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
              <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Activitate recentă
              </h3>

              <div v-if="!recentActivity.length" class="text-center py-6">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="text-gray-500 text-sm">Nu ai activitate recentă</p>
                <p class="text-gray-400 text-xs mt-1">Activitățile tale vor apărea aici</p>
              </div>

              <div v-else class="space-y-3">
                <div
                  v-for="activity in recentActivity"
                  :key="activity.id"
                  class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <!-- Activity Icon -->
                  <div
                    :class="{
                      'bg-green-100 text-green-600': activity.type === 'booking_completed',
                      'bg-blue-100 text-blue-600': activity.type === 'booking_created',
                      'bg-yellow-100 text-yellow-600': activity.type === 'review_submitted',
                      'bg-red-100 text-red-600': activity.type === 'booking_cancelled'
                    }"
                    class="p-2 rounded-full flex-shrink-0"
                  >
                    <svg v-if="activity.type === 'booking_completed'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <svg v-else-if="activity.type === 'booking_created'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <svg v-else-if="activity.type === 'review_submitted'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </div>

                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
                    <p class="text-xs text-gray-600 mt-1">{{ activity.description }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ formatRelativeTime(activity.created_at) }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Learning Tips -->
            <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-6 border border-blue-200/50">
              <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                Sfat pentru învățare
              </h3>
              <div class="bg-white/70 rounded-lg p-4">
                <p class="text-gray-700 text-sm leading-relaxed">
                  {{ currentTip }}
                </p>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
              <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Acțiuni rapide
              </h3>

              <div class="space-y-3">
                <router-link
                  to="/tutors"
                  class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors group"
                >
                  <div class="p-2 bg-blue-600 rounded-lg mr-3 group-hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Caută tutori</p>
                    <p class="text-sm text-gray-600">Găsește tutorul perfect</p>
                  </div>
                </router-link>

                <button
                  v-if="studentStore.dashboard?.stats?.pending_reviews > 0"
                  @click="goToReviews"
                  class="w-full flex items-center p-3 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition-colors group"
                >
                  <div class="p-2 bg-yellow-600 rounded-lg mr-3 group-hover:bg-yellow-700 transition-colors">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                  </div>
                  <div class="text-left">
                    <p class="font-medium text-gray-900">Completează review-uri</p>
                    <p class="text-sm text-gray-600">{{ studentStore.dashboard.stats.pending_reviews }} în așteptare</p>
                  </div>
                </button>

                <router-link
                  to="/student/bookings"
                  class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors group"
                >
                  <div class="p-2 bg-purple-600 rounded-lg mr-3 group-hover:bg-purple-700 transition-colors">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Vezi toate rezervările</p>
                    <p class="text-sm text-gray-600">Istoric și programări</p>
                  </div>
                </router-link>
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

// Stores
const authStore = useAuthStore()
const studentStore = useStudentStore()

// Reactive data
const recentActivity = ref([])

// Computed
const weeklyStats = computed(() => {
  return studentStore.dashboard?.weekly_stats || {
    lessons: 0,
    spent: 0,
    new_tutors: 0,
    subjects_studied: 0
  }
})

const weeklyProgress = computed(() => {
  const stats = weeklyStats.value
  const totalPoints = stats.lessons * 25 + (stats.new_tutors * 15) + (stats.subjects_studied * 10)
  return Math.min(100, totalPoints)
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

// Methods
const loadDashboardData = async () => {
  try {
    await studentStore.getDashboard()
    generateRecentActivity()
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  }
}

const generateRecentActivity = () => {
  // Generate mock recent activity based on dashboard data
  const activities = []

  const dashboard = studentStore.dashboard
  if (!dashboard) return

  // Add activities based on recent bookings
  if (dashboard.recent_bookings && dashboard.recent_bookings.length > 0) {
    dashboard.recent_bookings.slice(0, 3).forEach(booking => {
      activities.push({
        id: `completed-${booking.id}`,
        type: 'booking_completed',
        title: `Lecție finalizată`,
        description: `${booking.subject.name} cu ${booking.tutor.first_name} ${booking.tutor.last_name}`,
        created_at: booking.completed_at || booking.scheduled_at
      })

      if (booking.review) {
        activities.push({
          id: `review-${booking.id}`,
          type: 'review_submitted',
          title: `Review trimis`,
          description: `Ai evaluat lecția de ${booking.subject.name} cu ${booking.review.rating} stele`,
          created_at: booking.review.created_at
        })
      }
    })
  }

  // Add activities for upcoming bookings
  if (dashboard.upcoming_bookings && dashboard.upcoming_bookings.length > 0) {
    dashboard.upcoming_bookings.slice(0, 2).forEach(booking => {
      activities.push({
        id: `created-${booking.id}`,
        type: 'booking_created',
        title: `Lecție programată`,
        description: `${booking.subject.name} cu ${booking.tutor.first_name} ${booking.tutor.last_name}`,
        created_at: new Date(Date.now() - Math.random() * 7 * 24 * 60 * 60 * 1000).toISOString()
      })
    })
  }

  // Sort by date and limit to 5 most recent
  recentActivity.value = activities
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    .slice(0, 5)
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ro-RO', {
    day: 'numeric',
    month: 'long'
  })
}

const formatTime = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleTimeString('ro-RO', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatRelativeTime = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))

  if (diffInHours < 1) {
    const diffInMinutes = Math.floor((now - date) / (1000 * 60))
    return `acum ${diffInMinutes} ${diffInMinutes === 1 ? 'minut' : 'minute'}`
  } else if (diffInHours < 24) {
    return `acum ${diffInHours} ${diffInHours === 1 ? 'oră' : 'ore'}`
  } else {
    const diffInDays = Math.floor(diffInHours / 24)
    return `acum ${diffInDays} ${diffInDays === 1 ? 'zi' : 'zile'}`
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

const goToReviews = () => {
  // Navigate to bookings page with completed filter
  router.push('/student/bookings?status=completed')
}

// Lifecycle
onMounted(() => {
  loadDashboardData()
})
</script>
