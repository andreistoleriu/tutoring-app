<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    @click="$emit('close')">

    <div
      class="bg-white rounded-2xl shadow-2xl w-full max-w-md"
      @click.stop>

      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Subscription</h3>
          <button
            @click="$emit('close')"
            class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="p-6 text-center">
        <p class="text-gray-600 mb-4">Subscription feature in development</p>
        <button
          @click="$emit('close')"
          class="w-full text-gray-600 hover:text-gray-800 py-2 transition-colors">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// Minimal component to prevent import errors
defineProps({
  show: Boolean
})

defineEmits(['close', 'upgraded'])
</script>
