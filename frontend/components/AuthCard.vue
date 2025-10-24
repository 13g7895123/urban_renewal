<template>
  <div class="flex items-center justify-center w-full h-full">
    <UCard class="auth-card w-full overflow-y-auto" style="max-height: 90vh;" :class="[maxWidthClass, cardClass]">
      <!-- Optional Header Slot -->
      <template v-if="$slots.header" #header>
        <slot name="header" />
      </template>

      <!-- Main Content Slot -->
      <slot />
    </UCard>
  </div>
</template>

<script setup>
defineProps({
  maxWidthClass: {
    type: String,
    default: 'max-w-lg'
  },
  cardClass: {
    type: String,
    default: ''
  }
})
</script>

<style scoped>
.auth-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
  padding: 1.5rem;
}

/* Form Field Styles (shared between login and signup) */
.auth-card :deep(.form-field) {
  margin-bottom: 0.5rem;
  width: 100%;
}

.auth-card :deep(.form-field input) {
  color: #000000 !important;
  border: none;
  border-bottom: 1px solid #d1d5db;
  border-radius: 0;
  padding: 8px 4px;
  background: transparent;
  width: 100%;
  outline: none;
  box-shadow: none;
  transition: border-bottom-color 0.3s ease;
}

.auth-card :deep(.form-field input::placeholder) {
  color: #6b7280 !important;
}

.auth-card :deep(.form-field input:focus) {
  border: none;
  border-bottom: 1px solid #2FA633;
  box-shadow: none;
  outline: none;
}

.auth-card :deep(.form-field .ui-input-wrapper) {
  width: 100%;
  background: transparent;
  border: none;
  box-shadow: none;
}

.auth-card :deep(.form-field .ui-input-base) {
  background: transparent;
  border: none;
  box-shadow: none;
}

.auth-card :deep(.form-field div) {
  border: none;
  box-shadow: none;
  background: transparent;
}

.auth-card :deep(.form-field .ui-input) {
  border: none;
  box-shadow: none;
  background: transparent;
}

/* Input Group Styles (for login page with icons) */
.auth-card :deep(.input-group) {
  display: flex;
  align-items: center;
  padding-bottom: 12px;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 1.5rem;
}

.auth-card :deep(.input-group .input-icon) {
  width: 20px;
  height: 20px;
  margin-right: 16px;
  color: #6b7280;
  flex-shrink: 0;
}

.auth-card :deep(.input-group .custom-input) {
  flex: 1;
  background: transparent;
  border: none;
}

.auth-card :deep(.input-group .custom-input input) {
  background: transparent !important;
  border: none !important;
  outline: none !important;
  box-shadow: none !important;
  padding: 8px 0 !important;
  color: #000000 !important;
}

.auth-card :deep(.input-group .custom-input input::placeholder) {
  color: #6b7280 !important;
}

.auth-card :deep(.input-group .custom-input .ui-input-wrapper) {
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
}

.auth-card :deep(.input-group .password-toggle) {
  padding: 0;
  background: transparent;
  border: none;
}
</style>
