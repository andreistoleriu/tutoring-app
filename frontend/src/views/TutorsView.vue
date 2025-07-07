<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <!-- Header with Search -->
    <div class="bg-white/80 backdrop-blur-xl border-b border-gray-200/50 sticky top-16 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Search Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
          <!-- Subject Filter -->
          <div class="relative">
            <select v-model="filters.subject" @change="searchTutors" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none">
              <option value="">Toate materiile</option>
              <option v-for="subject in subjects" :key="subject.id" :value="subject.slug">
                {{ subject.name }}
              </option>
            </select>
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>

          <!-- Location Filter -->
          <div class="relative">
            <select v-model="filters.location" @change="searchTutors" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none">
              <option value="">Toate locațiile</option>
              <optgroup v-for="(cities, county) in locations" :key="county" :label="county">
                <option v-for="city in cities" :key="city.id" :value="city.city">
                  {{ city.city }}
                </option>
              </optgroup>
            </select>
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>


          <!-- Lesson Type Filter -->
          <div class="relative">
            <select v-model="filters.lesson_type" @change="searchTutors" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none">
              <option value="">Tip lecție</option>
              <option value="online">Online</option>
              <option value="in_person">Față în față</option>
            </select>
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>

          <!-- Price Range -->
          <div class="relative">
            <select v-model="filters.price_range" @change="searchTutors" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none">
              <option value="">Preț/oră</option>
              <option value="0-50">0-50 RON</option>
              <option value="50-100">50-100 RON</option>
              <option value="100-150">100-150 RON</option>
              <option value="150+">150+ RON</option>
            </select>
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>

          <!-- Sort By -->
          <div class="relative">
            <select v-model="filters.sort_by" @change="searchTutors" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none">
              <option value="">Sortează</option>
              <option value="rating">Rating</option>
              <option value="price_low">Preț crescător</option>
              <option value="price_high">Preț descrescător</option>
              <option value="popular">Popularitate</option>
              <option value="featured">Recomandat</option>
            </select>
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>

          <!-- Search Input -->
          <div class="relative">
            <input
              v-model="filters.search"
              @input="debounceSearch"
              type="text"
              placeholder="Caută tutori..."
              class="w-full px-4 py-3 pl-10 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Results Info -->
        <div class="flex justify-between items-center">
          <p class="text-gray-600">
            <span v-if="!loading">
              {{ totalResults }} tutori găsiți
            </span>
            <span v-else>Căutare...</span>
          </p>
          <div class="flex items-center space-x-2">
            <button
              @click="viewMode = 'grid'"
              :class="viewMode === 'grid' ? 'bg-blue-100 text-blue-600' : 'text-gray-400'"
              class="p-2 rounded-lg transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
              </svg>
            </button>
            <button
              @click="viewMode = 'list'"
              :class="viewMode === 'list' ? 'bg-blue-100 text-blue-600' : 'text-gray-400'"
              class="p-2 rounded-lg transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 6" :key="i" class="animate-pulse">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-start space-x-4">
              <div class="w-16 h-16 bg-gray-200 rounded-full"></div>
              <div class="flex-1">
                <div class="h-4 bg-gray-200 rounded mb-2"></div>
                <div class="h-3 bg-gray-200 rounded mb-4 w-2/3"></div>
                <div class="space-y-2">
                  <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                  <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Results -->
      <div v-else-if="tutors.length === 0" class="text-center py-16">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Niciun tutor găsit</h3>
        <p class="text-gray-600 mb-6">Încearcă să modifici filtrele de căutare pentru a găsi tutori.</p>
        <button @click="clearFilters" class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
          Șterge filtrele
        </button>
      </div>

      <!-- Tutors Grid -->
      <div v-else>
        <!-- Grid View -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="tutor in tutors"
            :key="tutor.id"
            class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 hover:shadow-xl hover:border-blue-200 transition-all duration-300 group cursor-pointer"
            @click="viewTutor(tutor.id)"
          >
            <!-- Tutor Card -->
            <div class="p-6">
              <!-- Header -->
              <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-4">
                  <!-- Avatar -->
                  <div class="relative">
                    <img
                      v-if="tutor.profile_image"
                      :src="tutor.profile_image"
                      :alt="tutor.user.full_name"
                      class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-lg"
                    >
                    <div
                      v-else
                      class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white font-bold text-xl shadow-lg"
                    >
                      {{ tutor.user.first_name[0] }}{{ tutor.user.last_name[0] }}
                    </div>
                    <!-- Online Status -->
                    <div v-if="tutor.offers_online" class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-2 border-white rounded-full"></div>
                  </div>

                  <!-- Info -->
                    <div class="flex items-center space-x-1 text-sm text-gray-600 mb-1">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                      <span class="font-medium">{{ tutor.location.city }}, {{ tutor.location.county }}</span>
                    </div>
                </div>

                <!-- Featured Badge -->
                <div v-if="tutor.is_featured" class="px-2 py-1 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-medium rounded-full">
                  ⭐ Recomandat
                </div>
              </div>

              <!-- Bio -->
              <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                {{ tutor.bio || 'Tutor experimentat cu pasiune pentru predare.' }}
              </p>

              <!-- Subjects -->
              <div class="flex flex-wrap gap-2 mb-4">
                <span
                  v-for="subject in tutor.subjects.slice(0, 3)"
                  :key="subject.id"
                  class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full"
                >
                  {{ subject.name }}
                </span>
                <span
                  v-if="tutor.subjects.length > 3"
                  class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full"
                >
                  +{{ tutor.subjects.length - 3 }}
                </span>
              </div>

              <!-- Stats -->
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                  <!-- Rating -->
                  <div class="flex items-center space-x-1">
                    <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="font-medium">{{ tutor.rating }}</span>
                    <span>({{ tutor.total_reviews }})</span>
                  </div>

                  <!-- Lessons -->
                  <div class="flex items-center space-x-1">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ tutor.total_lessons }} lecții</span>
                  </div>
                </div>
              </div>

              <!-- Lesson Types -->
              <div class="flex items-center space-x-2 mb-4">
                <span v-if="tutor.offers_online" class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full flex items-center space-x-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                  <span>Online</span>
                </span>
                <span v-if="tutor.offers_in_person" class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full flex items-center space-x-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  <span>Față în față</span>
                </span>
              </div>

              <!-- Location Badge for Face-to-Face -->
              <div v-if="tutor.offers_in_person" class="mb-4">
                <div class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 text-sm font-medium rounded-full border border-blue-200">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  Disponibil în {{ tutor.location.city }}
                </div>
              </div>

              <!-- Footer -->
              <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <div class="text-right">
                  <p class="text-2xl font-bold text-gray-900">{{ tutor.hourly_rate }} <span class="text-sm font-normal text-gray-600">RON/oră</span></p>
                </div>
                <button class="px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform group-hover:scale-105">
                  Vezi profil
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- List View -->
        <div v-else class="space-y-4">
          <div
            v-for="tutor in tutors"
            :key="tutor.id"
            class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 hover:shadow-lg transition-all duration-300 cursor-pointer"
            @click="viewTutor(tutor.id)"
          >
            <div class="p-6 flex items-center space-x-6">
              <!-- Avatar -->
              <div class="relative flex-shrink-0">
                <img
                  v-if="tutor.profile_image"
                  :src="tutor.profile_image"
                  :alt="tutor.user.full_name"
                  class="w-20 h-20 rounded-full object-cover border-2 border-white shadow-lg"
                >
                <div
                  v-else
                  class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white font-bold text-2xl shadow-lg"
                >
                  {{ tutor.user.first_name[0] }}{{ tutor.user.last_name[0] }}
                </div>
                <div v-if="tutor.offers_online" class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 border-2 border-white rounded-full"></div>
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                      <h3 class="text-xl font-semibold text-gray-900">{{ tutor.user.full_name }}</h3>
                      <div v-if="tutor.is_featured" class="px-3 py-1 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-medium rounded-full">
                        ⭐ Recomandat
                      </div>
                    </div>

                    <div class="flex items-center space-x-1 text-gray-600 mb-3">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                      <span class="font-medium">{{ tutor.location.city }}, {{ tutor.location.county }}</span>
                    </div>

                    <p class="text-gray-600 mb-4 line-clamp-2">
                      {{ tutor.bio || 'Tutor experimentat cu pasiune pentru predare.' }}
                    </p>

                    <!-- Subjects -->
                    <div class="flex flex-wrap gap-2 mb-4">
                      <span
                        v-for="subject in tutor.subjects.slice(0, 5)"
                        :key="subject.id"
                        class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full"
                      >
                        {{ subject.name }}
                      </span>
                      <span
                        v-if="tutor.subjects.length > 5"
                        class="px-3 py-1 bg-gray-100 text-gray-600 text-sm font-medium rounded-full"
                      >
                        +{{ tutor.subjects.length - 5 }}
                      </span>
                    </div>

                    <!-- Stats and Lesson Types -->
                    <div class="flex items-center space-x-6 text-sm text-gray-600">
                      <!-- Rating -->
                      <div class="flex items-center space-x-1">
                        <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="font-medium">{{ tutor.rating }}</span>
                        <span>({{ tutor.total_reviews }} review{{ tutor.total_reviews !== 1 ? 'uri' : '' }})</span>
                      </div>

                      <!-- Lessons -->
                      <div class="flex items-center space-x-1">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ tutor.total_lessons }} lecții completate</span>
                      </div>

                      <!-- Lesson Types -->
                      <div class="flex items-center space-x-2">
                        <span v-if="tutor.offers_online" class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Online</span>
                        <span v-if="tutor.offers_in_person" class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full">Față în față</span>
                      </div>
                    </div>
                  </div>

                  <!-- Location Badge for Face-to-Face -->
              <div v-if="tutor.offers_in_person" class="mb-4">
                <div class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 text-sm font-medium rounded-full border border-blue-200">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  Disponibil în {{ tutor.location.city }}
                </div>
              </div>

                  <!-- Price and Action -->
                  <div class="text-right flex-shrink-0 ml-6">
                    <p class="text-3xl font-bold text-gray-900 mb-2">{{ tutor.hourly_rate }} <span class="text-sm font-normal text-gray-600">RON/oră</span></p>
                    <button class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                      Vezi profil
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="mt-12 flex justify-center">
          <nav class="flex items-center space-x-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-4 py-2 text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Anterior
            </button>

            <button
              v-for="page in getVisiblePages()"
              :key="page"
              @click="changePage(page)"
              :class="page === pagination.current_page ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100'"
              class="px-4 py-2 rounded-lg font-medium transition-colors"
            >
              {{ page }}
            </button>

            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-4 py-2 text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Următor
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'

