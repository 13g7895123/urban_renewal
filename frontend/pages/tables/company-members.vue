<template>
  <NuxtLayout name="main">
    <template #title>企業成員管理</template>
    
    <div class="p-8">
      <div class="bg-green-500 text-white p-4 rounded-t-lg flex justify-between items-center">
        <h2 class="text-xl font-semibold">成員管理</h2>
        <UButton
          color="white"
          variant="solid"
          size="sm"
          class="text-green-700"
          @click="addNewManager"
        >
          <Icon name="heroicons:plus" class="w-5 h-5 mr-1" />
          新增核心成員
        </UButton>
      </div>
      
      <UCard class="rounded-t-none">
        <div class="space-y-8">
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
      </UCard>
    </div>

    <!-- Add New Manager Modal -->
    <UModal v-model="showAddManagerModal" :ui="{ width: 'max-w-md', overlay: { background: 'bg-gray-200/75' } }">
      <UCard>
        <template #header>
          <h3 class="text-lg font-semibold text-gray-900">新增核心成員</h3>
        </template>

        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">帳號</label>
            <UInput v-model="newManagerForm.username" placeholder="請輸入帳號" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">姓名</label>
            <UInput v-model="newManagerForm.fullName" placeholder="請輸入姓名" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">密碼</label>
            <UInput 
              v-model="newManagerForm.password" 
              type="password" 
              placeholder="請輸入密碼（至少6位）" 
            />
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <UButton variant="outline" @click="closeAddManagerModal">
              取消
            </UButton>
            <UButton 
              color="green" 
              @click="confirmAddManager"
              :disabled="!newManagerForm.username || !newManagerForm.fullName || !newManagerForm.password"
            >
              <Icon name="heroicons:check" class="w-4 h-4 mr-2" />
              確認新增
            </UButton>
          </div>
        </div>
      </UCard>
    </UModal>

    <!-- Set As User Confirmation Modal -->
    <UModal v-model="showSetAsUserModal" :ui="{ width: 'max-w-md', overlay: { background: 'bg-gray-200/75' } }">
      <UCard>
        <template #header>
          <h3 class="text-lg font-semibold text-gray-900">確認降級</h3>
        </template>

        <div class="p-6">
          <p class="text-gray-700 mb-6">
            確定要將 <strong>{{ selectedMember?.name }}</strong> 降級為一般用戶嗎？
          </p>

          <div class="flex justify-end gap-3">
            <UButton variant="outline" @click="showSetAsUserModal = false">
              取消
            </UButton>
            <UButton color="blue" @click="confirmSetAsUser">
              確認降級
            </UButton>
          </div>
        </div>
      </UCard>
    </UModal>

    <!-- Set As Manager Confirmation Modal -->
    <UModal v-model="showSetAsManagerModal" :ui="{ width: 'max-w-md', overlay: { background: 'bg-gray-200/75' } }">
      <UCard>
        <template #header>
          <h3 class="text-lg font-semibold text-gray-900">確認提升</h3>
        </template>

        <div class="p-6">
          <p class="text-gray-700 mb-6">
            確定要將 <strong>{{ selectedMember?.name }}</strong> 提升為管理者嗎？
          </p>

          <div class="flex justify-end gap-3">
            <UButton variant="outline" @click="showSetAsManagerModal = false">
              取消
            </UButton>
            <UButton color="blue" @click="confirmSetAsManager">
              確認提升
            </UButton>
          </div>
        </div>
      </UCard>
    </UModal>

    <!-- Delete Member Confirmation Modal -->
    <UModal v-model="showDeleteModal" :ui="{ width: 'max-w-md', overlay: { background: 'bg-gray-200/75' } }">
      <UCard>
        <template #header>
          <h3 class="text-lg font-semibold text-gray-900">確認刪除</h3>
        </template>

        <div class="p-6">
          <p class="text-gray-700 mb-6">
            確定要刪除 <strong>{{ selectedMember?.name }}</strong> 嗎？此操作無法復原。
          </p>

          <div class="flex justify-end gap-3">
            <UButton variant="outline" @click="showDeleteModal = false">
              取消
            </UButton>
            <UButton color="red" @click="confirmDelete">
              確定刪除
            </UButton>
          </div>
        </div>
      </UCard>
    </UModal>
  </NuxtLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

