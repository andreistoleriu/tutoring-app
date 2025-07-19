<!-- frontend/src/components/ads/AdBanner.vue -->
<template>
  <div v-if="shouldShowAd && ad" class="w-full mb-4">
    <div class="relative bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-gray-200 overflow-hidden">
      <!-- Ad label -->
      <div class="absolute top-2 left-2 z-10">
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
          Reclamă
        </span>
      </div>

      <!-- Close button -->
      <button
        @click="closeAd"
        class="absolute top-2 right-2 z-10 text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <!-- Ad content -->
      <div class="p-4 pt-8">
        <div class="flex items-center space-x-4">
          <!-- Ad image -->
          <div v-if="ad.image_url" class="flex-shrink-0">
            <img
              :src="ad.image_url"
              :alt="ad.title"
              class="h-12 w-12 rounded-lg object-cover"
              @error="handleImageError"
            >
          </div>

          <!-- Ad text -->
          <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold text-gray-900 truncate">{{ ad.title }}</h3>
            <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ ad.description }}</p>
          </div>

          <!-- CTA Button -->
          <div class="flex-shrink-0">
            <button
              @click="handleClick"
              class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
              Vezi mai mult
            </button>
          </div>
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
  // Hide broken image
  event.target.style.display = 'none'
}

onMounted(() => {
  // Record impression when component is mounted and visible
  if (shouldShowAd.value && adsStore.recordImpression) {
    try {
      // Small delay to ensure it's actually visible
      setTimeout(() => {
        if (shouldShowAd.value) {
          // Note: You'd need to implement recordImpression in adsStore
          console.log('Recording ad impression for:', props.ad.id)
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
