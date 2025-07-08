<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Profilul meu</h1>
            <p class="text-gray-600 mt-1">Actualizează informațiile tale pentru a atrage mai mulți studenți</p>
          </div>
          <router-link
            :to="{ name: 'tutor-dashboard' }"
            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
          >
            ← Înapoi la dashboard
          </router-link>
        </div>
      </div>

      <!-- Profile Form -->
      <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-8">
        <form @submit.prevent="saveProfile" class="space-y-8">
          <!-- Personal Information -->
          <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Informații personale</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Profile Photo -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-3">Fotografia de profil</label>
                <div class="flex items-center space-x-6">
                  <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white text-2xl font-semibold">
                    {{ getInitials() }}
                  </div>
                  <div>
                    <input
                      type="file"
                      ref="photoInput"
                      @change="handlePhotoUpload"
                      accept="image/*"
                      class="hidden"
                    >
                    <button
                      type="button"
                      @click="$refs.photoInput.click()"
                      class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                      Încarcă fotografie
                    </button>
                    <p class="text-sm text-gray-500 mt-1">JPG, PNG sau GIF. Maxim 2MB.</p>
                  </div>
                </div>
              </div>

              <!-- First Name -->
              <div>
                <label for="firstName" class="block text-sm font-medium text-gray-700 mb-2">Prenume</label>
                <input
                  id="firstName"
                  v-model="profile.firstName"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                >
              </div>

              <!-- Last Name -->
              <div>
                <label for="lastName" class="block text-sm font-medium text-gray-700 mb-2">Nume</label>
                <input
                  id="lastName"
                  v-model="profile.lastName"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                >
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input
                  id="email"
                  v-model="profile.email"
                  type="email"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50"
                  readonly
                >
                <p class="text-sm text-gray-500 mt-1">Email-ul nu poate fi modificat</p>
              </div>

              <!-- Phone -->
              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                <input
                  id="phone"
                  v-model="profile.phone"
                  type="tel"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
              </div>
            </div>
          </div>

          <!-- Professional Information -->
          <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Informații profesionale</h2>
            <div class="space-y-6">
              <!-- Bio -->
              <div>
                <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Descriere</label>
                <textarea
                  id="bio"
                  v-model="profile.bio"
                  rows="4"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Descrie-te pe scurt și explică de ce studenții ar trebui să te aleagă..."
                ></textarea>
                <p class="text-sm text-gray-500 mt-1">{{ profile.bio.length }}/500 caractere</p>
              </div>

              <!-- Subjects -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Materii predate</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                  <div
                    v-for="subject in availableSubjects"
                    :key="subject.id"
                    class="flex items-center space-x-3"
                  >
                    <input
                      :id="`subject-${subject.id}`"
                      v-model="profile.selectedSubjects"
                      :value="subject.id"
                      type="checkbox"
                      class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    >
                    <label :for="`subject-${subject.id}`" class="text-sm text-gray-700">
                      {{ subject.name }}
                    </label>
                  </div>
                </div>
              </div>

              <!-- Experience -->
              <div>
                <label for="experience" class="block text-sm font-medium text-gray-700 mb-2">Experiență</label>
                <textarea
                  id="experience"
                  v-model="profile.experience"
                  rows="3"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Descrie experiența ta în predare..."
                ></textarea>
              </div>

              <!-- Education -->
              <div>
                <label for="education" class="block text-sm font-medium text-gray-700 mb-2">Educație</label>
                <textarea
                  id="education"
                  v-model="profile.education"
                  rows="3"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Descrie studiile tale..."
                ></textarea>
              </div>

              <!-- Hourly Rate -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="hourlyRate" class="block text-sm font-medium text-gray-700 mb-2">Preț pe oră (RON)</label>
                  <input
                    id="hourlyRate"
                    v-model.number="profile.hourlyRate"
                    type="number"
                    min="20"
                    max="500"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                  >
                </div>

                <!-- Location -->
                <div>
                  <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Locația</label>
                  <select
                    id="location"
                    v-model="profile.locationId"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                  >
                    <option value="">Selectează orașul</option>
                    <option
                      v-for="location in availableLocations"
                      :key="location.id"
                      :value="location.id"
                    >
                      {{ location.city }}, {{ location.county }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Lesson Types -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Tipuri de lecții oferite</label>
                <div class="space-y-3">
                  <div class="flex items-center space-x-3">
                    <input
                      id="offersOnline"
                      v-model="profile.offersOnline"
                      type="checkbox"
                      class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    >
                    <label for="offersOnline" class="text-sm text-gray-700">
                      Lecții online (Zoom, Google Meet, etc.)
                    </label>
                  </div>
                  <div class="flex items-center space-x-3">
                    <input
                      id="offersInPerson"
                      v-model="profile.offersInPerson"
                      type="checkbox"
                      class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    >
                    <label for="offersInPerson" class="text-sm text-gray-700">
                      Lecții față în față (la domiciliu sau la bibliotecă)
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Save Button -->
          <div class="flex justify-end space-x-4">
            <router-link
              :to="{ name: 'tutor-dashboard' }"
              class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
            >
              Anulează
            </router-link>
            <button
              type="submit"
              :disabled="saving"
              class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50"
            >
              {{ saving ? 'Se salvează...' : 'Salvează modificările' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTutorStore } from '@/stores/tutor'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()
const tutorStore = useTutorStore()

// Reactive data
const saving = ref(false)
const loading = ref(false)
const profile = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  bio: '',
  experience: '',
  education: '',
  hourlyRate: 50,
  locationId: '',
  selectedSubjects: [],
  offersOnline: true,
  offersInPerson: true,
  photo: null
})

