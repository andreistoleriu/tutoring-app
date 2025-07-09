<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center space-x-4 mb-4">
          <router-link
            to="/dashboard/student"
            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
          </router-link>
          <h1 class="text-3xl font-bold text-gray-900">Rezervările mele</h1>
        </div>
        <p class="text-gray-600">Gestionează toate rezervările tale</p>
      </div>

      <!-- Filters -->
      <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Status Filter -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
              Status
            </label>
            <select
              id="status"
              v-model="filters.status"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Toate</option>
              <option value="pending">În așteptare</option>
              <option value="confirmed">Confirmate</option>
              <option value="completed">Finalizate</option>
              <option value="cancelled">Anulate</option>
            </select>
          </div>

          <!-- Date From -->
          <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">
              De la data
            </label>
            <input
              id="date_from"
              v-model="filters.date_from"
              @change="applyFilters"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Date To -->
          <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">
              Până la data
            </label>
            <input
              id="date_to"
              v-model="filters.date_to"
              @change="applyFilters"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Clear Filters -->
          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="w-full px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Resetează
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6">
        <div class="flex items-center">
          <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <h3 class="text-red-800 font-semibold">Eroare la încărcarea rezervărilor</h3>
            <p class="text-red-600">{{ error }}</p>
          </div>
        </div>
        <button
          @click="loadBookings"
          class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
        >
          Încearcă din nou
        </button>
      </div>

      <!-- Bookings List -->
      <div v-else class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50">
        <!-- Empty State -->
        <div v-if="!bookings?.data?.length" class="text-center py-16">
          <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">Nu ai rezervări</h3>
          <p class="text-gray-600 mb-6">
            {{ hasActiveFilters ? 'Nu s-au găsit rezervări cu aceste filtre.' : 'Nu ai făcut încă nicio rezervare.' }}
          </p>
          <router-link
            v-if="!hasActiveFilters"
            to="/tutors"
            class="inline-block px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200"
          >
            Caută un tutor
          </router-link>
        </div>

        <!-- Bookings Grid -->
        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="booking in bookings.data"
            :key="booking.id"
            class="p-6 hover:bg-gray-50/50 transition-colors"
          >
            <div class="flex items-start justify-between">
              <div class="flex items-start space-x-4 flex-1">
                <!-- Tutor Avatar -->
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-xl">
                  {{ getInitials(booking.tutor.first_name + ' ' + booking.tutor.last_name) }}
                </div>

                <div class="flex-1">
                  <!-- Subject & Tutor -->
                  <h3 class="text-xl font-semibold text-gray-900 mb-1">
                    {{ booking.subject.name }}
                  </h3>
                  <p class="text-gray-600 mb-3">
                    cu {{ booking.tutor.first_name }} {{ booking.tutor.last_name }}
                  </p>

                  <!-- Booking Details -->
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                    <div class="flex items-center text-gray-600">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                      </svg>
                      {{ formatDate(booking.scheduled_at) }}
                    </div>

                    <div class="flex items-center text-gray-600">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      {{ formatTime(booking.scheduled_at) }}
                    </div>

                    <div class="flex items-center text-gray-600">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                      </svg>
                      {{ booking.price }} RON
                    </div>

                    <div class="flex items-center text-gray-600">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                      </svg>
                      {{ getLessonTypeLabel(booking.lesson_type) }}
                    </div>
                  </div>

                  <!-- Student Notes -->
                  <div v-if="booking.student_notes" class="mt-3 p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-800">
                      <strong>Notele tale:</strong> {{ booking.student_notes }}
                    </p>
                  </div>

                  <!-- Tutor Notes -->
                  <div v-if="booking.tutor_notes" class="mt-3 p-3 bg-green-50 rounded-lg">
                    <p class="text-sm text-green-800">
                      <strong>Notele tutorului:</strong> {{ booking.tutor_notes }}
                    </p>
                  </div>

                  <!-- Review Display -->
                  <div v-if="booking.review" class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center mb-2">
                      <span class="text-sm font-medium text-gray-700 mr-2">Review-ul tău:</span>
                      <div class="flex items-center">
                        <svg
                          v-for="star in 5"
                          :key="star"
                          :class="star <= booking.review.rating ? 'text-yellow-500' : 'text-gray-300'"
                          class="w-4 h-4"
                          fill="currentColor"
                          viewBox="0 0 20 20"
                        >
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                      </div>
                    </div>
                    <p class="text-sm text-gray-700">{{ booking.review.comment }}</p>

                    <!-- Tutor Reply -->
                    <div v-if="booking.review.tutor_reply" class="mt-3 pl-4 border-l-2 border-blue-200">
                      <p class="text-sm font-medium text-gray-700 mb-1">Răspunsul tutorului:</p>
                      <p class="text-sm text-gray-600">{{ booking.review.tutor_reply }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Side: Status & Actions -->
              <div class="flex flex-col items-end space-y-3 ml-6">
                <!-- Status Badge -->
                <span
                  :class="{
                    'bg-yellow-100 text-yellow-800': booking.status === 'pending',
                    'bg-green-100 text-green-800': booking.status === 'confirmed',
                    'bg-blue-100 text-blue-800': booking.status === 'completed',
                    'bg-red-100 text-red-800': booking.status === 'cancelled'
                  }"
                  class="px-3 py-1 rounded-full text-sm font-medium"
                >
                  {{ getStatusLabel(booking.status) }}
                </span>

                <!-- Payment Status -->
                <span
                  :class="{
                    'bg-green-100 text-green-800': booking.payment_status === 'paid',
                    'bg-yellow-100 text-yellow-800': booking.payment_status === 'pending',
                    'bg-red-100 text-red-800': booking.payment_status === 'failed'
                  }"
                  class="px-3 py-1 rounded-full text-xs font-medium"
                >
                  {{ getPaymentStatusLabel(booking.payment_status) }}
                </span>

                <!-- Actions -->
                <div class="flex flex-col space-y-2">
                  <!-- Review Button -->
                  <button
                    v-if="booking.can_review"
                    @click="openReviewModal(booking)"
                    class="px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors"
                  >
                    Lasă review
                  </button>

                  <!-- Cancel Button -->
                  <button
                    v-if="booking.can_cancel"
                    @click="cancelBooking(booking.id)"
                    class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors"
                  >
                    Anulează
                  </button>

                  <!-- Contact Tutor -->
                  <button
                    v-if="booking.status === 'confirmed'"
                    @click="contactTutor(booking.tutor)"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                  >
                    Contactează
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="bookings?.meta" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Afișează {{ bookings.meta.from || 0 }} - {{ bookings.meta.to || 0 }}
              din {{ bookings.meta.total }} rezervări
            </div>

            <div class="flex items-center space-x-2">
              <!-- Previous Page -->
              <button
                v-if="bookings.meta.current_page > 1"
                @click="changePage(bookings.meta.current_page - 1)"
                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Anterior
              </button>

              <!-- Page Numbers -->
              <button
                v-for="page in getPageNumbers()"
                :key="page"
                @click="changePage(page)"
                :class="{
                  'bg-blue-600 text-white': page === bookings.meta.current_page,
                  'bg-white text-gray-700 hover:bg-gray-50': page !== bookings.meta.current_page
                }"
                class="px-3 py-2 text-sm font-medium border border-gray-300 rounded-lg"
              >
                {{ page }}
              </button>

              <!-- Next Page -->
              <button
                v-if="bookings.meta.current_page < bookings.meta.last_page"
                @click="changePage(bookings.meta.current_page + 1)"
                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Următorul
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Review Modal -->
    <div v-if="showReviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4">
          Lasă un review pentru {{ selectedBooking?.subject?.name }}
        </h3>

        <form @submit.prevent="submitReview">
          <!-- Rating -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Evaluare *
            </label>
            <div class="flex items-center space-x-1">
              <button
                v-for="star in 5"
                :key="star"
                type="button"
                @click="reviewForm.rating = star"
                :class="star <= reviewForm.rating ? 'text-yellow-500' : 'text-gray-300'"
                class="w-8 h-8 hover:text-yellow-400 transition-colors"
              >
                <svg fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Comment -->
          <div class="mb-6">
            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
              Comentariu
            </label>
            <textarea
              id="comment"
              v-model="reviewForm.comment"
              rows="4"
              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Scrie câteva cuvinte despre experiența ta..."
            ></textarea>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="closeReviewModal"
              class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
            >
              Anulează
            </button>
            <button
              type="submit"
              :disabled="!reviewForm.rating || submittingReview"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ submittingReview ? 'Se trimite...' : 'Trimite review' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStudentStore } from '@/stores/student'

