# 前端測試指南

## 測試架構

```
tests/
├── composables/         # Composable 測試
├── stores/             # Store 測試
├── components/         # 組件測試（待建立）
├── utils/              # 工具函數測試
│   └── test-helpers.ts # 測試輔助函數
└── setup.ts            # 測試環境設置
```

## 執行測試

### 執行所有測試
```bash
cd frontend
npm run test
```

### 執行測試並觀察變化
```bash
npm run test
```

### 執行單次測試
```bash
npm run test:run
```

### 生成覆蓋率報告
```bash
npm run test:coverage
```

## 測試環境設置

測試環境已在 `tests/setup.ts` 中配置：
- Mock $fetch
- Mock navigateTo
- Mock useRoute
- Mock useRuntimeConfig
- Mock localStorage

## 編寫測試

### Store 測試範例

```typescript
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useYourStore } from '~/stores/your-store'

describe('Your Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('should initialize correctly', () => {
    const store = useYourStore()
    expect(store.someState).toBeDefined()
  })

  it('should update state', () => {
    const store = useYourStore()
    store.updateSomething('new value')
    expect(store.someState).toBe('new value')
  })
})
```

### Composable 測試範例

```typescript
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'

describe('useYourComposable', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('should work correctly', () => {
    // 測試邏輯
    expect(true).toBe(true)
  })
})
```

### 組件測試範例

```typescript
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import YourComponent from '~/components/YourComponent.vue'

describe('YourComponent', () => {
  it('should render correctly', () => {
    const wrapper = mount(YourComponent, {
      props: {
        title: 'Test Title'
      }
    })

    expect(wrapper.text()).toContain('Test Title')
  })

  it('should emit event on click', async () => {
    const wrapper = mount(YourComponent)
    
    await wrapper.find('button').trigger('click')

    expect(wrapper.emitted('click')).toBeTruthy()
  })
})
```

## 測試輔助函數

使用 `tests/utils/test-helpers.ts` 中的輔助函數：

```typescript
import { createMockUser, createMockApiResponse } from '../utils/test-helpers'

// 建立 mock 使用者
const user = createMockUser({ role: 'admin' })

// 建立 mock API 響應
const response = createMockApiResponse({ user })
```

## Mock 技巧

### Mock $fetch

```typescript
global.$fetch = vi.fn().mockResolvedValue({
  success: true,
  data: { /* your data */ }
})
```

### Mock 失敗的 API 請求

```typescript
global.$fetch = vi.fn().mockRejectedValue(
  new Error('API Error')
)
```

### Mock Composable

```typescript
vi.mock('~/composables/useYourComposable', () => ({
  useYourComposable: () => ({
    data: ref({ value: 'mocked' }),
    fetch: vi.fn()
  })
}))
```

## 測試最佳實踐

1. **描述性測試名稱**：清楚描述測試內容
2. **獨立性**：每個測試應該獨立
3. **清理**：使用 beforeEach 清理狀態
4. **AAA 模式**：Arrange（準備）、Act（執行）、Assert（斷言）
5. **測試覆蓋率**：目標 70%+ 覆蓋率

## 常用斷言

```typescript
// 相等性
expect(actual).toBe(expected)
expect(actual).toEqual(expected) // 深度比較
expect(actual).not.toBe(expected)

// 真值
expect(value).toBeTruthy()
expect(value).toBeFalsy()
expect(value).toBeNull()
expect(value).toBeUndefined()

// 類型
expect(value).toBeInstanceOf(Class)
expect(typeof value).toBe('string')

// 陣列/物件
expect(array).toContain(item)
expect(array).toHaveLength(3)
expect(object).toHaveProperty('key')

// 函數
expect(fn).toHaveBeenCalled()
expect(fn).toHaveBeenCalledWith(arg1, arg2)
expect(fn).toHaveBeenCalledTimes(2)

// 異步
await expect(promise).resolves.toBe(value)
await expect(promise).rejects.toThrow(Error)
```

## 組件測試技巧

### 測試 Props

```typescript
const wrapper = mount(Component, {
  props: { title: 'Test' }
})
expect(wrapper.props('title')).toBe('Test')
```

### 測試 Emits

```typescript
await wrapper.find('button').trigger('click')
expect(wrapper.emitted('submit')).toBeTruthy()
expect(wrapper.emitted('submit')[0]).toEqual([expectedData])
```

### 測試插槽

```typescript
const wrapper = mount(Component, {
  slots: {
    default: '<div>Slot Content</div>'
  }
})
expect(wrapper.html()).toContain('Slot Content')
```

## 持續整合

在 CI/CD 中執行測試：

```yaml
# .github/workflows/tests.yml
name: Frontend Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup Node.js
        uses: actions/setup-node@v2
        with:
          node-version: '18'
          
      - name: Install dependencies
        run: npm ci
        
      - name: Run tests
        run: npm run test:run
```

## 疑難排解

### 測試找不到模組
- 確認 vitest.config.ts 中的 alias 設置正確
- 檢查 import 路徑

### Mock 不生效
- 確認 vi.clearAllMocks() 在 beforeEach 中執行
- 檢查 mock 的順序

### 組件測試失敗
- 確認已正確導入組件
- 檢查是否需要提供必要的 props
- 確認 Pinia 已正確設置

## 相關資源

- [Vitest 官方文檔](https://vitest.dev/)
- [Vue Test Utils](https://test-utils.vuejs.org/)
- [Testing Library](https://testing-library.com/)
