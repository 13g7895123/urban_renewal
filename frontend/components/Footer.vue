<template>
  <footer class="footer-section bg-white border-t border-gray-200 py-4">
    <div class="container mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
        <div class="md:col-span-4"></div>

        <div class="md:col-span-4 flex flex-col gap-8 md:flex-row items-center justify-center">
          <label class="text-4xl font-bold">開發建經</label>
          <div class="contact-info space-y-2 text-sm text-gray-600 text-center md:text-left">
            <div class="flex items-center justify-center md:justify-start">
              <Icon name="heroicons:phone" class="w-4 h-4 mr-2" />
              服務電話: 02-6604-3889
            </div>
            <div class="flex items-center justify-center md:justify-start">
              <Icon name="heroicons:printer" class="w-4 h-4 mr-2" />
              服務傳真: 02-2585-3900
            </div>
            <div class="flex items-center justify-center md:justify-start whitespace-nowrap">
              <Icon name="heroicons:envelope" class="w-4 h-4 mr-2" />
              服務信箱: K80686159@gmail.com
            </div>
          </div>
        </div>
        
        <div class="md:col-span-4 text-center md:text-right">
          <div class="copyright text-xs text-gray-400">
            {{ copyrightText }}
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  logoStyle?: string
  copyrightText?: string
}>(), {
  logoStyle: 'width: auto;',
  copyrightText: '© 2024 都更計票系統'
})
</script>

<style scoped>
.footer-logo {
  border-radius: 8px;
}

.footer-section {
  background-color: white !important;
}
</style>