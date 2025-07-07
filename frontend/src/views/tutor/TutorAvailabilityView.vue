<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Reactive data
const saving = ref(false)

const weekDays = ref([
  {
    name: 'Luni',
    isActive: true,
    timeSlots: [
      { startTime: '09:00', endTime: '12:00' },
      { startTime: '14:00', endTime: '17:00' }
    ]
  },
  {
    name: 'Marți',
    isActive: true,
    timeSlots: [
      { startTime: '09:00', endTime: '12:00' },
      { startTime: '14:00', endTime: '17:00' }
    ]
  },
  {
    name: 'Miercuri',
    isActive: true,
    timeSlots: [
      { startTime: '09:00', endTime: '12:00' },
      { startTime: '14:00', endTime: '17:00' }
    ]
  },
  {
    name: 'Joi',
    isActive: true,
    timeSlots: [
      { startTime: '09:00', endTime: '12:00' },
      { startTime: '14:00', endTime: '17:00' }
    ]
  },
  {
    name: 'Vineri',
    isActive: true,
    timeSlots: [
      { startTime: '09:00', endTime: '12:00' },
      { startTime: '14:00', endTime: '17:00' }
    ]
  },
  {
    name: 'Sâmbătă',
    isActive: false,
    timeSlots: []
  },
  {
    name: 'Duminică',
    isActive: false,
    timeSlots: []
  }
])

const bookingRules = ref({
  advanceNotice: 24,
  maxAdvanceDays: 30
})

const timeOptions = [
  '06:00', '06:30', '07:00', '07:30', '08:00', '08:30', '09:00', '09:30',
  '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30',
  '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
  '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00', '21:30',
  '22:00', '22:30', '23:00'
]

// Computed properties
const activeDaysCount = computed(() => {
  return weekDays.value.filter(day => day.isActive).length
})

const totalSlotsCount = computed(() => {
  return weekDays.value.reduce((total, day) => {
    return total + (day.isActive ? day.timeSlots.length : 0)
  }, 0)
})

const totalHoursPerWeek = computed(() => {
  let totalMinutes = 0
  weekDays.value.forEach(day => {
    if (day.isActive) {
      day.timeSlots.forEach(slot => {
        if (slot.startTime && slot.endTime) {
          const start = parseTime(slot.startTime)
          const end = parseTime(slot.endTime)
          if (end > start) {
            totalMinutes += end - start
          }
        }
      })
    }
  })
  return Math.round(totalMinutes / 60 * 10) / 10 // Round to 1 decimal
})

const averageHoursPerDay = computed(() => {
  if (activeDaysCount.value === 0) return 0
  return Math.round(totalHoursPerWeek.value / activeDaysCount.value * 10) / 10
})

// Methods
const parseTime = (timeStr) => {
  if (!timeStr) return 0
  const [hours, minutes] = timeStr.split(':').map(Number)
  return hours * 60 + minutes
}

const addTimeSlot = (dayIndex) => {
  weekDays.value[dayIndex].timeSlots.push({
    startTime: '',
    endTime: ''
  })
}

const removeTimeSlot = (dayIndex, slotIndex) => {
  weekDays.value[dayIndex].timeSlots.splice(slotIndex, 1)
}

const setBusinessHours = () => {
  weekDays.value.forEach((day, index) => {
    if (index < 5) { // Monday to Friday
      day.isActive = true
      day.timeSlots = [
        { startTime: '09:00', endTime: '12:00' },
        { startTime: '13:00', endTime: '17:00' }
      ]
    } else {
      day.isActive = false
      day.timeSlots = []
    }
  })
}

const setEveningHours = () => {
  weekDays.value.forEach((day, index) => {
    if (index < 5) { // Monday to Friday
      day.isActive = true
      day.timeSlots = [
        { startTime: '17:00', endTime: '21:00' }
      ]
    } else {
      day.isActive = false
      day.timeSlots = []
    }
  })
}

const clearAllSlots = () => {
  if (confirm('Ești sigur că vrei să ștergi toate intervalele orare?')) {
    weekDays.value.forEach(day => {
      day.isActive = false
      day.timeSlots = []
    })
  }
}

const validateTimeSlots = () => {
  for (let day of weekDays.value) {
    if (day.isActive) {
      for (let slot of day.timeSlots) {
        if (!slot.startTime || !slot.endTime) {
          alert('Te rugăm să completezi toate intervalele orare sau să le ștergi.')
          return false
        }
        if (parseTime(slot.startTime) >= parseTime(slot.endTime)) {
          alert('Ora de început trebuie să fie înainte de ora de sfârșit.')
          return false
        }
      }
    }
  }
  return true
}

const saveAvailability = async () => {
  if (!validateTimeSlots()) {
    return
  }

  saving.value = true

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000))

    // Here you would normally call your API
    // await tutorStore.updateAvailability({
    //   weekDays: weekDays.value,
    //   bookingRules: bookingRules.value
    // })

    alert('Disponibilitatea a fost salvată cu succes!')
    router.push({ name: 'tutor-dashboard' })
  } catch (error) {
    console.error('Error saving availability:', error)
    alert('A apărut o eroare la salvarea disponibilității.')
  } finally {
    saving.value = false
  }
}

