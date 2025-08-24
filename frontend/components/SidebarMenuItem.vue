<template>
  <div 
    class="relative"
    @mouseenter="handleMouseEnter"
    @mouseleave="handleMouseLeave"
  >
    <button
      @click="toggleItem"
      class="w-full flex items-center px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 group"
      :class="{ 
        'justify-center': collapsed,
        'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400': isCurrentRoute
      }"
    >
      <!-- Icon -->
      <component 
        :is="getIcon(item.icon)" 
        class="w-5 h-5 transition-colors duration-200" 
        :class="isCurrentRoute ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 group-hover:text-primary-500'"
      />
      
      <!-- Text and Arrow (desktop) -->
      <transition
        enter-active-class="transition-all duration-300 delay-150"
        enter-from-class="opacity-0 transform -translate-x-2"
        enter-to-class="opacity-100 transform translate-x-0"
        leave-active-class="transition-all duration-150"
        leave-from-class="opacity-100 transform translate-x-0"
        leave-to-class="opacity-0 transform -translate-x-2"
      >
        <div v-if="!collapsed" class="flex items-center justify-between flex-1 ml-3 overflow-hidden">
          <span class="font-medium whitespace-nowrap">{{ item.name }}</span>
          <ChevronDownIcon 
            v-if="item.children"
            class="w-4 h-4 transition-transform duration-300 flex-shrink-0"
            :class="{ 'rotate-180': isExpanded }"
          />
        </div>
      </transition>
    </button>

    <!-- Tooltip for collapsed state -->
    <transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 transform scale-90 translate-x-1"
      enter-to-class="opacity-100 transform scale-100 translate-x-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 transform scale-100 translate-x-0"
      leave-to-class="opacity-0 transform scale-90 translate-x-1"
    >
      <div
        v-if="collapsed && showTooltip && !item.children"
        class="absolute left-full top-1/2 ml-3 px-3 py-2 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-lg pointer-events-none z-50 whitespace-nowrap shadow-lg"
        style="transform: translateY(-50%)"
      >
        {{ item.name }}
        <!-- Arrow -->
        <div class="absolute right-full top-1/2 w-0 h-0 border-t-4 border-b-4 border-r-4 border-transparent border-r-gray-900 dark:border-r-gray-700" style="transform: translateY(-50%)"></div>
      </div>
    </transition>

    <!-- Submenu - Expanded Sidebar -->
    <transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 max-h-0"
      enter-to-class="opacity-100 max-h-96"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 max-h-96"
      leave-to-class="opacity-0 max-h-0"
    >
      <div v-if="isExpanded && !collapsed && item.children" class="ml-8 mt-2 space-y-1 overflow-hidden">
        <NuxtLink
          v-for="child in item.children"
          :key="child.name"
          :to="child.href"
          class="block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-primary-500 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-all duration-200"
        >
          {{ child.name }}
        </NuxtLink>
      </div>
    </transition>

    <!-- Submenu Popover - Collapsed Sidebar -->
    <transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 transform scale-90 translate-x-1"
      enter-to-class="opacity-100 transform scale-100 translate-x-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 transform scale-100 translate-x-0"
      leave-to-class="opacity-0 transform scale-90 translate-x-1"
    >
      <div
        v-if="collapsed && showCollapsedSubmenu && item.children"
        class="absolute left-full top-0 ml-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 min-w-48"
        @mouseenter="handleSubmenuMouseEnter"
        @mouseleave="handleSubmenuMouseLeave"
      >
        <!-- Parent item title -->
        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ item.name }}</span>
        </div>
        
        <!-- Child items -->
        <div class="py-1">
          <NuxtLink
            v-for="child in item.children"
            :key="child.name"
            :to="child.href"
            class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200"
            @click="showCollapsedSubmenu = false"
          >
            {{ child.name }}
          </NuxtLink>
        </div>
        
        <!-- Arrow -->
        <div class="absolute right-full top-4 w-0 h-0 border-t-4 border-b-4 border-r-4 border-transparent border-r-white dark:border-r-gray-800"></div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { 
  ChartBarIcon,
  CogIcon,
  QuestionMarkCircleIcon,
  ChevronDownIcon,
  FolderIcon,
  UsersIcon,
  UserGroupIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  collapsed: {
    type: Boolean,
    default: false
  }
})

const route = useRoute()
const isExpanded = ref(false)
const showTooltip = ref(false)
const showCollapsedSubmenu = ref(false)
const hideTimer = ref(null)

// Check if this item or any of its children is the current route
const isCurrentRoute = computed(() => {
  if (props.item.href === route.path) {
    return true
  }
  if (props.item.children) {
    return props.item.children.some(child => child.href === route.path)
  }
  return false
})

// Auto-expand if current route is a child
watch(() => route.path, (newPath) => {
  if (props.item.children) {
    const hasActiveChild = props.item.children.some(child => child.href === newPath)
    if (hasActiveChild) {
      isExpanded.value = true
    }
  }
}, { immediate: true })

const handleMouseEnter = () => {
  // Clear any existing hide timer
  if (hideTimer.value) {
    clearTimeout(hideTimer.value)
    hideTimer.value = null
  }
  
  showTooltip.value = true
  if (props.collapsed && props.item.children) {
    showCollapsedSubmenu.value = true
  }
}

const handleMouseLeave = () => {
  showTooltip.value = false
  if (props.collapsed && props.item.children) {
    // Add a longer delay to allow moving mouse to submenu
    hideTimer.value = setTimeout(() => {
      showCollapsedSubmenu.value = false
      hideTimer.value = null
    }, 300)
  }
}

const handleSubmenuMouseEnter = () => {
  // Clear the hide timer when mouse enters submenu
  if (hideTimer.value) {
    clearTimeout(hideTimer.value)
    hideTimer.value = null
  }
}

const handleSubmenuMouseLeave = () => {
  // Hide submenu immediately when leaving submenu area
  showCollapsedSubmenu.value = false
  if (hideTimer.value) {
    clearTimeout(hideTimer.value)
    hideTimer.value = null
  }
}

const toggleItem = () => {
  if (props.item.children && !props.collapsed) {
    isExpanded.value = !isExpanded.value
  } else if (props.item.children && props.collapsed) {
    // Toggle collapsed submenu on click
    showCollapsedSubmenu.value = !showCollapsedSubmenu.value
  } else if (props.item.href) {
    // Navigate to the href if the item has one and no children
    navigateTo(props.item.href)
  }
}

const iconComponents = {
  ChartBarIcon,
  CogIcon,
  QuestionMarkCircleIcon,
  FolderIcon,
  UsersIcon,
  UserGroupIcon
}

const getIcon = (iconName) => {
  return iconComponents[iconName] || ChartBarIcon
}

// Cleanup timer on unmount
onUnmounted(() => {
  if (hideTimer.value) {
    clearTimeout(hideTimer.value)
  }
})
</script>