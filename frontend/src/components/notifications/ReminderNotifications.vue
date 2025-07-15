<template>
  <div class="relative">
    <!-- Notification Bell -->
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
      :class="{ 'text-blue-600': hasUnread }"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 17h5l-3.14 5.86-2.86-5.86zm0 0V12a3 3 0 00-6 0v5l-3 3h12l-3-3z"/>
      </svg>

      <!-- Unread Count Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <div
      v-show="showDropdown"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
      @click.stop
    >
      <!-- Header -->
      <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900">Notificări</h3>
        <button
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="text-sm text-blue-600 hover:text-blue-700"
        >
          Marchează toate ca citite
        </button>
      </div>

      <!-- Notifications List -->
      <div class="max-h-96 overflow-y-auto">
        <div v-if="loading" class="p-4 text-center text-gray-500">
          Se încarcă...
        </div>

        <div v-else-if="reminders.length === 0" class="p-4 text-center text-gray-500">
          Nu ai notificări noi
        </div>

        <div v-else>
          <div
            v-for="reminder in reminders"
            :key="reminder.id"
            class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
            :class="{ 'bg-blue-50': !reminder.is_read }"
            @click="handleReminderClick(reminder)"
          >
            <div class="flex items-start space-x-3">
              <!-- Icon -->
              <div class="flex-shrink-0 mt-1">
                <div
                  class="w-2 h-2 rounded-full"
                  :class="reminder.is_read ? 'bg-gray-300' : 'bg-blue-500'"
                ></div>
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">
                  {{ reminder.title }}
                </p>
                <p class="text-sm text-gray-600 mt-1">
                  {{ reminder.message }}
                </p>
                <p class="text-xs text-gray-400 mt-2">
                  {{ formatTime(reminder.scheduled_at) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-4 py-3 border-t border-gray-200">
        <router-link
          to="/notifications"
          class="text-sm text-blue-600 hover:text-blue-700"
          @click="showDropdown = false"
        >
          Vezi toate notificările
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

// State
const showDropdown = ref(false)
const reminders = ref([])
const unreadCount = ref(0)
const loading = ref(false)

// Computed
const hasUnread = computed(() => unreadCount.value > 0)

// Methods
const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
  if (showDropdown.value) {
    loadReminders()
  }
}

const loadReminders = async () => {
  loading.value = true
  try {
    const response = await api.get('reminders')
    reminders.value = response.data.reminders
    unreadCount.value = response.data.unread_count
  } catch (error) {
    console.error('Error loading reminders:', error)
  } finally {
    loading.value = false
  }
}

const markAllAsRead = async () => {
  try {
    await api.post('reminders/mark-all-read')
    reminders.value.forEach(reminder => {
      reminder.is_read = true
    })
    unreadCount.value = 0
  } catch (error) {
    console.error('Error marking reminders as read:', error)
  }
}

const handleReminderClick = async (reminder) => {
  // Mark as read if not already
  if (!reminder.is_read) {
    try {
      await api.post(`reminders/${reminder.id}/mark-read`)
      reminder.is_read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    } catch (error) {
      console.error('Error marking reminder as read:', error)
    }
  }

  // Navigate based on reminder type
  showDropdown.value = false

  if (reminder.booking_id) {
    switch (reminder.type) {
      case 'lesson_reminder_student':
      case 'lesson_reminder_tutor':
        router.push(`/bookings/${reminder.booking_id}`)
        break
      case 'review_reminder':
        router.push(`/student/bookings?review=${reminder.booking_id}`)
        break
      default:
        router.push('/dashboard')
    }
  }
}

const formatTime = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = Math.abs(now - date) / (1000 * 60 * 60)

  if (diffInHours < 1) {
    return 'Acum câteva minute'
  } else if (diffInHours < 24) {
    return `${Math.floor(diffInHours)} ${diffInHours === 1 ? 'oră' : 'ore'} în urmă`
  } else {
    return date.toLocaleDateString('ro-RO', {
      day: 'numeric',
      month: 'short',
      hour: '2-digit',
      minute: '2-digit'
    })
  }
}

// Auto-refresh unread count
const loadUnreadCount = async () => {
  try {
    const response = await api.get('reminders/unread-count')
    unreadCount.value = response.data.unread_count
  } catch (error) {
    console.error('Error loading unread count:', error)
  }
}

// Click outside to close
const closeOnClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showDropdown.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadUnreadCount()
  document.addEventListener('click', closeOnClickOutside)

  // Refresh unread count every 2 minutes
  const interval = setInterval(loadUnreadCount, 120000)
  onUnmounted(() => {
    clearInterval(interval)
    document.removeEventListener('click', closeOnClickOutside)
  })
})
</script>
