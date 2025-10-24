<template>
  <footer class="footer-section bg-white border-t border-gray-200 py-4">
    <div class="container mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
        <div class="md:col-span-4"></div>
        
        <div class="md:col-span-4 flex flex-col md:flex-row items-center justify-center">
          <img src="/assets/images/logo.jpg" alt="Logo" class="footer-logo" :style="logoStyle" />
          <div class="contact-info space-y-2 text-sm text-gray-600 text-center md:text-left">
            <div class="flex items-center justify-center md:justify-start">
              <Icon name="heroicons:phone" class="w-4 h-4 mr-2" />
              服務電話: (02)-8913-6162
            </div>
            <div class="flex items-center justify-center md:justify-start">
              <Icon name="heroicons:printer" class="w-4 h-4 mr-2" />
              服務傳真: (02)-3393-0761
            </div>
            <div class="flex items-center justify-center md:justify-start whitespace-nowrap">
              <Icon name="heroicons:envelope" class="w-4 h-4 mr-2" />
              服務信箱: service@都更平台.tw
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