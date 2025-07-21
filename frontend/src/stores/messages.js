// frontend/src/stores/messages.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useMessageStore = defineStore('messages', () => {
  // State
  const conversations = ref([])
  const currentConversation = ref(null)
  const messages = ref([])
  const loading = ref(false)
  const sending = ref(false)
  const error = ref(null)
  const unreadCount = ref(0)

  // Computed
  const hasUnreadMessages = computed(() => unreadCount.value > 0)

  // Actions
  const loadConversations = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('/messages')
      conversations.value = response.data.conversations
      unreadCount.value = response.data.total_unread
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea conversațiilor'
      console.error('Error loading conversations:', err)
    } finally {
      loading.value = false
    }
  }

  const loadConversation = async (conversationId) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get(`/messages/${conversationId}`)
      currentConversation.value = response.data.conversation
      messages.value = response.data.messages.reverse() // Show oldest first

      // Update unread count for this conversation
      const conv = conversations.value.find(c => c.id === conversationId)
      if (conv) {
        conv.unread_count = 0
        updateUnreadCount()
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la încărcarea mesajelor'
      console.error('Error loading conversation:', err)
    } finally {
      loading.value = false
    }
  }

  const sendMessage = async (messageData) => {
    sending.value = true
    error.value = null

    try {
      const response = await api.post('/messages', messageData)
      const newMessage = response.data.message

      // Add message to current conversation
      if (currentConversation.value &&
          newMessage.conversation_id === currentConversation.value.id) {
        messages.value.push(newMessage)
      }

      // Update conversation in the list
      const conversation = response.data.conversation
      const existingIndex = conversations.value.findIndex(c => c.id === conversation.id)

      if (existingIndex >= 0) {
        // Move to top and update
        conversations.value.splice(existingIndex, 1)
        conversations.value.unshift({
          ...conversation,
          latest_message: newMessage,
          unread_count: 0
        })
      } else {
        // Add new conversation
        conversations.value.unshift({
          ...conversation,
          latest_message: newMessage,
          unread_count: 0
        })
      }

      return newMessage
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la trimiterea mesajului'
      console.error('Error sending message:', err)
      throw err
    } finally {
      sending.value = false
    }
  }

  const startConversation = async (tutorId, message) => {
    sending.value = true
    error.value = null

    try {
      const response = await api.post('/messages/start-conversation', {
        tutor_id: tutorId,
        message: message
      })

      const conversation = response.data.conversation
      const newMessage = response.data.message

      // Add to conversations list
      conversations.value.unshift({
        ...conversation,
        latest_message: newMessage,
        unread_count: 0
      })

      return conversation
    } catch (err) {
      error.value = err.response?.data?.message || 'Eroare la inițierea conversației'
      console.error('Error starting conversation:', err)
      throw err
    } finally {
      sending.value = false
    }
  }

  const markAsRead = async (conversationId) => {
    try {
      await api.patch(`/messages/${conversationId}/read`)

      // Update local state
      const conversation = conversations.value.find(c => c.id === conversationId)
      if (conversation) {
        conversation.unread_count = 0
        updateUnreadCount()
      }
    } catch (err) {
      console.error('Error marking as read:', err)
    }
  }

  const updateUnreadCount = () => {
    unreadCount.value = conversations.value.reduce((total, conv) => {
      return total + (conv.unread_count || 0)
    }, 0)
  }

  const loadUnreadCount = async () => {
    try {
      const response = await api.get('/messages/unread-count')
      unreadCount.value = response.data.unread_count
    } catch (err) {
      console.error('Error loading unread count:', err)
    }
  }

  const clearError = () => {
    error.value = null
  }

  const reset = () => {
    conversations.value = []
    currentConversation.value = null
    messages.value = []
    unreadCount.value = 0
    error.value = null
  }

  return {
    // State
    conversations,
    currentConversation,
    messages,
    loading,
    sending,
    error,
    unreadCount,

    // Computed
    hasUnreadMessages,

    // Actions
    loadConversations,
    loadConversation,
    sendMessage,
    startConversation,
    markAsRead,
    loadUnreadCount,
    clearError,
    reset
  }
})
