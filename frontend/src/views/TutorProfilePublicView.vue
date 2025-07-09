<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="w-24 h-24 mx-auto mb-4 text-gray-400">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Tutorul nu a fost găsit</h3>
        <p class="text-gray-600 mb-6">{{ error }}</p>
        <router-link
          to="/tutors"
          class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors"
        >
          Înapoi la lista tutorilor
        </router-link>
      </div>
    </div>

    <!-- Tutor Profile Content -->
    <div v-else-if="tutor" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 mb-8">
        <div class="p-8">
          <div class="flex flex-col lg:flex-row lg:items-start lg:space-x-8">
            <!-- Avatar and Basic Info -->
            <div class="flex-shrink-0 text-center lg:text-left mb-6 lg:mb-0">
              <div class="relative inline-block">
                <img
                  v-if="tutor.profile_image_url"
                  :src="tutor.profile_image_url"
                  :alt="`${tutor.user.first_name} ${tutor.user.last_name}`"
                  class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-xl"
                >
                <div
                  v-else
                  class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white font-bold text-4xl shadow-xl"
                >
                  {{ tutor.user.first_name[0] }}{{ tutor.user.last_name[0] }}
                </div>

                <!-- Online Status -->
                <div v-if="tutor.offers_online" class="absolute -bottom-2 -right-2 w-8 h-8 bg-green-500 border-4 border-white rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                  </svg>
                </div>

                <!-- Verified Badge -->
                <div v-if="tutor.is_verified" class="absolute -top-2 -right-2 w-8 h-8 bg-blue-500 border-4 border-white rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Main Info -->
            <div class="flex-1">
              <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-4">
                <div>
                  <div class="flex items-center space-x-3 mb-2">
                    <h1 class="text-3xl font-bold text-gray-900">
                      {{ tutor.user.first_name }} {{ tutor.user.last_name }}
                    </h1>
                    <div v-if="tutor.is_featured" class="px-3 py-1 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-sm font-medium rounded-full">
                      ⭐ Recomandat
                    </div>
                  </div>

                  <!-- Location -->
                  <div class="flex items-center space-x-2 text-gray-600 mb-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="font-medium">{{ tutor.location.city }}, {{ tutor.location.county }}</span>
                  </div>

                  <!-- Rating -->
                  <div v-if="tutor.total_reviews > 0" class="flex items-center space-x-2 mb-4">
                    <div class="flex items-center">
                      <div class="flex space-x-1">
                        <svg v-for="star in 5" :key="star" class="w-5 h-5" :class="star <= Math.round(tutor.rating) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                      </div>
                      <span class="text-lg font-semibold text-gray-900 ml-2">{{ tutor.rating.toFixed(1) }}</span>
                      <span class="text-gray-600 ml-1">({{ tutor.total_reviews }} {{ tutor.total_reviews === 1 ? 'recenzie' : 'recenzii' }})</span>
                    </div>
                  </div>
                </div>

                <!-- Price -->
                <div class="text-right">
                  <div class="text-3xl font-bold text-gray-900">{{ tutor.hourly_rate }} <span class="text-lg font-normal text-gray-600">RON/oră</span></div>
                  <div class="text-sm text-gray-500 mt-1">{{ tutor.total_lessons }} lecții predate</div>
                </div>
              </div>

              <!-- Lesson Types -->
              <div class="flex flex-wrap gap-2 mb-4">
                <div v-if="tutor.offers_online" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                  💻 Online
                </div>
                <div v-if="tutor.offers_in_person" class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                  🏠 La domiciliu
                </div>
              </div>

              <!-- Subjects -->
              <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Materii predate</h3>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="subject in tutor.subjects"
                    :key="subject.id"
                    class="px-3 py-1 bg-gradient-to-r from-blue-100 to-purple-100 text-blue-800 rounded-lg text-sm font-medium"
                  >
                    {{ subject.name }}
                  </span>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex flex-col sm:flex-row gap-4">
                <!-- For authenticated students -->
                <template v-if="authStore.isAuthenticated && authStore.isStudent">
                  <button
                    @click="bookLesson"
                    class="flex-1 sm:flex-none px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                  >
                    📅 Rezervă lecție
                  </button>
                  <button
                    @click="contactTutor"
                    class="flex-1 sm:flex-none px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors"
                  >
                    💬 Contactează
                  </button>
                </template>

                <!-- For authenticated tutors -->
                <template v-else-if="authStore.isAuthenticated && authStore.isTutor">
                  <div class="flex-1 sm:flex-none px-8 py-3 bg-gray-100 text-gray-600 font-semibold rounded-xl text-center">
                    🎓 Profil de tutor
                  </div>
                </template>

                <!-- For unauthenticated users -->
                <template v-else>
                  <button
                    @click="handleLoginClick"
                    class="flex-1 sm:flex-none px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                  >
                    🔐 Conectează-te pentru a rezerva
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Sections -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Bio Section -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Despre mine</h2>
            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ tutor.bio || 'Tutor experimentat cu pasiune pentru predare.' }}</p>
          </div>

          <!-- Experience Section -->
          <div v-if="tutor.experience" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Experiență</h2>
            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ tutor.experience }}</p>
          </div>

          <!-- Education Section -->
          <div v-if="tutor.education" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Educație</h2>
            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ tutor.education }}</p>
          </div>

          <!-- Reviews Section -->
          <div v-if="tutor.reviews && tutor.reviews.length > 0" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Recenzii ({{ tutor.reviews.length }})</h2>
            <div class="space-y-6">
              <div
                v-for="review in tutor.reviews.slice(0, showAllReviews ? tutor.reviews.length : 3)"
                :key="review.id"
                class="border-b border-gray-100 pb-6 last:border-b-0 last:pb-0"
              >
                <div class="flex items-start space-x-4">
                  <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white font-bold">
                    {{ review.student.first_name[0] }}
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                      <div>
                        <h4 class="font-semibold text-gray-900">{{ review.student.first_name }} {{ review.student.last_name[0] }}.</h4>
                        <div class="flex items-center space-x-2">
                          <div class="flex">
                            <svg v-for="star in 5" :key="star" class="w-4 h-4" :class="star <= review.rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                          </div>
                          <span class="text-sm text-gray-500">{{ formatDate(review.created_at) }}</span>
                        </div>
                      </div>
                    </div>
                    <p class="text-gray-700 mb-3">{{ review.comment }}</p>

                    <!-- Tutor Reply -->
                    <div v-if="review.tutor_reply" class="mt-4 p-4 bg-gray-50 rounded-lg">
                      <div class="flex items-center space-x-2 mb-2">
                        <span class="text-sm font-medium text-gray-900">Răspuns de la tutor:</span>
                        <span class="text-xs text-gray-500">{{ formatDate(review.tutor_replied_at) }}</span>
                      </div>
                      <p class="text-gray-700 text-sm">{{ review.tutor_reply }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Show More Reviews Button -->
            <div v-if="tutor.reviews.length > 3" class="mt-6 text-center">
              <button
                @click="showAllReviews = !showAllReviews"
                class="px-6 py-2 text-blue-600 hover:text-blue-800 font-medium"
              >
                {{ showAllReviews ? 'Arată mai puține' : `Arată toate recenziile (${tutor.reviews.length})` }}
              </button>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Quick Stats -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistici</h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Lecții predate</span>
                <span class="font-semibold text-gray-900">{{ tutor.total_lessons }}</span>
              </div>
              <div v-if="tutor.total_reviews > 0" class="flex items-center justify-between">
                <span class="text-gray-600">Rating mediu</span>
                <span class="font-semibold text-gray-900">{{ tutor.rating.toFixed(1) }}/5</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Timp de răspuns</span>
                <span class="font-semibold text-gray-900">~2 ore</span>
              </div>
            </div>
          </div>

          <!-- Contact Card -->
          <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Contactează tutorul</h3>
            <div class="space-y-3">
              <!-- For authenticated students -->
              <template v-if="authStore.isAuthenticated && authStore.isStudent">
                <button
                  @click="bookLesson"
                  class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200"
                >
                  📅 Rezervă lecție
                </button>
                <button
                  @click="contactTutor"
                  class="w-full px-4 py-3 border border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors"
                >
                  💬 Trimite mesaj
                </button>
              </template>

              <!-- For authenticated tutors -->
              <template v-else-if="authStore.isAuthenticated && authStore.isTutor">
                <div class="w-full px-4 py-3 bg-gray-100 text-gray-600 font-semibold rounded-xl text-center">
                  🎓 Profilul unui coleg tutor
                </div>
              </template>

              <!-- For unauthenticated users -->
              <template v-else>
                <button
                  @click="handleLoginClick"
                  class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200"
                >
                  🔐 Conectează-te
                </button>
                <div class="text-center">
                  <p class="text-sm text-gray-600 mb-2">Pentru a rezerva lecții sau contacta tutorul</p>
                  <button
                    @click="handleRegisterClick"
                    class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                  >
                    Sau creează un cont nou
                  </button>
                </div>
              </template>
            </div>
          </div>

          <!-- Safety Notice -->
          <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6">
            <div class="flex items-start space-x-3">
              <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
              <div>
                <h4 class="text-sm font-medium text-yellow-800 mb-1">Sfat de siguranță</h4>
                <p class="text-sm text-yellow-700">Întâlnirile fizice ar trebui să aibă loc în locuri publice sau cunoscute. Păstrează comunicarea prin platformă.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

// Reactive data
const tutor = ref(null)
const loading = ref(true)
const error = ref(null)
const showAllReviews = ref(false)

// Try to get the modal functions from the root component
const openLoginModal = () => {
  // Emit a custom event to the parent App component
  window.dispatchEvent(new CustomEvent('open-login-modal'))
}

// Methods
const loadTutor = async () => {
  try {
    loading.value = true
    error.value = null

    const tutorId = route.params.id
    console.log('Loading tutor with ID:', tutorId)

    const response = await api.get(`tutors/${tutorId}`)
    tutor.value = response.data.tutor

    console.log('Tutor loaded:', tutor.value)
  } catch (err) {
    console.error('Error loading tutor:', err)
    if (err.response?.status === 404) {
      error.value = 'Tutorul nu a fost găsit.'
    } else {
      error.value = 'A apărut o eroare la încărcarea profilului tutorului.'
    }
  } finally {
    loading.value = false
  }
}

const bookLesson = () => {
  if (!authStore.isAuthenticated) {
    openLoginModal()
    return
  }

  if (!authStore.isStudent) {
    alert('Doar studenții pot rezerva lecții.')
    return
  }

  // TODO: Implement booking functionality
  alert('Funcționalitatea de rezervare va fi implementată în curând!')
}

const contactTutor = () => {
  if (!authStore.isAuthenticated) {
    openLoginModal()
    return
  }

  if (!authStore.isStudent) {
    alert('Doar studenții pot contacta tutorii.')
    return
  }

  // TODO: Implement contact functionality
  alert('Funcționalitatea de contact va fi implementată în curând!')
}

const handleLoginClick = () => {
  openLoginModal()
}

const handleRegisterClick = () => {
  // Emit a custom event to open register modal
  window.dispatchEvent(new CustomEvent('open-register-modal'))
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ro-RO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  loadTutor()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