// Lifecycle
onMounted(() => {
  // Load existing availability data
  console.log('Availability component mounted')
})
</script><template>
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
            <router-link
              :to="{ name: 'tutor-dashboard' }"
              class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
            >
              ← Înapoi la dashboard
            </router-link>
            <button
              @click="saveAvailability"
              :disabled="saving"
              class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50"
            >
              {{ saving ? 'Se salvează...' : 'Salvează' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Availability Calendar -->
      <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-8">
        <div class="mb-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-2">Program săptămânal</h2>
          <p class="text-gray-600">Selectează intervalele orare în care ești disponibil pentru lecții.</p>
        </div>

        <!-- Week Days Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-7 gap-6">
          <div
            v-for="(day, dayIndex) in weekDays"
            :key="dayIndex"
            class="bg-gray-50 rounded-xl p-4"
          >
            <!-- Day Header -->
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-gray-900">{{ day.name }}</h3>
              <div class="flex items-center space-x-2">
                <input
                  :id="`day-${dayIndex}`"
                  v-model="day.isActive"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                >
                <label :for="`day-${dayIndex}`" class="text-sm text-gray-600">Activ</label>
              </div>
            </div>

            <!-- Time Slots -->
            <div v-if="day.isActive" class="space-y-3">
              <div
                v-for="(slot, slotIndex) in day.timeSlots"
                :key="slotIndex"
                class="flex items-center space-x-2"
              >
                <select
                  v-model="slot.startTime"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Început</option>
                  <option v-for="time in timeOptions" :key="time" :value="time">{{ time }}</option>
                </select>
                <span class="text-gray-500">-</span>
                <select
                  v-model="slot.endTime"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Sfârșit</option>
                  <option v-for="time in timeOptions" :key="time" :value="time">{{ time }}</option>
                </select>
                <button
                  @click="removeTimeSlot(dayIndex, slotIndex)"
                  class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>

              <!-- Add Time Slot Button -->
              <button
                @click="addTimeSlot(dayIndex)"
                class="w-full px-3 py-2 border-2 border-dashed border-gray-300 text-gray-600 rounded-lg hover:border-blue-500 hover:text-blue-600 transition-colors flex items-center justify-center space-x-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span class="text-sm">Adaugă interval</span>
              </button>
            </div>

            <!-- Inactive Day Message -->
            <div v-else class="text-center py-8">
              <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
              </svg>
              <p class="text-sm text-gray-500">Zi inactivă</p>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 pt-6 border-t border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Acțiuni rapide</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <button
              @click="setBusinessHours"
              class="flex items-center justify-center px-4 py-3 bg-blue-50 text-blue-700 rounded-xl hover:bg-blue-100 transition-colors"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Program standard (9-17)
            </button>
            <button
              @click="setEveningHours"
              class="flex items-center justify-center px-4 py-3 bg-purple-50 text-purple-700 rounded-xl hover:bg-purple-100 transition-colors"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
              </svg>
              Program seara (17-21)
            </button>
            <button
              @click="clearAllSlots"
              class="flex items-center justify-center px-4 py-3 bg-red-50 text-red-700 rounded-xl hover:bg-red-100 transition-colors"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
              Șterge tot
            </button>
          </div>
        </div>

        <!-- Booking Rules -->
        <div class="mt-8 pt-6 border-t border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Reguli de rezervare</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="advanceNotice" class="block text-sm font-medium text-gray-700 mb-2">
                Timp minim de preaviz (ore)
              </label>
              <select
                id="advanceNotice"
                v-model="bookingRules.advanceNotice"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="1">1 oră</option>
                <option value="2">2 ore</option>
                <option value="4">4 ore</option>
                <option value="12">12 ore</option>
                <option value="24">24 ore</option>
                <option value="48">48 ore</option>
              </select>
            </div>
            <div>
              <label for="maxAdvance" class="block text-sm font-medium text-gray-700 mb-2">
                Rezervare cu maxim (zile în avans)
              </label>
              <select
                id="maxAdvance"
                v-model="bookingRules.maxAdvanceDays"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="7">7 zile</option>
                <option value="14">14 zile</option>
                <option value="30">30 zile</option>
                <option value="60">60 zile</option>
                <option value="90">90 zile</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary -->
      <div class="mt-8 bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rezumat disponibilitate</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="text-center">
            <p class="text-2xl font-bold text-blue-600">{{ activeDaysCount }}</p>
            <p class="text-sm text-gray-600">Zile active</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold text-green-600">{{ totalHoursPerWeek }}</p>
            <p class="text-sm text-gray-600">Ore/săptămână</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold text-purple-600">{{ averageHoursPerDay }}</p>
            <p class="text-sm text-gray-600">Ore/zi (medie)</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold text-orange-600">{{ totalSlotsCount }}</p>
            <p class="text-sm text-gray-600">Intervale totale</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
