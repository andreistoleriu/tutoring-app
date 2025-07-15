<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Preferințe notificări</h3>

    <form @submit.prevent="savePreferences" class="space-y-6">
      <!-- Notification Types -->
      <div>
        <h4 class="text-md font-medium text-gray-900 mb-4">Tipuri de notificări</h4>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700">Reminder lecții</label>
              <p class="text-sm text-gray-500">Primește notificări înainte de lecții</p>
            </div>
            <input
              v-model="preferences.lesson_reminders"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
          </div>

          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700">Reminder review-uri</label>
              <p class="text-sm text-gray-500">Primește notificări să scrii review-uri</p>
            </div>
            <input
              v-model="preferences.review_reminders"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
          </div>

          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700">Confirmări rezervări</label>
              <p class="text-sm text-gray-500">Primește notificări când rezervările sunt confirmate</p>
            </div>
            <input
              v-model="preferences.booking_confirmations"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
          </div>

          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700">Reminder plăți</label>
              <p class="text-sm text-gray-500">Primește notificări despre plăți restante</p>
            </div>
            <input
              v-model="preferences.payment_reminders"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
          </div>
        </div>
      </div>

      <!-- Delivery Methods -->
      <div>
        <h4 class="text-md font-medium text-gray-900 mb-4">Metode de livrare</h4>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700">Email</label>
              <p class="text-sm text-gray-500">Primește notificări prin email</p>
            </div>
            <input
              v-model="preferences.email_notifications"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
          </div>

          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700">Push notifications</label>
              <p class="text-sm text-gray-500">Primește notificări în browser</p>
            </div>
            <input
              v-model="preferences.push_notifications"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
          </div>

          <div class="flex items-center justify-between">
            <div>
              <label class="text-sm font-medium text-gray-700">SMS</label>
              <p class="text-sm text-gray-500">Primește notificări prin SMS</p>
            </div>
            <input
              v-model="preferences.sms_notifications"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
          </div>
        </div>
      </div>

      <!-- Timing -->
      <div>
        <h4 class="text-md font-medium text-gray-900 mb-4">Timing reminder</h4>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Primește reminder cu câte ore înainte de lecție
          </label>
          <select
            v-model="preferences.reminder_hours_before"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="1">1 oră înainte</option>
            <option value="2">2 ore înainte</option>
            <option value="6">6 ore înainte</option>
            <option value="12">12 ore înainte</option>
            <option value="24">24 ore înainte</option>
            <option value="48">48 ore înainte</option>
          </select>
        </div>
      </div>

      <!-- Save Button -->
      <div class="flex justify-end">
        <button
          type="submit"
          :disabled="saving"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
        >
          {{ saving ? 'Se salvează...' : 'Salvează preferințele' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

// State
const preferences = ref({
  lesson_reminders: true,
  review_reminders: true,
  payment_reminders: true,
  booking_confirmations: true,
  email_notifications: true,
  sms_notifications: false,
  push_notifications: true,
  reminder_hours_before: 24,
})

const saving = ref(false)
const loading = ref(false)

// Methods
const loadPreferences = async () => {
  loading.value = true
  try {
    const response = await api.get('notification-preferences')
    preferences.value = response.data.preferences
  } catch (error) {
    console.error('Error loading preferences:', error)
  } finally {
    loading.value = false
  }
}

const savePreferences = async () => {
  saving.value = true
  try {
    await api.put('notification-preferences', preferences.value)
    alert('Preferințele au fost salvate cu succes!')
  } catch (error) {
    console.error('Error saving preferences:', error)
    alert('Eroare la salvarea preferințelor. Încearcă din nou.')
  } finally {
    saving.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadPreferences()
})
</script>
