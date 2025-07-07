<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <!-- Hero Section -->
    <main class="relative overflow-hidden">
      <!-- Background decoration -->
      <div class="absolute inset-0 bg-gradient-to-br from-blue-600/5 via-purple-600/5 to-pink-600/5"></div>
      <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-blue-400 to-purple-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
      <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-purple-400 to-pink-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse delay-1000"></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center">
          <!-- Main Heading -->
          <h1 class="text-5xl md:text-7xl font-bold text-gray-900 mb-6">
            Găsește tutorul
            <span class="text-gradient block">perfect pentru tine</span>
          </h1>

          <p class="text-xl text-gray-600 mb-12 max-w-3xl mx-auto leading-relaxed">
            Conectează-te cu cei mai buni tutori din România. Învață orice materie,
            online sau față în față, la propriul tău ritm.
          </p>

          <!-- Search Section -->
          <div class="max-w-2xl mx-auto mb-16">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/50 p-8">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <!-- Subject Search -->
                <div class="relative">
                  <select v-model="searchForm.subject" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 appearance-none">
                    <option value="">Alege materia</option>
                    <option v-for="subject in subjects" :key="subject.id" :value="subject.slug">
                      {{ subject.name }}
                    </option>
                  </select>
                  <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </div>
                </div>

                <!-- Location Search -->
                <div class="relative">
                  <select v-model="searchForm.location" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 appearance-none">
                    <option value="">Alege județul</option>
                    <option v-for="(cities, county) in locations" :key="county" :value="county">
                      {{ county }}
                    </option>
                  </select>
                  <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </div>
                </div>

                <!-- Lesson Type -->
                <div class="relative">
                  <select v-model="searchForm.lessonType" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 appearance-none">
                    <option value="">Tip lecție</option>
                    <option value="online">Online</option>
                    <option value="in_person">Față în față</option>
                  </select>
                  <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </div>
                </div>
              </div>

              <button @click="searchTutors" class="w-full py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <span class="flex items-center justify-center space-x-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                  <span>Caută tutori</span>
                </span>
              </button>
            </div>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <div class="bg-white/60 backdrop-blur-xl rounded-2xl p-6 border border-gray-200/50">
              <div class="text-3xl font-bold text-blue-600 mb-2">500+</div>
              <div class="text-gray-600">Tutori verificați</div>
            </div>
            <div class="bg-white/60 backdrop-blur-xl rounded-2xl p-6 border border-gray-200/50">
              <div class="text-3xl font-bold text-purple-600 mb-2">10k+</div>
              <div class="text-gray-600">Lecții realizate</div>
            </div>
            <div class="bg-white/60 backdrop-blur-xl rounded-2xl p-6 border border-gray-200/50">
              <div class="text-3xl font-bold text-pink-600 mb-2">4.9/5</div>
              <div class="text-gray-600">Rating mediu</div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

export default {
  name: 'HomeView',
  setup() {
    const router = useRouter()
    const subjects = ref([])
    const locations = ref({})
    const searchForm = ref({
      subject: '',
      location: '',
      lessonType: ''
    })

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

    const searchTutors = () => {
      const query = {}
      if (searchForm.value.subject) query.subject = searchForm.value.subject
      if (searchForm.value.location) query.location = searchForm.value.location
      if (searchForm.value.lessonType) query.lesson_type = searchForm.value.lessonType

      // Navigate to tutors page with search parameters
      router.push({ name: 'tutors', query })
    }

    onMounted(() => {
      loadSubjects()
      loadLocations()
    })

    return {
      subjects,
      locations,
      searchForm,
      searchTutors
    }
  }
}
</script>
