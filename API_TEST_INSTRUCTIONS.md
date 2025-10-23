# API 連接測試說明

## 測試總結

已完成以下修復和驗證：

### ✅ 問題已修復

1. **環境變數配置錯誤** - 已修正 `NUXT_PUBLIC_BACKEND_PORT` 為 `9228`
2. **API URL 重複 `/api/` 問題** - 已修正 API URL 構建邏輯
3. **客戶端 vs 服務端 URL 混淆** - 添加智能切換邏輯
4. **前端容器構建配置** - 重新構建並載入正確的環境變數

### 📊 自動化測試結果

運行 `./test-connection.sh` 的結果：

```
✓ Backend API (http://localhost:9228/api/*) - OK
✓ Counties API (http://localhost:9228/api/locations/counties) - OK
✓ Frontend Home (http://localhost:4357/) - OK
✓ phpMyAdmin (http://localhost:9428/) - OK
```

## 快速測試方法

### 方法 1: 運行自動化測試腳本

```bash
./test-connection.sh
```

### 方法 2: 手動測試後端 API

```bash
# 測試 Urban Renewals API
curl http://localhost:9228/api/urban-renewals

# 測試 Counties API
curl http://localhost:9228/api/locations/counties

# 應該返回 JSON 格式的數據
```

### 方法 3: 瀏覽器開發者工具測試

1. **打開前端應用**
   ```
   http://localhost:4357
   ```

2. **打開瀏覽器開發者工具** (按 F12)

3. **切換到 Console 標籤**

4. **執行測試代碼**：

```javascript
// 測試 1: 檢查 API URL 配置
console.log('=== 環境配置 ===');
console.log('當前位置:', window.location.href);
console.log('預期 API URL:', 'http://localhost:9228/api');

// 測試 2: 直接測試 API 連接
console.log('\n=== 測試 API 連接 ===');
fetch('http://localhost:9228/api/urban-renewals')
  .then(response => {
    console.log('✓ API 連接成功!');
    console.log('HTTP Status:', response.status);
    console.log('請求 URL:', response.url);
    return response.json();
  })
  .then(data => {
    console.log('✓ 數據接收成功!');
    console.log('響應數據:', data);
  })
  .catch(error => {
    console.error('✗ API 連接失敗:', error);
  });

// 測試 3: 測試 Counties API
console.log('\n=== 測試 Counties API ===');
fetch('http://localhost:9228/api/locations/counties')
  .then(response => response.json())
  .then(data => {
    console.log('✓ Counties 數據:', data);
    if (data.status === 'success' && data.data && data.data.length > 0) {
      console.log('✓ 成功獲取', data.data.length, '個縣市');
      console.log('前 3 個縣市:', data.data.slice(0, 3));
    }
  })
  .catch(error => {
    console.error('✗ Counties API 失敗:', error);
  });
```

5. **查看結果**
   - 如果看到 `✓ API 連接成功!` 和數據，表示連接正常
   - 如果看到 `✗ API 連接失敗`，請檢查下面的故障排除步驟

### 方法 4: 使用 Network 標籤驗證

1. 打開瀏覽器開發者工具 (F12)
2. 切換到 **Network** 標籤
3. 刷新頁面或進行操作
4. 查看 API 請求
   - 確認請求 URL 是 `http://localhost:9228/api/...`
   - **不應該** 是 `http://backend:8000/api/...`

## 預期行為

### ✓ 正確的 API URL

在瀏覽器中應該看到：
```
http://localhost:9228/api/urban-renewals
http://localhost:9228/api/locations/counties
```

### ✓ Console 日誌

瀏覽器控制台應該顯示：
```
[API] Client-side using localhost URL: http://localhost:9228/api
[API] Base URL resolved to: http://localhost:9228/api
[API] GET http://localhost:9228/api/urban-renewals
[API] Success: http://localhost:9228/api/urban-renewals
```

### ✗ 錯誤的 API URL (已修復)

以下 URL **不應該**出現：
- ❌ `http://backend:8000/api/...` (Docker 內部地址)
- ❌ `http://backend:8000/api/api/...` (重複的 /api/)

## 服務端口配置

| 服務 | 主機端口 | 容器端口 | 用途 |
|------|---------|---------|------|
| 前端 (Frontend) | **4357** | 3000 | 網頁應用 |
| 後端 (Backend API) | **9228** | 8000 | API 服務 |
| phpMyAdmin | **9428** | 80 | 數據庫管理 |
| MariaDB | **9328** | 3306 | 數據庫 |

**重要**:
- 瀏覽器訪問後端應使用 `localhost:9228`
- Docker 容器內部通信使用 `backend:8000`

## 故障排除

### 問題 1: 仍然看到 `backend:8000`

**解決方案**: 清除瀏覽器緩存
```bash
# 在瀏覽器中按 Ctrl+Shift+Delete
# 或使用隱私模式/無痕模式重新打開
```

### 問題 2: API 返回 404

**檢查服務狀態**:
```bash
docker ps
```

確認所有容器都在運行：
- `urban_renewal-frontend-1` - Up
- `urban_renewal-backend-1` - Up
- `mariadb` - Up (healthy)

### 問題 3: 前端無法訪問

**檢查端口占用**:
```bash
lsof -i :4357  # 檢查 4357 端口
lsof -i :9228  # 檢查 9228 端口
```

**重啟服務**:
```bash
./start-dev.sh
```

### 問題 4: 需要查看詳細日誌

```bash
# 查看前端日誌
docker logs urban_renewal-frontend-1 --tail 50

# 查看後端日誌
docker logs urban_renewal-backend-1 --tail 50

# 查看 MariaDB 日誌
docker logs mariadb --tail 50
```

## 驗證清單

在瀏覽器中完成以下檢查：

- [ ] 前端可訪問: http://localhost:4357
- [ ] 後端 API 可訪問: http://localhost:9228/api/urban-renewals
- [ ] phpMyAdmin 可訪問: http://localhost:9428
- [ ] Browser Console 顯示正確的 API URL (localhost:9228)
- [ ] Network 標籤顯示請求到 localhost:9228
- [ ] API 返回成功的 JSON 響應
- [ ] 沒有 CORS 錯誤
- [ ] 沒有 404 錯誤

## 技術細節

### useApi.js 邏輯

前端的 `composables/useApi.js` 現在包含智能邏輯：

```javascript
// 客戶端 (瀏覽器) - 使用 localhost
if (isClient) {
  return `http://localhost:${config.public.backendPort}/api`
}

// 服務端 (SSR) - 使用 Docker 內部網絡
return `http://backend:8000/api`
```

### Docker Compose 配置

`docker-compose.local.yml` 中的環境變數：

```yaml
environment:
  - NUXT_PUBLIC_API_BASE_URL=http://backend:8000
  - NUXT_PUBLIC_BACKEND_HOST=backend
  - NUXT_PUBLIC_BACKEND_PORT=9228  # 主機端口!
```

## 總結

✅ **API 連接已完全修復**

- 後端 API 可以正常訪問
- 前端在瀏覽器中正確使用 `localhost:9228`
- 服務端渲染使用內部 Docker 網絡
- 所有端口配置正確

如果你看到任何錯誤，請參考上面的故障排除步驟或運行 `./test-connection.sh` 進行自動診斷。
