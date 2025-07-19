<!-- frontend/src/components/subscription/SubscriptionBanner.vue -->
<template>
  <div v-if="shouldShowBanner" class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl p-6 text-white mb-6">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <div class="flex items-center mb-2">
          <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          <h3 class="text-lg font-semibold">
            {{ subscription?.isInTrial ? 'Ai încă timp să încerci Premium!' : 'Upgrade la Premium' }}
          </h3>
        </div>

        <p class="text-blue-100 text-sm mb-4">
          {{ subscription?.isInTrial
            ? `Mai ai ${subscription.trial_days_remaining || 0} zile în perioada de încercare gratuită.`
            : 'Descoperă toate funcționalitățile Premium și elimină reclamele.'
          }}
        </p>

        <div class="flex flex-wrap gap-2 text-xs">
          <span class="bg-white/20 backdrop-blur-sm rounded-full px-3 py-1">
            🚫 Fără reclame
          </span>
          <span class="bg-white/20 backdrop-blur-sm rounded-full px-3 py-1">
            ⚡ Suport prioritar
          </span>
          <span class="bg-white/20 backdrop-blur-sm rounded-full px-3 py-1">
            🎯 Funcții avansate
          </span>
        </div>
      </div>

      <div class="ml-6 flex flex-col space-y-2">
        <button
          @click="$emit('upgrade')"
          class="bg-white text-blue-600 px-6 py-2 rounded-lg font-medium text-sm hover:bg-blue-50 transition-colors whitespace-nowrap">
          {{ subscription?.isInTrial ? 'Upgrade acum' : 'Începe Premium' }}
        </button>

        <button
          v-if="!subscription?.isInTrial"
          @click="dismissBanner"
          class="text-blue-200 hover:text-white text-xs underline">
          Mai târziu
        </button>
      </div>
    </div>

    <!-- Progress bar for trial -->
    <div v-if="subscription?.isInTrial" class="mt-4">
      <div class="flex items-center justify-between text-xs text-blue-200 mb-1">
        <span>Progres trial</span>
        <span>{{ Math.max(0, subscription.trial_days_remaining || 0) }} / {{ subscription.trial_total_days || 14 }} zile</span>
      </div>
      <div class="w-full bg-white/20 rounded-full h-2">
        <div
          class="bg-white rounded-full h-2 transition-all duration-300"
          :style="{ width: trialProgressPercentage + '%' }"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useSubscriptionStore } from '@/stores/subscription'

const emit = defineEmits(['upgrade', 'dismiss'])

const subscriptionStore = useSubscriptionStore()
const dismissed = ref(false)

const subscription = computed(() => subscriptionStore.subscription)

const shouldShowBanner = computed(() => {
  if (dismissed.value) return false
  if (!subscription.value) return true

  // Show if in trial or free plan
  return subscription.value.isInTrial || subscription.value.plan_type !== 'premium'
})

const trialProgressPercentage = computed(() => {
  if (!subscription.value?.isInTrial) return 0

  const daysRemaining = subscription.value.trial_days_remaining || 0
  const totalDays = subscription.value.trial_total_days || 14
  const usedDays = totalDays - daysRemaining

  return Math.min(100, Math.max(0, (usedDays / totalDays) * 100))
})

const dismissBanner = () => {
  dismissed.value = true
  emit('dismiss')
}
</script>
