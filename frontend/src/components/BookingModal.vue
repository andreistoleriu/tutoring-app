<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-end sm:items-center justify-center z-50 p-0 sm:p-4"
    @click="close"
  >
    <!-- Mobile: Bottom Sheet, Desktop: Center Modal -->
    <div
      class="bg-white w-full h-[90vh] sm:h-auto sm:max-h-[85vh] sm:max-w-lg sm:rounded-2xl rounded-t-3xl sm:rounded-b-2xl flex flex-col"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200 flex-shrink-0">
        <div>
          <h2 class="text-xl font-bold text-gray-900">Rezervă lecție</h2>
          <p class="text-sm text-gray-600 mt-1">cu {{ tutor.user.first_name }} {{ tutor.user.last_name }}</p>
        </div>
        <button
          @click="close"
          class="p-2 hover:bg-gray-100 rounded-xl transition-colors"
        >
          <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-y-auto p-6 space-y-6">
        <!-- Step 1: Subject Selection -->
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-gray-900">Materia *</label>
          <div class="grid grid-cols-1 gap-2">
            <button
              v-for="subject in tutor.subjects"
              :key="subject.id"
              @click="bookingData.subject_id = subject.id"
              :class="[
                'p-4 text-left rounded-xl border-2 transition-all',
                bookingData.subject_id === subject.id
                  ? 'border-blue-500 bg-blue-50 text-blue-900'
                  : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <div class="font-medium">{{ subject.name }}</div>
              <div v-if="subject.pivot && subject.pivot.experience_description" class="text-sm text-gray-600 mt-1">
                {{ subject.pivot.experience_description }}
              </div>
            </button>
          </div>
        </div>

        <!-- Step 2: Lesson Type -->
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-gray-900">Tipul lecției *</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button
              v-if="tutor.offers_online"
              @click="selectLessonType('online')"
              :class="[
                'p-4 text-center rounded-xl border-2 transition-all',
                bookingData.lesson_type === 'online'
                  ? 'border-blue-500 bg-blue-50 text-blue-900'
                  : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <div class="text-2xl mb-2">💻</div>
              <div class="font-medium">Online</div>
              <div class="text-sm text-gray-600">Video call</div>
            </button>
            <button
              v-if="tutor.offers_in_person"
              @click="selectLessonType('in_person')"
              :class="[
                'p-4 text-center rounded-xl border-2 transition-all',
                bookingData.lesson_type === 'in_person'
                  ? 'border-blue-500 bg-blue-50 text-blue-900'
                  : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <div class="text-2xl mb-2">🏠</div>
              <div class="font-medium">Față în față</div>
              <div class="text-sm text-gray-600">{{ tutor.location && tutor.location.city ? tutor.location.city : 'Local' }}</div>
            </button>
          </div>

          <!-- Debug indicator -->
          <div class="text-xs text-gray-500 bg-gray-100 p-2 rounded">
            Tip lecție selectat: {{ bookingData.lesson_type || 'Niciuna' }}
          </div>
        </div>

        <!-- Step 3: Duration -->
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

        <!-- Step 4: Date Selection -->
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-gray-900">Selectează data *</label>
          <div class="grid grid-cols-7 gap-1 text-center text-xs font-medium text-gray-500 mb-2">
            <div>L</div>
            <div>M</div>
            <div>M</div>
            <div>J</div>
            <div>V</div>
            <div>S</div>
            <div>D</div>
          </div>
          <div class="grid grid-cols-7 gap-1">
            <button
              v-for="date in availableDates"
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
        </div>

        <!-- Step 5: Time Selection -->
        <div v-if="selectedDate" class="space-y-3">
          <label class="block text-sm font-semibold text-gray-900">Selectează ora *</label>

          <!-- Show available time slots if they exist -->
          <div v-if="availableTimeSlots.length > 0" class="grid grid-cols-3 sm:grid-cols-4 gap-2">
            <button
              v-for="slot in availableTimeSlots"
              :key="slot.time"
              @click="bookingData.scheduled_at = slot.datetime"
              :class="[
                'p-3 text-center rounded-lg border-2 transition-all',
                bookingData.scheduled_at === slot.datetime
                  ? 'border-blue-500 bg-blue-50 text-blue-900'
                  : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <div class="font-medium">{{ slot.time }}</div>
              <div class="text-xs text-gray-600">{{ slot.type }}</div>
            </button>
          </div>

          <!-- Show message if no time slots available -->
          <div v-else class="space-y-3">
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
              <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
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
                    <div>Ziua săptămânii: {{ availableDates.find(d => d.dateString === selectedDate) && availableDates.find(d => d.dateString === selectedDate).dayName }}</div>
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
            <button
              @click="bookingData.payment_method = 'card'"
              :class="[
                'p-4 text-center rounded-xl border-2 transition-all',
                bookingData.payment_method === 'card'
                  ? 'border-blue-500 bg-blue-50 text-blue-900'
                  : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <div class="text-2xl mb-2">💳</div>
              <div class="font-medium">Card</div>
              <div class="text-sm text-gray-600">Plată online</div>
            </button>
            <button
              v-if="bookingData.lesson_type === 'in_person'"
              @click="bookingData.payment_method = 'cash'"
              :class="[
                'p-4 text-center rounded-xl border-2 transition-all',
                bookingData.payment_method === 'cash'
                  ? 'border-blue-500 bg-blue-50 text-blue-900'
                  : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <div class="text-2xl mb-2">💵</div>
              <div class="font-medium">Cash</div>
              <div class="text-sm text-gray-600">La lecție</div>
            </button>
          </div>
        </div>

        <!-- Step 7: Notes -->
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-gray-900">Note pentru tutor (opțional)</label>
          <textarea
            v-model="bookingData.student_notes"
            rows="3"
            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
            placeholder="Spune-i tutorului despre obiectivele tale sau întrebări specifice..."
            maxlength="500"
          ></textarea>
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
          <button
            @click="submitBooking"
            :disabled="!isFormValid || submitting"
            :class="[
              'w-full py-4 rounded-xl font-semibold transition-all',
              isFormValid && !submitting
                ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700'
                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
            ]"
          >
            {{ submitting ? 'Se procesează...' : 'Confirmă rezervarea' }}
          </button>
          <button
            @click="close"
            class="w-full py-3 border-2 border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Anulează
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
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

