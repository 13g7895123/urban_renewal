# 購物頁面公開存取評估報告

## 📋 評估概要

本報告分析將購物頁面 (`/pages/shopping`) 改為不需登入即可存取的可行性與調整方案。

---

## 🔍 現況分析

### 當前實作狀況

#### 1. 購物頁面 (`frontend/pages/pages/shopping.vue`)

**位置**：`/pages/shopping`

**當前內容**：
- 兩個商品卡片：
  1. 增開更新會 - $3000
  2. 增加議題 - $1000
- 加入購物車按鈕（目前為 TODO 狀態）

**當前狀態**：
```vue
definePageMeta({
  layout: false
})

const addToCart = (type) => {
  console.log(`Adding ${type} to cart`)
  // TODO: Implement cart functionality
}
```

**中介層設定**：
- ❌ **未設定** `middleware: 'auth'`
- ✅ **理論上已經可以不登入存取**

---

#### 2. 購買紀錄頁面 (`frontend/pages/tables/order.vue`)

**位置**：`/tables/order`

**當前內容**：
- 訂單列表（目前為空資料狀態）
- 包含訂單編號、訂購內容、總金額、下單時間

**當前狀態**：
```vue
definePageMeta({
  layout: false
})

const orders = ref([])  // 空陣列
```

**中介層設定**：
- ❌ **未設定** `middleware: 'auth'`
- ✅ **理論上已經可以不登入存取**

---

### 導航列設定

**檔案**：`frontend/layouts/main.vue`

```javascript
{
  label: '購買',
  path: '/pages/shopping',
  icon: 'heroicons:shopping-bag',
  requiresAuth: false  // ← 推測（需確認）
},
{
  label: '購買紀錄',
  path: '/tables/order',
  icon: 'heroicons:document-text',
  requiresAuth: false  // ← 推測（需確認）
}
```

---

## ✅ 好消息：購物頁面已可公開存取！

經過分析，**購物頁面目前沒有設定任何認證中介層**，理論上已經可以不登入存取。

但為了確保完整的使用者體驗，建議進行以下調整：

---

## 🔧 建議調整方案

### 方案 A：完全公開存取（推薦）

**適用場景**：讓訪客瀏覽商品並加入購物車，結帳時才要求登入

#### 需要調整的項目

#### 1. 購物車功能實作

**目標**：未登入使用者可以加入購物車，資料暫存於 LocalStorage/SessionStorage

```vue
// frontend/pages/pages/shopping.vue

<script setup>
import { useShoppingCart } from '~/composables/useShoppingCart'

const { addToCart: addCartItem } = useShoppingCart()

const addToCart = (type) => {
  const product = {
    type,
    name: type === 'renewal' ? '增開更新會' : '增加議題',
    price: type === 'renewal' ? 3000 : 1000,
    quantity: 1
  }
  
  addCartItem(product)
  
  // 顯示成功訊息
  showSuccess('已加入購物車', `${product.name} 已加入購物車`)
}
</script>
```

#### 2. 建立購物車 Composable

**新檔案**：`frontend/composables/useShoppingCart.js`

```javascript
export const useShoppingCart = () => {
  const cart = useState('cart', () => [])
  
  // 從 localStorage 載入購物車
  const loadCart = () => {
    if (process.client) {
      const savedCart = localStorage.getItem('shopping_cart')
      if (savedCart) {
        cart.value = JSON.parse(savedCart)
      }
    }
  }
  
  // 儲存購物車到 localStorage
  const saveCart = () => {
    if (process.client) {
      localStorage.setItem('shopping_cart', JSON.stringify(cart.value))
    }
  }
  
  // 加入商品
  const addToCart = (product) => {
    const existingItem = cart.value.find(item => item.type === product.type)
    
    if (existingItem) {
      existingItem.quantity++
    } else {
      cart.value.push(product)
    }
    
    saveCart()
  }
  
  // 移除商品
  const removeFromCart = (type) => {
    cart.value = cart.value.filter(item => item.type !== type)
    saveCart()
  }
  
  // 清空購物車
  const clearCart = () => {
    cart.value = []
    saveCart()
  }
  
  // 計算總金額
  const totalAmount = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
  })
  
  // 商品數量
  const itemCount = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.quantity, 0)
  })
  
  // 初始化
  onMounted(() => {
    loadCart()
  })
  
  return {
    cart,
    addToCart,
    removeFromCart,
    clearCart,
    totalAmount,
    itemCount,
    loadCart
  }
}
```

#### 3. 建立結帳頁面（需登入）

**新檔案**：`frontend/pages/checkout.vue`

