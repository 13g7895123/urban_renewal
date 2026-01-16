<template>
  <NuxtLayout name="main">
    <template #title>企業與成員管理</template>
    
    <div class="p-6 max-w-7xl mx-auto">
      <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">企業與成員管理</h1>
        <div class="flex gap-3">
          <UButton
            v-if="currentTab === 'members'"
            color="green"
            @click="addNewManager"
          >
            <Icon name="heroicons:plus" class="w-5 h-5 mr-1" />
            新增核心成員
          </UButton>
          <UButton
            v-if="currentTab === 'approval'"
            color="blue"
            variant="outline"
            @click="fetchPendingUsers"
            :loading="loadingPending"
          >
            <Icon name="heroicons:arrow-path" class="w-4 h-4 mr-1" />
            重新整理列表
          </UButton>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="flex border-b border-gray-200 mb-6 bg-white rounded-t-lg px-4 pt-4 shadow-sm">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="currentTab = tab.id"
          class="px-6 py-3 text-sm font-medium transition-colors relative"
          :class="currentTab === tab.id ? 'text-green-600' : 'text-gray-500 hover:text-gray-700'"
        >
          <div class="flex items-center gap-2">
            <Icon :name="tab.icon" class="w-5 h-5" />
            {{ tab.label }}
          </div>
          <div v-if="currentTab === tab.id" class="absolute bottom-0 left-0 right-0 h-0.5 bg-green-500"></div>
          <span v-if="tab.id === 'approval' && pendingUsers.length > 0" class="absolute -top-1 -right-1 flex h-4 w-4">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] text-white items-center justify-center font-bold">
              {{ pendingUsers.length }}
            </span>
          </span>
        </button>
      </div>
      
      <!-- Tab Contents -->
      <div class="bg-white rounded-b-lg shadow-lg border border-gray-200 border-t-0 p-6 min-h-[500px]">
        
        <!-- Tab 1: 企業資料 -->
        <div v-if="currentTab === 'profile'" class="max-w-4xl mx-auto">
          <form @submit.prevent="saveCompanyProfile" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <UFormGroup label="企業名稱" required>
                <UInput v-model="form.companyName" placeholder="請輸入企業名稱" size="lg" />
              </UFormGroup>
              
              <UFormGroup label="統一編號">
                <UInput v-model="form.taxId" placeholder="請輸入統一編號" size="lg" />
              </UFormGroup>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <UFormGroup label="企業電話">
                <UInput v-model="form.companyPhone" placeholder="02-xxxx-xxxx" size="lg" />
              </UFormGroup>
              
              <UFormGroup label="最大案場配額">
                <UInput v-model="form.maxRenewalCount" type="number" size="lg" />
              </UFormGroup>
            </div>

            <div class="flex justify-end pt-6 border-t border-gray-100">
              <UButton 
                type="submit"
                color="green" 
                size="xl"
                class="px-8 shadow-md"
                :loading="loading"
              >
                儲存設定
              </UButton>
            </div>
          </form>
        </div>

        <!-- Tab 2: 成員管理 -->
        <div v-if="currentTab === 'members'" class="space-y-8">
          <section>
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-gray-700 flex items-center gap-2">
                <Icon name="heroicons:shield-check" class="text-blue-500" />
                企業核心管理者
              </h3>
            </div>
            <UTable :columns="memberColumns" :rows="managers">
              <template #name-data="{ row }">
                <div class="font-medium text-gray-900">{{ row.name }}</div>
                <div class="text-xs text-gray-500">@{{ row.username }}</div>
              </template>
              <template #actions-data="{ row }">
                <div class="flex justify-end gap-2">
                  <UButton size="xs" color="gray" variant="ghost" @click="setAsUser(row)">降級為一般用戶</UButton>
                  <UButton size="xs" color="red" variant="ghost" @click="deleteManager(row.id)">刪除</UButton>
                </div>
              </template>
            </UTable>
          </section>

          <section class="pt-8 border-t border-gray-100">
            <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2">
              <Icon name="heroicons:users" class="text-green-500" />
              已核准一般使用者
            </h3>
            <UTable :columns="memberColumns" :rows="users">
              <template #name-data="{ row }">
                <div class="font-medium text-gray-900">{{ row.name }}</div>
                <div class="text-xs text-gray-500">@{{ row.username }}</div>
              </template>
              <template #actions-data="{ row }">
                <div class="flex justify-end gap-2">
                  <UButton size="xs" color="blue" variant="ghost" @click="setAsManager(row)">提升為管理者</UButton>
                  <UButton size="xs" color="red" variant="ghost" @click="deleteUser(row.id)">刪除</UButton>
                </div>
              </template>
            </UTable>
          </section>
        </div>

        <!-- Tab 3: 帳號審核與邀請 -->
        <div v-if="currentTab === 'approval'" class="space-y-10">
          <!-- 邀請碼管理 -->
          <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
              <div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">企業邀請編號</h3>
                <p class="text-sm text-gray-600">將此編號提供給欲加入企業的個人，註冊時填入即可申請。</p>
              </div>
              <div class="flex items-center gap-4 bg-white p-2 rounded-lg border border-gray-200 shadow-inner">
                <code class="text-2xl font-mono font-bold text-green-600 tracking-wider px-4">
                  {{ inviteCodeData.code || '------' }}
                </code>
                <UButton
                  color="gray"
                  variant="ghost"
                  @click="copyInviteCode"
                  v-if="inviteCodeData.code"
                  title="複製編號"
                >
                  <Icon name="heroicons:document-duplicate" class="w-5 h-5" />
                </UButton>
              </div>
              <UButton 
                color="blue" 
                @click="handleGenerateInviteCode"
                :loading="loadingInviteCode"
              >
                {{ inviteCodeData.code ? '重新產生編號' : '產生邀請編號' }}
              </UButton>
            </div>
          </div>

          <!-- 待審核清單 -->
          <div>
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
              <Icon name="heroicons:clock" class="text-orange-500" />
              等待審核中的申請 ({{ pendingUsers.length }})
            </h3>
            
            <div v-if="pendingUsers.length === 0" class="text-center py-20 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50">
              <Icon name="heroicons:user-plus" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
              <p class="text-gray-500">目前沒有待審核的申請</p>
            </div>

            <UTable v-else :columns="pendingColumns" :rows="pendingUsers" class="border border-gray-200 rounded-xl overflow-hidden">
              <template #actions-data="{ row }">
                <div class="flex gap-2">
                  <UButton
                    size="sm"
                    color="green"
                    @click="handleApproval(row.id, 'approve')"
                  >
                    核准加入
                  </UButton>
                  <UButton
                    size="sm"
                    color="red"
                    variant="soft"
                    @click="handleApproval(row.id, 'reject')"
                  >
                    拒絕
                  </UButton>
                </div>
              </template>
            </UTable>
          </div>
        <!-- Tab 4: 案場指派管理 -->
        <div v-if="currentTab === 'renewals'" class="space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Renewal Projects List -->
            <div class="lg:col-span-1 space-y-4">
              <h3 class="font-bold text-gray-700 flex items-center gap-2">
                <Icon name="heroicons:list-bullet" class="text-blue-500" />
                案場列表
              </h3>
              <div class="space-y-2 max-h-[600px] overflow-y-auto pr-2">
                <div 
                  v-for="renewal in renewals" 
                  :key="renewal.id"
                  @click="selectRenewal(renewal)"
                  class="p-4 rounded-xl border-2 transition-all cursor-pointer hover:shadow-md"
                  :class="selectedRenewal?.id === renewal.id ? 'border-green-500 bg-green-50 shadow-sm' : 'border-gray-100 bg-white hover:border-gray-300'"
                >
                  <div class="font-bold text-gray-800">{{ renewal.name }}</div>
                  <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                    <Icon name="heroicons:user-group" class="w-3 h-3" />
                    指派人數: {{ renewal.member_count || 0 }}
                  </div>
                </div>
                <div v-if="renewals.length === 0" class="text-center py-10 bg-gray-50 rounded-xl border border-dashed text-gray-400">
                  尚無案場資料
                </div>
              </div>
            </div>

            <!-- Right: Assigned Members and Assignment Actions -->
            <div class="lg:col-span-2 space-y-6">
              <div v-if="selectedRenewal" class="bg-gray-50 rounded-2xl p-6 border border-gray-200">
                <div class="flex justify-between items-center mb-6">
                  <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ selectedRenewal.name }}</h2>
                    <p class="text-sm text-gray-500">管理此案場的指派成員與權限</p>
                  </div>
                  <UButton color="green" @click="openAssignMemberModal">
                    <Icon name="heroicons:user-plus" class="w-5 h-5 mr-1" />
                    指派新成員
                  </UButton>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                  <UTable :columns="assignmentColumns" :rows="renewalMembers">
                    <template #name-data="{ row }">
                      <div class="font-medium text-gray-900">{{ row.name }}</div>
                      <div class="text-xs text-gray-500">@{{ row.username }}</div>
                    </template>
                    <template #actions-data="{ row }">
                      <UButton
                        size="xs"
                        color="red"
                        variant="ghost"
                        @click="handleUnassign(row.user_id)"
                      >
                        移除指派
                      </UButton>
                    </template>
                  </UTable>
                  <div v-if="renewalMembers.length === 0" class="text-center py-12 text-gray-500">
                    目前此案場尚未指派任何工作人員
                  </div>
                </div>
              </div>

              <div v-else class="flex flex-col items-center justify-center py-20 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 text-gray-400">
                <Icon name="heroicons:cursor-arrow-rays" class="w-16 h-16 mb-4 opacity-20" />
                <p>請從左側選擇一個案場以進行管理</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

