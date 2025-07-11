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
              type="date"
              v-model="filters.date_from"
              @change="applyFilters"
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
              type="date"
              v-model="filters.date_to"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Clear Filters -->
          <div class="flex items-end">
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
            >
              Șterge filtrele
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="space-y-6">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-8">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-4 text-gray-600">Se încarcă rezervările...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
          <div class="text-red-800 mb-2">{{ error }}</div>
          <button
            @click="loadBookings"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            Încearcă din nou
          </button>
        </div>

        <!-- Empty State - when no bookings exist -->
        <div v-else-if="!hasBookings && !hasActiveFilters"
             class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
          <div class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6a2 2 0 012 2v10a2 2 0 01-2 2H8a2 2 0 01-2-2V9a2 2 0 012-2z"></path>
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nu ai rezervări încă</h3>
          <p class="text-gray-600 mb-4">Când vei rezerva o lecție, o vei vedea aici.</p>
          <button
            @click="$router.push('/tutors')"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Caută tutori
          </button>
        </div>

        <!-- No Results with Filters -->
        <div v-else-if="!hasBookings && hasActiveFilters"
             class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nu s-au găsit rezervări</h3>
          <p class="text-gray-600 mb-4">Încearcă să modifici filtrele pentru a găsi rezervările tale.</p>
          <button
            @click="clearFilters"
            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors"
          >
            Șterge toate filtrele
          </button>
        </div>

        <!-- Bookings List -->
        <div v-else class="space-y-6">
          <div
            v-for="booking in bookingsList"
            :key="booking.id"
            class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 overflow-hidden hover:shadow-md transition-shadow"
          >
            <div class="p-6">
              <!-- Booking Header -->
              <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-4">
                  <!-- Tutor Avatar -->
                  <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                    <span class="text-white font-semibold text-lg">
                      {{ getInitials(booking.tutor?.first_name, booking.tutor?.last_name) }}
                    </span>
                  </div>
                  <!-- Booking Info -->
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                      {{ booking.tutor?.first_name }} {{ booking.tutor?.last_name }}
                    </h3>
                    <p class="text-gray-600">{{ booking.subject?.name }}</p>
                  </div>
                </div>
                <!-- Status Badge -->
                <span :class="getStatusClass(booking.status)"
                      class="px-3 py-1 rounded-full text-xs font-medium">
                  {{ getStatusText(booking.status) }}
                </span>
              </div>

              <!-- Booking Details -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="text-sm">
                  <span class="text-gray-500">📅 Data</span>
                  <p class="font-medium">{{ formatDate(booking.scheduled_at) }}</p>
                </div>
                <div class="text-sm">
                  <span class="text-gray-500">🕒 Ora</span>
                  <p class="font-medium">{{ formatTime(booking.scheduled_at) }}</p>
                </div>
                <div class="text-sm">
                  <span class="text-gray-500">⏱️ Durata</span>
                  <p class="font-medium">{{ booking.duration_minutes }} min</p>
                </div>
                <div class="text-sm">
                  <span class="text-gray-500">💰 Preț</span>
                  <p class="font-medium">{{ booking.price }} RON</p>
                </div>
              </div>

              <!-- Additional Info -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="text-sm">
                  <span class="text-gray-500">📍 Tip lecție</span>
                  <p class="font-medium">
                    {{ booking.lesson_type === 'online' ? '🌐 Online' : '🏢 Față în față' }}
                  </p>
                </div>
                <div class="text-sm">
                  <span class="text-gray-500">💳 Plată</span>
                  <p class="font-medium">
                    {{ booking.payment_method === 'card' ? '💳 Card' : '💵 Cash' }}
                  </p>
                </div>
              </div>

              <!-- Student Notes -->
              <div v-if="booking.student_notes" class="mb-4">
                <div class="bg-gray-50 rounded-lg p-3">
                  <span class="text-xs text-gray-500 block mb-1">📝 Notele tale:</span>
                  <p class="text-sm text-gray-700">{{ booking.student_notes }}</p>
                </div>
              </div>

              <!-- Tutor Notes -->
              <div v-if="booking.tutor_notes" class="mb-4">
                <div class="bg-blue-50 rounded-lg p-3">
                  <span class="text-xs text-blue-600 block mb-1">👨‍🏫 Notele tutorului:</span>
                  <p class="text-sm text-blue-700">{{ booking.tutor_notes }}</p>
                </div>
              </div>

              <!-- Review Section -->
              <div v-if="booking.review" class="mb-4">
                <div class="bg-green-50 rounded-lg p-3">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-green-600 font-medium">⭐ Review-ul tău</span>
                    <div class="flex items-center space-x-1">
                      <span v-for="star in 5" :key="star" class="text-yellow-400">
                        {{ star <= booking.review.rating ? '★' : '☆' }}
                      </span>
                    </div>
                  </div>
                  <p class="text-sm text-green-700">{{ booking.review.comment }}</p>
                  <div v-if="booking.review.tutor_reply" class="mt-2 pl-4 border-l-2 border-green-200">
                    <span class="text-xs text-green-600 block">Răspunsul tutorului:</span>
                    <p class="text-sm text-green-600">{{ booking.review.tutor_reply }}</p>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="text-xs text-gray-500">
                  Rezervat la: {{ formatDateTime(booking.created_at) }}
                </div>

                <div class="flex items-center space-x-2">
                  <!-- Cancel Booking -->
                  <button
                    v-if="booking.status === 'pending' || booking.status === 'confirmed'"
                    @click="cancelBooking(booking.id)"
                    class="px-4 py-2 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200 transition-colors"
                  >
                    Anulează
                  </button>

                  <!-- Leave Review -->
                  <button
                    v-if="booking.status === 'completed' && !booking.review"
                    @click="openReviewModal(booking)"
                    class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
                  >
                    Lasă review
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
        <div v-if="bookingsMeta.total > 0" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Afișează {{ bookingsMeta.from || 0 }} - {{ bookingsMeta.to || 0 }}
              din {{ bookingsMeta.total }} rezervări
            </div>

            <div class="flex items-center space-x-2">
              <!-- Previous Page -->
              <button
                v-if="bookingsMeta.current_page > 1"
                @click="changePage(bookingsMeta.current_page - 1)"
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
                  'bg-blue-600 text-white': page === bookingsMeta.current_page,
                  'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50': page !== bookingsMeta.current_page
                }"
                class="px-3 py-2 text-sm font-medium rounded-lg"
              >
                {{ page }}
              </button>

              <!-- Next Page -->
              <button
                v-if="bookingsMeta.current_page < bookingsMeta.last_page"
                @click="changePage(bookingsMeta.current_page + 1)"
                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Următorul
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Review Modal -->
  <div v-if="showReviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md">
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
              :class="star <= reviewForm.rating ? 'text-yellow-400' : 'text-gray-300'"
              class="text-2xl hover:text-yellow-400 transition-colors"
            >
              ★
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
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
            placeholder="Scrie-ți impresiile despre lecție..."
          ></textarea>
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-end space-x-3">
          <button
            type="button"
            @click="closeReviewModal"
            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
          >
            Anulează
          </button>
          <button
            type="submit"
            :disabled="!reviewForm.rating || submittingReview"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {{ submittingReview ? 'Se trimite...' : 'Trimite review' }}
          </button>
        </div>
      </form>
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

