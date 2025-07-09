<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Recenzii</h1>
            <p class="text-gray-600 mt-1">Vezi toate recenziile primite de la studenți</p>
          </div>
          <div class="flex items-center space-x-4">
            <router-link
              :to="{ name: 'tutor-dashboard' }"
              class="bg-white border border-gray-300 text-gray-700 font-medium px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors"
            >
              ← Înapoi la dashboard
            </router-link>
            <button
              @click="refreshReviews"
              class="bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold px-6 py-3 rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              Reîmprospătează
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <span class="ml-3 text-gray-600">Se încarcă recenziile...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Eroare la încărcarea recenziilor</h3>
            <p class="text-sm text-red-700 mt-1">{{ error }}</p>
          </div>
          <div class="ml-auto">
            <button
              @click="refreshReviews"
              class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors"
            >
              Încearcă din nou
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div v-else>
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="text-center">
              <div class="flex items-center justify-center mb-3">
                <div class="text-3xl font-bold text-yellow-600">{{ overallRating }}</div>
                <div class="flex items-center ml-2">
                  <svg
                    v-for="star in 5"
                    :key="star"
                    class="w-5 h-5"
                    :class="star <= Math.round(overallRating) ? 'text-yellow-400' : 'text-gray-300'"
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

          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="text-center">
              <div class="text-3xl font-bold text-blue-600 mb-2">{{ totalReviews }}</div>
              <p class="text-sm text-gray-600">Total recenzii</p>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="text-center">
              <div class="text-3xl font-bold text-green-600 mb-2">{{ positivePercentage }}%</div>
              <p class="text-sm text-gray-600">Recenzii pozitive</p>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
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
              <div class="border-b border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                  <h2 class="text-xl font-semibold text-gray-900">Toate recenziile</h2>
                  <div class="flex items-center space-x-4">
                    <!-- Search -->
                    <div class="relative">
                      <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Caută în recenzii..."
                        class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      >
                      <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                      </svg>
                    </div>
                  </div>
                </div>

                <div class="flex items-center space-x-4">
                  <!-- Sort -->
                  <select
                    v-model="sortBy"
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    <option value="newest">Cele mai noi</option>
                    <option value="oldest">Cele mai vechi</option>
                    <option value="highest">Rating cel mai mare</option>
                    <option value="lowest">Rating cel mai mic</option>
                  </select>

                  <!-- Filter by rating -->
                  <select
                    v-model="filterByRating"
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    <option value="all">Toate rating-urile</option>
                    <option value="5">5 stele</option>
                    <option value="4">4 stele</option>
                    <option value="3">3 stele</option>
                    <option value="2">2 stele</option>
                    <option value="1">1 stea</option>
                  </select>
                </div>
              </div>

              <div class="p-6">
                <!-- No Reviews -->
                <div v-if="filteredAndSortedReviews.length === 0" class="text-center py-12">
                  <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                  </div>
                  <h3 class="text-lg font-medium text-gray-900 mb-2">Nicio recenzie</h3>
                  <p class="text-gray-600">Recenziile vor apărea aici după ce studenții îți vor evalua lecțiile.</p>
                </div>

                <!-- Reviews List -->
                <div v-else class="space-y-6">
                  <div
                    v-for="review in filteredAndSortedReviews"
                    :key="review.id"
                    class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow"
                  >
                    <div class="flex items-center space-x-3 mb-4">
                      <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">{{ getInitials(getSafeName(review)) }}</span>
                      </div>
                      <div>
                        <h4 class="font-semibold text-gray-900">{{ getSafeName(review) }}</h4>
                        <p class="text-sm text-gray-600">{{ review.subject?.name || 'Materie necunoscută' }}</p>
                      </div>
                      <div class="ml-auto text-right">
                        <div class="flex items-center mb-1">
                          <svg
                            v-for="star in 5"
                            :key="star"
                            class="w-4 h-4"
                            :class="star <= review.rating ? 'text-yellow-400' : 'text-gray-300'"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                          >
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                          </svg>
                        </div>
                        <span class="text-sm text-gray-600">{{ formatDate(review.created_at) }}</span>
                      </div>
                    </div>

                    <div class="mb-4">
                      <p class="text-gray-700 leading-relaxed">{{ review.comment }}</p>
                    </div>

                    <!-- Tutor Reply -->
                    <div v-if="review.reply" class="bg-blue-50 rounded-lg p-4 mt-4">
                      <div class="flex items-center mb-2">
                        <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                        <span class="text-sm font-semibold text-blue-800">Răspunsul tău</span>
                        <span class="text-xs text-blue-600 ml-2">{{ formatDate(review.reply_date) }}</span>
                      </div>
                      <p class="text-blue-700 text-sm">{{ review.reply }}</p>
                    </div>

                    <!-- Reply Form -->
                    <div v-else-if="activeReplyForm === review.id" class="mt-4">
                      <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Răspunde la recenzie</label>
                        <textarea
                          v-model="replyText"
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="Scrie răspunsul tău aici..."
                        ></textarea>
                        <div class="flex items-center justify-end space-x-3 mt-3">
                          <button
                            @click="cancelReply"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                          >
                            Anulează
                          </button>
                          <button
                            @click="submitReply(review.id)"
                            :disabled="!replyText.trim() || submittingReply"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                          >
                            <span v-if="submittingReply">Se trimite...</span>
                            <span v-else>Trimite răspuns</span>
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- Reply Button -->
                    <div v-else class="mt-4">
                      <button
                        @click="showReplyForm(review.id)"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors"
                      >
                        Răspunde la recenzie
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="mt-8 flex items-center justify-center space-x-2">
                  <button
                    @click="changePage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Anterior
                  </button>

                  <span class="px-3 py-2 text-sm text-gray-700">
                    Pagina {{ currentPage }} din {{ totalPages }}
                  </span>

                  <button
                    @click="changePage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Următorul
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Sidebar -->
          <div class="space-y-6">
            <!-- Rating Breakdown -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribuția rating-urilor</h3>
              <div class="space-y-3">
                <div v-for="rating in [5, 4, 3, 2, 1]" :key="rating" class="flex items-center">
                  <span class="text-sm font-medium text-gray-600 w-3">{{ rating }}</span>
                  <svg class="w-4 h-4 text-yellow-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                  <div class="flex-1 mx-2">
                    <div class="bg-gray-200 rounded-full h-2">
                      <div
                        class="bg-yellow-400 h-2 rounded-full"
                        :style="{ width: getRatingPercentage(rating) + '%' }"
                      ></div>
                    </div>
                  </div>
                  <span class="text-sm text-gray-600 w-8">{{ getRatingCount(rating) }}</span>
                </div>
              </div>
            </div>

            <!-- Top Tags -->
            <div v-if="topTags.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Cuvinte frecvente</h3>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="tag in topTags"
                  :key="tag.name"
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
                >
                  {{ tag.name }}
                  <span class="ml-1 text-xs bg-blue-200 text-blue-800 rounded-full px-2 py-0.5">{{ tag.count }}</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useTutorStore } from '@/stores/tutor'

