<!-- frontend/src/components/ads/AdSidebar.vue -->
<template>
  <div v-if="shouldShowAds && visibleAds.length > 0" class="w-full space-y-4">
    <!-- Multiple sidebar ads -->
    <div
      v-for="(ad, index) in visibleAds"
      :key="ad.id"
      class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200"
    >
      <!-- Ad Header -->
      <div class="px-3 py-2 bg-gray-50 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-gray-500 flex items-center">
            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            Reclamă
          </span>
          <div class="flex items-center space-x-2">
            <span class="text-xs text-gray-400">#{{ index + 1 }}</span>
            <button
              @click="closeAd(ad.id)"
              class="text-gray-400 hover:text-gray-600 transition-colors p-1 hover:bg-gray-100 rounded"
              :aria-label="`Închide reclama ${ad.title}`"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Ad Content -->
      <div class="p-4">
        <!-- Ad Image -->
        <div v-if="ad.image_url" class="mb-3">
          <img
            :src="ad.image_url"
            :alt="ad.title"
            class="w-full h-32 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
            loading="lazy"
            @click="handleClick(ad)"
            @error="handleImageError(ad)"
          >
        </div>

        <!-- Ad Text -->
        <div class="mb-3">
          <h3 class="text-sm font-semibold text-gray-900 mb-1 line-clamp-2 hover:text-blue-600 transition-colors cursor-pointer"
              @click="handleClick(ad)">
            {{ ad.title }}
          </h3>
          <p class="text-xs text-gray-600 line-clamp-3 leading-relaxed">{{ ad.description }}</p>
        </div>

        <!-- Ad Metrics (if available) -->
        <div v-if="showMetrics && (ad.impressions || ad.clicks)" class="flex items-center justify-between mb-3 text-xs text-gray-500">
          <div class="flex items-center">
            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
              <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
            </svg>
            {{ ad.impressions || 0 }}
          </div>
          <div class="flex items-center">
            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
            {{ ad.clicks || 0 }}
          </div>
        </div>

        <!-- CTA Button -->
        <button
          @click="handleClick(ad)"
          class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-[1.02]"
          :disabled="clicking"
        >
          <svg v-if="!clicking" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
          </svg>
          <div v-else class="w-4 h-4 mr-2 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
          {{ clicking ? 'Se încarcă...' : 'Vezi oferta' }}
        </button>

        <!-- Additional Info -->
        <div v-if="ad.priority && showPriority" class="mt-2 text-xs text-gray-400 text-center">
          Prioritate: {{ ad.priority }}
        </div>
      </div>
    </div>

    <!-- Load More Button (if there are more ads) -->
    <button
      v-if="hasMoreAds && !loading"
      @click="loadMoreAds"
      class="w-full py-2 text-sm text-blue-600 hover:text-blue-700 border border-blue-200 hover:border-blue-300 rounded-lg transition-colors"
    >
      Încarcă mai multe reclame
    </button>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center p-4 bg-gray-50 rounded-lg">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
      <span class="ml-2 text-sm text-gray-600">Se încarcă...</span>
    </div>

    <!-- Error State -->
    <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
      <div class="flex items-center">
        <svg class="w-4 h-4 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div>
          <p class="text-sm text-red-600">{{ error }}</p>
          <button
            @click="fetchAds"
            class="text-xs text-red-800 underline mt-1 hover:text-red-900"
          >
            Încearcă din nou
          </button>
        </div>
      </div>
    </div>

    <!-- No Ads State -->
    <div v-if="!loading && !error && shouldShowAds && ads.length === 0" class="p-4 bg-gray-50 rounded-lg text-center">
      <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
      </svg>
      <p class="text-sm text-gray-600">Nicio reclamă disponibilă</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useSubscriptionStore } from '@/stores/subscription'
import { useAdsStore } from '@/stores/ads'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const props = defineProps({
  placement: {
    type: String,
    default: 'sidebar'
  },
  limit: {
    type: Number,
    default: 2
  },
  showMetrics: {
    type: Boolean,
    default: false
  },
  showPriority: {
    type: Boolean,
    default: false
  },
  autoRefresh: {
    type: Boolean,
    default: false
  },
  refreshInterval: {
    type: Number,
    default: 60000 // 1 minute
  }
})

const emit = defineEmits(['adClosed', 'adClicked', 'adsLoaded'])

// Stores
const subscriptionStore = useSubscriptionStore()
const adsStore = useAdsStore()
const authStore = useAuthStore()

