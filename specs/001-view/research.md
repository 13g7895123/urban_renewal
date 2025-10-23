# Research & Technical Decisions: 都更計票系統

**Feature**: 都更計票系統 - 完整功能規範
**Date**: 2025-10-08
**Status**: Complete

## Overview

本研究文件記錄都更計票系統的關鍵技術決策、替代方案評估和最佳實踐研究。由於這是一個現有專案的完整規範整理，大部分技術選型已經確定並在生產環境中運行，本文件主要記錄這些決策的理由和考量因素。

## Key Technical Decisions

### 1. 前後端分離架構

**Decision**: 採用前後端分離架構，前端使用 Nuxt 3，後端使用 CodeIgniter 4

**Rationale**:
- **開發效率**: 前後端可獨立開發、測試和部署，提高團隊協作效率
- **技術專精**: 前端專注於使用者體驗，後端專注於業務邏輯和資料處理
- **擴展性**: 未來可輕鬆支援行動裝置 App（共用後端 API）
- **維護性**: 前後端職責清晰，問題定位和修復更容易
- **效能優化**: 前端可利用 Nuxt 3 的 SSR/SSG 功能提升效能和 SEO

**Alternatives Considered**:
1. **Monolithic MVC 架構** (如 CodeIgniter 4 完整 MVC)
   - 優點：開發簡單、部署容易
   - 缺點：前後端耦合、難以擴展、不利於現代前端框架使用
   - **Rejected Because**: 系統複雜度高，需要豐富的前端互動功能（即時報到顯示、複雜表單、投票介面），前後端分離更適合

2. **微服務架構**
   - 優點：服務獨立、容錯性高、可獨立擴展
   - 缺點：複雜度高、運維成本高、需要服務網格和 API Gateway
   - **Rejected Because**: 系統規模尚不需要微服務，單體後端 + 前後端分離已足夠滿足需求

### 2. 前端技術堆疊：Nuxt 3 + Vue 3 + Nuxt UI

**Decision**: 前端使用 Nuxt 3 + Vue 3 + Nuxt UI + Tailwind CSS

**Rationale**:
- **Nuxt 3**: 提供完整的 Vue 3 框架解決方案，包含 SSR、檔案路由、自動匯入等功能
- **Vue 3 Composition API**: 提供更好的程式碼組織和重用性，符合現代前端開發趨勢
- **Nuxt UI**: 基於 Headless UI 和 Tailwind CSS 的高品質元件庫，提供開箱即用的 UI 元件
- **Tailwind CSS**: Utility-first CSS 框架，提供快速的樣式開發和一致的設計系統
- **TypeScript 替代**: 專案憲章要求不使用 TypeScript，完全採用 JavaScript ES6+

**Alternatives Considered**:
1. **Vuetify**（專案曾使用）
   - 優點：Material Design 元件豐富、文件完整
   - 缺點：Vue 3 支援不完整、客製化困難、Bundle 大小較大
   - **Rejected Because**: 專案憲章明確禁止使用 Vuetify，改用 Nuxt UI

2. **React + Next.js**
   - 優點：生態系統龐大、社群活躍、就業市場需求高
   - 缺點：學習曲線較陡、團隊已熟悉 Vue、需重寫現有程式碼
   - **Rejected Because**: 團隊已投資 Vue 生態系統，重寫成本過高

3. **純 Vue 3 (Vite)**
   - 優點：輕量、快速、靈活
   - 缺點：需自行配置路由、SSR、SEO 等功能
   - **Rejected Because**: Nuxt 3 提供完整的框架解決方案，減少配置工作

### 3. 後端技術堆疊：CodeIgniter 4 + MySQL

**Decision**: 後端使用 CodeIgniter 4 (PHP) + MySQL

**Rationale**:
- **CodeIgniter 4**: 輕量級 PHP 框架，學習曲線平緩，適合中小型專案
- **MVC 架構**: 清晰的程式碼結構，易於維護
- **MySQL**: 成熟穩定的關係型資料庫，支援事務和外鍵約束
- **PHP 生態**: 豐富的套件和資源，如 PHPSpreadsheet（Excel 處理）、JWT 函式庫等
- **部署簡單**: PHP + MySQL 是最常見的伺服器環境，部署和維護容易

