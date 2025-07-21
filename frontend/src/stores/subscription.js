import { defineStore } from 'pinia'

export const useSubscriptionStore = defineStore('subscription', {
  state: () => ({
    subscription: {
      plan_type: 'free_trial',
      status: 'expired', // Expired trial should show ads
      shows_ads: true, // Enable ads for testing
      is_in_trial: false,
      trial_days_remaining: 0,
      days_remaining: 0,
      plan_name: 'Trial Expirat'
    },
    loading: false,
    error: null
  }),

  getters: {
    isTrialUser: (state) => state.subscription?.plan_type === 'free_trial',
    isPremiumUser: (state) => state.subscription?.plan_type === 'premium' && state.subscription?.status === 'active',

    // FIXED: Show ads for non-premium users in development
    shouldShowAds: (state) => {
      const sub = state.subscription
      if (!sub) return true

      // Show ads unless user is active premium
      const isActivePremium = sub.plan_type === 'premium' && sub.status === 'active'
      const shouldShow = !isActivePremium

      console.log('🎯 Development shouldShowAds check:', {
        subscription: sub,
        isActivePremium,
        shouldShow
      })

      return shouldShow
    },

    daysRemaining: (state) => state.subscription?.days_remaining ?? 0,
    trialDaysRemaining: (state) => state.subscription?.trial_days_remaining ?? 0,
    isInTrial: (state) => state.subscription?.is_in_trial ?? false,
    hasExpired: (state) => state.subscription?.status === 'expired'
  },

  actions: {
    async getSubscription() {
      console.log('🔄 Subscription store - development mode (no backend)')
      console.log('📊 Current subscription:', this.subscription)
      console.log('🎯 Should show ads:', this.shouldShowAds)
      return this.subscription
    },

    async createSubscription() {
      console.log('🆕 Create subscription - development mode')
      return this.subscription
    },

    async upgradeSubscription() {
      console.log('⬆️ Upgrade subscription - development mode')
      // Simulate upgrade to premium
      this.subscription = {
        ...this.subscription,
        plan_type: 'premium',
        status: 'active',
        shows_ads: false,
        days_remaining: 30
      }
      console.log('✅ Upgraded to premium:', this.subscription)
      return this.subscription
    },

    // Development helper methods
    simulateExpiredTrial() {
      this.subscription = {
        plan_type: 'free_trial',
        status: 'expired',
        shows_ads: true,
        is_in_trial: false,
        trial_days_remaining: 0,
        days_remaining: 0,
        plan_name: 'Trial Expirat'
      }
      console.log('🚨 Simulated expired trial:', this.subscription)
    },

    simulateActiveTrial() {
      this.subscription = {
        plan_type: 'free_trial',
        status: 'active',
        shows_ads: true,
        is_in_trial: true,
        trial_days_remaining: 7,
        days_remaining: 7,
        plan_name: 'Trial Activ'
      }
      console.log('⏰ Simulated active trial:', this.subscription)
    },

    simulatePremium() {
      this.subscription = {
        plan_type: 'premium',
        status: 'active',
        shows_ads: false,
        is_in_trial: false,
        trial_days_remaining: 0,
        days_remaining: 25,
        plan_name: 'Premium'
      }
      console.log('💎 Simulated premium:', this.subscription)
    }
  }
})
