<template>
  <div class="min-h-screen flex flex-col">
    <!-- Navbar -->
    <header class="navbar-gradient h-20 flex items-center px-6">
      <div class="flex-1"></div>
      
      <h1 class="text-white text-xl font-bold">
        <slot name="title">都更計票系統首頁</slot>
      </h1>
      
      <div class="flex-1 flex justify-end space-x-4">
        <UButton 
          variant="ghost" 
          color="white" 
          size="lg"
          class="text-white navbar-btn"
          @click="$router.push('/signup')"
        >
          <Icon name="heroicons:user-plus" class="w-5 h-5 mr-2" />
          註冊
        </UButton>
        
        <UButton 
          variant="ghost" 
          color="white" 
          size="lg"
          class="text-white navbar-btn"
          @click="$router.push('/login')"
        >
          <Icon name="heroicons:arrow-right-on-rectangle" class="w-5 h-5 mr-2" />
          登入
        </UButton>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
      <BackgroundImage :container-class="mainClass">
        <slot />
      </BackgroundImage>
    </main>

    <!-- Footer -->
    <Footer :logo-style="logoStyle" />
  </div>
</template>

<script>
export default {
  name: 'AuthLayout',
  props: {
    mainClass: {
      type: String,
      default: 'flex items-center justify-center py-12'
    },
    logoStyle: {
      type: String,
      default: 'width: auto;'
    }
  }
}
</script>

<style scoped>
.navbar-gradient {
  background: linear-gradient(to right, #2FA633, #72BB29);
}

.navbar-btn:hover {
  background-color: rgba(47, 166, 51, 0.2) !important;
}
</style>