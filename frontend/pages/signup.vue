<template>
  <AuthPageLayout max-width-class="max-w-lg"
    :card-class="{ 'mb-8': selectedAccountType === 'business' && currentStep === 2 }">
    <template #title>註冊</template>
    <!-- Progress Steps -->
    <div class="progress-container mb-4">
      <div class="flex justify-between items-center">
        <div class="step-item" :class="{ active: currentStep === 1, completed: currentStep > 1 }">
          <div class="step-circle">
            <Icon v-if="currentStep > 1" name="heroicons:check" class="w-5 h-5" />
            <span v-else>1</span>
          </div>
          <div class="step-text">選擇帳號類型</div>
        </div>
        <div class="step-line" :class="{ completed: currentStep > 1 }"></div>
        <div class="step-item" :class="{ active: currentStep === 2, completed: currentStep > 2 }">
          <div class="step-circle">
            <Icon v-if="currentStep > 2" name="heroicons:check" class="w-5 h-5" />
            <span v-else>2</span>
          </div>
          <div class="step-text">填入資料</div>
        </div>
        <div class="step-line" :class="{ completed: currentStep > 2 }"></div>
        <div class="step-item" :class="{ active: currentStep === 3, completed: currentStep > 3 }">
          <div class="step-circle">
            <Icon v-if="currentStep > 3" name="heroicons:check" class="w-5 h-5" />
            <span v-else>3</span>
          </div>
          <div class="step-text">完成</div>
        </div>
      </div>
    </div>

    <!-- Step 1: Account Type Selection -->
    <div v-if="currentStep === 1" class="account-selection">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- Personal Account -->
        <div class="account-option" :class="{ 'selected': selectedAccountType === 'personal' }">
          <button @click="selectAccountType('personal')" class="account-btn personal-btn w-full"
            :class="{ 'active': selectedAccountType === 'personal' }">
            <Icon name="heroicons:user" class="w-8 h-8 mb-2" />
            <div class="text-lg font-semibold">個人帳號</div>
          </button>
          <div class="radio-container">
            <label class="radio-label">
              <input type="radio" name="accountType" value="personal" :checked="selectedAccountType === 'personal'"
                @change="selectAccountType('personal')" class="radio-btn" />
            </label>
          </div>
        </div>

        <!-- Business Account -->
        <div class="account-option" :class="{ 'selected': selectedAccountType === 'business' }">
          <button @click="selectAccountType('business')" class="account-btn business-btn w-full"
            :class="{ 'active': selectedAccountType === 'business' }">
            <Icon name="heroicons:building-office" class="w-8 h-8 mb-2" />
            <div class="text-lg font-semibold">企業帳號</div>
          </button>
          <div class="radio-container">
            <label class="radio-label">
              <input type="radio" name="accountType" value="business" :checked="selectedAccountType === 'business'"
                @change="selectAccountType('business')" class="radio-btn" />
            </label>
          </div>
        </div>
      </div>

      <!-- Next Button -->
      <UButton @click="handleNext" block size="lg" class="next-btn" :disabled="!selectedAccountType">
        下一步
      </UButton>
    </div>

    <!-- Step 2: Form Data -->
    <div v-if="currentStep === 2" class="form-section">
      <!-- Personal Account Form -->
      <div v-if="selectedAccountType === 'personal'" class="form-grid">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-4">
          <div class="form-field">
            <UInput v-model="formData.account" placeholder="帳號" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.nickname" placeholder="暱稱" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.password" placeholder="密碼" :type="showPassword ? 'text' : 'password'"
              :ui="{ base: 'pr-10', icon: { trailing: { pointer: 'pointer-events-auto' } } }">
              <template #trailing>
                <UButton variant="ghost" size="xs" @click="showPassword = !showPassword"
                  class="focus:outline-none relative z-20 cursor-pointer">
                  <Icon :name="showPassword ? 'heroicons:eye' : 'heroicons:eye-slash'" class="w-4 h-4 text-gray-500" />
                </UButton>
              </template>
            </UInput>
          </div>
          <div class="form-field">
            <UInput v-model="formData.confirmPassword" placeholder="確認密碼"
              :type="showConfirmPassword ? 'text' : 'password'"
              :ui="{ base: 'pr-10', icon: { trailing: { pointer: 'pointer-events-auto' } } }">
              <template #trailing>
                <UButton variant="ghost" size="xs" @click="showConfirmPassword = !showConfirmPassword"
                  class="focus:outline-none relative z-20 cursor-pointer">
                  <Icon :name="showConfirmPassword ? 'heroicons:eye' : 'heroicons:eye-slash'"
                    class="w-4 h-4 text-gray-500" />
                </UButton>
              </template>
            </UInput>
          </div>
          <div class="form-field">
            <UInput v-model="formData.fullName" placeholder="姓名" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.email" placeholder="信箱" type="email" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.phone" placeholder="手機號碼" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.lineId" placeholder="Line ID (選填)" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.companyName" placeholder="公司名稱" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.jobTitle" placeholder="職稱 (選填)" />
          </div>
        </div>
      </div>

      <!-- Business Account Form -->
      <div v-if="selectedAccountType === 'business'" class="form-grid">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-4">
          <div class="form-field">
            <UInput v-model="formData.account" placeholder="帳號" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.nickname" placeholder="暱稱" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.password" placeholder="密碼" :type="showPassword ? 'text' : 'password'"
              :ui="{ base: 'pr-10', icon: { trailing: { pointer: 'pointer-events-auto' } } }">
              <template #trailing>
                <UButton variant="ghost" size="xs" @click="showPassword = !showPassword"
                  class="focus:outline-none relative z-20 cursor-pointer">
                  <Icon :name="showPassword ? 'heroicons:eye' : 'heroicons:eye-slash'" class="w-4 h-4 text-gray-500" />
                </UButton>
              </template>
            </UInput>
          </div>
          <div class="form-field">
            <UInput v-model="formData.confirmPassword" placeholder="確認密碼"
              :type="showConfirmPassword ? 'text' : 'password'"
              :ui="{ base: 'pr-10', icon: { trailing: { pointer: 'pointer-events-auto' } } }">
              <template #trailing>
                <UButton variant="ghost" size="xs" @click="showConfirmPassword = !showConfirmPassword"
                  class="focus:outline-none relative z-20 cursor-pointer">
                  <Icon :name="showConfirmPassword ? 'heroicons:eye' : 'heroicons:eye-slash'"
                    class="w-4 h-4 text-gray-500" />
                </UButton>
              </template>
            </UInput>
          </div>
          <div class="form-field">
            <UInput v-model="formData.fullName" placeholder="姓名" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.email" placeholder="信箱" type="email" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.phone" placeholder="手機號碼" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.lineId" placeholder="Line ID (選填)" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.companyName" placeholder="公司名稱" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.jobTitle" placeholder="職稱 (選填)" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.businessName" placeholder="企業名稱" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.taxId" placeholder="統一編號" />
          </div>
          <div class="form-field">
            <UInput v-model="formData.businessPhone" placeholder="企業電話" />
          </div>
        </div>
      </div>

      <!-- Form Buttons -->
      <div class="flex gap-4 mt-4">
        <UButton @click="handleRegister" size="lg" class="register-btn flex-1" :loading="loading">
          註冊
        </UButton>
        <UButton @click="goBack" variant="outline" size="lg" class="back-btn flex-1">
          回上一頁
        </UButton>
      </div>
    </div>

    <!-- Step 3: Completion -->
    <div v-if="currentStep === 3" class="completion-section text-center">
      <Icon name="heroicons:check-circle" class="w-16 h-16 text-green-500 mx-auto mb-4" />
      <h3 class="text-2xl font-bold mb-4 text-gray-800">註冊完成！</h3>
      <p class="text-gray-600 mb-6">您的帳號已成功建立</p>
      <UButton @click="$router.push('/login')" size="lg" class="login-btn">
        前往登入
      </UButton>
    </div>
  </AuthPageLayout>
