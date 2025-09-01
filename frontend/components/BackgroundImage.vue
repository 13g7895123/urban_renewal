<template>
  <div class="background-image" :class="containerClass">
    <slot />
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  containerClass?: string
}>(), {
  containerClass: 'flex items-center justify-center py-12'
})
</script>

<style scoped>
.background-image {
  background-image: url('/assets/images/bg.jpg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}
</style>