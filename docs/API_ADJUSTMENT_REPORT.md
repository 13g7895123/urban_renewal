# API 調整說明文件

## 頁面資訊
- **頁面路徑**: `/tables/meeting/[meetingId]/voting-topics/[topicId]/basic-info`
- **功能**: 投票議題基本資料編輯

## 相關 API

### 1. 取得會議資訊
- **Endpoint**: `GET /api/meetings/{id}`
- **用途**: 顯示頁面頂部的會議基本資訊（會議名稱、時間、更新會）。

### 2. 取得投票議題詳情
- **Endpoint**: `GET /api/voting-topics/{id}`
- **用途**: 載入目前的投票議題資料，包括議題名稱、匿名設定、以及現有的選項列表。
- **回傳新增欄位**: `voting_choices` - 投票選項列表（如同意/不同意）

### 3. 更新投票議題
- **Endpoint**: `PUT /api/voting-topics/{id}`
- **用途**: 儲存使用者編輯後的投票議題資料。
- **新增支援欄位**: `voting_options` - 投票選項列表

## 前端調整說明

### 1. 新增「投票選項」列表
- **位置**: 在「匿名與否」設定下方，與「所有權人」列表上方之間。
- **預設值**: 預設包含兩個項目：「同意」與「不同意」。
- **欄位**:
  - **編號**: 自動產生，靠左對齊。
  - **選項名稱**: 文字輸入欄位。
  - **操作**: 包含「新增項目」與「刪除」按鈕。
- **功能**:
  - **新增項目**: 點擊後在該項目下方插入一筆新資料。
  - **刪除**: 點擊後移除該筆資料。

### 2. 樣式調整
- **編號欄位**: 標題與內容皆調整為靠左對齊。
- **置頂欄位**: (針對下方的所有權人列表) 標題與內容皆調整為置中對齊。

## 後端調整說明

### 1. 新增資料表 `voting_choices`
用於儲存投票選項（如同意/不同意），與原本的 `voting_options`（所有權人選項）區分。

**資料表結構**:
| 欄位 | 類型 | 說明 |
|------|------|------|
| id | INT | 主鍵 |
| voting_topic_id | INT | 投票議題ID (外鍵) |
| choice_name | VARCHAR(255) | 選項名稱 |
| sort_order | INT | 排序順序 |
| created_at | DATETIME | 建立時間 |
| updated_at | DATETIME | 更新時間 |
| deleted_at | DATETIME | 軟刪除時間 |

### 2. 新增 Model
- **檔案**: `backend/app/Models/VotingChoiceModel.php`
- **功能**: 處理 `voting_choices` 資料表的 CRUD 操作

### 3. 修改 Controller
- **檔案**: `backend/app/Controllers/Api/VotingTopicController.php`
- **修改內容**:
  - `create()`: 支援接收 `voting_options` 欄位，若未提供則自動建立預設選項（同意/不同意）
  - `update()`: 支援更新 `voting_options` 欄位

### 4. 修改 VotingTopicModel
- **檔案**: `backend/app/Models/VotingTopicModel.php`
- **修改內容**: `getTopicWithDetails()` 方法新增回傳 `voting_choices` 欄位

## Payload 結構

**Request (PUT /api/voting-topics/{id})**:
```json
{
  "meeting_id": 8,
  "topic_name": "議題名稱",
  "is_anonymous": false,
  "voting_options": [
    { "name": "同意" },
    { "name": "不同意" }
  ],
  "property_owners": [
    { "name": "所有權人1", "is_pinned": false },
    { "name": "所有權人2", "is_pinned": true }
  ],
  "max_selections": 1,
  "accepted_count": 1,
  "alternate_count": 0,
  "land_area_ratio_numerator": 2,
  "land_area_ratio_denominator": 3,
  "building_area_ratio_numerator": 3,
  "building_area_ratio_denominator": 4,
  "people_ratio_numerator": 55,
  "people_ratio_denominator": 100,
  "remarks": "備註"
}
```

**Response (GET /api/voting-topics/{id})**:
```json
{
  "success": true,
  "message": "投票議題詳情",
  "data": {
    "id": 2,
    "meeting_id": 8,
    "topic_title": "議題名稱",
    "is_anonymous": 0,
    "voting_choices": [
      { "id": 1, "choice_name": "同意", "sort_order": 0 },
      { "id": 2, "choice_name": "不同意", "sort_order": 1 }
    ],
    "voting_options": [
      { "id": 1, "option_name": "所有權人1", "is_pinned": 0 }
    ]
  }
}
```

## 遷移檔案
- **檔案**: `backend/app/Database/Migrations/2025-12-06-010000_CreateVotingChoicesTable.php`
- **執行指令**: `docker exec urban_renewal_backend_dev php spark migrate`
