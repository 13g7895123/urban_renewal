<template>
  <NuxtLayout name="auth">
    <template #title>無權限訪問</template>

    <div class="flex items-center justify-center py-12">
      <UCard class="w-full max-w-md text-center">
        <template #header>
          <div class="flex justify-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center">
              <Icon name="heroicons:shield-exclamation" class="w-12 h-12 text-red-500" />
            </div>
          </div>
        </template>

        <div class="space-y-4">
          <h2 class="text-2xl font-bold text-gray-800">無權限訪問</h2>
          <p class="text-gray-600">
            抱歉，您沒有權限訪問此頁面。
          </p>
          <p class="text-sm text-gray-500">
            如果您認為這是錯誤，請聯繫系統管理員。
          </p>
        </div>

        <template #footer>
          <div class="flex justify-center gap-4">
            <UButton
              @click="goBack"
              variant="outline"
              color="gray"
            >
              <Icon name="heroicons:arrow-left" class="w-4 h-4 mr-2" />
              返回上一頁
            </UButton>
            <UButton
              @click="goHome"
              color="primary"
            >
              <Icon name="heroicons:home" class="w-4 h-4 mr-2" />
              返回首頁
            </UButton>
          </div>
        </template>
      </UCard>
    </div>
  </NuxtLayout>
</template>

<script setup>
const router = useRouter()

const goBack = () => {
  router.back()
}

const goHome = () => {
  navigateTo('/')
}
</script>

<style scoped>
/* Additional styles if needed */
</style>
