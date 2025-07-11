<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Se încarcă profilul tutorului...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="text-red-600 text-5xl mb-4">⚠️</div>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Eroare</h2>
        <p class="text-gray-600 mb-4">{{ error }}</p>
        <button
          @click="loadTutor"
          class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors"
        >
          Încearcă din nou
        </button>
      </div>
    </div>

    <!-- Tutor Profile Content - Original Design -->
    <div v-else-if="tutor" class="pb-20">
      <!-- Main Container -->
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

          <!-- Left Column - Tutor Card -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl p-8 shadow-sm text-center">
              <!-- Avatar with Status -->
              <div class="relative inline-block mb-6">
                <div class="w-32 h-32 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-4xl font-bold mx-auto">
                  {{ tutor.user.first_name[0] }}{{ tutor.user.last_name[0] }}
                </div>
                <!-- Verified Badge -->
                <div v-if="tutor.is_verified" class="absolute -top-2 -right-2 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <!-- Online Status -->
                <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-4 border-white"></div>
              </div>

              <!-- Name and Info -->
              <h1 class="text-2xl font-bold text-gray-900 mb-2">
                {{ tutor.user.first_name }} {{ tutor.user.last_name }}
              </h1>

              <!-- Recommended Badge -->
              <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800 mb-3">
                Recomandat
              </div>

              <!-- Location -->
              <div class="flex items-center justify-center space-x-1 text-gray-600 mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>{{ tutor.location.city }}, {{ tutor.location.county }}</span>
              </div>

              <!-- Rating -->
              <div class="mb-4">
                <!-- Stars and Rating -->
                <div class="flex items-center justify-center space-x-2 mb-2">
                  <div class="flex items-center space-x-1">
                    <span v-for="i in 5" :key="i" :class="i <= tutor.rating ? 'text-yellow-400' : 'text-gray-300'" class="text-lg">
                      ⭐
                    </span>
                  </div>
                  <span class="text-xl font-bold text-gray-900">{{ tutor.rating.toFixed(1) }}</span>
                </div>

                <!-- Stats -->
                <div class="flex items-center justify-center space-x-4 text-sm text-gray-600">
                  <span class="flex items-center">
                    <span class="font-medium text-gray-900">{{ tutor.total_reviews }}</span>
                    <span class="ml-1">{{ tutor.total_reviews === 1 ? 'recenzie' : 'recenzii' }}</span>
                  </span>
                  <span class="text-gray-400">•</span>
                  <span class="flex items-center">
                    <span class="font-medium text-gray-900">{{ tutor.total_lessons }}</span>
                    <span class="ml-1">{{ tutor.total_lessons === 1 ? 'lecție predată' : 'lecții predate' }}</span>
                  </span>
                </div>
              </div>

              <!-- Status Badges -->
              <div class="flex flex-wrap justify-center gap-2 mb-6">
                <span v-if="tutor.offers_online" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                  📱 Online
                </span>
                <span v-if="tutor.is_verified" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">
                  ✓ Verificat
                </span>
              </div>

              <!-- Price -->
              <div class="text-center mb-6">
                <div class="text-4xl font-bold text-gray-900 mb-1">{{ tutor.hourly_rate }} RON</div>
                <div class="text-gray-600">per oră</div>
              </div>

              <!-- Quick Stats -->
              <div class="grid grid-cols-2 gap-4 mb-6 pt-6 border-t border-gray-200">
                <div class="text-center">
                  <div class="text-2xl font-bold text-gray-900">{{ tutor.total_lessons }}</div>
                  <div class="text-xs text-gray-600">Lecții predate</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-gray-900">{{ tutor.rating.toFixed(1) }}</div>
                  <div class="text-xs text-gray-600">Rating mediu</div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="space-y-3">
                <template v-if="authStore.isAuthenticated && authStore.isStudent">
                  <button
                    @click="openBookingModal"
                    class="w-full py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl"
                  >
                    📅 Rezervă lecție
                  </button>
                  <button
                    @click="contactTutor"
                    class="w-full py-2 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
                  >
                    💬 Contactează
                  </button>
                </template>
                <template v-else>
                  <button
                    @click="handleLoginClick"
                    class="w-full py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200"
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
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 mt-6">
              <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                  <h4 class="text-sm font-medium text-yellow-800 mb-1">Sfat de siguranță</h4>
                  <p class="text-sm text-yellow-700">Întâlnirile fizice ar trebui să aibă loc în locuri publice sau cunoscute. Păstrează comunicarea prin platformă.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Details -->
          <div class="lg:col-span-2 space-y-6">

            <!-- Materii predate section -->
            <div class="bg-white rounded-2xl p-6 shadow-sm">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Materii predate</h2>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="subject in tutor.subjects"
                  :key="subject.id"
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
                >
                  {{ subject.name }}
                </span>
              </div>
            </div>

            <!-- Despre mine Section -->
            <div class="bg-white rounded-2xl p-6 shadow-sm">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Despre mine</h2>
              <p class="text-gray-700 leading-relaxed">
                {{ tutor.bio || 'Acest tutor nu și-a completat încă biografia.' }}
              </p>
            </div>

            <!-- Experiență Section -->
            <div v-if="tutor.experience" class="bg-white rounded-2xl p-6 shadow-sm">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Experiență</h2>
              <p class="text-gray-700 leading-relaxed">{{ tutor.experience }}</p>
            </div>

            <!-- Educație Section -->
            <div v-if="tutor.education" class="bg-white rounded-2xl p-6 shadow-sm">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Educație</h2>
              <p class="text-gray-700 leading-relaxed">{{ tutor.education }}</p>
            </div>

            <!-- Statistici Section -->
            <div class="bg-white rounded-2xl p-6 shadow-sm">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Statistici</h2>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-xl">
                  <div class="text-2xl font-bold text-gray-900 mb-1">{{ tutor.total_lessons }}</div>
                  <div class="text-sm text-gray-600">Lecții predate</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-xl">
                  <div class="text-2xl font-bold text-gray-900">{{ tutor.rating.toFixed(1) }}/5</div>
                  <div class="text-sm text-gray-600">Rating mediu</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-xl">
                  <div class="text-2xl font-bold text-gray-900">~2 ore</div>
                  <div class="text-sm text-gray-600">Timp de răspuns</div>
                </div>
              </div>
            </div>



            <!-- Reviews Section -->
            <div v-if="tutor.reviews && tutor.reviews.length > 0" class="bg-white rounded-2xl p-6 shadow-sm">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">
                  Recenzii ({{ tutor.reviews.length }})
                </h2>
                <button
                  v-if="tutor.reviews.length > 3"
                  @click="showAllReviews = !showAllReviews"
                  class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                >
                  {{ showAllReviews ? 'Arată mai puține' : 'Arată toate' }}
                </button>
              </div>

              <div class="space-y-4">
                <div
                  v-for="(review, index) in (showAllReviews ? tutor.reviews : tutor.reviews.slice(0, 1))"
                  :key="review.id"
                  class="border-b border-gray-100 last:border-b-0 pb-4 last:pb-0"
                >
                  <div class="flex items-start space-x-3">
                    <!-- Avatar -->
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                      {{ review.student.first_name[0] }}
                    </div>
                    <div class="flex-1">
                      <div class="flex items-center justify-between mb-2">
                        <div>
                          <p class="font-medium text-gray-900 text-sm">{{ review.student.first_name }} {{ review.student.last_name[0] }}.</p>
                          <div class="flex items-center space-x-1">
                            <span v-for="i in 5" :key="i" :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-300'" class="text-sm">
                              ⭐
                            </span>
                          </div>
                        </div>
                        <span class="text-xs text-gray-500">{{ formatDate(review.created_at) }}</span>
                      </div>
                      <p class="text-gray-700 text-sm leading-relaxed">{{ review.comment }}</p>

                      <!-- Tutor Reply -->
                      <div v-if="review.tutor_reply" class="bg-gray-50 rounded-lg p-3 mt-3">
                        <p class="text-xs font-medium text-gray-900 mb-1">Răspuns de la tutor:</p>
                        <p class="text-xs text-gray-700">{{ review.tutor_reply }}</p>
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

    <!-- Booking Modal -->
    <BookingModal
      :is-open="showBookingModal"
      :tutor="tutor"
      @close="closeBookingModal"
      @success="onBookingSuccess"
    />

    <!-- Success Modal -->
    <div
      v-if="showSuccessModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click="closeSuccessModal"
    >
      <div
        class="bg-white rounded-2xl max-w-md w-full p-6 text-center"
        @click.stop
      >
        <div class="text-6xl mb-4">🎉</div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Rezervare confirmată!</h3>
        <p class="text-gray-600 mb-6">
          Rezervarea ta a fost trimisă către {{ tutor.user.first_name }}.
          Vei primi o notificare când tutorul confirmă sau respinge rezervarea.
        </p>
        <div class="space-y-3">
          <button
            @click="goToBookings"
            class="w-full py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors"
          >
            Vezi rezervările mele
          </button>
          <button
            @click="closeSuccessModal"
            class="w-full py-2 text-gray-600 hover:text-gray-800 transition-colors"
          >
            Închide
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BookingModal from '@/components/BookingModal.vue'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

// Reactive data
const tutor = ref(null)
const loading = ref(true)
const error = ref(null)
const showAllReviews = ref(false)
const showBookingModal = ref(false)
const showSuccessModal = ref(false)
const lastBooking = ref(null)

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

const openBookingModal = () => {
  if (!authStore.isAuthenticated) {
    openLoginModal()
    return
  }

  if (!authStore.isStudent) {
    alert('Doar studenții pot rezerva lecții.')
    return
  }

  showBookingModal.value = true
}

const closeBookingModal = () => {
  showBookingModal.value = false
}

const onBookingSuccess = (booking) => {
  lastBooking.value = booking
  showBookingModal.value = false
  showSuccessModal.value = true
}

const closeSuccessModal = () => {
  showSuccessModal.value = false
  lastBooking.value = null
}

const goToBookings = () => {
  router.push({ name: 'student-bookings' })
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

const openLoginModal = () => {
  // Emit a custom event to the parent App component
  window.dispatchEvent(new CustomEvent('open-login-modal'))
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
