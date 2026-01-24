# 開發測試頁面

這個目錄包含用於開發和除錯的測試頁面。

## 測試頁面列表

- `test-all-features.vue` - 完整功能測試頁面
- `test-api.vue` - API 測試頁面
- `test-features.vue` - 功能測試頁面
- `test-role.vue` - 角色權限測試頁面
- `test-session.vue` - Session 測試頁面
- `test-simple.vue` - 簡單測試頁面

## 訪問這些頁面

這些頁面在開發環境中可以通過以下路由訪問：

- `/dev-tests/test-all-features`
- `/dev-tests/test-api`
- `/dev-tests/test-features`
- `/dev-tests/test-role`
- `/dev-tests/test-session`
- `/dev-tests/test-simple`

## 生產環境

在生產環境中，建議通過 middleware 或環境變數來限制對這些頁面的訪問。

## 注意事項

這些頁面僅用於開發和測試目的，不應在生產環境中公開訪問。