</template>

<script setup>
const { $swal } = useNuxtApp()
const { post } = useApi()
const { showSuccess, showError, showWarning } = useSweetAlert()
const currentStep = ref(1)
const selectedAccountType = ref('')
const loading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const formData = ref({
  account: '',
  nickname: '',
  password: '',
  confirmPassword: '',
  fullName: '',
  email: '',
  phone: '',
  lineId: '',
  companyName: '',
  jobTitle: '',
  businessName: '',
  taxId: '',
  businessPhone: ''
})

const selectAccountType = (type) => {
  selectedAccountType.value = type
}

const handleNext = () => {
  if (!selectedAccountType.value) {
    return
  }

  currentStep.value = 2
}

const goBack = () => {
  currentStep.value = 1
}

const handleRegister = async () => {
  if (!(await validateForm())) {
    return
  }

  loading.value = true
  try {
    // 準備 API 資料
    const registerData = {
      account: formData.value.account,
      nickname: formData.value.nickname,
      password: formData.value.password,
      fullName: formData.value.fullName,
      email: formData.value.email,
      phone: formData.value.phone,
      lineId: formData.value.lineId,
      jobTitle: formData.value.jobTitle,
      accountType: selectedAccountType.value
    }

    // 如果是企業帳號，加入企業相關欄位
    if (selectedAccountType.value === 'business') {
      registerData.businessName = formData.value.businessName
      registerData.taxId = formData.value.taxId
      registerData.businessPhone = formData.value.businessPhone
    }

    // 呼叫註冊 API
    const response = await post('/auth/register', registerData)

    if (response.success) {
      currentStep.value = 3
    } else {
      await showError('註冊失敗', response.message || '請稍後再試')
    }
  } catch (error) {
    console.error('Registration error:', error)
    const errorMessage = error.response?.data?.message || error.message || '請稍後再試'
    await showError('註冊失敗', errorMessage)
  } finally {
    loading.value = false
  }
}

