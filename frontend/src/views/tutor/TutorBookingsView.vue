<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Rezervări</h1>
            <p class="text-gray-600 mt-1">Gestionează toate rezervările tale</p>
          </div>
          <router-link
            :to="{ name: 'tutor-dashboard' }"
            class="bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors py-2 px-4"
          >
            ← Înapoi la dashboard
          </router-link>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              id="statusFilter"
              v-model="filters.status"
              @change="applyFilters"
              class="w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent py-3 px-4"
            >
              <option value="">Toate statusurile</option>
              <option value="pending">În așteptare</option>
              <option value="confirmed">Confirmate</option>
              <option value="completed">Finalizate</option>
              <option value="cancelled">Anulate</option>
            </select>
          </div>
          <div>
            <label for="typeFilter" class="block text-sm font-medium text-gray-700 mb-2">Tip lecție</label>
            <select
              id="typeFilter"
              v-model="filters.type"
              @change="applyFilters"
              class="w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent py-3 px-4"
            >
              <option value="">Toate tipurile</option>
              <option value="online">Online</option>
              <option value="in-person">Față în față</option>
            </select>
          </div>
          <div>
            <label for="dateFrom" class="block text-sm font-medium text-gray-700 mb-2">De la data</label>
            <input
              id="dateFrom"
              v-model="filters.dateFrom"
              @change="applyFilters"
              type="date"
              class="w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent py-3 px-4"
            >
          </div>
          <div>
            <label for="dateTo" class="block text-sm font-medium text-gray-700 mb-2">Până la data</label>
            <input
              id="dateTo"
              v-model="filters.dateTo"
              @change="applyFilters"
              type="date"
              class="w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent py-3 px-4"
            >
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-xl">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">În așteptare</p>
              <p class="text-2xl font-bold text-gray-900">{{ getBookingsByStatus('pending').length }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-xl">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Confirmate</p>
              <p class="text-2xl font-bold text-gray-900">{{ getBookingsByStatus('confirmed').length }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-xl">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Finalizate</p>
              <p class="text-2xl font-bold text-gray-900">{{ getBookingsByStatus('completed').length }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-xl">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Anulate</p>
              <p class="text-2xl font-bold text-gray-900">{{ getBookingsByStatus('cancelled').length }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Bookings List -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">
              Lista rezervărilor ({{ filteredBookings.length }})
            </h2>
            <div class="flex items-center space-x-2">
              <button
                @click="refreshBookings"
                :disabled="loading"
                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div class="p-6">
          <!-- Loading State -->
          <div v-if="loading" class="space-y-4">
            <div v-for="i in 5" :key="i" class="animate-pulse flex items-center space-x-4 p-4 border border-gray-200 rounded-xl">
              <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
              <div class="flex-1">
                <div class="h-4 bg-gray-200 rounded mb-2"></div>
                <div class="h-3 bg-gray-200 rounded w-2/3"></div>
              </div>
            </div>
          </div>

          <!-- No Bookings -->
          <div v-else-if="filteredBookings.length === 0" class="text-center py-12">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nicio rezervare găsită</h3>
            <p class="text-gray-600">Încearcă să schimbi filtrele pentru a vedea rezervările.</p>
          </div>

          <!-- Bookings List -->
          <div v-else class="space-y-4">
            <div
              v-for="booking in filteredBookings"
              :key="booking.id"
              class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <!-- Student Avatar -->
                  <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white font-semibold">
                    {{ getInitials(booking.student.full_name) }}
                  </div>

                  <!-- Booking Info -->
                  <div>
                    <div class="flex items-center space-x-3 mb-1">
                      <h3 class="font-semibold text-gray-900">{{ booking.subject.name }}</h3>
                      <span
                        class="text-xs font-medium rounded-full py-1 px-2"
                        :class="getStatusClasses(booking.status)"
                      >
                        {{ getStatusText(booking.status) }}
                      </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">cu {{ booking.student.full_name }}</p>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                      <span class="flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ formatDate(booking.scheduled_at) }}</span>
                      </span>
                      <span class="flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ formatTime(booking.scheduled_at) }}</span>
                      </span>
                      <span
                        class="text-xs font-medium rounded-full py-1 px-2"
                        :class="booking.lesson_type === 'online' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                      >
                        {{ booking.lesson_type === 'online' ? 'Online' : 'Față în față' }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-3">
                  <span class="text-lg font-bold text-gray-900">{{ booking.price }} RON</span>

                  <!-- Pending Actions -->
                  <div v-if="booking.status === 'pending'" class="flex space-x-2">
                    <button
                      @click="confirmBooking(booking.id)"
                      :disabled="processing"
                      class="bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 py-2 px-4"
                    >
                      Acceptă
                    </button>
                    <button
                      @click="rejectBooking(booking.id)"
                      :disabled="processing"
                      class="bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 py-2 px-4"
                    >
                      Respinge
                    </button>
                  </div>

                  <!-- Confirmed Actions -->
                  <div v-else-if="booking.status === 'confirmed'" class="flex space-x-2">
                    <button
                      @click="startLesson(booking.id)"
                      :disabled="!isLessonStartable(booking.scheduled_at)"
                      class="bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 py-2 px-4"
                    >
                      {{ isLessonStartable(booking.scheduled_at) ? 'Începe lecția' : 'Prea devreme' }}
                    </button>
                  </div>

                  <!-- Completed Actions -->
                  <div v-else-if="booking.status === 'completed'" class="flex space-x-2">
                    <button
                      v-if="booking.payment_method === 'cash' && booking.payment_status === 'pending'"
                      @click="confirmCashPayment(booking.id)"
                      class="bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors py-2 px-4"
                    >
                      Confirmă plata
                    </button>
                    <span v-else class="bg-green-100 text-green-800 text-sm font-medium rounded-lg py-2 px-4">
                      Finalizată
                    </span>
                  </div>
                </div>
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
import { useRouter } from 'vue-router'

const router = useRouter()

// Reactive data
const loading = ref(false)
const processing = ref(false)

const filters = ref({
  status: '',
  type: '',
  dateFrom: '',
  dateTo: ''
})

const bookings = ref([
  {
    id: 1,
    student: { full_name: 'Maria Popescu', email: 'maria@email.com', phone: '+40123456789' },
    subject: { name: 'Matematică' },
    scheduled_at: '2025-07-08T10:00:00',
    lesson_type: 'online',
    status: 'pending',
    price: 75,
    payment_method: 'card',
    payment_status: 'pending'
  },
  {
    id: 2,
    student: { full_name: 'Ion Ionescu', email: 'ion@email.com', phone: '+40123456790' },
    subject: { name: 'Fizică' },
    scheduled_at: '2025-07-08T14:30:00',
    lesson_type: 'in-person',
    status: 'confirmed',
    price: 80,
    payment_method: 'cash',
    payment_status: 'pending'
  },
  {
    id: 3,
    student: { full_name: 'Ana Georgescu', email: 'ana@email.com', phone: '+40123456791' },
    subject: { name: 'Chimie' },
    scheduled_at: '2025-07-09T09:00:00',
    lesson_type: 'online',
    status: 'confirmed',
    price: 70,
    payment_method: 'card',
    payment_status: 'paid'
  },
  {
    id: 4,
    student: { full_name: 'Cristian Pavel', email: 'cristian@email.com', phone: '+40123456792' },
    subject: { name: 'Matematică' },
    scheduled_at: '2025-07-05T16:00:00',
    lesson_type: 'in-person',
    status: 'completed',
    price: 75,
    payment_method: 'cash',
    payment_status: 'pending'
  }
])

// Computed properties
const filteredBookings = computed(() => {
  let filtered = [...bookings.value]

  if (filters.value.status) {
    filtered = filtered.filter(booking => booking.status === filters.value.status)
  }

  if (filters.value.type) {
    filtered = filtered.filter(booking => booking.lesson_type === filters.value.type)
  }

  if (filters.value.dateFrom) {
    filtered = filtered.filter(booking =>
      new Date(booking.scheduled_at) >= new Date(filters.value.dateFrom)
    )
  }

  if (filters.value.dateTo) {
    filtered = filtered.filter(booking =>
      new Date(booking.scheduled_at) <= new Date(filters.value.dateTo)
    )
  }

  return filtered.sort((a, b) => new Date(b.scheduled_at) - new Date(a.scheduled_at))
})

// Methods
const getBookingsByStatus = (status) => {
  return bookings.value.filter(booking => booking.status === status)
}

const getInitials = (fullName) => {
  if (!fullName) return 'N/A'
  return fullName.split(' ').map(name => name[0]).join('').toUpperCase()
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ro-RO', {
    weekday: 'long',
    month: 'long',
    day: 'numeric'
  })
}

const formatTime = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleTimeString('ro-RO', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusClasses = (status) => {
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

const isLessonStartable = (scheduledAt) => {
  const lessonTime = new Date(scheduledAt)
  const now = new Date()
  const diffInMinutes = (lessonTime - now) / (1000 * 60)
  return diffInMinutes <= 15 && diffInMinutes >= -60
}

const applyFilters = () => {
  console.log('Filters applied:', filters.value)
}

const refreshBookings = async () => {
  loading.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1000))
    console.log('Bookings refreshed')
  } catch (error) {
    console.error('Error refreshing bookings:', error)
  } finally {
    loading.value = false
  }
}

