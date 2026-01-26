/**
 * Land and Building Management Composable
 * 處理地號和建號的新增、刪除、格式化邏輯
 */
import { reactive } from 'vue'

export const useLandBuilding = () => {
  /**
   * 新增地號
   * @param {Array} lands - 地號陣列
   * @param {Object} landData - 要新增的地號資料
   * @param {Object} availablePlots - 可用地號列表(用於查找顯示名稱)
   */
  const addLand = (lands, landData, availablePlots = []) => {
    // 檢查是否已存在相同的地號
    const isDuplicate = lands.some(land => land.plot_number === landData.plot_number)
    
    if (isDuplicate) {
      throw new Error(`地號 ${landData.plot_number} 已經存在，請勿重複新增`)
    }

    // 從可用地號列表中找到對應的地號資訊
    const selectedPlot = availablePlots.find(plot =>
      (plot.landNumber || plot.plot_number) === landData.plot_number
    )

    lands.push({
      plot_number: landData.plot_number,
      plot_number_display: selectedPlot?.chineseFullLandNumber || selectedPlot?.fullLandNumber || landData.plot_number,
      total_area: landData.total_area,
      ownership_numerator: landData.ownership_numerator,
      ownership_denominator: landData.ownership_denominator
    })
  }

  /**
   * 刪除地號
   * @param {Array} lands - 地號陣列
   * @param {number} index - 要刪除的索引
   */
  const removeLand = (lands, index) => {
    lands.splice(index, 1)
  }

  /**
   * 新增建號
   * @param {Array} buildings - 建號陣列
   * @param {Object} buildingData - 要新增的建號資料
   * @param {Object} locationData - 地區資料(counties, districts, sections)
   */
  const addBuilding = (buildings, buildingData, locationData) => {
    const { counties = [], districts = [], sections = [] } = locationData

    // 檢查是否已存在相同的建號（縣市、行政區、段小段、主號、副號都相同）
    const isDuplicate = buildings.some(building =>
      building.county === buildingData.county &&
      building.district === buildingData.district &&
      building.section === buildingData.section &&
      building.building_number_main === buildingData.building_number_main &&
      building.building_number_sub === buildingData.building_number_sub
    )
    
    if (isDuplicate) {
      const buildingNumber = buildingData.building_number_sub 
        ? `${buildingData.building_number_main}-${buildingData.building_number_sub}`
        : buildingData.building_number_main
      throw new Error(`建號 ${buildingNumber} 已經存在，請勿重複新增`)
    }

    // 取得中文名稱用於顯示
    const countyObj = counties.find(c => c.code === buildingData.county)
    const districtObj = districts.find(d => d.code === buildingData.district)
    const sectionObj = sections.find(s => s.code === buildingData.section)

    const countyName = countyObj ? countyObj.name : buildingData.county
    const districtName = districtObj ? districtObj.name : buildingData.district
    const sectionName = sectionObj ? sectionObj.name : buildingData.section

    buildings.push({
      county: buildingData.county,
      district: buildingData.district,
      section: buildingData.section,
      location: `${countyName}/${districtName}/${sectionName}`,
      building_number_main: buildingData.building_number_main,
      building_number_sub: buildingData.building_number_sub,
      building_area: buildingData.building_area,
      ownership_numerator: buildingData.ownership_numerator,
      ownership_denominator: buildingData.ownership_denominator,
      building_address: buildingData.building_address
    })
  }

  /**
   * 刪除建號
   * @param {Array} buildings - 建號陣列
   * @param {number} index - 要刪除的索引
   */
  const removeBuilding = (buildings, index) => {
    buildings.splice(index, 1)
  }

  /**
   * 格式化建號顯示
   * @param {Object} building - 建號物件
   * @returns {string} 格式化後的建號
   */
  const formatBuildingNumber = (building) => {
    const main = building.building_number_main || building.buildingNumberMain || ''
    const sub = building.building_number_sub || building.buildingNumberSub || ''

    if (!main && !sub) return '-'
    if (!sub || sub === '0' || sub === 0) return main
    return `${main}-${sub}`
  }

  /**
   * 格式化地號顯示
   * @param {Object} land - 地號物件
   * @returns {string} 格式化後的地號
   */
  const formatLandNumber = (land) => {
    // 如果有 plot_number_display,優先使用
    if (land.plot_number_display) {
      return land.plot_number_display
    }

    // 如果有 chineseFullLandNumber,優先使用
    if (land.chineseFullLandNumber) {
      return land.chineseFullLandNumber
    }

    // 如果有 fullLandNumber,使用它
    if (land.fullLandNumber) {
      return land.fullLandNumber
    }

    // 如果 plot_number 已經格式化,直接使用
    if (land.plot_number && land.plot_number.includes('-')) {
      return land.plot_number
    }

    // 嘗試從 main 和 sub 組合
    const main = land.land_number_main || land.landNumberMain || ''
    const sub = land.land_number_sub || land.landNumberSub || ''

    if (!main && !sub) return land.plot_number || '-'
    if (!sub || sub === '0' || sub === 0) return main
    return `${main}-${sub}`
  }

  /**
   * 建立空的地號表單資料
   * @returns {Object} 地號表單資料
   */
  const createEmptyLandForm = () => reactive({
    plot_number: '',
    total_area: '',
    ownership_numerator: '',
    ownership_denominator: ''
  })

  /**
   * 建立空的建號表單資料
   * @returns {Object} 建號表單資料
   */
  const createEmptyBuildingForm = () => reactive({
    county: '',
    district: '',
    section: '',
    building_number_main: '',
    building_number_sub: '',
    building_area: '',
    ownership_numerator: '',
    ownership_denominator: '',
    building_address: ''
  })

  /**
   * 重置地號表單
   * @param {Object} landForm - 地號表單物件
   */
  const resetLandForm = (landForm) => {
    landForm.plot_number = ''
    landForm.total_area = ''
    landForm.ownership_numerator = ''
    landForm.ownership_denominator = ''
  }

  /**
   * 重置建號表單
   * @param {Object} buildingForm - 建號表單物件
   */
  const resetBuildingForm = (buildingForm) => {
    buildingForm.county = ''
    buildingForm.district = ''
    buildingForm.section = ''
    buildingForm.building_number_main = ''
    buildingForm.building_number_sub = ''
    buildingForm.building_area = ''
    buildingForm.ownership_numerator = ''
    buildingForm.ownership_denominator = ''
    buildingForm.building_address = ''
  }

  /**
   * 驗證地號資料
   * @param {Object} landData - 地號資料
   * @returns {boolean} 是否有效
   */
  const validateLand = (landData) => {
    return !!(
      landData.plot_number &&
      landData.total_area &&
      landData.ownership_numerator &&
      landData.ownership_denominator
    )
  }

  /**
   * 驗證建號資料
   * @param {Object} buildingData - 建號資料
   * @returns {boolean} 是否有效
   */
  const validateBuilding = (buildingData) => {
    return !!(
      buildingData.county &&
      buildingData.district &&
      buildingData.section &&
      buildingData.building_number_main &&
      buildingData.building_number_sub &&
      buildingData.building_area &&
      buildingData.ownership_numerator &&
      buildingData.ownership_denominator
    )
  }

  return {
    // 地號相關
    addLand,
    removeLand,
    formatLandNumber,
    createEmptyLandForm,
    resetLandForm,
    validateLand,

    // 建號相關
    addBuilding,
    removeBuilding,
    formatBuildingNumber,
    createEmptyBuildingForm,
    resetBuildingForm,
    validateBuilding
  }
}
