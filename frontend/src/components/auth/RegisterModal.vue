<template>
  <!-- Modal Backdrop -->
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    @click="closeModal"
  >
    <!-- Modal Content -->
    <div
      class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all duration-300 max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Header -->
      <div class="px-8 pt-8 pb-6 sticky top-0 bg-white rounded-t-2xl z-10 border-b border-gray-100">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-bold text-gray-900">Înregistrare</h2>
          <button
            @click="closeModal"
            class="p-2 hover:bg-gray-100 rounded-full transition-colors"
          >
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <p class="text-gray-600 mb-2">Creează-ți un cont pentru a începe să înveți sau să predai.</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleRegister" class="px-8 pb-8 pt-4">
        <!-- User Type Selection - Fixed Layout -->
        <div class="mb-6 mt-4">
          <label class="block text-sm font-medium text-gray-700 mb-4">
            Tip cont
          </label>
          <div class="grid grid-cols-2 gap-4">
            <!-- Student Card -->
            <label class="relative cursor-pointer">
              <input
                v-model="form.user_type"
                type="radio"
                value="student"
                class="sr-only"
              >
              <div
                class="p-4 border-2 rounded-xl text-center transition-all duration-200 min-h-[120px] flex flex-col justify-center"
                :class="form.user_type === 'student' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
              >
                <svg class="w-8 h-8 mx-auto mb-3" :class="form.user_type === 'student' ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <p class="font-semibold text-base mb-1" :class="form.user_type === 'student' ? 'text-blue-900' : 'text-gray-700'">Student</p>
                <p class="text-xs text-gray-500 leading-tight">Vreau să învăț</p>
              </div>
            </label>

            <!-- Tutor Card - Fixed Layout -->
            <label class="relative cursor-pointer">
              <input
                v-model="form.user_type"
                type="radio"
                value="tutor"
                class="sr-only"
              >
              <div
                class="p-4 border-2 rounded-xl text-center transition-all duration-200 min-h-[120px] flex flex-col justify-center"
                :class="form.user_type === 'tutor' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300'"
              >
                <svg class="w-8 h-8 mx-auto mb-3" :class="form.user_type === 'tutor' ? 'text-purple-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H5m14 0a2 2 0 002-2v-2a2 2 0 00-2-2h-2m-2 0H5a2 2 0 00-2 2v2a2 2 0 002 2h2"></path>
                </svg>
                <p class="font-semibold text-base mb-1" :class="form.user_type === 'tutor' ? 'text-purple-900' : 'text-gray-700'">Tutor</p>
                <p class="text-xs text-gray-500 leading-tight">Vreau să predau</p>
              </div>
            </label>
          </div>
          <p v-if="errors.user_type" class="mt-2 text-sm text-red-600">{{ errors.user_type[0] }}</p>
        </div>

        <!-- Name Fields -->
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
              Prenume
            </label>
            <input
              id="first_name"
              v-model="form.first_name"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
              :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.first_name }"
              placeholder="Prenume"
            >
            <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ errors.first_name[0] }}</p>
          </div>

          <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
              Nume
            </label>
            <input
              id="last_name"
              v-model="form.last_name"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
              :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.last_name }"
              placeholder="Nume"
            >
            <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">{{ errors.last_name[0] }}</p>
          </div>
        </div>

        <!-- Email Field -->
        <div class="mb-4">
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
            Email
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
            :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.email }"
            placeholder="student@test.com"
          >
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
        </div>

        <!-- Phone Field -->
        <div class="mb-4">
          <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
            Telefon <span class="text-gray-400">(opțional)</span>
          </label>
          <input
            id="phone"
            v-model="form.phone"
            type="tel"
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
            :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.phone }"
            placeholder="0712345678"
          >
          <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone[0] }}</p>
        </div>

        <!-- Location Field -->
        <div class="mb-4">
          <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">
            Locația
          </label>
          <select
            id="location_id"
            v-model="form.location_id"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
            :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.location_id }"
          >
            <option value="">Alege orașul</option>
            <option v-for="location in locations" :key="location.id" :value="location.id">
              {{ location.name }}
            </option>
          </select>
          <p v-if="errors.location_id" class="mt-1 text-sm text-red-600">{{ errors.location_id[0] }}</p>
        </div>

        <!-- Password Field -->
        <div class="mb-4">
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Parolă
          </label>
          <div class="relative">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
              :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.password }"
              placeholder="••••••••"
            >
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
            >
              <svg v-if="showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
              <svg v-else class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
              </svg>
            </button>
          </div>
          <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
        </div>

        <!-- Confirm Password Field -->
        <div class="mb-6">
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
            Confirmă parola
          </label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
            placeholder="••••••••"
          >
        </div>

        <!-- Terms and Privacy Checkboxes -->
        <div class="mb-6 space-y-3">
          <label class="flex items-start">
            <input
              v-model="form.terms_accepted"
              type="checkbox"
              required
              class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-blue-500"
            >
            <span class="ml-3 text-sm text-gray-600">
              Accept <a href="#" class="text-blue-600 hover:text-blue-800">Termenii și condițiile</a>
            </span>
          </label>

          <label class="flex items-start">
            <input
              v-model="form.privacy_accepted"
              type="checkbox"
              required
              class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-blue-500"
            >
            <span class="ml-3 text-sm text-gray-600">
              Accept <a href="#" class="text-blue-600 hover:text-blue-800">Politica de confidențialitate</a>
            </span>
          </label>
        </div>

        <!-- Error Message -->
        <div v-if="errors.general" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-600">{{ errors.general }}</p>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
        >
          <span v-if="loading" class="flex items-center justify-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Se înregistrează...
          </span>
          <span v-else>
            {{ form.user_type === 'tutor' ? 'Devino tutor' : 'Creează cont' }}
          </span>
        </button>

        <!-- Divider -->
        <div class="my-6 flex items-center">
          <div class="border-t border-gray-300 flex-1"></div>
          <span class="px-4 text-sm text-gray-500">sau</span>
          <div class="border-t border-gray-300 flex-1"></div>
        </div>

        <!-- Login Link -->
        <div class="text-center">
          <p class="text-gray-600">
            Ai deja cont?
            <button
              type="button"
              @click="switchToLogin"
              class="text-blue-600 font-semibold hover:text-blue-800 transition-colors"
            >
              Conectează-te
            </button>
          </p>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import api from '@/services/api'