const confirmBooking = async (bookingId) => {
  processing.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1000))

    const booking = bookings.value.find(b => b.id === bookingId)
    if (booking) {
      booking.status = 'confirmed'
    }

    alert('Rezervarea a fost confirmată cu succes!')
  } catch (error) {
    console.error('Error confirming booking:', error)
    alert('A apărut o eroare la confirmarea rezervării.')
  } finally {
    processing.value = false
  }
}

const rejectBooking = async (bookingId) => {
  if (!confirm('Ești sigur că vrei să respingi această rezervare?')) {
    return
  }

  processing.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1000))

    const booking = bookings.value.find(b => b.id === bookingId)
    if (booking) {
      booking.status = 'cancelled'
    }

    alert('Rezervarea a fost respinsă.')
  } catch (error) {
    console.error('Error rejecting booking:', error)
    alert('A apărut o eroare la respingerea rezervării.')
  } finally {
    processing.value = false
  }
}

const startLesson = (bookingId) => {
  alert(`Funcționalitatea pentru lecția ${bookingId} va fi disponibilă în curând!`)
}

const confirmCashPayment = async (bookingId) => {
  if (!confirm('Confirmă că ai primit plata în numerar pentru această lecție.')) {
    return
  }

  try {
    await new Promise(resolve => setTimeout(resolve, 1000))

    const booking = bookings.value.find(b => b.id === bookingId)
    if (booking) {
      booking.payment_status = 'paid'
    }

    alert('Plata în numerar a fost confirmată!')
  } catch (error) {
    console.error('Error confirming cash payment:', error)
    alert('A apărut o eroare la confirmarea plății.')
  }
}

// Lifecycle
onMounted(() => {
  const today = new Date()
  const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000)

  filters.value.dateFrom = thirtyDaysAgo.toISOString().split('T')[0]
  filters.value.dateTo = today.toISOString().split('T')[0]
})
</script>
