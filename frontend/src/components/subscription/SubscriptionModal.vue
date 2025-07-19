<!-- frontend/src/components/subscription/SubscriptionModal.vue -->
<template>
  <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeModal"></div>

    <!-- Modal -->
    <div class="flex min-h-screen items-center justify-center p-4">
      <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
        <!-- Header -->
        <div class="border-b border-gray-200 px-6 py-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ subscription?.isInTrial ? 'Upgrade to Premium' : 'Subscription' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <svg class="h-6 w-6" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="px-6 py-4">
          <!-- Current Plan Status -->
          <div v-if="subscription" class="mb-6">
            <div class="rounded-lg bg-gray-50 p-4">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Current Plan</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="subscription.plan_type === 'premium' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                  {{ subscription.plan_name }}
                </span>
              </div>

              <div v-if="subscription.is_in_trial" class="text-sm text-gray-600 mb-2">
                <span class="font-medium">{{ subscription.trial_days_remaining }}</span> days left in trial
              </div>

              <div v-else-if="subscription.plan_type === 'premium'" class="text-sm text-green-600 mb-2">
                <span class="font-medium">{{ subscription.days_remaining }}</span> days remaining
              </div>

              <div v-if="subscription.shows_ads" class="flex items-center text-xs text-gray-500">
                <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                </svg>
                Ads enabled
              </div>
            </div>
          </div>

          <!-- Premium Plan Benefits -->
          <div v-if="subscription?.plan_type !== 'premium'" class="mb-6">
            <h4 class="text-base font-semibold text-gray-900 mb-4">Premium Benefits</h4>
            <div class="space-y-3">
              <div class="flex items-start">
                <svg class="h-5 w-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <div>
                  <p class="text-sm font-medium text-gray-900">No Advertisements</p>
                  <p class="text-xs text-gray-500">Enjoy ad-free browsing</p>
                </div>
              </div>
              <div class="flex items-start">
                <svg class="h-5 w-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <div>
                  <p class="text-sm font-medium text-gray-900">Priority Support</p>
                  <p class="text-xs text-gray-500">Get help faster</p>
                </div>
              </div>
              <div class="flex items-start">
                <svg class="h-5 w-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <div>
                  <p class="text-sm font-medium text-gray-900">Advanced Features</p>
                  <p class="text-xs text-gray-500">Unlock premium tools</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Pricing -->
          <div v-if="subscription?.plan_type !== 'premium'" class="mb-6">
            <div class="rounded-lg border-2 border-blue-200 bg-blue-50 p-4">
              <div class="text-center">
                <div class="text-3xl font-bold text-blue-900">€3.49</div>
                <div class="text-sm text-blue-600">per month</div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-3">
            <button
              v-if="subscription?.plan_type !== 'premium'"
              @click="upgradeToPremium"
              :disabled="loading"
              class="w-full flex items-center justify-center px-4 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
              <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? 'Processing...' : 'Upgrade to Premium' }}
            </button>

            <button
              v-if="subscription?.plan_type === 'premium'"
              @click="showCancelModal = true"
              class="w-full flex items-center justify-center px-4 py-3 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
              Cancel Subscription
            </button>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-600">{{ error }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div v-if="showCancelModal" class="fixed inset-0 z-60 overflow-y-auto">
      <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showCancelModal = false"></div>
      <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
          <div class="px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cancel Subscription</h3>
            <p class="text-sm text-gray-600 mb-4">Are you sure you want to cancel your premium subscription? You'll lose access to premium features and ads will be shown again.</p>

            <div class="space-y-3">
              <button
                @click="confirmCancel"
                :disabled="loading"
                class="w-full flex items-center justify-center px-4 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50">
                {{ loading ? 'Cancelling...' : 'Yes, Cancel' }}
              </button>
              <button
                @click="showCancelModal = false"
                class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Keep Subscription
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useSubscriptionStore } from '@/stores/subscription'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'upgraded'])

const subscriptionStore = useSubscriptionStore()
const showCancelModal = ref(false)

// Computed
const showModal = computed(() => props.show)
const subscription = computed(() => subscriptionStore.subscription)
const loading = computed(() => subscriptionStore.loading)
const error = computed(() => subscriptionStore.error)

// Methods
const closeModal = () => {
  emit('close')
}

const upgradeToPremium = async () => {
  try {
    const result = await subscriptionStore.upgradeToPremium()
    if (result.payment_url) {
      // Redirect to Netopia payment page
      window.location.href = result.payment_url
    }
  } catch (error) {
    console.error('Upgrade failed:', error)
  }
}

const confirmCancel = async () => {
  try {
    await subscriptionStore.cancelSubscription()
    showCancelModal.value = false
    emit('close')
  } catch (error) {
    console.error('Cancel failed:', error)
  }
}

onMounted(() => {
  if (showModal.value) {
    subscriptionStore.getSubscription()
  }
})
</script>
