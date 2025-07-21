<template>
  <!-- Mobile-First Messages View -->
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-4 py-3">
        <div class="flex items-center justify-between">
          <h1 class="text-xl font-semibold text-gray-900">Mesaje</h1>
          <div class="flex items-center space-x-2">
            <!-- Unread badge -->
            <div v-if="hasUnreadMessages"
                 class="bg-red-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center">
              {{ unreadCount > 99 ? '99+' : unreadCount }}
            </div>
            <!-- Auto-refresh indicator -->
            <div v-if="isAutoRefreshing"
                 class="flex items-center space-x-1 text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
              <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
              <span>Live</span>
            </div>
            <!-- Manual refresh button -->
            <button @click="refreshConversations"
                    :disabled="loading"
                    class="p-2 rounded-full hover:bg-gray-100 transition-colors"
                    title="Actualizează manual">
              <svg class="h-5 w-5 text-gray-600" :class="{ 'animate-spin': loading }"
                   fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="mx-4 mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-red-700 text-sm">{{ error }}</p>
      <button @click="clearError" class="mt-2 text-red-600 text-sm underline">
        Închide
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading && conversations.length === 0" class="p-4">
      <div class="space-y-4">
        <div v-for="i in 5" :key="i" class="loading-skeleton">
          <div class="flex items-center space-x-3 p-4 bg-white rounded-lg">
            <div class="w-12 h-12 bg-gray-300 rounded-full skeleton-shimmer"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-300 rounded w-1/3 skeleton-shimmer"></div>
              <div class="h-3 bg-gray-300 rounded w-2/3 skeleton-shimmer"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading && conversations.length === 0"
         class="text-center py-12 px-4">
      <div class="w-16 h-16 mx-auto mb-4 bg-gray-200 rounded-full flex items-center justify-center">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Nicio conversație</h3>
      <p class="text-gray-500 text-sm mb-6">Începe o conversație cu un tutor pentru a primi ajutor.</p>
      <router-link to="/tutors"
                   class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
        Caută tutori
      </router-link>
    </div>

    <!-- Conversations List -->
    <div v-else class="pb-6">
      <div class="space-y-1 px-4 mt-4">
        <div v-for="conversation in conversations" :key="conversation.id"
             @click="openConversation(conversation)"
             class="bg-white rounded-lg p-4 border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer relative">

          <!-- Unread indicator -->
          <div v-if="conversation.unread_count > 0"
               class="absolute top-2 right-2 bg-blue-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center z-10">
            {{ conversation.unread_count > 9 ? '9+' : conversation.unread_count }}
          </div>

          <div class="flex items-start space-x-3">
            <!-- Avatar -->
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
              {{ getInitials(conversation.other_participant) }}
            </div>

            <!-- Conversation info -->
            <div class="flex-1 min-w-0 pr-8">
              <div class="flex items-center justify-between mb-1">
                <h3 class="font-medium text-gray-900 truncate pr-2">
                  {{ conversation.other_participant.first_name }} {{ conversation.other_participant.last_name }}
                </h3>
                <span class="text-xs text-gray-500 whitespace-nowrap">
                  {{ formatMessageTime(conversation.last_message_at) }}
                </span>
              </div>

              <!-- Last message preview -->
              <p class="text-sm text-gray-600 truncate"
                 :class="{ 'font-medium text-gray-900': conversation.unread_count > 0 }">
                <span v-if="conversation.latest_message?.sender_id === currentUserId">Tu: </span>
                {{ conversation.latest_message?.message || 'Începe conversația...' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Start Conversation Modal -->
    <div v-if="showStartModal"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center p-4">
      <div class="bg-white rounded-t-xl sm:rounded-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Începe o conversație nouă</h3>

        <form @submit.prevent="handleStartConversation" class="space-y-4">
          <!-- Tutor selection would go here if needed -->

          <!-- Message input -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Mesaj</label>
            <textarea v-model="newMessage"
                     rows="3"
                     class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="Scrie mesajul tău..."></textarea>
          </div>

          <!-- Actions -->
          <div class="flex space-x-3">
            <button type="button" @click="showStartModal = false"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
              Anulează
            </button>
            <button type="submit" :disabled="sending || !newMessage.trim()"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
              <span v-if="sending">Se trimite...</span>
              <span v-else>Trimite</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useMessageStore } from '@/stores/messages'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const messageStore = useMessageStore()
const authStore = useAuthStore()

// Component state
const showStartModal = ref(false)
const newMessage = ref('')
const selectedTutorId = ref(null)
const isAutoRefreshing = ref(true)

// Auto-refresh intervals
let conversationsPolling = null
let unreadCountPolling = null

// Computed
const conversations = computed(() => messageStore.conversations)
const loading = computed(() => messageStore.loading)
const sending = computed(() => messageStore.sending)
const error = computed(() => messageStore.error)
const unreadCount = computed(() => messageStore.unreadCount)
const hasUnreadMessages = computed(() => messageStore.hasUnreadMessages)
const currentUserId = computed(() => authStore.user?.id)

// Methods
const refreshConversations = async () => {
  await messageStore.loadConversations()
}

const refreshConversationsSilent = async () => {
  // Silent refresh without showing loading spinner
  try {
    await messageStore.loadConversations()
  } catch (error) {
    console.error('Silent conversations refresh failed:', error)
  }
}

const refreshUnreadCount = async () => {
  try {
    await messageStore.loadUnreadCount()
  } catch (error) {
    console.error('Unread count refresh failed:', error)
  }
}

const startAutoRefresh = () => {
  if (!authStore.isAuthenticated) return

  // Refresh conversations every 5 seconds
  conversationsPolling = setInterval(refreshConversationsSilent, 5000)

  // Refresh unread count every 10 seconds
  unreadCountPolling = setInterval(refreshUnreadCount, 10000)

  isAutoRefreshing.value = true
}

const stopAutoRefresh = () => {
  if (conversationsPolling) {
    clearInterval(conversationsPolling)
    conversationsPolling = null
  }

  if (unreadCountPolling) {
    clearInterval(unreadCountPolling)
    unreadCountPolling = null
  }

  isAutoRefreshing.value = false
}

const openConversation = (conversation) => {
  router.push(`/messages/${conversation.id}`)
}

const getInitials = (user) => {
  if (!user) return '?'
  return `${user.first_name?.[0] || ''}${user.last_name?.[0] || ''}`.toUpperCase()
}

const formatMessageTime = (timestamp) => {
  if (!timestamp) return ''

  const date = new Date(timestamp)
  const now = new Date()
  const diffInHours = (now - date) / (1000 * 60 * 60)

  if (diffInHours < 24) {
    return date.toLocaleTimeString('ro-RO', {
      hour: '2-digit',
      minute: '2-digit'
    })
  } else if (diffInHours < 24 * 7) {
    return date.toLocaleDateString('ro-RO', {
      weekday: 'short'
    })
  } else {
    return date.toLocaleDateString('ro-RO', {
      day: '2-digit',
      month: '2-digit'
    })
  }
}

const handleStartConversation = async () => {
  if (!newMessage.value.trim() || !selectedTutorId.value) return

  try {
    const conversation = await messageStore.startConversation(
      selectedTutorId.value,
      newMessage.value.trim()
    )

    showStartModal.value = false
    newMessage.value = ''
    selectedTutorId.value = null

    // Navigate to the new conversation
    router.push(`/messages/${conversation.id}`)
  } catch (error) {
    // Error is handled by the store
  }
}

const clearError = () => {
  messageStore.clearError()
}

// Lifecycle
onMounted(async () => {
  // Initial load
  await refreshConversations()

  // Start auto-refresh
  startAutoRefresh()
})

onUnmounted(() => {
  // Clean up auto-refresh
  stopAutoRefresh()
})

// Handle visibility change (pause when tab is not visible)
const handleVisibilityChange = () => {
  if (document.hidden) {
    stopAutoRefresh()
  } else {
    startAutoRefresh()
    // Refresh immediately when tab becomes visible
    refreshConversationsSilent()
  }
}

// Listen for visibility changes to optimize performance
onMounted(() => {
  document.addEventListener('visibilitychange', handleVisibilityChange)
})

onUnmounted(() => {
  document.removeEventListener('visibilitychange', handleVisibilityChange)
})
</script>

<style scoped>
/* Mobile-first responsive design */
@media (max-width: 640px) {
  .max-w-md {
    max-width: calc(100vw - 2rem);
    margin: 1rem;
  }

  .p-6 {
    padding: 1rem;
  }

  .space-x-3 > * + * {
    margin-left: 0.5rem;
  }
}

/* Loading skeleton animations */
.skeleton-shimmer {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading-shimmer 1.5s infinite;
}

@keyframes loading-shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

/* Focus styles for accessibility */
button:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

textarea:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

/* Conversation hover effects */
.cursor-pointer:hover {
  transform: translateY(-1px);
  transition: all 0.2s ease-in-out;
}

/* Live indicator animation */
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

/* Button hover effects */
button:not(:disabled):hover {
  transform: scale(1.02);
  transition: transform 0.1s ease-out;
}

button:not(:disabled):active {
  transform: scale(0.98);
  transition: transform 0.05s ease-out;
}

/* Smooth transitions */
.transition-all {
  transition: all 0.2s ease-in-out;
}

/* Performance optimizations */
.conversations-container {
  transform: translateZ(0); /* Force hardware acceleration */
}
</style>
