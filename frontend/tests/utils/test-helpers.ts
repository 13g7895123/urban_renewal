/**
 * 測試輔助函數
 */

/**
 * 建立 mock 使用者
 */
export function createMockUser(overrides = {}) {
  return {
    id: 1,
    username: 'testuser',
    email: 'test@example.com',
    role: 'member',
    is_active: 1,
    is_company_manager: 0,
    ...overrides
  }
}

/**
 * 建立 mock 企業
 */
export function createMockCompany(overrides = {}) {
  return {
    id: 1,
    name: 'Test Company',
    tax_id: '12345678',
    contact_person: 'Test Contact',
    contact_phone: '02-12345678',
    is_active: 1,
    ...overrides
  }
}

/**
 * 建立 mock API 響應
 */
export function createMockApiResponse(data: any, success = true) {
  return {
    success,
    data,
    message: success ? 'Success' : 'Error'
  }
}

/**
 * 延遲函數（用於測試異步操作）
 */
export function delay(ms: number) {
  return new Promise(resolve => setTimeout(resolve, ms))
}

/**
 * 建立 mock $fetch
 */
export function createMockFetch(response: any) {
  return vi.fn().mockResolvedValue(response)
}

/**
 * 建立 mock 失敗的 $fetch
 */
export function createMockFetchError(error: any) {
  return vi.fn().mockRejectedValue(error)
}
