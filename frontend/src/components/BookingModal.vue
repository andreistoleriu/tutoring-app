<template>
  <div v-if="isOpen"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-end sm:items-center justify-center z-50 p-0 sm:p-4"
    @click="close">
    <!-- Mobile: Bottom Sheet, Desktop: Center Modal -->
    <div
      class="bg-white w-full h-[90vh] sm:h-auto sm:max-h-[85vh] sm:max-w-lg sm:rounded-2xl rounded-t-3xl sm:rounded-b-2xl flex flex-col"
      @click.stop>
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200 flex-shrink-0">
        <div>
          <h2 class="text-xl font-bold text-gray-900">Rezervă lecție</h2>
          <p class="text-sm text-gray-600 mt-1">cu {{ tutor.user.first_name }} {{ tutor.user.last_name }}</p>
        </div>
        <button @click="close" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
          <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-y-auto p-6 space-y-6">
        <!-- ADDED: Step Progress Indicator -->
        <div class="mb-6">
          <div class="flex items-center space-x-2 text-xs">
            <div :class="[
              'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold',
              bookingData.subject_id ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600'
            ]">1</div>
            <span :class="bookingData.subject_id ? 'text-green-600 font-medium' : 'text-gray-500'">Materia</span>

            <div class="w-4 h-0.5 bg-gray-300"></div>

            <div :class="[
              'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold',
              bookingData.lesson_type ? 'bg-green-500 text-white' : bookingData.subject_id ? 'bg-orange-400 text-white animate-pulse' : 'bg-gray-300 text-gray-600'
            ]">2</div>
            <span :class="bookingData.lesson_type ? 'text-green-600 font-medium' : bookingData.subject_id ? 'text-orange-600 font-medium' : 'text-gray-500'">Tipul</span>

            <div class="w-4 h-0.5 bg-gray-300"></div>

            <div :class="[
              'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold',
              selectedDate ? 'bg-green-500 text-white' : bookingData.lesson_type ? 'bg-orange-400 text-white animate-pulse' : 'bg-gray-300 text-gray-600'
            ]">3</div>
            <span :class="selectedDate ? 'text-green-600 font-medium' : bookingData.lesson_type ? 'text-orange-600 font-medium' : 'text-gray-500'">Data</span>

            <div class="w-4 h-0.5 bg-gray-300"></div>

            <div :class="[
              'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold',
              bookingData.scheduled_at ? 'bg-green-500 text-white' : selectedDate ? 'bg-orange-400 text-white animate-pulse' : 'bg-gray-300 text-gray-600'
            ]">4</div>
            <span :class="bookingData.scheduled_at ? 'text-green-600 font-medium' : selectedDate ? 'text-orange-600 font-medium' : 'text-gray-500'">Ora</span>
          </div>
        </div>

        <!-- Step 1: Subject Selection -->
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-gray-900">Materia *</label>
          <div class="grid grid-cols-1 gap-2">
            <button v-for="subject in tutor.subjects" :key="subject.id" @click="bookingData.subject_id = subject.id"
              :class="[
                'p-4 text-left rounded-xl border-2 transition-all',
                bookingData.subject_id === subject.id
                  ? 'border-blue-500 bg-blue-50 text-blue-900'
                  : 'border-gray-200 hover:border-gray-300'
              ]">
              <div class="font-medium">{{ subject.name }}</div>
              <div v-if="subject.pivot && subject.pivot.experience_description" class="text-sm text-gray-600 mt-1">
                {{ subject.pivot.experience_description }}
              </div>
            </button>
          </div>
        </div>

        <!-- Step 2: Lesson Type - ENHANCED -->
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-gray-900">
            Tipul lecției *
            <span v-if="!bookingData.lesson_type && bookingData.subject_id" class="text-orange-500 text-xs animate-pulse">
              (Selectează pentru a continua)
            </span>
          </label>

          <div :class="[
            'grid grid-cols-1 sm:grid-cols-2 gap-3',
            !bookingData.lesson_type && bookingData.subject_id ? 'ring-2 ring-orange-200 ring-opacity-50 rounded-xl p-2' : ''
          ]">
            <button v-if="tutor.offers_online" @click="selectLessonType('online')" :class="[
              'p-4 text-center rounded-xl border-2 transition-all',
              bookingData.lesson_type === 'online'
                ? 'border-blue-500 bg-blue-50 text-blue-900'
                : !bookingData.lesson_type && bookingData.subject_id
                  ? 'border-orange-300 bg-orange-50 hover:border-orange-400 hover:bg-orange-100'
                  : 'border-gray-200 hover:border-gray-300'
            ]">
              <div class="text-2xl mb-2">💻</div>
              <div class="font-medium">Online</div>
              <div class="text-sm text-gray-600">Video call</div>
            </button>
            <button v-if="tutor.offers_in_person" @click="selectLessonType('in_person')" :class="[
              'p-4 text-center rounded-xl border-2 transition-all',
              bookingData.lesson_type === 'in_person'
                ? 'border-blue-500 bg-blue-50 text-blue-900'
                : !bookingData.lesson_type && bookingData.subject_id
                  ? 'border-orange-300 bg-orange-50 hover:border-orange-400 hover:bg-orange-100'
                  : 'border-gray-200 hover:border-gray-300'
            ]">
              <div class="text-2xl mb-2">🏠</div>
              <div class="font-medium">Față în față</div>
              <div class="text-sm text-gray-600">{{ tutor.location && tutor.location.city ? tutor.location.city :
                'Local' }}</div>
            </button>
          </div>

          <!-- Enhanced status indicator -->
          <div class="text-xs text-gray-500 bg-gray-100 p-2 rounded">
            Tip lecție selectat:
            <span :class="bookingData.lesson_type ? 'text-green-600 font-medium' : 'text-red-500'">
              {{ bookingData.lesson_type === 'online' ? 'Online (Video call)' :
                 bookingData.lesson_type === 'in_person' ? 'Față în față' :
                 'Niciuna (selectează mai sus)' }}
            </span>
          </div>
        </div>

        <!-- ADDED: Step 3: Duration Selection -->
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-gray-900">Durata lecției *</label>
          <div class="grid grid-cols-3 gap-3">
            <button
              v-for="duration in durationOptions"
              :key="duration.value"
              @click="bookingData.duration_minutes = duration.value"
              :class="[
                'p-3 text-center rounded-xl border-2 transition-all',
                bookingData.duration_minutes === duration.value
                  ? 'border-blue-500 bg-blue-50 text-blue-900'
                  : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <div class="font-medium">{{ duration.label }}</div>
              <div class="text-xs text-gray-600">{{ calculatePrice(duration.value) }} RON</div>
            </button>
          </div>
        </div>

        <!-- Step 4: Date Selection - ENHANCED -->
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-gray-900">
            Selectează data *
            <span v-if="!selectedDate && bookingData.lesson_type" class="text-orange-500 text-xs animate-pulse">
              (Alege o zi)
            </span>
          </label>

          <!-- Show message if lesson type not selected -->
          <div v-if="!bookingData.lesson_type" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
            <div class="flex items-center space-x-2">
              <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
              <p class="text-sm text-yellow-800">Selectează primul tipul lecției pentru a vedea datele disponibile.</p>
            </div>
          </div>

          <!-- Calendar - only show if lesson type selected -->
          <div v-else>
            <!-- FIXED: Better Romanian calendar headers -->
            <div class="grid grid-cols-7 gap-1 text-center text-xs font-medium text-gray-500 mb-2">
              <div>Lu</div>  <!-- Luni -->
              <div>Ma</div>  <!-- Marți -->
              <div>Mi</div>  <!-- Miercuri -->
              <div>Jo</div>  <!-- Joi -->
              <div>Vi</div>  <!-- Vineri -->
              <div>Sâ</div>  <!-- Sâmbătă -->
              <div>Du</div>  <!-- Duminică -->
            </div>

            <!-- WORKING: Use your original availableDates but show them in proper calendar format -->
            <div class="grid grid-cols-7 gap-1">
              <!-- Empty spaces for proper week alignment -->
              <div
                v-for="emptyDay in getEmptyDaysAtStart()"
                :key="`empty-${emptyDay}`"
                class="aspect-square"
              ></div>

              <!-- Your original working date buttons -->
              <button
                v-for="date in availableDates.slice(0, 28 - getEmptyDaysAtStart())"
                :key="date.dateString"
                @click="selectDate(date)"
                :disabled="!date.hasSlots"
                :class="[
                  'aspect-square rounded-lg text-sm font-medium transition-all',
                  selectedDate === date.dateString
                    ? 'bg-blue-600 text-white'
                    : date.hasSlots
                      ? 'bg-white border border-gray-200 text-gray-900 hover:border-blue-300'
                      : 'bg-gray-50 text-gray-400 cursor-not-allowed'
                ]"
              >
                {{ date.day }}
              </button>
            </div>

            <!-- Debug info -->
            <div class="text-xs bg-gray-100 p-2 rounded">
              <div><strong>Calendar Debug:</strong></div>
              <div>Empty days at start: {{ getEmptyDaysAtStart() }}</div>
              <div>First date: {{ availableDates[0]?.dateString }} ({{ availableDates[0]?.dayName }})</div>
              <div>Available dates count: {{ availableDates.length }}</div>
              <div>Tutor availability loaded: {{ Object.keys(tutorAvailability).length > 0 ? 'Yes' : 'No' }}</div>
            </div>
          </div>
        </div>

        <!-- Step 5: Time Selection - ENHANCED -->
        <div v-if="selectedDate" class="space-y-3">
          <label class="block text-sm font-semibold text-gray-900">
            Selectează ora *
            <span v-if="!bookingData.scheduled_at" class="text-orange-500 text-xs animate-pulse">
              (Alege o oră)
            </span>
          </label>

          <!-- Show available time slots if they exist -->
          <div v-if="availableTimeSlots.length > 0" class="grid grid-cols-3 sm:grid-cols-4 gap-2">
            <button v-for="slot in availableTimeSlots" :key="slot.time"
              @click="bookingData.scheduled_at = slot.datetime" :class="[
                'p-3 text-center rounded-lg border-2 transition-all',
                bookingData.scheduled_at === slot.datetime
                  ? 'border-blue-500 bg-blue-50 text-blue-900'
                  : 'border-gray-200 hover:border-gray-300'
              ]">
              <div class="font-medium">{{ slot.time }}</div>
              <div class="text-xs text-gray-600">{{ slot.type }}</div>
            </button>
          </div>

          <!-- Show message if no time slots available -->
          <div v-else class="space-y-3">
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
              <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                  </path>
                </svg>
                <div>
                  <h4 class="text-sm font-medium text-yellow-800 mb-1">Nu sunt ore disponibile</h4>
                  <p class="text-sm text-yellow-700">
                    Tutorul nu are ore disponibile în această zi pentru tipul de lecție selectat.
                    Te rugăm să alegi altă zi sau alt tip de lecție.
                  </p>

                  <!-- Debug info -->
                  <div class="mt-3 text-xs bg-white p-2 rounded border">
                    <div><strong>Debug info:</strong></div>
                    <div>Data selectată: {{ selectedDate }}</div>
                    <div>Ziua săptămânii: {{availableDates.find(d => d.dateString === selectedDate) &&
                      availableDates.find(d => d.dateString === selectedDate).dayName }}</div>
                    <div>Tip lecție: {{ bookingData.lesson_type || 'Nu este selectat' }}</div>
                    <div>Disponibilitate încărcată: {{ Object.keys(tutorAvailability).length > 0 ? 'Da' : 'Nu' }}</div>
                    <div>Zile cu disponibilitate: {{ Object.keys(tutorAvailability).join(', ') || 'Niciuna' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 6: Payment Method -->
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-gray-900">Metoda de plată *</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button @click="bookingData.payment_method = 'card'" :class="[
              'p-4 text-center rounded-xl border-2 transition-all',
              bookingData.payment_method === 'card'
                ? 'border-blue-500 bg-blue-50 text-blue-900'
                : 'border-gray-200 hover:border-gray-300'
            ]">
              <div class="text-2xl mb-2">💳</div>
              <div class="font-medium">Card</div>
              <div class="text-sm text-gray-600">Plată online</div>
            </button>
            <button v-if="bookingData.lesson_type === 'in_person'" @click="bookingData.payment_method = 'cash'" :class="[
              'p-4 text-center rounded-xl border-2 transition-all',
              bookingData.payment_method === 'cash'
                ? 'border-blue-500 bg-blue-50 text-blue-900'
                : 'border-gray-200 hover:border-gray-300'
            ]">
              <div class="text-2xl mb-2">💵</div>
              <div class="font-medium">Cash</div>
              <div class="text-sm text-gray-600">La lecție</div>
            </button>
          </div>
        </div>

        <!-- Step 7: Notes -->
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-gray-900">Note pentru tutor (opțional)</label>
          <textarea v-model="bookingData.student_notes" rows="3"
            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
            placeholder="Spune-i tutorului despre obiectivele tale sau întrebări specifice..."
            maxlength="500"></textarea>
          <div class="text-xs text-gray-500 text-right">
            {{ (bookingData.student_notes && bookingData.student_notes.length) || 0 }}/500
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="border-t border-gray-200 p-6 flex-shrink-0 space-y-4">
        <!-- Summary -->
        <div class="bg-gray-50 rounded-xl p-4 space-y-2">
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Preț lecție:</span>
            <span class="font-semibold">{{ totalPrice }} RON</span>
          </div>
          <div v-if="selectedDate && bookingData.scheduled_at" class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Data & ora:</span>
            <span class="font-medium">{{ formatSelectedDateTime }}</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
          <button @click="submitBooking" :disabled="!isFormValid || submitting" :class="[
            'w-full py-4 rounded-xl font-semibold transition-all',
            isFormValid && !submitting
              ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700'
              : 'bg-gray-300 text-gray-500 cursor-not-allowed'
          ]">
            {{ submitting ? 'Se procesează...' : 'Confirmă rezervarea' }}
          </button>
          <button @click="close"
            class="w-full py-3 border-2 border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            Anulează
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useStudentStore } from '@/stores/student'
import api from '@/services/api'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  tutor: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['close', 'success'])

