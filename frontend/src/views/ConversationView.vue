<!-- Replace your frontend/src/views/ConversationView.vue with this: -->
<template>
  <div class="flex flex-col h-screen bg-gray-50">
    <!-- Chat Header -->
    <div class="bg-white border-b border-gray-200 px-4 py-3 flex items-center space-x-3 sticky top-0 z-10">
      <!-- Back button -->
      <button @click="goBack"
              class="p-2 -ml-2 rounded-full hover:bg-gray-100 transition-colors">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>

      <!-- Participant info -->
      <div class="flex items-center space-x-3 flex-1">
        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-sm relative">
          {{ getInitials(otherParticipant) }}
          <!-- Online indicator -->
          <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
        </div>
        <div>
          <h2 class="font-semibold text-gray-900">
            {{ getParticipantName(otherParticipant) }}
          </h2>
          <p class="text-sm text-gray-500 flex items-center">
            {{ otherParticipant?.user_type === 'tutor' ? 'Tutor' : 'Student' }}
            <!-- Auto-refresh indicator -->
            <span class="ml-2 w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
          </p>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="mx-4 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-red-700 text-sm">{{ error }}</p>
    </div>

    <!-- Messages Container -->
    <div ref="messagesContainer"
         class="flex-1 overflow-y-auto px-4 py-4 space-y-4"
         style="height: calc(100vh - 140px);"
         @scroll="handleScroll">

      <!-- Messages -->
      <div v-for="message in messages" :key="message.id"
           class="flex"
           :class="{ 'justify-end': message.sender_id === currentUserId }">

        <div class="max-w-xs sm:max-w-md">
          <!-- Message bubble -->
          <div class="rounded-2xl px-4 py-2 shadow-sm"
               :class="message.sender_id === currentUserId
                 ? 'bg-blue-600 text-white rounded-br-md'
                 : 'bg-white text-gray-900 border border-gray-200 rounded-bl-md'">

            <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ message.message }}</p>
          </div>

          <!-- Message time -->
          <div class="flex items-center mt-1 px-2"
               :class="{ 'justify-end': message.sender_id === currentUserId }">
            <span class="text-xs text-gray-500">
              {{ formatMessageTime(message.created_at) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Loading messages -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="flex" :class="{ 'justify-end': i % 2 === 0 }">
          <div class="max-w-xs">
            <div class="animate-pulse bg-gray-300 rounded-2xl h-12 w-32"></div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="!loading && messages.length === 0" class="text-center py-8">
        <div class="w-16 h-16 mx-auto mb-4 bg-gray-200 rounded-full flex items-center justify-center">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
        </div>
        <p class="text-gray-500 text-sm">Începe conversația cu primul mesaj!</p>
      </div>
    </div>

    <!-- Scroll to bottom button -->
    <div v-if="showScrollButton" class="absolute bottom-20 right-4 z-10">
      <button @click="scrollToBottom"
              class="bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
        </svg>
      </button>
    </div>

    <!-- Message Input -->
    <div class="bg-white border-t border-gray-200 p-4">
      <form @submit.prevent="sendMessage" class="flex items-end space-x-3">
        <div class="flex-1">
          <textarea v-model="newMessage"
                    ref="messageInput"
                    @keydown.enter.exact.prevent="sendMessage"
                    :disabled="sending"
                    rows="1"
                    class="w-full px-4 py-2 border border-gray-300 rounded-full resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50"
                    placeholder="Scrie un mesaj..."
                    style="max-height: 120px; min-height: 40px;"></textarea>
        </div>

        <button type="submit"
                :disabled="!newMessage.trim() || sending"
                class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
          <svg v-if="sending" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
          </svg>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMessageStore } from '@/stores/messages'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const messageStore = useMessageStore()
const authStore = useAuthStore()

// Component state
const newMessage = ref('')
const messagesContainer = ref(null)
const messageInput = ref(null)
const showScrollButton = ref(false)
let refreshInterval = null

// Computed
const conversationId = computed(() => parseInt(route.params.id))
const currentConversation = computed(() => messageStore.currentConversation)
const messages = computed(() => messageStore.messages)
const loading = computed(() => messageStore.loading)
const sending = computed(() => messageStore.sending)
const error = computed(() => messageStore.error)
const currentUserId = computed(() => authStore.user?.id)
const otherParticipant = computed(() => {
  if (!currentConversation.value) return null
  return currentConversation.value.tutor_id === currentUserId.value
    ? currentConversation.value.student
    : currentConversation.value.tutor
})

// Methods
const loadConversation = async (silent = false) => {
  if (conversationId.value) {
    try {
      await messageStore.loadConversation(conversationId.value)
      if (!silent) {
        await nextTick()
        scrollToBottom()
      }
    } catch (error) {
      console.error('Error loading conversation:', error)
    }
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value) return

  const messageText = newMessage.value.trim()
  newMessage.value = ''

  try {
    await messageStore.sendMessage({
      recipient_id: otherParticipant.value?.id,
      message: messageText,
      type: 'text'
    })

    await nextTick()
    scrollToBottom()
  } catch (error) {
    // Restore message on error
    newMessage.value = messageText
    console.error('Error sending message:', error)
  }
}

const goBack = () => {
  router.push('/messages')
}

const getInitials = (user) => {
  if (!user) return '?'
  const name = getParticipantName(user)
  const names = name.split(' ')
  return names.map(n => n[0]).join('').toUpperCase() || '?'
}

const getParticipantName = (user) => {
  if (!user) return 'Unknown'
  return `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'Unknown User'
}

const formatMessageTime = (timestamp) => {
  if (!timestamp) return ''

  const date = new Date(timestamp)
  const now = new Date()
  const diffInHours = (now - date) / (1000 * 60 * 60)

  if (diffInHours < 1) {
    return 'Acum'
  } else if (diffInHours < 24) {
    return date.toLocaleTimeString('ro-RO', {
      hour: '2-digit',
      minute: '2-digit'
    })
  } else {
    return date.toLocaleDateString('ro-RO', {
      day: '2-digit',
      month: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    })
  }
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
      showScrollButton.value = false
    }
  })
}

// Check if user has scrolled up
const handleScroll = () => {
  if (messagesContainer.value) {
    const { scrollTop, scrollHeight, clientHeight } = messagesContainer.value
    const isAtBottom = scrollTop + clientHeight >= scrollHeight - 100
    showScrollButton.value = !isAtBottom && messages.value.length > 0
  }
}

// Auto-scroll when messages change
watch(messages, () => {
  scrollToBottom()
}, { deep: true })

// Auto-scroll when conversation loads
watch(currentConversation, () => {
  if (currentConversation.value) {
    scrollToBottom()
  }
})

// Lifecycle
onMounted(() => {
  loadConversation()

  // Set up auto-refresh every 3 seconds
  refreshInterval = setInterval(() => {
    loadConversation(true) // Silent reload to avoid scrolling interruption
  }, 3000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>
