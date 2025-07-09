// COMPLETE FIX: Replace your entire TutorsView.vue with this corrected version

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <!-- Loading Overlay -->
    <div
      v-if="loading"
      class="fixed inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center"
    >
      <div class="text-center">
        <div class="relative">
          <div class="w-16 h-16 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Căutăm tutori...</h3>
        <p class="text-gray-600">Te rugăm să aștepți</p>
      </div>
    </div>

    <!-- Header with Search -->
    <div class="bg-white/80 backdrop-blur-xl border-b border-gray-200/50 sticky top-16 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Search Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
          <!-- Subject Filter -->
          <div class="relative">
            <select
              v-model="filters.subject"
              @change="searchTutors"
              :disabled="loading"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none disabled:opacity-50"
            >
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
            <select
              v-model="filters.location"
              @change="searchTutors"
              :disabled="loading"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none disabled:opacity-50"
            >
              <option value="">Toate locațiile</option>
              <option v-for="location in locations" :key="location.id" :value="location.slug">
                {{ location.city }}, {{ location.county }}
              </option>
            </select>
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>

          <!-- Lesson Type Filter -->
          <div class="relative">
            <select
              v-model="filters.lesson_type"
              @change="searchTutors"
              :disabled="loading"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none disabled:opacity-50"
            >
              <option value="">Tip lecție</option>
              <option value="online">Online</option>
              <option value="in_person">La domiciliu</option>
            </select>
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>

          <!-- Price Range Filter -->
          <div class="relative">
            <select
              v-model="filters.price_range"
              @change="searchTutors"
              :disabled="loading"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none disabled:opacity-50"
            >
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
            <select
              v-model="filters.sort_by"
              @change="searchTutors"
              :disabled="loading"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none disabled:opacity-50"
            >
              <option value="">Sortează</option>
              <option value="rating">Rating</option>
              <option value="price_low">Preț crescător</option>
              <option value="price_high">Preț descrescător</option>
              <option value="experience">Experiență</option>
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
              :disabled="loading"
              type="text"
              placeholder="Caută tutori..."
              class="w-full px-4 py-3 pl-10 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
            >
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
              <svg v-if="!loading" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <div v-else class="w-4 h-4 border-2 border-gray-300 border-t-blue-600 rounded-full animate-spin"></div>
            </div>
          </div>
        </div>

        <!-- Results Info and Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div class="flex items-center space-x-4">
            <p class="text-gray-600">
              <span v-if="!loading && tutors.length > 0">
                {{ totalResults }} {{ totalResults === 1 ? 'tutor găsit' : 'tutori găsiți' }}
              </span>
              <span v-else-if="!loading && tutors.length === 0">
                Niciun tutor găsit
              </span>
              <span v-else class="flex items-center">
                <div class="w-4 h-4 border-2 border-gray-300 border-t-blue-600 rounded-full animate-spin mr-2"></div>
                Căutăm...
              </span>
            </p>

            <!-- Clear Filters Button -->
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              :disabled="loading"
              class="text-sm text-blue-600 hover:text-blue-800 font-medium disabled:opacity-50"
            >
              Șterge filtrele
            </button>
          </div>

          <!-- View Mode Toggle -->
          <div class="flex items-center space-x-2">
            <button
              @click="viewMode = 'grid'"
              :disabled="loading"
              :class="viewMode === 'grid' ? 'bg-blue-100 text-blue-600' : 'text-gray-400 hover:text-gray-600'"
              class="p-2 rounded-lg transition-colors disabled:opacity-50"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
              </svg>
            </button>
            <button
              @click="viewMode = 'list'"
              :disabled="loading"
              :class="viewMode === 'list' ? 'bg-blue-100 text-blue-600' : 'text-gray-400 hover:text-gray-600'"
              class="p-2 rounded-lg transition-colors disabled:opacity-50"
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
      <!-- Empty State -->
      <div v-if="!loading && tutors.length === 0" class="text-center py-12">
        <div class="w-24 h-24 mx-auto mb-4 text-gray-300">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Niciun tutor găsit</h3>
        <p class="text-gray-600 mb-6">Încearcă să modifici filtrele de căutare pentru a găsi tutori.</p>
        <button
          @click="clearFilters"
          class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors"
        >
          Șterge filtrele
        </button>
      </div>

      <!-- Tutors Grid/List -->
      <div v-else-if="!loading">
        <!-- Grid View -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div
            v-for="tutor in tutors"
            :key="tutor.id"
            class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-lg border border-gray-200/50 hover:shadow-2xl hover:border-blue-200 transition-all duration-500 group cursor-pointer overflow-hidden"
            @click="viewTutor(tutor.id)"
          >
            <!-- Tutor Card Content -->
            <div class="p-8">
              <!-- Header -->
              <div class="flex items-start justify-between mb-6">
                <div class="flex items-center space-x-6">
                  <!-- Avatar -->
                  <div class="relative flex-shrink-0">
                    <img
                      v-if="tutor.profile_image_url"
                      :src="tutor.profile_image_url"
                      :alt="`${tutor.user?.first_name || ''} ${tutor.user?.last_name || ''}`"
                      class="w-20 h-20 rounded-2xl object-cover border-3 border-white shadow-xl"
                    >
                    <div
                      v-else
                      class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl shadow-xl"
                    >
                      {{ (tutor.user?.first_name?.[0] || '') + (tutor.user?.last_name?.[0] || '') }}
                    </div>

                    <!-- Online Status -->
                    <div v-if="tutor.offers_online" class="absolute -bottom-2 -right-2 w-7 h-7 bg-green-500 border-3 border-white rounded-xl flex items-center justify-center">
                      <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                      </svg>
                    </div>

                    <!-- Verified Badge -->
                    <div v-if="tutor.is_verified" class="absolute -top-2 -left-2 w-7 h-7 bg-blue-500 border-3 border-white rounded-xl flex items-center justify-center">
                      <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                      </svg>
                    </div>
                  </div>

                  <!-- Info -->
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-3 mb-2">
                      <h3 class="text-2xl font-bold text-gray-900 truncate">
                        {{ tutor.user?.first_name || 'Nume' }} {{ tutor.user?.last_name || 'Prenume' }}
                      </h3>
                    </div>

                    <div class="flex items-center space-x-2 text-gray-600 mb-3">
                      <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                      <span class="font-medium">{{ tutor.location?.city || 'Necunoscut' }}, {{ tutor.location?.county || '' }}</span>
                    </div>

                    <!-- Rating -->
                    <div v-if="(tutor.total_reviews || 0) > 0" class="flex items-center space-x-2 mb-1">
                      <div class="flex">
                        <svg v-for="star in 5" :key="star" class="w-5 h-5" :class="star <= Math.round(parseFloat(tutor.rating) || 0) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                      </div>
                      <span class="text-lg font-semibold text-gray-900">{{ formatRating(tutor.rating) }}</span>
                      <span class="text-gray-500">({{ tutor.total_reviews || 0 }} {{ (tutor.total_reviews || 0) === 1 ? 'recenzie' : 'recenzii' }})</span>
                    </div>
                  </div>
                </div>

                <!-- Featured Badge -->
                <div v-if="tutor.is_featured" class="px-4 py-2 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-sm font-semibold rounded-2xl shadow-lg">
                  ⭐ Recomandat
                </div>
              </div>

              <!-- Bio -->
              <div class="mb-6">
                <p class="text-gray-700 leading-relaxed line-clamp-3">
                  {{ tutor.bio || 'Tutor experimentat cu pasiune pentru predare și rezultate excelente.' }}
                </p>
              </div>

              <!-- Subjects -->
              <div class="mb-6">
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="subject in (tutor.subjects || []).slice(0, 4)"
                    :key="subject.id"
                    class="px-3 py-2 bg-gradient-to-r from-blue-50 to-purple-50 text-blue-700 text-sm font-medium rounded-xl border border-blue-100"
                  >
                    {{ subject.name }}
                  </span>
                  <span
                    v-if="(tutor.subjects || []).length > 4"
                    class="px-3 py-2 bg-gray-100 text-gray-600 text-sm font-medium rounded-xl"
                  >
                    +{{ (tutor.subjects || []).length - 4 }} altele
                  </span>
                </div>
              </div>

              <!-- Lesson Types & Stats -->
              <div class="flex items-center justify-between mb-6">
                <div class="flex space-x-3">
                  <div v-if="tutor.offers_online" class="flex items-center px-3 py-2 bg-green-100 text-green-700 rounded-xl text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                    </svg>
                    Online
                  </div>
                  <div v-if="tutor.offers_in_person" class="flex items-center px-3 py-2 bg-blue-100 text-blue-700 rounded-xl text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                    La domiciliu
                  </div>
                </div>

                <div v-if="tutor.total_lessons > 0" class="text-sm text-gray-500">
                  {{ tutor.total_lessons }} {{ tutor.total_lessons === 1 ? 'lecție predată' : 'lecții predate' }}
                </div>
              </div>

              <!-- Footer -->
              <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                <div class="text-left">
                  <div class="text-3xl font-bold text-gray-900">
                    {{ tutor.hourly_rate || 0 }}
                    <span class="text-lg font-normal text-gray-600">RON/oră</span>
                  </div>
                </div>
                <button class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-2xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform group-hover:scale-105">
                  Vezi profil
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch, computed, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'