// Stores
const authStore = useAuthStore()
const studentStore = useStudentStore()

// Reactive data
const submitting = ref(false)
const loadingAvailability = ref(false)
const tutorAvailability = ref({})
const selectedDate = ref('')

// Duration options
const durationOptions = [
  { value: 30, label: '30 min' },
  { value: 60, label: '1 oră' },
  { value: 90, label: '1.5 ore' },
  { value: 120, label: '2 ore' }
]

// Booking form data
const bookingData = ref({
  subject_id: null,
  lesson_type: '',
  duration_minutes: 60,
  scheduled_at: '',
  payment_method: 'card',
  student_notes: ''
})

// ENHANCED: Better lesson type selection with debugging
const selectLessonType = (type) => {
  console.log('🎯 Selecting lesson type:', type)
  bookingData.value.lesson_type = type
  console.log('✅ Lesson type set to:', bookingData.value.lesson_type)

  // Reset time selection when lesson type changes
  bookingData.value.scheduled_at = ''

  // Reset payment method if needed
  if (type === 'online' && bookingData.value.payment_method === 'cash') {
    bookingData.value.payment_method = 'card'
    console.log('🔄 Changed payment method to card for online lesson')
  }

  // Force refresh of time slots if date is already selected
  if (selectedDate.value) {
    console.log('🔄 Refreshing time slots for selected date:', selectedDate.value)
    nextTick(() => {
      console.log('📅 Available time slots:', availableTimeSlots.value.length)
    })
  }
}

