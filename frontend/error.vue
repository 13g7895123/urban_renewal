<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex">
    <!-- Sidebar integration for consistency -->
    <AppSidebar />
    
    <!-- Main content area with sidebar positioning -->
    <div 
      class="min-h-screen flex flex-col flex-1 main-content-area"
      :class="{
        'sidebar-collapsed': sidebarCollapsed
      }"
    >
      <AppNavbar />
      
      <!-- 404 Content -->
      <main class="flex-1 flex items-center justify-center p-6">
        <div class="text-center max-w-2xl mx-auto">
          <!-- Primary content card with existing styling patterns -->
          <div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-12">
            
            <!-- Large error code number in primary color -->
            <div class="mb-8">
              <h1 class="text-9xl font-bold text-primary-500 leading-none select-none">
                {{ errorCode }}
              </h1>
            </div>

            <!-- Folder/document icon with question mark overlay -->
            <div class="mb-8">
              <div class="relative inline-flex items-center justify-center">
                <FolderIcon class="w-24 h-24 text-gray-300 dark:text-gray-600" />
                <div class="absolute -top-2 -right-2 bg-primary-500 rounded-full w-10 h-10 flex items-center justify-center">
                  <QuestionMarkCircleIcon class="w-6 h-6 text-white" />
                </div>
              </div>
            </div>

            <!-- Multilingual messaging -->
            <div class="mb-8 space-y-2">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ t('error.page_not_found') }}
              </h2>
              <p class="text-lg text-gray-600 dark:text-gray-300">
                {{ t('error.page_not_found_en') }}
              </p>
              <p class="text-gray-500 dark:text-gray-400">
                {{ t('error.page_description') }}
              </p>
            </div>

            <!-- Search functionality with enter key support -->
            <div class="mb-8">
              <div class="relative max-w-md mx-auto">
                <input
                  v-model="searchQuery"
                  type="text"
                  :placeholder="t('error.search_placeholder')"
                  @keyup.enter="performSearch"
                  class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200"
                />
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                </div>
                <button
                  @click="performSearch"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center"
                >
                  <ArrowRightIcon class="h-5 w-5 text-primary-500 hover:text-primary-600 transition-colors duration-200" />
                </button>
              </div>
            </div>

            <!-- Navigation options in priority order -->
            <div class="space-y-6">
              <!-- Primary button - Back to Dashboard -->
              <div>
                <NuxtLink
                  to="/"
                  class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transform hover:scale-[1.02] transition-all duration-200"
                >
                  <HomeIcon class="w-5 h-5 mr-2" />
                  {{ t('error.back_to_dashboard') }}
                </NuxtLink>
              </div>

              <!-- Secondary navigation options - responsive button layout -->
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <NuxtLink
                  to="/projects?search=true"
                  class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200"
                >
                  <MagnifyingGlassIcon class="w-4 h-4 mr-2" />
                  {{ t('error.search_projects') }}
                </NuxtLink>
                
                <NuxtLink
                  to="/projects"
                  class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200"
                >
                  <FolderIcon class="w-4 h-4 mr-2" />
                  {{ t('error.view_all_projects') }}
                </NuxtLink>
                
                <NuxtLink
                  to="/clients"
                  class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200"
                >
                  <UsersIcon class="w-4 h-4 mr-2" />
                  {{ t('error.client_management') }}
                </NuxtLink>
                
                <NuxtLink
                  to="/help"
                  class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200"
                >
                  <QuestionMarkCircleIcon class="w-4 h-4 mr-2" />
                  {{ t('error.contact_support') }}
                </NuxtLink>
              </div>

              <!-- Additional helpful information -->
              <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  {{ t('error.helpful_tips') }}
                </p>
                <div class="mt-3 space-y-2">
                  <div class="flex items-center text-xs text-gray-400 dark:text-gray-500">
                    <CheckIcon class="w-4 h-4 mr-2 text-primary-500" />
                    {{ t('error.tip_1') }}
                  </div>
                  <div class="flex items-center text-xs text-gray-400 dark:text-gray-500">
                    <CheckIcon class="w-4 h-4 mr-2 text-primary-500" />
                    {{ t('error.tip_2') }}
                  </div>
                  <div class="flex items-center text-xs text-gray-400 dark:text-gray-500">
                    <CheckIcon class="w-4 h-4 mr-2 text-primary-500" />
                    {{ t('error.tip_3') }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
      
      <!-- Footer integration -->
      <AppFootbar v-if="showFootbar" />
    </div>
  </div>
</template>

<script setup>
import {
  FolderIcon,
  QuestionMarkCircleIcon,
  HomeIcon,
  MagnifyingGlassIcon,
  ArrowRightIcon,
  UsersIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

// Use existing composables and stores
const { t } = useI18n()
const sidebarStore = useSidebarStore()
const { sidebarCollapsed } = storeToRefs(sidebarStore)

// Use website settings store for footer display
const websiteSettingsStore = useWebsiteSettingsStore()
const { showFooter } = storeToRefs(websiteSettingsStore)

// For backward compatibility
const showFootbar = computed(() => showFooter.value)

// Search functionality
const searchQuery = ref('')

const performSearch = () => {
  if (searchQuery.value.trim()) {
    // Navigate to projects page with search query - this will pre-fill the search field
    navigateTo(`/projects?q=${encodeURIComponent(searchQuery.value)}`)
  }
}

// Handle different error types
const error = useError()

// Determine error type and customize content accordingly
const errorType = computed(() => {
  if (error.value?.statusCode === 500) return 'server_error'
  if (error.value?.statusCode === 403) return 'access_denied'
  if (error.value?.statusCode === 401) return 'unauthorized'
  return 'not_found' // Default to 404
})

const errorCode = computed(() => {
  return error.value?.statusCode || 404
})

const errorTitle = computed(() => {
  switch (errorType.value) {
    case 'server_error': return '500 - Server Error'
    case 'access_denied': return '403 - Access Denied'
    case 'unauthorized': return '401 - Unauthorized'
    default: return `${errorCode.value} - ${t('error.page_not_found')}`
  }
})

// Set page metadata
useHead({
  title: `${errorTitle.value} | Project Management`,
  meta: [
    { name: 'description', content: t('error.page_description') }
  ]
})

// Add error tracking if needed
onMounted(() => {
  // Optional: Track 404 errors for analytics
  console.log('404 Error:', {
    path: useRoute().fullPath,
    timestamp: new Date().toISOString(),
    userAgent: process.client ? navigator.userAgent : 'server'
  })
})
</script>

<style scoped>
/* Additional responsive design enhancements */
@media (max-width: 640px) {
  .text-9xl {
    font-size: 6rem;
  }
}

/* Hover effects for interactive elements */
.transform {
  transition: transform 0.2s ease-in-out;
}

/* Focus improvements for accessibility */
button:focus-visible,
a:focus-visible {
  outline: 2px solid var(--primary-500);
  outline-offset: 2px;
}

/* Dark mode specific adjustments */
.dark .bg-gradient-to-r {
  background-image: linear-gradient(
    to right,
    var(--primary-500),
    var(--primary-600)
  );
}

.dark .hover\:from-primary-600:hover {
  background-image: linear-gradient(
    to right,
    var(--primary-600),
    var(--primary-700)
  );
}
</style>