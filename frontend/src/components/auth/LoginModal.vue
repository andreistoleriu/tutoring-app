<template>
  <!-- Modal Backdrop -->
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    @click="closeModal"
  >
    <!-- Modal Content -->
    <div
      class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all duration-300"
      @click.stop
    >
      <!-- Header -->
      <div class="px-8 pt-8 pb-4">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-900">Conectare</h2>
          <button
            @click="closeModal"
            class="p-2 hover:bg-gray-100 rounded-full transition-colors"
          >
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <p class="text-gray-600 mb-6">Conectează-te pentru a accesa toate funcționalitățile platformei.</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="px-8 pb-8">
        <!-- Email -->
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
            placeholder="adresa@email.com"
          >
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
        </div>

        <!-- Password -->
        <div class="mb-6">
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
              placeholder="Introdu parola"
            >
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-3 flex items-center"
            >
              <svg v-if="showPassword" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
              <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.05 8.05a10.05 10.05 0 013.821-2.925m4.242 4.242L19.95 13.95A10.055 10.055 0 0012 19c-.414 0-.821-.025-1.22-.075m3.341-6.096a3 3 0 00-4.243-4.243m0 0L4.929 4.929"></path>
              </svg>
            </button>
          </div>
          <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
        </div>

        <!-- Remember & Forgot Password -->
        <div class="flex items-center justify-between mb-6">
          <label class="flex items-center">
            <input
              v-model="form.remember"
              type="checkbox"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            >
            <span class="ml-2 text-sm text-gray-600">Ține-mă minte</span>
          </label>
          <button type="button" class="text-sm text-blue-600 hover:text-blue-800 transition-colors">
            Ai uitat parola?
          </button>
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
            Se conectează...
          </span>
          <span v-else>Conectare</span>
        </button>

        <!-- Divider -->
        <div class="my-6 flex items-center">
          <div class="border-t border-gray-300 flex-1"></div>
          <span class="px-4 text-sm text-gray-500">sau</span>
          <div class="border-t border-gray-300 flex-1"></div>
        </div>

        <!-- Register Link -->
        <div class="text-center">
          <p class="text-gray-600">
            Nu ai cont?
            <button
              type="button"
              @click="switchToRegister"
              class="text-blue-600 font-semibold hover:text-blue-800 transition-colors"
            >
              Înregistrează-te
            </button>
          </p>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

export default {
  name: 'LoginModal',
  props: {
    isOpen: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'switch-to-register'],
  setup(props, { emit }) {
    const authStore = useAuthStore()
    const router = useRouter()

    const loading = ref(false)
    const showPassword = ref(false)

    const form = reactive({
      email: '',
      password: '',
      remember: false
    })

    const errors = reactive({
      email: '',
      password: '',
      general: ''
    })

    const closeModal = () => {
      emit('close')
      resetForm()
    }

    const switchToRegister = () => {
      emit('switch-to-register')
    }

    const resetForm = () => {
      form.email = ''
      form.password = ''
      form.remember = false
      Object.keys(errors).forEach(key => errors[key] = '')
    }

    const handleLogin = async () => {
      loading.value = true
      Object.keys(errors).forEach(key => errors[key] = '')

      try {
        await authStore.login(form)
        closeModal()

        // Redirect based on user type
        if (authStore.isTutor) {
          router.push('/dashboard/tutor')
        } else {
          router.push('/dashboard/student')
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

    return {
      form,
      errors,
      loading,
      showPassword,
      closeModal,
      switchToRegister,
      handleLogin
    }
  }
}
</script>
