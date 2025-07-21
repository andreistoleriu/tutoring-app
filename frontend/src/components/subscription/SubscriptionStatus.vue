<!-- frontend/src/components/subscription/SubscriptionStatus.vue -->
<template>
  <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
    <!-- Loading State -->
    <div v-if="loading" class="p-6 text-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
      <p class="text-sm text-gray-600">Se încarcă statusul abonamentului...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="p-6">
      <div class="flex items-center text-red-600 mb-4">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span class="font-medium">Eroare la încărcarea abonamentului</span>
      </div>
      <p class="text-sm text-gray-600 mb-4">{{ error }}</p>
      <button
        @click="refreshSubscription"
        class="text-sm bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
      >
        Încearcă din nou
      </button>
    </div>

    <!-- Subscription Content -->
    <div v-else>
      <!-- Header -->
      <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-purple-50 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div :class="getStatusIconClass()">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path v-if="isPremiumUser" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                <path v-else-if="isInTrial" d="M10 2L3 7v11c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V7l-7-5zM10 1l8 6v11c0 1.66-1.34 3-3 3H5c-1.66 0-3-1.34-3-3V7l8-6z"/>
                <path v-else d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">{{ getStatusTitle() }}</h3>
              <p class="text-sm text-gray-600">{{ getStatusSubtitle() }}</p>
            </div>
          </div>

          <!-- Status Badge -->
          <span :class="getStatusBadgeClass()">
            {{ getStatusBadgeText() }}
          </span>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6">
        <!-- Premium User -->
        <div v-if="isPremiumUser" class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">Plan activ</span>
            <span class="text-sm text-green-600 font-semibold">{{ subscription.plan_name }}</span>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">Valabil până la</span>
            <span class="text-sm text-gray-900">{{ formatDate(subscription.expires_at) }}</span>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">Zile rămase</span>
            <span class="text-sm font-semibold" :class="getDaysRemainingClass()">
              {{ daysRemaining }} {{ daysRemaining === 1 ? 'zi' : 'zile' }}
            </span>
          </div>

          <!-- Premium Benefits -->
          <div class="mt-6">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Beneficii Premium</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="flex items-center text-sm text-green-600">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Fără reclame
              </div>
              <div class="flex items-center text-sm text-green-600">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Suport prioritar
              </div>
              <div class="flex items-center text-sm text-green-600">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Funcții avansate
              </div>
              <div class="flex items-center text-sm text-green-600">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Mesagerie nelimitată
              </div>
            </div>
          </div>

          <!-- Renewal Notice -->
          <div v-if="daysRemaining <= 7" class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-start">
              <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
              </svg>
              <div>
                <h5 class="text-sm font-medium text-amber-800">Abonamentul expiră curând</h5>
                <p class="text-sm text-amber-700 mt-1">
                  Pentru a continua să beneficiezi de toate avantajele Premium, reînnoiește-ți abonamentul.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Trial User -->
        <div v-else-if="isInTrial" class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">Trial gratuit</span>
            <span class="text-sm text-yellow-600 font-semibold">{{ trialDaysRemaining }} zile rămase</span>
          </div>

          <!-- Trial Progress Bar -->
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="bg-gradient-to-r from-yellow-400 to-orange-500 h-2 rounded-full transition-all duration-300"
              :style="{ width: `${getTrialProgress()}%` }"
            ></div>
          </div>

          <div class="text-xs text-gray-500 text-center">
            {{ getTrialProgress() }}% din trial utilizat
          </div>

          <!-- Trial Benefits -->
          <div class="mt-6">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Ce includem în trial</h4>
            <div class="space-y-2">
              <div class="flex items-center text-sm text-gray-600">
                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Acces complet la platformă
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Conectare cu tutori/studenți
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <svg class="w-4 h-4 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                </svg>
                Reclame afișate
              </div>
            </div>
          </div>

          <!-- Upgrade CTA -->
          <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="text-center">
              <h5 class="text-sm font-semibold text-blue-900 mb-2">Upgrade la Premium</h5>
              <p class="text-sm text-blue-700 mb-4">
                Doar 3.49 EUR/lună pentru experiență fără reclame și funcții premium
              </p>
              <button
                @click="$emit('upgrade')"
                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:from-blue-700 hover:to-purple-700 transition-all duration-200 transform hover:scale-105"
                :disabled="upgrading"
              >
                <span v-if="!upgrading">Upgrade acum</span>
                <span v-else class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Se procesează...
                </span>
              </button>
            </div>
          </div>
        </div>

        <!-- Expired User -->
        <div v-else class="space-y-4">
          <div class="text-center p-6 bg-red-50 border border-red-200 rounded-lg">
            <svg class="w-12 h-12 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5l-6.928-7.5a2 2 0 00-2.928 0L3.804 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <h4 class="text-lg font-semibold text-red-900 mb-2">Trial-ul a expirat</h4>
            <p class="text-sm text-red-700 mb-4">
              Pentru a continua să folosești platforma, te rugăm să îți upgrade-ezi contul la Premium.
            </p>

            <!-- Features lost -->
            <div class="text-left mb-6">
              <h5 class="text-sm font-medium text-red-800 mb-3 text-center">Ce nu mai poți face:</h5>
              <div class="space-y-2">
                <div class="flex items-center text-sm text-red-600">
                  <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                  </svg>
                  Rezervare de lecții
                </div>
                <div class="flex items-center text-sm text-red-600">
                  <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                  </svg>
                  Mesagerie cu tutorii
                </div>
                <div class="flex items-center text-sm text-red-600">
                  <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                  </svg>
                  Funcții avansate
                </div>
              </div>
            </div>

            <button
              @click="$emit('upgrade')"
              class="w-full bg-gradient-to-r from-red-600 to-pink-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-red-700 hover:to-pink-700 transition-all duration-200 transform hover:scale-105"
              :disabled="upgrading"
            >
              <span v-if="!upgrading">Reactivează contul - 3.49 EUR/lună</span>
              <span v-else class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Se procesează...
              </span>
            </button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="subscription" class="mt-6 pt-6 border-t border-gray-200">
          <div class="flex flex-col sm:flex-row gap-3">
            <button
              @click="refreshSubscription"
              class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors"
              :disabled="loading"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              Actualizează
            </button>

            <button
              v-if="isPremiumUser && !hasExpired"
              @click="$emit('cancel')"
              class="flex-1 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-100 transition-colors border border-red-200"
              :disabled="canceling"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              {{ canceling ? 'Se anulează...' : 'Anulează' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useSubscriptionStore } from '@/stores/subscription'

const props = defineProps({
  upgrading: {
    type: Boolean,
    default: false
  },
  canceling: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['upgrade', 'cancel', 'refresh'])

const subscriptionStore = useSubscriptionStore()

// Computed properties
const subscription = computed(() => subscriptionStore.subscription)
const loading = computed(() => subscriptionStore.loading)
const error = computed(() => subscriptionStore.error)
const isPremiumUser = computed(() => subscriptionStore.isPremiumUser)
const isInTrial = computed(() => subscriptionStore.isInTrial)
const daysRemaining = computed(() => subscriptionStore.daysRemaining)
const trialDaysRemaining = computed(() => subscriptionStore.trialDaysRemaining)
const hasExpired = computed(() => subscriptionStore.hasExpired)

// Methods
const getStatusTitle = () => {
  if (isPremiumUser.value) return 'Premium Activ'
  if (isInTrial.value) return 'Trial Gratuit'
  return 'Abonament Expirat'
}

const getStatusSubtitle = () => {
  if (isPremiumUser.value) return 'Beneficiezi de toate funcțiile premium'
  if (isInTrial.value) return 'Testezi gratuit toate funcțiile'
  return 'Reactivează-ți contul pentru acces complet'
}

const getStatusIconClass = () => {
  const baseClass = 'w-10 h-10 rounded-full flex items-center justify-center'
  if (isPremiumUser.value) return `${baseClass} bg-green-100 text-green-600`
  if (isInTrial.value) return `${baseClass} bg-yellow-100 text-yellow-600`
  return `${baseClass} bg-red-100 text-red-600`
}

const getStatusBadgeClass = () => {
  const baseClass = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
  if (isPremiumUser.value) return `${baseClass} bg-green-100 text-green-800`
  if (isInTrial.value) return `${baseClass} bg-yellow-100 text-yellow-800`
  return `${baseClass} bg-red-100 text-red-800`
}

const getStatusBadgeText = () => {
  if (isPremiumUser.value) return 'Premium'
  if (isInTrial.value) return 'Trial'
  return 'Expirat'
}

const getDaysRemainingClass = () => {
  if (daysRemaining.value <= 3) return 'text-red-600'
  if (daysRemaining.value <= 7) return 'text-yellow-600'
  return 'text-green-600'
}

const getTrialProgress = () => {
  const totalDays = subscription.value?.trial_total_days || 14
  const used = totalDays - trialDaysRemaining.value
  return Math.round((used / totalDays) * 100)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('ro-RO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const refreshSubscription = () => {
  emit('refresh')
  subscriptionStore.getSubscription()
}
</script>

<style scoped>
/* Gradient animations */
.bg-gradient-to-r {
  background-size: 200% 200%;
  animation: gradientShift 3s ease infinite;
}

@keyframes gradientShift {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

/* Loading animation */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Mobile optimizations */
@media (max-width: 640px) {
  .sm\:flex-row {
    flex-direction: column;
  }

  .sm\:grid-cols-2 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }
}
</style>