// State
const ads = ref([])
const loading = ref(false)
const error = ref(null)
const clicking = ref(false)
const closedAdIds = ref(new Set())
const currentPage = ref(1)
const totalAds = ref(0)
const refreshTimer = ref(null)

// Computed
const shouldShowAds = computed(() => {
  return subscriptionStore.shouldShowAds && authStore.isAuthenticated
})

const visibleAds = computed(() => {
  return ads.value.filter(ad => !closedAdIds.value.has(ad.id))
})

const hasMoreAds = computed(() => {
  return visibleAds.value.length < totalAds.value && visibleAds.value.length < props.limit * 2
})

// Methods
const handleClick = async (ad) => {
  if (clicking.value) return

  clicking.value = true

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
  } finally {
    setTimeout(() => {
      clicking.value = false
    }, 1000) // Prevent rapid clicking
  }
}

const closeAd = (adId) => {
  closedAdIds.value.add(adId)
  emit('adClosed', adId)

  // If we've closed too many ads, fetch more
  if (visibleAds.value.length < 1 && hasMoreAds.value) {
    loadMoreAds()
  }
}

const handleImageError = (ad) => {
  console.warn(`Failed to load image for ad ${ad.id}:`, ad.image_url)
  // Could implement fallback image here
}

const fetchAds = async (append = false) => {
  if (!shouldShowAds.value) {
    ads.value = []
    return
  }

  loading.value = true
  if (!append) {
    error.value = null
  }

  try {
    const response = await api.get('/ads', {
      params: {
        placement: props.placement,
        type: 'sidebar',
        limit: props.limit,
        page: append ? currentPage.value + 1 : 1
      }
    })

    const newAds = response.data.ads || []
    totalAds.value = response.data.total || newAds.length

    if (append) {
      // Filter out duplicates
      const existingIds = new Set(ads.value.map(ad => ad.id))
      const uniqueNewAds = newAds.filter(ad => !existingIds.has(ad.id))
      ads.value = [...ads.value, ...uniqueNewAds]
      currentPage.value++
    } else {
      ads.value = newAds
      currentPage.value = 1
    }

    // Record impressions for new ads
    for (const ad of newAds) {
      try {
        await adsStore.recordImpression(ad.id)
      } catch (impressionError) {
        console.warn('Failed to record impression for ad', ad.id, impressionError)
      }
    }

    emit('adsLoaded', {
      ads: ads.value,
      total: totalAds.value,
      visible: visibleAds.value.length
    })

    console.log('✅ Sidebar ads fetched:', {
      count: ads.value.length,
      visible: visibleAds.value.length,
      placement: props.placement
    })
  } catch (err) {
    console.error('❌ Error fetching sidebar ads:', err)
    error.value = 'Nu s-au putut încărca reclamele'
    if (!append) {
      ads.value = []
    }
  } finally {
    loading.value = false
  }
}

const loadMoreAds = async () => {
  if (loading.value || !hasMoreAds.value) return
  await fetchAds(true)
}

const startAutoRefresh = () => {
  if (props.autoRefresh && !refreshTimer.value) {
    refreshTimer.value = setInterval(() => {
      fetchAds(false) // Refresh, don't append
    }, props.refreshInterval)
  }
}

const stopAutoRefresh = () => {
  if (refreshTimer.value) {
    clearInterval(refreshTimer.value)
    refreshTimer.value = null
  }
}

// Watch for subscription changes
watch(shouldShowAds, (newValue) => {
  if (newValue) {
    fetchAds()
    startAutoRefresh()
  } else {
    ads.value = []
    stopAutoRefresh()
  }
})

// Lifecycle
onMounted(async () => {
  if (authStore.isAuthenticated) {
    try {
      await subscriptionStore.getSubscription()
      if (shouldShowAds.value) {
        await fetchAds()
        startAutoRefresh()
      }
    } catch (error) {
      console.error('Error initializing sidebar ads:', error)
    }
  }
})

// Cleanup
import { onUnmounted } from 'vue'
onUnmounted(() => {
  stopAutoRefresh()
})

// Expose methods
defineExpose({
  refresh: () => fetchAds(false),
  loadMore: loadMoreAds,
  getVisibleAds: () => visibleAds.value,
  startRefresh: startAutoRefresh,
  stopRefresh: stopAutoRefresh
})
</script>

<style scoped>
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

/* Mobile optimizations */
@media (max-width: 640px) {
  .space-y-4 > * + * {
    margin-top: 1rem;
  }
}

/* Hover effects */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Button loading state */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
