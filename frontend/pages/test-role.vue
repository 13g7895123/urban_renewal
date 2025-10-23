<template>
  <NuxtLayout name="main">
    <template #title>角色權限測試</template>

    <div class="p-8">
      <div class="max-w-4xl mx-auto space-y-6">
        <!-- User Info Card -->
        <UCard>
          <template #header>
            <h2 class="text-xl font-bold">當前用戶資訊</h2>
          </template>

          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-500">用戶名稱</p>
                <p class="font-semibold">{{ user?.username || '未登入' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">真實姓名</p>
                <p class="font-semibold">{{ user?.full_name || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">角色</p>
                <p class="font-semibold">
                  <UBadge
                    :color="getRoleBadgeColor()"
                    variant="subtle"
                  >
                    {{ getRoleDisplayName() }}
                  </UBadge>
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500">電子信箱</p>
                <p class="font-semibold">{{ user?.email || '-' }}</p>
              </div>
            </div>
          </div>
        </UCard>

        <!-- Role Checks Card -->
        <UCard>
          <template #header>
            <h2 class="text-xl font-bold">角色檢查</h2>
          </template>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded-lg">
              <Icon
                :name="isAdmin ? 'heroicons:check-circle' : 'heroicons:x-circle'"
                :class="isAdmin ? 'text-green-500' : 'text-gray-300'"
                class="w-8 h-8 mx-auto mb-2"
              />
              <p class="text-sm font-medium">管理員</p>
            </div>

            <div class="text-center p-4 bg-gray-50 rounded-lg">
              <Icon
                :name="isChairman ? 'heroicons:check-circle' : 'heroicons:x-circle'"
                :class="isChairman ? 'text-green-500' : 'text-gray-300'"
                class="w-8 h-8 mx-auto mb-2"
              />
              <p class="text-sm font-medium">理事長</p>
            </div>

            <div class="text-center p-4 bg-gray-50 rounded-lg">
              <Icon
                :name="isMember ? 'heroicons:check-circle' : 'heroicons:x-circle'"
                :class="isMember ? 'text-green-500' : 'text-gray-300'"
                class="w-8 h-8 mx-auto mb-2"
              />
              <p class="text-sm font-medium">會員</p>
            </div>

            <div class="text-center p-4 bg-gray-50 rounded-lg">
              <Icon
                :name="isObserver ? 'heroicons:check-circle' : 'heroicons:x-circle'"
                :class="isObserver ? 'text-green-500' : 'text-gray-300'"
                class="w-8 h-8 mx-auto mb-2"
              />
              <p class="text-sm font-medium">觀察員</p>
            </div>
          </div>
        </UCard>

        <!-- Permissions Card -->
        <UCard>
          <template #header>
            <h2 class="text-xl font-bold">權限檢查</h2>
          </template>

          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <Icon
                  :name="canManageUrbanRenewal ? 'heroicons:check-circle' : 'heroicons:x-circle'"
                  :class="canManageUrbanRenewal ? 'text-green-500' : 'text-gray-300'"
                  class="w-5 h-5"
                />
                <span>管理更新會</span>
              </div>
              <UBadge :color="canManageUrbanRenewal ? 'green' : 'gray'" variant="subtle">
                {{ canManageUrbanRenewal ? '有權限' : '無權限' }}
              </UBadge>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <Icon
                  :name="canManageMeetings ? 'heroicons:check-circle' : 'heroicons:x-circle'"
                  :class="canManageMeetings ? 'text-green-500' : 'text-gray-300'"
                  class="w-5 h-5"
                />
                <span>管理會議</span>
              </div>
              <UBadge :color="canManageMeetings ? 'green' : 'gray'" variant="subtle">
                {{ canManageMeetings ? '有權限' : '無權限' }}
              </UBadge>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <Icon
                  :name="canVote ? 'heroicons:check-circle' : 'heroicons:x-circle'"
                  :class="canVote ? 'text-green-500' : 'text-gray-300'"
                  class="w-5 h-5"
                />
                <span>投票</span>
              </div>
              <UBadge :color="canVote ? 'green' : 'gray'" variant="subtle">
                {{ canVote ? '有權限' : '無權限' }}
              </UBadge>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <Icon
                  :name="canManageUsers ? 'heroicons:check-circle' : 'heroicons:x-circle'"
                  :class="canManageUsers ? 'text-green-500' : 'text-gray-300'"
                  class="w-5 h-5"
                />
                <span>管理用戶</span>
              </div>
              <UBadge :color="canManageUsers ? 'green' : 'gray'" variant="subtle">
                {{ canManageUsers ? '有權限' : '無權限' }}
              </UBadge>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <Icon
                  :name="canManageSettings ? 'heroicons:check-circle' : 'heroicons:x-circle'"
                  :class="canManageSettings ? 'text-green-500' : 'text-gray-300'"
                  class="w-5 h-5"
                />
                <span>管理系統設定</span>
              </div>
              <UBadge :color="canManageSettings ? 'green' : 'gray'" variant="subtle">
                {{ canManageSettings ? '有權限' : '無權限' }}
              </UBadge>
            </div>
          </div>
        </UCard>

        <!-- Actions Card -->
        <UCard>
          <template #header>
            <h2 class="text-xl font-bold">測試說明</h2>
          </template>

          <div class="space-y-4">
            <p class="text-gray-600">
              這是一個角色權限測試頁面。您可以使用不同的帳號登入來測試不同的權限：
            </p>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2">
              <p class="font-semibold text-blue-900">測試帳號：</p>
              <ul class="list-disc list-inside text-sm text-blue-800 space-y-1">
                <li><code>admin</code> / <code>password</code> - 管理員（完整權限）</li>
                <li><code>chairman</code> / <code>password</code> - 理事長（管理會議、投票）</li>
                <li><code>member1</code> / <code>password</code> - 會員（投票）</li>
                <li><code>observer1</code> / <code>password</code> - 觀察員（僅查看）</li>
              </ul>
            </div>

            <div class="flex gap-4">
              <UButton
                @click="logout"
                color="red"
                variant="soft"
              >
                <Icon name="heroicons:arrow-right-on-rectangle" class="w-4 h-4 mr-2" />
                登出並切換帳號
              </UButton>

              <UButton
                @click="goToLogin"
                color="gray"
                variant="outline"
              >
                <Icon name="heroicons:arrow-left" class="w-4 h-4 mr-2" />
                返回登入頁面
              </UButton>
            </div>
          </div>
        </UCard>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
definePageMeta({
  middleware: ['auth']
})

const authStore = useAuthStore()
const {
  isAdmin,
  isChairman,
  isMember,
  isObserver,
  canManageUrbanRenewal,
  canManageMeetings,
  canVote,
  canManageUsers,
  canManageSettings,
  getRoleDisplayName
} = useRole()

const user = computed(() => authStore.user)

const getRoleBadgeColor = () => {
  const role = user.value?.role
  const colorMap = {
    admin: 'red',
    chairman: 'blue',
    member: 'green',
    observer: 'gray'
  }
  return colorMap[role] || 'gray'
}

const logout = async () => {
  await authStore.logout()
}

const goToLogin = () => {
  navigateTo('/login')
}
</script>

<style scoped>
code {
  @apply bg-gray-100 px-2 py-1 rounded text-sm font-mono;
}
</style>
