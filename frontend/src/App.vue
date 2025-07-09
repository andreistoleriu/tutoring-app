<template>
  <div id="app">
    <!-- Navigation Header -->
    <header class="bg-white/80 backdrop-blur-xl border-b border-gray-200/50 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <router-link to="/" class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
            </div>
            <h1 class="text-2xl font-bold text-gradient">TutoringApp</h1>
          </router-link>

          <!-- Navigation -->
          <nav class="hidden md:flex items-center space-x-6">
            <router-link to="/tutors" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Tutori</router-link>
            <a href="#materii" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Materii</a>
            <a href="#cum-functioneaza" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Cum funcționează</a>
            <a href="#contact" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Contact</a>
          </nav>

          <!-- Auth Section -->
          <div class="flex items-center space-x-3">
            <!-- Not Authenticated -->
            <template v-if="!authStore.isAuthenticated">
              <button
                @click="showLoginModal = true"
                class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium transition-colors"
              >
                Conectare
              </button>
              <button
                @click="showRegisterModal = true"
                class="px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-full hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
              >
                Înregistrare
              </button>
            </template>

            <!-- Authenticated -->
            <template v-else>
              <!-- Dashboard Link -->
              <router-link
                :to="authStore.isTutor ? '/dashboard/tutor' : '/dashboard/student'"
                class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium transition-colors"
              >
                Dashboard
              </router-link>

              <!-- User Menu -->
              <div class="relative" ref="userMenuRef">
                <button
                  @click="showUserMenu = !showUserMenu"
                  class="flex items-center space-x-3 p-2 rounded-full hover:bg-gray-100 transition-colors"
                >
                  <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                    {{ authStore.userInitials }}
                  </div>
                  <span class="hidden md:block font-medium text-gray-700">{{ authStore.userName }}</span>
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>

                <!-- User Dropdown -->
                <div
                  v-if="showUserMenu"
                  class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50"
                >
                  <router-link
                    :to="authStore.isTutor ? '/dashboard/tutor' : '/dashboard/student'"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors"
                    @click="showUserMenu = false"
                  >
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                      </svg>
                      <span>Dashboard</span>
                    </div>
                  </router-link>

                  <router-link
                    to="/profile"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors"
                    @click="showUserMenu = false"
                  >
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                      <span>Profil</span>
                    </div>
                  </router-link>

                  <div class="border-t border-gray-200 my-2"></div>

                  <button
                    @click="handleLogout"
                    class="block w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 transition-colors"
                  >
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                      </svg>
                      <span>Deconectare</span>
                    </div>
                  </button>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <router-view />

    <!-- Auth Modals -->
    <LoginModal
      :is-open="showLoginModal"
      @close="showLoginModal = false"
      @switch-to-register="switchToRegister"
    />

    <RegisterModal
      :is-open="showRegisterModal"
      @close="showRegisterModal = false"
      @switch-to-login="switchToLogin"
    />
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LoginModal from './components/auth/LoginModal.vue'
import RegisterModal from './components/auth/RegisterModal.vue'


export default {
  name: 'App',
  components: {
    LoginModal,
    RegisterModal
  },
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()

    const showLoginModal = ref(false)
    const showRegisterModal = ref(false)
    const showUserMenu = ref(false)
    const userMenuRef = ref(null)

    const switchToRegister = () => {
      showLoginModal.value = false
      showRegisterModal.value = true
    }

    const switchToLogin = () => {
      showRegisterModal.value = false
      showLoginModal.value = true
    }

    const handleLogout = async () => {
      await authStore.logout()
      showUserMenu.value = false
      router.push('/')
    }

    // Close user menu when clicking outside
    const handleClickOutside = (event) => {
      if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
        showUserMenu.value = false
      }
    }


  onMounted(async () => {
    // Initialize auth state
    await authStore.initAuth()

    // Add click outside listener
    document.addEventListener('click', handleClickOutside)

    // ADD THESE: Listen for modal events from child components
    window.addEventListener('open-login-modal', () => {
      showLoginModal.value = true
    })

    window.addEventListener('open-register-modal', () => {
      showRegisterModal.value = true
    })
  })

  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)

    // ADD THESE: Cleanup event listeners
    window.removeEventListener('open-login-modal', () => {
      showLoginModal.value = true
    })

    window.removeEventListener('open-register-modal', () => {
      showRegisterModal.value = true
    })
  })

    return {
      authStore,
      showLoginModal,
      showRegisterModal,
      showUserMenu,
      userMenuRef,
      switchToRegister,
      switchToLogin,
      handleLogout
    }
  }
}
</script>
