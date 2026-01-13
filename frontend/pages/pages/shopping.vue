<template>
  <NuxtLayout name="main">
    <template #title>購買商品</template>
    
    <div class="p-8">
      <!-- Shopping Cart Info Banner -->
      <div v-if="itemCount > 0" class="mb-6">
        <UCard class="bg-green-50 border-green-200">
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <Icon name="heroicons:shopping-cart" class="w-6 h-6 text-green-600 mr-3" />
              <div>
                <div class="font-semibold text-green-900">購物車內有 {{ itemCount }} 件商品</div>
                <div class="text-sm text-green-700">總金額：NT$ {{ totalAmount.toLocaleString() }}</div>
              </div>
            </div>
            <UButton color="green" size="lg" @click="goToCheckout">
              <Icon name="heroicons:arrow-right" class="w-5 h-5 mr-2" />
              前往結帳
            </UButton>
          </div>
        </UCard>
      </div>

      <!-- Page Header -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center">
          <div class="bg-white/20 p-3 rounded-lg mr-4">
            <Icon name="heroicons:shopping-bag" class="w-8 h-8 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-semibold">購買商品</h2>
            <p class="text-green-100 text-sm mt-1">選擇您需要的服務方案</p>
          </div>
        </div>
      </div>
      
      <!-- Page Content -->
      <div class="flex justify-center items-center min-h-[50vh]">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl w-full">
          <!-- Add New Renewal Meeting Card -->
          <UCard class="bg-white shadow-lg hover:shadow-xl transition-shadow">
            <div class="flex flex-col items-center p-6">
              <h3 class="text-xl font-semibold text-gray-800 mb-4">增開更新會</h3>
              
              <div class="w-24 h-24 mb-4 flex items-center justify-center bg-green-100 rounded-full">
                <Icon name="heroicons:user-group" class="w-12 h-12 text-green-600" />
              </div>
              
              <div class="text-3xl font-bold text-gray-800 mb-2">NT$ 3,000</div>
              
              <p class="text-gray-600 text-center mb-6">增開更新會管理數量</p>
              
              <div v-if="hasItem('renewal')" class="w-full mb-4">
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                  <div class="text-sm text-green-700 mb-2">已在購物車</div>
                  <div class="flex items-center justify-center gap-3">
                    <UButton 
                      size="sm" 
                      color="green" 
                      variant="outline"
                      @click="decreaseQuantity('renewal')"
                      :disabled="getItemQuantity('renewal') <= 1"
                    >
                      <Icon name="heroicons:minus" class="w-4 h-4" />
                    </UButton>
                    <span class="font-semibold text-lg min-w-[2rem] text-center">{{ getItemQuantity('renewal') }}</span>
                    <UButton 
                      size="sm" 
                      color="green" 
                      variant="outline"
                      @click="increaseQuantity('renewal')"
                    >
                      <Icon name="heroicons:plus" class="w-4 h-4" />
                    </UButton>
                  </div>
                </div>
              </div>
              
              <UButton 
                color="green" 
                size="lg" 
                class="w-full"
                @click="addToCart('renewal')"
                :variant="hasItem('renewal') ? 'outline' : 'solid'"
              >
                <Icon name="heroicons:shopping-cart" class="w-5 h-5 mr-2" />
                {{ hasItem('renewal') ? '再加一個' : '加入購物車' }}
              </UButton>
            </div>
          </UCard>
          
          <!-- Add Issue Card -->
          <UCard class="bg-white shadow-lg hover:shadow-xl transition-shadow">
            <div class="flex flex-col items-center p-6">
              <h3 class="text-xl font-semibold text-gray-800 mb-4">增加議題</h3>
              
              <div class="w-24 h-24 mb-4 flex items-center justify-center bg-blue-100 rounded-full">
                <Icon name="heroicons:document-text" class="w-12 h-12 text-blue-600" />
              </div>
              
              <div class="text-3xl font-bold text-gray-800 mb-2">NT$ 1,000</div>
              
              <p class="text-gray-600 text-center mb-6">增加議題使用數量</p>
              
              <div v-if="hasItem('issue')" class="w-full mb-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                  <div class="text-sm text-blue-700 mb-2">已在購物車</div>
                  <div class="flex items-center justify-center gap-3">
                    <UButton 
                      size="sm" 
                      color="blue" 
                      variant="outline"
                      @click="decreaseQuantity('issue')"
                      :disabled="getItemQuantity('issue') <= 1"
                    >
                      <Icon name="heroicons:minus" class="w-4 h-4" />
                    </UButton>
                    <span class="font-semibold text-lg min-w-[2rem] text-center">{{ getItemQuantity('issue') }}</span>
                    <UButton 
                      size="sm" 
                      color="blue" 
                      variant="outline"
                      @click="increaseQuantity('issue')"
                    >
                      <Icon name="heroicons:plus" class="w-4 h-4" />
                    </UButton>
                  </div>
                </div>
              </div>
              
              <UButton 
                color="green" 
                size="lg" 
                class="w-full"
                @click="addToCart('issue')"
                :variant="hasItem('issue') ? 'outline' : 'solid'"
              >
                <Icon name="heroicons:shopping-cart" class="w-5 h-5 mr-2" />
                {{ hasItem('issue') ? '再加一個' : '加入購物車' }}
              </UButton>
            </div>
          </UCard>
        </div>
      </div>

      <!-- View Cart Button -->
      <div v-if="itemCount > 0" class="mt-8 text-center">
        <UButton color="gray" variant="outline" @click="toggleCartPreview">
          <Icon name="heroicons:eye" class="w-5 h-5 mr-2" />
          查看購物車內容
        </UButton>
      </div>

      <!-- Cart Preview Modal -->
      <UModal v-model="showCartPreview">
        <UCard>
          <template #header>
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold">購物車內容</h3>
              <UButton color="gray" variant="ghost" icon="heroicons:x-mark" @click="showCartPreview = false" />
            </div>
          </template>

          <div class="space-y-4">
            <div v-for="item in cart" :key="item.type" class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-4">
                <Icon 
                  :name="item.type === 'renewal' ? 'heroicons:user-group' : 'heroicons:document-text'" 
                  class="w-8 h-8"
                  :class="item.type === 'renewal' ? 'text-green-600' : 'text-blue-600'"
                />
                <div>
                  <div class="font-semibold">{{ item.name }}</div>
                  <div class="text-sm text-gray-500">NT$ {{ item.price.toLocaleString() }} × {{ item.quantity }}</div>
                </div>
              </div>
              <div class="flex items-center gap-4">
                <div class="text-lg font-bold">NT$ {{ (item.price * item.quantity).toLocaleString() }}</div>
                <UButton 
                  color="red" 
                  variant="ghost" 
                  size="sm"
                  @click="removeFromCart(item.type)"
                >
                  <Icon name="heroicons:trash" class="w-4 h-4" />
                </UButton>
              </div>
            </div>

            <div class="border-t pt-4">
              <div class="flex justify-between items-center text-lg font-bold">
                <span>總計</span>
                <span class="text-green-600">NT$ {{ totalAmount.toLocaleString() }}</span>
              </div>
            </div>
          </div>

          <template #footer>
            <div class="flex justify-end gap-3">
              <UButton color="gray" variant="outline" @click="showCartPreview = false">
                繼續購物
              </UButton>
              <UButton color="green" @click="goToCheckout">
                <Icon name="heroicons:arrow-right" class="w-5 h-5 mr-2" />
                前往結帳
              </UButton>
            </div>
          </template>
        </UCard>
      </UModal>
    </div>
  </NuxtLayout>
