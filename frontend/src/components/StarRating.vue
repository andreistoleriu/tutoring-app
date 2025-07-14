<template>
  <div class="flex items-center space-x-1">
    <!-- Stars -->
    <div class="flex space-x-1">
      <button
        v-for="star in maxStars"
        :key="star"
        type="button"
        @click="handleStarClick(star)"
        @mouseover="handleMouseOver(star)"
        @mouseleave="handleMouseLeave"
        :disabled="readonly || disabled"
        :class="[
          'transition-all duration-200',
          interactive ? 'cursor-pointer hover:scale-110 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 rounded' : 'cursor-default',
          disabled ? 'opacity-50 cursor-not-allowed' : ''
        ]"
      >
        <svg
          :class="[
            'transition-colors duration-200',
            getStarClass(star),
            size === 'sm' ? 'w-4 h-4' : size === 'lg' ? 'w-8 h-8' : 'w-5 h-5'
          ]"
          viewBox="0 0 24 24"
        >
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
      </button>
    </div>

    <!-- Rating Text -->
    <div v-if="showText" :class="textClass">
      <span v-if="showNumeric" class="font-semibold">
        {{ displayRating }}/{{ maxStars }}
      </span>
      <span v-if="showLabel" :class="showNumeric ? 'ml-1' : ''">
        {{ getRatingLabel(currentRating) }}
      </span>
      <span v-if="showCount && count > 0" class="text-gray-500 ml-1">
        ({{ count }})
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

// Props
const props = defineProps({
  // Core props
  modelValue: {
    type: Number,
    default: 0
  },
  maxStars: {
    type: Number,
    default: 5
  },

  // Behavior props
  readonly: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  allowHalf: {
    type: Boolean,
    default: false
  },
  clearable: {
    type: Boolean,
    default: false
  },

  // Appearance props
  size: {
    type: String,
    default: 'md', // sm, md, lg
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },

  // Text display props
  showText: {
    type: Boolean,
    default: false
  },
  showNumeric: {
    type: Boolean,
    default: false
  },
  showLabel: {
    type: Boolean,
    default: false
  },
  showCount: {
    type: Boolean,
    default: false
  },
  count: {
    type: Number,
    default: 0
  },
  textClass: {
    type: String,
    default: 'text-sm text-gray-600'
  },

  // Custom labels
  labels: {
    type: Object,
    default: () => ({
      1: 'Foarte slab',
      2: 'Slab',
      3: 'Decent',
      4: 'Bun',
      5: 'Excelent'
    })
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'change', 'hover'])

// State
const hoverRating = ref(0)

// Computed
const interactive = computed(() => !props.readonly && !props.disabled)

const currentRating = computed(() => {
  return hoverRating.value || props.modelValue || 0
})

const displayRating = computed(() => {
  const rating = props.modelValue || 0
  return props.allowHalf ? rating.toFixed(1) : Math.round(rating)
})

// Methods
const getStarClass = (star) => {
  const rating = currentRating.value
  const isFilled = star <= Math.floor(rating)
  const isHalfFilled = props.allowHalf && star === Math.ceil(rating) && rating % 1 !== 0

  if (isFilled) {
    return 'text-yellow-400 fill-current'
  } else if (isHalfFilled) {
    return 'text-yellow-400 fill-current opacity-50'
  } else {
    return 'text-gray-300 fill-current'
  }
}

const getRatingLabel = (rating) => {
  if (!rating) return 'Neevaluat'

  const roundedRating = Math.round(rating)
  return props.labels[roundedRating] || 'Neevaluat'
}

const handleStarClick = (star) => {
  if (!interactive.value) return

  let newRating = star

  // If clearable and clicking the same star, clear the rating
  if (props.clearable && props.modelValue === star) {
    newRating = 0
  }

  emit('update:modelValue', newRating)
  emit('change', newRating)
}

const handleMouseOver = (star) => {
  if (!interactive.value) return

  hoverRating.value = star
  emit('hover', star)
}

const handleMouseLeave = () => {
  if (!interactive.value) return

  hoverRating.value = 0
  emit('hover', 0)
}

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  // Reset hover when modelValue changes externally
  hoverRating.value = 0
})
</script>

<style scoped>
/* Additional hover effects */
.hover\:scale-110:hover {
  transform: scale(1.1);
}

/* Focus ring customization */
.focus\:ring-yellow-500:focus {
  --tw-ring-color: rgb(245 158 11);
}

.focus\:ring-offset-2:focus {
  --tw-ring-offset-width: 2px;
}

/* Smooth transitions */
svg {
  transition: color 0.2s ease-in-out, fill 0.2s ease-in-out, transform 0.2s ease-in-out;
}
</style>
