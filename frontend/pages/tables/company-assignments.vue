<template>
  <NuxtLayout name="main">
    <template #title>更新會指派管理</template>
    
    <div class="p-8">
      <div class="bg-green-500 text-white p-4 rounded-t-lg">
        <h2 class="text-xl font-semibold">更新會指派管理</h2>
      </div>
      
      <UCard class="rounded-t-none">
        <div class="space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Renewal Projects List -->
            <div class="lg:col-span-1 space-y-4">
              <h3 class="font-bold text-gray-700 flex items-center gap-2">
                <Icon name="heroicons:list-bullet" class="text-blue-500" />
                更新會列表
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
                  尚無更新會資料
                </div>
              </div>
            </div>

            <!-- Right: Assigned Members and Assignment Actions -->
            <div class="lg:col-span-2 space-y-6">
              <div v-if="selectedRenewal" class="bg-gray-50 rounded-2xl p-6 border border-gray-200">
                <div class="flex justify-between items-center mb-6">
                  <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ selectedRenewal.name }}</h2>
                    <p class="text-sm text-gray-500">管理此更新會的指派成員與權限</p>
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
                    目前此更新會尚未指派任何工作人員
                  </div>
                </div>
              </div>

              <div v-else class="flex flex-col items-center justify-center py-20 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 text-gray-400">
                <Icon name="heroicons:cursor-arrow-rays" class="w-16 h-16 mb-4 opacity-20" />
                <p>請從左側選擇一個更新會以進行管理</p>
              </div>
            </div>
          </div>
        </div>
      </UCard>
    </div>

    <!-- Assign Member Modal -->
    <UModal v-model="showAssignModal" :ui="{ width: 'max-w-md', overlay: { background: 'bg-gray-200/75' } }">
      <UCard>
        <template #header>
          <h3 class="text-lg font-semibold text-gray-900">指派人員至更新會</h3>
        </template>

        <div class="p-6 space-y-4">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
            <p class="text-sm text-blue-800">
              <strong>{{ selectedRenewal?.name }}</strong>
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">選擇成員</label>
            <select
              v-model="selectedUserId"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="">-- 請選擇成員 --</option>
              <option
                v-for="user in availableUsers"
                :key="user.id"
                :value="user.id"
              >
                {{ user.full_name || user.username }} (@{{ user.username }})
              </option>
            </select>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <UButton variant="outline" @click="closeAssignModal">
              取消
            </UButton>
            <UButton 
              color="green" 
              @click="confirmAssign"
              :disabled="!selectedUserId"
            >
              <Icon name="heroicons:check" class="w-4 h-4 mr-2" />
              確認指派
            </UButton>
          </div>
        </div>
      </UCard>
    </UModal>

    <!-- Unassign Confirmation Modal -->
    <UModal v-model="showUnassignModal" :ui="{ width: 'max-w-md', overlay: { background: 'bg-gray-200/75' } }">
      <UCard>
        <template #header>
          <h3 class="text-lg font-semibold text-gray-900">確認移除指派</h3>
        </template>

        <div class="p-6">
          <p class="text-gray-700 mb-6">
            確定要將此成員從更新會中移除嗎？
          </p>

          <div class="flex justify-end gap-3">
            <UButton variant="outline" @click="showUnassignModal = false">
              取消
            </UButton>
            <UButton color="red" @click="confirmUnassign">
              確認移除
            </UButton>
          </div>
        </div>
      </UCard>
    </UModal>
  </NuxtLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'

definePageMeta({
  layout: false,
  middleware: ['auth', 'company-manager']
})

const { 
  getCompanyRenewals,
  getRenewalMembers,
  assignMemberToRenewal,
  unassignMemberFromRenewal,
  getAvailableMembers
} = useCompany()

const { showSuccess, showError } = useSweetAlert()

const renewals = ref([])
const selectedRenewal = ref(null)
const renewalMembers = ref([])
const loadingRenewals = ref(false)

// Modal states
const showAssignModal = ref(false)
const showUnassignModal = ref(false)
const availableUsers = ref([])
const selectedUserId = ref('')
const userToUnassign = ref(null)

const assignmentColumns = [
  { key: 'name', label: '姓名' },
  { key: 'email', label: '電子郵件' },
  { key: 'actions', label: '' }
]

const loadRenewals = async () => {
  loadingRenewals.value = true
  try {
    const result = await getCompanyRenewals()
    if (result.success) {
      renewals.value = result.data.data || []
      if (!selectedRenewal.value && renewals.value.length > 0) {
        selectRenewal(renewals.value[0])
      } else if (selectedRenewal.value) {
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
    console.log('getRenewalMembers result:', result)
    console.log('result.data:', result.data)
    console.log('result.data.data:', result.data.data)
    if (result.success) {
      renewalMembers.value = result.data.data || []
      console.log('renewalMembers set to:', renewalMembers.value)
    }
  } catch (error) {
    console.error('Failed to fetch renewal members:', error)
  }
}

const openAssignMemberModal = async () => {
  if (!selectedRenewal.value) return

  const availableRes = await getAvailableMembers()
  const allUsers = availableRes.success ? availableRes.data.data : []
  
  const unassignedUsers = allUsers.filter(u => 
    !renewalMembers.value.some(m => m.user_id === u.id)
  )

  if (unassignedUsers.length === 0) {
    showError('無可用人員', '所有公司成員皆已指派至此更新會，或目前無已核准的成員。')
    return
  }

  availableUsers.value = unassignedUsers
  selectedUserId.value = ''
  showAssignModal.value = true
}

const closeAssignModal = () => {
  showAssignModal.value = false
  selectedUserId.value = ''
  availableUsers.value = []
}

const confirmAssign = async () => {
  if (!selectedUserId.value) return

  try {
    const res = await assignMemberToRenewal(selectedRenewal.value.id, selectedUserId.value)
    if (res.success) {
      showSuccess('成功', '人員指派成功')
      closeAssignModal()
      await fetchRenewalMembers(selectedRenewal.value.id)
      await loadRenewals()
    } else {
      throw new Error(res.error?.message || '指派失敗')
    }
  } catch (error) {
    showError('錯誤', error.message)
  }
}

const handleUnassign = async (userId) => {
  userToUnassign.value = userId
  showUnassignModal.value = true
}

const confirmUnassign = async () => {
  if (!userToUnassign.value) return

  try {
    const res = await unassignMemberFromRenewal(selectedRenewal.value.id, userToUnassign.value)
    if (res.success) {
      showSuccess('成功', '指派已移除')
      showUnassignModal.value = false
      userToUnassign.value = null
      await fetchRenewalMembers(selectedRenewal.value.id)
      await loadRenewals()
    } else {
      throw new Error(res.error?.message || '移除失敗')
    }
  } catch (error) {
    showError('錯誤', error.message)
  }
}

onMounted(() => {
  loadRenewals()
})
</script>
