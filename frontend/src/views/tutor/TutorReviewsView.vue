<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-6xl mx-auto" style="padding: 2rem 1rem;">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Recenzii</h1>
            <p class="text-gray-600 mt-1">Vezi ce spun studenții despre lecțiile tale</p>
          </div>
          <router-link
            :to="{ name: 'tutor-dashboard' }"
            class="bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
            style="padding: 0.5rem 1rem;"
          >
            ← Înapoi la dashboard
          </router-link>
        </div>
      </div>

      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200" style="padding: 1.5rem;">
          <div class="text-center">
            <div class="text-3xl font-bold text-purple-600 mb-2">{{ overallRating }}</div>
            <div class="flex justify-center items-center mb-2">
              <div v-for="i in 5" :key="i" class="w-5 h-5">
                <svg
                  class="w-5 h-5"
                  :class="i <= Math.round(overallRating) ? 'text-yellow-400' : 'text-gray-300'"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
              </div>
            </div>
            <p class="text-sm text-gray-600">Rating general</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200" style="padding: 1.5rem;">
          <div class="text-center">
            <div class="text-3xl font-bold text-blue-600 mb-2">{{ totalReviews }}</div>
            <p class="text-sm text-gray-600">Total recenzii</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200" style="padding: 1.5rem;">
          <div class="text-center">
            <div class="text-3xl font-bold text-green-600 mb-2">{{ positivePercentage }}%</div>
            <p class="text-sm text-gray-600">Recenzii pozitive</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200" style="padding: 1.5rem;">
          <div class="text-center">
            <div class="text-3xl font-bold text-orange-600 mb-2">{{ averageResponseTime }}h</div>
            <p class="text-sm text-gray-600">Timp răspuns mediu</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Reviews List -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
            <div class="border-b border-gray-100" style="padding: 1.5rem;">
              <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Recenzii recente</h2>
                <div class="flex items-center space-x-2">
                  <select
                    v-model="sortBy"
                    @change="sortReviews"
                    class="border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    style="padding: 0.5rem 0.75rem;"
                  >
                    <option value="newest">Cel mai recente</option>
                    <option value="oldest">Cel mai vechi</option>
                    <option value="highest">Rating cel mai mare</option>
                    <option value="lowest">Rating cel mai mic</option>
                  </select>
                </div>
              </div>
            </div>

            <div style="padding: 1.5rem;">
              <!-- Loading State -->
              <div v-if="loading" class="space-y-6">
                <div v-for="i in 5" :key="i" class="animate-pulse">
                  <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                    <div class="flex-1">
                      <div class="h-4 bg-gray-200 rounded mb-2"></div>
                      <div class="h-3 bg-gray-200 rounded w-2/3 mb-2"></div>
                      <div class="h-16 bg-gray-200 rounded"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- No Reviews -->
              <div v-else-if="sortedReviews.length === 0" class="text-center" style="padding: 3rem 0;">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nicio recenzie încă</h3>
                <p class="text-gray-600">Recenziile studenților vor apărea aici după lecții.</p>
              </div>

              <!-- Reviews List -->
              <div v-else class="space-y-6">
                <div
                  v-for="review in sortedReviews"
                  :key="review.id"
                  class="border border-gray-200 rounded-xl"
                  style="padding: 1.5rem;"
                >
                  <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-4">
                      <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-white font-semibold">
                        {{ getInitials(review.student.full_name) }}
                      </div>
                      <div>
                        <h4 class="font-semibold text-gray-900">{{ review.student.full_name }}</h4>
                        <p class="text-sm text-gray-600">{{ review.subject }}</p>
                        <p class="text-xs text-gray-500">{{ formatDate(review.date) }}</p>
                      </div>
                    </div>
                    <div class="flex items-center space-x-1">
                      <div v-for="i in 5" :key="i" class="w-4 h-4">
                        <svg
                          class="w-4 h-4"
                          :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-300'"
                          fill="currentColor"
                          viewBox="0 0 20 20"
                        >
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                      </div>
                      <span class="text-sm font-medium text-gray-600" style="margin-left: 0.5rem;">{{ review.rating }}/5</span>
                    </div>
                  </div>

                  <p class="text-gray-700 mb-4">{{ review.comment }}</p>

                  <!-- Tags from review -->
                  <div v-if="review.tags && review.tags.length > 0" class="flex flex-wrap gap-2 mb-4">
                    <span
                      v-for="tag in review.tags"
                      :key="tag"
                      class="bg-blue-100 text-blue-800 text-xs font-medium rounded-full"
                      style="padding: 0.25rem 0.5rem;"
                    >
                      {{ tag }}
                    </span>
                  </div>

                  <!-- Reply Section -->
                  <div v-if="review.reply" class="mt-4 bg-gray-50 rounded-lg" style="padding: 1rem;">
                    <div class="flex items-start space-x-3">
                      <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center text-white text-sm font-semibold">
                        Tu
                      </div>
                      <div class="flex-1">
                        <p class="text-sm text-gray-700">{{ review.reply }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ formatDate(review.replyDate) }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Reply Button -->
                  <div v-else class="mt-4">
                    <button
                      @click="showReplyForm(review.id)"
                      class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                    >
                      Răspunde la recenzie
                    </button>
                  </div>

                  <!-- Reply Form -->
                  <div v-if="activeReplyForm === review.id" class="mt-4 bg-gray-50 rounded-lg" style="padding: 1rem;">
                    <textarea
                      v-model="replyText"
                      rows="3"
                      placeholder="Scrie un răspuns..."
                      class="w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      style="padding: 0.75rem;"
                    ></textarea>
                    <div class="flex justify-end space-x-2 mt-3">
                      <button
                        @click="cancelReply"
                        class="text-gray-600 hover:text-gray-800"
                        style="padding: 0.5rem 1rem;"
                      >
                        Anulează
                      </button>
                      <button
                        @click="submitReply(review.id)"
                        :disabled="!replyText.trim() || submittingReply"
                        class="bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        style="padding: 0.5rem 1rem;"
                      >
                        {{ submittingReply ? 'Se trimite...' : 'Trimite răspuns' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Rating Breakdown -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200" style="padding: 1.5rem;">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribuția rating-urilor</h3>
            <div class="space-y-3">
              <div v-for="rating in [5, 4, 3, 2, 1]" :key="rating" class="flex items-center">
                <span class="text-sm text-gray-600 w-3">{{ rating }}</span>
                <svg class="w-4 h-4 text-yellow-400 fill-current" style="margin-left: 0.25rem;" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <div class="flex-1 bg-gray-200 rounded-full h-2" style="margin-left: 0.5rem;">
                  <div
                    class="bg-yellow-400 h-2 rounded-full transition-all duration-300"
                    :style="{ width: getRatingPercentage(rating) + '%' }"
                  />
                </div>
                <span class="text-sm text-gray-600 w-8" style="margin-left: 0.5rem;">
                  {{ getRatingCount(rating) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Most Mentioned Tags -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200" style="padding: 1.5rem;">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cel mai des menționat</h3>
            <div class="space-y-3">
              <div
                v-for="tag in topTags"
                :key="tag.name"
                class="flex items-center justify-between"
              >
                <span class="text-sm text-gray-700">{{ tag.name }}</span>
                <div class="flex items-center space-x-2">
                  <div class="w-16 bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-blue-400 h-2 rounded-full"
                      :style="{ width: (tag.count / topTags[0]?.count || 1) * 100 + '%' }"
                    />
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ tag.count }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Tips for Better Reviews -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200" style="padding: 1.5rem;">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sfaturi pentru recenzii mai bune</h3>
            <div class="space-y-4">
              <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Fii punctual</h4>
                  <p class="text-xs text-gray-600">Punctualitatea este foarte apreciată de studenți.</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.13 8.13 0 01-3.9-.96L5 20l1.94-4.1A8.13 8.13 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Comunică clar</h4>
                  <p class="text-xs text-gray-600">Explică conceptele pas cu pas și verifică înțelegerea.</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Oferă materiale</h4>
                  <p class="text-xs text-gray-600">Trimite exerciții sau resurse suplimentare.</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Răspunde la recenzii</h4>
                  <p class="text-xs text-gray-600">Mulțumește pentru feedback și arată că îți pasă.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Reactive data
const loading = ref(false)
const submittingReply = ref(false)
const activeReplyForm = ref(null)
const replyText = ref('')
const sortBy = ref('newest')

const reviews = ref([
  {
    id: 1,
    student: { full_name: 'Alexandra Munteanu' },
    rating: 5,
    comment: 'Explicații foarte clare și răbdare multă. Recomand cu încredere!',
    subject: 'Matematică',
    date: '2025-07-05',
    tags: ['Explicații clare', 'Răbdare', 'Punctualitate'],
    reply: null,
    replyDate: null
  },
  {
    id: 2,
    student: { full_name: 'Cristian Pavel' },
    rating: 4,
    comment: 'Profesor foarte bun, m-a ajutat mult la pregătirea pentru BAC. Îi mulțumesc!',
    subject: 'Fizică',
    date: '2025-07-03',
    tags: ['Pregătire BAC', 'Profesionalism'],
    reply: 'Mulțumesc pentru recenzie! A fost o plăcere să te ajut la pregătirea pentru BAC. Mult succes!',
    replyDate: '2025-07-04'
  },
  {
    id: 3,
    student: { full_name: 'Maria Ionescu' },
    rating: 5,
    comment: 'Cea mai bună profesoară de chimie! Explică totul foarte clar și are materiale excelente.',
    subject: 'Chimie',
    date: '2025-07-01',
    tags: ['Explicații clare', 'Materiale utile', 'Profesionalism'],
    reply: null,
    replyDate: null
  },
  {
    id: 4,
    student: { full_name: 'Andrei Popescu' },
    rating: 4,
    comment: 'Lecții foarte utile, am înțeles conceptele pe care nu le știam. Recomand!',
    subject: 'Matematică',
    date: '2025-06-28',
    tags: ['Înțelegere concepte', 'Utilitate'],
    reply: 'Îți mulțumesc, Andrei! Mă bucur că ai înțeles conceptele. Succes în continuare!',
    replyDate: '2025-06-29'
  }
])

// Computed properties
const overallRating = computed(() => {
  if (reviews.value.length === 0) return 0
  const sum = reviews.value.reduce((acc, review) => acc + review.rating, 0)
  return Math.round((sum / reviews.value.length) * 10) / 10
})

const totalReviews = computed(() => reviews.value.length)

const positivePercentage = computed(() => {
  if (reviews.value.length === 0) return 0
  const positiveReviews = reviews.value.filter(review => review.rating >= 4).length
  return Math.round((positiveReviews / reviews.value.length) * 100)
})

const averageResponseTime = computed(() => {
  return 2.4
})

const sortedReviews = computed(() => {
  const sorted = [...reviews.value]

  switch (sortBy.value) {
    case 'newest':
      return sorted.sort((a, b) => new Date(b.date) - new Date(a.date))
    case 'oldest':
      return sorted.sort((a, b) => new Date(a.date) - new Date(b.date))
    case 'highest':
      return sorted.sort((a, b) => b.rating - a.rating)
    case 'lowest':
      return sorted.sort((a, b) => a.rating - b.rating)
    default:
      return sorted
  }
})

const topTags = computed(() => {
  const tagCounts = {}

  reviews.value.forEach(review => {
    if (review.tags) {
      review.tags.forEach(tag => {
        tagCounts[tag] = (tagCounts[tag] || 0) + 1
      })
    }
  })

  return Object.entries(tagCounts)
    .map(([name, count]) => ({ name, count }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 6)
})

// Methods
const getInitials = (fullName) => {
  if (!fullName) return 'N/A'
  return fullName.split(' ').map(name => name[0]).join('').toUpperCase()
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ro-RO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getRatingCount = (rating) => {
  return reviews.value.filter(review => review.rating === rating).length
}

const getRatingPercentage = (rating) => {
  if (reviews.value.length === 0) return 0
  const count = getRatingCount(rating)
  return Math.round((count / reviews.value.length) * 100)
}

const sortReviews = () => {
  // Sorting is handled by computed property
}

const showReplyForm = (reviewId) => {
  activeReplyForm.value = reviewId
  replyText.value = ''
}

const cancelReply = () => {
  activeReplyForm.value = null
  replyText.value = ''
}

const submitReply = async (reviewId) => {
  if (!replyText.value.trim()) return

  submittingReply.value = true

  try {
    await new Promise(resolve => setTimeout(resolve, 1000))

    const review = reviews.value.find(r => r.id === reviewId)
    if (review) {
      review.reply = replyText.value
      review.replyDate = new Date().toISOString().split('T')[0]
    }

    activeReplyForm.value = null
    replyText.value = ''

    alert('Răspunsul a fost trimis cu succes!')
  } catch (error) {
    console.error('Error submitting reply:', error)
    alert('A apărut o eroare la trimiterea răspunsului.')
  } finally {
    submittingReply.value = false
  }
}

// Lifecycle
onMounted(() => {
  console.log('Reviews component mounted')
})
</script>
