// frontend/src/stores/subscription.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useSubscriptionStore = defineStore('subscription', () => {
  // State
  const subscription = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const paymentUrl = ref(null)

  // Computed
  const isTrialUser = computed(() => subscription.value?.plan_type === 'free_trial')
  const isPremiumUser = computed(() => subscription.value?.plan_type === 'premium')
  const shouldShowAds = computed(() => subscription.value?.shows_ads ?? true)
  const daysRemaining = computed(() => subscription.value?.days_remaining ?? 0)
  const trialDaysRemaining = computed(() => subscription.value?.trial_days_remaining ?? 14)
  const isInTrial = computed(() => subscription.value?.is_in_trial ?? false)
  const hasExpired = computed(() => daysRemaining.value <= 0)

  // Actions
  const getSubscription = async () => {
    loading.value = true
    error.value = null

    try {
      console.log('🔄 Loading subscription data...')
      const response = await api.get('/subscription')
      subscription.value = response.data.subscription

      console.log('✅ Subscription loaded:', subscription.value)
      return response.data
    } catch (err) {
      console.error('❌ Error loading subscription:', err)
      error.value = err.response?.data?.message || 'Error loading subscription'

      // If no subscription found, create a default trial state
      if (err.response?.status === 404) {
        subscription.value = {
          plan_type: 'free_trial',
          status: 'active',
          shows_ads: true,
          is_in_trial: true,
          trial_days_remaining: 14,
          days_remaining: 14,
          trial_total_days: 14,
          plan_name: 'Trial Gratuit'
        }
        console.log('📝 Created default trial subscription state')
      }
    } finally {
      loading.value = false
    }
  }

  const upgradeToPremium = async () => {
    loading.value = true
    error.value = null

    try {
      console.log('⬆️ Upgrading to premium...')
      const response = await api.post('/subscription/upgrade')

      if (response.data.payment_url) {
        paymentUrl.value = response.data.payment_url
        return { payment_url: response.data.payment_url }
      }

      // Direct upgrade without payment
      subscription.value = response.data.subscription
      console.log('✅ Upgraded to premium:', subscription.value)

      return response.data
    } catch (err) {
      console.error('❌ Error upgrading subscription:', err)
      error.value = err.response?.data?.message || 'Error upgrading subscription'
      throw err
    } finally {
      loading.value = false
    }
  }

  const cancelSubscription = async () => {
    loading.value = true
    error.value = null

    try {
      console.log('❌ Cancelling subscription...')
      const response = await api.post('/subscription/cancel')
      subscription.value = response.data.subscription

      console.log('✅ Subscription cancelled:', subscription.value)
      return response.data
    } catch (err) {
      console.error('❌ Error cancelling subscription:', err)
      error.value = err.response?.data?.message || 'Error cancelling subscription'
      throw err
    } finally {
      loading.value = false
    }
  }

  const checkPaymentStatus = async (paymentId) => {
    try {
      console.log('💳 Checking payment status:', paymentId)
      const response = await api.get(`/subscription/payment/${paymentId}/status`)

      if (response.data.subscription) {
        subscription.value = response.data.subscription
      }

      return response.data
    } catch (err) {
      console.error('❌ Error checking payment status:', err)
      throw err
    }
  }

  const handlePaymentSuccess = async () => {
    try {
      // Refresh subscription data after successful payment
      await getSubscription()
    } catch (err) {
      console.error('❌ Error refreshing subscription after payment:', err)
    }
  }

  // Initialize subscription on store creation
  const init = async () => {
    try {
      await getSubscription()
    } catch (error) {
      console.log('📄 Could not load subscription initially, will work with default state')
    }
  }

  return {
    // State
    subscription,
    loading,
    error,
    paymentUrl,

    // Computed
    isTrialUser,
    isPremiumUser,
    shouldShowAds,
    daysRemaining,
    trialDaysRemaining,
    isInTrial,
    hasExpired,

    // Actions
    getSubscription,
    upgradeToPremium,
    cancelSubscription,
    checkPaymentStatus,
    handlePaymentSuccess,
    init
  }
})
