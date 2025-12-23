# 註冊日誌 API 文件

此模組提供管理員查詢系統註冊嘗試的紀錄，包含成功與失敗的請求。

## 基底 URL
`/api/admin/registration-logs`

## 權限
公開存取 (Public) - 原始設計為 Admin Only，目前已移除驗證。

---

## 1. 取得日誌列表
取得分頁的註冊日誌列表，支援篩選。

- **URL**: `GET /api/admin/registration-logs`
- **Query Parameters**:
    - `page` (int, optional): 頁碼，預設 1
    - `per_page` (int, optional): 每頁筆數，預設 20
    - `status` (string, optional): 狀態篩選 (`success` | `error`)
    - `date_from` (string, optional): 起始日期 (`YYYY-MM-DD`)
    - `date_to` (string, optional): 結束日期 (`YYYY-MM-DD`)
    - `ip_address` (string, optional): IP 位址搜尋
- **Response**:
    ```json
    {
        "status": "success",
        "data": [
            {
                "id": "1",
                "request_data": { ... },
                "response_status": "error",
                "response_code": "400",
                "response_message": "帳號已透過 API 註冊但驗證失敗",
                "error_details": { ... },
                "ip_address": "172.18.0.1",
                "user_agent": "Mozilla/5.0...",
                "created_user_id": null,
                "created_at": "2025-12-23 13:00:00"
            }
        ],
        "pagination": {
            "total": 12,
            "page": 1,
            "per_page": 20,
            "total_pages": 1
        },
        "message": "取得註冊日誌列表成功"
    }
    ```

## 2. 取得統計資料
取得註冊嘗試的總覽統計數據。

- **URL**: `GET /api/admin/registration-logs/statistics`
- **Response**:
    ```json
    {
        "status": "success",
        "data": {
            "total": 150,
            "success_count": 120,
            "error_count": 30,
            "today_count": 5
        },
        "message": "取得統計資料成功"
    }
    ```

## 3. 取得單筆詳情
取得特定日誌的詳細資訊。

- **URL**: `GET /api/admin/registration-logs/{id}`
- **Path Parameters**:
    - `id` (int): 日誌 ID
- **Response**:
    ```json
    {
        "status": "success",
        "data": {
            "id": "1",
            "request_data": {
                "account": "testUser",
                "email": "test@example.com",
                "password": "******"
            },
            "response_status": "error",
            "response_code": "422",
            "response_message": "資料驗證失敗",
            "error_details": {
                "account": "The account field must contain a unique value."
            },
            "ip_address": "172.18.0.1",
            "user_agent": "Mozilla/5.0...",
            "created_user_id": null,
            "created_at": "2025-12-23 13:00:00"
        },
        "message": "取得註冊日誌詳情成功"
    }
    ```

## 4. 刪除日誌
刪除特定的註冊日誌。

- **URL**: `DELETE /api/admin/registration-logs/{id}`
- **Path Parameters**:
    - `id` (int): 日誌 ID
- **Response**:
    ```json
    {
        "status": "success",
        "message": "刪除註冊日誌成功"
    }
    ```
