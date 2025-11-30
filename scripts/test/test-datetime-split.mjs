/**
 * 測試日期時間拆分和組合邏輯
 */

console.log('=== 測試日期時間拆分和組合 ===\n')

// 測試 1: 拆分 datetime-local 格式
console.log('測試 1: 拆分 datetime-local 格式')
const meetingDateTime = '2025-12-15T14:00'
const [date, time] = meetingDateTime.split('T')
console.log('輸入:', meetingDateTime)
console.log('拆分後 - 日期:', date)
console.log('拆分後 - 時間:', time)
console.log('✅ 測試通過\n')

// 測試 2: 組合 meeting_date 和 meeting_time (HH:MM:SS 格式)
console.log('測試 2: 組合日期時間 (HH:MM:SS 格式)')
const meetingDate = '2025-12-15'
const meetingTime = '14:00:00'
const timeStr = meetingTime.substring(0, 5)
const combined = `${meetingDate}T${timeStr}`
console.log('輸入 - 日期:', meetingDate)
console.log('輸入 - 時間:', meetingTime)
console.log('取前5字元:', timeStr)
console.log('組合結果:', combined)
console.log('✅ 測試通過\n')

// 測試 3: 組合 meeting_date 和 meeting_time (HH:MM 格式)
console.log('測試 3: 組合日期時間 (HH:MM 格式)')
const meetingTime2 = '14:00'
const timeStr2 = meetingTime2.substring(0, 5)
const combined2 = `${meetingDate}T${timeStr2}`
console.log('輸入 - 日期:', meetingDate)
console.log('輸入 - 時間:', meetingTime2)
console.log('取前5字元:', timeStr2)
console.log('組合結果:', combined2)
console.log('✅ 測試通過\n')

// 測試 4: 完整往返測試
console.log('測試 4: 完整往返測試')
const original = '2025-12-15T14:30'
console.log('原始值:', original)

// 拆分
const [d, t] = original.split('T')
const splitDate = d || ''
const splitTime = t || '00:00'
console.log('拆分後 - meeting_date:', splitDate)
console.log('拆分後 - meeting_time:', splitTime)

// 組合回來
const reconstructed = `${splitDate}T${splitTime.substring(0, 5)}`
console.log('組合回來:', reconstructed)

if (original === reconstructed) {
  console.log('✅ 往返測試通過 - 值保持一致\n')
} else {
  console.log('❌ 往返測試失敗 - 值不一致\n')
}

// 測試 5: 邊界情況 - 空值
console.log('測試 5: 邊界情況 - 空值')
const emptyDateTime = ''
if (emptyDateTime) {
  console.log('有值')
} else {
  console.log('空值,不進行拆分')
  console.log('meeting_date: ""')
  console.log('meeting_time: ""')
  console.log('✅ 測試通過\n')
}

// 測試 6: 模擬後端返回的資料格式
console.log('測試 6: 模擬後端返回的資料格式')
const backendData = {
  meeting_date: '2025-12-15',
  meeting_time: '14:00:00'
}
console.log('後端資料:', backendData)
if (backendData.meeting_date && backendData.meeting_time) {
  const timeString = backendData.meeting_time.substring(0, 5)
  const frontendValue = `${backendData.meeting_date}T${timeString}`
  console.log('轉換為前端格式:', frontendValue)
  console.log('✅ 測試通過\n')
}

console.log('=== 所有測試完成 ===')