export default {
  name: 'RegisterModal',
  props: {
    isOpen: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'switch-to-login'],
  setup(props, { emit }) {
    const authStore = useAuthStore()
    const router = useRouter()

    const loading = ref(false)
    const showPassword = ref(false)
    const locations = ref([])

    const form = reactive({
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      password: '',
      password_confirmation: '',
      user_type: 'student',
      location_id: '',
      terms_accepted: false,
      privacy_accepted: false
    })

    const errors = reactive({
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      password: '',
      location_id: '',
      user_type: '',
      general: ''
    })

    const loadLocations = async () => {
      try {
        const response = await api.get('locations')
        locations.value = response.data.locations
      } catch (error) {
        console.error('Error loading locations:', error)
        // Set fallback locations if API fails
        locations.value = [
          { id: 1, name: 'București' },
          { id: 2, name: 'Cluj-Napoca' },
          { id: 3, name: 'Timișoara' },
          { id: 4, name: 'Iași' },
          { id: 5, name: 'Constanța' }
        ]
      }
    }

    const closeModal = () => {
      emit('close')
      resetForm()
    }

    const switchToLogin = () => {
      emit('switch-to-login')
    }

    const resetForm = () => {
      Object.keys(form).forEach(key => {
        if (typeof form[key] === 'boolean') {
          form[key] = false
        } else {
          form[key] = ''
        }
      })
      form.user_type = 'student'
      Object.keys(errors).forEach(key => errors[key] = '')
    }

    const handleRegister = async () => {
      loading.value = true
      Object.keys(errors).forEach(key => errors[key] = '')

      try {
        await authStore.register(form)
        closeModal()

        // Redirect based on user type
        if (authStore.isTutor) {
          router.push('dashboard/tutor')
        } else {
          router.push('dashboard/student')
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors)
        } else {
          errors.general = error.response?.data?.message || 'A apărut o eroare. Te rugăm să încerci din nou.'
        }
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      loadLocations()
    })

    return {
      loading,
      showPassword,
      locations,
      form,
      errors,
      closeModal,
      switchToLogin,
      handleRegister
    }
  }
}
</script>