// Composables
const route = useRoute()
const router = useRouter()
const studentStore = useStudentStore()

// Reactive data
const loading = ref(false)
const error = ref(null)
const bookings = ref(null)
const showReviewModal = ref(false)
const selectedBooking = ref(null)
const submittingReview = ref(false)

// Filters
const filters = reactive({
  status: route.query.status || '',
  date_from: route.query.date_from || '',
  date_to: route.query.date_to || '',
  page: parseInt(route.query.page) || 1
})

// Review form
const reviewForm = reactive({
  rating: 0,
  comment: ''
})

// Computed
const hasActiveFilters = computed(() => {
  return filters.status || filters.date_from || filters.date_to
})

// Methods
const loadBookings = async () => {
  loading.value = true
  error.value = null

  try {
    const filterParams = { ...filters }
    // Remove empty filters
    Object.keys(filterParams).forEach(key => {
      if (!filterParams[key]) {
        delete filterParams[key]
      }
    })

    const response = await studentStore.getBookings(filterParams)
    bookings.value = response.bookings
  } catch (err) {
    console.error('Error loading bookings:', err)
    error.value = err.message || 'Eroare la încărcarea rezervărilor'
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  filters.page = 1 // Reset to first page when applying filters

  // Update URL query params
  const query = { ...filters }
  Object.keys(query).forEach(key => {
    if (!query[key]) {
      delete query[key]
    }
  })

  router.push({ query })
  loadBookings()
}

const clearFilters = () => {
  filters.status = ''
  filters.date_from = ''
  filters.date_to = ''
  filters.page = 1

  router.push({ query: {} })
  loadBookings()
}

const changePage = (page) => {
  filters.page = page
  const query = { ...filters }
  router.push({ query })
  loadBookings()
}

const getPageNumbers = () => {
  if (!bookings.value?.meta) return []

  const current = bookings.value.meta.current_page
  const last = bookings.value.meta.last_page
  const pages = []

  // Show max 5 pages
  let start = Math.max(1, current - 2)
  let end = Math.min(last, start + 4)

  if (end - start < 4) {
    start = Math.max(1, end - 4)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
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

const getPaymentStatusLabel = (status) => {
  const labels = {
    paid: 'Plătită',
    pending: 'În așteptare',
    failed: 'Eșuată'
  }
  return labels[status] || status
}

const getLessonTypeLabel = (type) => {
  const labels = {
    online: 'Online',
    in_person: 'Față în față'
  }
  return labels[type] || type
}

const cancelBooking = async (bookingId) => {
  if (!confirm('Ești sigur că vrei să anulezi această rezervare?')) {
    return
  }

  try {
    await studentStore.cancelBooking(bookingId, 'Anulată de student')

    // Reload bookings to reflect changes
    await loadBookings()

    alert('Rezervarea a fost anulată cu succes.')
  } catch (error) {
    console.error('Error cancelling booking:', error)
    alert('A apărut o eroare la anularea rezervării.')
  }
}

const openReviewModal = (booking) => {
  selectedBooking.value = booking
  reviewForm.rating = 0
  reviewForm.comment = ''
  showReviewModal.value = true
}

const closeReviewModal = () => {
  showReviewModal.value = false
  selectedBooking.value = null
  reviewForm.rating = 0
  reviewForm.comment = ''
}

const submitReview = async () => {
  if (!reviewForm.rating) return

  submittingReview.value = true

  try {
    await studentStore.submitReview(selectedBooking.value.id, {
      rating: reviewForm.rating,
      comment: reviewForm.comment
    })

    // Reload bookings to show the new review
    await loadBookings()

    closeReviewModal()
    alert('Review-ul a fost trimis cu succes!')
  } catch (error) {
    console.error('Error submitting review:', error)
    alert('A apărut o eroare la trimiterea review-ului.')
  } finally {
    submittingReview.value = false
  }
}

const contactTutor = (tutor) => {
  // This would open a contact modal or redirect to messaging
  alert(`Funcționalitatea de contact va fi implementată curând pentru ${tutor.first_name} ${tutor.last_name}.`)
}

// Lifecycle
onMounted(() => {
  loadBookings()
})
</script>
