<!-- frontend/src/components/ads/AdInline.vue -->
<template>
  <div v-if="shouldShowAd && ad" class="w-full my-6">
    <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-lg border border-gray-200 p-4 relative">
      <!-- Ad label -->
      <div class="flex items-center justify-between mb-3">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Reclamă sponsorizată
        </span>
        <button
          @click="closeAd"
          class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Ad content -->
      <div class="flex items-start space-x-4">
        <!-- Ad image -->
        <div v-if="ad.image_url" class="flex-shrink-0">
          <img
            :src="ad.image_url"
            :alt="ad.title"
            class="h-16 w-16 rounded-lg object-cover border border-gray-200"
            @error="handleImageError"
          >
        </div>

        <!-- Ad text and CTA -->
        <div class="flex-1 min-w-0">
          <h3 class="text-base font-semibold text-gray-900 mb-1">{{ ad.title }}</h3>
          <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ ad.description }}</p>

          <button
            @click="handleClick"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow-md">
            Află mai multe
            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </button>
        </div>
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
  // Replace broken image with placeholder
  event.target.outerHTML = `
    <div class="h-16 w-16 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
      <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          console.log('Recording inline ad impression for:', props.ad.id)
        }
      }, 1000)
    } catch (error) {
      console.error('Error recording ad impression:', error)
    }
  }
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
