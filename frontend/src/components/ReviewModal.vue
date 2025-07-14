<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="closeModal">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.stop>
      <!-- Header -->
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-xl font-bold text-gray-900">
              {{ isEditMode ? 'Editează review-ul' : 'Evaluează lecția' }}
            </h3>
            <p class="text-gray-600 mt-1">
              {{ booking.subject?.name }} cu {{ getTutorName(booking.tutor) }}
            </p>
          </div>
          <button
            @click="closeModal"
            class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6">
        <!-- Booking Info -->
        <div class="bg-gray-50 rounded-xl p-4 mb-6">
          <div class="flex items-center space-x-4">
            <!-- Tutor Avatar -->
            <div class="flex-shrink-0">
              <img
                v-if="booking.tutor?.profile_image"
                :src="booking.tutor.profile_image"
                :alt="getTutorName(booking.tutor)"
                class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm"
              >
              <div
                v-else
                class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white font-semibold shadow-sm"
              >
                {{ getInitials(getTutorName(booking.tutor)) }}
              </div>
            </div>

            <!-- Booking Details -->
            <div class="flex-1">
              <h4 class="font-semibold text-gray-900">{{ booking.subject?.name }}</h4>
              <p class="text-sm text-gray-600">cu {{ getTutorName(booking.tutor) }}</p>
              <div class="flex items-center space-x-4 mt-1 text-sm text-gray-500">
                <span class="flex items-center space-x-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  <span>{{ formatDate(booking.completed_at || booking.scheduled_at) }}</span>
                </span>
                <span class="flex items-center space-x-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                  <span>{{ booking.price }} RON</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Review Form -->
        <form @submit.prevent="submitReview">
          <!-- Rating Section -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">
              Cum evaluezi această lecție? *
            </label>
            <div class="flex items-center space-x-2">
              <!-- Star Rating -->
              <div class="flex space-x-1">
                <button
                  v-for="star in 5"
                  :key="star"
                  type="button"
                  @click="reviewForm.rating = star"
                  @mouseover="hoverRating = star"
                  @mouseleave="hoverRating = 0"
                  class="focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 rounded transition-transform hover:scale-110"
                >
                  <svg
                    class="w-8 h-8 transition-colors"
                    :class="(hoverRating ? star <= hoverRating : star <= reviewForm.rating)
                      ? 'text-yellow-400 fill-current'
                      : 'text-gray-300'"
                    viewBox="0 0 24 24"
                  >
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                  </svg>
                </button>
              </div>

              <!-- Rating Text -->
              <div class="ml-4">
                <span class="text-lg font-semibold text-gray-900">
                  {{ reviewForm.rating || 0 }}/5
                </span>
                <p class="text-sm text-gray-600">
                  {{ getRatingText(reviewForm.rating) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Comment Section -->
          <div class="mb-6">
            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
              Comentariu (opțional)
            </label>
            <textarea
              id="comment"
              v-model="reviewForm.comment"
              rows="4"
              maxlength="1000"
              placeholder="Scrie aici părerea ta despre lecție, tutore și experiența avută..."
              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
            ></textarea>
            <div class="flex justify-between items-center mt-2">
              <p class="text-xs text-gray-500">
                Comentariul tău va fi vizibil public și va ajuta alți studenți să aleagă tutorul potrivit.
              </p>
              <span class="text-xs text-gray-400">
                {{ reviewForm.comment?.length || 0 }}/1000
              </span>
            </div>
          </div>

          <!-- Quick Review Templates -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">
              Sau alege un template rapid:
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <button
                v-for="template in reviewTemplates"
                :key="template.rating"
                type="button"
                @click="applyTemplate(template)"
                class="p-3 text-left border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors"
              >
                <div class="flex items-center mb-1">
                  <div class="flex space-x-1 mr-2">
                    <svg
                      v-for="star in 5"
                      :key="star"
                      class="w-4 h-4"
                      :class="star <= template.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'"
                      viewBox="0 0 24 24"
                    >
                      <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ template.title }}</span>
                </div>
                <p class="text-xs text-gray-600">{{ template.comment.substring(0, 60) }}...</p>
              </button>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <span class="text-red-800 text-sm">{{ error }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-between space-x-4">
            <button
              type="button"
              @click="closeModal"
              class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium"
            >
              Anulează
            </button>

            <div class="flex space-x-3">
              <!-- Delete Button (if editing existing review) -->
              <button
                v-if="isEditMode && existingReview"
                type="button"
                @click="deleteReview"
                :disabled="submitting"
                class="px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Șterge
              </button>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="!isFormValid || submitting"
                class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 font-medium disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
              >
                <span v-if="submitting" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Se trimite...
                </span>
                <span v-else>
                  {{ isEditMode ? 'Actualizează' : 'Trimite review-ul' }}
                </span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useStudentStore } from '@/stores/student'

