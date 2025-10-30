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
import { ref, onMounted } from 'vue'

definePageMeta({
  layout: false
})

const { getCompanyProfile, updateCompanyProfile, getAllCompanyMembers, setAsCompanyUser, setAsCompanyManager, deleteUser: deleteUserApi } = useCompany()
const { $swal } = useNuxtApp()

// TODO: Get company ID from auth or route params
const companyId = ref(1)

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
  loading.value = true
  try {
    // Load company profile
    const profileResult = await getCompanyProfile(companyId.value)
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

    // Load company members
    await loadMembers()
  } catch (error) {
    console.error('Failed to load company data:', error)
    $swal.fire({
      title: '錯誤',
      text: '載入企業資料失敗',
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
      const members = membersResult.data.data.members || []

      // Separate managers and users
      managers.value = members.filter(m => m.is_company_manager == 1).map(m => ({
        id: m.id,
        username: m.username,
        name: m.full_name || m.username,
        company: m.urban_renewal_name || ''
      }))

      users.value = members.filter(m => m.is_company_manager == 0).map(m => ({
        id: m.id,
        username: m.username,
        name: m.full_name || m.username,
        company: m.urban_renewal_name || ''
      }))
    }
  } catch (error) {
    console.error('Failed to load members:', error)
  }
}

const saveCompanyProfile = async () => {
  loading.value = true
  try {
    const result = await updateCompanyProfile(companyId.value, {
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

const addNewManager = () => {
  console.log('Adding new manager')
  $swal.fire({
    title: '提示',
    text: '新增使用者功能開發中',
    icon: 'info',
    confirmButtonText: '確定',
    confirmButtonColor: '#3b82f6'
  })
}

const setAsUser = async (manager) => {
  try {
    const result = await setAsCompanyUser(manager.id)

    if (result.success) {
      $swal.fire({
        title: '成功',
        text: `已將 ${manager.name || manager.username} 設為企業使用者`,
        icon: 'success',
        confirmButtonText: '確定',
        confirmButtonColor: '#10b981'
      })

      // Reload members list
      await loadMembers()
    } else {
      throw new Error(result.error?.message || '設定失敗')
    }
  } catch (error) {
    console.error('Failed to set as user:', error)
    $swal.fire({
      title: '錯誤',
      text: error.message || '設定企業使用者失敗',
      icon: 'error',
      confirmButtonText: '確定',
      confirmButtonColor: '#ef4444'
    })
  }
}

const setAsManager = async (user) => {
  try {
    const result = await setAsCompanyManager(user.id)

    if (result.success) {
      $swal.fire({
        title: '成功',
        text: `已將 ${user.name || user.username} 設為企業管理者`,
        icon: 'success',
        confirmButtonText: '確定',
        confirmButtonColor: '#10b981'
      })

      // Reload members list
      await loadMembers()
    } else {
      throw new Error(result.error?.message || '設定失敗')
    }
  } catch (error) {
    console.error('Failed to set as manager:', error)
    $swal.fire({
      title: '錯誤',
      text: error.message || '設定企業管理者失敗',
      icon: 'error',
      confirmButtonText: '確定',
      confirmButtonColor: '#ef4444'
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
          $swal.fire({
            title: '已刪除',
            text: '管理者已被刪除',
            icon: 'success',
            confirmButtonText: '確定',
            confirmButtonColor: '#10b981'
          })

          // Reload members list
          await loadMembers()
        } else {
          throw new Error(deleteResult.error?.message || '刪除失敗')
        }
      } catch (error) {
        console.error('Failed to delete manager:', error)
        $swal.fire({
          title: '錯誤',
          text: error.message || '刪除管理者失敗',
          icon: 'error',
          confirmButtonText: '確定',
          confirmButtonColor: '#ef4444'
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
          $swal.fire({
            title: '已刪除',
            text: '使用者已被刪除',
            icon: 'success',
            confirmButtonText: '確定',
            confirmButtonColor: '#10b981'
          })

          // Reload members list
          await loadMembers()
        } else {
          throw new Error(deleteResult.error?.message || '刪除失敗')
        }
      } catch (error) {
        console.error('Failed to delete user:', error)
        $swal.fire({
          title: '錯誤',
          text: error.message || '刪除使用者失敗',
          icon: 'error',
          confirmButtonText: '確定',
          confirmButtonColor: '#ef4444'
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