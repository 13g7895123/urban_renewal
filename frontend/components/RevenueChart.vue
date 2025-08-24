<template>
  <div class="h-64">
    <!-- Loading State -->
    <div v-show="loading" class="h-64 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
      <div class="text-center">
        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent align-[-0.125em] motion-reduce:animate-[spin_1.5s_linear_infinite]" role="status">
          <span class="!absolute !-m-px !h-px !w-px !overflow-hidden !whitespace-nowrap !border-0 !p-0 ![clip:rect(0,0,0,0)]">載入中...</span>
        </div>
        <p class="mt-2 text-gray-500 dark:text-gray-400">載入收入趨勢...</p>
      </div>
    </div>
    
    <!-- Error State -->
    <div v-show="error && !loading" class="h-64 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
      <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-red-500 dark:text-red-400 text-sm">{{ error }}</p>
        <button @click="retryChart" class="mt-2 text-primary-600 hover:text-primary-700 text-sm underline">重試</button>
      </div>
    </div>
    
    <!-- ApexCharts Container -->
    <div v-show="!loading && !error" class="h-full w-full">
      <ClientOnly>
        <apexchart 
          ref="chartRef"
          :options="chartOptions" 
          :series="chartSeries" 
          type="line" 
          height="240"
          width="100%"
        />
        <template #fallback>
          <div class="h-64 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
            <div class="text-center">
              <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent align-[-0.125em] motion-reduce:animate-[spin_1.5s_linear_infinite]" role="status">
                <span class="!absolute !-m-px !h-px !w-px !overflow-hidden !whitespace-nowrap !border-0 !p-0 ![clip:rect(0,0,0,0)]">載入中...</span>
              </div>
              <p class="mt-2 text-gray-500 dark:text-gray-400">載入圖表中...</p>
            </div>
          </div>
        </template>
      </ClientOnly>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  revenueData: {
    type: Array,
    default: () => []
  }
})

const chartRef = ref(null)
const loading = ref(true)
const error = ref(null)
const { formatChartCurrency } = useCurrency()

// ApexCharts Configuration
const chartOptions = computed(() => ({
  chart: {
    id: 'revenue-trend-chart',
    type: 'line',
    height: 240,
    toolbar: {
      show: false
    },
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 800
    }
  },
  xaxis: {
    categories: props.revenueData.map(item => item.month_name || item.month),
    labels: {
      style: {
        colors: '#6B7280',
        fontSize: '12px'
      }
    }
  },
  yaxis: {
    labels: {
      formatter: function (value) {
        return formatChartCurrency(value)
      },
      style: {
        colors: '#6B7280',
        fontSize: '12px'
      }
    }
  },
  colors: ['#6366F1', '#22C55E'],
  stroke: {
    curve: 'smooth',
    width: [3, 3],
    dashArray: [0, 5]
  },
  fill: {
    type: ['gradient', 'solid'],
    gradient: {
      shade: 'light',
      type: 'vertical',
      shadeIntensity: 0.25,
      gradientToColors: undefined,
      inverseColors: false,
      opacityFrom: 0.5,
      opacityTo: 0.1,
      stops: [0, 100]
    },
    opacity: [0.8, 0.3]
  },
  grid: {
    borderColor: '#E5E7EB',
    strokeDashArray: 4,
    xaxis: {
      lines: {
        show: false
      }
    }
  },
  legend: {
    position: 'top',
    horizontalAlign: 'left',
    fontSize: '14px',
    fontWeight: 500,
    markers: {
      width: 12,
      height: 12,
      radius: 6
    }
  },
  tooltip: {
    theme: 'light',
    y: {
      formatter: function (value) {
        return formatChartCurrency(value)
      }
    }
  },
  dataLabels: {
    enabled: false
  }
}))

const chartSeries = computed(() => {
  if (!props.revenueData || props.revenueData.length === 0) {
    return []
  }

  return [
    {
      name: '實際收入',
      data: props.revenueData.map(item => item.revenue || 0)
    },
    {
      name: '預期收入',
      data: props.revenueData.map(item => item.expected_revenue || 0)
    }
  ]
})

const initChart = async () => {
  loading.value = true
  error.value = null
  
  try {
    // Only run on client side
    if (!process.client || typeof window === 'undefined') {
      console.log('Not on client side, skipping chart init')
      loading.value = false
      return
    }
    
    console.log('Starting ApexCharts initialization...')
    
    // Wait for DOM to be ready
    await nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))
    
    // Check if we have revenue data
    if (!props.revenueData || props.revenueData.length === 0) {
      console.warn('No revenue data found')
      loading.value = false
      return
    }

    console.log('ApexCharts initialized successfully with data:', props.revenueData)
    loading.value = false
  } catch (err) {
    console.error('Error initializing ApexCharts:', err)
    error.value = `圖表載入失敗: ${err.message}`
    loading.value = false
  }
}

// Retry chart loading function
const retryChart = async () => {
  console.log('Retrying chart initialization')
  error.value = null
  loading.value = true
  
  await new Promise(resolve => setTimeout(resolve, 500))
  await initChart()
}

// Watch for data changes
watch(() => props.revenueData, async (newData) => {
  console.log('Revenue data changed:', newData)
  if (newData && newData.length > 0) {
    await nextTick()
    loading.value = false
  }
}, { immediate: false, deep: true })

onMounted(async () => {
  console.log('RevenueChart mounted with ApexCharts')
  
  // Only proceed if we're on the client
  if (!process.client) {
    console.log('Server-side rendering, skipping chart initialization')
    loading.value = false
    return
  }
  
  // Wait for hydration
  await new Promise(resolve => setTimeout(resolve, 300))
  await initChart()
})
</script>

<style scoped>
/* ApexCharts specific styles */
:deep(.apexcharts-tooltip) {
  background: white !important;
  border: 1px solid #e5e7eb !important;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
}

:deep(.apexcharts-legend) {
  padding: 0 !important;
  margin-bottom: 10px !important;
}
</style>