// Props
const props = defineProps({
  booking: {
    type: Object,
    required: true
  },
  existingReview: {
    type: Object,
    default: null
  }
})

// Emits
const emit = defineEmits(['close', 'success'])

// Composables
const studentStore = useStudentStore()

// State
const reviewForm = ref({
  rating: 0,
  comment: ''
})

const hoverRating = ref(0)
const submitting = ref(false)
const error = ref('')

// Computed
const isEditMode = computed(() => !!props.existingReview)

const isFormValid = computed(() => {
  return reviewForm.value.rating > 0
})

// Review templates for quick selection
const reviewTemplates = ref([
  {
    rating: 5,
    title: 'Excelent',
    comment: 'Lecție foarte bună! Tutorul a explicat foarte clar și a fost foarte răbdător. Recomand cu încredere!'
  },
  {
    rating: 4,
    title: 'Foarte bun',
    comment: 'Experiență pozitivă. Tutorul cunoaște bine materia și explică accesibil. O să mai rezerv lecții.'
  },
  {
    rating: 3,
    title: 'Decent',
    comment: 'Lecție OK, dar ar putea fi îmbunătățită. Tutorul știe materia dar explicațiile ar putea fi mai clare.'
  },
  {
    rating: 2,
    title: 'Sub așteptări',
    comment: 'Nu am fost foarte mulțumit de lecție. Tutorul părea nepregătit și explicațiile nu au fost foarte clare.'
  }
])

// Methods
const getTutorName = (tutor) => {
  if (!tutor) return 'Tutor necunoscut'

  if (tutor.first_name || tutor.last_name) {
    return `${tutor.first_name || ''} ${tutor.last_name || ''}`.trim()
  }

  return tutor.full_name || tutor.name || 'Tutor necunoscut'
}

const getInitials = (name) => {
  if (!name) return 'NA'
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

const getRatingText = (rating) => {
  const ratingTexts = {
    1: 'Foarte slab',
    2: 'Slab',
    3: 'Decent',
    4: 'Bun',
    5: 'Excelent'
  }
  return ratingTexts[rating] || 'Selectează rating-ul'
}

const applyTemplate = (template) => {
  reviewForm.value.rating = template.rating
  reviewForm.value.comment = template.comment
}

const submitReview = async () => {
  if (!isFormValid.value || submitting.value) return

  submitting.value = true
  error.value = ''

  try {
    const reviewData = {
      booking_id: props.booking.id,
      rating: reviewForm.value.rating,
      comment: reviewForm.value.comment
    }

    if (isEditMode.value) {
      await studentStore.updateReview(props.existingReview.id, {
        rating: reviewForm.value.rating,
        comment: reviewForm.value.comment
      })
    } else {
      await studentStore.submitReview(reviewData)
    }

    emit('success', {
      action: isEditMode.value ? 'updated' : 'created',
      review: reviewData
    })

  } catch (err) {
    console.error('Error submitting review:', err)
    error.value = err.response?.data?.message || 'A apărut o eroare la trimiterea review-ului'
  } finally {
    submitting.value = false
  }
}

const deleteReview = async () => {
  if (!confirm('Ești sigur că vrei să ștergi acest review?')) return

  submitting.value = true
  error.value = ''

  try {
    await studentStore.deleteReview(props.existingReview.id)

    emit('success', {
      action: 'deleted',
      review: props.existingReview
    })

  } catch (err) {
    console.error('Error deleting review:', err)
    error.value = err.response?.data?.message || 'A apărut o eroare la ștergerea review-ului'
  } finally {
    submitting.value = false
  }
}

const closeModal = () => {
  if (!submitting.value) {
    emit('close')
  }
}

// Lifecycle
onMounted(() => {
  // Pre-fill form if editing existing review
  if (props.existingReview) {
    reviewForm.value.rating = props.existingReview.rating
    reviewForm.value.comment = props.existingReview.comment || ''
  }
})

// Watch for prop changes
watch(() => props.existingReview, (newReview) => {
  if (newReview) {
    reviewForm.value.rating = newReview.rating
    reviewForm.value.comment = newReview.comment || ''
  }
}, { immediate: true })
</script>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
