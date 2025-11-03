<template>
  <NuxtLayout name="main">
    <template #title>企業基本資料</template>
    
    <div class="p-8">
      <!-- Header with green background -->
      <div class="bg-green-500 text-white p-4 rounded-t-lg">
        <h2 class="text-xl font-semibold">企業基本資料</h2>
      </div>
      
      <!-- Form Content -->
      <UCard class="rounded-t-none">
        <form @submit.prevent="saveCompanyProfile" class="space-y-6">
          <!-- Company Name and ID -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">企業名稱</label>
              <UInput 
                v-model="form.companyName" 
                placeholder="中華開發建築經理股份有限公司"
                class="w-full"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">統一編號</label>
              <UInput 
                v-model="form.taxId" 
                placeholder="94070886"
                class="w-full"
              />
            </div>
          </div>
          
          <!-- Company Phone and Max Updates -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">企業電話</label>
              <UInput 
                v-model="form.companyPhone" 
                placeholder="02-6604-3889"
                class="w-full"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">最大更新會數量</label>
              <UInput 
                v-model="form.maxRenewalCount" 
                type="number"
                placeholder="1"
                class="w-full"
              />
            </div>
          </div>
          
          <!-- Max Vote Count -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">最大議題數量</label>
              <UInput 
                v-model="form.maxIssueCount" 
                type="number"
                placeholder="8"
                class="w-full"
              />
            </div>
          </div>
          
          <!-- New Manager Section -->
          <div class="mt-8 flex items-end justify-end gap-4">
            <UButton
              color="primary"
              size="sm"
              variant="outline"
              @click="reloadMembers"
              :loading="loading"
            >
              <Icon name="heroicons:arrow-path" class="w-4 h-4 mr-1" />
              重新載入
            </UButton>
            <UButton
              color="green"
              size="sm"
              @click="addNewManager"
            >
              <Icon name="heroicons:plus" class="w-4 h-4 mr-1" />
              新增使用者
            </UButton>
          </div>
          
          <!-- Managers Table -->
          <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">企業管理者</h3>
            <div v-if="managers.length === 0" class="border rounded-lg p-8 text-center text-gray-500">
              尚無企業管理者
            </div>
            <div v-else class="border rounded-lg overflow-hidden">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="p-4 text-left text-gray-700 font-medium">使用者名稱</th>
                    <th class="p-4 text-left text-gray-700 font-medium">姓名</th>
                    <th class="p-4 text-left text-gray-700 font-medium">所屬企業</th>
                    <th class="p-4 text-right text-gray-700 font-medium">操作</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(manager, index) in managers" :key="manager.id" class="border-b">
                    <td class="p-4 text-gray-700">{{ manager.username }}</td>
                    <td class="p-4 text-gray-700">{{ manager.name }}</td>
                    <td class="p-4 text-gray-700">{{ manager.company }}</td>
                    <td class="p-4 text-right space-x-2">
                      <UButton
                        color="blue"
                        size="xs"
                        @click="setAsUser(manager)"
                      >
                        設為使用者
                      </UButton>
                      <UButton
                        color="red"
                        size="xs"
                        @click="deleteManager(index)"
                      >
                        刪除
                      </UButton>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">企業使用者</h3>
            <div v-if="users.length === 0" class="border rounded-lg p-8 text-center text-gray-500">
              尚無企業使用者，請從企業管理者中點擊「設為使用者」來新增使用者
            </div>
            <div v-else class="border rounded-lg overflow-hidden">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="p-4 text-left text-gray-700 font-medium">使用者名稱</th>
                    <th class="p-4 text-left text-gray-700 font-medium">姓名</th>
                    <th class="p-4 text-left text-gray-700 font-medium">所屬企業</th>
                    <th class="p-4 text-right text-gray-700 font-medium">操作</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(user, index) in users" :key="user.id" class="border-b">
                    <td class="p-4 text-gray-700">{{ user.username }}</td>
                    <td class="p-4 text-gray-700">{{ user.name }}</td>
                    <td class="p-4 text-gray-700">{{ user.company }}</td>
                    <td class="p-4 text-right space-x-2">
                      <UButton
                        color="green"
                        size="xs"
                        @click="setAsManager(user)"
                      >
                        設為管理者
                      </UButton>
                      <UButton
                        color="red"
                        size="xs"
                        @click="deleteUser(index)"
                      >
                        刪除
                      </UButton>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <!-- Save Button -->
          <div class="flex justify-end pt-4">
            <UButton 
              type="submit"
              color="green" 
              size="lg"
            >
              儲存
            </UButton>
          </div>
        </form>
      </UCard>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

