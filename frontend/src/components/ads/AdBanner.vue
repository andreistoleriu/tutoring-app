<!-- frontend/src/components/ads/AdBanner.vue -->
<template>
  <div v-if="shouldShowAds && ads.length > 0" class="ad-container">
    <!-- Multiple Ad Display -->
    <div
      v-for="ad in displayedAds"
      :key="ad.id"
      :class="getAdClasses(props.type)"
      @click="handleAdClick(ad)"
    >
      <!-- Banner Type Ad -->
      <div v-if="props.type === 'banner'" class="banner-ad relative">
        <!-- Ad Header -->
        <div class="flex items-center justify-between p-3 bg-gray-50 border-b border-gray-200">
          <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span class="text-xs font-medium text-gray-500">Conținut sponsorizat</span>
          </div>
          <button
            @click.stop="closeAd(ad.id)"
            class="text-gray-400 hover:text-gray-600 transition-colors"
            v-if="allowClose"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Banner Content -->
        <div class="flex flex-col sm:flex-row">
          <!-- Image -->
          <div v-if="ad.image_url" class="w-full sm:w-1/3 lg:w-1/4">
            <img
              :src="ad.image_url"
              :alt="ad.title"
              class="w-full h-32 sm:h-full object-cover"
              loading="lazy"
              @error="handleImageError(ad)"
            />
          </div>

          <!-- Content -->
          <div class="flex-1 p-4">
            <div class="flex flex-col h-full justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">{{ ad.title }}</h3>
                <p class="text-sm text-gray-600 mb-3 line-clamp-3">{{ ad.description }}</p>
              </div>

              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center text-xs text-gray-500">
                  <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                  </svg>
                  {{ ad.impressions || 0 }} vizualizări
                </div>

                <button
                  @click.stop="handleAdClick(ad)"
                  class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                  <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                  </svg>
                  Vezi oferta
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card Type Ad -->
      <div v-else-if="props.type === 'card'" class="card-ad">
        <div class="bg-white border border-gray-200 rounded-xl shadow-lg p-4">
          <div class="text-center">
            <img
              v-if="ad.image_url"
              :src="ad.image_url"
              :alt="ad.title"
              class="w-full h-32 object-cover rounded-lg mb-3"
              loading="lazy"
              @error="handleImageError(ad)"
            />
            <h4 class="text-base font-bold text-gray-900 mb-2 line-clamp-2">{{ ad.title }}</h4>
            <p class="text-sm text-gray-600 mb-4 line-clamp-3">{{ ad.description }}</p>
            <button
              @click.stop="handleAdClick(ad)"
              class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              Descoperă mai mult
            </button>
            <div class="flex items-center justify-center mt-2 text-xs text-gray-400">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              Conținut sponsorizat
            </div>
          </div>
        </div>
      </div>

      <!-- Popup Type Ad -->
      <div v-else-if="props.type === 'popup'" class="popup-ad">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-40" @click="closeAd(ad.id)"></div>

        <!-- Modal -->
        <div class="relative z-50 bg-white rounded-lg shadow-xl max-w-sm w-full mx-4 overflow-hidden">
          <!-- Close Button -->
          <button
            @click.stop="closeAd(ad.id)"
            class="absolute top-3 right-3 z-10 text-gray-400 hover:text-gray-600 bg-white rounded-full p-1 shadow-sm"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Popup Content -->
          <div class="p-6 text-center">
            <img
              v-if="ad.image_url"
              :src="ad.image_url"
              :alt="ad.title"
              class="w-full h-40 object-cover rounded-lg mb-4"
              loading="lazy"
              @error="handleImageError(ad)"
            />
            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ ad.title }}</h3>
            <p class="text-gray-600 mb-6">{{ ad.description }}</p>
            <button
              @click.stop="handleAdClick(ad)"
              class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              Vezi oferta specială
            </button>
            <div class="text-xs text-gray-400 mt-3">Conținut sponsorizat</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center p-4">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
      <span class="ml-2 text-sm text-gray-600">Se încarcă reclamele...</span>
    </div>

    <!-- Error State -->
    <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-sm text-red-600">{{ error }}</p>
      <button
        @click="fetchAds"
        class="text-xs text-red-800 underline mt-1 hover:text-red-900"
      >
        Încearcă din nou
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useSubscriptionStore } from '@/stores/subscription'
import { useAdsStore } from '@/stores/ads'
import api from '@/services/api'

