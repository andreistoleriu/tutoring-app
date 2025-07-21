<!-- frontend/src/components/subscription/SubscriptionModal.vue -->
<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click="handleBackdropClick">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity"></div>

      <!-- Modal -->
      <div class="flex min-h-full items-center justify-center p-4">
        <div
          class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl transform transition-all"
          @click.stop
        >
          <!-- Header -->
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-bold text-gray-900">
                {{ modalTitle }}
              </h2>
              <button
                @click="closeModal"
                class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Content -->
          <div class="px-6 py-6">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-8">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
              <p class="text-gray-600">{{ loadingMessage }}</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="text-center py-6">
              <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Eroare</h3>
              <p class="text-gray-600 mb-4">{{ error }}</p>
              <button
                @click="retry"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
              >
                Încearcă din nou
              </button>
            </div>

            <!-- Current Subscription Status -->
            <div v-else-if="subscription" class="space-y-6">
              <!-- Current Plan Display -->
              <div class="bg-gray-50 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                  <h3 class="font-semibold text-gray-900">Plan actual</h3>
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getStatusBadgeClass()"
                  >
                    {{ subscription.plan_name || getStatusText() }}
                  </span>
                </div>

                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="font-medium" :class="getStatusTextClass()">
                      {{ getStatusDisplay() }}
                    </span>
                  </div>

                  <div v-if="subscription.is_in_trial" class="flex justify-between">
                    <span class="text-gray-600">Zile rămase în trial:</span>
                    <span class="font-medium text-yellow-600">
                      {{ subscription.trial_days_remaining || 0 }}
                    </span>
                  </div>

                  <div v-else-if="subscription.plan_type === 'premium'" class="flex justify-between">
                    <span class="text-gray-600">Zile rămase:</span>
                    <span class="font-medium text-green-600">
                      {{ subscription.days_remaining || 0 }}
                    </span>
                  </div>

                  <div class="flex justify-between">
                    <span class="text-gray-600">Reclame:</span>
                    <span class="font-medium" :class="subscription.shows_ads ? 'text-yellow-600' : 'text-green-600'">
                      {{ subscription.shows_ads ? 'Activate' : 'Dezactivate' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Premium Plan Benefits (if not premium) -->
              <div v-if="!isPremiumUser" class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                  Beneficii Premium
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                  <div class="flex items-center text-sm text-gray-700">
                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Fără reclame
                  </div>
                  <div class="flex items-center text-sm text-gray-700">
                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Suport prioritar
                  </div>
                  <div class="flex items-center text-sm text-gray-700">
                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Funcții avansate
                  </div>
                  <div class="flex items-center text-sm text-gray-700">
                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Mesagerie nelimitată
                  </div>
                </div>

                <div class="text-center">
                  <div class="text-3xl font-bold text-gray-900 mb-1">3.49 EUR</div>
                  <div class="text-sm text-gray-600 mb-4">/lună</div>

                  <button
                    @click="handleUpgrade"
                    :disabled="upgrading"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:transform-none"
                  >
                    <span v-if="!upgrading" class="flex items-center justify-center">
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                      </svg>
                      {{ getUpgradeButtonText() }}
                    </span>
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

              <!-- Premium Management Section -->
              <div v-else class="space-y-4">
                <div class="bg-green-50 rounded-xl p-4">
                  <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <div>
                      <h3 class="font-semibold text-green-900">Premium Activ</h3>
                      <p class="text-sm text-green-700">Beneficiezi de toate funcțiile premium</p>
                    </div>
                  </div>
                </div>

                <!-- Renewal Notice -->
                <div v-if="daysRemaining <= 7 && daysRemaining > 0" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                  <div class="flex items-start">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                      <h4 class="text-sm font-medium text-amber-800">Abonamentul expiră curând</h4>
                      <p class="text-sm text-amber-700 mt-1">
                        Abonamentul tău expiră în {{ daysRemaining }} {{ daysRemaining === 1 ? 'zi' : 'zile' }}.
                        Reînnoiește pentru a continua să beneficiezi de Premium.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Management Actions -->
                <div class="space-y-3">
                  <button
                    @click="handleRenew"
                    :disabled="renewing"
                    class="w-full bg-blue-600 text-white px-4 py-3 rounded-xl hover:bg-blue-700 transition-colors font-medium disabled:opacity-50"
                  >
                    <span v-if="!renewing">🔄 Reînnoiește abonamentul</span>
                    <span v-else>Se procesează...</span>
                  </button>

                  <button
                    @click="showCancelConfirm = true"
                    class="w-full bg-gray-100 text-gray-700 px-4 py-3 rounded-xl hover:bg-gray-200 transition-colors font-medium"
                  >
                    ❌ Anulează abonamentul
                  </button>
                </div>
              </div>

              <!-- Trial Expiration Warning -->
              <div v-if="hasExpired" class="bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-start">
                  <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  <div>
                    <h4 class="text-sm font-medium text-red-800">Trial-ul a expirat</h4>
                    <p class="text-sm text-red-700 mt-1">
                      Pentru a continua să folosești platforma cu acces complet, upgrade-ează la Premium.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Cancel Confirmation -->
            <div v-if="showCancelConfirm" class="space-y-4">
              <div class="text-center py-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5l-6.928-7.5a2 2 0 00-2.928 0L3.804 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                  </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Anulează abonamentul?</h3>
                <p class="text-gray-600 mb-4">
                  Vei pierde accesul la funcțiile Premium și vor fi afișate din nou reclame.
                </p>
              </div>

              <div class="flex space-x-3">
                <button
                  @click="showCancelConfirm = false"
                  class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors"
                >
                  Păstrează Premium
                </button>
                <button
                  @click="handleCancel"
                  :disabled="canceling"
                  class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
                >
                  {{ canceling ? 'Se anulează...' : 'Confirmă anularea' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="px-6 py-4 bg-gray-50 rounded-b-2xl">
            <div class="flex justify-between items-center text-xs text-gray-500">
              <span>Facturare securizată</span>
              <span>Anulare oricând</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useSubscriptionStore } from '@/stores/subscription'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'upgraded', 'canceled', 'renewed'])

// Store
const subscriptionStore = useSubscriptionStore()

// State
const loading = ref(false)
const error = ref(null)
const upgrading = ref(false)
const canceling = ref(false)
const renewing = ref(false)
const showCancelConfirm = ref(false)
const loadingMessage = ref('')

// Computed
const subscription = computed(() => subscriptionStore.subscription)
const isPremiumUser = computed(() => subscriptionStore.isPremiumUser)
const hasExpired = computed(() => subscriptionStore.hasExpired)
const daysRemaining = computed(() => subscriptionStore.daysRemaining)

const modalTitle = computed(() => {
  if (isPremiumUser.value) return 'Gestionează abonamentul'
  if (hasExpired.value) return 'Reactivează contul'
  return 'Upgrade la Premium'
})

// Methods
const closeModal = () => {
  showCancelConfirm.value = false
  emit('close')
}

const handleBackdropClick = (e) => {
  if (e.target === e.currentTarget) {
    closeModal()
  }
}

const getStatusBadgeClass = () => {
  if (isPremiumUser.value) return 'bg-green-100 text-green-800'
  if (subscription.value?.is_in_trial) return 'bg-yellow-100 text-yellow-800'
  return 'bg-red-100 text-red-800'
}

const getStatusTextClass = () => {
  if (isPremiumUser.value) return 'text-green-600'
  if (subscription.value?.is_in_trial) return 'text-yellow-600'
  return 'text-red-600'
}

const getStatusText = () => {
  if (isPremiumUser.value) return 'Premium'
  if (subscription.value?.is_in_trial) return 'Trial Activ'
  return 'Trial Expirat'
}

const getStatusDisplay = () => {
  if (isPremiumUser.value) return 'Premium Activ'
  if (subscription.value?.is_in_trial) return 'În perioada de trial'
  return 'Trial expirat'
}

const getUpgradeButtonText = () => {
  if (hasExpired.value) return 'Reactivează pentru 3.49 EUR/lună'
  return 'Upgrade la Premium'
}

const handleUpgrade = async () => {
  upgrading.value = true
  loadingMessage.value = 'Se procesează upgrade-ul...'
  error.value = null

  try {
    const result = await subscriptionStore.upgradeToPremium()

    if (result.payment_url) {
      // Redirect to payment
      window.open(result.payment_url, '_blank')
      closeModal()
    } else {
      // Direct upgrade success
      emit('upgraded')
      closeModal()
    }
  } catch (err) {
    error.value = err.message || 'Eroare la procesarea upgrade-ului'
  } finally {
    upgrading.value = false
    loadingMessage.value = ''
  }
}

const handleRenew = async () => {
  renewing.value = true
  loadingMessage.value = 'Se reînnoiește abonamentul...'
  error.value = null

  try {
    await subscriptionStore.upgradeToPremium() // Same as upgrade for renewal
    emit('renewed')
    closeModal()
  } catch (err) {
    error.value = err.message || 'Eroare la reînnoirea abonamentului'
  } finally {
    renewing.value = false
    loadingMessage.value = ''
  }
}

const handleCancel = async () => {
  canceling.value = true
  loadingMessage.value = 'Se anulează abonamentul...'
  error.value = null

  try {
    await subscriptionStore.cancelSubscription()
    emit('canceled')
    closeModal()
  } catch (err) {
    error.value = err.message || 'Eroare la anularea abonamentului'
  } finally {
    canceling.value = false
    showCancelConfirm.value = false
    loadingMessage.value = ''
  }
}

const retry = () => {
  error.value = null
  subscriptionStore.getSubscription()
}

// Watch for modal opening
watch(() => props.show, (newValue) => {
  if (newValue) {
    showCancelConfirm.value = false
    error.value = null
    // Refresh subscription data when modal opens
    subscriptionStore.getSubscription()
  }
})
</script>

<style scoped>
/* Ensure backdrop-blur works */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
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

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Button hover effects */
.transform:hover {
  transform: scale(1.05);
}

/* Mobile optimizations */
@media (max-width: 640px) {
  .max-w-lg {
    max-width: calc(100vw - 2rem);
  }
}
</style>
