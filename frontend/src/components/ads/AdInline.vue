<!-- frontend/src/components/ads/AdInline.vue -->
<template>
  <div v-if="shouldShowAd && selectedAd" class="w-full my-6">
    <!-- Feed/Inline Ad -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
      <!-- Ad Header -->
      <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <div class="flex items-center space-x-1">
              <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <span class="text-sm font-medium text-blue-700">Conținut sponsorizat</span>
            </div>

            <!-- Ad Badge -->
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
              </svg>
              Reclamă
            </span>
          </div>

          <div class="flex items-center space-x-2">
            <!-- Priority indicator -->
            <div v-if="selectedAd.priority && selectedAd.priority > 5"
                 class="flex items-center text-xs text-amber-600">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              Premium
            </div>

            <!-- Close button -->
            <button
              @click.stop="closeAd"
              class="text-gray-400 hover:text-gray-600 transition-colors p-1 hover:bg-white hover:bg-opacity-60 rounded"
              :aria-label="`Închide reclama ${selectedAd.title}`"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Ad Content -->
      <div class="p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:space-x-6">
          <!-- Ad Image -->
          <div v-if="selectedAd.image_url" class="mb-4 lg:mb-0 lg:flex-shrink-0">
            <img
              :src="selectedAd.image_url"
              :alt="selectedAd.title"
              class="w-full lg:w-48 h-48 lg:h-32 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
              loading="lazy"
              @click="handleClick"
              @error="handleImageError"
            />
          </div>

          <!-- Ad Text Content -->
          <div class="flex-1 min-w-0">
            <div class="mb-4">
              <h3
                class="text-xl font-bold text-gray-900 mb-2 cursor-pointer hover:text-blue-600 transition-colors line-clamp-2"
                @click="handleClick"
              >
                {{ selectedAd.title }}
              </h3>
              <p class="text-gray-600 leading-relaxed line-clamp-3">
                {{ selectedAd.description }}
              </p>
            </div>

            <!-- Ad Metrics & CTA Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <!-- Metrics -->
              <div v-if="showMetrics" class="flex items-center space-x-4 text-sm text-gray-500">
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                  </svg>
                  {{ formatNumber(selectedAd.impressions || 0) }} vizualizări
                </div>
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                  </svg>
                  {{ formatNumber(selectedAd.clicks || 0) }} click-uri
                </div>
              </div>

              <!-- CTA Button -->
              <button
                @click="handleClick"
                class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
                :disabled="clicking"
              >
                <svg v-if="!clicking" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <div v-else class="w-5 h-5 mr-2 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                {{ clicking ? 'Se deschide...' : 'Descoperă oferta' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Optional sponsored footer -->
      <div v-if="showFooter" class="px-4 py-3 bg-gray-50 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2 text-xs text-gray-500">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Verificat și aprobat</span>
          </div>

          <div class="flex items-center space-x-3 text-xs text-gray-400">
            <span>ID: {{ selectedAd.id }}</span>
            <span v-if="selectedAd.priority">Prioritate: {{ selectedAd.priority }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading overlay -->
    <div v-if="loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg">
      <div class="flex items-center space-x-2">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
        <span class="text-sm text-gray-600">Se încarcă...</span>
      </div>
    </div>
  </div>

  <!-- Error State -->
  <div v-else-if="error && shouldShowAd" class="w-full my-6 p-4 bg-red-50 border border-red-200 rounded-lg">
    <div class="flex items-center">
      <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      <div>
        <p class="text-sm text-red-600">{{ error }}</p>
        <button
          @click="fetchAd"
          class="text-xs text-red-800 underline mt-1 hover:text-red-900"
        >
          Încearcă din nou
        </button>
      </div>
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
    default: 'feed'
  },
  showMetrics: {
    type: Boolean,
    default: true
  },
  showFooter: {
    type: Boolean,
    default: false
  },
  autoRotate: {
    type: Boolean,
    default: false
  },
  rotateInterval: {
    type: Number,
    default: 30000 // 30 seconds
  },
  adId: {
    type: [Number, String],
    default: null
  }
})

const emit = defineEmits(['close', 'clicked', 'loaded', 'error'])

// Stores
const subscriptionStore = useSubscriptionStore()
const adsStore = useAdsStore()
const authStore = useAuthStore()

// State
const ads = ref([])
const selectedAd = ref(null)
const loading = ref(false)
const error = ref(null)
const clicking = ref(false)
const adClosed = ref(false)
const rotateTimer = ref(null)
const currentAdIndex = ref(0)

// Computed
const shouldShowAd = computed(() => {
  return !adClosed.value && subscriptionStore.shouldShowAds && selectedAd.value && authStore.isAuthenticated
})

