<template>
  <!-- Message Badge Component for Navigation -->
  <div class="relative">
    <router-link to="/messages"
                 class="flex items-center space-x-2 p-2 rounded-lg transition-colors"
                 :class="isActive ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'">
      <div class="relative">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>

        <!-- Unread badge -->
        <div v-if="hasUnreadMessages"
             class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
          {{ unreadCount > 9 ? '9+' : unreadCount }}
        </div>
      </div>

      <span v-if="showLabel" class="font-medium">Mesaje</span>
    </router-link>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useMessageStore } from '@/stores/messages'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  showLabel: {
    type: Boolean,
    default: true
  }
})

const route = useRoute()
const messageStore = useMessageStore()
const authStore = useAuthStore()

// Computed
const isActive = computed(() => route.path.startsWith('/messages'))
const hasUnreadMessages = computed(() => messageStore.hasUnreadMessages)
const unreadCount = computed(() => messageStore.unreadCount)
const isAuthenticated = computed(() => authStore.isAuthenticated)

let intervalId = null

// Methods
const loadUnreadCount = () => {
  if (isAuthenticated.value) {
    messageStore.loadUnreadCount()
  }
}

// Lifecycle
onMounted(() => {
  if (isAuthenticated.value) {
    loadUnreadCount()

    // Set up periodic refresh every 30 seconds
    intervalId = setInterval(loadUnreadCount, 30000)
  }
})

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId)
  }
})
</script>