definePageMeta({
  layout: false,
  middleware: ['auth', 'company-manager']
})

const { 
  getCompanyProfile, 
  updateCompanyProfile, 
  getAllCompanyMembers, 
  setAsCompanyUser, 
  setAsCompanyManager, 
  deleteUser: deleteUserApi,
  getPendingUsers,
  approveUser,
  getInviteCode,
  generateInviteCode,
  getCompanyRenewals,
  getRenewalMembers,
  assignMemberToRenewal,
  unassignMemberFromRenewal,
  getAvailableMembers
} = useCompany()

const { $swal } = useNuxtApp()
const { showSuccess, showError, showConfirm, showDeleteConfirm, showCustom } = useSweetAlert()
const authStore = useAuthStore()

// Tab state
const currentTab = ref('profile')
const tabs = [
  { id: 'profile', label: '企業資料', icon: 'heroicons:building-office' },
  { id: 'members', label: '成員管理', icon: 'heroicons:user-group' },
  { id: 'approval', label: '帳號審核與邀請', icon: 'heroicons:finger-print' },
  { id: 'renewals', label: '案場指派管理', icon: 'heroicons:map-pin' }
]

const companyId = computed(() => authStore.companyId)
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
const pendingUsers = ref([])
const inviteCodeData = ref({ code: '', active: false })
const loading = ref(false)
const loadingPending = ref(false)
const loadingInviteCode = ref(false)
const renewals = ref([])
const selectedRenewal = ref(null)
const renewalMembers = ref([])
const loadingRenewals = ref(false)

