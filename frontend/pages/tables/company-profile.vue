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
            <UFormGroup label="企業名稱" required>
              <UInput 
                v-model="form.companyName" 
                placeholder="請輸入企業名稱"
                class="w-full"
              />
            </UFormGroup>
            
            <UFormGroup label="統一編號">
              <UInput 
                v-model="form.taxId" 
                placeholder="請輸入統一編號"
                class="w-full"
              />
            </UFormGroup>
          </div>
          
          <!-- Company Phone and Max Updates -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <UFormGroup label="企業電話">
              <UInput 
                v-model="form.companyPhone" 
                placeholder="02-xxxx-xxxx"
                class="w-full"
              />
            </UFormGroup>
            
            <UFormGroup label="最大更新會數量">
              <UInput 
                v-model="form.maxRenewalCount" 
                type="number"
                class="w-full"
              />
            </UFormGroup>
          </div>

          <div class="flex justify-end pt-6 border-t border-gray-100">
            <UButton 
              type="submit"
              color="green" 
              size="lg"
              class="px-8"
              :loading="loading"
            >
              儲存設定
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

const { getCompanyProfile, updateCompanyProfile } = useCompany()
const { showSuccess, showError, showCustom } = useSweetAlert()
const authStore = useAuthStore()

const hasCompanyAccess = computed(() => authStore.user?.is_company_manager)

const form = ref({
  companyName: '',
  taxId: '',
  companyPhone: '',
  maxRenewalCount: 1,
  maxIssueCount: 8
})

const loading = ref(false)

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
  } catch (error) {
    console.error('Failed to load company data:', error)
  } finally {
    loading.value = false
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

onMounted(() => {
  loadCompanyData()
})
</script>
