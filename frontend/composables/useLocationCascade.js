/**
 * Location Cascade Composable
 * 處理縣市/行政區/段小段的級聯選擇邏輯
 */
import { ref } from 'vue'

export const useLocationCascade = () => {
  const { get } = useApi()

  // 狀態
  const counties = ref([])
  const districts = ref([])
  const sections = ref([])

  /**
   * 獲取所有縣市
   */
  const fetchCounties = async () => {
    try {
      const response = await get('/locations/counties')
      if (response.data?.status === 'success') {
        counties.value = response.data.data
      }
    } catch (err) {
      console.error('[useLocationCascade] Failed to fetch counties:', err)
      throw err
    }
  }

  /**
   * 獲取指定縣市的行政區
   * @param {string} countyCode - 縣市代碼
   */
  const fetchDistricts = async (countyCode) => {
    if (!countyCode) {
      districts.value = []
      return
    }

    try {
      const response = await get(`/locations/districts/${countyCode}`)
      if (response.data?.status === 'success') {
        districts.value = response.data.data
      }
    } catch (err) {
      console.error('[useLocationCascade] Failed to fetch districts:', err)
      throw err
    }
  }

  /**
   * 獲取指定行政區的段小段
   * @param {string} countyCode - 縣市代碼
   * @param {string} districtCode - 行政區代碼
   */
  const fetchSections = async (countyCode, districtCode) => {
    if (!countyCode || !districtCode) {
      sections.value = []
      return
    }

    try {
      const response = await get(`/locations/sections/${countyCode}/${districtCode}`)
      if (response.data?.status === 'success') {
        sections.value = response.data.data
      }
    } catch (err) {
      console.error('[useLocationCascade] Failed to fetch sections:', err)
      throw err
    }
  }

  /**
   * 當縣市改變時的處理
   * @param {string} countyCode - 縣市代碼
   */
  const onCountyChange = async (countyCode) => {
    // 重置下級選項
    districts.value = []
    sections.value = []

    if (!countyCode) return

    await fetchDistricts(countyCode)
  }

  /**
   * 當行政區改變時的處理
   * @param {string} countyCode - 縣市代碼
   * @param {string} districtCode - 行政區代碼
   */
  const onDistrictChange = async (countyCode, districtCode) => {
    // 重置下級選項
    sections.value = []

    if (!countyCode || !districtCode) return

    await fetchSections(countyCode, districtCode)
  }

  /**
   * 重置所有選項
   */
  const reset = () => {
    districts.value = []
    sections.value = []
  }

  return {
    // 狀態
    counties,
    districts,
    sections,

    // 方法
    fetchCounties,
    fetchDistricts,
    fetchSections,
    onCountyChange,
    onDistrictChange,
    reset
  }
}