const assignmentColumns = [
  { key: 'name', label: '姓名' },
  { key: 'email', label: '電子郵件' },
  { key: 'actions', label: '' }
]

const memberColumns = [
  { key: 'name', label: '使用者' },
  { key: 'company', label: '案場' },
  { key: 'actions', label: '' }
]

const pendingColumns = [
  { key: 'username', label: '帳號' },
  { key: 'full_name', label: '姓名' },
  { key: 'phone', label: '電話' },
  { key: 'created_at', label: '申請時間' },
  { key: 'actions', label: '操作' }
]

// Load company profile and members
const loadCompanyData = async () => {
  if (!hasCompanyAccess.value) {
    await showCustom({
      title: '無法存取',
      text: '您的帳號未關聯任何企業，無法使用此功能',
      icon: 'warning',
      showConfirmButton: true,
      confirmButtonText: '關閉'
    })
    navigateTo('/')
    return
  }

  loading.value = true
  try {
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
    }

    await Promise.all([
      loadMembers(),
      fetchPendingUsers(),
      fetchInviteCode(),
      loadRenewals()
    ])
  } catch (error) {
    console.error('Failed to load company data:', error)
  } finally {
    loading.value = false
  }
}

// Load company members
const loadMembers = async () => {
  try {
    const membersResult = await getAllCompanyMembers(companyId.value, { per_page: 100 })
    if (membersResult.success && membersResult.data?.data) {
      const members = membersResult.data.data.users || []

      managers.value = members.filter(m => m.is_company_manager == 1).map(m => ({
        id: m.id,
        username: m.username,
        name: m.full_name || m.username,
        company: m.urban_renewal_name || '未指派'
      }))

      users.value = members.filter(m => m.is_company_manager == 0).map(m => ({
        id: m.id,
        username: m.username,
        name: m.full_name || m.username,
        company: m.urban_renewal_name || '未指派'
      }))
    }
  } catch (error) {
    console.error('Failed to load members:', error)
  }
}