export default {
  name: 'TutorsView',
  setup() {
    const route = useRoute()
    const router = useRouter()

    const tutors = ref([])
    const subjects = ref([])
    const locations = ref([])
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

    // FIXED: Add missing computed property
    const hasActiveFilters = computed(() => {
      return Object.entries(filters.value).some(([key, value]) => {
        if (key === 'page') return false
        return value && value !== ''
      })
    })

    // FIXED: Safe rating formatter
    const formatRating = (rating) => {
      const num = parseFloat(rating) || 0
      return num.toFixed(1)
    }

    const loadSubjects = async () => {
      try {
        const response = await api.get('subjects')
        subjects.value = response.data.subjects || []
        console.log('✅ Subjects loaded:', subjects.value.length)
      } catch (error) {
        console.error('❌ Error loading subjects:', error)
        subjects.value = []
      }
    }

    const loadLocations = async () => {
      try {
        const response = await api.get('locations')
        locations.value = response.data.locations || []
        console.log('✅ Locations loaded:', locations.value.length)
      } catch (error) {
        console.error('❌ Error loading locations:', error)
        locations.value = []
      }
    }

    const searchTutors = async () => {
      // Prevent multiple simultaneous requests
      if (loading.value) {
        console.log('Already loading, skipping request')
        return
      }

      loading.value = true
      console.log('🔄 Starting tutor search...')

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

        console.log('Search params:', params.toString())

        const response = await api.get(`tutors?${params.toString()}`)

        console.log('✅ API response received:', {
          tutors: response.data.tutors?.length || 0,
          pagination: response.data.pagination,
          total: response.data.pagination?.total || 0
        })

        tutors.value = response.data.tutors || []
        pagination.value = response.data.pagination || {}
        totalResults.value = response.data.pagination?.total || 0

        // Update URL without page reload
        const query = { ...filters.value }
        Object.keys(query).forEach(key => {
          if (!query[key]) delete query[key]
        })

        await router.replace({ query }).catch(() => {})

        console.log('✅ Search completed successfully')

      } catch (error) {
        console.error('❌ Error searching tutors:', error)
        tutors.value = []
        totalResults.value = 0
        pagination.value = {}
      } finally {
        // CRITICAL: Multiple ways to ensure loading stops
        console.log('🛑 Stopping loading...')
        loading.value = false

        // Force update in next tick
        await nextTick()
        loading.value = false

        // Backup timeout
        setTimeout(() => {
          loading.value = false
          console.log('🛑 Final: Loading set to false')
        }, 100)
      }
    }

    const debounceSearch = () => {
      if (searchTimeout) {
        clearTimeout(searchTimeout)
      }

      searchTimeout = setTimeout(() => {
        filters.value.page = 1
        searchTutors()
      }, 500)
    }

    const clearFilters = () => {
      if (searchTimeout) {
        clearTimeout(searchTimeout)
      }

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

    const viewTutor = (tutorId) => {
      if (!loading.value && tutorId) {
        router.push({ name: 'tutor-profile-public', params: { id: tutorId } })
      }
    }

    // Watch for route changes
    watch(() => route.query, (newQuery, oldQuery) => {
      if (!loading.value && JSON.stringify(newQuery) !== JSON.stringify(oldQuery)) {
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
      }
    }, { immediate: false })

    onMounted(async () => {
      console.log('TutorsView mounted')

      try {
        await Promise.all([
          loadSubjects(),
          loadLocations()
        ])

        await searchTutors()
      } catch (error) {
        console.error('Error during mount:', error)
        loading.value = false
      }
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
      hasActiveFilters,
      formatRating,
      searchTutors,
      debounceSearch,
      clearFilters,
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
