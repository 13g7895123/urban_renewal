/**
 * Property Owner Form Composable
 * 處理所有權人表單的核心邏輯
 * 支援 create 和 edit 兩種模式
 */
import { ref, reactive, computed } from 'vue'

/**
 * @param {Object} options - 選項
 * @param {string} options.mode - 模式: 'create' | 'edit'
 * @param {number} options.urbanRenewalId - 更新會 ID
 * @param {number} options.ownerId - 所有權人 ID (edit 模式必填)
 */
export const usePropertyOwnerForm = (options) => {
  const { mode, urbanRenewalId, ownerId } = options
  const router = useRouter()
  const { get, post, put } = useApi()
  const { showSuccess, showError } = useSweetAlert()
  const {
    addLand: addLandHelper,
    removeLand: removeLandHelper,
    addBuilding: addBuildingHelper,
    removeBuilding: removeBuildingHelper,
    formatLandNumber,
    formatBuildingNumber,
    createEmptyLandForm,
    createEmptyBuildingForm,
    resetLandForm,
    resetBuildingForm
  } = useLandBuilding()
  const {
    counties,
    fetchCounties,
    onCountyChange: locationOnCountyChange,
    onDistrictChange: locationOnDistrictChange
  } = useLocationCascade()

  // ==================== 狀態 ====================
  const loading = ref(false)
  const isSubmitting = ref(false)
  const urbanRenewalName = ref('')
  const availablePlots = ref([])

  // Modal 控制
  const showAddLandModal = ref(false)
  const showAddBuildingModal = ref(false)

  // Edit 模式特有狀態
  const isReloadingLands = ref(false)
  const isReloadingBuildings = ref(false)

  // Create 模式特有狀態 (loading overlay)
  const isLoading = ref(false)
  const loadingProgress = ref(0)

  // 地號和建號的級聯選擇資料
  const buildingDistricts = ref([])
  const buildingSections = ref([])

  // 主表單資料
  const formData = reactive({
    id: ownerId,
    urban_renewal_id: urbanRenewalId,
    owner_name: '',
    identity_number: '',
    owner_code: '',
    phone1: '',
    phone2: '',
    contact_address: '',
    registered_address: '',
    exclusion_type: '',
    buildings: [],
    lands: [],
    notes: ''
  })

  // 地號表單
  const landForm = createEmptyLandForm()

  // 建號表單
  const buildingForm = createEmptyBuildingForm()

  // 地區映射緩存 (用於 create 模式的 getChineseLandNumber)
  const locationMappings = ref({
    counties: new Map(),
    districts: new Map(),
    sections: new Map()
  })

  // ==================== 初始化方法 ====================

  /**
   * 生成所有權人編號 (create 模式)
   */
  const generateOwnerCode = () => {
    const timestamp = Date.now()
    const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0')
    formData.owner_code = `OW${timestamp}${randomNum}`.slice(-10)
  }

  /**
   * 獲取更新會資訊
   */
  const fetchUrbanRenewalInfo = async () => {
    try {
      if (mode === 'create') {
        loadingProgress.value = 25
      }

      const response = await get(`/urban-renewals/${urbanRenewalId}`)

      if (response.data?.status === 'success') {
        urbanRenewalName.value = response.data.data.name

        if (mode === 'create') {
          loadingProgress.value = 50
        }
      }
    } catch (err) {
      console.error('[usePropertyOwnerForm] Failed to fetch urban renewal info:', err)
    }
  }

  /**
   * 獲取可用地號列表
   */
  const fetchAvailablePlots = async () => {
    try {
      if (mode === 'create') {
        loadingProgress.value = 75
      }

      const response = await get(`/urban-renewals/${urbanRenewalId}/land-plots`)

      if (response.data?.status === 'success') {
        if (mode === 'create') {
          // Create 模式: 轉換為中文格式
          const plotsWithChineseNames = await Promise.all(
            (response.data.data || []).map(async (plot) => {
              const chineseFullLandNumber = await getChineseLandNumber(plot)
              return {
                ...plot,
                chineseFullLandNumber
              }
            })
          )
          availablePlots.value = plotsWithChineseNames
          loadingProgress.value = 100
        } else {
          // Edit 模式: 直接使用
          availablePlots.value = response.data.data || []
        }
      }
    } catch (err) {
      console.error('[usePropertyOwnerForm] Failed to fetch land plots:', err)
    }
  }

  /**
   * 獲取所有權人資料 (edit 模式)
   */
  const fetchPropertyOwner = async () => {
    try {
      loading.value = true
      const response = await get(`/property-owners/${ownerId}`)

      if (response.data?.status === 'success') {
        const data = response.data.data
        Object.assign(formData, {
          id: data.id,
          urban_renewal_id: data.urban_renewal_id,
          owner_name: data.name || '',
          identity_number: data.id_number || '',
          owner_code: data.owner_code || '',
          phone1: data.phone1 || '',
          phone2: data.phone2 || '',
          contact_address: data.contact_address || '',
          registered_address: data.household_address || '',
          exclusion_type: data.exclusion_type || '',
          buildings: data.buildings || [],
          lands: data.lands || [],
          notes: data.notes || ''
        })
      }
    } catch (err) {
      console.error('[usePropertyOwnerForm] Failed to fetch property owner:', err)
      showError('載入失敗', '無法載入所有權人資料')
    } finally {
      loading.value = false
    }
  }

  /**
   * 將地號代號轉換為中文顯示 (create 模式)
   */
  const getChineseLandNumber = async (plot) => {
    // 如果 fullLandNumber 已經是中文格式,直接使用
    if (plot.fullLandNumber && !plot.fullLandNumber.match(/^[A-Z]{3,}/)) {
      return plot.fullLandNumber
    }

    let countyName = plot.county
    let districtName = plot.district
    let sectionName = plot.section

    try {
      // 獲取縣市名稱
      if (locationMappings.value.counties.has(plot.county)) {
        countyName = locationMappings.value.counties.get(plot.county)
      } else if (counties.value.length > 0) {
        const county = counties.value.find(c => c.code === plot.county)
        if (county) {
          countyName = county.name
          locationMappings.value.counties.set(plot.county, county.name)
        }
      }

      // 獲取行政區名稱
      const districtKey = `${plot.county}_${plot.district}`
      if (locationMappings.value.districts.has(districtKey)) {
        districtName = locationMappings.value.districts.get(districtKey)
      } else {
        try {
          const response = await get(`/locations/districts/${plot.county}`)
          if (response.data?.status === 'success') {
            const district = response.data.data.find(d => d.code === plot.district)
            if (district) {
              districtName = district.name
              locationMappings.value.districts.set(districtKey, district.name)
            }
          }
        } catch (error) {
          console.error('Error fetching district:', error)
        }
      }

      // 獲取段小段名稱
      const sectionKey = `${plot.county}_${plot.district}_${plot.section}`
      if (locationMappings.value.sections.has(sectionKey)) {
        sectionName = locationMappings.value.sections.get(sectionKey)
      } else {
        try {
          const response = await get(`/locations/sections/${plot.county}/${plot.district}`)
          if (response.data?.status === 'success') {
            const section = response.data.data.find(s => s.code === plot.section)
            if (section) {
              sectionName = section.name
              locationMappings.value.sections.set(sectionKey, section.name)
            }
          }
        } catch (error) {
          console.error('Error fetching section:', error)
        }
      }
    } catch (error) {
      console.error('Error in getChineseLandNumber:', error)
    }

    return `${countyName}${districtName}${sectionName}${plot.landNumber}`
  }

  // ==================== 地號和建號管理 ====================

  /**
   * 新增地號
   */
  const addLand = () => {
    addLandHelper(formData.lands, landForm, availablePlots.value)
    resetLandForm(landForm)
    showAddLandModal.value = false
  }

  /**
   * 刪除地號
   */
  const removeLand = (index) => {
    removeLandHelper(formData.lands, index)
  }

  /**
   * 新增建號
   */
  const addBuilding = () => {
    addBuildingHelper(
      formData.buildings,
      buildingForm,
      {
        counties: counties.value,
        districts: buildingDistricts.value,
        sections: buildingSections.value
      }
    )

    resetBuildingForm(buildingForm)
    buildingDistricts.value = []
    buildingSections.value = []
    showAddBuildingModal.value = false
  }

  /**
   * 刪除建號
   */
  const removeBuilding = (index) => {
    removeBuildingHelper(formData.buildings, index)
  }

  /**
   * 建號縣市改變
   */
  const onBuildingCountyChange = async () => {
    buildingForm.district = ''
    buildingForm.section = ''
    buildingDistricts.value = []
    buildingSections.value = []

    if (!buildingForm.county) return

    try {
      const response = await get(`/locations/districts/${buildingForm.county}`)
      if (response.data?.status === 'success') {
        buildingDistricts.value = response.data.data
      }
    } catch (error) {
      console.error('Error fetching districts:', error)
    }
  }

  /**
   * 建號行政區改變
   */
  const onBuildingDistrictChange = async () => {
    buildingForm.section = ''
    buildingSections.value = []

    if (!buildingForm.district) return

    try {
      const response = await get(`/locations/sections/${buildingForm.county}/${buildingForm.district}`)
      if (response.data?.status === 'success') {
        buildingSections.value = response.data.data
      }
    } catch (error) {
      console.error('Error fetching sections:', error)
    }
  }

  // ==================== Edit 模式特有方法 ====================

  /**
   * 重新載入地號 (edit 模式)
   */
  const reloadLands = async () => {
    if (mode !== 'edit') return

    isReloadingLands.value = true
    try {
      const response = await get(`/property-owners/${ownerId}`)

      if (response.data?.status === 'success') {
        const data = response.data.data
        formData.lands = data.lands || []
        showSuccess('重新整理成功', `已載入 ${formData.lands.length} 筆地號資料`)
      }
    } catch (err) {
      console.error('[usePropertyOwnerForm] Failed to reload lands:', err)
      showError('重新整理失敗', '無法載入地號資料')
    } finally {
      isReloadingLands.value = false
    }
  }

  /**
   * 重新載入建號 (edit 模式)
   */
  const reloadBuildings = async () => {
    if (mode !== 'edit') return

    isReloadingBuildings.value = true
    try {
      const response = await get(`/property-owners/${ownerId}`)

      if (response.data?.status === 'success') {
        const data = response.data.data
        formData.buildings = data.buildings || []
        showSuccess('重新整理成功', `已載入 ${formData.buildings.length} 筆建號資料`)
      }
    } catch (err) {
      console.error('[usePropertyOwnerForm] Failed to reload buildings:', err)
      showError('重新整理失敗', '無法載入建號資料')
    } finally {
      isReloadingBuildings.value = false
    }
  }

  // ==================== 表單提交 ====================

  /**
   * 提交表單
   */
  const submit = async () => {
    if (!formData.owner_name) {
      showError('驗證失敗', '請填寫所有權人名稱')
      return
    }

    isSubmitting.value = true

    try {
      let response

      if (mode === 'create') {
        // Create 模式: 轉換欄位名稱
        const submitData = {
          urban_renewal_id: formData.urban_renewal_id,
          owner_name: formData.owner_name,
          identity_number: formData.identity_number,
          phone1: formData.phone1,
          phone2: formData.phone2,
          contact_address: formData.contact_address,
          registered_address: formData.registered_address,
          exclusion_type: formData.exclusion_type,
          lands: formData.lands.map(land => {
            const selectedPlot = availablePlots.value.find(plot =>
              (plot.landNumber || plot.plot_number) === land.plot_number
            )

            return {
              plot_number: selectedPlot?.landNumberMain || land.plot_number.split('-')[0] || land.plot_number,
              total_area: parseFloat(land.total_area) || 0,
              ownership_numerator: parseInt(land.ownership_numerator) || 1,
              ownership_denominator: parseInt(land.ownership_denominator) || 1
            }
          }),
          buildings: formData.buildings,
          notes: formData.notes
        }

        response = await post('/property-owners', submitData)
      } else {
        // Edit 模式: 直接提交
        response = await put(`/property-owners/${ownerId}`, formData)
      }

      if (response.data?.status === 'success') {
        const successMessage = mode === 'create' ? '所有權人已成功建立' : '所有權人資料已成功更新'
        const successTitle = mode === 'create' ? '新增成功！' : '更新成功！'

        await showSuccess(successTitle, successMessage)
        router.push(`/tables/urban-renewal/${urbanRenewalId}/property-owners`)
      } else {
        showError(mode === 'create' ? '新增失敗' : '更新失敗', response.data?.message || '操作失敗')
      }
    } catch (err) {
      console.error('[usePropertyOwnerForm] Submit error:', err)

      let errorMessage = mode === 'create' ? '新增失敗，請稍後再試' : '更新失敗，請稍後再試'
      if (err.data && err.data.message) {
        errorMessage = err.data.message
      } else if (err.message) {
        errorMessage = err.message
      }

      showError(mode === 'create' ? '新增失敗' : '更新失敗', errorMessage)
    } finally {
      isSubmitting.value = false
    }
  }

  /**
   * 返回列表頁
   */
  const goBack = () => {
    router.push(`/tables/urban-renewal/${urbanRenewalId}/property-owners`)
  }

  // ==================== 初始化 ====================

  /**
   * 初始化資料 (create 模式)
   */
  const initializeCreate = async () => {
    try {
      isLoading.value = true
      loadingProgress.value = 0

      generateOwnerCode()
      loadingProgress.value = 10

      // 並行獲取資料 with timeout
      const timeout = new Promise((_, reject) =>
        setTimeout(() => reject(new Error('Initialization timeout')), 10000)
      )

      await Promise.race([
        Promise.all([
          fetchCounties(),
          fetchUrbanRenewalInfo()
        ]),
        timeout
      ])

      loadingProgress.value = 50
      await fetchAvailablePlots()

      // 平滑動畫
      setTimeout(() => {
        isLoading.value = false
      }, 500)
    } catch (error) {
      console.error('[usePropertyOwnerForm] Error initializing create mode:', error)
      showError('載入失敗', '無法載入必要資料，請重新整理頁面或稍後再試')
      isLoading.value = false
      loadingProgress.value = 0
    }
  }

  /**
   * 初始化資料 (edit 模式)
   */
  const initializeEdit = async () => {
    await Promise.all([
      fetchPropertyOwner(),
      fetchUrbanRenewalInfo(),
      fetchAvailablePlots(),
      fetchCounties()
    ])
  }

  /**
   * 初始化 (根據模式選擇)
   */
  const initialize = async () => {
    if (mode === 'create') {
      await initializeCreate()
    } else if (mode === 'edit') {
      await initializeEdit()
    }
  }

  // ==================== 返回值 ====================

  return {
    // 狀態
    loading,
    isSubmitting,
    isLoading: mode === 'create' ? isLoading : computed(() => false),
    loadingProgress: mode === 'create' ? loadingProgress : computed(() => 0),
    urbanRenewalName,
    availablePlots,
    formData,
    landForm,
    buildingForm,
    showAddLandModal,
    showAddBuildingModal,

    // 建號級聯資料
    buildingDistricts,
    buildingSections,

    // 縣市資料
    counties,

    // 方法
    addLand,
    removeLand,
    addBuilding,
    removeBuilding,
    onBuildingCountyChange,
    onBuildingDistrictChange,
    submit,
    goBack,
    initialize,

    // Edit 模式特有
    ...(mode === 'edit' ? {
      isReloadingLands,
      isReloadingBuildings,
      reloadLands,
      reloadBuildings,
      formatLandNumber,
      formatBuildingNumber
    } : {})
  }
}