**Alternatives Considered**:
1. **Laravel**
   - 優點：功能豐富、生態系統強大、Eloquent ORM 強大
   - 缺點：學習曲線較陡、對小型專案可能過於複雜
   - **Rejected Because**: CodeIgniter 4 已滿足需求，Laravel 的額外功能對本專案非必要

2. **Node.js + Express**
   - 優點：前後端使用同一語言、非同步 I/O 效能好、npm 生態豐富
   - 缺點：團隊不熟悉、缺乏成熟的 ORM、需重寫現有程式碼
   - **Rejected Because**: 團隊已熟悉 PHP 和 CodeIgniter，重寫成本過高

3. **Django (Python)**
   - 優點：功能完整、Admin 介面強大、ORM 優秀
   - 缺點：團隊不熟悉 Python、需重新學習
   - **Rejected Because**: 團隊已投資 PHP 生態系統

### 4. 投票權重計算策略

**Decision**: 基於所有權人持分比例計算投票權重，使用資料庫交易確保一致性

**Rationale**:
- **法定需求**: 都市更新投票需按土地/建物面積和所有權人數計算
- **精確計算**: 持分比例需精確計算到小數點後多位
- **資料一致性**: 使用資料庫交易確保投票記錄和統計的一致性
- **效能考量**: 預先計算每個所有權人的持分，避免每次投票時重複計算

**Implementation Approach**:
1. 在 `owner_land_ownerships` 和 `owner_building_ownerships` 表中記錄持分比例
2. 投票時計算該所有權人的總持分（土地 + 建物）
3. 統計時按持分加權計算同意/不同意/棄權的面積和人數
4. 使用資料庫交易確保投票記錄和統計的原子性

**Alternatives Considered**:
1. **簡單計數（不考慮持分）**
   - 優點：實作簡單、效能好
   - 缺點：不符合法定要求
   - **Rejected Because**: 法定要求必須按持分計算

2. **即時計算持分**
   - 優點：資料即時、不需預先儲存
   - 缺點：效能差、查詢複雜
   - **Rejected Because**: 效能不佳，預先儲存更有效率

### 5. 即時報到顯示實作

**Decision**: 使用 Server-Sent Events (SSE) 實作即時報到顯示

**Rationale**:
- **單向通訊**: 報到顯示只需伺服器推送資料到客戶端，不需客戶端回傳
- **簡單實作**: SSE 比 WebSocket 更簡單，瀏覽器原生支援
- **自動重連**: SSE 在連線中斷時自動重連
- **HTTP/2 友善**: SSE 可利用 HTTP/2 的多工特性

**Implementation Approach**:
1. 後端建立 SSE 端點：`GET /api/meetings/{id}/attendance/stream`
2. 當有新報到記錄時，推送事件到所有連線的客戶端
3. 前端使用 `EventSource` API 接收事件並更新 UI
4. 使用 Redis Pub/Sub 在多個後端實例間同步事件（如果需要水平擴展）

**Alternatives Considered**:
1. **WebSocket**
   - 優點：雙向通訊、低延遲
   - 缺點：實作複雜、需額外的 WebSocket 伺服器
   - **Rejected Because**: 本功能只需單向通訊，WebSocket 過於複雜

2. **輪詢 (Polling)**
   - 優點：實作簡單、相容性好
   - 缺點：延遲高、浪費頻寬和伺服器資源
   - **Rejected Because**: 即時性不佳，且浪費資源

3. **Long Polling**
   - 優點：即時性好、相容性好
   - 缺點：實作複雜、連線管理困難
   - **Rejected Because**: SSE 更簡單且效果相同

### 6. 批次資料處理（Excel 匯入/匯出）

**Decision**: 使用 PHPSpreadsheet 處理 Excel 檔案

**Rationale**:
- **功能完整**: 支援讀取和寫入 Excel 2007+ (XLSX) 格式
- **社群支援**: PHPSpreadsheet 是 PHPExcel 的繼任者，活躍維護
- **格式支援**: 支援樣式、公式、圖表等進階功能
- **記憶體優化**: 提供串流讀取和寫入，適合大型檔案

**Implementation Approach**:
1. **匯入**:
   - 檔案上傳到暫存目錄
   - PHPSpreadsheet 讀取 Excel 檔案
   - 驗證資料格式和必填欄位
   - 批次插入資料庫（使用交易）
   - 返回匯入結果報告（成功/失敗記錄）