const tutorStore = useTutorStore()

// Reactive state
const reviews = ref([])
const loading = ref(false)
const error = ref(null)

// Sorting and filtering
const sortBy = ref('newest')
const filterByRating = ref('all')
const searchQuery = ref('')

// Reply functionality
const activeReplyForm = ref(null)
const replyText = ref('')
const submittingReply = ref(false)

// Pagination
const currentPage = ref(1)
const totalPages = ref(1)
const totalReviews = ref(0)
const perPage = ref(15)

// Computed properties
const overallRating = computed(() => {
  if (reviews.value.length === 0) return 0
  const sum = reviews.value.reduce((acc, review) => acc + review.rating, 0)
  return Math.round((sum / reviews.value.length) * 10) / 10
})

const positivePercentage = computed(() => {
  if (reviews.value.length === 0) return 0
  const positiveReviews = reviews.value.filter(review => review.rating >= 4).length
  return Math.round((positiveReviews / reviews.value.length) * 100)
})

const averageResponseTime = computed(() => {
  // Calculate based on actual data
  const reviewsWithReplies = reviews.value.filter(r => r.reply && r.reply_date)
  if (reviewsWithReplies.length === 0) return 0

  let totalHours = 0
  reviewsWithReplies.forEach(review => {
    const reviewDate = new Date(review.created_at)
    const replyDate = new Date(review.reply_date)
    const diffHours = (replyDate - reviewDate) / (1000 * 60 * 60)
    totalHours += diffHours
  })

  return Math.round((totalHours / reviewsWithReplies.length) * 10) / 10
})

