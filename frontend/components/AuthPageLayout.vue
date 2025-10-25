<template>
  <NuxtLayout
    name="auth"
    main-class="flex items-center justify-center h-full overflow-y-auto py-4"
    logo-style="width: auto;"
  >
    <template #title>
      <slot name="title">都更計票系統</slot>
    </template>

    <AuthCard :max-width-class="maxWidthClass" :card-class="cardClass">
      <template v-if="$slots.header" #header>
        <slot name="header" />
      </template>

      <slot />
    </AuthCard>
  </NuxtLayout>
</template>

<script setup>
defineProps({
  maxWidthClass: {
    type: String,
    default: 'max-w-lg'
  },
  cardClass: {
    type: [String, Object, Array],
    default: ''
  }
})
</script>
