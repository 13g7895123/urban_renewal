<template>
  <NuxtLayout name="main">
    <template #title>帳號審核與邀請</template>
    
    <div class="p-8">
      <div class="bg-green-500 text-white p-4 rounded-t-lg">
        <h2 class="text-xl font-semibold">帳號審核與邀請</h2>
      </div>
      
      <UCard class="rounded-t-none">
        <div class="space-y-10">
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
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <Icon name="heroicons:clock" class="text-orange-500" />
                等待審核中的申請 ({{ pendingUsers.length }})
              </h3>
              <UButton
                color="blue"
                variant="outline"
                size="sm"
                @click="fetchPendingUsers"
                :loading="loadingPending"
              >
                <Icon name="heroicons:arrow-path" class="w-4 h-4 mr-1" />
                重新整理
              </UButton>
            </div>
            
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
        </div>
      </UCard>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'

definePageMeta({
  layout: false,
  middleware: ['auth', 'company-manager']
})

const { 
  getPendingUsers,
  approveUser,
  getInviteCode,
  generateInviteCode
} = useCompany()

const { showSuccess, showError, showConfirm } = useSweetAlert()

const pendingUsers = ref([])
const inviteCodeData = ref({ code: '', active: false })
const loadingPending = ref(false)
const loadingInviteCode = ref(false)

const pendingColumns = [
  { key: 'username', label: '帳號' },
  { key: 'full_name', label: '姓名' },
  { key: 'phone', label: '電話' },
  { key: 'created_at', label: '申請時間' },
  { key: 'actions', label: '操作' }
]

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
        await fetchPendingUsers()
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

onMounted(() => {
  fetchPendingUsers()
  fetchInviteCode()
})
</script>
