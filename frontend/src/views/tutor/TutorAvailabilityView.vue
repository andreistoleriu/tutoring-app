<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Disponibilitate</h1>
            <p class="text-gray-600 mt-1">Setează orele în care poți preda</p>
          </div>
          <div class="flex items-center space-x-4">
            <router-link :to="{ name: 'tutor-dashboard' }"
              class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
              ← Înapoi la dashboard
            </router-link>
            <button @click="saveAvailability" :disabled="saving"
              class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50">
              {{ saving ? 'Se salvează...' : 'Salvează disponibilitatea' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Se încarcă disponibilitatea...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <p class="text-red-800">{{ error }}</p>
        </div>
        <button @click="loadAvailability" class="mt-3 px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700">
          Încearcă din nou
        </button>
      </div>

      <!-- Availability Form -->
      <div v-else>
        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
            <div class="text-3xl font-bold text-blue-600 mb-2">{{ activeDaysCount }}</div>
            <div class="text-sm text-gray-600">Zile active</div>
          </div>
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
            <div class="text-3xl font-bold text-green-600 mb-2">{{ totalSlotsCount }}</div>
            <div class="text-sm text-gray-600">Intervale orare</div>
          </div>
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
            <div class="text-3xl font-bold text-purple-600 mb-2">{{ totalHoursCount.toFixed(1) }}h</div>
            <div class="text-sm text-gray-600">Ore disponibile/săptămână</div>
          </div>
        </div>

        <!-- Week Days -->
        <div class="space-y-6">
          <div v-for="(day, dayIndex) in weekDays" :key="dayIndex"
            class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Day Header -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between"
              :class="{ 'bg-red-50 border-red-200': day.isActive && day.timeSlots.length === 0 }">
              <div class="flex items-center space-x-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ day.name }}</h3>
                <div class="flex items-center space-x-2">
                  <input :id="`day-${dayIndex}`" v-model="day.isActive" type="checkbox"
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                  <label :for="`day-${dayIndex}`" class="text-sm text-gray-600">
                    {{ day.isActive ? 'Activ' : 'Inactiv' }}
                  </label>
                </div>

                <!-- Validation Warning -->
                <div v-if="day.isActive && day.timeSlots.length === 0" class="flex items-center space-x-1 text-red-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <span class="text-xs font-medium">Adaugă intervale orare</span>
                </div>
              </div>

              <div v-if="day.isActive && day.timeSlots.length > 0" class="text-sm text-gray-500">
                {{ day.timeSlots.length }} intervale,
                {{ calculateDayTotalHours(day).toFixed(1) }}h total
              </div>
            </div>

            <!-- Time Slots Content -->
            <div class="p-6">
              <!-- Active Day - Time Slots -->
              <div v-if="day.isActive">
                <!-- Existing Time Slots -->
                <div v-if="day.timeSlots.length > 0" class="space-y-4 mb-6">
                  <div v-for="(slot, slotIndex) in day.timeSlots" :key="slotIndex"
                    class="flex items-center space-x-4 p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <!-- Time Range -->
                    <div class="flex items-center space-x-2">
                      <select v-model="slot.startTime"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                        <option v-for="time in timeOptions" :key="time" :value="time">
                          {{ time }}
                        </option>
                      </select>

                      <span class="text-gray-500">-</span>

                      <select v-model="slot.endTime"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                        <option v-for="time in timeOptions" :key="time" :value="time">
                          {{ time }}
                        </option>
                      </select>
                    </div>

                    <!-- Duration Display -->
                    <div class="text-sm text-gray-600 min-w-[80px]">
                      {{ calculateSlotDuration(slot.startTime, slot.endTime) }}
                    </div>

                    <!-- Lesson Type -->
                    <div class="flex-1">
                      <select v-model="slot.lessonType"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                        <option value="both">🌐 Ambele tipuri</option>
                        <option value="online">💻 Doar online</option>
                        <option value="in_person">📍 Doar față în față</option>
                      </select>
                    </div>

                    <!-- Remove Button -->
                    <button @click="removeTimeSlot(dayIndex, slotIndex)"
                      class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
                      title="Șterge interval">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                      </svg>
                    </button>
                  </div>
                </div>

                <!-- Add New Time Slot -->
                <button @click="addTimeSlot(dayIndex)"
                  class="w-full py-4 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200 flex items-center justify-center space-x-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  <span class="font-medium">Adaugă interval orar</span>
                </button>
              </div>

              <!-- Inactive Day -->
              <div v-else class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                  </svg>
                </div>
                <p class="text-gray-500 font-medium">Zi inactivă</p>
                <p class="text-sm text-gray-400 mt-1">Activează ziua pentru a adăuga intervale orare</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Booking Rules -->
        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
              </path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Reguli de rezervare
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Preaviz minim
              </label>
              <select v-model="bookingRules.advanceNotice"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="2">2 ore în avans</option>
                <option value="4">4 ore în avans</option>
                <option value="12">12 ore în avans</option>
                <option value="24">24 ore în avans</option>
                <option value="48">48 ore în avans</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Rezervare maximă în avans
              </label>
              <select v-model="bookingRules.maxAdvanceDays"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="7">7 zile în avans</option>
                <option value="14">14 zile în avans</option>
                <option value="30">30 zile în avans</option>
                <option value="60">60 zile în avans</option>
                <option value="90">90 zile în avans</option>
              </select>
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
import api from '@/services/api'

const router = useRouter()

// Reactive data
const saving = ref(false)
const loading = ref(false)
const error = ref(null)

// Week days data structure
const weekDays = ref([
  { name: 'Luni', dayOfWeek: 1, isActive: false, timeSlots: [] },
  { name: 'Marți', dayOfWeek: 2, isActive: false, timeSlots: [] },
  { name: 'Miercuri', dayOfWeek: 3, isActive: false, timeSlots: [] },
  { name: 'Joi', dayOfWeek: 4, isActive: false, timeSlots: [] },
  { name: 'Vineri', dayOfWeek: 5, isActive: false, timeSlots: [] },
  { name: 'Sâmbătă', dayOfWeek: 6, isActive: false, timeSlots: [] },
  { name: 'Duminică', dayOfWeek: 0, isActive: false, timeSlots: [] }
])

// Booking rules
const bookingRules = ref({
  advanceNotice: 24,
  maxAdvanceDays: 30
})

// Time options
const timeOptions = ref([])

// Generate time options (6:00 - 23:00, 30-minute intervals)
const generateTimeOptions = () => {
  const options = []
  for (let hour = 6; hour <= 23; hour++) {
    for (let minute = 0; minute < 60; minute += 30) {
      const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`
      options.push(timeString)
    }
  }
  timeOptions.value = options
}

// Computed properties
const activeDaysCount = computed(() => {
  return weekDays.value.filter(day => day.isActive).length
})

const totalSlotsCount = computed(() => {
  return weekDays.value.reduce((total, day) => {
    return total + (day.isActive ? day.timeSlots.length : 0)
  }, 0)
})

const totalHoursCount = computed(() => {
  return weekDays.value.reduce((total, day) => {
    return total + calculateDayTotalHours(day)
  }, 0)
})

// Helper functions
const calculateSlotDuration = (startTime, endTime) => {
  if (!startTime || !endTime) return '0 min'

  const minutes = calculateSlotDurationMinutes(startTime, endTime)
  const hours = Math.floor(minutes / 60)
  const remainingMinutes = minutes % 60

  if (hours > 0) {
    return remainingMinutes > 0 ? `${hours}h ${remainingMinutes}m` : `${hours}h`
  }
  return `${remainingMinutes}m`
}

const calculateSlotDurationMinutes = (startTime, endTime) => {
  if (!startTime || !endTime) return 0

  const [startHour, startMinute] = startTime.split(':').map(Number)
  const [endHour, endMinute] = endTime.split(':').map(Number)

  const startTotalMinutes = startHour * 60 + startMinute
  const endTotalMinutes = endHour * 60 + endMinute

  return Math.max(0, endTotalMinutes - startTotalMinutes)
}

const calculateDayTotalHours = (day) => {
  if (!day.isActive) return 0

  return day.timeSlots.reduce((total, slot) => {
    const duration = calculateSlotDurationMinutes(slot.startTime, slot.endTime)
    return total + (duration / 60)
  }, 0)
}

// Load existing availability
const loadAvailability = async () => {
  loading.value = true
  error.value = null

  try {
    console.log('Loading availability...')

    const response = await api.get('tutor/availability')
    console.log('Availability response:', response.data)

    const availability = response.data.availability || response.data

    // Reset weekDays
    weekDays.value.forEach(day => {
      day.isActive = false
      day.timeSlots = []
    })

    // Populate availability data
    if (availability && typeof availability === 'object') {
      Object.keys(availability).forEach(dayNumber => {
        const dayData = availability[dayNumber]
        const dayIndex = weekDays.value.findIndex(d => d.dayOfWeek === parseInt(dayNumber))

        if (dayIndex !== -1 && Array.isArray(dayData)) {
          weekDays.value[dayIndex].isActive = dayData.length > 0
          weekDays.value[dayIndex].timeSlots = dayData.map(slot => ({
            id: slot.id || null,
            startTime: slot.start_time,
            endTime: slot.end_time,
            lessonType: slot.lesson_type || 'both',
            isActive: slot.is_active !== false
          }))
        }
      })
    }

  } catch (err) {
    console.error('Error loading availability:', err)
    error.value = 'Eroare la încărcarea disponibilității. Încearcă din nou.'

    // Set default empty state
    weekDays.value.forEach(day => {
      day.isActive = false
      day.timeSlots = []
    })
  } finally {
    loading.value = false
  }
}

// Add time slot to a day
const addTimeSlot = (dayIndex) => {
  const lastSlot = weekDays.value[dayIndex].timeSlots.slice(-1)[0]
  const defaultStartTime = lastSlot ? lastSlot.endTime : '09:00'
  const defaultEndTime = lastSlot ?
    (parseInt(lastSlot.endTime.split(':')[0]) + 1).toString().padStart(2, '0') + ':00' :
    '10:00'

  weekDays.value[dayIndex].timeSlots.push({
    id: null,
    startTime: defaultStartTime,
    endTime: defaultEndTime,
    lessonType: 'both',
    isActive: true
  })

  // Activate the day if it wasn't active
  if (!weekDays.value[dayIndex].isActive) {
    weekDays.value[dayIndex].isActive = true
  }
}

// Remove time slot from a day
const removeTimeSlot = (dayIndex, slotIndex) => {
  weekDays.value[dayIndex].timeSlots.splice(slotIndex, 1)

  // If no time slots left, deactivate the day
  if (weekDays.value[dayIndex].timeSlots.length === 0) {
    weekDays.value[dayIndex].isActive = false
  }
}

// Validate time slots
const validateTimeSlots = () => {
  for (const day of weekDays.value) {
    if (day.isActive) {
      if (day.timeSlots.length === 0) {
        alert(`Te rugăm să adaugi cel puțin un interval orar pentru ${day.name}.`)
        return false
      }

      for (const slot of day.timeSlots) {
        if (!slot.startTime || !slot.endTime) {
          alert(`Te rugăm să completezi toate orele pentru ${day.name}.`)
          return false
        }

        if (slot.startTime >= slot.endTime) {
          alert(`Ora de început trebuie să fie mai mică decât ora de sfârșit pentru ${day.name}.`)
          return false
        }

        if (!slot.lessonType) {
          alert(`Te rugăm să selectezi tipul de lecție pentru ${day.name}.`)
          return false
        }
      }

      // Check for overlapping slots
      for (let i = 0; i < day.timeSlots.length; i++) {
        for (let j = i + 1; j < day.timeSlots.length; j++) {
          const slot1 = day.timeSlots[i]
          const slot2 = day.timeSlots[j]

          if ((slot1.startTime < slot2.endTime && slot1.endTime > slot2.startTime)) {
            alert(`Intervalele orare se suprapun pentru ${day.name}.`)
            return false
          }
        }
      }
    }
  }
  return true
}

// Save availability
const saveAvailability = async () => {
  if (!validateTimeSlots()) {
    return
  }

  saving.value = true
  error.value = null

  try {
    console.log('Saving availability...')

    // Prepare availability data for API
    const availabilityData = []

    weekDays.value.forEach(day => {
      if (day.isActive && day.timeSlots.length > 0) {
        day.timeSlots.forEach(slot => {
          availabilityData.push({
            day_of_week: day.dayOfWeek,
            start_time: slot.startTime,
            end_time: slot.endTime,
            lesson_type: slot.lessonType,
            is_active: slot.isActive !== false
          })
        })
      }
    })

    console.log('Availability data to save:', { availability: availabilityData })

    const response = await api.put('tutor/availability', {
      availability: availabilityData
    })

    console.log('Save response:', response.data)

    alert('Disponibilitatea a fost salvată cu succes!')
    router.push({ name: 'tutor-dashboard' })

  } catch (err) {
    console.error('Error saving availability:', err)
    error.value = 'A apărut o eroare la salvarea disponibilității'

    if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      let errorMessage = 'Erori de validare:\n'
      Object.keys(errors).forEach(field => {
        errorMessage += `- ${field}: ${errors[field].join(', ')}\n`
      })
      alert(errorMessage)
    } else {
      alert('A apărut o eroare la salvarea disponibilității. Încearcă din nou.')
    }
  } finally {
    saving.value = false
  }
}

// Lifecycle
onMounted(async () => {
  console.log('TutorAvailabilityView mounted - loading real data from API')
  generateTimeOptions()
  await loadAvailability()
})
</script>