// Methods
const formatNumber = (num) => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  } else if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K'
  }
  return num.toString()
}

const handleClick = async () => {
  if (clicking.value || !selectedAd.value) return

  clicking.value = true

  try {
    // Record click in ads store
    await adsStore.recordClick(selectedAd.value.id)

    // Emit event for parent component
    emit('clicked', selectedAd.value)

    // Open link in new tab
    if (selectedAd.value.click_url) {
      window.open(selectedAd.value.click_url, '_blank', 'noopener,noreferrer')
    }
  } catch (error) {
    console.error('Error handling inline ad click:', error)
    // Still open the link even if tracking fails
    if (selectedAd.value.click_url) {
      window.open(selectedAd.value.click_url, '_blank', 'noopener,noreferrer')
    }
  } finally {
    setTimeout(() => {
      clicking.value = false
    }, 1500)
  }
}

const closeAd = () => {
  adClosed.value = true
  stopRotation()
  emit('close', selectedAd.value?.id)
}

const handleImageError = () => {
  console.warn(`Failed to load image for inline ad ${selectedAd.value?.id}:`, selectedAd.value?.image_url)
}

const fetchAd = async () => {
  if (!subscriptionStore.shouldShowAds) {
    selectedAd.value = null
    return
  }

  loading.value = true
  error.value = null

  try {
    const params = {
      placement: props.placement,
      type: 'inline',
      limit: props.autoRotate ? 5 : 1
    }

    if (props.adId) {
      params.ad_id = props.adId
    }

    const response = await api.get('/ads', { params })

    ads.value = response.data.ads || []

    if (ads.value.length > 0) {
      selectedAd.value = ads.value[0]
      currentAdIndex.value = 0

      // Record impression
      try {
        await adsStore.recordImpression(selectedAd.value.id)
      } catch (impressionError) {
        console.warn('Failed to record impression for inline ad', selectedAd.value.id, impressionError)
      }

      // Start rotation if enabled and multiple ads
      if (props.autoRotate && ads.value.length > 1) {
        startRotation()
      }

      emit('loaded', selectedAd.value)
    } else {
      selectedAd.value = null
    }

    console.log('✅ Inline ad fetched:', {
      ad: selectedAd.value?.id,
      total: ads.value.length,
      placement: props.placement
    })
  } catch (err) {
    console.error('❌ Error fetching inline ad:', err)
    error.value = 'Nu s-a putut încărca reclama'
    selectedAd.value = null
    emit('error', err)
  } finally {
    loading.value = false
  }
}

const rotateAd = () => {
  if (ads.value.length <= 1) return

  currentAdIndex.value = (currentAdIndex.value + 1) % ads.value.length
  const newAd = ads.value[currentAdIndex.value]

  if (newAd && newAd.id !== selectedAd.value?.id) {
    selectedAd.value = newAd

    // Record impression for rotated ad
    adsStore.recordImpression(newAd.id).catch(error => {
      console.warn('Failed to record impression for rotated ad', newAd.id, error)
    })
  }
}

const startRotation = () => {
  if (rotateTimer.value) return

  rotateTimer.value = setInterval(rotateAd, props.rotateInterval)
}

const stopRotation = () => {
  if (rotateTimer.value) {
    clearInterval(rotateTimer.value)
    rotateTimer.value = null
  }
}

// Watch for subscription changes
watch(() => subscriptionStore.shouldShowAds, (newValue) => {
  if (newValue) {
    fetchAd()
  } else {
    selectedAd.value = null
    stopRotation()
  }
})

// Lifecycle
onMounted(async () => {
  if (authStore.isAuthenticated) {
    try {
      await subscriptionStore.getSubscription()
      if (subscriptionStore.shouldShowAds) {
        await fetchAd()
      }
    } catch (error) {
      console.error('Error initializing inline ad:', error)
    }
  }
})

// Cleanup
import { onUnmounted } from 'vue'
onUnmounted(() => {
  stopRotation()
})

// Expose methods
defineExpose({
  refresh: fetchAd,
  rotate: rotateAd,
  startRotation,
  stopRotation,
  getSelectedAd: () => selectedAd.value
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

/* Gradient animations */
.bg-gradient-to-r {
  background-size: 200% 200%;
  animation: gradientShift 3s ease infinite;
}

@keyframes gradientShift {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

/* Mobile optimizations */
@media (max-width: 640px) {
  .lg\:flex-row {
    flex-direction: column;
  }

  .lg\:w-48 {
    width: 100%;
  }

  .lg\:h-32 {
    height: 12rem;
  }
}

/* Hover animations */
.transform {
  transition: transform 0.2s ease-in-out;
}

.hover\:scale-105:hover {
  transform: scale(1.05);
}

/* Loading animation */
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
