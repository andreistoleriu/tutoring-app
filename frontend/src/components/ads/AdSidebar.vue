<!-- frontend/src/components/ads/AdSidebar.vue -->
<template>
  <div v-if="shouldShowAd && ad" class="w-full mb-4">
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
      <!-- Ad label -->
      <div class="px-3 py-2 bg-gray-50 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-gray-500">Reclamă</span>
          <button
            @click="closeAd"
            class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Ad content -->
      <div class="p-4">
        <!-- Ad image -->
        <div v-if="ad.image_url" class="mb-3">
          <img
            :src="ad.image_url"
            :alt="ad.title"
            class="w-full h-32 object-cover rounded-lg"
            @error="handleImageError"
          >
        </div>

        <!-- Ad text -->
        <div class="mb-3">
          <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ ad.title }}</h3>
          <p class="text-xs text-gray-600 line-clamp-3">{{ ad.description }}</p>
        </div>

        <!-- CTA Button -->
        <button
          @click="handleClick"
          class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
          </svg>
          Vezi oferta
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useSubscriptionStore } from '@/stores/subscription'
import { useAdsStore } from '@/stores/ads'

const props = defineProps({
  ad: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close'])

const subscriptionStore = useSubscriptionStore()
const adsStore = useAdsStore()
const adClosed = ref(false)

const shouldShowAd = computed(() => {
  return !adClosed.value && subscriptionStore.shouldShowAds && props.ad
})

const handleClick = async () => {
  try {
    // Record click in ads store
    if (adsStore.recordClick) {
      await adsStore.recordClick(props.ad.id)
    }

    // Open link in new tab
    if (props.ad.link_url) {
      window.open(props.ad.link_url, '_blank', 'noopener,noreferrer')
    }
  } catch (error) {
    console.error('Error recording ad click:', error)
    // Still open the link even if click tracking fails
    if (props.ad.link_url) {
      window.open(props.ad.link_url, '_blank', 'noopener,noreferrer')
    }
  }
}

const closeAd = () => {
  adClosed.value = true
  emit('close')
}

const handleImageError = (event) => {
  // Hide broken image and show placeholder
  event.target.style.display = 'none'
  event.target.parentElement.innerHTML = `
    <div class="w-full h-32 bg-gray-100 rounded-lg flex items-center justify-center">
      <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
    </div>
  `
}

onMounted(() => {
  // Record impression when component is mounted and visible
  if (shouldShowAd.value && adsStore.recordImpression) {
    try {
      setTimeout(() => {
        if (shouldShowAd.value) {
          console.log('Recording sidebar ad impression for:', props.ad.id)
        }
      }, 1000)
    } catch (error) {
      console.error('Error recording ad impression:', error)
    }
  }
})
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