// Computed properties
const isFormValid = computed(() => {
  const valid = bookingData.value.subject_id &&
         bookingData.value.lesson_type &&
         bookingData.value.duration_minutes &&
         bookingData.value.scheduled_at &&
         bookingData.value.payment_method

  // Debug: Log validation state
  console.log('Form validation:', {
    subject_id: !!bookingData.value.subject_id,
    lesson_type: !!bookingData.value.lesson_type,
    duration_minutes: !!bookingData.value.duration_minutes,
    scheduled_at: !!bookingData.value.scheduled_at,
    payment_method: !!bookingData.value.payment_method,
    overall_valid: valid
  })

  return valid
})

const totalPrice = computed(() => {
  return calculatePrice(bookingData.value.duration_minutes)
})

const availableDates = computed(() => {
  const dates = []
  const today = new Date()

  // Generate next 14 days
  for (let i = 1; i <= 14; i++) {
    const date = new Date(today)
    date.setDate(today.getDate() + i)

    const dateString = date.toISOString().split('T')[0]
    const dayName = date.toLocaleDateString('en', { weekday: 'long' }).toLowerCase()

    // Check if tutor has availability on this day
    const hasSlots = tutorAvailability.value[dayName] && tutorAvailability.value[dayName].length > 0

    dates.push({
      date,
      dateString,
      day: date.getDate(),
      dayName,
      hasSlots
    })
  }

  return dates
})