// Computed properties
const isFormValid = computed(() => {
  const valid = bookingData.value.subject_id &&
    bookingData.value.lesson_type &&
    bookingData.value.duration_minutes &&
    bookingData.value.scheduled_at &&
    bookingData.value.payment_method

  return valid
})

const totalPrice = computed(() => {
  return calculatePrice(bookingData.value.duration_minutes)
})

const availableDates = computed(() => {
  return calendarDates.value.filter(date => date.isValid)
})


const availableTimeSlots = computed(() => {
  // Basic validation
  if (!selectedDate.value || !bookingData.value.lesson_type) {
    console.log('❌ No date or lesson type selected')
    return []
  }

  // Debug: Check what we have
  console.log('🔍 Debug time slots generation:')
  console.log('- Selected date:', selectedDate.value)
  console.log('- Lesson type:', bookingData.value.lesson_type)
  console.log('- Tutor availability keys:', Object.keys(tutorAvailability.value))

  // Get the selected date object
  const selectedDateObj = availableDates.value.find(d => d.dateString === selectedDate.value)
  if (!selectedDateObj) {
    console.log('❌ Date object not found')
    return []
  }

  console.log('📅 Selected date object:', selectedDateObj)

  // Try different day name formats
  const possibleDayNames = [
    selectedDateObj.dayName,
    selectedDateObj.dayName.charAt(0).toUpperCase() + selectedDateObj.dayName.slice(1),
    selectedDateObj.date.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase(),
    selectedDateObj.date.getDay(),
    ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'][selectedDateObj.date.getDay()]
  ]

  console.log('🔄 Trying day name formats:', possibleDayNames)

  let dayAvailability = []
  for (const dayName of possibleDayNames) {
    if (tutorAvailability.value[dayName] && tutorAvailability.value[dayName].length > 0) {
      dayAvailability = tutorAvailability.value[dayName]
      console.log('✅ Found availability with key:', dayName, dayAvailability)
      break
    }
  }

  if (dayAvailability.length === 0) {
    console.log('❌ No availability found for any day name format')
    console.log('📊 Available data:', tutorAvailability.value)
    return []
  }

  // FIXED: Generate time slots only once, not per availability record
  const slots = []
  const addedSlots = new Set() // Track unique slots to prevent duplicates

  // Check if ANY availability record matches the lesson type
  const hasMatchingAvailability = dayAvailability.some(availability =>
    availability.lesson_type === 'both' || availability.lesson_type === bookingData.value.lesson_type
  )

  if (!hasMatchingAvailability) {
    console.log('❌ No availability matches lesson type:', bookingData.value.lesson_type)
    return []
  }

  // FIXED: Generate slots only once for the day, not per availability record
  const baseSlots = [
    { time: '09:00', datetime: `${selectedDate.value}T09:00:00` },
    { time: '10:00', datetime: `${selectedDate.value}T10:00:00` },
    { time: '11:00', datetime: `${selectedDate.value}T11:00:00` },
    { time: '14:00', datetime: `${selectedDate.value}T14:00:00` },
    { time: '15:00', datetime: `${selectedDate.value}T15:00:00` },
    { time: '16:00', datetime: `${selectedDate.value}T16:00:00` }
  ]

  // Add each slot only once with appropriate type
  baseSlots.forEach(baseSlot => {
    const slotKey = baseSlot.datetime

    if (!addedSlots.has(slotKey)) {
      // Find the best availability type for this slot
      const matchingAvailability = dayAvailability.find(availability =>
        availability.lesson_type === 'both' || availability.lesson_type === bookingData.value.lesson_type
      )

      const slot = {
        ...baseSlot,
        type: matchingAvailability?.lesson_type === 'both' ? 'Ambele' :
              matchingAvailability?.lesson_type === 'online' ? 'Online' : 'Față în față'
      }

      slots.push(slot)
      addedSlots.add(slotKey)
    }
  })

  console.log('✅ Generated unique slots:', slots.length)
  console.log('🎯 Final available slots:', slots)
  return slots
})


