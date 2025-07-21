<template>
  <!-- Mobile-First Chat View -->
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
        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
          {{ getInitials(otherParticipant) }}
        </div>
        <div>
          <h2 class="font-semibold text-gray-900">
            {{ otherParticipant?.first_name }} {{ otherParticipant?.last_name }}
          </h2>
          <p class="text-sm text-gray-500">
            {{ otherParticipant?.user_type === 'tutor' ? 'Tutor' : 'Student' }}
          </p>
        </div>
      </div>

      <!-- Options menu -->
      <button class="p-2 rounded-full hover:bg-gray-100 transition-colors">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
        </svg>
      </button>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="mx-4 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-red-700 text-sm">{{ error }}</p>
    </div>

    <!-- Messages Container -->
    <div ref="messagesContainer"
         class="flex-1 overflow-y-auto px-4 py-4 space-y-4"
         @scroll="handleScroll">

      <!-- Loading older messages -->
      <div v-if="loadingOlder" class="text-center py-2">
        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
      </div>

      <!-- Messages -->
      <div v-for="(message, index) in messages" :key="message.id"
           class="flex"
           :class="{ 'justify-end': message.sender_id === currentUserId }">

        <div class="max-w-xs sm:max-w-md">
          <!-- Message bubble -->
          <div class="rounded-2xl px-4 py-2 shadow-sm"
               :class="message.sender_id === currentUserId
                 ? 'bg-blue-600 text-white rounded-br-md'
                 : 'bg-white text-gray-900 border border-gray-200 rounded-bl-md'">

            <!-- Booking reference message -->
            <div v-if="message.type === 'booking_reference'"
                 class="border rounded-lg p-3 mb-2"
                 :class="message.sender_id === currentUserId
                   ? 'border-blue-300 bg-blue-500'
                   : 'border-gray-300 bg-gray-50'">
              <div class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-medium">Rezervare referită</span>
              </div>
              <p class="text-sm mt-1 opacity-90">
                {{ message.metadata?.booking_subject }} - {{ formatBookingDate(message.metadata?.booking_date) }}
              </p>
            </div>

            <!-- Regular message -->
            <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ message.message }}</p>
          </div>

          <!-- Message time and status -->
          <div class="flex items-center mt-1 px-2"
               :class="{ 'justify-end': message.sender_id === currentUserId }">
            <span class="text-xs text-gray-500">
              {{ formatMessageTime(message.created_at) }}
            </span>

            <!-- Read status for sent messages -->
            <div v-if="message.sender_id === currentUserId" class="ml-1">
              <svg v-if="message.is_read" class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              <svg v-else class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading initial messages -->
      <div v-if="loading && messages.length === 0" class="space-y-4">
        <div v-for="i in 5" :key="i" class="flex" :class="{ 'justify-end': i % 2 === 0 }">
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

    <!-- Message Input -->
    <div class="bg-white border-t border-gray-200 p-4">
      <form @submit.prevent="sendMessage" class="flex items-end space-x-3">
        <!-- Message input -->
        <div class="flex-1">
          <textarea v-model="newMessage"
                    ref="messageInput"
                    @keydown.enter.exact.prevent="sendMessage"
                    @keydown.enter.shift.exact="handleShiftEnter"
                    :disabled="sending"
                    rows="1"
                    class="w-full px-4 py-2 border border-gray-300 rounded-full resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50"
                    placeholder="Scrie un mesaj..."
                    style="max-height: 120px; min-height: 40px;"
                    @input="autoResize"></textarea>
        </div>

        <!-- Send button -->
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

      <!-- Quick actions -->
      <div v-if="showQuickActions" class="flex space-x-2 mt-3">
        <button @click="suggestBooking"
                class="flex items-center space-x-1 px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span>Programează lecție</span>
        </button>
      </div>
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
const loadingOlder = ref(false)
const showQuickActions = ref(false)

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
const loadConversation = async () => {
  if (conversationId.value) {
    await messageStore.loadConversation(conversationId.value)
    await nextTick()
    scrollToBottom()
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

    // Auto-resize textarea back to single line
    if (messageInput.value) {
      messageInput.value.style.height = '40px'
    }
  } catch (error) {
    // Restore message on error
    newMessage.value = messageText
  }
}

const goBack = () => {
  router.push('/messages')
}

const getInitials = (user) => {
  if (!user) return '?'
  return `${user.first_name?.[0] || ''}${user.last_name?.[0] || ''}`.toUpperCase()
}

const formatMessageTime = (timestamp) => {
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

const formatBookingDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('ro-RO', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const handleScroll = () => {
  // TODO: Implement loading older messages when scrolled to top
}

const handleShiftEnter = (event) => {
  // Allow shift+enter for new lines
  return true
}

const autoResize = () => {
  const textarea = messageInput.value
  if (textarea) {
    textarea.style.height = 'auto'
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px'
  }
}

const suggestBooking = () => {
  newMessage.value = 'Aș vrea să programez o lecție cu tine. Când ești disponibil/ă?'
  messageInput.value?.focus()
}

// Watch for new messages and scroll to bottom
watch(messages, () => {
  nextTick(() => {
    scrollToBottom()
  })
}, { deep: true })

// Lifecycle
onMounted(() => {
  loadConversation()

  // Mark conversation as read when user is viewing it
  if (conversationId.value) {
    messageStore.markAsRead(conversationId.value)
  }
})

onUnmounted(() => {
  // Clean up
})
</script>
