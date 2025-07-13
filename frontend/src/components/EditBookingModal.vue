<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="p-4 sm:p-6 border-b border-gray-100">
        <div class="flex items-center justify-between">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Editează rezervarea</h2>
          <button @click="close" class="text-gray-400 hover:text-gray-600 p-1">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
        <!-- Current Booking Info -->
        <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
          <h3 class="font-semibold text-gray-900 mb-2 text-sm sm:text-base">Rezervarea curentă</h3>
          <div class="space-y-1 text-xs sm:text-sm text-gray-600">
            <p><span class="font-medium">Profesor:</span> {{ getTutorName() }}</p>
            <p><span class="font-medium">Materie:</span> {{ booking.subject?.name }}</p>
            <p><span class="font-medium">Data:</span> {{ formatDateTime(booking.scheduled_at) }}</p>
            <p><span class="font-medium">Durată:</span> {{ booking.duration_minutes }} min</p>
            <p><span class="font-medium">Tip:</span> {{ booking.lesson_type === 'online' ? 'Online' : 'Față în față' }}</p>
            <p><span class="font-medium">Preț:</span> {{ booking.price }} RON</p>
          </div>
        </div>

        <!-- Edit Form -->
        <form @submit.prevent="handleUpdate" class="space-y-4 sm:space-y-6">
          <!-- Subject Selection -->
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-900">Materie *</label>

            <!-- Debug info -->
            <div v-if="availableSubjects.length === 0" class="text-xs text-gray-500 bg-yellow-50 p-2 rounded">
              Se încarcă materiile... ({{ availableSubjects.length }} found)
            </div>

            <div v-if="availableSubjects.length > 0" class="grid grid-cols-1 gap-2">
              <button
                v-for="subject in availableSubjects"
                :key="subject.id"
                type="button"
                @click="selectSubject(subject.id)"
                :class="[
                  'p-3 text-left rounded-lg border-2 transition-all',
                  editData.subject_id === subject.id
                    ? 'border-indigo-500 bg-indigo-50 text-indigo-900'
                    : 'border-gray-200 hover:border-gray-300'
                ]"
              >
                <div class="font-medium text-sm">{{ subject.name }}</div>
                <div v-if="subject.description" class="text-xs text-gray-600 mt-1">{{ subject.description }}</div>
              </button>
            </div>

            <!-- Fallback if no subjects loaded -->
            <div v-else class="text-sm text-gray-500 bg-gray-50 p-3 rounded-lg">
              Nu s-au putut încărca materiile. Se folosesc valorile implicite...
              <button
                @click="loadTutorSubjects()"
                class="ml-2 text-blue-600 hover:text-blue-800 underline"
              >
                Încearcă din nou
              </button>
            </div>
          </div>

          <!-- Lesson Type -->
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-900">Tip lecție *</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <button
                type="button"
                @click="selectLessonType('online')"
                :class="[
                  'p-3 sm:p-4 rounded-xl border-2 transition-all text-left',
                  editData.lesson_type === 'online'
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-200 hover:border-gray-300'
                ]"
              >
                <div class="font-medium text-sm sm:text-base">Online</div>
                <div class="text-xs text-gray-600">Video call</div>
              </button>

              <button
                type="button"
                @click="selectLessonType('in_person')"
                :class="[
                  'p-3 sm:p-4 rounded-xl border-2 transition-all text-left',
                  editData.lesson_type === 'in_person'
                    ? 'border-purple-500 bg-purple-50'
                    : 'border-gray-200 hover:border-gray-300'
                ]"
              >
                <div class="font-medium text-sm sm:text-base">Față în față</div>
                <div class="text-xs text-gray-600">La domiciliu</div>
              </button>
            </div>
          </div>

          <!-- Duration -->
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-900">Durată *</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3">
              <button
                v-for="duration in durationOptions"
                :key="duration.value"
                type="button"
                @click="editData.duration_minutes = duration.value"
                :class="[
                  'p-2 sm:p-3 text-center rounded-lg border-2 transition-all',
                  editData.duration_minutes === duration.value
                    ? 'border-blue-500 bg-blue-50 text-blue-900'
                    : 'border-gray-200 hover:border-gray-300'
                ]"
              >
                <div class="font-medium text-xs sm:text-sm">{{ duration.label }}</div>
                <div class="text-xs text-gray-600">{{ calculatePrice(duration.value) }} RON</div>
              </button>
            </div>
          </div>

          <!-- Date Selection -->
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
                type="button"
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

          <!-- Time Selection -->
          <div v-if="selectedDate" class="space-y-3">
            <label class="block text-sm font-semibold text-gray-900">Selectează ora *</label>

            <!-- Show available time slots if they exist -->
            <div v-if="availableTimeSlots.length > 0" class="grid grid-cols-3 sm:grid-cols-4 gap-2">
              <button
                v-for="slot in availableTimeSlots"
                :key="slot.time"
                type="button"
                @click="editData.scheduled_at = slot.datetime"
                :class="[
                  'p-3 text-center rounded-lg border-2 transition-all',
                  editData.scheduled_at === slot.datetime
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
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-900">Metoda de plată</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <button
                type="button"
                @click="editData.payment_method = 'card'"
                :class="[
                  'p-3 sm:p-4 rounded-xl border-2 transition-all text-left',
                  editData.payment_method === 'card'
                    ? 'border-green-500 bg-green-50'
                    : 'border-gray-200 hover:border-gray-300'
                ]"
              >
                <div class="font-medium text-sm sm:text-base">Card</div>
                <div class="text-xs text-gray-600">Plată online</div>
              </button>

              <button
                type="button"
                @click="editData.payment_method = 'cash'"
                :class="[
                  'p-3 sm:p-4 rounded-xl border-2 transition-all text-left',
                  editData.payment_method === 'cash'
                    ? 'border-orange-500 bg-orange-50'
                    : 'border-gray-200 hover:border-gray-300'
                ]"
              >
                <div class="font-medium text-sm sm:text-base">Cash</div>
                <div class="text-xs text-gray-600">Plată la lecție</div>
              </button>
            </div>
          </div>

          <!-- Notes -->
          <div class="space-y-3">
            <label for="notes" class="block text-sm font-semibold text-gray-900">Note pentru profesor</label>
            <textarea
              id="notes"
              v-model="editData.student_notes"
              rows="3"
              placeholder="Adaugă detalii suplimentare..."
              class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-sm sm:text-base"
            ></textarea>
          </div>

          <!-- Summary -->
          <div v-if="hasAnyChanges" class="bg-blue-50 rounded-lg p-3 sm:p-4">
            <h4 class="font-semibold text-blue-900 mb-2 text-sm sm:text-base">Rezumat modificări</h4>
            <div class="space-y-1 text-xs sm:text-sm text-blue-800">
              <p v-if="hasChanges.subject_id">
                <span class="font-medium">Materie:</span>
                {{ getSubjectName(editData.subject_id) }}
              </p>
              <p v-if="hasChanges.lesson_type">
                <span class="font-medium">Tip lecție:</span>
                {{ editData.lesson_type === 'online' ? 'Online' : 'Față în față' }}
              </p>
              <p v-if="hasChanges.duration">
                <span class="font-medium">Durată:</span> {{ editData.duration_minutes }} min
              </p>
              <p v-if="hasChanges.scheduled_at">
                <span class="font-medium">Data și ora:</span> {{ formatDateTime(editData.scheduled_at) }}
              </p>
              <p v-if="hasChanges.payment_method">
                <span class="font-medium">Plată:</span>
                {{ editData.payment_method === 'card' ? 'Card' : 'Cash' }}
              </p>
              <p v-if="priceChanged" class="font-semibold">
                <span class="font-medium">Preț nou:</span> {{ newPrice }} RON
                <span class="text-xs">({{ priceChange > 0 ? '+' : '' }}{{ priceChange }} RON)</span>
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col gap-3">
            <button
              type="submit"
              :disabled="!isFormValid || submitting"
              :class="[
                'w-full py-3 rounded-xl font-medium transition-all text-sm sm:text-base',
                isFormValid && !submitting
                  ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700'
                  : 'bg-gray-300 text-gray-500 cursor-not-allowed'
              ]"
            >
              {{ submitting ? 'Se actualizează...' : 'Salvează modificările' }}
            </button>
            <button
              type="button"
              @click="close"
              class="w-full py-3 border-2 border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition-colors text-sm sm:text-base"
            >
              Anulează
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useStudentStore } from '@/stores/student'
import api from '@/services/api'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  booking: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['close', 'success'])

