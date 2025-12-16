# API 不一致報告 (已更新)
> 產生日期：2025-12-16
> 狀態：前端已依據文件部分修正

本報告列出了前端修正後，尚待後端確認或處理的項目。

## 1. 已修正的前端模組

### 出席管理 (`useAttendance.js`)
- 已將 URL 結構調整為嵌套式：`/meetings/{id}/attendances/...`
- 已調整所有方法的參數以接收 `meetingId`
- **待確認**：後端是否已實作 `POST /api/meetings/{id}/attendances/export` (前端目前已改為 POST)

### 會議管理 (`useMeetings.js`)
- 已將 `searchMeetings` 改為呼叫 `getMeetings` 並帶入 `search` 參數
- **待後端處理**：
    - 缺少的端點：`GET /meetings/upcoming` (用於儀表板?)
    - 缺少的端點：`GET /meetings/status-statistics`

### 使用者管理 (`useUsers.js`)
- 已註解掉文件中未列出的功能，避免誤用：
    - `toggleUserStatus`
    - `changeUserPassword`
    - `restoreUser`
    - `forceDeleteUser`
- **待後端處理**：
    - 確認 `GET /users/stats` 是否應為 `/role-statistics` 或後端需實作 `/stats`

## 2. 仍需後端確認/實作項目列表

以下是前端依賴但文件/後端似乎尚未支援的功能：

1.  **儀表板與統計**
    - `GET /api/meetings/upcoming`: 取得即將到來的會議
    - `GET /api/meetings/status-statistics`: 取得會議狀態統計
    - `GET /api/users/stats`: 取得使用者統計 (文件中僅有 `/role-statistics`)

2.  **企業管理補強**
    - `GET /api/companies/{id}/managers`: 取得特定企業的管理者
    - `GET /api/companies/{id}/users`: 取得特定企業的使用者 (目前前端尚未使用此路徑，但可能需要)

3.  **其他**
    - `PUT /api/users/profile`: 目前文件僅有 GET，需確認更新個人資料的正確端點 (通常是 `PUT /users/{id}`)
    - `PATCH /api/users/{id}/password`: 變更密碼的正確端點

## 3. 下一步建議

請後端開發人員：
1.  確認上述「待後端處理」的 API 是否需要實作。
2.  若 API 已存在但路徑不同，請更新 API 文件。
3.  若無需實作，請告知前端尋找替代方案。
