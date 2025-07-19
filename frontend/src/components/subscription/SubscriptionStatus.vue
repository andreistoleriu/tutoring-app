<!-- frontend/src/components/subscription/SubscriptionStatus.vue -->
<template>
  <div class="bg-white rounded-lg border border-gray-200 p-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900">Status Abonament</h3>
      <button
        @click="refreshSubscription"
        :disabled="loading"
        class="text-sm text-blue-600 hover:text-blue-800 disabled:opacity-50">
        <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span v-else>Actualizează</span>
      </button>
    </div>

    <div v-if="subscription" class="space-y-4">
      <!-- Plan Info -->
      <div class="flex items-center justify-between py-3 border-b border-gray-200">
        <span class="text-sm font-medium text-gray-700">Plan actual</span>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              :class="subscription.plan_type === 'premium' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
          {{ subscription.plan_name || (subscription.plan_type === 'premium' ? 'Premium' : 'Trial Gratuit') }}
        </span>
      </div>

      <!-- Status -->
      <div class="flex items-center justify-between py-3 border-b border-gray-200">
        <span class="text-sm font-medium text-gray-700">Status</span>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              :class="subscription.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
          {{ subscription.status === 'active' ? 'Activ' : 'Inactiv' }}
        </span>
      </div>

      <!-- Days Remaining -->
      <div v-if="subscription.is_in_trial" class="flex items-center justify-between py-3 border-b border-gray-200">
        <span class="text-sm font-medium text-gray-700">Zile rămase în trial</span>
        <span class="text-sm font-semibold text-yellow-600">{{ subscription.trial_days_remaining || 0 }}</span>
      </div>

      <div v-else-if="subscription.plan_type === 'premium'" class="flex items-center justify-between py-3 border-b border-gray-200">
        <span class="text-sm font-medium text-gray-700">Zile rămase</span>
        <span class="text-sm font-semibold text-green-600">{{ subscription.days_remaining || 0 }}</span>
      </div>

      <!-- Ads Status -->
      <div class="flex items-center justify-between py-3 border-b border-gray-200">
        <span class="text-sm font-medium text-gray-700">Reclame</span>
        <span class="text-sm font-semibold"
              :class="subscription.shows_ads ? 'text-yellow-600' : 'text-green-600'">
          {{ subscription.shows_ads ? 'Activate' : 'Dezactivate' }}
        </span>
      </div>

      <!-- Price -->
      <div v-if="subscription.plan_type === 'premium'" class="flex items-center justify-between py-3 border-b border-gray-200">
        <span class="text-sm font-medium text-gray-700">Preț lunar</span>
        <span class="text-sm font-semibold text-gray-900">€{{ subscription.price || '3.49' }}</span>
      </div>

      <!-- Actions -->
      <div class="pt-4 space-y-2">
        <button
          v-if="subscription.plan_type !== 'premium'"
          @click="$emit('upgrade')"
          class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Upgrade la Premium
        </button>

        <button
          v-if="subscription.plan_type === 'premium'"
          @click="$emit('manage')"
          class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          Gestionează Abonamentul
        </button>
      </div>
    </div>

    <div v-else class="text-center py-8">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">Nu s-a găsit abonament</h3>
      <p class="mt-1 text-sm text-gray-500">Începe cu o perioadă de încercare gratuită</p>
      <button
        @click="$emit('upgrade')"
        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
        Începe Trial Gratuit
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useSubscriptionStore } from '@/stores/subscription'

const emit = defineEmits(['upgrade', 'manage'])

const subscriptionStore = useSubscriptionStore()

const subscription = computed(() => subscriptionStore.subscription)
const loading = computed(() => subscriptionStore.loading)

const refreshSubscription = async () => {
  try {
    await subscriptionStore.getSubscription()
  } catch (error) {
    console.error('Error refreshing subscription:', error)
  }
}

onMounted(() => {
  if (!subscription.value) {
    subscriptionStore.getSubscription()
  }
})
</script>
