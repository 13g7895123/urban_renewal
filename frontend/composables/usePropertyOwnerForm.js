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

  // ==================== 測試資料填充 ====================

  /**
   * 填入主表單測試資料
   */
  const fillTestData = () => {
    const testNames = [
      '張三丰', '李四海', '王五明', '陳六福', '林七星',
      '黃八方', '劉九龍', '吳十全', '鄭一品', '謝二郎'
    ]

    const testExcludeReasons = [
      '法院囑託查封', '假扣押', '假處分', '破產登記', '未經繼承'
    ]

    const randomName = testNames[Math.floor(Math.random() * testNames.length)]

    // Generate random ID
    const idPrefixes = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D1', 'D2', 'E1', 'E2']
    const randomPrefix = idPrefixes[Math.floor(Math.random() * idPrefixes.length)]
    const randomNumbers = Math.floor(Math.random() * 100000000).toString().padStart(8, '0')

    // Fill form data
    formData.owner_name = randomName
    formData.identity_number = randomPrefix + randomNumbers
    formData.phone1 = `09${Math.floor(Math.random() * 100000000).toString().padStart(8, '0')}`
    formData.phone2 = `02-${Math.floor(Math.random() * 100000000).toString().padStart(8, '0')}`
    formData.contact_address = `台北市${['大安區', '信義區', '中山區', '松山區', '萬華區'][Math.floor(Math.random() * 5)]}${randomName}路${Math.floor(Math.random() * 999) + 1}號`
    formData.registered_address = `台北市${['中正區', '大同區', '中山區', '松山區', '大安區'][Math.floor(Math.random() * 5)]}${randomName}街${Math.floor(Math.random() * 999) + 1}號`
    formData.exclusion_type = testExcludeReasons[Math.floor(Math.random() * testExcludeReasons.length)]

    const now = new Date()
    const dateString = `${now.getFullYear()}年${now.getMonth() + 1}月${now.getDate()}日 ${now.getHours()}:${String(now.getMinutes()).padStart(2, '0')}`
    formData.notes = `${randomName}的測試資料，${mode === 'edit' ? '更新於' : '建立於'}${dateString}`

    showSuccess('測試資料已填入', '所有表單欄位已自動填入測試資料')
  }

  /**
   * 填入地號測試資料
   */
  const fillLandTestData = () => {
    if (availablePlots.value.length === 0) {
      showSuccess('無可用地號', '請先新增地號到更新會')
      return
    }

    // Select random plot - use landNumber or plot_number to match the select value
    const randomPlot = availablePlots.value[Math.floor(Math.random() * availablePlots.value.length)]
    landForm.plot_number = randomPlot.landNumber || randomPlot.plot_number

    // Random area and ownership
    landForm.total_area = (Math.random() * 500 + 100).toFixed(2)
    landForm.ownership_numerator = Math.floor(Math.random() * 10) + 1
    landForm.ownership_denominator = Math.floor(Math.random() * 100) + 10

    showSuccess('測試地號已填入', '地號表單已自動填入測試資料')
  }

  /**
   * 填入建號測試資料
   */
  const fillBuildingTestData = async () => {
    if (counties.value.length === 0) {
      showSuccess('無可用縣市', '請稍後再試')
      return
    }

    // Select random county from available options
    const randomCounty = counties.value[Math.floor(Math.random() * counties.value.length)]
    buildingForm.county = randomCounty.code

    // Fetch districts for selected county
    await onBuildingCountyChange()

    if (buildingDistricts.value.length === 0) {
      showSuccess('無可用行政區', '該縣市沒有可用的行政區資料')
      return
    }

    // Select random district
    const randomDistrict = buildingDistricts.value[Math.floor(Math.random() * buildingDistricts.value.length)]
    buildingForm.district = randomDistrict.code

    // Fetch sections for selected district
    await onBuildingDistrictChange()

    if (buildingSections.value.length === 0) {
      showSuccess('無可用段小段', '該行政區沒有可用的段小段資料')
      return
    }

    // Select random section
    const randomSection = buildingSections.value[Math.floor(Math.random() * buildingSections.value.length)]
    buildingForm.section = randomSection.code

    // Fill other fields
    buildingForm.building_number_main = String(Math.floor(Math.random() * 9999) + 1).padStart(5, '0')
    buildingForm.building_number_sub = String(Math.floor(Math.random() * 999)).padStart(3, '0')
    buildingForm.building_area = (Math.random() * 200 + 50).toFixed(2)
    buildingForm.ownership_numerator = Math.floor(Math.random() * 10) + 1
    buildingForm.ownership_denominator = Math.floor(Math.random() * 100) + 10
    buildingForm.building_address = `${randomCounty.name}${randomDistrict.name}測試路${Math.floor(Math.random() * 999) + 1}號`

    showSuccess('測試建號已填入', '建號表單已自動填入測試資料')
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

    // 測試資料填充
    fillTestData,
    fillLandTestData,
    fillBuildingTestData,

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