// Store
const studentStore = useStudentStore()

// Reactive data
const submitting = ref(false)
const selectedDate = ref('')
const tutorAvailability = ref({})
const availableSubjects = ref([])

// Duration options
const durationOptions = [
  { value: 30, label: '30 min' },
  { value: 60, label: '1 oră' },
  { value: 90, label: '1.5 ore' },
  { value: 120, label: '2 ore' }
]

// Edit form data
const editData = ref({
  subject_id: null,
  lesson_type: '',
  duration_minutes: 60,
  scheduled_at: '',
  payment_method: 'card',
  student_notes: ''
})

// Computed properties
const tutor = computed(() => {
  // Handle different possible tutor object structures
  return props.booking.tutor?.tutor ||
         props.booking.tutor ||
         { offers_online: true, offers_in_person: true, hourly_rate: 75 }
})

const availableDates = computed(() => {
  const dates = []
  const today = new Date()

  console.log('Computing available dates with availability:', tutorAvailability.value)

  // Generate next 14 days
  for (let i = 1; i <= 14; i++) {
    const date = new Date(today)
    date.setDate(today.getDate() + i)

    const dateString = date.toISOString().split('T')[0]
    const dayName = date.toLocaleDateString('en', { weekday: 'long' }).toLowerCase()

    console.log('Checking day:', dayName, 'availability:', tutorAvailability.value[dayName])

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

  console.log('Generated dates:', dates)
  return dates
})

const availableTimeSlots = computed(() => {
  if (!selectedDate.value || !editData.value.lesson_type) {
    console.log('No date or lesson type selected', {
      selectedDate: selectedDate.value,
      lessonType: editData.value.lesson_type
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
                             availability.lesson_type === editData.value.lesson_type

    console.log('Lesson type match check:', {
      availabilityType: availability.lesson_type,
      selectedType: editData.value.lesson_type,
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
      // Handle both time formats: "HH:MM" or full datetime
      let startTimeStr, endTimeStr

      if (startTime.includes('T')) {
        startTimeStr = startTime.split('T')[1].split(':').slice(0, 2).join(':')
        endTimeStr = endTime.split('T')[1].split(':').slice(0, 2).join(':')
      } else {
        startTimeStr = startTime.split(':').slice(0, 2).join(':')
        endTimeStr = endTime.split(':').slice(0, 2).join(':')
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
      while (current < end && slotCount < 20) {
        const timeString = current.toTimeString().slice(0, 5)
        const datetime = `${selectedDate.value}T${timeString}:00`

        // Check if this slot fits our lesson duration
        const slotEnd = new Date(current)
        slotEnd.setMinutes(current.getMinutes() + editData.value.duration_minutes)

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

const hasChanges = computed(() => ({
  subject_id: editData.value.subject_id !== props.booking.subject_id,
  lesson_type: editData.value.lesson_type !== props.booking.lesson_type,
  duration: editData.value.duration_minutes !== props.booking.duration_minutes,
  scheduled_at: editData.value.scheduled_at !== props.booking.scheduled_at,
  payment_method: editData.value.payment_method !== props.booking.payment_method,
  student_notes: editData.value.student_notes !== (props.booking.student_notes || '')
}))

const hasAnyChanges = computed(() => Object.values(hasChanges.value).some(changed => changed))

const newPrice = computed(() => calculatePrice(editData.value.duration_minutes))

const priceChanged = computed(() => newPrice.value !== props.booking.price)

const priceChange = computed(() => newPrice.value - props.booking.price)

const isFormValid = computed(() => {
  return editData.value.subject_id &&
         editData.value.lesson_type &&
         editData.value.duration_minutes &&
         editData.value.scheduled_at &&
         editData.value.payment_method &&
         hasAnyChanges.value
})

// Methods
const calculatePrice = (duration) => {
  return Math.round((duration / 60) * (tutor.value.hourly_rate || 75))
}

const getTutorName = () => {
  const booking = props.booking

  // Try different possible structures for tutor name
  if (booking.tutor?.user?.first_name) {
    return `${booking.tutor.user.first_name} ${booking.tutor.user.last_name}`
  }
  if (booking.tutor?.first_name) {
    return `${booking.tutor.first_name} ${booking.tutor.last_name}`
  }
  if (booking.tutor?.full_name) {
    return booking.tutor.full_name
  }
  if (booking.tutor?.name) {
    return booking.tutor.name
  }

  return 'Profesor'
}

const selectSubject = (subjectId) => {
  console.log('Selecting subject:', subjectId)
  editData.value.subject_id = subjectId
  console.log('Subject ID set to:', editData.value.subject_id)
  console.log('Current editData:', editData.value)
}

const selectLessonType = (type) => {
  console.log('Selecting lesson type:', type)
  editData.value.lesson_type = type
  // Reset time selection when lesson type changes
  editData.value.scheduled_at = ''
}

const getSubjectName = (subjectId) => {
  const subject = availableSubjects.value.find(s => s.id === subjectId)
  return subject ? subject.name : 'Materie selectată'
}

const formatDateTime = (dateTime) => {
  if (!dateTime) return ''
  const date = new Date(dateTime)
  return date.toLocaleString('ro-RO', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const loadTutorSubjects = async () => {
  try {
    console.log('=== LOADING TUTOR SUBJECTS ===')
    console.log('Full booking object:', props.booking)
    console.log('Booking tutor:', props.booking.tutor)
    console.log('Booking tutor subjects:', props.booking.tutor?.subjects)

    // Try to get tutor subjects from the booking object
    if (props.booking.tutor?.subjects && Array.isArray(props.booking.tutor.subjects) && props.booking.tutor.subjects.length > 0) {
      availableSubjects.value = props.booking.tutor.subjects
      console.log('✅ SUCCESS: Using tutor subjects from booking:', availableSubjects.value)
      return
    }

    // If not in booking, try to get tutor details from API
    console.log('No subjects in booking, fetching tutor details from API...')

    const tutorId = props.booking.tutor_id ||
                   props.booking.tutor?.id ||
                   props.booking.tutor?.user_id ||
                   props.booking.tutor?.user?.id

    console.log('Resolved tutor ID:', tutorId)

    if (tutorId) {
      console.log('Fetching tutor details for ID:', tutorId)
      const response = await api.get(`tutors/${tutorId}`)
      console.log('Tutor API response:', response.data)

      if (response.data.tutor?.subjects && Array.isArray(response.data.tutor.subjects)) {
        availableSubjects.value = response.data.tutor.subjects
        console.log('✅ SUCCESS: Using tutor subjects from API:', availableSubjects.value)
        return
      }

      // Try other possible response structures
      if (response.data.subjects && Array.isArray(response.data.subjects)) {
        availableSubjects.value = response.data.subjects
        console.log('✅ SUCCESS: Using subjects from API response:', availableSubjects.value)
        return
      }
    }

    console.log('⚠️ Could not load tutor subjects, using current subject as fallback')

    // Fallback: use current booking subject
    if (props.booking.subject) {
      availableSubjects.value = [props.booking.subject]
      console.log('✅ Fallback: Using current booking subject:', availableSubjects.value)
    } else {
      availableSubjects.value = [{
        id: props.booking.subject_id,
        name: 'Programare',
        description: 'Materia din rezervarea curentă'
      }]
      console.log('✅ Fallback: Created subject from booking data:', availableSubjects.value)
    }

  } catch (error) {
    console.error('❌ ERROR loading tutor subjects:', error)

    // Error fallback
    availableSubjects.value = [{
      id: props.booking.subject_id || 1,
      name: props.booking.subject?.name || 'Programare',
      description: 'Materia din rezervarea curentă'
    }]
    console.log('✅ Error fallback subject created:', availableSubjects.value)
  }
}
const loadTutorAvailability = async () => {
  try {
    // Debug: Check all possible tutor ID fields
    console.log('Booking object:', props.booking)
    console.log('Tutor from booking:', props.booking.tutor)

    const tutorId = props.booking.tutor_id ||
                   props.booking.tutor?.id ||
                   props.booking.tutor?.user_id ||
                   props.booking.tutor?.user?.id

    console.log('Resolved tutor ID:', tutorId)

    if (!tutorId) {
      console.error('No tutor ID found in booking object')
      return
    }

    console.log('Loading availability for tutor:', tutorId)
    const response = await api.get(`tutors/${tutorId}/availability`)
    console.log('Availability response:', response.data)

    tutorAvailability.value = response.data.availability || {}
    console.log('Tutor availability set to:', tutorAvailability.value)
  } catch (error) {
    console.error('Error loading tutor availability:', error)
    tutorAvailability.value = {}
  }
}

const selectDate = (date) => {
  selectedDate.value = date.dateString
  editData.value.scheduled_at = '' // Reset time selection
}

const handleUpdate = async () => {
  if (!isFormValid.value) {
    console.log('Form is not valid, cannot submit')
    console.log('Form validation state:', {
      subject_id: !!editData.value.subject_id,
      lesson_type: !!editData.value.lesson_type,
      duration_minutes: !!editData.value.duration_minutes,
      scheduled_at: !!editData.value.scheduled_at,
      payment_method: !!editData.value.payment_method,
      hasChanges: hasAnyChanges.value
    })
    return
  }

  submitting.value = true

  try {
    console.log('Submitting booking update with data:', editData.value)

    const response = await studentStore.updateBooking(props.booking.id, editData.value)
    console.log('Update response:', response)

    emit('success', response.booking)
    close()
  } catch (error) {
    console.error('Error updating booking:', error)
    alert(error.message || 'Eroare la actualizarea rezervării')
  } finally {
    submitting.value = false
  }
}

const close = () => {
  emit('close')
}

// Initialize form data
const initializeForm = () => {
  if (!props.booking) return

  console.log('=== BOOKING OBJECT STRUCTURE ===')
  console.log('Full booking:', JSON.stringify(props.booking, null, 2))
  console.log('Booking keys:', Object.keys(props.booking))
  console.log('Tutor object:', props.booking.tutor)
  console.log('Tutor keys:', props.booking.tutor ? Object.keys(props.booking.tutor) : 'No tutor object')
  console.log('================================')

  editData.value = {
    subject_id: props.booking.subject_id,
    lesson_type: props.booking.lesson_type,
    duration_minutes: props.booking.duration_minutes,
    scheduled_at: props.booking.scheduled_at,
    payment_method: props.booking.payment_method,
    student_notes: props.booking.student_notes || ''
  }

  // Set selected date for time slot display
  if (props.booking.scheduled_at) {
    selectedDate.value = props.booking.scheduled_at.split('T')[0]
    console.log('Set selectedDate to:', selectedDate.value)
  }

  console.log('Form initialized with:', editData.value)
}

// Watch for changes
watch(() => props.booking, initializeForm, { immediate: true })

watch(() => props.isOpen, (isOpen) => {
  console.log('Modal open state changed:', isOpen)
  if (isOpen && props.booking) {
    console.log('Modal opened, initializing and loading data')
    initializeForm()
    loadTutorSubjects()
    loadTutorAvailability()
  }
})

watch(() => editData.value.lesson_type, () => {
  console.log('Lesson type changed to:', editData.value.lesson_type)
  // Reset time selection when lesson type changes
  editData.value.scheduled_at = ''
})

watch(() => editData.value.duration_minutes, () => {
  console.log('Duration changed to:', editData.value.duration_minutes)
  // Reset time selection when duration changes
  editData.value.scheduled_at = ''
})

onMounted(() => {
  console.log('EditBookingModal mounted', {
    isOpen: props.isOpen,
    booking: props.booking
  })

  if (props.isOpen && props.booking) {
    initializeForm()
    console.log('Loading data on mount...')
    loadTutorSubjects()
    loadTutorAvailability()
  }
})
</script>