```vue
<template>
  <NuxtLayout name="main">
    <template #title>結帳</template>
    
    <div class="p-8">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold mb-6">確認訂單</h2>
        
        <!-- 購物車清單 -->
        <UCard class="mb-6">
          <div v-for="item in cart" :key="item.type" class="flex justify-between items-center p-4 border-b last:border-b-0">
            <div>
              <div class="font-semibold">{{ item.name }}</div>
              <div class="text-sm text-gray-500">數量：{{ item.quantity }}</div>
            </div>
            <div class="text-lg font-bold">${{ item.price * item.quantity }}</div>
          </div>
          
          <div class="flex justify-between items-center p-4 bg-gray-50">
            <div class="text-lg font-bold">總計</div>
            <div class="text-2xl font-bold text-green-600">${{ totalAmount }}</div>
          </div>
        </UCard>
        
        <!-- 結帳按鈕 -->
        <div class="flex justify-end gap-4">
          <UButton color="gray" @click="goBack">返回</UButton>
          <UButton color="green" size="lg" @click="submitOrder">
            <Icon name="heroicons:credit-card" class="w-5 h-5 mr-2" />
            確認購買
          </UButton>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
definePageMeta({
  middleware: 'auth',  // 結帳頁面需要登入
  layout: false
})

const { cart, totalAmount, clearCart } = useShoppingCart()
const router = useRouter()

const goBack = () => {
  router.push('/pages/shopping')
}

const submitOrder = async () => {
  // TODO: 實作訂單 API
  console.log('Submitting order:', cart.value)
  
  // 清空購物車
  clearCart()
  
  // 導向購買紀錄頁面
  showSuccess('購買成功', '訂單已送出')
  router.push('/tables/order')
}
</script>
```

#### 4. 在購物頁面加入前往結帳按鈕

```vue
<!-- frontend/pages/pages/shopping.vue -->

<template>
  <NuxtLayout name="main">
    <template #title>議題管理_會議選擇</template>
    
    <div class="p-8">
      <!-- 購物車資訊 -->
      <div v-if="itemCount > 0" class="mb-6 flex justify-end">
        <UButton color="green" size="lg" @click="goToCheckout">
          <Icon name="heroicons:shopping-cart" class="w-5 h-5 mr-2" />
          前往結帳 ({{ itemCount }} 件商品，${{ totalAmount }})
        </UButton>
      </div>
      
      <!-- 商品卡片 -->
      <div class="flex justify-center items-center min-h-[60vh]">
        <!-- ... 現有商品卡片 ... -->
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
const { itemCount, totalAmount, addToCart: addCartItem } = useShoppingCart()
const router = useRouter()

const addToCart = (type) => {
  const product = {
    type,
    name: type === 'renewal' ? '增開更新會' : '增加議題',
    price: type === 'renewal' ? 3000 : 1000,
    quantity: 1
  }
  
  addCartItem(product)
  showSuccess('已加入購物車', `${product.name} 已加入購物車`)
}

const goToCheckout = () => {
  router.push('/checkout')
}
</script>
```

#### 5. 購買紀錄頁面（需登入查看）

**調整**：`frontend/pages/tables/order.vue`

```vue
<script setup>
definePageMeta({
  middleware: 'auth',  // 加入登入驗證
  layout: false
})

// 新增：從 API 取得訂單資料
const { data: ordersData, pending } = await useAsyncData('orders', async () => {
  // TODO: 實作訂單 API
  // const { data } = await useApi().get('/orders')
  // return data
  return []
})

const orders = computed(() => ordersData.value || [])
</script>
```

---

### 方案 B：部分公開（選配）

**適用場景**：購物頁面需要特定權限存取

#### 調整方式

1. **訪客可瀏覽，但無法加入購物車**

```vue
<script setup>
const { isLoggedIn } = useAuthStore()

const addToCart = (type) => {
  if (!isLoggedIn) {
    showWarning('請先登入', '登入後才能加入購物車')
    router.push('/login')
    return
  }
  
  // 正常加入購物車流程
  // ...
}
</script>
```

2. **限定已購買特定產品的使用者才能看到購物頁面**

```vue
<script setup>
definePageMeta({
  middleware: ['auth', 'has-subscription']  // 自訂中介層
})
</script>
```

---

## 🗂️ 需要建立的後端 API

### 1. 訂單 API

```php
// backend/app/Controllers/Api/OrderController.php

/**
 * 建立訂單
 * POST /api/orders
 */
public function create()
{
    $user = auth_validate_request();
    
    $data = $this->request->getJSON(true);
    
    // 驗證
    $validation = \Config\Services::validation();
    $validation->setRules([
        'items' => 'required|is_array',
        'total_amount' => 'required|numeric'
    ]);
    
    if (!$validation->run($data)) {
        return response_error('資料驗證失敗', 400, $validation->getErrors());
    }
    
    // 建立訂單
    $orderModel = model('OrderModel');
    $orderId = $orderModel->createOrder($user['id'], $data);
    
    return response_success('訂單建立成功', ['order_id' => $orderId], 201);
}

/**
 * 取得使用者訂單列表
 * GET /api/orders
 */
public function index()
{
    $user = auth_validate_request();
    
    $orderModel = model('OrderModel');
    $orders = $orderModel->getUserOrders($user['id']);
    
    return response_success('訂單列表', $orders);
}
```