const validateForm = async () => {
  const requiredFields = ['account', 'nickname', 'password', 'confirmPassword', 'fullName', 'email', 'phone']

  for (const field of requiredFields) {
    if (!formData.value[field]) {
      await showWarning('欄位未填寫完整', '請填寫所有必填欄位')
      return false
    }
  }

  if (formData.value.password !== formData.value.confirmPassword) {
    await showError('密碼不一致', '密碼與確認密碼不符')
    return false
  }

  // Additional validation for business accounts
  if (selectedAccountType.value === 'business') {
    if (!formData.value.businessName || !formData.value.taxId) {
      await showWarning('企業資料未完整', '請填寫企業相關資料')
      return false
    }
  }

  return true
}

// Page metadata - guest only (redirect if already authenticated)
definePageMeta({
  middleware: 'guest',
  layout: false
})
</script>

<style scoped>
/* Progress Steps */
.progress-container {
  padding: 0 0.5rem;
}

.step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  position: relative;
  z-index: 2;
}

.step-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 2px solid #d1d5db;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  color: #6b7280;
  font-weight: bold;
  margin-bottom: 0.25rem;
  transition: all 0.3s ease;
}

.step-item.active .step-circle {
  border-color: #3b82f6;
  background: #3b82f6;
  color: white;
}

.step-item.completed .step-circle {
  border-color: #22c55e;
  background: #22c55e;
  color: white;
}

.step-text {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
}

.step-item.active .step-text {
  color: #3b82f6;
  font-weight: 600;
}

.step-item.completed .step-text {
  color: #22c55e;
  font-weight: 600;
}