definePageMeta({
  layout: false,
  middleware: ['auth', 'company-manager']
})

const { 
  getAllCompanyMembers, 
  setAsCompanyUser, 
  setAsCompanyManager, 
  deleteUser: deleteUserApi,
  createUser
} = useCompany()

const { showSuccess, showError, showWarning } = useSweetAlert()
const authStore = useAuthStore()

const companyId = computed(() => authStore.companyId)
const managers = ref([])
const users = ref([])
const loading = ref(false)

// Modal states
const showAddManagerModal = ref(false)
const showSetAsUserModal = ref(false)
const showSetAsManagerModal = ref(false)
const showDeleteModal = ref(false)
const selectedMember = ref(null)

// New manager form data
const newManagerForm = ref({
  username: '',
  fullName: '',
  password: ''
})

const memberColumns = [
  { key: 'name', label: '使用者' },
  { key: 'company', label: '更新會' },
  { key: 'actions', label: '' }
]

const loadMembers = async () => {
  if (!companyId.value) return
  
  loading.value = true
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
  } finally {
    loading.value = false
  }
}

const addNewManager = () => {
  // 重置表單
  newManagerForm.value = {
    username: '',
    fullName: '',
    password: ''
  }
  showAddManagerModal.value = true
}

const closeAddManagerModal = () => {
  showAddManagerModal.value = false
  newManagerForm.value = {
    username: '',
    fullName: '',
    password: ''
  }
}

const confirmAddManager = async () => {
  const { username, fullName, password } = newManagerForm.value
  
  // 驗證密碼長度（與註冊API一致：最少6個字元）
  if (password.length < 6) {
    showWarning('密碼長度不足', '密碼至少需要6個字元')
    return
  }

  try {
    const userData = {
      username,
      full_name: fullName,
      password,
      role: 'member',
      company_id: companyId.value,
      is_company_manager: 1,
      is_active: 1,
      approval_status: 'approved'
    }
    
    const result = await createUser(userData)
    
    if (result.success) {
      showSuccess('新增成功', '核心成員已成功新增')
      closeAddManagerModal()
      await loadMembers()
    } else {
      showError('新增失敗', result.error?.message || result.data?.message || '未知錯誤')
    }
  } catch (error) {
    console.error('Failed to create manager:', error)
    showError('新增失敗', error.message || '未知錯誤')
  }
}

const setAsUser = (manager) => {
  selectedMember.value = manager
  showSetAsUserModal.value = true
}

const confirmSetAsUser = async () => {
  try {
    const result = await setAsCompanyUser(selectedMember.value.id)
    if (result.success) {
      showSetAsUserModal.value = false
      selectedMember.value = null
      showSuccess('操作成功', '已降級為一般用戶')
      await loadMembers() // 重新載入資料
    }
  } catch (error) {
    showError('操作失敗', error.message || '未知錯誤')
  }
}

const setAsManager = (user) => {
  selectedMember.value = user
  showSetAsManagerModal.value = true
}

const confirmSetAsManager = async () => {
  try {
    const result = await setAsCompanyManager(selectedMember.value.id)
    if (result.success) {
      showSetAsManagerModal.value = false
      selectedMember.value = null
      showSuccess('操作成功', '已提升為管理者')
      await loadMembers() // 重新載入資料
    }
  } catch (error) {
    showError('操作失敗', error.message || '未知錯誤')
  }
}

const deleteManager = (row) => {
  selectedMember.value = row
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  try {
    const res = await deleteUserApi(selectedMember.value.id)
    if (res.success) {
      showDeleteModal.value = false
      selectedMember.value = null
      showSuccess('刪除成功', '成員已成功移除')
      await loadMembers() // 重新載入資料
    } else {
      showError('刪除失敗', res.error?.message || res.data?.message || '未知錯誤')
    }
  } catch (error) {
    showError('刪除失敗', error.message || '未知錯誤')
  }
}

const deleteUser = deleteManager

onMounted(() => {
  loadMembers()
})
</script>