2. **匯出**:
   - 查詢資料庫取得所有權人資料
   - PHPSpreadsheet 建立 Excel 檔案
   - 設定欄位標題和樣式
   - 輸出檔案供下載

**Alternatives Considered**:
1. **CSV 格式**
   - 優點：簡單、輕量、易解析
   - 缺點：不支援格式化、使用者體驗較差
   - **Rejected Because**: 使用者習慣 Excel 格式，CSV 不支援格式化

2. **前端處理（如 SheetJS）**
   - 優點：減輕伺服器負擔
   - 缺點：大型檔案效能差、安全性風險
   - **Rejected Because**: 後端處理更安全且可控

### 7. 並發投票處理策略

**Decision**: 使用資料庫交易 + 樂觀鎖定處理並發投票

**Rationale**:
- **資料一致性**: 交易確保投票記錄和統計的原子性
- **並發控制**: 樂觀鎖定避免投票衝突
- **效能平衡**: 相對於悲觀鎖定，樂觀鎖定在低衝突情況下效能更好
- **簡單實作**: 使用資料庫特性，無需額外的分散式鎖

**Implementation Approach**:
1. 在 `voting_records` 表中使用 `updated_at` 作為版本欄位
2. 投票時檢查該所有權人是否已投票：
   - 如未投票：插入新記錄
   - 如已投票：檢查 `updated_at` 是否匹配，若匹配則更新，否則返回衝突錯誤
3. 使用資料庫交易包裹整個投票流程
4. 投票成功後更新 `voting_topics` 表的統計欄位

**Alternatives Considered**:
1. **悲觀鎖定（FOR UPDATE）**
   - 優點：確保強一致性
   - 缺點：效能較差、可能造成死鎖
   - **Rejected Because**: 投票衝突機率低，樂觀鎖定已足夠

2. **分散式鎖（Redis）**
   - 優點：適合分散式環境
   - 缺點：增加複雜度、需額外維護 Redis
   - **Rejected Because**: 系統規模尚不需要分散式鎖

3. **佇列處理（Message Queue）**
   - 優點：削峰填谷、非同步處理
   - 缺點：實作複雜、增加延遲、使用者體驗較差
   - **Rejected Because**: 投票需即時回饋，非同步處理不適合

### 8. 身份驗證與授權

**Decision**: 使用 JWT Token 進行身份驗證，角色權限檢查使用中介軟體

**Rationale**:
- **無狀態**: JWT 是無狀態的，伺服器不需儲存 Session
- **可擴展**: 適合多伺服器環境，無需 Session 同步
- **標準化**: JWT 是業界標準，有豐富的函式庫支援
- **安全性**: 使用 HMAC-SHA256 或 RSA 簽名確保 Token 不被竄改

**Implementation Approach**:
1. 登入成功後產生 JWT Token，包含使用者 ID、角色等資訊
2. Token 有效期設定為 24 小時
3. 提供 Refresh Token 機制（有效期 7 天）
4. 前端將 Token 儲存在 localStorage 或 Cookie
5. 每次 API 請求在 Header 中帶入 Token：`Authorization: Bearer <token>`
6. 後端中介軟體驗證 Token 並解析使用者資訊
7. 根據使用者角色檢查權限

**Security Considerations**:
- Token 儲存在 httpOnly Cookie 中更安全（防止 XSS）
- 使用 HTTPS 防止 Token 被竊取
- Token 中不應包含敏感資訊（如密碼）
- 實作 Token 黑名單機制處理登出和強制過期

**Alternatives Considered**:
1. **Session-based Authentication**
   - 優點：簡單、可隨時撤銷
   - 缺點：需 Session 儲存、不適合分散式環境
   - **Rejected Because**: JWT 更適合前後端分離和擴展

2. **OAuth 2.0**
   - 優點：標準化、支援第三方登入
   - 缺點：實作複雜、對本專案過於複雜
   - **Rejected Because**: 系統無需第三方登入，JWT 已足夠

### 9. 開發環境配置

**Decision**: 使用 Docker Compose 統一管理開發環境，Port 配置為 4000-4999 範圍