export default {
  name: 'TutorsView',
  setup() {
    const route = useRoute()
    const router = useRouter()

    const tutors = ref([])
    const subjects = ref([])
    const locations = ref({})
    const loading = ref(false)
    const viewMode = ref('grid')
    const pagination = ref(null)
    const totalResults = ref(0)

    const filters = ref({
      subject: route.query.subject || '',
      location: route.query.location || '',
      lesson_type: route.query.lesson_type || '',
      price_range: route.query.price_range || '',
      sort_by: route.query.sort_by || '',
      search: route.query.search || '',
      page: parseInt(route.query.page) || 1
    })

    let searchTimeout = null

    const loadSubjects = async () => {
      try {
        const response = await api.get('/subjects')
        subjects.value = response.data.subjects
      } catch (error) {
        console.error('Error loading subjects:', error)
      }
    }

    const loadLocations = async () => {
      try {
        const response = await api.get('/locations')
        locations.value = response.data.locations
      } catch (error) {
        console.error('Error loading locations:', error)
      }
    }

    const searchTutors = async () => {
      loading.value = true
      try {
        const params = new URLSearchParams()

        Object.keys(filters.value).forEach(key => {
          if (filters.value[key]) {
            if (key === 'price_range') {
             const [min, max] = filters.value[key].split('-')
             if (min) params.append('min_price', min)
             if (max && max !== '+') params.append('max_price', max)
           } else {
             params.append(key, filters.value[key])
           }
         }
       })

       const response = await api.get(`/tutors?${params.toString()}`)
       tutors.value = response.data.tutors
       pagination.value = response.data.pagination
       totalResults.value = response.data.pagination.total

       // Update URL without page reload
       const query = { ...filters.value }
       Object.keys(query).forEach(key => {
         if (!query[key]) delete query[key]
       })
       router.replace({ query })

     } catch (error) {
       console.error('Error searching tutors:', error)
       tutors.value = []
       totalResults.value = 0
     } finally {
       loading.value = false
     }
   }

   const debounceSearch = () => {
     clearTimeout(searchTimeout)
     searchTimeout = setTimeout(() => {
       searchTutors()
     }, 500)
   }

   const clearFilters = () => {
     filters.value = {
       subject: '',
       location: '',
       lesson_type: '',
       price_range: '',
       sort_by: '',
       search: '',
       page: 1
     }
     searchTutors()
   }

   const changePage = (page) => {
     if (page >= 1 && page <= pagination.value.last_page) {
       filters.value.page = page
       searchTutors()
       window.scrollTo({ top: 0, behavior: 'smooth' })
     }
   }

   const getVisiblePages = () => {
     if (!pagination.value) return []

     const current = pagination.value.current_page
     const last = pagination.value.last_page
     const delta = 2
     const range = []

     for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
       range.push(i)
     }

     if (current - delta > 2) {
       range.unshift('...')
     }
     if (current + delta < last - 1) {
       range.push('...')
     }

     range.unshift(1)
     if (last > 1) {
       range.push(last)
     }

     return range.filter((item, index, arr) => arr.indexOf(item) === index)
   }

   const viewTutor = (tutorId) => {
     router.push(`/tutors/${tutorId}`)
   }

   // Watch for route changes
   watch(() => route.query, (newQuery) => {
     filters.value = {
       subject: newQuery.subject || '',
       location: newQuery.location || '',
       lesson_type: newQuery.lesson_type || '',
       price_range: newQuery.price_range || '',
       sort_by: newQuery.sort_by || '',
       search: newQuery.search || '',
       page: parseInt(newQuery.page) || 1
     }
     searchTutors()
   })

   onMounted(() => {
     loadSubjects()
     loadLocations()
     searchTutors()
   })

   return {
     tutors,
     subjects,
     locations,
     loading,
     viewMode,
     pagination,
     totalResults,
     filters,
     searchTutors,
     debounceSearch,
     clearFilters,
     changePage,
     getVisiblePages,
     viewTutor
   }
 }
}
</script>

<style scoped>
.line-clamp-2 {
 display: -webkit-box;
 -webkit-line-clamp: 2;
 -webkit-box-orient: vertical;
 overflow: hidden;
}
</style>