// Computed properties for easier access
const bookingsList = computed(() => {
  console.log('🔍 Computing bookingsList from:', bookings.value)
  return bookings.value?.bookings || []
})

const bookingsMeta = computed(() => {
  return bookings.value?.meta || {}
})

const hasBookings = computed(() => {
  const count = bookingsList.value.length
  console.log('📊 Has bookings computed:', count > 0, 'count:', count)
  return count > 0
})

const hasActiveFilters = computed(() => {
  return filters.status || filters.date_from || filters.date_to
})

// Methods
const loadBookings = async () => {
  loading.value = true
  error.value = null

  try {
    console.log('🔍 Loading bookings with filters:', filters)

    const filterParams = { ...filters }
    // Remove empty filters
    Object.keys(filterParams).forEach(key => {
      if (!filterParams[key]) {
        delete filterParams[key]
      }
    })

    console.log('🔍 Clean filter params:', filterParams)

    const response = await studentStore.getBookings(filterParams)

    console.log('📋 Complete response:', response)
    console.log('📋 Bookings array:', response.bookings)
    console.log('📋 Bookings count:', response.bookings?.length || 0)
    console.log('📋 Meta data:', response.meta)

    // Set bookings correctly
    bookings.value = response

    // Additional debugging
    console.log('📋 Final bookings.value:', bookings.value)
    console.log('📋 Final bookingsList computed:', bookingsList.value)

  } catch (err) {
    console.error('❌ Error loading bookings:', err)
    error.value = err.message || 'Eroare la încărcarea rezervărilor'
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  filters.page = 1
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
  if (!bookingsMeta.value) return []

  const current = bookingsMeta.value.current_page
  const last = bookingsMeta.value.last_page
  const pages = []

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

// Helper methods
const getInitials = (firstName, lastName) => {
  if (!firstName && !lastName) return 'NA'
  const first = firstName ? firstName.charAt(0).toUpperCase() : ''
  const last = lastName ? lastName.charAt(0).toUpperCase() : ''
  return first + last || first || last
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('ro-RO', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleTimeString('ro-RO', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatDateTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('ro-RO', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    pending: 'În așteptare',
    confirmed: 'Confirmată',
    completed: 'Finalizată',
    cancelled: 'Anulată'
  }
  return texts[status] || status
}

// Action methods
const cancelBooking = async (bookingId) => {
  if (!confirm('Ești sigur că vrei să anulezi această rezervare?')) {
    return
  }

  try {
    await studentStore.cancelBooking(bookingId, 'Anulată de student')
    await loadBookings() // Reload bookings
    alert('Rezervarea a fost anulată cu succes!')
  } catch (error) {
    console.error('Error cancelling booking:', error)
    alert('Eroare la anularea rezervării. Încearcă din nou.')
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
  if (!reviewForm.rating) {
    alert('Te rugăm să alegi o evaluare!')
    return
  }

  submittingReview.value = true

  try {
    await studentStore.submitReview(selectedBooking.value.id, {
      rating: reviewForm.rating,
      comment: reviewForm.comment
    })

    closeReviewModal()
    await loadBookings() // Reload to show the review
    alert('Review-ul a fost trimis cu succes!')
  } catch (error) {
    console.error('Error submitting review:', error)
    alert('Eroare la trimiterea review-ului. Încearcă din nou.')
  } finally {
    submittingReview.value = false
  }
}

const contactTutor = (tutor) => {
  // You can implement a contact modal or redirect to a messaging system
  alert(`Funcționalitate în dezvoltare: Contact ${tutor.first_name} ${tutor.last_name}`)
}

// Lifecycle
onMounted(async () => {
  console.log('🚀 StudentBookingsView mounted')
  await loadBookings()
})
</script>