const formatSelectedDateTime = computed(() => {
  if (!bookingData.value.scheduled_at) return ''

  const date = new Date(bookingData.value.scheduled_at)
  return date.toLocaleDateString('ro-RO', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
})

// Methods
const calculatePrice = (minutes) => {
  if (!minutes || !props.tutor || !props.tutor.hourly_rate) return 0
  return Math.round((props.tutor.hourly_rate * minutes / 60) * 100) / 100
}

const ensureLessonTypeSelected = () => {
  if (!bookingData.value.lesson_type) {
    console.log('⚠️ No lesson type selected yet')
    // Auto-select if only one option available
    if (props.tutor.offers_online && !props.tutor.offers_in_person) {
      bookingData.value.lesson_type = 'online'
      console.log('✅ Auto-selected online lesson type')
    } else if (!props.tutor.offers_online && props.tutor.offers_in_person) {
      bookingData.value.lesson_type = 'in_person'
      console.log('✅ Auto-selected in-person lesson type')
    }
  }
}

const getEmptyDaysAtStart = () => {
  if (!availableDates.value.length) return 0

  const firstDate = availableDates.value[0].date
  const dayOfWeek = firstDate.getDay() // 0=Sunday, 1=Monday, etc.

  // Convert to Monday=0 system
  return dayOfWeek === 0 ? 6 : dayOfWeek - 1
}

const selectDate = (date) => {
  console.log('Date selected:', date)
  selectedDate.value = date.dateString
  bookingData.value.scheduled_at = '' // Reset time selection

  // Log available time slots after date selection
  console.log('Available time slots for', date.dateString, ':', availableTimeSlots.value)
}

const loadTutorAvailability = async () => {
  loadingAvailability.value = true

  try {
    console.log('Loading availability for tutor:', props.tutor.user_id || props.tutor.id)

    // Use the correct API endpoint
    const response = await api.get(`tutors/${props.tutor.user_id || props.tutor.id}/availability`)
    console.log('Availability response:', response.data)

    tutorAvailability.value = response.data.availability || {}

    // Check if tutor has any availability set up
    if (Object.keys(tutorAvailability.value).length === 0) {
      console.warn('Tutor has no availability set up')
    }

  } catch (error) {
    console.error('Error loading tutor availability:', error)

    // Don't set fallback availability - show error instead
    tutorAvailability.value = {}
  } finally {
    loadingAvailability.value = false
  }
}

const calendarDates = computed(() => {
  const dates = []
  const today = new Date()

  // Get the first date to show (tomorrow)
  const startDate = new Date(today)
  startDate.setDate(today.getDate() + 1)

  // Find Monday of the week containing startDate
  const firstMonday = new Date(startDate)
  const dayOfWeek = startDate.getDay() // 0=Sunday, 1=Monday, ..., 6=Saturday
  const daysFromMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1 // Convert to Monday=0 system
  firstMonday.setDate(startDate.getDate() - daysFromMonday)

  // Generate 4 weeks worth of dates (28 days) starting from the Monday
  for (let i = 0; i < 28; i++) {
    const date = new Date(firstMonday)
    date.setDate(firstMonday.getDate() + i)

    const dateString = date.toISOString().split('T')[0]
    const dayName = date.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase()

    // Only show dates from tomorrow onwards
    const isValid = date > today

    // Check if tutor has availability on this day (only for valid dates)
    const hasSlots = isValid &&
                    tutorAvailability.value[dayName] &&
                    tutorAvailability.value[dayName].length > 0

    dates.push({
      key: `${dateString}-${i}`, // Unique key for v-for
      date,
      dateString,
      day: date.getDate(),
      dayName,
      hasSlots,
      isValid,
      // Debug info
      actualDayName: date.toLocaleDateString('ro-RO', { weekday: 'long' }),
      jsDay: date.getDay()
    })
  }

  return dates
})

const submitBooking = async () => {
  if (!isFormValid.value || submitting.value) return

  submitting.value = true

  try {
    const bookingPayload = {
      tutor_id: props.tutor.user_id || props.tutor.user.id,
      subject_id: bookingData.value.subject_id,
      scheduled_at: bookingData.value.scheduled_at,
      duration_minutes: bookingData.value.duration_minutes,
      lesson_type: bookingData.value.lesson_type,
      payment_method: bookingData.value.payment_method,
      student_notes: bookingData.value.student_notes
    }

    console.log('Submitting booking:', bookingPayload)

    const response = await studentStore.createBooking(bookingPayload)

    emit('success', response.booking)
    close()

  } catch (error) {
    console.error('Error creating booking:', error)

    let errorMessage = 'A apărut o eroare la crearea rezervării.'

    if (error.response && error.response.data && error.response.data.message) {
      errorMessage = error.response.data.message
    } else if (error.message) {
      errorMessage = error.message
    }

    // Show user-friendly error messages
    if (errorMessage.includes('time slot is not available')) {
      alert('Acest interval orar nu este disponibil pentru tipul de lecție selectat. Te rugăm să alegi alt interval sau alt tip de lecție.')
    } else if (errorMessage.includes('already booked')) {
      alert('Acest interval orar este deja rezervat. Te rugăm să alegi alt interval.')
    } else if (errorMessage.includes('does not teach')) {
      alert('Acest tutor nu predă materia selectată.')
    } else if (errorMessage.includes('Cash payment')) {
      alert('Plata cash este disponibilă doar pentru lecțiile față în față.')
    } else {
      alert(errorMessage)
    }
  } finally {
    submitting.value = false
  }
}

const close = () => {
  emit('close')

  // Reset form
  bookingData.value = {
    subject_id: null,
    lesson_type: '',
    duration_minutes: 60,
    scheduled_at: '',
    payment_method: 'card',
    student_notes: ''
  }
  selectedDate.value = ''
}

// CLEANED UP: Single watch for modal opening with auto-selection
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    console.log('🔧 Modal opened, initializing...')

    // Load availability
    loadTutorAvailability()

    // Set default values ONLY if not already set
    if (!bookingData.value.subject_id && props.tutor.subjects && props.tutor.subjects.length === 1) {
      bookingData.value.subject_id = props.tutor.subjects[0].id
      console.log('✅ Auto-selected single subject:', props.tutor.subjects[0].name)
    }

    // FIXED: Only auto-select lesson type if ONLY ONE option is available
    setTimeout(() => {
      if (!bookingData.value.lesson_type) {
        if (props.tutor.offers_online && !props.tutor.offers_in_person) {
          bookingData.value.lesson_type = 'online'
          console.log('✅ Auto-selected online lesson type (only option)')
        } else if (!props.tutor.offers_online && props.tutor.offers_in_person) {
          bookingData.value.lesson_type = 'in_person'
          console.log('✅ Auto-selected in-person lesson type (only option)')
        } else {
          // FIXED: Don't auto-select when both options are available - let user choose
          console.log('ℹ️ Both lesson types available - user must choose')
        }
      }
    }, 100)
  }
})