**Rationale**:
- **一致性**: 所有開發者使用相同的環境配置
- **隔離性**: Docker 容器隔離，不影響本機環境
- **可重現**: 環境配置版本控制，問題可重現
- **Port 避免衝突**: 使用 4000-4999 範圍避免與常用 Port (3000-3999) 衝突

**Port Allocation**:
- 前端開發伺服器：4001
- 後端 API 服務：4002
- MySQL 資料庫：4306
- phpMyAdmin：4003
- Redis（選用）：6379（保持預設）

**Alternatives Considered**:
1. **手動安裝環境**
   - 優點：輕量、直接
   - 缺點：環境不一致、問題難以重現
   - **Rejected Because**: Docker 提供更好的一致性

2. **使用 Vagrant**
   - 優點：完整的虛擬機環境
   - 缺點：資源消耗大、啟動慢
   - **Rejected Because**: Docker 更輕量且足夠

### 10. 資料庫設計原則

**Decision**: 遵循第三正規化（3NF），使用外鍵約束，所有表格使用軟刪除

**Rationale**:
- **3NF**: 減少資料冗余，提高資料一致性
- **外鍵約束**: 確保參照完整性，防止孤立記錄
- **軟刪除**: 保留歷史資料，支援資料恢復和審計
- **索引策略**: 為常用查詢欄位建立索引，提升查詢效能

**Key Design Decisions**:
1. **表格命名**: 使用複數形式（如 `users`、`meetings`）
2. **欄位命名**: 使用蛇形命名法（如 `created_at`、`meeting_date`）
3. **時間戳記**: 所有表格包含 `created_at`、`updated_at`、`deleted_at`
4. **主鍵**: 所有表格使用自動遞增的整數 `id`
5. **外鍵命名**: 格式為 `{table_name}_id`（如 `urban_renewal_id`）

**Denormalization Considerations**:
- `voting_topics` 表中冗余儲存統計資料（`total_votes`、`agree_votes` 等）
- 原因：投票統計查詢頻繁，即時計算效能差
- 更新策略：每次投票時更新統計欄位（使用交易確保一致性）

**Alternatives Considered**:
1. **NoSQL（如 MongoDB）**
   - 優點：彈性 schema、水平擴展容易
   - 缺點：缺乏事務支援（舊版本）、查詢較複雜
   - **Rejected Because**: 系統需要強一致性和複雜查詢，關係型資料庫更適合

## Best Practices Research

### Vue 3 + Nuxt 3 Best Practices

1. **使用 Composition API**:
   ```javascript
   <script setup>
   import { ref, computed, onMounted } from 'vue';

   // 響應式資料
   const count = ref(0);

   // 計算屬性
   const doubleCount = computed(() => count.value * 2);

   // 生命週期
   onMounted(() => {
     console.log('元件已掛載');
   });
   </script>
   ```

2. **使用 Composables 封裝可重用邏輯**:
   ```javascript
   // composables/useAuth.js
   export function useAuth() {
     const user = ref(null);
     const isAuthenticated = computed(() => !!user.value);

     const login = async (credentials) => {
       // 登入邏輯
     };

     return { user, isAuthenticated, login };
   }
   ```

3. **使用 useFetch 進行資料取得**:
   ```javascript
   const { data, pending, error } = await useFetch('/api/users');
   ```

4. **使用中介軟體保護路由**:
   ```javascript
   // middleware/auth.js
   export default defineNuxtRouteMiddleware((to, from) => {
     const auth = useAuth();
     if (!auth.isAuthenticated) {
       return navigateTo('/login');
     }
   });
   ```

### CodeIgniter 4 Best Practices

1. **使用 Resource Controller**:
   ```php
   class Users extends ResourceController
   {
       protected $modelName = 'App\Models\UserModel';
       protected $format = 'json';

       public function index() {
           $users = $this->model->findAll();
           return $this->respond(['status' => 'success', 'data' => $users]);
       }
   }
   ```

2. **使用模型驗證**:
   ```php
   protected $validationRules = [
       'name' => 'required|min_length[3]',
       'email' => 'required|valid_email|is_unique[users.email]',
   ];

   protected $validationMessages = [
       'name' => ['required' => '姓名為必填欄位'],
       'email' => ['required' => '電子郵件為必填欄位'],
   ];
   ```

