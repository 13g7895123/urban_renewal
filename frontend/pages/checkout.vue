<template>
  <NuxtLayout name="main">
    <template #title>結帳</template>
    
    <div class="p-8">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
          <div class="flex items-center">
            <div class="bg-white/20 p-3 rounded-lg mr-4">
              <Icon name="heroicons:credit-card" class="w-8 h-8 text-white" />
            </div>
            <div>
              <h2 class="text-2xl font-semibold">確認訂單</h2>
              <p class="text-green-100 text-sm mt-1">請確認您的訂單內容</p>
            </div>
          </div>
        </div>

        <!-- Empty Cart Message -->
        <div v-if="cart.length === 0" class="text-center py-12">
          <Icon name="heroicons:shopping-cart" class="w-24 h-24 text-gray-300 mx-auto mb-4" />
          <h3 class="text-xl font-semibold text-gray-700 mb-2">購物車是空的</h3>
          <p class="text-gray-500 mb-6">還沒有選購任何商品</p>
          <UButton color="green" @click="router.push('/pages/shopping')">
            <Icon name="heroicons:shopping-bag" class="w-5 h-5 mr-2" />
            前往選購
          </UButton>
        </div>

        <!-- Order Summary -->
        <div v-else>
          <!-- Cart Items -->
          <UCard class="mb-6">
            <template #header>
              <h3 class="text-lg font-semibold">訂單明細</h3>
            </template>

            <div class="space-y-4">
              <div v-for="item in cart" :key="item.type" class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-4">
                  <div 
                    class="w-12 h-12 flex items-center justify-center rounded-lg"
                    :class="item.type === 'renewal' ? 'bg-green-100' : 'bg-blue-100'"
                  >
                    <Icon 
                      :name="item.type === 'renewal' ? 'heroicons:user-group' : 'heroicons:document-text'" 
                      class="w-6 h-6"
                      :class="item.type === 'renewal' ? 'text-green-600' : 'text-blue-600'"
                    />
                  </div>
                  <div>
                    <div class="font-semibold text-gray-800">{{ item.name }}</div>
                    <div class="text-sm text-gray-500">單價：NT$ {{ item.price.toLocaleString() }}</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-sm text-gray-500 mb-1">數量：{{ item.quantity }}</div>
                  <div class="text-lg font-bold text-gray-800">NT$ {{ (item.price * item.quantity).toLocaleString() }}</div>
                </div>
              </div>
            </div>

            <template #footer>
              <div class="space-y-2">
                <div class="flex justify-between items-center text-sm text-gray-600">
                  <span>小計</span>
                  <span>NT$ {{ totalAmount.toLocaleString() }}</span>
                </div>
                <div class="flex justify-between items-center text-sm text-gray-600">
                  <span>稅金</span>
                  <span>NT$ 0</span>
                </div>
                <div class="border-t pt-2 flex justify-between items-center">
                  <span class="text-lg font-bold text-gray-800">總計</span>
                  <span class="text-2xl font-bold text-green-600">NT$ {{ totalAmount.toLocaleString() }}</span>
                </div>
              </div>
            </template>
          </UCard>

          <!-- Payment Information -->
          <UCard class="mb-6">
            <template #header>
              <h3 class="text-lg font-semibold">付款資訊</h3>
            </template>

            <div class="space-y-4">
              <UAlert
                icon="heroicons:information-circle"
                color="blue"
                variant="soft"
                title="付款說明"
                description="目前僅支援線上信用卡付款，訂單送出後將導向付款頁面。"
              />

              <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex items-center gap-3 mb-3">
                  <Icon name="heroicons:credit-card" class="w-6 h-6 text-gray-600" />
                  <span class="font-semibold text-gray-800">信用卡付款</span>
                </div>
                <div class="text-sm text-gray-600 space-y-1">
                  <p>• 支援 Visa、Mastercard、JCB</p>
                  <p>• 使用安全加密連線</p>
                  <p>• 付款完成後立即開通服務</p>
                </div>
              </div>
            </div>
          </UCard>

          <!-- User Information -->
          <UCard class="mb-6">
            <template #header>
              <h3 class="text-lg font-semibold">訂購人資訊</h3>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">姓名</label>
                <div class="text-gray-800">{{ user?.name || '載入中...' }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <div class="text-gray-800">{{ user?.email || '載入中...' }}</div>
              </div>
            </div>
          </UCard>

          <!-- Action Buttons -->
          <div class="flex justify-between items-center gap-4">
            <UButton 
              color="gray" 
              variant="outline"
              size="lg"
              @click="goBack"
            >
              <Icon name="heroicons:arrow-left" class="w-5 h-5 mr-2" />
              返回繼續購物
            </UButton>
            
            <UButton 
              color="green" 
              size="lg"
              @click="submitOrder"
              :loading="isSubmitting"
              :disabled="isSubmitting"
            >
              <Icon name="heroicons:credit-card" class="w-5 h-5 mr-2" />
              確認購買並付款
            </UButton>
          </div>

          <!-- Terms Notice -->
          <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
              點擊「確認購買並付款」即表示您同意我們的
              <a href="#" class="text-green-600 hover:underline">服務條款</a>
              和
              <a href="#" class="text-green-600 hover:underline">隱私政策</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import Swal from 'sweetalert2'
import { useAuthStore } from '~/stores/auth'

definePageMeta({
  middleware: 'auth',  // 結帳頁面需要登入
  layout: false
})

const router = useRouter()
const authStore = useAuthStore()
const { cart, totalAmount, clearCart } = useShoppingCart()

const user = computed(() => authStore.user)
const isSubmitting = ref(false)

// 檢查購物車是否為空
onMounted(() => {
  if (cart.value.length === 0) {
    console.log('[Checkout] Cart is empty')
  }
})

const goBack = () => {
  router.push('/pages/shopping')
}

const submitOrder = async () => {
  try {
    isSubmitting.value = true

    // TODO: 實作訂單 API
    // 目前先模擬訂單建立
    console.log('[Checkout] Submitting order:', {
      user: user.value,
      items: cart.value,
      total: totalAmount.value
    })

    // 模擬 API 呼叫
    await new Promise(resolve => setTimeout(resolve, 1500))

    // 建立訂單編號
    const orderNumber = 'ORD' + Date.now()

    // 清空購物車
    clearCart()

    // 顯示成功訊息
    await Swal.fire({
      icon: 'success',
      title: '購買成功！',
      html: `
        <div class="text-left">
          <p class="mb-2"><strong>訂單編號：</strong>${orderNumber}</p>
          <p class="mb-2"><strong>付款金額：</strong>NT$ ${totalAmount.value.toLocaleString()}</p>
          <p class="text-sm text-gray-600 mt-4">
            服務已開通，您可以在購買紀錄中查看訂單詳情。
          </p>
        </div>
      `,
      confirmButtonText: '查看購買紀錄',
      confirmButtonColor: '#22c55e'
    })

    // 導向購買紀錄頁面
    router.push('/tables/order')

  } catch (error) {
    console.error('[Checkout] Order submission failed:', error)
    
    Swal.fire({
      icon: 'error',
      title: '購買失敗',
      text: error.message || '訂單處理失敗，請稍後再試',
      confirmButtonText: '確定'
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>
