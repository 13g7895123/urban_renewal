<template>
  <BackgroundImage>
    <div class="min-h-screen flex items-center justify-center p-6">
      <div class="text-center max-w-md mx-auto">
        <!-- Error card -->
        <div class="bg-white rounded-lg shadow-lg p-12">
          <!-- Error code -->
          <div class="mb-6">
            <h1 class="text-8xl font-bold text-green-600 leading-none">
              {{ error.statusCode }}
            </h1>
          </div>

          <!-- Error message -->
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">
              頁面未找到
            </h2>
            <p class="text-gray-600">
              抱歉，您所訪問的頁面不存在或已被移動。
            </p>
          </div>

          <!-- Action buttons -->
          <div class="space-y-4">
            <UButton 
              @click="handleError"
              color="green"
              size="lg"
              block
              class="font-semibold"
            >
              返回首頁
            </UButton>
          </div>
        </div>
      </div>
    </div>
    
    <Footer />
  </BackgroundImage>
</template>

<script setup>
defineProps({
  error: Object
})

const handleError = async () => {
  await navigateTo('/')
}
</script>