# OpenAPI 規範文件建立完成摘要

## 建立日期
2025-10-08

## 檔案清單

### 1. README.md (164 行)
- 合約目錄說明文件
- 檔案說明和使用方式
- 工具推薦和驗證方法
- 回應格式和 HTTP 狀態碼說明

### 2. common.yaml (458 行)
共用的 Schema 定義，包含：
- **安全性定義**: BearerAuth (JWT)
- **通用回應結構**: SuccessResponse, ErrorResponse, ValidationErrorResponse
- **分頁**: Pagination, PaginatedResponse
- **基礎欄位**: TimestampFields, AddressFields, ContactFields
- **枚舉類型**: 
  - MeetingType (會議類型)
  - MeetingStatus (會議狀態)
  - AttendanceType (報到類型)
  - VotingStatus (投票狀態)
  - VotingMethod (投票方式)
  - VoteChoice (投票選擇)
  - UserRole (使用者角色)
  - ExclusionType (排除類型)
- **通用參數**: PageParam, PerPageParam, SearchParam, IDPathParam
- **通用回應**: 標準錯誤回應模板

### 3. auth.yaml (477 行)
身份驗證 API，包含 **6 個端點**：
- `POST /api/auth/login` - 使用者登入
- `POST /api/auth/logout` - 使用者登出
- `POST /api/auth/refresh` - 刷新 Token
- `GET /api/auth/me` - 取得當前使用者資訊
- `POST /api/auth/forgot-password` - 忘記密碼
- `POST /api/auth/reset-password` - 重設密碼

**Schema 定義**: User

### 4. urban-renewals.yaml (1,049 行)
都市更新會 API，包含 **4 個模組**：

#### 都市更新會管理 (5 個端點)
- `GET /api/urban-renewals` - 取得都市更新會列表
- `POST /api/urban-renewals` - 建立都市更新會
- `GET /api/urban-renewals/{id}` - 取得都市更新會詳情
- `PUT /api/urban-renewals/{id}` - 更新都市更新會
- `DELETE /api/urban-renewals/{id}` - 刪除都市更新會

#### 所有權人管理 (6 個端點)
- `GET /api/urban-renewals/{id}/property-owners` - 取得所有權人列表
- `POST /api/urban-renewals/{id}/property-owners/import` - 批次匯入所有權人
- `GET /api/urban-renewals/{id}/property-owners/export` - 匯出所有權人資料
- `GET /api/property-owners/template` - 下載匯入範本
- `GET /api/property-owners/{id}` - 取得所有權人詳情
- `PUT /api/property-owners/{id}` - 更新所有權人
- `DELETE /api/property-owners/{id}` - 刪除所有權人

#### 土地資料管理 (5 個端點)
- `GET /api/urban-renewals/{id}/land-plots` - 取得土地列表
- `POST /api/urban-renewals/{id}/land-plots` - 建立土地資料
- `GET /api/land-plots/{id}` - 取得土地資料詳情
- `PUT /api/land-plots/{id}` - 更新土地資料
- `DELETE /api/land-plots/{id}` - 刪除土地資料

#### 地址階層資料 (3 個端點)
- `GET /api/locations/counties` - 取得縣市列表
- `GET /api/locations/districts/{countyCode}` - 取得鄉鎮市區列表
- `GET /api/locations/sections/{countyCode}/{districtCode}` - 取得地段列表

**Schema 定義**:
- UrbanRenewal, UrbanRenewalCreate, UrbanRenewalUpdate
- PropertyOwnerUpdate
- LandPlotCreate, LandPlotUpdate
- County, District, Section

### 5. meetings.yaml (1,070 行)
會議 API，包含 **2 個模組**：

#### 會議管理 (6 個端點)
- `GET /api/meetings` - 取得會議列表
- `POST /api/meetings` - 建立會議
- `GET /api/meetings/{id}` - 取得會議詳情
- `PUT /api/meetings/{id}` - 更新會議資料
- `DELETE /api/meetings/{id}` - 刪除會議
- `PATCH /api/meetings/{id}/status` - 更新會議狀態
- `GET /api/meetings/{id}/statistics` - 取得會議統計資料

#### 會員報到管理 (4 個端點)
- `POST /api/meeting-attendance/check-in` - 會員報到（單筆）
- `POST /api/meeting-attendance/batch-check-in` - 批次報到
- `GET /api/meeting-attendance/{meetingId}/summary` - 取得報到摘要
- `GET /api/meeting-attendance/{meetingId}/export` - 匯出報到資料

**Schema 定義**:
- Meeting, MeetingDetail, MeetingCreate, MeetingUpdate
- MeetingStatistics
- Attendance, AttendanceCheckIn, AttendanceSummary

### 6. voting.yaml (1,159 行)
投票 API，包含 **2 個模組**：

