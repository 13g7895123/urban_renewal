import { ref, computed, onMounted } from 'vue'

export const useShoppingCart = () => {
  const cart = useState('shopping_cart', () => [])

  // 從 localStorage 載入購物車
  const loadCart = () => {
    if (process.client) {
      const savedCart = localStorage.getItem('shopping_cart')
      if (savedCart) {
        try {
          cart.value = JSON.parse(savedCart)
          console.log('[Shopping Cart] Loaded cart from localStorage:', cart.value)
        } catch (error) {
          console.error('[Shopping Cart] Failed to parse saved cart:', error)
          cart.value = []
        }
      }
    }
  }

  // 儲存購物車到 localStorage
  const saveCart = () => {
    if (process.client) {
      localStorage.setItem('shopping_cart', JSON.stringify(cart.value))
      console.log('[Shopping Cart] Saved cart to localStorage:', cart.value)
    }
  }

  // 加入商品
  const addToCart = (product) => {
    const existingItem = cart.value.find(item => item.type === product.type)

    if (existingItem) {
      existingItem.quantity++
      console.log('[Shopping Cart] Updated quantity:', existingItem)
    } else {
      cart.value.push({ ...product })
      console.log('[Shopping Cart] Added new item:', product)
    }

    saveCart()
  }

  // 更新商品數量
  const updateQuantity = (type, quantity) => {
    const item = cart.value.find(item => item.type === type)
    if (item && quantity > 0) {
      item.quantity = quantity
      saveCart()
      console.log('[Shopping Cart] Updated quantity for', type, ':', quantity)
    }
  }

  // 移除商品
  const removeFromCart = (type) => {
    const index = cart.value.findIndex(item => item.type === type)
    if (index !== -1) {
      const removedItem = cart.value.splice(index, 1)
      saveCart()
      console.log('[Shopping Cart] Removed item:', removedItem)
    }
  }

  // 清空購物車
  const clearCart = () => {
    cart.value = []
    saveCart()
    console.log('[Shopping Cart] Cart cleared')
  }

  // 計算總金額
  const totalAmount = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
  })

  // 商品數量
  const itemCount = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.quantity, 0)
  })

  // 檢查是否有特定商品
  const hasItem = (type) => {
    return cart.value.some(item => item.type === type)
  }

  // 取得特定商品數量
  const getItemQuantity = (type) => {
    const item = cart.value.find(item => item.type === type)
    return item ? item.quantity : 0
  }

  // 初始化時載入購物車
  if (process.client && cart.value.length === 0) {
    loadCart()
  }

  return {
    cart,
    addToCart,
    updateQuantity,
    removeFromCart,
    clearCart,
    totalAmount,
    itemCount,
    hasItem,
    getItemQuantity,
    loadCart
  }
}