const fetchPendingUsers = async () => {
  loadingPending.value = true
  try {
    const result = await getPendingUsers()
    if (result.success) {
      pendingUsers.value = result.data.data || []
    }
  } catch (error) {
    console.error('Failed to fetch pending users:', error)
  } finally {
    loadingPending.value = false
  }
}

const handleApproval = async (userId, action) => {
  const confirmText = action === 'approve' ? '核准此使用者的加入申請？' : '拒絕此使用者的加入申請？'
  const result = await showConfirm(
    action === 'approve' ? '核准確認' : '拒絕確認',
    confirmText,
    action === 'approve' ? '確認核准' : '確認拒絕'
  )

  if (result.isConfirmed) {
    try {
      const res = await approveUser(userId, action)
      if (res.success) {
        await showSuccess('已完成', res.data.message || '操作成功')
        await Promise.all([fetchPendingUsers(), loadMembers()])
      } else {
        throw new Error(res.error?.message || '操作失敗')
      }
    } catch (error) {
      showError('錯誤', error.message)
    }
  }
}

const fetchInviteCode = async () => {
  try {
    const result = await getInviteCode()
    if (result.success) {
      inviteCodeData.value = {
        code: result.data.data.invite_code,
        active: result.data.data.invite_code_active == 1
      }
    }
  } catch (error) {
    console.error('Failed to fetch invite code:', error)
  }
}

const handleGenerateInviteCode = async () => {
  const result = await showConfirm('更換邀請碼', '更新後舊的邀請碼將立即失效，確認要執行嗎？', '確認更新')
  if (result.isConfirmed) {
    loadingInviteCode.value = true
    try {
      const res = await generateInviteCode()
      if (res.success) {
        inviteCodeData.value.code = res.data.data.invite_code
        showSuccess('成功', '邀請碼已更新')
      }
    } catch (error) {
      showError('錯誤', '產生邀請碼失敗')
    } finally {
      loadingInviteCode.value = false
    }
  }
}

const copyInviteCode = () => {
  if (inviteCodeData.value.code) {
    navigator.clipboard.writeText(inviteCodeData.value.code)
    showSuccess('已複製', '邀請碼已複製到剪貼簿', 1000)
  }
}

const saveCompanyProfile = async () => {
  loading.value = true
  try {
    const result = await updateCompanyProfile({
      name: form.value.companyName,
      tax_id: form.value.taxId,
      company_phone: form.value.companyPhone,
      max_renewal_count: form.value.maxRenewalCount,
      max_issue_count: form.value.maxIssueCount
    })

    if (result.success) {
      showSuccess('成功', '企業資料已儲存')
    } else {
      throw new Error(result.error?.message || '儲存失敗')
    }
  } catch (error) {
    showError('錯誤', error.message)
  } finally {
    loading.value = false
  }
}

