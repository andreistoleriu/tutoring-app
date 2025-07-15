const confirmCancelBooking = (booking) => {
  const reason = prompt('Motivul anulării (opțional):')

  if (reason !== null) { // User didn't cancel the prompt
    cancelBooking(booking.id, reason)
  }
}

const cancelBooking = async (bookingId, reason = '') => {
  loading.value = true

  try {
    console.log('❌ Cancelling booking:', bookingId)
    await studentStore.cancelBooking(bookingId, reason)

    // Reload bookings to reflect the change
    loadBookings()

    alert('Rezervarea a fost anulată cu succes!')
  } catch (err) {
    console.error('❌ Failed to cancel booking:', err)
    alert('Eroare la anularea rezervării: ' + (err.message || 'Eroare necunoscută'))
  } finally {
    loading.value = false
  }
}<!-- frontend/src/views/student/StudentBookingsView.vue -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <h1 class="text-2xl font-bold text-gray-900">Rezervările Mele</h1>

          <!-- Mobile filter toggle -->
          <button
            @click="showFilters = !showFilters"
            class="md:hidden bg-blue-600 text-white px-4 py-2 rounded-lg"
          >
            Filtre
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6" :class="{ 'hidden md:block': !showFilters }">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="filters.status"
              @change="applyFilters"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
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
            <label class="block text-sm font-medium text-gray-700 mb-2">De la data</label>
            <input
              type="date"
              v-model="filters.date_from"
              @change="applyFilters"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>

          <!-- Date To -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Până la data</label>
            <input
              type="date"
              v-model="filters.date_to"
              @change="applyFilters"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>

          <!-- Clear Filters -->
          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
            >
              Resetează
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Se încarcă rezervările...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6">
        <div class="flex">
          <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div>
            <h3 class="text-red-800 font-medium">Eroare la încărcarea rezervărilor</h3>
            <p class="text-red-700 mt-1">{{ error }}</p>
            <button
              @click="loadBookings"
              class="mt-3 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors"
            >
              Încearcă din nou
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!hasBookings" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h.5a2 2 0 012 2v10a2 2 0 01-2 2h-13a2 2 0 01-2-2V9a2 2 0 012-2H8z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Nu ai rezervări</h3>
        <p class="mt-2 text-gray-600">
          {{ filters.status ? 'Nu există rezervări cu acest status.' : 'Încă nu ai făcut nicio rezervare.' }}
        </p>
        <router-link
          to="/tutors"
          class="mt-6 inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors"
        >
          Găsește un tutor
        </router-link>
      </div>

      <!-- Bookings List -->
      <div v-else class="space-y-6">
        <!-- Booking Card -->
        <div
          v-for="booking in bookingsList"
          :key="booking.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
        >
          <div class="p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
              <div class="flex items-center space-x-4 mb-4 sm:mb-0">
                <!-- Tutor Avatar -->
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                    {{ getInitials(booking.tutor) }}
                  </div>
                </div>

                <!-- Booking Info -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">
                    {{ booking.tutor.first_name }} {{ booking.tutor.last_name }}
                  </h3>
                  <p class="text-gray-600">{{ booking.subject.name }}</p>
                </div>
              </div>

              <!-- Status Badge -->
              <div class="flex items-center space-x-2">
                <span :class="getStatusBadgeClass(booking.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                  {{ getStatusText(booking.status) }}
                </span>
              </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
              <div>
                <p class="text-sm text-gray-500">Data și ora</p>
                <p class="font-medium">{{ formatDateTime(booking.scheduled_at) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Durată</p>
                <p class="font-medium">{{ booking.duration_minutes }} minute</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Tip lecție</p>
                <p class="font-medium">{{ getLessonTypeText(booking.lesson_type) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Preț</p>
                <p class="font-medium">{{ booking.price }} RON</p>
              </div>
            </div>

            <!-- Notes -->
            <div v-if="booking.student_notes" class="mb-6">
              <p class="text-sm text-gray-500 mb-1">Notele tale</p>
              <p class="text-gray-700 bg-gray-50 p-3 rounded-lg">{{ booking.student_notes }}</p>
            </div>

            <!-- Review Section -->
            <div v-if="booking.status === 'completed'" class="mb-6">
              <!-- Existing Review -->
              <div v-if="booking.has_review && booking.review" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="font-medium text-blue-900">Review-ul tău</h4>
                  <button
                    @click="editReview(booking)"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                  >
                    Editează
                  </button>
                </div>

                <!-- Rating Stars -->
                <div class="flex items-center mb-2">
                  <div class="flex space-x-1">
                    <svg
                      v-for="star in 5"
                      :key="star"
                      class="w-5 h-5"
                      :class="star <= booking.review.rating ? 'text-yellow-400' : 'text-gray-300'"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                  </div>
                  <span class="ml-2 text-sm text-gray-600">{{ booking.review.rating }}/5</span>
                </div>

                <!-- Comment -->
                <p v-if="booking.review.comment" class="text-gray-700">{{ booking.review.comment }}</p>
                <p v-else class="text-gray-500 italic">Fără comentariu</p>
              </div>

              <!-- Review Call-to-Action -->
              <div v-else-if="booking.can_review" class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="font-medium text-green-900">Lasă un review</h4>
                    <p class="text-green-700 text-sm">Împărtășește experiența ta cu acest tutor</p>
                  </div>
                  <button
                    @click="openReviewModal(booking)"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
                  >
                    Scrie review
                  </button>
                </div>
              </div>
            </div>

            <!-- Action Buttons for Pending/Confirmed Bookings -->
            <div v-if="['pending', 'confirmed'].includes(booking.status)" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="font-medium text-yellow-900">Acțiuni disponibile</h4>
                  <p class="text-yellow-700 text-sm">Poți modifica sau anula această rezervare</p>
                </div>
                <div class="flex gap-2">
                  <button
                    @click="editBooking(booking)"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
                  >
                    Modifică
                  </button>
                  <button
                    @click="confirmCancelBooking(booking)"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors"
                  >
                    Anulează
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="bookingsMeta.last_page > 1" class="mt-8">
        <div class="bg-white px-4 py-3 flex items-center justify-between border border-gray-200 rounded-lg sm:px-6">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="changePage(bookingsMeta.current_page - 1)"
              :disabled="bookingsMeta.current_page <= 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Anterior
            </button>
            <button
              @click="changePage(bookingsMeta.current_page + 1)"
              :disabled="bookingsMeta.current_page >= bookingsMeta.last_page"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Următor
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Afișând
                <span class="font-medium">{{ bookingsMeta.from || 0 }}</span>
                până la
                <span class="font-medium">{{ bookingsMeta.to || 0 }}</span>
                din
                <span class="font-medium">{{ bookingsMeta.total || 0 }}</span>
                rezultate
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  @click="changePage(bookingsMeta.current_page - 1)"
                  :disabled="bookingsMeta.current_page <= 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  ←
                </button>

                <button
                  v-for="page in getVisiblePages()"
                  :key="page"
                  @click="changePage(page)"
                  :class="{
                    'bg-blue-50 border-blue-500 text-blue-600': page === bookingsMeta.current_page,
                    'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': page !== bookingsMeta.current_page
                  }"
                  class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                >
                  {{ page }}
                </button>

                <button
                  @click="changePage(bookingsMeta.current_page + 1)"
                  :disabled="bookingsMeta.current_page >= bookingsMeta.last_page"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  →
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Review Modal -->
  <ReviewModal
    :is-open="showReviewModal"
    :booking="selectedBookingForReview"
    :existing-review="selectedBookingForReview?.review"
    @close="closeReviewModal"
    @success="handleReviewSuccess"
  />

  <!-- Edit Booking Modal -->
  <EditBookingModal
    :is-open="showEditModal"
    :booking="selectedBookingForEdit"
    @close="closeEditModal"
    @success="handleEditSuccess"
  />
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStudentStore } from '@/stores/student'
import ReviewModal from '@/components/ReviewModal.vue'
import EditBookingModal from '@/components/EditBookingModal.vue'

// Composables
const route = useRoute()
const router = useRouter()
const studentStore = useStudentStore()

// State
const loading = ref(false)
const error = ref(null)
const bookings = ref(null)
const showFilters = ref(false)
const showReviewModal = ref(false)
const showEditModal = ref(false)
const selectedBookingForReview = ref(null)
const selectedBookingForEdit = ref(null)

// Filters
const filters = reactive({
  status: route.query.status || '',
  date_from: route.query.date_from || '',
  date_to: route.query.date_to || '',
  page: parseInt(route.query.page) || 1
})

// Computed properties
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

// Methods
const loadBookings = async () => {
  loading.value = true
  error.value = null

  try {
    console.log('🔄 Loading bookings with filters:', filters)
    const response = await studentStore.getBookings(filters)
    bookings.value = response
    console.log('✅ Bookings loaded successfully:', response)
  } catch (err) {
    console.error('❌ Failed to load bookings:', err)
    error.value = err.message || 'Eroare la încărcarea rezervărilor'
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  filters.page = 1 // Reset to first page
  updateUrlParams()
  loadBookings()
}

const clearFilters = () => {
  filters.status = ''
  filters.date_from = ''
  filters.date_to = ''
  filters.page = 1
  updateUrlParams()
  loadBookings()
}

const changePage = (page) => {
  if (page >= 1 && page <= bookingsMeta.value.last_page) {
    filters.page = page
    updateUrlParams()
    loadBookings()
  }
}

const updateUrlParams = () => {
  const query = {}
  Object.keys(filters).forEach(key => {
    if (filters[key]) {
      query[key] = filters[key]
    }
  })

  router.replace({ query })
}

const getVisiblePages = () => {
  const current = bookingsMeta.value.current_page
  const total = bookingsMeta.value.last_page
  const delta = 2

  const range = []
  const start = Math.max(1, current - delta)
  const end = Math.min(total, current + delta)

  for (let i = start; i <= end; i++) {
    range.push(i)
  }

  return range
}

// Review Methods
const openReviewModal = (booking) => {
  console.log('📝 Opening review modal for booking:', booking.id)
  selectedBookingForReview.value = booking
  showReviewModal.value = true
}

const editReview = (booking) => {
  console.log('✏️ Editing review for booking:', booking.id)
  selectedBookingForReview.value = booking
  showReviewModal.value = true
}

const closeReviewModal = () => {
  showReviewModal.value = false
  selectedBookingForReview.value = null
}

const handleReviewSuccess = (data) => {
  console.log('✅ Review action completed:', data)

  // Reload bookings to get fresh data
  loadBookings()

  // Show success message
  const actionText = data.action === 'created' ? 'adăugat' :
                     data.action === 'updated' ? 'actualizat' : 'șters'

  // You can implement a toast notification here
  alert(`Review-ul a fost ${actionText} cu succes!`)

  closeReviewModal()
}

// Edit Booking Methods
const editBooking = (booking) => {
  console.log('✏️ Opening edit modal for booking:', booking.id)
  selectedBookingForEdit.value = booking
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  selectedBookingForEdit.value = null
}

const handleEditSuccess = (updatedBooking) => {
  console.log('✅ Booking updated successfully:', updatedBooking)

  // Reload bookings to get fresh data
  loadBookings()

  alert('Rezervarea a fost actualizată cu succes!')
  closeEditModal()
}

// Other Actions
const viewBookingDetails = (booking) => {
  // Navigate to booking detail page or open modal
  router.push(`/student/bookings/${booking.id}`)
}

const confirmCancelBooking = (booking) => {
  const reason = prompt('Motivul anulării (opțional):')

  if (reason !== null) { // User didn't cancel the prompt
    cancelBooking(booking.id, reason)
  }
}

const cancelBooking = async (bookingId, reason = '') => {
  loading.value = true

  try {
    console.log('❌ Cancelling booking:', bookingId)
    await studentStore.cancelBooking(bookingId, reason)

    // Reload bookings to reflect the change
    loadBookings()

    alert('Rezervarea a fost anulată cu succes!')
  } catch (err) {
    console.error('❌ Failed to cancel booking:', err)
    alert('Eroare la anularea rezervării: ' + (err.message || 'Eroare necunoscută'))
  } finally {
    loading.value = false
  }
}

// Utility Methods
const getInitials = (tutor) => {
  if (!tutor || !tutor.first_name) return '?'

  const first = tutor.first_name.charAt(0).toUpperCase()
  const last = tutor.last_name ? tutor.last_name.charAt(0).toUpperCase() : ''

  return first + last
}

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
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

const getLessonTypeText = (type) => {
  const texts = {
    online: 'Online',
    in_person: 'Față în față'
  }
  return texts[type] || type
}

const formatDateTime = (dateString) => {
  if (!dateString) return ''

  const date = new Date(dateString)

  // Check if date is valid
  if (isNaN(date.getTime())) return dateString

  return date.toLocaleDateString('ro-RO', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(() => {
  console.log('📱 StudentBookingsView mounted')
  console.log('🔍 Initial filters:', filters)
  loadBookings()
})

// Watch for route changes
watch(() => route.query, (newQuery) => {
  console.log('🔄 Route query changed:', newQuery)

  // Update filters from URL
  filters.status = newQuery.status || ''
  filters.date_from = newQuery.date_from || ''
  filters.date_to = newQuery.date_to || ''
  filters.page = parseInt(newQuery.page) || 1

  // Reload bookings with new filters
  loadBookings()
}, { deep: true })
</script>

<style scoped>
/* Mobile-first responsive design */
@media (max-width: 640px) {
  .grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }

  .sm\:grid-cols-2 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }

  .lg\:grid-cols-4 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .space-y-6 > * + * {
    margin-top: 1rem;
  }

  .gap-3 {
    gap: 0.5rem;
  }
}

@media (max-width: 480px) {
  .px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .py-6 {
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
  }

  .p-6 {
    padding: 1rem;
  }

  .text-2xl {
    font-size: 1.5rem;
  }

  .text-lg {
    font-size: 1.125rem;
  }
}

/* Custom animations */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Button hover effects */
button:not(:disabled):hover {
  transform: translateY(-1px);
  transition: all 0.2s ease-in-out;
}

button:not(:disabled):active {
  transform: translateY(0);
}

/* Card hover effects */
.bg-white:hover {
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  transition: box-shadow 0.3s ease-in-out;
}

/* Focus styles for accessibility */
button:focus,
select:focus,
input:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

/* Loading animation */
.loading-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

/* Status badge animations */
.status-badge {
  transition: all 0.2s ease-in-out;
}

.status-badge:hover {
  transform: scale(1.05);
}

/* Responsive text scaling */
@media (max-width: 640px) {
  .responsive-text {
    font-size: 0.875rem;
    line-height: 1.25rem;
  }
}
</style>
