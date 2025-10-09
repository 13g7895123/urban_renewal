# Implementation Plan: 都更計票系統 - 完整功能規範

**Branch**: `001-view` | **Date**: 2025-10-08 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-view/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

本實作計劃針對都更計票系統的完整功能規範進行設計和架構規劃。系統為都市更新組織提供會員管理、會議規劃、出席簽到、投票表決和統計報表等完整功能。採用前後端分離架構，前端使用 Nuxt 3 + Vue 3 + Nuxt UI，後端使用 CodeIgniter 4 (PHP) + MySQL。

主要技術挑戰包括：
- 複雜的投票權重計算（基於所有權人持分比例）
- 即時報到顯示功能（大螢幕顯示）
- 大量並發投票的資料一致性處理
- 完整的權限管理和審計日誌

## Technical Context

**Language/Version**:
- 前端：JavaScript ES6+ (不使用 TypeScript)、Vue 3、Nuxt 3
- 後端：PHP 7.4+ (CodeIgniter 4 框架)

**Primary Dependencies**:
- 前端：Nuxt 3、Vue 3、Nuxt UI、Tailwind CSS、Heroicons、Pinia
- 後端：CodeIgniter 4、JWT Authentication、PHPUnit

**Storage**:
- MySQL 5.7+（主要資料庫）
- Redis（選用，用於快取和 Session 管理）

**Testing**:
- 前端：Vitest + Vue Test Utils
- 後端：PHPUnit
- E2E：Playwright 或 Cypress（選用）

**Target Platform**:
- 前端：現代瀏覽器（Chrome、Firefox、Safari、Edge 最新兩個主要版本）
- 後端：Linux 伺服器（Docker 容器化部署）
- 資料庫：MySQL 5.7+ / MariaDB 10.3+

**Project Type**: Web application (前後端分離)

**Performance Goals**:
- API 回應時間 < 200ms (P95)
- 前端頁面載入 < 3 秒 (P90)
- 支援至少 100 位使用者同時在線
- 投票統計計算 < 5 秒
- FCP < 1.8 秒、LCP < 2.5 秒

**Constraints**:
- 所有註解和文件必須使用正體中文
- 所有使用者介面文字使用正體中文
- 不使用 TypeScript，完全採用 JavaScript ES6+
- 前端禁止使用 Vuetify，必須使用 Nuxt UI
- Git Commit 訊息必須使用正體中文
- 開發環境 Port 配置：前端 4001、後端 4002、MySQL 4306、phpMyAdmin 4003

**Scale/Scope**:
- 預期使用者規模：每個都市更新會 100-500 位所有權人
- 系統同時管理多個都市更新會（預估 10-50 個）
- 每場會議預估 10-50 個投票議題
- 總使用者數：管理員 + 工作人員 + 會員，預估 1000-5000 人
- 資料保留期限：5 年

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### ✅ 核心原則檢查 (Core Principles)

#### 1. 程式碼品質 (Code Quality)
- ✅ **語言規範**: 使用 JavaScript ES6+，不使用 TypeScript
- ✅ **編碼標準**: 遵循 ESLint 和 Prettier 配置規範
- ✅ **模組化**: 每個功能模組具有單一職責（MVC 架構、Composables 模式）
- ✅ **命名規範**:
  - 變數和函數使用英文 camelCase
  - 類別使用 PascalCase
  - 常數使用 UPPER_SNAKE_CASE
  - 檔案名稱使用 kebab-case
- ✅ **註解規範**: 所有註解使用正體中文

#### 2. 文件規範 (Documentation Standards)
- ✅ **語言要求**: 所有文件、註解、README 使用正體中文
- ✅ **API 文件**: API 端點和技術規格使用正體中文說明
- ✅ **Git Commit**: 使用正體中文，遵循 Conventional Commits 格式
- ✅ **必要文件**: README.md、API.md、CHANGELOG.md、.specify/ 目錄