// Watch for lesson type changes to update available payment methods
watch(() => bookingData.value.lesson_type, (newType) => {
  console.log('Lesson type changed to:', newType)
  if (newType === 'online' && bookingData.value.payment_method === 'cash') {
    bookingData.value.payment_method = 'card'
  }
  // Reset time selection when lesson type changes
  bookingData.value.scheduled_at = ''
})

// Watch for duration changes to refresh available time slots
watch(() => bookingData.value.duration_minutes, () => {
  console.log('Duration changed, resetting time selection')
  bookingData.value.scheduled_at = '' // Reset time selection
})

watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    console.log('🔧 Modal opened, initializing...')

    // FIXED: Reset form to empty state
    bookingData.value = {
      subject_id: null,
      lesson_type: '', // Start empty
      duration_minutes: 60, // Keep default duration
      scheduled_at: '',
      payment_method: 'card',
      student_notes: ''
    }
    selectedDate.value = ''

    // Load availability
    loadTutorAvailability()

    // Auto-select subject only if there's only one
    if (props.tutor.subjects && props.tutor.subjects.length === 1) {
      bookingData.value.subject_id = props.tutor.subjects[0].id
      console.log('✅ Auto-selected single subject:', props.tutor.subjects[0].name)
    }

    // FIXED: Only auto-select lesson type if ONLY ONE option available
    setTimeout(() => {
      if (!bookingData.value.lesson_type) {
        if (props.tutor.offers_online && !props.tutor.offers_in_person) {
          bookingData.value.lesson_type = 'online'
          console.log('✅ Auto-selected online (only option)')
        } else if (!props.tutor.offers_online && props.tutor.offers_in_person) {
          bookingData.value.lesson_type = 'in_person'
          console.log('✅ Auto-selected in-person (only option)')
        }
        // If both options available, don't auto-select anything
      }
    }, 100)
  }
})
</script>
