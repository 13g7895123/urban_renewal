<template>
  <NuxtLayout name="main">
    <template #title>議題管理_會議選擇</template>
    
    <div class="p-8">
      <!-- Page Content -->
      <div class="flex justify-center items-center min-h-[60vh]">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl w-full">
          <!-- Add New Renewal Meeting Card -->
          <UCard class="bg-white shadow-lg">
            <div class="flex flex-col items-center p-6">
              <h3 class="text-xl font-semibold text-gray-800 mb-4">增開更新會</h3>
              
              <div class="w-24 h-24 mb-4 flex items-center justify-center bg-gray-100 rounded-full">
                <Icon name="heroicons:user-group" class="w-12 h-12 text-gray-600" />
              </div>
              
              <div class="text-3xl font-bold text-gray-800 mb-2">$3000</div>
              
              <p class="text-gray-600 text-center mb-6">增開更新會管理數量</p>
              
              <UButton 
                color="green" 
                size="lg" 
                class="w-full"
                @click="addToCart('renewal')"
              >
                <Icon name="heroicons:shopping-cart" class="w-5 h-5 mr-2" />
                加入購物車
              </UButton>
            </div>
          </UCard>
          
          <!-- Add Issue Card -->
          <UCard class="bg-white shadow-lg">
            <div class="flex flex-col items-center p-6">
              <h3 class="text-xl font-semibold text-gray-800 mb-4">增加議題</h3>
              
              <div class="w-24 h-24 mb-4 flex items-center justify-center bg-gray-100 rounded-full">
                <Icon name="heroicons:document-text" class="w-12 h-12 text-gray-600" />
              </div>
              
              <div class="text-3xl font-bold text-gray-800 mb-2">$1000</div>
              
              <p class="text-gray-600 text-center mb-6">增加議題使用數量</p>
              
              <UButton 
                color="green" 
                size="lg" 
                class="w-full"
                @click="addToCart('issue')"
              >
                <Icon name="heroicons:shopping-cart" class="w-5 h-5 mr-2" />
                加入購物車
              </UButton>
            </div>
          </UCard>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
definePageMeta({
  layout: false
})

const addToCart = (type) => {
  console.log(`Adding ${type} to cart`)
  // TODO: Implement cart functionality
}
</script>