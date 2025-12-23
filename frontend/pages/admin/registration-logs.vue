<template>
    <NuxtLayout name="main">
        <template #title>註冊日誌紀錄</template>

        <div class="space-y-6">
            <!-- 統計卡片 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <UCard>
                    <div class="text-sm text-gray-500 mb-1">今日註冊數</div>
                    <div class="text-3xl font-bold">{{ statistics.today_count || 0 }}</div>
                </UCard>
                <UCard>
                    <div class="text-sm text-gray-500 mb-1">總請求數</div>
                    <div class="text-3xl font-bold">{{ statistics.total || 0 }}</div>
                </UCard>
                <UCard>
                    <div class="text-sm text-gray-500 mb-1">成功次數</div>
                    <div class="text-3xl font-bold text-green-600">{{ statistics.success_count || 0 }}</div>
                </UCard>
                <UCard>
                    <div class="text-sm text-gray-500 mb-1">失敗次數</div>
                    <div class="text-3xl font-bold text-red-600">{{ statistics.error_count || 0 }}</div>
                </UCard>
            </div>

            <!-- 篩選工具列 -->
            <UCard>
                <div class="flex flex-wrap gap-4 items-end">
                    <div class="w-full sm:w-48">
                        <label class="text-sm font-medium mb-1 block">狀態</label>
                        <USelect v-model="filters.status" :options="statusOptions" placeholder="全部狀態" />
                    </div>

                    <div class="w-full sm:w-48">
                        <label class="text-sm font-medium mb-1 block">IP 位址</label>
                        <UInput v-model="filters.ip_address" placeholder="搜尋 IP..." />
                    </div>

                    <div class="w-full sm:w-48">
                        <label class="text-sm font-medium mb-1 block">起始日期</label>
                        <UInput type="date" v-model="filters.date_from" />
                    </div>

                    <div class="w-full sm:w-48">
                        <label class="text-sm font-medium mb-1 block">結束日期</label>
                        <UInput type="date" v-model="filters.date_to" />
                    </div>

                    <div class="flex gap-2">
                        <UButton color="gray" icon="heroicons:magnifying-glass" @click="fetchLogs(1)">搜尋</UButton>
                        <UButton color="white" icon="heroicons:arrow-path" @click="resetFilters">重置</UButton>
                    </div>
                </div>
            </UCard>

            <!-- 日誌列表 -->
            <UCard>
                <UTable :columns="columns" :rows="logs" :loading="loading">
                    <template #status-data="{ row }">
                        <UBadge :color="row.response_status === 'success' ? 'green' : 'red'" variant="subtle">
                            {{ row.response_status === 'success' ? '成功' : '失敗' }}
                        </UBadge>
                    </template>

                    <template #created_at-data="{ row }">
                        {{ formatDate(row.created_at) }}
                    </template>

                    <template #actions-data="{ row }">
                        <UButton size="2xs" color="gray" variant="ghost" icon="heroicons:eye" @click="viewDetail(row)">
                            詳情
                        </UButton>
                    </template>
                </UTable>

                <!-- 分頁 -->
                <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                    <div class="text-sm text-gray-500">
                        共 {{ pagination.total }} 筆紀錄
                    </div>
                    <UPagination v-model="pagination.page" :total="pagination.total" :page-count="pagination.per_page"
                        @update:model-value="fetchLogs" />
                </div>
            </UCard>

            <!-- 詳情 Modal -->
            <UModal v-model="showDetailModal">
                <UCard v-if="selectedLog">
                    <template #header>
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-bold">日誌詳情 #{{ selectedLog.id }}</h3>
                            <UButton color="gray" variant="ghost" icon="heroicons:x-mark"
                                @click="showDetailModal = false" />
                        </div>
                    </template>

                    <div class="space-y-4 max-h-[70vh] overflow-y-auto p-1">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">時間</div>
                                <div>{{ formatDate(selectedLog.created_at) }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">IP 位址</div>
                                <div>{{ selectedLog.ip_address }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">狀態</div>
                                <div
                                    :class="selectedLog.response_status === 'success' ? 'text-green-600' : 'text-red-600'">
                                    {{ selectedLog.response_status === 'success' ? '成功' : '失敗' }} ({{
                                        selectedLog.response_code
                                    }})
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">訊息</div>
                                <div>{{ selectedLog.response_message }}</div>
                            </div>
                        </div>

                        <div v-if="selectedLog.error_details && Object.keys(selectedLog.error_details).length > 0">
                            <div class="text-sm text-gray-500 mb-1">錯誤詳情</div>
                            <pre class="bg-red-50 text-red-700 p-3 rounded text-xs overflow-x-auto">{{
                                JSON.stringify(selectedLog.error_details, null, 2) }}</pre>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 mb-1">請求資料</div>
                            <pre class="bg-gray-50 p-3 rounded text-xs overflow-x-auto">{{
                                JSON.stringify(selectedLog.request_data, null, 2) }}</pre>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 mb-1">User Agent</div>
                            <div class="text-xs text-gray-600 break-all">{{ selectedLog.user_agent }}</div>
                        </div>
                    </div>

                    <template #footer>
                        <div class="flex justify-end">
                            <UButton color="gray" @click="showDetailModal = false">關閉</UButton>
                        </div>
                    </template>
                </UCard>
            </UModal>
        </div>
    </NuxtLayout>
</template>

<script setup>
const { $api } = useNuxtApp()
const loading = ref(false)
const showDetailModal = ref(false)
const selectedLog = ref(null)

const statistics = ref({
    today_count: 0,
    total: 0,
    success_count: 0,
    error_count: 0
})

const logs = ref([])
const pagination = ref({
    page: 1,
    per_page: 20,
    total: 0,
    total_pages: 1
})

const filters = ref({
    status: '',
    date_from: '',
    date_to: '',
    ip_address: ''
})

const statusOptions = [
    { label: '全部狀態', value: '' },
    { label: '成功', value: 'success' },
    { label: '失敗', value: 'error' }
]

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'created_at', label: '時間' },
    { key: 'ip_address', label: 'IP 位址' },
    { key: 'response_status', label: '狀態' },
    { key: 'response_message', label: '訊息' },
    { key: 'actions', label: '操作' }
]

