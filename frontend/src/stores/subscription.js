import { defineStore } from 'pinia'

export const useSubscriptionStore = defineStore('subscription', {
  state: () => ({
    subscription: {
      plan_type: 'free',
      status: 'active',
      shows_ads: false, // Hide ads in development
      is_in_trial: false,
      trial_days_remaining: 0,
      days_remaining: 0
    },
    loading: false,
    error: null
  }),

  getters: {
    shouldShowAds: () => false // Always hide ads in development
  },

  actions: {
    async getSubscription() {
      console.log('🔄 Subscription store - development mode (no backend)')
      return this.subscription
    },
    async createSubscription() {
      console.log('🆕 Create subscription - development mode')
      return this.subscription
    },
    async upgradeSubscription() {
      console.log('⬆️ Upgrade subscription - development mode')
      return this.subscription
    }
  }
})
