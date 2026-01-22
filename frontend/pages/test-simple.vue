<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-4">簡單測試頁面</h1>
    <p>如果你看到這段文字，表示頁面可以正常渲染。</p>
    
    <div class="mt-4 bg-blue-100 p-4 rounded">
      <h2 class="font-bold">測試區域</h2>
      <p>當前時間: {{ currentTime }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

definePageMeta({
  layout: false,
  middleware: []
})

const currentTime = ref(new Date().toLocaleString())
</script>