const props = defineProps({
  placement: {
    type: String,
    default: 'header',
    validator: (value) => ['header', 'sidebar', 'footer', 'feed', 'modal'].includes(value)
  },
  type: {
    type: String,
    default: 'banner',
    validator: (value) => ['banner', 'card', 'popup'].includes(value)
  },
  limit: {
    type: Number,
    default: 3
  },
  autoRefresh: {
    type: Boolean,
    default: false
  },
  refreshInterval: {
    type: Number,
    default: 30000 // 30 seconds
  },
  allowClose: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['adClosed', 'adClicked'])

const authStore = useAuthStore()
const subscriptionStore = useSubscriptionStore()
const adsStore = useAdsStore()

const ads = ref([])
const loading = ref(false)
const error = ref(null)
const refreshTimer = ref(null)
const closedAdIds = ref(new Set())

const shouldShowAds = computed(() => {
  return subscriptionStore.shouldShowAds && authStore.isAuthenticated
})

const displayedAds = computed(() => {
  return ads.value.filter(ad => !closedAdIds.value.has(ad.id))
})

const getAdClasses = (type) => {
  const baseClasses = 'cursor-pointer transition-all duration-200 hover:opacity-95'

  switch (type) {
    case 'banner':
      return `${baseClasses} bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-4 hover:shadow-md`
    case 'card':
      return `${baseClasses} bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 mb-3 hover:shadow-lg`
    case 'popup':
      return `${baseClasses} fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50`
    default:
      return baseClasses
  }
}

const handleAdClick = async (ad) => {
  try {
    // Record click in ads store
    await adsStore.recordClick(ad.id)

    // Emit event for parent component
    emit('adClicked', ad)

    // Open link in new tab
    if (ad.click_url) {
      window.open(ad.click_url, '_blank', 'noopener,noreferrer')
    }
  } catch (error) {
    console.error('Error handling ad click:', error)
    // Still open the link even if tracking fails
    if (ad.click_url) {
      window.open(ad.click_url, '_blank', 'noopener,noreferrer')
    }
  }
}

const closeAd = (adId) => {
  closedAdIds.value.add(adId)
  emit('adClosed', adId)
}

const handleImageError = (ad) => {
  console.warn(`Failed to load image for ad ${ad.id}:`, ad.image_url)
  // Could implement fallback image here
}

const fetchAds = async () => {
  if (!shouldShowAds.value) {
    ads.value = []
    return
  }

  loading.value = true
  error.value = null

  try {
    const response = await api.get('/ads', {
      params: {
        placement: props.placement,
        type: props.type,
        limit: props.limit
      }
    })

    ads.value = response.data.ads || []

    // Record impressions for displayed ads
    for (const ad of ads.value) {
      try {
        await adsStore.recordImpression(ad.id)
      } catch (impressionError) {
        console.warn('Failed to record impression for ad', ad.id, impressionError)
      }
    }

    console.log('✅ Ads fetched:', {
      count: ads.value.length,
      placement: props.placement,
      type: props.type,
      shouldShow: response.data.should_show_ads
    })
  } catch (err) {
    console.error('❌ Error fetching ads:', err)
    error.value = 'Nu s-au putut încărca reclamele'
    ads.value = []
  } finally {
    loading.value = false
  }
}

const startAutoRefresh = () => {
  if (props.autoRefresh && !refreshTimer.value) {
    refreshTimer.value = setInterval(fetchAds, props.refreshInterval)
  }
}

const stopAutoRefresh = () => {
  if (refreshTimer.value) {
    clearInterval(refreshTimer.value)
    refreshTimer.value = null
  }
}

// Watch for changes in subscription status
watch(shouldShowAds, (newValue) => {
  if (newValue) {
    fetchAds()
  } else {
    ads.value = []
    stopAutoRefresh()
  }
})

onMounted(async () => {
  // Wait for subscription status, then fetch ads
  if (authStore.isAuthenticated) {
    try {
      await subscriptionStore.getSubscription()
      if (shouldShowAds.value) {
        await fetchAds()
        startAutoRefresh()
      }
    } catch (error) {
      console.error('Error initializing ads:', error)
    }
  }
})

onUnmounted(() => {
  stopAutoRefresh()
})

// Expose methods for parent components
defineExpose({
  refresh: fetchAds,
  startRefresh: startAutoRefresh,
  stopRefresh: stopAutoRefresh,
  getDisplayedAds: () => displayedAds.value
})
</script>

<style scoped>
.ad-container {
  @apply space-y-4;
}

.banner-ad,
.card-ad {
  @apply relative;
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Mobile-first responsive adjustments */
@media (max-width: 640px) {
  .popup-ad .relative {
    @apply mx-4;
  }

  .banner-ad .flex {
    @apply flex-col;
  }

  .banner-ad img {
    @apply h-32;
  }
}

@media (min-width: 640px) {
  .banner-ad img {
    @apply h-full min-h-[120px];
  }
}

/* Animation for popup */
.popup-ad {
  animation: fadeInScale 0.3s ease-out;
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: translate(-50%, -50%) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
  }
}
</style>
