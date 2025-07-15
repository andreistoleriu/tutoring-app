<!-- frontend/src/components/ReviewModal.vue -->
<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
    @click.self="closeModal"
  >
    <div class="bg-white rounded-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEditing ? 'Actualizează Review-ul' : 'Lasă un Review' }}
        </h3>
        <button
          @click="closeModal"
          :disabled="submitting"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-6">
        <!-- Booking Info -->
        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
          <h4 class="font-medium text-gray-900">Detalii Rezervare</h4>
          <div class="text-sm text-gray-600 space-y-1">
            <p><span class="font-medium">Tutor:</span> {{ booking.tutor.first_name }} {{ booking.tutor.last_name }}</p>
            <p><span class="font-medium">Materia:</span> {{ booking.subject.name }}</p>
            <p><span class="font-medium">Data:</span> {{ formatDate(booking.scheduled_at) }}</p>
            <p><span class="font-medium">Durată:</span> {{ booking.duration_minutes }} minute</p>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex">
            <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <p class="text-red-800">{{ error }}</p>
          </div>
        </div>

        <!-- Review Form -->
        <form @submit.prevent="submitReview" class="space-y-6">
          <!-- Rating -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">
              Evaluare <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center space-x-1">
              <button
                v-for="star in 5"
                :key="star"
                type="button"
                @click="setRating(star)"
                :disabled="submitting"
                class="p-1 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded transition-colors"
                :class="{
                  'text-yellow-400': star <= reviewForm.rating,
                  'text-gray-300': star > reviewForm.rating,
                  'hover:text-yellow-400': !submitting
                }"
              >
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </button>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              {{ getRatingText(reviewForm.rating) }}
            </p>
          </div>

          <!-- Comment -->
          <div>
            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
              Comentariu (opțional)
            </label>
            <textarea
              id="comment"
              v-model="reviewForm.comment"
              :disabled="submitting"
              rows="4"
              maxlength="1000"
              placeholder="Împărtășește experiența ta cu acest tutor..."
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                     focus:ring-blue-500 focus:border-blue-500 resize-none
                     disabled:bg-gray-100 disabled:cursor-not-allowed"
            />
            <div class="flex justify-between mt-1">
              <p class="text-xs text-gray-500">Maxim 1000 de caractere</p>
              <p class="text-xs text-gray-500">{{ reviewForm.comment.length }}/1000</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex space-x-3 pt-4">
            <!-- Delete Button (only when editing) -->
            <button
              v-if="isEditing"
              type="button"
              @click="deleteReview"
              :disabled="submitting"
              class="flex-1 bg-red-600 text-white py-3 px-4 rounded-lg font-medium
                     hover:bg-red-700 focus:ring-4 focus:ring-red-200
                     disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ submitting ? 'Se șterge...' : 'Șterge Review' }}
            </button>

            <!-- Cancel Button -->
            <button
              type="button"
              @click="closeModal"
              :disabled="submitting"
              class="flex-1 bg-gray-300 text-gray-700 py-3 px-4 rounded-lg font-medium
                     hover:bg-gray-400 focus:ring-4 focus:ring-gray-200
                     disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Anulează
            </button>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="!isFormValid || submitting"
              class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium
                     hover:bg-blue-700 focus:ring-4 focus:ring-blue-200
                     disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ submitting ? 'Se trimite...' : (isEditing ? 'Actualizează' : 'Trimite Review') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useStudentStore } from '@/stores/student'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
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
const submitting = ref(false)
const error = ref('')

const reviewForm = reactive({
  rating: 0,
  comment: ''
})

// Computed
const isEditing = computed(() => !!props.existingReview)

const isFormValid = computed(() => {
  return reviewForm.rating > 0 && reviewForm.rating <= 5
})

// Methods
const setRating = (rating) => {
  if (!submitting.value) {
    reviewForm.rating = rating
  }
}

const getRatingText = (rating) => {
  const texts = {
    1: 'Foarte nesatisfăcut',
    2: 'Nesatisfăcut',
    3: 'Neutru',
    4: 'Satisfăcut',
    5: 'Foarte satisfăcut'
  }
  return texts[rating] || 'Selectează o evaluare'
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('ro-RO', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const submitReview = async () => {
  if (!isFormValid.value) return

  submitting.value = true
  error.value = ''

  try {
    console.log('📝 Submitting review for booking:', props.booking.id)
    console.log('📝 Review data:', {
      booking_id: props.booking.id,
      rating: reviewForm.rating,
      comment: reviewForm.comment || null
    })

    const reviewData = {
      booking_id: props.booking.id,  // ✅ Ensure booking_id is always sent
      rating: reviewForm.rating,
      comment: reviewForm.comment.trim() || null
    }

    let response
    if (isEditing.value) {
      response = await studentStore.updateReview(props.existingReview.id, reviewData)
    } else {
      response = await studentStore.submitReview(reviewData)
    }

    console.log('✅ Review submitted successfully:', response)

    emit('success', {
      action: isEditing.value ? 'updated' : 'created',
      review: response.review || response
    })

    closeModal()

  } catch (err) {
    console.error('❌ Error submitting review:', err)
    error.value = err.response?.data?.message || err.message || 'A apărut o eroare la trimiterea review-ului'
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

    closeModal()

  } catch (err) {
    console.error('❌ Error deleting review:', err)
    error.value = err.response?.data?.message || 'A apărut o eroare la ștergerea review-ului'
  } finally {
    submitting.value = false
  }
}

const closeModal = () => {
  if (!submitting.value) {
    resetForm()
    emit('close')
  }
}

const resetForm = () => {
  reviewForm.rating = 0
  reviewForm.comment = ''
  error.value = ''
}

// Lifecycle
onMounted(() => {
  console.log('📋 Review modal mounted with booking:', props.booking)
  console.log('📋 Existing review:', props.existingReview)

  // Pre-fill form if editing existing review
  if (props.existingReview) {
    reviewForm.rating = props.existingReview.rating
    reviewForm.comment = props.existingReview.comment || ''
  }
})

// Watch for prop changes
watch(() => props.existingReview, (newReview) => {
  if (newReview) {
    reviewForm.rating = newReview.rating
    reviewForm.comment = newReview.comment || ''
  } else {
    resetForm()
  }
}, { immediate: true })

watch(() => props.isOpen, (isOpen) => {
  if (!isOpen) {
    resetForm()
  }
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

/* Focus styles for accessibility */
button:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

textarea:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}
</style>