definePageMeta({
  layout: false,
  middleware: ['auth', 'company-manager']
})

const { getCompanyProfile, updateCompanyProfile, getAllCompanyMembers, setAsCompanyUser, setAsCompanyManager, deleteUser: deleteUserApi } = useCompany()
const { $swal } = useNuxtApp()
const authStore = useAuthStore()

// 從登入使用者取得企業 ID (用於成員管理)
const companyId = computed(() => authStore.user?.urban_renewal_id)
const hasCompanyAccess = computed(() => authStore.user?.is_company_manager)

const form = ref({
  companyName: '',
  taxId: '',
  companyPhone: '',
  maxRenewalCount: 1,
  maxIssueCount: 8
})

const managers = ref([])
const users = ref([])
const loading = ref(false)

// Load company profile and members
const loadCompanyData = async () => {
  // 檢查使用者是否有企業權限
  if (!hasCompanyAccess.value) {
    await $swal.fire({
      title: '無法存取',
      text: '您的帳號未關聯任何企業，無法使用此功能',
      icon: 'warning',
      confirmButtonText: '確定',
      confirmButtonColor: '#f59e0b'
    })
    navigateTo('/dashboard')
    return
  }

  loading.value = true
  try {
    // Load company profile (使用新的 /companies/me API，不需要傳入 companyId)
    const profileResult = await getCompanyProfile()
    if (profileResult.success && profileResult.data?.data) {
      const data = profileResult.data.data
      form.value = {
        companyName: data.name || '',
        taxId: data.tax_id || '',
        companyPhone: data.company_phone || '',
        maxRenewalCount: data.max_renewal_count || 1,
        maxIssueCount: data.max_issue_count || 8
      }
    } else {
      throw new Error(profileResult.error?.message || '載入企業資料失敗')
    }

    // Load company members
    await loadMembers()
  } catch (error) {
    console.error('Failed to load company data:', error)
    await $swal.fire({
      title: '錯誤',
      text: error.message || '載入企業資料失敗',
      icon: 'error',
      confirmButtonText: '確定',
      confirmButtonColor: '#ef4444'
    })
  } finally {
    loading.value = false
  }
}

// Load company members (managers and users)
const loadMembers = async () => {
  try {
    const membersResult = await getAllCompanyMembers(companyId.value, { per_page: 100 })
    if (membersResult.success && membersResult.data?.data) {
      // API 返回格式: { users: [...], pager: {...} }
      const members = membersResult.data.data.users || []

      // Separate managers and users based on is_company_manager field
      // 確保正確處理 is_company_manager 的各種可能值 (可能是數字、字串或布林值)
      managers.value = members.filter(m => {
        const isManager = m.is_company_manager
        // 明確檢查管理者的條件
        return isManager == 1 || isManager === '1' || isManager === true || isManager === 'true'
      }).map(m => ({
        id: m.id,
        username: m.username,
        name: m.full_name || m.username,
        company: m.urban_renewal_name || ''
      }))

      users.value = members.filter(m => {
        const isManager = m.is_company_manager
        // 明確檢查非管理者的條件 (包含 null, undefined, 0, '0', false 等)
        return isManager == 0 || isManager === '0' || isManager === false || isManager === 'false' ||
               isManager === null || isManager === undefined || isManager === ''
      }).map(m => ({
        id: m.id,
        username: m.username,
        name: m.full_name || m.username,
        company: m.urban_renewal_name || ''
      }))

      console.log('[Company Profile] Loaded members:', {
        total: members.length,
        managers: managers.value.length,
        users: users.value.length,
        rawData: members // 輸出原始資料以便調試
      })
    }
  } catch (error) {
    console.error('Failed to load members:', error)
  }
}

