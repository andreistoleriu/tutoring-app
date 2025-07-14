<template>
  <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
    <!-- Header -->
    <div class="flex items-start justify-between">
      <div class="flex items-center space-x-4">
        <!-- Student Avatar -->
        <div class="flex-shrink-0">
          <img
            v-if="review.student?.profile_image"
            :src="review.student.profile_image"
            :alt="getStudentName()"
            class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm"
          >
          <div
            v-else
            class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm shadow-sm"
          >
            {{ getStudentInitials() }}
          </div>
        </div>

        <!-- Review Info -->
        <div class="flex-1">
          <div class="flex items-center space-x-3 mb-1">
            <h4 class="font-semibold text-gray-900">{{ getStudentName() }}</h4>

            <!-- Star Rating -->
            <StarRating
              :model-value="review.rating"
              :readonly="true"
              size="sm"
              :show-text="false"
            />

            <!-- Rating Number -->
            <span class="text-sm font-medium text-gray-700">{{ review.rating }}/5</span>
          </div>

          <!-- Subject and Date -->
          <div class="flex items-center space-x-4 text-sm text-gray-500">
            <span v-if="review.subject || review.booking?.subject">
              {{ review.subject?.name || review.booking?.subject?.name }}
            </span>
            <span>{{ formatDate(review.created_at) }}</span>
            <span v-if="showVerified && isVerifiedReview" class="flex items-center text-green-600">
              <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
              Verificat
            </span>
          </div>
        </div>
      </div>

      <!-- Actions Menu -->
      <div v-if="showActions" class="relative">
        <button
          @click="showActionsMenu = !showActionsMenu"
          class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
          </svg>
        </button>

        <!-- Actions Dropdown -->
        <div
          v-if="showActionsMenu"
          v-click-outside="() => showActionsMenu = false"
          class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-10"
        >
          <button
            v-if="canEdit"
            @click="handleEdit"
            class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center space-x-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              <span>Editează review-ul</span>
            </div>
          </button>

          <button
            v-if="canDelete"
            @click="handleDelete"
            class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 transition-colors"
          >
            <div class="flex items-center space-x-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
              <span>Șterge review-ul</span>
            </div>
          </button>

          <button
            v-if="canReport"
            @click="handleReport"
            class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center space-x-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
              <span>Raportează</span>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Review Comment -->
    <div v-if="review.comment" class="text-gray-700 leading-relaxed">
      <p class="whitespace-pre-wrap">{{ review.comment }}</p>
    </div>

    <!-- Tutor Reply -->
    <div v-if="review.tutor_reply" class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-400">
      <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
          <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
          </div>
        </div>
        <div class="flex-1">
          <div class="flex items-center justify-between mb-2">
            <h5 class="text-sm font-medium text-blue-900">Răspuns de la tutor</h5>
            <span class="text-xs text-blue-600">{{ formatDate(review.tutor_replied_at) }}</span>
          </div>
          <p class="text-blue-800 text-sm whitespace-pre-wrap">{{ review.tutor_reply }}</p>
        </div>
      </div>
    </div>

    <!-- Helpful Actions -->
    <div v-if="showHelpful" class="flex items-center justify-between pt-4 border-t border-gray-100">
      <div class="flex items-center space-x-4">
        <span class="text-sm text-gray-600">Ți-a fost util acest review?</span>

        <div class="flex items-center space-x-2">
          <button
            @click="handleHelpful(true)"
            :class="[
              'flex items-center space-x-1 px-3 py-1 rounded-lg text-sm transition-colors',
              helpfulVote === true
                ? 'bg-green-100 text-green-700'
                : 'text-gray-600 hover:bg-gray-100'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
            </svg>
            <span>Da</span>
            <span v-if="helpfulCount > 0" class="text-xs bg-gray-200 px-1 rounded">{{ helpfulCount }}</span>
          </button>

          <button
            @click="handleHelpful(false)"
            :class="[
              'flex items-center space-x-1 px-3 py-1 rounded-lg text-sm transition-colors',
              helpfulVote === false
                ? 'bg-red-100 text-red-700'
                : 'text-gray-600 hover:bg-gray-100'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018c.163 0 .326.02.485.06L17 4m-7 10v2a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2M5 4H3a2 2 0 00-2 2v6a2 2 0 002 2h2.5"></path>
            </svg>
            <span>Nu</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import StarRating from './StarRating.vue'

// Props
const props = defineProps({
  review: {
    type: Object,
    required: true
  },
  showActions: {
    type: Boolean,
    default: false
  },
  showHelpful: {
    type: Boolean,
    default: false
  },
  showVerified: {
    type: Boolean,
    default: true
  },
  canEdit: {
    type: Boolean,
    default: false
  },
  canDelete: {
    type: Boolean,
    default: false
  },
  canReport: {
    type: Boolean,
    default: true
  }
})

// Emits
const emit = defineEmits(['edit', 'delete', 'report', 'helpful'])

// State
const showActionsMenu = ref(false)
const helpfulVote = ref(null) // true, false, or null
const helpfulCount = ref(props.review.helpful_count || 0)

// Computed
const isVerifiedReview = computed(() => {
  // Review is verified if the booking was completed and paid
  return props.review.booking?.status === 'completed' &&
         props.review.booking?.payment_status === 'paid'
})

// Methods
const getStudentName = () => {
  const student = props.review.student
  if (!student) return 'Student anonim'

  if (student.first_name || student.last_name) {
    return `${student.first_name || ''} ${student.last_name || ''}`.trim()
  }

  return student.full_name || student.name || 'Student anonim'
}

const getStudentInitials = () => {
  const name = getStudentName()
  if (name === 'Student anonim') return 'SA'

  const nameParts = name.trim().split(' ')
  if (nameParts.length === 1) return nameParts[0][0].toUpperCase()
  return nameParts[0][0].toUpperCase() + nameParts[nameParts.length - 1][0].toUpperCase()
}

const formatDate = (dateString) => {
  if (!dateString) return ''

  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('ro-RO', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    })
  } catch (error) {
    return 'Data necunoscută'
  }
}

const handleEdit = () => {
  showActionsMenu.value = false
  emit('edit', props.review)
}

const handleDelete = () => {
  showActionsMenu.value = false
  if (confirm('Ești sigur că vrei să ștergi acest review?')) {
    emit('delete', props.review)
  }
}

const handleReport = () => {
  showActionsMenu.value = false
  emit('report', props.review)
}

const handleHelpful = (isHelpful) => {
  // Toggle vote if clicking the same option
  if (helpfulVote.value === isHelpful) {
    helpfulVote.value = null
  } else {
    helpfulVote.value = isHelpful
  }

  emit('helpful', {
    review: props.review,
    helpful: helpfulVote.value
  })
}
</script>

<!-- Click outside directive (you may need to add this globally or use a library) -->
<script>
// Simple click outside implementation
const clickOutside = {
  beforeMount(el, binding) {
    el.clickOutsideEvent = function(event) {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}

export default {
  directives: {
    'click-outside': clickOutside
  }
}
</script>

<style scoped>
/* Smooth animations */
.transition-colors {
  transition: color 0.2s ease-in-out, background-color 0.2s ease-in-out;
}

/* Review card hover effect */
.review-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Actions menu animation */
.actions-menu {
  animation: fadeInScale 0.2s ease-out;
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