const availableTimeSlots = computed(() => {
  if (!selectedDate.value || !bookingData.value.lesson_type) {
    console.log('No date or lesson type selected', {
      selectedDate: selectedDate.value,
      lessonType: bookingData.value.lesson_type
    })
    return []
  }

  const selectedDateObj = availableDates.value.find(d => d.dateString === selectedDate.value)
  if (!selectedDateObj) {
    console.log('Selected date object not found')
    return []
  }

  console.log('Looking for availability on:', selectedDateObj.dayName)
  console.log('Tutor availability data:', tutorAvailability.value)

  const dayAvailability = tutorAvailability.value[selectedDateObj.dayName] || []
  console.log('Day availability:', dayAvailability)

  if (dayAvailability.length === 0) {
    console.log('No availability for this day')
    return []
  }

  const slots = []

  dayAvailability.forEach((availability, availIndex) => {
    console.log(`Processing availability ${availIndex}:`, availability)

    // Check if this availability matches the selected lesson type
    const lessonTypeMatches = availability.lesson_type === 'both' ||
                             availability.lesson_type === bookingData.value.lesson_type

    console.log('Lesson type match check:', {
      availabilityType: availability.lesson_type,
      selectedType: bookingData.value.lesson_type,
      matches: lessonTypeMatches
    })

    if (!lessonTypeMatches) {
      console.log('Skipping - lesson type mismatch')
      return
    }

    // Generate 30-minute slots within the availability window
    const startTime = availability.start_time
    const endTime = availability.end_time

    console.log('Generating slots from', startTime, 'to', endTime)

    try {
      // Handle both time formats: "HH:MM" or full datetime "2025-07-11T09:00:00.000000Z"
      let startTimeStr, endTimeStr

      if (startTime.includes('T')) {
        // Full datetime format - extract just the time part
        startTimeStr = startTime.split('T')[1].split(':').slice(0, 2).join(':') // "2025-07-11T09:00:00.000000Z" -> "09:00"
        endTimeStr = endTime.split('T')[1].split(':').slice(0, 2).join(':')     // "2025-07-11T12:00:00.000000Z" -> "12:00"
      } else {
        // Simple time format - just extract HH:MM
        startTimeStr = startTime.split(':').slice(0, 2).join(':') // "09:00:00" -> "09:00"
        endTimeStr = endTime.split(':').slice(0, 2).join(':')     // "12:00:00" -> "12:00"
      }

      console.log('Parsed times:', { startTimeStr, endTimeStr })

      let current = new Date(`2000-01-01T${startTimeStr}:00`)
      const end = new Date(`2000-01-01T${endTimeStr}:00`)

      console.log('Time range:', {
        current: current.toTimeString(),
        end: end.toTimeString(),
        currentValid: !isNaN(current.getTime()),
        endValid: !isNaN(end.getTime())
      })

      if (isNaN(current.getTime()) || isNaN(end.getTime())) {
        console.error('Invalid date objects created')
        return
      }

      let slotCount = 0
      while (current < end && slotCount < 20) { // Safety limit
        const timeString = current.toTimeString().slice(0, 5)
        const datetime = `${selectedDate.value}T${timeString}:00`

        // Check if this slot fits our lesson duration
        const slotEnd = new Date(current)
        slotEnd.setMinutes(current.getMinutes() + bookingData.value.duration_minutes)

        console.log('Checking slot:', {
          time: timeString,
          slotEnd: slotEnd.toTimeString().slice(0, 5),
          endTime: endTimeStr,
          fits: slotEnd <= end
        })

        if (slotEnd <= end) {
          let type = 'Ambele'
          if (availability.lesson_type === 'online') type = 'Online'
          else if (availability.lesson_type === 'in_person') type = 'Față în față'

          const slot = {
            time: timeString,
            datetime,
            type,
            availabilityIndex: availIndex
          }

          slots.push(slot)
          console.log('Added slot:', slot)
        } else {
          console.log('Slot does not fit duration requirement')
        }

        current.setMinutes(current.getMinutes() + 30)
        slotCount++
      }
    } catch (error) {
      console.error('Error generating time slots:', error)
    }
  })

  console.log('Final available slots:', slots)
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

const selectLessonType = (type) => {
  console.log('Selecting lesson type:', type)
  bookingData.value.lesson_type = type
  console.log('Lesson type after selection:', bookingData.value.lesson_type)

  // Reset time selection when lesson type changes
  bookingData.value.scheduled_at = ''
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

// Load availability when modal opens
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    console.log('Modal opened, loading availability')
    loadTutorAvailability()

    // Set default values ONLY if not already set
    if (!bookingData.value.subject_id && props.tutor.subjects && props.tutor.subjects.length === 1) {
      bookingData.value.subject_id = props.tutor.subjects[0].id
      console.log('Auto-selected single subject:', props.tutor.subjects[0].name)
    }

    // Do NOT auto-select lesson type - let user choose
    console.log('Tutor offers online:', props.tutor.offers_online)
    console.log('Tutor offers in-person:', props.tutor.offers_in_person)
  }
})
</script>