// 重新載入成員資料
const reloadMembers = async () => {
  loading.value = true
  try {
    await loadMembers()
    await $swal.fire({
      title: '成功',
      text: '已重新載入企業成員資料',
      icon: 'success',
      confirmButtonText: '確定',
      confirmButtonColor: '#10b981',
      timer: 1500,
      timerProgressBar: true,
      showConfirmButton: false
    })
  } catch (error) {
    console.error('Failed to reload members:', error)
    await $swal.fire({
      title: '錯誤',
      text: '重新載入失敗，請稍後再試',
      icon: 'error',
      confirmButtonText: '確定',
      confirmButtonColor: '#ef4444'
    })
  } finally {
    loading.value = false
  }
}

const saveCompanyProfile = async () => {
  loading.value = true
  try {
    // 使用新的 /companies/me API，不需要傳入 companyId
    const result = await updateCompanyProfile({
      name: form.value.companyName,
      tax_id: form.value.taxId,
      company_phone: form.value.companyPhone,
      max_renewal_count: form.value.maxRenewalCount,
      max_issue_count: form.value.maxIssueCount
    })

    if (result.success) {
      $swal.fire({
        title: '成功',
        text: '企業資料已儲存',
        icon: 'success',
        confirmButtonText: '確定',
        confirmButtonColor: '#10b981'
      })
    } else {
      throw new Error(result.error?.message || '儲存失敗')
    }
  } catch (error) {
    console.error('Failed to save company profile:', error)
    $swal.fire({
      title: '錯誤',
      text: error.message || '儲存企業資料失敗',
      icon: 'error',
      confirmButtonText: '確定',
      confirmButtonColor: '#ef4444'
    })
  } finally {
    loading.value = false
  }
}

