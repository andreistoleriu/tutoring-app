<!-- frontend/src/components/messages/StartConversationButton.vue -->
<template>
  <div>
    <!-- Trigger Button -->
    <button @click="openModal"
            :disabled="loading"
            class="w-full bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center space-x-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
      </svg>
      <span>💬 Trimite mesaj</span>
    </button>

    <!-- Modal -->
    <div v-if="showModal"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center p-4">
      <div class="bg-white rounded-t-xl sm:rounded-xl w-full max-w-md max-h-[80vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
              Mesaj pentru {{ tutorName }}
            </h3>
            <button @click="closeModal"
                    class="p-2 hover:bg-gray-100 rounded-full transition-colors">
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
          <!-- Tutor Info -->
          <div class="flex items-center space-x-3 mb-4 p-3 bg-gray-50 rounded-lg">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
              {{ getInitials(tutorName) }}
            </div>
            <div>
              <h4 class="font-medium text-gray-900">{{ tutorName }}</h4>
              <p class="text-sm text-gray-600">{{ getTutorBio() }}</p>
            </div>
          </div>

          <!-- Quick message templates -->
          <div class="mb-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Alege un mesaj rapid:</p>
            <div class="space-y-2">
              <button v-for="template in messageTemplates" :key="template.id"
                      @click="selectTemplate(template.message)"
                      class="w-full text-left p-3 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                {{ template.message }}
              </button>
            </div>
          </div>

          <!-- Message Form -->
          <form @submit.prevent="sendMessage">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Mesajul tău
              </label>
              <textarea v-model="message"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                        placeholder="Scrie mesajul tău aici..."
                        required></textarea>
            </div>

            <!-- Error message -->
            <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-red-700 text-sm">{{ error }}</p>
            </div>

            <!-- Action buttons -->
            <div class="flex space-x-3">
              <button type="button" @click="closeModal"
                      class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Anulează
              </button>
              <button type="submit" :disabled="sending || !message.trim()"
                      class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <span v-if="sending">Se trimite...</span>
                <span v-else>Începe conversația</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useMessageStore } from '@/stores/messages'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  tutor: {
    type: Object,
    required: true
  }
})

const router = useRouter()
const messageStore = useMessageStore()
const authStore = useAuthStore()

// Component state
const showModal = ref(false)
const message = ref('')
const loading = ref(false)

// Computed
const sending = computed(() => messageStore.sending)
const error = computed(() => messageStore.error)
const currentUser = computed(() => authStore.user)

// Get tutor name safely
const tutorName = computed(() => {
  if (props.tutor.user) {
    return `${props.tutor.user.first_name} ${props.tutor.user.last_name}`
  }
  return `${props.tutor.first_name || ''} ${props.tutor.last_name || ''}`.trim()
})

// Get tutor USER ID (not tutor profile ID)
const tutorUserId = computed(() => {
  // The tutor_id we need to send should be the user_id, not the tutor profile id
  if (props.tutor.user_id) {
    return props.tutor.user_id  // This is the user ID from tutors table
  }
  if (props.tutor.user && props.tutor.user.id) {
    return props.tutor.user.id  // This is the user object ID
  }
  return props.tutor.id  // Fallback to profile ID (might not work)
})

// Message templates for first-time conversations
const messageTemplates = ref([
  {
    id: 1,
    message: 'Bună! Aș fi interesat/ă de o lecție cu tine. Poți să îmi spui mai multe despre experiența ta?'
  },
  {
    id: 2,
    message: 'Salut! Am văzut profilul tău și mi-ar plăcea să programez o lecție. Când ești disponibil/ă?'
  },
  {
    id: 3,
    message: 'Bună ziua! Sunt interesat/ă de ajutorul tău la învățare. Care este abordarea ta de predare?'
  },
  {
    id: 4,
    message: 'Salut! Aș avea nevoie de ajutor cu materia pe care o predai. Poți să îmi spui cum funcționează o lecție cu tine?'
  }
])

// Methods
const openModal = () => {
  console.log('Opening modal, current user:', currentUser.value)
  console.log('Tutor object:', props.tutor)
  console.log('Tutor USER ID we will send:', tutorUserId.value)

  // Check if user is authenticated
  if (!currentUser.value) {
    alert('Trebuie să te conectezi pentru a trimite mesaje.')
    router.push('/login')
    return
  }

  // Check if user is a student
  if (currentUser.value.user_type !== 'student') {
    alert('Doar studenții pot trimite mesaje către tutori.')
    return
  }

  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  message.value = ''
  messageStore.clearError()
}

const selectTemplate = (template) => {
  message.value = template
}

const sendMessage = async () => {
  if (!message.value.trim() || sending.value) return

  console.log('Sending message to tutor USER ID:', tutorUserId.value)

  try {
    const conversation = await messageStore.startConversation(
      tutorUserId.value,
      message.value.trim()
    )

    closeModal()

    // Navigate to the new conversation
    router.push(`/messages/${conversation.id}`)
  } catch (error) {
    // Error is handled by the store
    console.error('Error starting conversation:', error)
  }
}

const getInitials = (name) => {
  if (!name) return '?'
  const names = name.split(' ')
  return names.map(n => n[0]).join('').toUpperCase()
}

const getTutorBio = () => {
  const bio = props.tutor.bio || props.tutor.description || 'Tutor profesionist'
  return bio.length > 60 ? bio.slice(0, 60) + '...' : bio
}
</script>
