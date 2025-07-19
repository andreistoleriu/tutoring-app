// frontend/src/stores/ads.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'

export const useAdsStore = defineStore('ads', () => {
  // State
  const ads = ref([])
  const shouldShowAds = ref(true)
  const loading = ref(false)
  const error = ref(null)

  // Computed
  const bannerAds = computed(() => ads.value.filter(ad => ad.type === 'banner'))
  const sidebarAds = computed(() => ads.value.filter(ad => ad.type === 'sidebar'))
  const inlineAds = computed(() => ads.value.filter(ad => ad.type === 'inline'))
  const popupAds = computed(() => ads.value.filter(ad => ad.type === 'popup'))

  // Actions
  const getAds = async () => {
    loading.value = true
    error.value = null

    try {
      console.log('📢 Loading ads...')
      const response = await api.get('/ads')
      ads.value = response.data.ads || []
      shouldShowAds.value = response.data.should_show_ads ?? true

      console.log('✅ Ads loaded:', {
        total: ads.value.length,
        banner: bannerAds.value.length,
        sidebar: sidebarAds.value.length,
        inline: inlineAds.value.length,
        shouldShow: shouldShowAds.value
      })

      return response.data
    } catch (err) {
      console.error('❌ Error loading ads:', err)
      error.value = err.response?.data?.message || 'Error loading ads'

      // Set fallback state
      ads.value = []
      shouldShowAds.value = false
    } finally {
      loading.value = false
    }
  }

  const recordClick = async (adId) => {
    try {
      const authStore = useAuthStore()
      const userId = authStore.user?.id

      if (!userId) {
        console.warn('No user ID for ad click tracking')
        return
      }

      console.log('🖱️ Recording ad click:', adId)
      await api.post(`/ads/${adId}/click`, {
        user_id: userId
      })

      console.log('✅ Ad click recorded')
    } catch (err) {
      console.error('❌ Error recording ad click:', err)
      // Don't throw error - ad click tracking shouldn't break user experience
    }
  }

  const recordImpression = async (adId) => {
    try {
      const authStore = useAuthStore()
      const userId = authStore.user?.id

      if (!userId) {
        console.warn('No user ID for ad impression tracking')
        return
      }

      console.log('👁️ Recording ad impression:', adId)
      // You'd implement this endpoint in your backend
      await api.post(`/ads/${adId}/impression`, {
        user_id: userId
      })

      console.log('✅ Ad impression recorded')
    } catch (err) {
      console.error('❌ Error recording ad impression:', err)
      // Don't throw error - ad impression tracking shouldn't break user experience
    }
  }

  const getAdsByType = (type) => {
    return ads.value.filter(ad => ad.type === type)
  }

  const getRandomAd = (type = null) => {
    const filteredAds = type ? getAdsByType(type) : ads.value
    if (filteredAds.length === 0) return null

    const randomIndex = Math.floor(Math.random() * filteredAds.length)
    return filteredAds[randomIndex]
  }

  const clearAds = () => {
    ads.value = []
    shouldShowAds.value = false
  }

  return {
    // State
    ads,
    shouldShowAds,
    loading,
    error,

    // Computed
    bannerAds,
    sidebarAds,
    inlineAds,
    popupAds,

    // Actions
    getAds,
    recordClick,
    recordImpression,
    getAdsByType,
    getRandomAd,
    clearAds,
  }
})
