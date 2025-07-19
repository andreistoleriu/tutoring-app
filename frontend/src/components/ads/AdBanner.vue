<template>
  <div v-if="shouldShowAds && ads.length > 0" class="ad-container">
    <!-- Banner Ads -->
    <div
      v-for="ad in ads"
      :key="ad.id"
      :class="getAdClasses(ad.type)"
      @click="handleAdClick(ad)"
    >
      <!-- Banner Type -->
      <div v-if="ad.type === 'banner'" class="banner-ad">
        <div class="relative overflow-hidden rounded-lg">
          <img
            v-if="ad.image_url"
            :src="ad.image_url"
            :alt="ad.title"
            class="w-full h-20 sm:h-24 object-cover"
            loading="lazy"
          />
          <div class="absolute inset-0 bg-gradient-to-r from-black/30 to-transparent"></div>
          <div class="absolute bottom-0 left-0 right-0 p-3 text-white">
            <h4 class="text-sm font-semibold line-clamp-1">{{ ad.title }}</h4>
            <p class="text-xs opacity-90 line-clamp-1 mt-1">{{ ad.description }}</p>
          </div>
        </div>
        <div class="text-xs text-gray-400 px-2 py-1">Sponsored</div>
      </div>

      <!-- Card Type -->
      <div v-else-if="ad.type === 'card'" class="card-ad">
        <div class="flex space-x-3 p-3">
          <img
            v-if="ad.image_url"
            :src="ad.image_url"
            :alt="ad.title"
            class="w-12 h-12 sm:w-16 sm:h-16 object-cover rounded-lg flex-shrink-0"
            loading="lazy"
          />
          <div class="flex-1 min-w-0">
            <h4 class="text-sm font-medium text-gray-900 line-clamp-2">{{ ad.title }}</h4>
            <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ ad.description }}</p>
            <div class="text-xs text-gray-400 mt-2">Sponsored</div>
          </div>
        </div>
      </div>

      <!-- Popup Type (Modal-like) -->
      <div v-else-if="ad.type === 'popup'" class="popup-ad">
        <div class="bg-white border border-gray-200 rounded-xl shadow-lg p-4">
          <div class="text-center">
            <img
              v-if="ad.image_url"
              :src="ad.image_url"
              :alt="ad.title"
              class="w-full h-32 object-cover rounded-lg mb-3"
              loading="lazy"
            />
            <h4 class="text-base font-bold text-gray-900 mb-2">{{ ad.title }}</h4>
            <p class="text-sm text-gray-600 mb-4">{{ ad.description }}</p>
            <button class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
              Learn More
            </button>
            <div class="text-xs text-gray-400 mt-2">Sponsored Content</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center p-4">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
      <span class="ml-2 text-sm text-gray-600">Loading ads...</span>
    </div>

    <!-- Error State -->
    <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-sm text-red-600">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useSubscriptionStore } from '@/stores/subscription'
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
  }
})

const authStore = useAuthStore()
const subscriptionStore = useSubscriptionStore()

const ads = ref([])
const loading = ref(false)
const error = ref(null)
const refreshTimer = ref(null)

const shouldShowAds = computed(() => {
  return subscriptionStore.shouldShowAds && authStore.isAuthenticated
})

const getAdClasses = (type) => {
  const baseClasses = 'cursor-pointer transition-all duration-200 hover:opacity-90 hover:scale-[1.02]'

  switch (type) {
    case 'banner':
      return `${baseClasses} bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-4`
    case 'card':
      return `${baseClasses} bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 mb-3`
    case 'popup':
      return `${baseClasses} fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50 max-w-sm w-full mx-4`
    default:
      return baseClasses
  }
}

const handleAdClick = async (ad) => {
  try {
    // Record click
    await api.post(`/ads/${ad.id}/click`)

    // Open link using your actual field name
    if (ad.click_url) {
      window.open(ad.click_url, '_blank', 'noopener,noreferrer')
    }
  } catch (error) {
    console.error('Error recording ad click:', error)
    // Still open the link even if tracking fails
    if (ad.click_url) {
      window.open(ad.click_url, '_blank', 'noopener,noreferrer')
    }
  }
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
        placement: props.placement,  // Using your actual field name
        type: props.type,
        limit: props.limit
      }
    })

    ads.value = response.data.ads || []

    // Debug logging
    console.log('Ads fetched:', {
      count: ads.value.length,
      placement: props.placement,
      type: props.type,
      shouldShow: response.data.should_show_ads,
      userType: response.data.user_type
    })
  } catch (err) {
    console.error('Error fetching ads:', err)
    error.value = 'Failed to load ads'
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
  }
})

onMounted(() => {
  // Fetch subscription status first, then ads
  if (authStore.isAuthenticated) {
    subscriptionStore.getSubscription().then(() => {
      fetchAds()
      startAutoRefresh()
    })
  }
})

// Cleanup on unmount
import { onUnmounted } from 'vue'
onUnmounted(() => {
  stopAutoRefresh()
})

// Expose methods for parent components
defineExpose({
  refresh: fetchAds,
  startRefresh: startAutoRefresh,
  stopRefresh: stopAutoRefresh
})
</script>

<style scoped>
.ad-container {
  @apply space-y-2;
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

/* Mobile-first responsive adjustments */
@media (max-width: 640px) {
  .popup-ad {
    @apply fixed inset-4 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2;
  }
}
</style>