### 2. 資料庫 Schema

```sql
-- 訂單主檔
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 訂單明細
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_type ENUM('renewal', 'issue') NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📋 實作清單

### Phase 1：基礎功能（最小可行產品）

- [ ] 確認購物頁面可公開存取（已完成）
- [ ] 建立 `useShoppingCart` composable
- [ ] 實作購物車功能（加入/移除/清空）
- [ ] 在購物頁面顯示購物車狀態
- [ ] 建立結帳頁面（需登入）
- [ ] 加入「前往結帳」按鈕

### Phase 2：後端整合

- [ ] 建立訂單資料表
- [ ] 實作訂單 API (`OrderController`)
- [ ] 建立訂單 Model (`OrderModel`)
- [ ] 整合金流（第三方支付）

### Phase 3：購買紀錄

- [ ] 購買紀錄頁面加入登入驗證
- [ ] 串接訂單 API
- [ ] 顯示使用者訂單歷史
- [ ] 訂單詳細資訊頁面

### Phase 4：進階功能

- [ ] 購物車數量角標（導航列顯示）
- [ ] 購物車側欄彈窗
- [ ] 優惠碼功能
- [ ] 訂單狀態追蹤
- [ ] 發票開立
- [ ] Email 通知

---

## ⚠️ 注意事項

### 1. 安全性考量

- ✅ **購物頁面可公開**：純展示商品，無敏感資訊
- ✅ **購物車在客戶端**：使用 localStorage，未登入也可使用
- ⚠️ **結帳必須登入**：防止匿名訂單
- ⚠️ **訂單驗證**：後端必須驗證商品價格，不可信任前端傳來的金額
- ⚠️ **CSRF 防護**：確保訂單 API 有 CSRF token 保護

### 2. 使用者體驗

#### 未登入使用者流程
```
瀏覽商品 → 加入購物車 → 點擊結帳 → 登入頁面 → 結帳頁面 → 完成購買
```

#### 已登入使用者流程
```
瀏覽商品 → 加入購物車 → 點擊結帳 → 結帳頁面 → 完成購買
```

### 3. 購物車資料處理

**登入前後的購物車合併**：

```javascript
// composables/useShoppingCart.js

const mergeGuestCart = async () => {
  const guestCart = localStorage.getItem('shopping_cart')
  
  if (guestCart && authStore.isLoggedIn) {
    // 將訪客購物車合併到使用者購物車
    const guestItems = JSON.parse(guestCart)
    
    // TODO: 呼叫 API 合併購物車
    // await api.post('/cart/merge', { items: guestItems })
    
    // 清除訪客購物車
    localStorage.removeItem('shopping_cart')
  }
}
```

---

## 🎯 建議採用的方案

### **推薦：方案 A（完全公開存取）**

**理由**：
1. ✅ 降低進入門檻，提升轉換率
2. ✅ 訪客可先瀏覽商品，增加購買意願
3. ✅ 符合一般電商網站的使用者體驗
4. ✅ 實作相對簡單，風險低

**實作優先順序**：
1. **高優先**：購物車功能（Phase 1）
2. **中優先**：後端訂單系統（Phase 2）
3. **中優先**：購買紀錄（Phase 3）
4. **低優先**：進階功能（Phase 4）

---

## 📊 預估工作量

| 階段 | 工作項目 | 預估時間 |
|------|----------|----------|
| Phase 1 | 購物車 Composable | 2-3 小時 |
| Phase 1 | 結帳頁面 | 2-3 小時 |
| Phase 1 | UI 調整 | 1-2 小時 |
| Phase 2 | 訂單資料表 | 1 小時 |
| Phase 2 | 訂單 API | 3-4 小時 |
| Phase 2 | 金流整合 | 8-12 小時 |
| Phase 3 | 購買紀錄 API 串接 | 2-3 小時 |
| **總計** | **基礎功能（不含金流）** | **11-16 小時** |
| **總計** | **含金流整合** | **19-28 小時** |

---

## 📝 總結

購物頁面**目前已經可以不登入存取**，但需要補齊購物車與結帳流程才能提供完整的購物體驗。

建議採用**方案 A（完全公開存取）**，優先實作 Phase 1 與 Phase 2，提供基本的購物功能，金流整合可視需求延後處理。

---

**文件版本**：1.0  
**最後更新**：2026-01-13  
**維護者**：開發團隊