const addNewManager = async () => {
  const { value: formValues } = await $swal.fire({
    title: '<div style="color: #000000; font-size: 24px; font-weight: 600; position: relative;">新增使用者<button type="button" id="fill-test-data-btn" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 13px; cursor: pointer; transition: all 0.2s; font-weight: 500;">📝 填入測試資料</button></div>',
    html: `
      <style>
        #fill-test-data-btn:hover {
          background: #2563eb;
          transform: translateY(-50%) scale(1.05);
        }
        .user-form-container {
          padding: 20px;
          padding-bottom: 40px;
          text-align: left;
        }
        .form-grid {
          display: grid;
          grid-template-columns: repeat(2, 1fr);
          gap: 16px;
        }
        .form-field {
          display: flex;
          flex-direction: column;
        }
        .form-field.full-width {
          grid-column: span 2;
        }
        .form-label {
          font-size: 14px;
          font-weight: 500;
          color: #4b5563;
          margin-bottom: 6px;
          display: flex;
          align-items: center;
        }
        .required-mark {
          color: #ef4444;
          margin-left: 4px;
          font-weight: 600;
        }
        .password-wrapper {
          position: relative;
          display: flex;
          align-items: center;
        }
        .form-input {
          width: 100%;
          padding: 10px 12px;
          border: 1.5px solid #d1d5db;
          border-radius: 8px;
          font-size: 14px;
          transition: all 0.2s;
          background: white;
        }
        .form-input.with-icon {
          padding-right: 40px;
        }
        .form-input:focus {
          outline: none;
          border-color: #10b981;
          box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        .form-input:disabled,
        .form-input:read-only {
          background: #f3f4f6;
          color: #6b7280;
          cursor: not-allowed;
        }
        .form-input::placeholder {
          color: #9ca3af;
        }
        .password-toggle {
          position: absolute;
          right: 12px;
          top: 50%;
          transform: translateY(-50%);
          background: none;
          border: none;
          cursor: pointer;
          padding: 4px;
          color: #6b7280;
          font-size: 18px;
          line-height: 1;
          transition: color 0.2s;
        }
        .password-toggle:hover {
          color: #10b981;
        }
        .info-badge {
          display: inline-block;
          background: #dbeafe;
          color: #1e40af;
          padding: 2px 8px;
          border-radius: 4px;
          font-size: 12px;
          margin-left: 8px;
          font-weight: 500;
        }
      </style>
      <div class="user-form-container">
        <div class="form-grid">
          <!-- 帳號 -->
          <div class="form-field full-width">
            <label class="form-label">
              帳號<span class="required-mark">*</span>
            </label>
            <input id="swal-username" class="form-input" placeholder="請輸入登入帳號">
          </div>

          <!-- 姓名 -->
          <div class="form-field">
            <label class="form-label">
              姓名<span class="required-mark">*</span>
            </label>
            <input id="swal-fullname" class="form-input" placeholder="請輸入真實姓名">
          </div>

          <!-- 暱稱 -->
          <div class="form-field">
            <label class="form-label">
              暱稱
            </label>
            <input id="swal-nickname" class="form-input" placeholder="選填，顯示用暱稱">
          </div>

          <!-- 密碼 -->
          <div class="form-field">
            <label class="form-label">
              密碼<span class="required-mark">*</span>
              <span class="info-badge">至少6個字元</span>
            </label>
            <div class="password-wrapper">
              <input id="swal-password" type="password" class="form-input with-icon" placeholder="••••••••">
              <button type="button" class="password-toggle" onclick="
                const input = document.getElementById('swal-password');
                const icon = this;
                if (input.type === 'password') {
                  input.type = 'text';
                  icon.textContent = '👁️';
                } else {
                  input.type = 'password';
                  icon.textContent = '👁️‍🗨️';
                }
              ">👁️‍🗨️</button>
            </div>
          </div>

          <!-- 確認密碼 -->
          <div class="form-field">
            <label class="form-label">
              確認密碼<span class="required-mark">*</span>
            </label>
            <div class="password-wrapper">
              <input id="swal-password-confirm" type="password" class="form-input with-icon" placeholder="••••••••">
              <button type="button" class="password-toggle" onclick="
                const input = document.getElementById('swal-password-confirm');
                const icon = this;
                if (input.type === 'password') {
                  input.type = 'text';
                  icon.textContent = '👁️';
                } else {
                  input.type = 'password';
                  icon.textContent = '👁️‍🗨️';
                }
              ">👁️‍🗨️</button>
            </div>
          </div>

          <!-- 信箱 -->
          <div class="form-field">
            <label class="form-label">
              信箱
            </label>
            <input id="swal-email" type="email" class="form-input" placeholder="example@email.com">
          </div>

          <!-- 手機號碼 -->
          <div class="form-field">
            <label class="form-label">
              手機號碼
            </label>
            <input id="swal-phone" class="form-input" placeholder="0912-345-678">
          </div>

          <!-- LINE 帳號 -->
          <div class="form-field">
            <label class="form-label">
              LINE 帳號
            </label>
            <input id="swal-line" class="form-input" placeholder="@example">
          </div>

          <!-- 職稱 -->
          <div class="form-field">
            <label class="form-label">
              職稱
            </label>
            <input id="swal-position" class="form-input" placeholder="例：經理、專員">
          </div>

          <!-- 公司名稱 -->
          <div class="form-field full-width">
            <label class="form-label">
              所屬企業
              <span class="info-badge">自動帶入</span>
            </label>
            <input id="swal-company" class="form-input" value="${form.value.companyName || '未設定'}" readonly>
          </div>
        </div>
      </div>
    `,
    didOpen: () => {
      // 填入測試資料的功能
      const fillTestDataBtn = document.getElementById('fill-test-data-btn')
      if (fillTestDataBtn) {
        fillTestDataBtn.addEventListener('click', () => {
          // 生成隨機資料
          const randomNum = Math.floor(Math.random() * 1000)
          const randomNames = ['張小明', '李小華', '王大同', '陳小美', '林建國', '黃志明', '劉佳玲', '吳文德']
          const randomNicknames = ['小明', '小華', '阿同', '小美', '阿國', '志明', '佳玲', '阿德']
          const randomPositions = ['經理', '專員', '主任', '副理', '組長', '襄理', '課長', '部長']
          const randomName = randomNames[Math.floor(Math.random() * randomNames.length)]
          const randomNickname = randomNicknames[Math.floor(Math.random() * randomNicknames.length)]
          const randomPosition = randomPositions[Math.floor(Math.random() * randomPositions.length)]

          // 填入表單
          document.getElementById('swal-username').value = `user${randomNum}`
          document.getElementById('swal-fullname').value = randomName
          document.getElementById('swal-nickname').value = randomNickname
          document.getElementById('swal-password').value = 'Test123456'
          document.getElementById('swal-password-confirm').value = 'Test123456'
          document.getElementById('swal-email').value = `user${randomNum}@example.com`
          document.getElementById('swal-phone').value = `09${Math.floor(Math.random() * 100000000).toString().padStart(8, '0')}`
          document.getElementById('swal-line').value = `@user${randomNum}`
          document.getElementById('swal-position').value = randomPosition

          // 顯示提示
          const toast = document.createElement('div')
          toast.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #10b981; color: white; padding: 12px 20px; border-radius: 8px; font-size: 14px; z-index: 99999; animation: slideIn 0.3s ease-out;'
          toast.textContent = '✓ 已填入測試資料'
          document.body.appendChild(toast)
          setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out'
            setTimeout(() => toast.remove(), 300)
          }, 2000)
        })
      }
    },
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: '✓ 確認新增',
    cancelButtonText: '✕ 取消',
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#6b7280',
    width: '800px',
    padding: '0 0 30px 0',
    customClass: {
      popup: 'rounded-xl',
      confirmButton: 'px-6 py-2.5 rounded-lg font-medium',
      cancelButton: 'px-6 py-2.5 rounded-lg font-medium',
      actions: 'mt-6'
    },
    preConfirm: () => {
      const username = document.getElementById('swal-username').value
      const fullName = document.getElementById('swal-fullname').value
      const nickname = document.getElementById('swal-nickname').value
      const password = document.getElementById('swal-password').value
      const passwordConfirm = document.getElementById('swal-password-confirm').value
      const email = document.getElementById('swal-email').value
      const phone = document.getElementById('swal-phone').value
      const lineAccount = document.getElementById('swal-line').value
      const position = document.getElementById('swal-position').value

      // 驗證必填欄位
      if (!username) {
        $swal.showValidationMessage('請輸入帳號')
        return false
      }
      if (!fullName) {
        $swal.showValidationMessage('請輸入姓名')
        return false
      }
      if (!password) {
        $swal.showValidationMessage('請輸入密碼')
        return false
      }
      if (password.length < 6) {
        $swal.showValidationMessage('密碼至少需要6個字元')
        return false
      }
      if (!passwordConfirm) {
        $swal.showValidationMessage('請輸入確認密碼')
        return false
      }
      if (password !== passwordConfirm) {
        $swal.showValidationMessage('密碼與確認密碼不相符')
        return false
      }

      // 驗證信箱格式（如有填寫）
      if (email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(email)) {
          $swal.showValidationMessage('信箱格式不正確')
          return false
        }
      }

      return {
        username,
        full_name: fullName,
        nickname,
        password,
        email,
        phone,
        line_account: lineAccount,
        position
      }
    }
  })

  if (formValues) {
    try {
      loading.value = true

      // 新增系統欄位
      const userData = {
        ...formValues,
        role: 'member',
        user_type: 'enterprise',
        urban_renewal_id: companyId.value,
        is_company_manager: 1  // 新註冊的企業帳號預設為企業管理者
      }

      const { createUser } = useCompany()
      const result = await createUser(userData)

      if (result.success) {
        // 重新載入成員列表
        await loadMembers()

        // 顯示成功訊息 1.5 秒後自動關閉
        await $swal.fire({
          title: '成功',
          text: '使用者已成功新增',
          icon: 'success',
          showConfirmButton: false,
          timer: 1500,
          timerProgressBar: true
        })
      } else {
        throw new Error(result.error?.message || '新增失敗')
      }
    } catch (error) {
      console.error('Failed to create user:', error)
      await $swal.fire({
        icon: 'error',
        title: '新增失敗',
        text: error.message || '新增使用者失敗',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true
      })
    } finally {
      loading.value = false
    }
  }
}

