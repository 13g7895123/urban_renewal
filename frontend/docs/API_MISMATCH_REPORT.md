# API 不一致報告 (已完成)
> 更新日期：2025-12-16
> 狀態：所有項目已實作

## 已完成的前端修改

### 出席管理 (`useAttendance.js`)
- ✅ 將 URL 結構調整為嵌套式：`/meetings/{id}/attendances/...`
- ✅ 調整所有方法的參數以接收 `meetingId`

### 會議管理 (`useMeetings.js`)
- ✅ 將 `searchMeetings` 改為呼叫 `getMeetings` 並帶入 `search` 參數

### 使用者管理 (`useUsers.js`)
- ✅ 註解掉文件中未列出的進階功能

---

## 已完成的後端實作

### MeetingController
- ✅ `GET /api/meetings/upcoming` - 取得即將到來的會議
- ✅ `GET /api/meetings/status-statistics` - 取得會議狀態統計
- ✅ `GET /api/meetings/search` - 搜尋會議

### UserController
- ✅ `GET /api/users/stats` - 取得使用者統計
- ✅ `PUT /api/users/profile` - 更新當前使用者資料
- ✅ `PATCH /api/users/{id}/password` - 變更使用者密碼（管理員用）

### CompanyController
- ✅ `GET /api/companies/{id}/managers` - 取得特定企業的管理者
- ✅ `GET /api/companies/{id}/users` - 取得特定企業的使用者

### Routes.php
- ✅ 新增上述所有 API 路由

---

## 修改的檔案列表

**前端：**
- `frontend/composables/useAttendance.js`
- `frontend/composables/useMeetings.js`
- `frontend/composables/useUsers.js`

**後端：**
- `backend/app/Controllers/Api/MeetingController.php`
- `backend/app/Controllers/Api/UserController.php`
- `backend/app/Controllers/Api/CompanyController.php`
- `backend/app/Config/Routes.php`