3. **使用資料庫交易**:
   ```php
   $db = \Config\Database::connect();
   $db->transStart();

   // 執行多個資料庫操作

   $db->transComplete();

   if ($db->transStatus() === FALSE) {
       // 交易失敗
   }
   ```

4. **使用查詢建構器避免 SQL 注入**:
   ```php
   $users = $this->builder()
       ->where('status', 1)
       ->where('created_at >', $date)
       ->get()
       ->getResultArray();
   ```

### Security Best Practices

1. **JWT Token 安全**:
   - 使用強密鑰（至少 256 位元）
   - 設定合理的過期時間（24 小時）
   - 實作 Refresh Token 機制
   - 在 httpOnly Cookie 中儲存 Token

2. **CORS 配置**:
   ```php
   $config->allowedOrigins = ['http://localhost:4001'];
   $config->allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
   $config->allowedHeaders = ['Content-Type', 'Authorization'];
   ```

3. **輸入驗證**:
   - 前端和後端都需驗證
   - 使用 CodeIgniter 的驗證規則
   - 驗證錯誤訊息使用正體中文

4. **XSS 防護**:
   - 使用 CodeIgniter 的 `esc()` 函數轉義輸出
   - 前端使用 Vue 的自動轉義

5. **CSRF 防護**:
   - 啟用 CodeIgniter 的 CSRF 保護
   - API 使用 JWT Token，可免除 CSRF

### Performance Best Practices

1. **前端效能**:
   - 使用 Nuxt 的自動程式碼分割
   - 使用 `useLazyFetch` 延遲載入資料
   - 圖片使用 Nuxt Image 優化
   - 使用 Tailwind CSS 的 PurgeCSS 移除未使用的樣式

2. **後端效能**:
   - 使用資料庫索引加速查詢
   - 避免 N+1 查詢問題（使用 JOIN 或預載入）
   - 使用 Redis 快取熱門資料
   - 實作 API 分頁（預設每頁 20 筆）

3. **資料庫效能**:
   - 為常用查詢欄位建立索引
   - 使用 EXPLAIN 分析慢查詢
   - 定期執行 OPTIMIZE TABLE
   - 設定適當的連線池大小

## Implementation Priorities

基於功能規範中的 User Story 優先級，建議實作順序：

### Phase 1 (P1 - High Priority)
1. 身份驗證與授權（FR-001 至 FR-006）
2. 都市更新會管理（FR-007 至 FR-011）
3. 會議管理（FR-027 至 FR-033）
4. 投票議題管理（FR-043 至 FR-048）
5. 投票表決管理（FR-049 至 FR-055）

### Phase 2 (P2 - Medium Priority)
6. 地籍資料管理（FR-012 至 FR-016）
7. 所有權人管理（FR-017 至 FR-022）
8. 會員報到管理（FR-034 至 FR-042）
9. 投票結果統計（FR-056 至 FR-061）
10. 使用者管理（FR-081 至 FR-087）

### Phase 3 (P3 - Low Priority)
11. 所有權關係管理（FR-023 至 FR-026）
12. 系統設定管理（FR-062 至 FR-066）
13. 通知系統（FR-067 至 FR-073）
14. 文件管理（FR-074 至 FR-080）

## References

### Documentation
- [Vue 3 官方文件](https://vuejs.org/)
- [Nuxt 3 官方文件](https://nuxt.com/)
- [Nuxt UI 文件](https://ui.nuxt.com/)
- [CodeIgniter 4 官方文件](https://codeigniter.com/user_guide/)
- [Tailwind CSS 文件](https://tailwindcss.com/)

### Libraries & Tools
- [PHPSpreadsheet](https://phpspreadsheet.readthedocs.io/)
- [Firebase JWT PHP](https://github.com/firebase/php-jwt)
- [Pinia (Vue Store)](https://pinia.vuejs.org/)
- [Vitest (Testing)](https://vitest.dev/)

### Best Practices
- [Vue 3 Composition API Best Practices](https://vuejs.org/guide/extras/composition-api-faq.html)
- [CodeIgniter 4 Best Practices](https://codeigniter.com/user_guide/general/common_functions.html)
- [RESTful API Design Best Practices](https://restfulapi.net/)
- [JWT Best Practices](https://tools.ietf.org/html/rfc8725)

---

**Status**: Research Complete ✅
**Next Step**: Proceed to Phase 1 - Design & Contracts