// Load from API instead of hardcoded
const availableSubjects = ref([])
const availableLocations = ref([])

// Methods
const getInitials = () => {
  if (!profile.value.firstName || !profile.value.lastName) {
    return authStore.user?.first_name?.[0] || 'T'
  }
  return `${profile.value.firstName[0]}${profile.value.lastName[0]}`
}

const handlePhotoUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      alert('Fișierul este prea mare. Dimensiunea maximă este 2MB.')
      return
    }
    profile.value.photo = file
  }
}

// Load real data from API
const loadSubjects = async () => {
  try {
    console.log('🔄 Loading subjects from API...')
    const response = await api.get('/subjects')
    availableSubjects.value = response.data.subjects
    console.log('✅ Subjects loaded:', availableSubjects.value.length)
  } catch (error) {
    console.error('❌ Error loading subjects:', error)
    alert('Eroare la încărcarea materiilor.')
  }
}

const loadLocations = async () => {
  try {
    console.log('🔄 Loading locations from API...')
    const response = await api.get('/locations')
    // Convert the grouped format to flat array for form
    availableLocations.value = response.data.all_locations || []
    console.log('✅ Locations loaded:', availableLocations.value.length)
  } catch (error) {
    console.error('❌ Error loading locations:', error)
    alert('Eroare la încărcarea locațiilor.')
  }
}

const loadProfile = async () => {
  loading.value = true
  try {
    console.log('🔄 Loading tutor profile from API...')

    // Get current user data
    const user = authStore.user
    if (user) {
      profile.value.firstName = user.first_name || ''
      profile.value.lastName = user.last_name || ''
      profile.value.email = user.email || ''
      profile.value.phone = user.phone || ''
    }

    // Get tutor-specific data from dashboard (which already loads tutor info)
    if (tutorStore.dashboard?.tutor) {
      const tutorData = tutorStore.dashboard.tutor
      profile.value.bio = tutorData.bio || ''
      profile.value.experience = tutorData.experience || ''
      profile.value.education = tutorData.education || ''
      profile.value.hourlyRate = tutorData.hourly_rate || 50
      profile.value.offersOnline = tutorData.offers_online !== false
      profile.value.offersInPerson = tutorData.offers_in_person !== false

      // Get selected subjects (assuming subjects is array of subject objects)
      if (tutorData.subjects && Array.isArray(tutorData.subjects)) {
        // If subjects come as names, we'll need to match them to IDs
        profile.value.selectedSubjects = tutorData.subjects.map(subjectName => {
          const subject = availableSubjects.value.find(s => s.name === subjectName)
          return subject ? subject.id : null
        }).filter(id => id !== null)
      }

      // Get location (assuming location comes as city name)
      if (tutorData.location) {
        const location = availableLocations.value.find(l => l.city === tutorData.location)
        profile.value.locationId = location ? location.id : ''
      }
    }

    console.log('✅ Profile loaded successfully')
  } catch (error) {
    console.error('❌ Error loading profile:', error)
    alert('Eroare la încărcarea profilului.')
  } finally {
    loading.value = false
  }
}

const saveProfile = async () => {
  if (profile.value.selectedSubjects.length === 0) {
    alert('Te rugăm să selectezi cel puțin o materie.')
    return
  }

  if (!profile.value.offersOnline && !profile.value.offersInPerson) {
    alert('Te rugăm să selectezi cel puțin un tip de lecție.')
    return
  }

  saving.value = true

  try {
    console.log('🔄 Saving profile...')

    // Prepare data for API
    const profileData = {
      first_name: profile.value.firstName,
      last_name: profile.value.lastName,
      email: profile.value.email,
      phone: profile.value.phone,
      bio: profile.value.bio,
      experience: profile.value.experience,
      education: profile.value.education,
      hourly_rate: profile.value.hourlyRate,
      location_id: profile.value.locationId,
      subjects: profile.value.selectedSubjects, // Array of subject IDs
      offers_online: profile.value.offersOnline,
      offers_in_person: profile.value.offersInPerson,
    }

    // Include photo if uploaded
    if (profile.value.photo) {
      const formData = new FormData()
      Object.keys(profileData).forEach(key => {
        if (Array.isArray(profileData[key])) {
          profileData[key].forEach(item => formData.append(`${key}[]`, item))
        } else {
          formData.append(key, profileData[key])
        }
      })
      formData.append('photo', profile.value.photo)

      // Use FormData for file upload
      await tutorStore.updateProfile(formData)
    } else {
      // Regular JSON data
      await tutorStore.updateProfile(profileData)
    }

    console.log('✅ Profile saved successfully')
    alert('Profilul a fost salvat cu succes!')
    router.push({ name: 'tutor-dashboard' })
  } catch (error) {
    console.error('❌ Error saving profile:', error)
    alert('A apărut o eroare la salvarea profilului.')
  } finally {
    saving.value = false
  }
}

// Lifecycle
onMounted(async () => {
  console.log('🔄 Initializing profile page...')

  // Load required data first
  await Promise.all([
    loadSubjects(),
    loadLocations()
  ])

  // Then load profile (needs subjects/locations to match data)
  await loadProfile()

  console.log('✅ Profile page initialized')
})
</script>
