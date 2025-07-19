<!-- frontend/src/components/subscription/SubscriptionBanner.vue -->
<template>
  <div v-if="shouldShowBanner" class="w-full mb-4">
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-4 text-white relative overflow-hidden">
      <!-- Background decoration -->
      <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-purple-600/20"></div>

      <!-- Close button -->
      <button
        @click="hideBanner"
        class="absolute top-2 right-2 text-white/70 hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <div class="relative flex items-center justify-between">
        <div class="flex-1 pr-4">
          <div class="flex items-center mb-2">
            <svg class="w-5 h-5 mr-2 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-sm font-semibold">
              <span v-if="isInTrial">{{ trialDaysRemaining }} zile rămase în perioada de încercare</span>
              <span v-else>Upgrade la Premium</span>
            </h3>
          </div>
          <p class="text-xs opacity-90 leading-relaxed">
            <span v-if="isInTrial">
              Elimină reclamele și accesează funcții premium pentru doar €3.49/lună.
              Fără reclame, suport prioritar și funcții avansate.
            </span>
            <span v-else>
              Experiență fără reclame, suport prioritar și funcții premium pentru doar €3.49/lună.
            </span>
          </p>
        </div>

        <div class="flex-shrink-0">
          <button
            @click="$emit('upgrade')"
            class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 shadow-lg hover:shadow-xl">
            <span class="flex items-center space-x-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Upgrade</span>
            </span>
          </button>
        </div>
      </div>

      <!-- Progress bar for trial -->
      <div v-if="isInTrial && trialDaysRemaining !== null" class="mt-3">
        <div class="flex justify-between text-xs mb-1 opacity-90">
          <span>Progres trial</span>
          <span>{{ 14 - trialDaysRemaining }}/14 zile</span>
        </div>
        <div class="w-full bg-white/20 rounded-full h-1.5">
          <div
            class="bg-yellow-300 h-1.5 rounded-full transition-all duration-300"
            :style="{ width: `${((14 - trialDaysRemaining) / 14) * 100}%` }">
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useSubscriptionStore } from '@/stores/subscription'

const emit = defineEmits(['upgrade'])

const subscriptionStore = useSubscriptionStore()
const bannerHidden = ref(false)

const shouldShowBanner = computed(() => {
  if (bannerHidden.value) return false

  const subscription = subscriptionStore.subscription
  return subscription && (subscription.plan_type === 'free_trial' || subscription.shows_ads)
})

const isInTrial = computed(() => subscriptionStore.isInTrial)
const trialDaysRemaining = computed(() => subscriptionStore.trialDaysRemaining)

const hideBanner = () => {
  bannerHidden.value = true
  // Store in localStorage to remember user preference
  localStorage.setItem('subscription_banner_hidden', 'true')
}

// Check if user previously hid the banner
if (localStorage.getItem('subscription_banner_hidden') === 'true') {
  bannerHidden.value = true
}
</script>