// 格式化日期
const formatDate = (dateString) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleString('zh-TW')
}

// 取得統計資料
const fetchStatistics = async () => {
    try {
        const res = await $api.get('/admin/registration-logs/statistics')
        if (res.status === 'success') {
            statistics.value = res.data
        }
    } catch (error) {
        console.error('Fetch statistics failed:', error)
    }
}

// 取得日誌列表
const fetchLogs = async (page = 1) => {
    loading.value = true
    try {
        const params = {
            page,
            per_page: pagination.value.per_page,
            ...filters.value
        }

        // 移除空值參數
        Object.keys(params).forEach(key => {
            if (params[key] === '' || params[key] === null) {
                delete params[key]
            }
        })

        const res = await $api.get('/admin/registration-logs', { params })

        if (res.status === 'success') {
            logs.value = res.data
            pagination.value = {
                ...pagination.value,
                ...res.pagination
            }
        }
    } catch (error) {
        console.error('Fetch logs failed:', error)
        // useToast().error('無法取得日誌列表')
    } finally {
        loading.value = false
    }
}

// 重置篩選
const resetFilters = () => {
    filters.value = {
        status: '',
        date_from: '',
        date_to: '',
        ip_address: ''
    }
    fetchLogs(1)
}

// 查看詳情
const viewDetail = (log) => {
    selectedLog.value = log
    showDetailModal.value = true
}

// 初始化
onMounted(() => {
    fetchStatistics()
    fetchLogs()
})

definePageMeta({
    // middleware: ['auth'],
    // 可以在這裡加入權限檢查 middleware
    // middleware: ['auth', 'admin-only'] 
})
</script>
