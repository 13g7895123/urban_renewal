# API 不一致報告
> 產生日期：2025-12-16
> 範圍：比較 `frontend/composables` 與 `backend/docs/API_DOCUMENTATION.md`

本報告列出了前端 API 使用方式與後端 API 文件規範不符之處。

## 1. 認證與使用者 (`useAuth.js`, `useUsers.js`)

| 前端呼叫 | 檔案 | 文件規範 API | 問題說明 |
|--------------|------|----------------|-------|
| `PUT /users/profile` | `useAuth.js` | `GET /api/users/profile` | `/users/profile` 文件中無 `PUT` 端點。使用者更新應使用 `PUT /api/users/{id}`。 |
| `POST /users/change-password` | `useAuth.js` | `POST /api/users/change-password` | **相符**。（註：文件將其列在使用者管理下） |
| `PATCH /users/{id}/password` | `useUsers.js` | N/A | 文件中找不到此端點。 |
| `PATCH /users/{id}/restore` | `useUsers.js` | N/A | 文件中找不到此端點。 |
| `DELETE /users/{id}/force` | `useUsers.js` | N/A | 文件中找不到此端點。 |
| `GET /users/stats` | `useUsers.js` | `GET /api/users/role-statistics`? | URL 不符。文件使用的是 `/role-statistics`。 |

## 2. 企業管理 (`useCompany.js`)

| 前端呼叫 | 檔案 | 文件規範 API | 問題說明 |
|--------------|------|----------------|-------|
| `GET /companies/{id}/managers` | `useCompany.js` | N/A | 文件中無明確定義。文件建議使用 `GET /api/urban-renewals/company-managers`（當前企業）或帶有篩選條件的 `GET /api/users`。 |
| `GET /companies/{id}/users` | `useCompany.js` | N/A | 文件中無明確定義。使用者應透過 `GET /api/users?company_id={id}` 取得。 |

## 3. 會議管理 (`useMeetings.js`)

| 前端呼叫 | 檔案 | 文件規範 API | 問題說明 |
|--------------|------|----------------|-------|
| `GET /meetings/search` | `useMeetings.js` | `GET /api/meetings?search=...` | 前端將搜尋視為獨立資源路徑，而非 index 的查詢參數。 |
| `GET /meetings/upcoming` | `useMeetings.js` | N/A | 文件中找不到此端點。文件中有 `GET /api/voting-topics/upcoming`。 |
| `GET /meetings/status-statistics`| `useMeetings.js` | N/A | 文件中找不到此端點。 |

## 4. 出席管理 (`useAttendance.js`)

| 前端呼叫 | 檔案 | 文件規範 API | 問題說明 |
|--------------|------|----------------|-------|
| `GET /meeting-attendance` | `useAttendance.js` | `GET /api/meetings/{id}/attendances` | URL 結構不符。前端假設由全域出席資源；後端將其嵌套在會議下。 |
| `POST /meeting-attendance/check-in` | `useAttendance.js` | `POST /api/meetings/{meetingId}/attendances/{ownerId}` | URL 和方法不符。前端使用通用報到端點；後端使用特定資源路徑。 |
| `POST /meeting-attendance/batch-check-in` | `useAttendance.js` | `POST /api/meetings/{id}/attendances/batch` | URL 不符。 |
| `PATCH /meeting-attendance/{id}/update-status` | `useAttendance.js` | `PUT /api/meetings/{meetingId}/attendances/{ownerId}` | URL 和方法不符。 |
| `GET /meeting-attendance/{id}/summary` | `useAttendance.js` | `GET /api/meetings/{id}/attendances/statistics` | URL 不符。 |
| `GET /meeting-attendance/{id}/export` | `useAttendance.js` | `POST /api/meetings/{id}/attendances/export` | 方法不符（GET vs POST）且 URL 不符（前端使用嵌套資源 ID vs 會議 ID）。 |

## 5. 其他發現

- `useVoting.js`: 大致相符。
- `useSystemSettings.js`: 相符。
- `useUrbanRenewal.js`: 相符。
- `usePropertyOwnerForm.js`: 相符。

## 建議事項

1.  **重構 `useAttendance.js`**：此模組差異最大。應重寫以使用嵌套 URL 結構 `/meetings/{id}/...`。
2.  **重構 `useMeetings.js`**：更新 `search` 改用查詢參數。若後端确实需要，請新增或實作缺少的端點（`upcoming`, `status-statistics`）。
3.  **重構 `useUsers.js`**：確認 `restore` 和 `forceDelete` 是否為預期功能，若是則在後端實作；否則從前端移除。
4.  **後端驗證**：檢查 `Routes.php` 是否實際實作了這些「遺失」的端點（例如 `/meetings/search`），這可能意味著文件已過時，而非前端錯誤。