const filteredAndSortedReviews = computed(() => {
  let filtered = [...reviews.value]

  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(review =>
      review.comment?.toLowerCase().includes(query) ||
      getSafeName(review).toLowerCase().includes(query) ||
      review.subject?.name?.toLowerCase().includes(query)
    )
  }

  // Filter by rating
  if (filterByRating.value !== 'all') {
    const rating = parseInt(filterByRating.value)
    filtered = filtered.filter(review => review.rating === rating)
  }

  // Sort
  switch (sortBy.value) {
    case 'newest':
      return filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    case 'oldest':
      return filtered.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
    case 'highest':
      return filtered.sort((a, b) => b.rating - a.rating)
    case 'lowest':
      return filtered.sort((a, b) => a.rating - b.rating)
    default:
      return filtered
  }
})

const topTags = computed(() => {
  // Extract common positive words from comments as tags
  const tagCounts = {}
  const commonWords = ['excelent', 'bun', 'foarte', 'profesionist', 'răbdător', 'clar', 'utilă', 'recomandat', 'înțeles', 'ajutat']

  reviews.value.forEach(review => {
    if (review.comment) {
      const words = review.comment.toLowerCase().split(/\s+/)
      commonWords.forEach(word => {
        if (words.some(w => w.includes(word))) {
          tagCounts[word] = (tagCounts[word] || 0) + 1
        }
      })
    }
  })

  return Object.entries(tagCounts)
    .map(([name, count]) => ({ name, count }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 6)
})

// Helper methods
const getSafeName = (item) => {
  if (item?.student?.first_name && item?.student?.last_name) {
    return `${item.student.first_name} ${item.student.last_name}`
  }
  if (item?.student?.full_name) return item.student.full_name
  if (item?.student?.first_name) return item.student.first_name
  if (item?.student?.name) return item.student.name
  if (item?.student_name) return item.student_name
  return 'Student'
}

const getInitials = (fullName) => {
  if (!fullName || typeof fullName !== 'string') return 'S'
  const nameParts = fullName.trim().split(' ').filter(part => part.length > 0)
  if (nameParts.length === 0) return 'S'
  if (nameParts.length === 1) return nameParts[0][0].toUpperCase()
  return nameParts[0][0].toUpperCase() + nameParts[nameParts.length - 1][0].toUpperCase()
}

const formatDate = (dateString) => {
  if (!dateString) return ''
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

// Actions
const loadReviews = async (page = 1) => {
  loading.value = true
  error.value = null

  try {
    const params = {
      page,
      per_page: perPage.value
    }

    console.log('Loading reviews with params:', params)

    const response = await tutorStore.getReviews(params)

    reviews.value = response.reviews || []
    currentPage.value = response.pagination?.current_page || 1
    totalPages.value = response.pagination?.last_page || 1
    totalReviews.value = response.pagination?.total || reviews.value.length

    console.log('Reviews loaded:', {
      count: reviews.value.length,
      pagination: response.pagination
    })

    // Log first review structure for debugging
    if (reviews.value.length > 0) {
      console.log('First review structure:', reviews.value[0])
    }

  } catch (err) {
    error.value = err.message || 'Eroare la încărcarea recenziilor'
    console.error('Error loading reviews:', err)
  } finally {
    loading.value = false
  }
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
    await tutorStore.submitReply(reviewId, replyText.value)

    // Update local state
    const review = reviews.value.find(r => r.id === reviewId)
    if (review) {
      review.reply = replyText.value
      review.reply_date = new Date().toISOString()
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

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
    loadReviews(page)
  }
}

const refreshReviews = () => {
  loadReviews(currentPage.value)
}

// Watch for filter/sort changes to update display
watch([sortBy, filterByRating, searchQuery], () => {
  // Filters are handled by computed property, no need to reload from API
  console.log('Filters changed:', {
    sortBy: sortBy.value,
    filterByRating: filterByRating.value,
    searchQuery: searchQuery.value
  })
})

// Lifecycle
onMounted(() => {
  console.log('Reviews component mounted, loading reviews...')
  loadReviews()
})
</script>