.step-line {
  flex: 1;
  height: 2px;
  background: #d1d5db;
  margin: 0 1rem;
  position: relative;
  top: -25px;
  z-index: 1;
  transition: background-color 0.3s ease;
}

.step-line.completed {
  background: #22c55e;
}

/* Account Selection */
.account-selection {
  padding: 0.5rem 0;
}

.account-option {
  text-align: center;
}

.account-btn {
  background: white;
  border: 2px solid #d1d5db;
  border-radius: 12px;
  padding: 1.5rem 1rem;
  transition: all 0.3s ease;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 120px;
}

.account-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.personal-btn {
  color: #3b82f6;
  border-color: #e5e7eb;
}

.personal-btn:hover {
  border-color: #3b82f6;
  background: #eff6ff;
}

.personal-btn.active {
  border-color: #3b82f6;
  background: #3b82f6;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.business-btn {
  color: #22c55e;
  border-color: #e5e7eb;
}

.business-btn:hover {
  border-color: #22c55e;
  background: #f0fdf4;
}

.business-btn.active {
  border-color: #22c55e;
  background: #22c55e;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);
}

.radio-container {
  margin-top: 1rem;
  display: flex;
  justify-content: center;
}

.radio-label {
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.radio-btn {
  width: 20px;
  height: 20px;
  accent-color: #3b82f6;
  background-color: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.account-option.selected .radio-btn {
  accent-color: #2FA633;
  background-color: white;
}

/* Personal account selected state */
.account-option:has(.radio-btn[value="personal"]:checked) .personal-btn {
  border-color: #3b82f6;
  background: #3b82f6;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

/* Business account selected state */
.account-option:has(.radio-btn[value="business"]:checked) .business-btn {
  border-color: #22c55e;
  background: #22c55e;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);
}

/* Next Button */
.next-btn {
  background: linear-gradient(to right, #0ea5e9, #38bdf8);
  border: none;
  color: white;
  border-radius: 8px;
  padding: 12px 24px;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.next-btn:hover {
  background: linear-gradient(to right, #0284c7, #0ea5e9);
  transform: translateY(-1px);
}

.next-btn:disabled {
  background: #d1d5db;
  color: #9ca3af;
  cursor: not-allowed;
  transform: none;
}


/* Form Section */
.form-section {
  padding: 0.5rem 0;
}

/* Buttons */
.register-btn {
  background: linear-gradient(to right, #0ea5e9, #38bdf8);
  border: none;
  color: white;
  border-radius: 8px;
  font-weight: 600;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.register-btn:hover {
  background: linear-gradient(to right, #0284c7, #0ea5e9);
}

.back-btn {
  background-color: #f3f4f6 !important;
  border: none !important;
  color: #6b7280 !important;
  border-radius: 8px;
  font-weight: 600;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: none !important;
  outline: none !important;
}

.back-btn:hover {
  background-color: #e5e7eb !important;
  border: none !important;
  box-shadow: none !important;
  outline: none !important;
}

.back-btn:focus {
  border: none !important;
  box-shadow: none !important;
  outline: none !important;
}

.back-btn:active {
  border: none !important;
  box-shadow: none !important;
  outline: none !important;
}

/* Deep selectors to override UButton component styles */
.back-btn :deep(button) {
  border: none !important;
  box-shadow: none !important;
  outline: none !important;
}

.back-btn :deep(.ui-button) {
  border: none !important;
  box-shadow: none !important;
  outline: none !important;
}

.back-btn :deep(*) {
  border: none !important;
  box-shadow: none !important;
  outline: none !important;
}

/* Completion Section */
.completion-section {
  padding: 1rem 0;
}

.login-btn {
  background: linear-gradient(to right, #2FA633, #72BB29);
  border: none;
  color: white;
  border-radius: 8px;
  font-weight: 600;
}

.login-btn:hover {
  background: linear-gradient(to right, #267a2b, #5fa022);
}
</style>