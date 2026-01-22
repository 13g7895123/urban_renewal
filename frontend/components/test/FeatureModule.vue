<template>
  <div class="bg-white rounded-lg shadow-md mb-4">
    <div 
      class="p-4 cursor-pointer hover:bg-gray-50 flex justify-between items-center"
      @click="$emit('toggle')"
    >
      <div class="flex items-center gap-3">
        <svg 
          class="w-5 h-5 transition-transform"
          :class="expanded ? 'rotate-90' : ''"
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <h2 class="text-xl font-bold">{{ title }}</h2>
      </div>
      <div class="text-sm text-gray-500">
        {{ expanded ? '點擊收合' : '點擊展開' }}
      </div>
    </div>
    
    <div v-show="expanded" class="border-t">
      <div class="p-4 space-y-4">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  expanded: {
    type: Boolean,
    default: false
  }
})

defineEmits(['toggle'])
</script>
