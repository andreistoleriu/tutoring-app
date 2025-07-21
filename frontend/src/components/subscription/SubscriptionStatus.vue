<template>
  <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-200/50 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Abonament</h3>
    <div class="text-center py-4">
      <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center">
        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
      </div>
      <p class="text-gray-600 text-sm">Subscription feature in development</p>
    </div>
  </div>
</template>

<script setup>
// Minimal component to prevent import errors
defineEmits(['upgrade', 'manage'])
</script>