#### 投票議題管理 (6 個端點)
- `GET /api/voting-topics` - 取得投票議題列表
- `POST /api/voting-topics` - 建立投票議題
- `GET /api/voting-topics/{id}` - 取得投票議題詳情
- `PUT /api/voting-topics/{id}` - 更新投票議題
- `DELETE /api/voting-topics/{id}` - 刪除投票議題
- `PATCH /api/voting-topics/{id}/start-voting` - 開始投票
- `PATCH /api/voting-topics/{id}/close-voting` - 關閉投票

#### 投票表決與統計 (6 個端點)
- `POST /api/voting/vote` - 投票（單筆）
- `POST /api/voting/batch-vote` - 批次投票
- `GET /api/voting/my-vote/{topicId}` - 取得我的投票
- `DELETE /api/voting/remove-vote` - 移除投票
- `GET /api/voting/statistics/{topicId}` - 取得投票統計
- `GET /api/voting/detailed/{topicId}` - 取得詳細投票記錄
- `GET /api/voting/export/{topicId}` - 匯出投票結果

**Schema 定義**:
- VotingTopic, VotingTopicDetail, VotingTopicCreate, VotingTopicUpdate
- VotingRecord, VotingRecordDetail, VoteRequest
- VotingStatistics

## 統計資訊

### 總覽
- **總行數**: 4,377 行
- **總檔案數**: 6 個
- **API 端點總數**: 47 個
- **Schema 定義總數**: 30+ 個

### 各模組端點數量
- 身份驗證: 6 個端點
- 都市更新會: 5 個端點
- 所有權人: 7 個端點
- 土地資料: 5 個端點
- 地址階層: 3 個端點
- 會議管理: 7 個端點
- 會員報到: 4 個端點
- 投票議題: 7 個端點
- 投票表決: 6 個端點

## 規範特色

### 1. 完整的文件說明
- 每個端點都有詳細的功能說明
- 包含使用場景和注意事項
- 提供多個請求範例

### 2. 統一的回應格式
- 成功回應: `{ success: true, data: {...}, message: "..." }`
- 錯誤回應: `{ success: false, error: { code: "...", message: "..." } }`
- 分頁回應: 包含 pagination 物件

### 3. 完善的錯誤處理
- 標準 HTTP 狀態碼
- 詳細的錯誤代碼和訊息
- 驗證錯誤包含欄位級別的錯誤詳情

### 4. 安全性考量
- JWT Bearer Token 認證
- 權限檢查說明
- 敏感資料保護

### 5. 業務邏輯說明
- 狀態轉換規則
- 前置條件檢查
- 限制條件說明

### 6. 完整的 Schema 定義
- 欄位類型和格式
- 必填欄位標記
- 範例數據
- 欄位說明（正體中文）

## 使用方式

### 檢視規範
```bash
# 使用 Swagger UI
npx swagger-ui-watcher auth.yaml

# 使用 Redoc
npx @redocly/cli preview-docs auth.yaml
```

### 驗證規範
```bash
# 使用 swagger-cli
npx @apidevtools/swagger-cli validate auth.yaml

# 驗證所有檔案
for file in *.yaml; do
  echo "Validating $file..."
  npx @apidevtools/swagger-cli validate "$file"
done
```

### 產生客戶端程式碼
```bash
# TypeScript Axios 客戶端
openapi-generator generate -i auth.yaml -g typescript-axios -o ./generated/ts-client

# JavaScript 客戶端
openapi-generator generate -i auth.yaml -g javascript -o ./generated/js-client
```

## 未來擴充建議

### 可能新增的模組
1. **使用者管理 API** (`/api/users/*`)
   - 已在 Routes.php 中定義，待補充規範

2. **系統設定 API** (`/api/system-settings/*`)
   - 已在 Routes.php 中定義，待補充規範

3. **通知 API** (`/api/notifications/*`)
   - 已在 Routes.php 中定義，待補充規範

4. **文件管理 API** (`/api/documents/*`)
   - 已在 Routes.php 中定義，待補充規範

### 規範改進方向
1. 增加更多實際範例
2. 補充錯誤代碼清單
3. 建立測試用例
4. 整合 API 測試工具（如 Postman Collection）

## 相關文件

- **專案規格**: `/home/jarvis/project/bonus/urban_renewal/specs/001-view/spec.md`
- **API 路由定義**: `/home/jarvis/project/bonus/urban_renewal/backend/app/Config/Routes.php`
- **專案憲章**: `/home/jarvis/project/bonus/urban_renewal/.specify/spec.constitution`

## 維護說明

### 更新規範時請確保
1. 與實際 API 實作保持一致
2. 更新變更紀錄
3. 驗證 YAML 語法正確性
4. 測試範例請求的有效性
5. 保持正體中文說明的完整性

---

**建立者**: Claude Code  
**建立日期**: 2025-10-08  
**OpenAPI 版本**: 3.0.3  
**總行數**: 4,377 行