const setAsUser = async (manager) => {
  try {
    const result = await setAsCompanyUser(manager.id)

    if (result.success) {
      // Reload members list
      await loadMembers()

      // 顯示成功訊息 1.5 秒後自動關閉
      await $swal.fire({
        title: '成功',
        text: `已將 ${manager.name || manager.username} 設為企業使用者`,
        icon: 'success',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true
      })
    } else {
      throw new Error(result.error?.message || '設定失敗')
    }
  } catch (error) {
    console.error('Failed to set as user:', error)
    $swal.fire({
      title: '錯誤',
      text: error.message || '設定企業使用者失敗',
      icon: 'error',
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true
    })
  }
}

const setAsManager = async (user) => {
  try {
    const result = await setAsCompanyManager(user.id)

    if (result.success) {
      // Reload members list
      await loadMembers()

      // 顯示成功訊息 1.5 秒後自動關閉
      await $swal.fire({
        title: '成功',
        text: `已將 ${user.name || user.username} 設為企業管理者`,
        icon: 'success',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true
      })
    } else {
      throw new Error(result.error?.message || '設定失敗')
    }
  } catch (error) {
    console.error('Failed to set as manager:', error)
    $swal.fire({
      title: '錯誤',
      text: error.message || '設定企業管理者失敗',
      icon: 'error',
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true
    })
  }
}