const addNewManager = async () => {
  // 這裡延用原有的新增使用者邏輯，但改為彈窗填寫
  const { value: formValues } = await $swal.fire({
    title: '新增核心成員',
    html: `
      <div class="space-y-4 text-left p-4">
        <div>
          <label class="block text-sm font-medium gray-700">帳號*</label>
          <input id="swal-username" class="w-full border p-2 rounded" placeholder="帳號">
        </div>
        <div>
          <label class="block text-sm font-medium gray-700">姓名*</label>
          <input id="swal-fullname" class="w-full border p-2 rounded" placeholder="姓名">
        </div>
        <div>
          <label class="block text-sm font-medium gray-700">密碼* (至少6位)</label>
          <input id="swal-password" type="password" class="w-full border p-2 rounded" placeholder="密碼">
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: '新增',
    preConfirm: () => {
      const username = document.getElementById('swal-username').value
      const fullName = document.getElementById('swal-fullname').value
      const password = document.getElementById('swal-password').value
      if (!username || !fullName || !password) {
        $swal.showValidationMessage('請填寫所有必填欄位')
        return false
      }
      return { username, full_name: fullName, password }
    }
  })

  if (formValues) {
    try {
      const userData = {
        ...formValues,
        role: 'member',
        company_id: companyId.value,
        is_company_manager: 1, // 核心成員預設為管理者
        is_active: 1,
        approval_status: 'approved'
      }
      const result = await createUser(userData)
      if (result.success) {
        await loadMembers()
        showSuccess('成功', '核心成員已新增')
      } else {
        throw new Error(result.error?.message || '新增失敗')
      }
    } catch (error) {
      showError('錯誤', error.message)
    }
  }
}

const setAsUser = async (manager) => {
  try {
    const result = await setAsCompanyUser(manager.id)
    if (result.success) {
      await loadMembers()
      showSuccess('成功', '已降級為一般用戶')
    }
  } catch (error) {
    showError('錯誤', '操作失敗')
  }
}

const setAsManager = async (user) => {
  try {
    const result = await setAsCompanyManager(user.id)
    if (result.success) {
      await loadMembers()
      showSuccess('成功', '已提升為管理者')
    }
  } catch (error) {
    showError('錯誤', '操作失敗')
  }
}

const deleteManager = async (id) => {
  const result = await showDeleteConfirm('確認刪除', '確定要刪除此成員嗎？')
  if (result.isConfirmed) {
    try {
      const res = await deleteUserApi(id)
      if (res.success) {
        await loadMembers()
        showSuccess('已刪除', '成員已移除')
      }
    } catch (error) {
      showError('錯誤', '刪除失敗')
    }
  }
}

const deleteUser = deleteManager

// Renewal Management Logic
const loadRenewals = async () => {
  loadingRenewals.value = true
  try {
    const result = await getCompanyRenewals()
    if (result.success) {
      renewals.value = result.data.data || []
      // If none selected and have renewals, select first one
      if (!selectedRenewal.value && renewals.value.length > 0) {
        selectRenewal(renewals.value[0])
      } else if (selectedRenewal.value) {
        // Refresh selected renewal data
        const updated = renewals.value.find(r => r.id === selectedRenewal.value.id)
        if (updated) selectedRenewal.value = updated
      }
    }
  } catch (error) {
    console.error('Failed to load renewals:', error)
  } finally {
    loadingRenewals.value = false
  }
}

const selectRenewal = async (renewal) => {
  selectedRenewal.value = renewal
  await fetchRenewalMembers(renewal.id)
}

const fetchRenewalMembers = async (renewalId) => {
  try {
    const result = await getRenewalMembers(renewalId)
    if (result.success) {
      renewalMembers.value = result.data.data || []
    }
  } catch (error) {
    console.error('Failed to fetch renewal members:', error)
  }
}

const openAssignMemberModal = async () => {
  if (!selectedRenewal.value) return

  // Fetch available members (approved company members)
  const availableRes = await getAvailableMembers()
  const availableUsers = availableRes.success ? availableRes.data.data : []
  
  // Filter out already assigned members
  const unassignedUsers = availableUsers.filter(u => 
    !renewalMembers.value.some(m => m.user_id === u.id)
  )

  if (unassignedUsers.length === 0) {
    await showCustom({
      title: '無可用人員',
      text: '所有公司成員皆已指派至此案件，或目前無已核准的成員。',
      icon: 'info'
    })
    return
  }

  const { value: userId } = await $swal.fire({
    title: '指派人員至案場',
    text: `正在指派人員至：${selectedRenewal.value.name}`,
    input: 'select',
    inputOptions: unassignedUsers.reduce((acc, user) => {
      acc[user.id] = `${user.full_name || user.username} (@${user.username})`
      return acc
    }, {}),
    inputPlaceholder: '請選擇要指派的成員',
    showCancelButton: true,
    confirmButtonText: '確認指派',
    cancelButtonText: '取消',
    inputValidator: (value) => {
      if (!value) return '請選擇成員'
    }
  })

  if (userId) {
    try {
      const res = await assignMemberToRenewal(selectedRenewal.value.id, userId)
      if (res.success) {
        showSuccess('成功', '人員指派成功')
        await fetchRenewalMembers(selectedRenewal.value.id)
        await loadRenewals() // Refresh count
      } else {
        throw new Error(res.error?.message || '指派失敗')
      }
    } catch (error) {
      showError('錯誤', error.message)
    }
  }
}

const handleUnassign = async (userId) => {
  const result = await showConfirm('取消指派', '確定要將此成員從案場中移除嗎？', '確認移除')
  if (result.isConfirmed) {
    try {
      const res = await unassignMemberFromRenewal(selectedRenewal.value.id, userId)
      if (res.success) {
        showSuccess('成功', '指派已移除')
        await fetchRenewalMembers(selectedRenewal.value.id)
        await loadRenewals() // Refresh count
      } else {
        throw new Error(res.error?.message || '移除失敗')
      }
    } catch (error) {
      showError('錯誤', error.message)
    }
  }
}

onMounted(() => {
  loadCompanyData()
})
</script>