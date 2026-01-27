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

const authStore = useAuthStore()

const companyId = computed(() => authStore.companyId)
const managers = ref([])
const users = ref([])
const loading = ref(false)

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

const addNewManager = async () => {
  // 使用原生 prompt 對話框
  const username = prompt('請輸入帳號：')
  if (!username) return
  
  const fullName = prompt('請輸入姓名：')
  if (!fullName) return
  
  const password = prompt('請輸入密碼（至少6位）：')
  if (!password) return
  
  // 驗證密碼長度
  if (password.length < 6) {
    alert('密碼至少需要6位字元')
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
    
    // 使用 useCompany 中的 createUser 方法
    const result = await createUser(userData)
    
    if (result.success) {
      alert('新增成功！')
      await loadMembers()
    } else {
      alert('新增失敗：' + (result.error?.message || result.data?.message || '未知錯誤'))
    }
  } catch (error) {
    console.error('Failed to create manager:', error)
    alert('新增失敗：' + (error.message || '未知錯誤'))
  }
}

const setAsUser = async (manager) => {
  try {
    const result = await setAsCompanyUser(manager.id)
    if (result.success) {
      await loadMembers()
      alert('已降級為一般用戶')
    }
  } catch (error) {
    alert('操作失敗：' + (error.message || '未知錯誤'))
  }
}

const setAsManager = async (user) => {
  try {
    const result = await setAsCompanyManager(user.id)
    if (result.success) {
      await loadMembers()
      alert('已提升為管理者')
    }
  } catch (error) {
    alert('操作失敗：' + (error.message || '未知錯誤'))
  }
}

const deleteManager = async (id) => {
  if (!confirm('確定要刪除此成員嗎？')) {
    return
  }
  
  try {
    const res = await deleteUserApi(id)
    if (res.success) {
      await loadMembers()
      alert('成員已移除')
    } else {
      alert('刪除失敗：' + (res.error?.message || res.data?.message || '未知錯誤'))
    }
  } catch (error) {
    alert('刪除失敗：' + (error.message || '未知錯誤'))
  }
}

const deleteUser = deleteManager

onMounted(() => {
  loadMembers()
})
</script>
