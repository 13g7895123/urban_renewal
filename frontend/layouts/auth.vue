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
    <main class="flex-1 login-container" :class="mainClass">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="footer-section bg-gray-50 border-t border-gray-200 py-8">
      <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
          <div class="md:col-span-4"></div>
          
          <div class="md:col-span-4 flex flex-col md:flex-row items-center justify-center">
            <img
              src="/assets/images/logo.jpg"
              alt="Logo"
              class="footer-logo"
              :style="logoStyle"
            />
            <div class="contact-info space-y-2 text-sm text-gray-600 text-center md:text-left">
              <div class="flex items-center justify-center md:justify-start">
                <Icon name="heroicons:phone" class="w-4 h-4 mr-2" />
                服務電話: (02) 1234-5678
              </div>
              <div class="flex items-center justify-center md:justify-start">
                <Icon name="heroicons:printer" class="w-4 h-4 mr-2" />
                服務傳真: (02) 1234-5679
              </div>
              <div class="flex items-center justify-center md:justify-start whitespace-nowrap">
                <Icon name="heroicons:envelope" class="w-4 h-4 mr-2" />
                服務信箱: service@example.com
              </div>
            </div>
          </div>
          
          <div class="md:col-span-4 text-center md:text-right">
            <div class="copyright text-xs text-gray-400">
              © 2024 都更計票系統
            </div>
          </div>
        </div>
      </div>
    </footer>
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

.login-container {
  background-image: url('/assets/images/bg.jpg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

.footer-logo {
  border-radius: 8px;
}
</style>