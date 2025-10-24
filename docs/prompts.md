1. 登入成功請幫我跳轉到內頁，並且內頁每個頁面都需要驗證身分，且sidebar要依據身分顯示資料
2.

## 問題修復 (2025-10-24)

### 問題 1: 登入跳轉與權限管理 ✅

**需求:**
登入成功請幫我跳轉到內頁，並且內頁每個頁面都需要驗證身分，且sidebar要依據身分顯示資料

**已修復:**
1. ✅ 更新 `pages/login.vue` - 使用 authStore 進行登入，依照角色跳轉
2. ✅ 更新 `layouts/main.vue` - 顯示用戶資訊、角色標籤、角色過濾選單、登出功能
3. ✅ 更新 `middleware/auth.js` - 修正登入頁面路徑為 `/login`
4. ✅ 更新 `stores/auth.js` - 修正登出重定向路徑為 `/login`
5. ✅ 添加頁面中介層保護 - `pages/index.vue`, `pages/tables/urban-renewal/index.vue`, `pages/tables/meeting/index.vue`

**登入後跳轉規則:**
- **Admin (管理員)** → `/tables/urban-renewal` (更新會管理)
- **Chairman (主任委員)** / **Member (地主成員)** → `/tables/meeting` (會議管理)
- **其他角色** → `/` (首頁)

**頁面權限設定:**
- 首頁 (`/`) - 所有已登入用戶
- 更新會管理 (`/tables/urban-renewal`) - 僅 admin
- 會議管理 (`/tables/meeting`) - admin, chairman, member
- 其他內頁 - 需要 auth middleware 保護

**側邊欄選單顯示規則:**

| 選單項目 | Admin | Chairman | Member | Observer |
|---------|-------|----------|--------|----------|
| 首頁 | ✅ | ✅ | ✅ | ✅ |
| 更新會管理 | ✅ | ❌ | ❌ | ❌ |
| 會議管理 | ✅ | ✅ | ✅ | ❌ |
| 投票管理 | ✅ | ✅ | ✅ | ❌ |
| 商城 | ✅ | ✅ | ✅ | ❌ |
| 購買紀錄 | ✅ | ✅ | ✅ | ❌ |
| 使用者資料變更 | ✅ | ✅ | ✅ | ✅ |
| 企業管理 | ✅ | ❌ | ❌ | ❌ |

**功能特色:**
- ✅ 顯示用戶姓名和角色標籤
- ✅ 根據角色動態顯示選單項目
- ✅ 點擊登出清除認證狀態並跳轉登入頁
- ✅ 所有內頁均受 auth middleware 保護
- ✅ 無權限訪問時顯示 `/unauthorized` 頁面

**測試方式:**
```bash
# 使用不同角色登入測試
# Admin: admin / password
# Chairman: chairman / password  
# Member: member1 / password
# Observer: observer1 / password
```