#### 3. 使用者體驗一致性 (UX Consistency)
- ✅ **UI 框架**: Nuxt 3 + Nuxt UI（禁止使用 Vuetify）
- ✅ **圖示系統**: Heroicons
- ✅ **設計系統**: 綠色漸層主色 (#2FA633 到 #72BB29)
- ✅ **響應式設計**: 支援手機、平板、桌面裝置
- ✅ **使用者介面文字**: 所有 UI 文字和錯誤訊息使用正體中文

#### 4. 效能要求 (Performance Requirements)
- ✅ **前端效能**: FCP < 1.8s、LCP < 2.5s、FID < 100ms
- ✅ **後端效能**: API 回應 < 200ms (P95)
- ✅ **資料庫**: 使用索引，避免 N+1 查詢
- ✅ **資源優化**: Bundle < 300KB (gzipped)

#### 5. 安全性要求 (Security Requirements)
- ✅ **身份驗證**: 使用 JWT Token
- ✅ **資料保護**: 敏感資料加密、HTTPS、SQL 注入防護、XSS 防護、CSRF 防護
- ✅ **CORS 設定**: 明確定義允許的來源

### ✅ 技術決策檢查 (Technical Decisions)

#### 技術堆疊符合憲章要求
- ✅ **前端**: Nuxt 3 + Vue 3 + Nuxt UI + Tailwind CSS（符合憲章）
- ✅ **後端**: CodeIgniter 4 (PHP)（符合憲章）
- ✅ **資料庫**: MySQL（符合憲章）
- ✅ **容器化**: Docker + Docker Compose（符合憲章）

#### 架構決策符合憲章要求
- ✅ **前端架構**: Vue 3 Composition API、Pinia 狀態管理、檔案路由
- ✅ **後端架構**: MVC 模式、RESTful API、服務層分離
- ✅ **資料庫設計**: 第三正規化（3NF）、索引策略、外鍵約束

#### 開發流程符合憲章要求
- ✅ **版本控制**: Git Flow、功能分支策略
- ✅ **提交規範**: Conventional Commits 格式，使用正體中文
- ✅ **程式碼審查**: 所有程式碼合併前需經審查

### 📋 憲章檢查結果

**狀態**: ✅ 全部通過

本專案完全符合專案憲章的所有要求，無需特殊豁免或複雜度追蹤。

## Project Structure

### Documentation (this feature)

```
specs/001-view/
├── spec.md              # 功能規範（已完成）
├── plan.md              # 實作計劃（本文件）
├── research.md          # Phase 0 研究文件
├── data-model.md        # Phase 1 資料模型
├── quickstart.md        # Phase 1 快速開始指南
├── contracts/           # Phase 1 API 合約
│   ├── auth.openapi.yaml
│   ├── urban-renewals.openapi.yaml
│   ├── meetings.openapi.yaml
│   ├── voting.openapi.yaml
│   └── ...
└── tasks.md             # Phase 2 任務清單（/speckit.tasks 命令生成）
```

### Source Code (repository root)

```
# Web application (前後端分離)
backend/
├── app/
│   ├── Config/                    # 配置檔案
│   │   ├── Routes.php            # API 路由定義
│   │   ├── Cors.php              # CORS 設定
│   │   └── ...
│   ├── Controllers/               # 控制器
│   │   ├── BaseController.php
│   │   └── Api/                  # API 控制器
│   │       ├── AuthController.php
│   │       ├── UrbanRenewalController.php
│   │       ├── MeetingController.php
│   │       ├── VotingController.php
│   │       └── ...
│   ├── Models/                    # 資料模型
│   │   ├── UrbanRenewalModel.php
│   │   ├── PropertyOwnerModel.php
│   │   ├── MeetingModel.php
│   │   ├── VotingTopicModel.php
│   │   └── ...
│   ├── Database/                  # 資料庫
│   │   ├── Migrations/           # 資料庫遷移
│   │   └── Seeds/                # 資料種子
│   └── Services/                  # 業務邏輯服務層（選用）
├── tests/                         # 測試
│   ├── unit/
│   ├── integration/
│   └── contract/
├── composer.json                  # PHP 套件管理
└── .env.example                   # 環境變數範例

frontend/
├── pages/                         # 頁面（檔案路由）
│   ├── index.vue                 # 首頁
│   ├── login.vue                 # 登入頁面
│   ├── tables/                   # 功能頁面
│   │   ├── urban-renewal/
│   │   │   ├── index.vue
│   │   │   └── [id]/
│   │   ├── meeting/
│   │   │   ├── index.vue
│   │   │   └── [meetingId]/
│   │   └── ...
│   └── pages/
│       └── user.vue
├── components/                    # Vue 元件
│   ├── Footer.vue
│   ├── BackgroundImage.vue
│   └── ...
├── composables/                   # Composables（可重用邏輯）
│   ├── useAuth.js
│   ├── useApi.js
│   ├── useUrbanRenewal.js
│   ├── useMeetings.js
│   └── useVoting.js
├── layouts/                       # 版面配置
│   ├── auth.vue                  # 登入版面
│   └── main.vue                  # 主要版面
├── middleware/                    # 中介軟體
│   └── auth.js
├── assets/                        # 靜態資源
│   └── images/
├── tests/                         # 測試
│   ├── unit/
│   └── e2e/
├── nuxt.config.ts                # Nuxt 配置
└── package.json                  # npm 套件管理

# 共用配置
docker-compose.yml                 # Docker Compose 配置
.env.example                       # 環境變數範例
README.md                          # 專案說明（正體中文）
CLAUDE.md                          # Claude Code 指引
```

**Structure Decision**:

本專案採用 **Web Application (前後端分離)** 架構：

1. **Backend (CodeIgniter 4)**:
   - 提供 RESTful API 服務
   - 嚴格遵循 MVC 架構
   - 使用 CodeIgniter 4 的控制器、模型、資料庫遷移系統
   - API 路由統一使用 `/api` 前綴

2. **Frontend (Nuxt 3)**:
   - 使用 Nuxt 3 的檔案路由系統
   - 採用 Vue 3 Composition API (`<script setup>` 語法)
   - 使用 Composables 封裝可重用邏輯
   - 使用 Nuxt UI 元件庫和 Tailwind CSS

3. **Docker Compose**:
   - 統一管理開發環境
   - 服務包括：前端、後端、MySQL、phpMyAdmin、Redis（選用）

## Complexity Tracking

*本專案無需填寫此部分，因為所有檢查項目均通過，無憲章違規需要特別說明。*

## Phase 0: Research & Decisions

*詳見 [research.md](./research.md)*

## Phase 1: Design Artifacts

*詳見以下文件：*
- [data-model.md](./data-model.md) - 資料模型設計
- [contracts/](./contracts/) - API 合約定義
- [quickstart.md](./quickstart.md) - 快速開始指南

## Phase 2: Implementation Tasks

*待 `/speckit.tasks` 命令生成，詳見 [tasks.md](./tasks.md)*

## Notes

### 關鍵技術決策

1. **投票權重計算**: 基於所有權人的持分比例，需在 VotingController 中實作複雜的權重計算邏輯
2. **即時報到顯示**: 使用 Nuxt 的 Server-Sent Events (SSE) 或 WebSocket 實作即時更新
3. **並發投票處理**: 使用資料庫交易和樂觀鎖定確保資料一致性
4. **權限管理**: 使用 JWT Token + 角色權限檢查中介軟體
5. **批次資料處理**: 所有權人匯入/匯出使用 PHPSpreadsheet 處理 Excel 檔案

### 開發環境配置

- **前端開發伺服器**: http://localhost:4001
- **後端 API 服務**: http://localhost:4002
- **MySQL 資料庫**: localhost:4306
- **phpMyAdmin**: http://localhost:4003
- **Redis**（選用）: localhost:6379

### 部署考量

- 建議使用 Nginx 作為反向代理
- 啟用 HTTPS（Let's Encrypt）
- 設定定期資料庫備份
- 使用 Redis 進行 Session 和快取管理
- 設定 Log Rotation

---

**Next Steps**: 執行 Phase 0 研究並生成 research.md