</template>

<script setup>
import Swal from 'sweetalert2'

definePageMeta({
  layout: false
})

const router = useRouter()
const { 
  cart, 
  itemCount, 
  totalAmount, 
  addToCart: addCartItem,
  updateQuantity,
  removeFromCart: removeCartItem,
  hasItem,
  getItemQuantity
} = useShoppingCart()

const showCartPreview = ref(false)

const products = {
  renewal: {
    type: 'renewal',
    name: '增開更新會',
    price: 3000,
    quantity: 1
  },
  issue: {
    type: 'issue',
    name: '增加議題',
    price: 1000,
    quantity: 1
  }
}

const addToCart = (type) => {
  const product = products[type]
  addCartItem(product)
  
  Swal.fire({
    icon: 'success',
    title: '已加入購物車',
    text: `${product.name} 已加入購物車`,
    timer: 1500,
    showConfirmButton: false
  })
}

const increaseQuantity = (type) => {
  const currentQty = getItemQuantity(type)
  updateQuantity(type, currentQty + 1)
}

const decreaseQuantity = (type) => {
  const currentQty = getItemQuantity(type)
  if (currentQty > 1) {
    updateQuantity(type, currentQty - 1)
  }
}

const removeFromCart = async (type) => {
  const result = await Swal.fire({
    icon: 'warning',
    title: '確定要移除嗎？',
    text: '此商品將從購物車中移除',
    showCancelButton: true,
    confirmButtonText: '確定移除',
    cancelButtonText: '取消',
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280'
  })

  if (result.isConfirmed) {
    removeCartItem(type)
    Swal.fire({
      icon: 'success',
      title: '已移除',
      text: '商品已從購物車移除',
      timer: 1500,
      showConfirmButton: false
    })
  }
}

const toggleCartPreview = () => {
  showCartPreview.value = !showCartPreview.value
}

const goToCheckout = () => {
  router.push('/checkout')
}
</script>