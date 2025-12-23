<template>
    <div class="h-screen flex flex-col overflow-hidden bg-gray-50">
        <!-- Header -->
        <header class="navbar-gradient h-16 flex-shrink-0 flex items-center px-6 shadow-sm">
            <div class="flex-1">
                <h1 class="text-white text-xl font-bold">
                    <slot name="title">更新會管理系統</slot>
                </h1>
            </div>

            <div class="flex items-center space-x-4">
                <NuxtLink to="/login" class="text-white hover:text-gray-200 text-sm font-medium">
                    登入
                </NuxtLink>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-hidden flex flex-col">
            <div class="flex-1 p-6 overflow-y-auto">
                <div class="bg-white rounded-lg shadow-lg min-h-full p-6 mx-auto max-w-7xl">
                    <slot />
                </div>
            </div>
            <Footer copyright-text="© 2020, made with ❤️ by Thread Tech" />
        </main>
    </div>
</template>

<script setup>
// 無需 Auth 檢查
</script>

<style scoped>
.navbar-gradient {
    background: linear-gradient(to right, #2FA633, #72BB29);
}
</style>
