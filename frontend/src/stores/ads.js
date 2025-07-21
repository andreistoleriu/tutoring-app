import { defineStore } from 'pinia'

export const useAdsStore = defineStore('ads', {
  state: () => ({
    ads: []
  }),

  getters: {
    getRandomAd: () => () => null,
    getAdsByType: () => () => []
  },

  actions: {
    async getAds() {
      console.log('🔄 Ads store - development mode (no backend)')
      return []
    },
    async recordClick() {
      console.log('📊 Ad click recorded - development mode')
    },
    async recordView() {
      console.log('👁️ Ad view recorded - development mode')
    }
  }
})
