<!-- frontend/src/components/ads/AdInline.vue -->
<template>
  <div v-if="shouldShowAd && ad" class="w-full my-6">
    <!-- Ad label -->
    <div class="text-center mb-2">
      <span class="text-xs text-gray-400 font-medium uppercase tracking-wide">Reclamă</span>
    </div>

    <!-- Ad content -->
    <div
      class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer overflow-hidden"
      @click="handleClick">

      <div class="p-6">
        <!-- Horizontal layout -->
        <div class="flex items-center space-x-4">
          <!-- Ad image -->
          <div v-if="ad.image_url" class="flex-shrink-0">
            <img
              :src="ad.image_url"
              :alt="ad.title"
              class="w-20 h-20 object-cover rounded-lg"
              @error="handleImageError"
            >
          </div>

          <!-- Fallback icon if no image -->
          <div v-else class="flex-shrink-0 w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
          </div>

          <!-- Ad text content -->
          <div class="flex-1 min-w-0">
            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">{{ ad.title }}</h3>
            <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ ad.description }}</p>

            <!-- CTA section -->
            <div class="flex items-center justify-between">
              <span class="text-blue-600 text-sm font-medium">{{ ad.cta_text || 'Vezi mai mult' }}</span>
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Optional sponsored badge -->
      <div class="px-6 py-2 bg-gray-50 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span class="text-xs text-gray-500">Conținut sponsorizat</span>
          </div>

          <button
            @click.stop="closeAd"
            class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
  // Hide broken image and show placeholder
  event.target.style.display = 'none'
  const placeholder = document.createElement('div')
  placeholder.className = 'w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center'
  placeholder.innerHTML = `
    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
    </svg>
  `
  event.target.parentElement.replaceChild(placeholder, event.target)
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