const deleteManager = (index) => {
  const manager = managers.value[index]
  $swal.fire({
    title: '確認刪除',
    text: `確定要刪除管理者 ${manager.name || manager.username} 嗎？`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '刪除',
    cancelButtonText: '取消',
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const deleteResult = await deleteUserApi(manager.id)

        if (deleteResult.success) {
          // Reload members list
          await loadMembers()

          // 顯示成功訊息 1.5 秒後自動關閉
          $swal.fire({
            title: '已刪除',
            text: '管理者已被刪除',
            icon: 'success',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
          })
        } else {
          throw new Error(deleteResult.error?.message || '刪除失敗')
        }
      } catch (error) {
        console.error('Failed to delete manager:', error)
        $swal.fire({
          title: '錯誤',
          text: error.message || '刪除管理者失敗',
          icon: 'error',
          showConfirmButton: false,
          timer: 1500,
          timerProgressBar: true
        })
      }
    }
  })
}

const deleteUser = (index) => {
  const user = users.value[index]
  $swal.fire({
    title: '確認刪除',
    text: `確定要刪除使用者 ${user.name || user.username} 嗎？`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '刪除',
    cancelButtonText: '取消',
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const deleteResult = await deleteUserApi(user.id)

        if (deleteResult.success) {
          // Reload members list
          await loadMembers()

          // 顯示成功訊息 1.5 秒後自動關閉
          $swal.fire({
            title: '已刪除',
            text: '使用者已被刪除',
            icon: 'success',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
          })
        } else {
          throw new Error(deleteResult.error?.message || '刪除失敗')
        }
      } catch (error) {
        console.error('Failed to delete user:', error)
        $swal.fire({
          title: '錯誤',
          text: error.message || '刪除使用者失敗',
          icon: 'error',
          showConfirmButton: false,
          timer: 1500,
          timerProgressBar: true
        })
      }
    }
  })
}

// Load data on mount
onMounted(() => {
  loadCompanyData()
})
</script>