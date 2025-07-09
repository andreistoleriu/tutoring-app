<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
          <h1 class="text-3xl font-bold text-gray-900">Profilul meu</h1>
        </div>
        <p class="text-gray-600">Gestionează informațiile tale personale</p>
      </div>

      <!-- Profile Form -->
      <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50">
        <div class="p-6 border-b border-gray-100">
          <h2 class="text-xl font-bold text-gray-900 flex items-center">
            <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Informații personale
          </h2>
        </div>

        <form @submit.prevent="updateProfile" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- First Name -->
            <div>
              <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                Prenume *
              </label>
              <input
                id="first_name"
                v-model="form.first_name"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                placeholder="Introdu prenumele"
              />
              <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">
                {{ errors.first_name[0] }}
              </p>
            </div>

            <!-- Last Name -->
            <div>
              <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                Nume *
              </label>
              <input
                id="last_name"
                v-model="form.last_name"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                placeholder="Introdu numele"
              />
              <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">
                {{ errors.last_name[0] }}
              </p>
            </div>

            <!-- Email (Read-only) -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                readonly
                class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 text-gray-500 cursor-not-allowed"
                placeholder="Email-ul nu poate fi modificat"
              />
              <p class="mt-1 text-sm text-gray-500">
                Pentru a schimba email-ul, contactează suportul
              </p>
            </div>

            <!-- Phone -->
            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                Telefon
              </label>
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                placeholder="Ex: 0712345678"
              />
              <p v-if="errors.phone" class="mt-1 text-sm text-red-600">
                {{ errors.phone[0] }}
              </p>
              <p class="mt-1 text-sm text-gray-500">
                Format: 0712345678 sau +40712345678
              </p>
            </div>
          </div>

          <!-- Account Info -->
          <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informații cont</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Tip utilizator
                </label>
                <div class="px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl">
                  <span class="text-blue-800 font-medium">Student</span>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Membru din
                </label>
                <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl">
                  <span class="text-gray-700">{{ formatDate(form.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="mt-8 flex items-center justify-between">
            <router-link
              to="/dashboard/student"
              class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
            >
              Anulează
            </router-link>

            <button
              type="submit"
              :disabled="loading"
              class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
            >
              <span v-if="loading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Se salvează...
              </span>
              <span v-else>Salvează modificările</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Success Message -->
      <div
        v-if="showSuccess"
        class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in"
      >
        Profilul a fost actualizat cu succes!
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useStudentStore } from '@/stores/student'

// Store
const studentStore = useStudentStore()

// Reactive data
const loading = ref(false)
const errors = ref({})
const showSuccess = ref(false)

// Form data
const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  created_at: ''
})

// Methods
const loadProfile = async () => {
  try {
    const profileData = await studentStore.getProfile()

    // Update form with profile data
    Object.assign(form, profileData.user)
  } catch (error) {
    console.error('Error loading profile:', error)
  }
}

const updateProfile = async () => {
  loading.value = true
  errors.value = {}

  try {
    await studentStore.updateProfile({
      first_name: form.first_name,
      last_name: form.last_name,
      phone: form.phone
    })

    // Show success message
    showSuccess.value = true
    setTimeout(() => {
      showSuccess.value = false
    }, 3000)

  } catch (error) {
    console.error('Error updating profile:', error)

    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''

  const date = new Date(dateString)
  return date.toLocaleDateString('ro-RO', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  loadProfile()
})
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}
</style>
