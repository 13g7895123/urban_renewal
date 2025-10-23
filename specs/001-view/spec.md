# Feature Specification: 都更計票系統 - 完整功能規範

**Feature Branch**: `001-view`
**Created**: 2025-10-08
**Status**: Draft
**Input**: User description: "幫我view過整份專案,整理出目前這個專案所有的功能與規範"

## 專案概述

都更計票系統是一個專為都市更新會議和投票管理設計的網頁應用系統，協助都市更新組織進行會員管理、會議規劃、出席簽到、投票表決和統計報表等作業。系統採用前後端分離架構，提供完整的權限管理和資料追蹤功能。

### 系統架構

- **前端**: Nuxt 3 + Vue 3 + Nuxt UI + Tailwind CSS
- **後端**: CodeIgniter 4 (PHP)
- **資料庫**: MySQL
- **開發環境**: Docker + Docker Compose

## User Scenarios & Testing *(mandatory)*

### User Story 1 - 使用者身份驗證與授權 (Priority: P1)

系統管理員或一般使用者需要安全地登入系統以存取各項功能。登入後系統應根據使用者角色顯示對應的功能選單和權限。

**Why this priority**: 身份驗證是系統的基礎功能，沒有驗證機制無法確保資料安全和使用者權限管理。

**Independent Test**: 可透過建立測試帳號，嘗試登入、登出、權限驗證等功能獨立測試。

**Acceptance Scenarios**:

1. **Given** 使用者在登入頁面輸入正確的帳號和密碼, **When** 點擊登入按鈕, **Then** 系統驗證成功並導向首頁，顯示歡迎訊息
2. **Given** 使用者在登入頁面輸入錯誤的帳號或密碼, **When** 點擊登入按鈕, **Then** 系統顯示錯誤訊息「帳號或密碼錯誤」，不允許登入
3. **Given** 使用者已登入系統, **When** 使用者點擊登出按鈕, **Then** 系統清除登入狀態並導向登入頁面
4. **Given** 使用者的 JWT Token 已過期, **When** 使用者嘗試存取任何 API, **Then** 系統要求使用者重新登入
5. **Given** 使用者連續輸入錯誤密碼達到限制次數, **When** 再次嘗試登入, **Then** 系統鎖定該帳號並顯示警告訊息

---

### User Story 2 - 都市更新會管理 (Priority: P1)

都市更新會管理員需要建立、檢視、編輯和刪除都市更新會的基本資料，包括名稱、土地面積、所有權人數、理事長資訊等。

**Why this priority**: 都市更新會是整個系統的核心實體，所有會議和投票都必須關聯到特定的更新會，因此是高優先級功能。

**Independent Test**: 可獨立測試建立一個新的都市更新會，填入完整資料並儲存，然後檢視、編輯和刪除該更新會。

**Acceptance Scenarios**:

1. **Given** 管理員在更新會管理頁面, **When** 點擊「新建更新會」按鈕並填入完整資料後提交, **Then** 系統建立新的更新會並顯示在列表中
2. **Given** 管理員在更新會列表頁面, **When** 點擊任一更新會的「基本資料」按鈕, **Then** 系統顯示該更新會的詳細資料頁面
3. **Given** 管理員在更新會詳細資料頁面, **When** 修改資料並點擊儲存, **Then** 系統更新資料並顯示成功訊息
4. **Given** 管理員在更新會列表頁面, **When** 選擇一個或多個更新會並點擊刪除, **Then** 系統提示確認後刪除選定的更新會
5. **Given** 系統中有多筆更新會資料, **When** 管理員使用搜尋或篩選功能, **Then** 系統顯示符合條件的更新會列表

---

### User Story 3 - 地籍資料管理 (Priority: P2)

管理員需要管理都市更新會下的地籍資料，包括土地和建物資訊，並能依據縣市、鄉鎮市區、地段等階層式結構進行管理。

**Why this priority**: 地籍資料是都市更新會的重要組成部分，但相較於更新會本身和會議管理，屬於次要優先級。

**Independent Test**: 可獨立測試在特定更新會下新增土地和建物資料，並檢視、編輯、刪除這些資料。

**Acceptance Scenarios**:

1. **Given** 管理員在更新會詳細頁面的地籍管理區域, **When** 點擊「新增土地」並填入縣市、鄉鎮市區、地段、地號等資料後提交, **Then** 系統建立新的土地記錄
2. **Given** 管理員在地籍管理頁面, **When** 選擇特定土地並點擊「新增建物」, **Then** 系統顯示建物新增表單並關聯到該土地
3. **Given** 管理員檢視地籍列表, **When** 使用縣市、鄉鎮市區的階層式下拉選單篩選, **Then** 系統顯示符合條件的地籍資料
4. **Given** 管理員在地籍資料頁面, **When** 點擊編輯並修改建物資料, **Then** 系統更新建物資料並顯示成功訊息
5. **Given** 管理員選擇土地或建物, **When** 點擊刪除並確認, **Then** 系統刪除該筆地籍資料

---

### User Story 4 - 所有權人管理 (Priority: P2)

管理員需要管理都市更新會下的所有權人資料，包括姓名、身分證號、聯絡電話、地址等，並支援批次匯入和匯出功能。

**Why this priority**: 所有權人資料是會議出席和投票的基礎，但可以在會議建立後再逐步補充，因此屬於次要優先級。

**Independent Test**: 可獨立測試新增所有權人、編輯資料、匯入 Excel 檔案和匯出所有權人清單。

**Acceptance Scenarios**:

1. **Given** 管理員在都市更新會的所有權人管理頁面, **When** 點擊「新增所有權人」並填入姓名、身分證號、電話等資料後提交, **Then** 系統建立新的所有權人記錄
2. **Given** 管理員在所有權人列表頁面, **When** 點擊「匯入」並上傳符合格式的 Excel 檔案, **Then** 系統批次建立所有權人資料並顯示匯入結果
3. **Given** 管理員在所有權人列表頁面, **When** 點擊「匯出」按鈕, **Then** 系統產生 Excel 檔案並下載到本機
4. **Given** 管理員檢視所有權人列表, **When** 點擊編輯特定所有權人, **Then** 系統顯示編輯表單並允許修改資料
5. **Given** 管理員選擇一個或多個所有權人, **When** 點擊刪除並確認, **Then** 系統刪除選定的所有權人記錄
6. **Given** 管理員新增所有權人時未填寫「所有權人編號」, **When** 提交表單, **Then** 系統自動產生唯一的所有權人編號

---

### User Story 5 - 所有權關係管理 (Priority: P3)

管理員需要建立所有權人與地籍（土地/建物）之間的持分關係，記錄每個所有權人在特定土地或建物的持分比例。

**Why this priority**: 所有權關係影響投票權重計算，但可以在投票議題建立前設定，屬於較低優先級。

**Independent Test**: 可獨立測試為特定所有權人建立與土地或建物的持分關係，並驗證持分比例計算。

**Acceptance Scenarios**:

1. **Given** 管理員在所有權人詳細頁面, **When** 點擊「新增持分」並選擇土地或建物以及輸入持分比例後提交, **Then** 系統建立所有權關係記錄
2. **Given** 管理員檢視特定土地或建物, **When** 檢視「所有權人清單」, **Then** 系統顯示所有持有該地籍的所有權人及其持分比例
3. **Given** 管理員在所有權關係頁面, **When** 修改持分比例並儲存, **Then** 系統更新持分資料
4. **Given** 管理員檢視所有權關係列表, **When** 刪除特定所有權關係並確認, **Then** 系統移除該關係記錄
5. **Given** 系統計算投票權重, **When** 進行投票統計, **Then** 系統根據所有權人的持分比例計算其投票權重

---

### User Story 6 - 會議管理 (Priority: P1)

管理員需要建立、檢視、編輯和刪除會議資料，包括會議名稱、類型、日期時間、地點、法定人數設定等，並能追蹤會議狀態。

**Why this priority**: 會議是投票和出席管理的核心，沒有會議就無法進行後續的投票作業，因此是高優先級功能。

**Independent Test**: 可獨立測試建立一個新會議，填入完整資料，並檢視、編輯和刪除該會議。

**Acceptance Scenarios**:

1. **Given** 管理員在會議管理頁面, **When** 點擊「新增會議」並填入會議名稱、類型、日期時間、地點等資料後提交, **Then** 系統建立新的會議記錄
2. **Given** 管理員在會議列表頁面, **When** 點擊任一會議的「基本資料」按鈕, **Then** 系統顯示該會議的詳細資料頁面
3. **Given** 管理員在會議詳細資料頁面, **When** 修改會議資料並點擊儲存, **Then** 系統更新會議資料並顯示成功訊息
4. **Given** 管理員在會議列表頁面, **When** 選擇一個或多個會議並點擊刪除, **Then** 系統提示確認後刪除選定的會議
5. **Given** 管理員建立會議時設定法定人數（土地面積、建物面積、所有權人數）, **When** 儲存會議, **Then** 系統記錄法定人數設定供後續投票統計使用
6. **Given** 會議正在進行中, **When** 管理員更新會議狀態為「進行中」, **Then** 系統允許會員報到和投票

---

### User Story 7 - 會員報到管理 (Priority: P2)

工作人員需要在會議當天記錄會員的出席狀況，包括報到類型（親自出席、委託出席、列席）和簽到時間。

**Why this priority**: 報到管理影響法定人數計算和投票權限，但可以在會議開始前或開始後進行，屬於次要優先級。

**Independent Test**: 可獨立測試為特定會議建立會員報到記錄，並檢視報到統計。

**Acceptance Scenarios**:

1. **Given** 工作人員在會員報到頁面, **When** 選擇所有權人並標記為「親自出席」後提交, **Then** 系統記錄該會員的報到資料和時間
2. **Given** 工作人員在會員報到頁面, **When** 選擇所有權人並標記為「委託出席」並輸入受託人資料, **Then** 系統記錄委託出席資料
3. **Given** 工作人員在會員報到頁面, **When** 選擇所有權人並標記為「列席」, **Then** 系統記錄列席資料但不納入法定人數計算
4. **Given** 管理員檢視會議報到統計, **When** 查看報到摘要, **Then** 系統顯示出席人數、納入計算總人數、列席總人數
5. **Given** 工作人員使用批次報到功能, **When** 選擇多個所有權人並批次標記出席狀態, **Then** 系統批次建立報到記錄
6. **Given** 會議有大螢幕顯示需求, **When** 開啟「報到顯示」頁面, **Then** 系統即時顯示最新的報到人員和統計資訊

---

### User Story 8 - 投票議題管理 (Priority: P1)

管理員需要建立、檢視、編輯和刪除投票議題，包括議題編號、標題、說明、投票方式（簡單多數、絕對多數、三分之二多數、全體一致）等。

**Why this priority**: 投票議題是會議的核心內容，沒有議題就無法進行投票，因此是高優先級功能。

**Independent Test**: 可獨立測試在特定會議下建立投票議題，填入完整資料，並檢視、編輯和刪除該議題。

**Acceptance Scenarios**:

1. **Given** 管理員在會議的投票議題頁面, **When** 點擊「新增議題」並填入議題編號、標題、說明、投票方式後提交, **Then** 系統建立新的投票議題
2. **Given** 管理員在投票議題列表頁面, **When** 點擊任一議題的「基本資料」按鈕, **Then** 系統顯示該議題的詳細資料頁面
3. **Given** 管理員在投票議題詳細頁面, **When** 修改議題資料並點擊儲存, **Then** 系統更新議題資料並顯示成功訊息
4. **Given** 管理員在投票議題列表頁面, **When** 選擇一個或多個議題並點擊刪除, **Then** 系統提示確認後刪除選定的議題
5. **Given** 管理員建立投票議題, **When** 選擇投票方式為「三分之二多數」, **Then** 系統記錄該投票方式並在統計時套用三分之二門檻

---

### User Story 9 - 投票表決管理 (Priority: P1)

會員需要在投票頁面對議題進行表決，選擇同意、不同意或棄權。系統應記錄投票結果並即時統計。

**Why this priority**: 投票表決是系統的核心功能，是整個系統存在的主要目的，因此是高優先級功能。

**Independent Test**: 可獨立測試以不同會員身份對特定議題進行投票，並驗證投票結果統計。

**Acceptance Scenarios**:

1. **Given** 會員在投票頁面, **When** 選擇「同意」、「不同意」或「棄權」並提交, **Then** 系統記錄該會員的投票結果
2. **Given** 會員已經投票, **When** 再次進入投票頁面, **Then** 系統顯示該會員的投票記錄並允許修改（若投票尚未關閉）
3. **Given** 管理員開啟投票議題, **When** 將議題狀態設定為「進行中」, **Then** 會員可以開始投票
4. **Given** 管理員關閉投票議題, **When** 將議題狀態設定為「已關閉」, **Then** 會員無法再投票或修改投票
5. **Given** 會員使用批次投票功能, **When** 選擇多個議題並統一投票, **Then** 系統批次記錄所有議題的投票結果
6. **Given** 管理員檢視投票統計, **When** 查看投票結果頁面, **Then** 系統顯示同意票數/面積、不同意票數/面積、棄權票數/面積和通過狀態

---

### User Story 10 - 投票結果統計與報表 (Priority: P2)

管理員需要檢視投票結果的詳細統計資料，包括按人數、土地面積、建物面積的統計，以及投票通過狀態和匯出報表功能。

**Why this priority**: 投票結果統計是投票後的重要功能，但相較於投票本身，屬於次要優先級。

**Independent Test**: 可獨立測試檢視特定議題的投票統計，並匯出報表。

**Acceptance Scenarios**:

1. **Given** 管理員在投票結果頁面, **When** 查看特定議題的統計資料, **Then** 系統顯示同意/不同意/棄權的票數、土地面積和建物面積統計
2. **Given** 投票議題已關閉, **When** 系統計算投票結果, **Then** 系統根據設定的投票方式判斷議題是否通過
3. **Given** 管理員在投票結果頁面, **When** 點擊「匯出報表」按鈕, **Then** 系統產生 Excel 或 PDF 報表並下載到本機
4. **Given** 管理員檢視投票詳細資料, **When** 查看個別投票記錄, **Then** 系統顯示每個會員的投票選擇和投票時間
5. **Given** 投票議題設定為「絕對多數」, **When** 同意票數/面積超過應出席人數/面積的一半, **Then** 系統判定議題通過

---

### User Story 11 - 系統設定管理 (Priority: P3)

系統管理員需要管理系統全域設定，包括系統名稱、標誌、聯絡資訊、預設值、權限設定等。

**Why this priority**: 系統設定是系統運作的基礎，但大部分設定可以使用預設值，因此屬於較低優先級。

**Independent Test**: 可獨立測試修改系統設定並驗證設定生效。

**Acceptance Scenarios**:

1. **Given** 系統管理員在系統設定頁面, **When** 修改系統名稱並儲存, **Then** 系統更新名稱並在所有頁面顯示新名稱
2. **Given** 系統管理員在系統設定頁面, **When** 上傳新的系統標誌, **Then** 系統更新標誌並在登入頁面和導覽列顯示
3. **Given** 系統管理員在系統設定頁面, **When** 修改預設法定人數設定, **Then** 新建立的會議自動套用新的預設值
4. **Given** 系統管理員在系統設定頁面, **When** 查看系統資訊, **Then** 系統顯示版本號、資料庫狀態、儲存空間使用狀況等資訊
5. **Given** 系統管理員在系統設定頁面, **When** 清除快取, **Then** 系統清除所有快取並重新載入設定

---

### User Story 12 - 通知系統 (Priority: P3)

系統需要在特定事件發生時發送通知給相關使用者，例如會議提醒、投票開始、投票結果公告等。

**Why this priority**: 通知系統提升使用者體驗，但不影響核心功能運作，因此屬於較低優先級。

**Independent Test**: 可獨立測試建立通知並驗證使用者收到通知。

**Acceptance Scenarios**:

1. **Given** 系統建立新會議, **When** 會議日期設定完成, **Then** 系統自動發送會議通知給所有相關會員
2. **Given** 投票議題開始, **When** 議題狀態變更為「進行中」, **Then** 系統發送投票開始通知給所有會員
3. **Given** 投票議題關閉, **When** 議題狀態變更為「已關閉」, **Then** 系統發送投票結果通知給所有會員
4. **Given** 使用者登入系統, **When** 檢視通知列表, **Then** 系統顯示所有未讀和已讀通知
5. **Given** 使用者在通知列表, **When** 點擊「標記為已讀」, **Then** 系統更新通知狀態並減少未讀通知計數
6. **Given** 系統管理員在通知設定頁面, **When** 選擇通知類型並關閉, **Then** 系統不再發送該類型的通知

---

### User Story 13 - 文件管理 (Priority: P3)

管理員需要上傳、檢視、下載和刪除與會議相關的文件，例如會議議程、簽到表、投票結果報告等。

**Why this priority**: 文件管理是輔助功能，不影響核心的投票作業，因此屬於較低優先級。

**Independent Test**: 可獨立測試上傳文件、下載文件和刪除文件。

**Acceptance Scenarios**:

1. **Given** 管理員在會議文件管理頁面, **When** 點擊「上傳文件」並選擇檔案後提交, **Then** 系統上傳文件並顯示在文件列表中
2. **Given** 管理員在文件列表頁面, **When** 點擊任一文件的「下載」按鈕, **Then** 系統下載該文件到本機
3. **Given** 管理員在文件列表頁面, **When** 點擊任一文件的「刪除」按鈕並確認, **Then** 系統刪除該文件及其實體檔案
4. **Given** 管理員在文件管理頁面, **When** 檢視文件統計資料, **Then** 系統顯示總文件數、檔案類型分佈、儲存空間使用狀況
5. **Given** 管理員在文件管理頁面, **When** 點擊「批次上傳」並選擇多個檔案, **Then** 系統批次上傳所有檔案

---

### User Story 14 - 使用者管理 (Priority: P2)

系統管理員需要管理系統使用者帳號，包括建立、檢視、編輯、刪除使用者，以及設定使用者角色和權限。

**Why this priority**: 使用者管理是多使用者系統的必要功能，但相較於核心業務功能，屬於次要優先級。

**Independent Test**: 可獨立測試建立新使用者、修改使用者資料、設定角色和權限，以及刪除使用者。

**Acceptance Scenarios**:

1. **Given** 系統管理員在使用者管理頁面, **When** 點擊「新增使用者」並填入帳號、密碼、姓名、角色等資料後提交, **Then** 系統建立新的使用者帳號
2. **Given** 系統管理員在使用者列表頁面, **When** 點擊任一使用者的「編輯」按鈕, **Then** 系統顯示使用者編輯頁面
3. **Given** 系統管理員在使用者編輯頁面, **When** 修改使用者角色並儲存, **Then** 系統更新使用者角色和對應的權限
4. **Given** 系統管理員在使用者列表頁面, **When** 停用特定使用者帳號, **Then** 該使用者無法登入系統
5. **Given** 系統管理員在使用者管理頁面, **When** 檢視角色統計資料, **Then** 系統顯示各角色的使用者數量分佈
6. **Given** 使用者連續登入失敗達到限制次數, **When** 系統管理員重置登入嘗試次數, **Then** 該使用者可以再次嘗試登入

---

### Edge Cases

- **會議時間衝突**: 當建立新會議時，如果同一更新會在相同時間已有其他會議，系統應發出警告訊息
- **投票權重計算錯誤**: 當所有權人的持分資料不完整或總和不等於 100% 時，系統如何處理投票權重計算？
- **會員重複報到**: 當工作人員意外為同一會員重複建立報到記錄時，系統應如何處理？應顯示警告還是自動更新？
- **投票議題順序**: 當會議有多個投票議題時，如何確保議題按照編號順序顯示和投票？
- **大量並發投票**: 當大量會員同時投票時，系統如何確保資料一致性和效能？
- **報表匯出失敗**: 當報表資料量過大或系統資源不足時，匯出功能應如何處理？應提供分批匯出或簡化報表的選項嗎？
- **文件上傳限制**: 當上傳的文件超過大小限制或為不支援的檔案類型時，系統應如何提示使用者？
- **刪除關聯資料**: 當刪除都市更新會時，如果該更新會下有會議、投票等關聯資料，系統應如何處理？應禁止刪除還是提供級聯刪除選項？
- **跨瀏覽器相容性**: 系統在不同瀏覽器（Chrome、Firefox、Safari、Edge）上是否能正常運作？
- **網路中斷**: 當使用者在投票過程中網路中斷，投票資料是否會遺失？系統是否有離線暫存機制？

## Requirements *(mandatory)*

### Functional Requirements

#### 身份驗證與授權
- **FR-001**: 系統必須提供使用者登入功能，使用帳號和密碼進行身份驗證
- **FR-002**: 系統必須使用 JWT Token 管理使用者登入狀態
- **FR-003**: 系統必須在 Token 過期時要求使用者重新登入
- **FR-004**: 系統必須記錄使用者的登入失敗次數，達到限制後鎖定帳號
- **FR-005**: 系統必須提供登出功能，清除使用者的登入狀態
- **FR-006**: 系統必須根據使用者角色控制功能存取權限

#### 都市更新會管理
- **FR-007**: 系統必須允許管理員建立都市更新會，包含名稱、土地面積、所有權人數、理事長資訊
- **FR-008**: 系統必須提供都市更新會列表檢視功能，顯示所有更新會的基本資訊
- **FR-009**: 系統必須允許管理員編輯都市更新會的資料
- **FR-010**: 系統必須允許管理員刪除都市更新會（軟刪除）
- **FR-011**: 系統必須提供都市更新會的搜尋和篩選功能

#### 地籍資料管理
- **FR-012**: 系統必須允許管理員為都市更新會新增土地資料，包含縣市、鄉鎮市區、地段、地號
- **FR-013**: 系統必須提供階層式的縣市/鄉鎮市區/地段選擇功能（級聯下拉選單）
- **FR-014**: 系統必須允許管理員為土地新增建物資料
- **FR-015**: 系統必須允許管理員編輯和刪除土地和建物資料
- **FR-016**: 系統必須記錄土地面積和建物面積資訊

#### 所有權人管理
- **FR-017**: 系統必須允許管理員為都市更新會新增所有權人，包含姓名、身分證號、聯絡電話、地址
- **FR-018**: 系統必須自動為所有權人產生唯一的所有權人編號
- **FR-019**: 系統必須提供所有權人資料的批次匯入功能（Excel 格式）
- **FR-020**: 系統必須提供所有權人資料的匯出功能（Excel 格式）
- **FR-021**: 系統必須允許管理員編輯和刪除所有權人資料
- **FR-022**: 系統必須支援所有權人的排除類型標記（法院囑託查封、假扣押、假處分、破產登記、未經繼承）

#### 所有權關係管理
- **FR-023**: 系統必須允許管理員建立所有權人與土地/建物之間的持分關係
- **FR-024**: 系統必須記錄每個所有權人在特定地籍的持分比例
- **FR-025**: 系統必須允許管理員編輯和刪除所有權關係
- **FR-026**: 系統必須提供檢視特定地籍的所有權人清單功能

#### 會議管理
- **FR-027**: 系統必須允許管理員建立會議，包含會議名稱、類型、日期時間、地點
- **FR-028**: 系統必須支援會議類型：會員大會、理事會、監事會、臨時會議
- **FR-029**: 系統必須允許管理員設定會議的法定人數（土地面積、建物面積、所有權人數的分子和分母）
- **FR-030**: 系統必須追蹤會議狀態：草稿、已排程、進行中、已完成、已取消
- **FR-031**: 系統必須提供會議列表檢視功能
- **FR-032**: 系統必須允許管理員編輯和刪除會議
- **FR-033**: 系統必須提供會議搜尋和篩選功能

#### 會員報到管理
- **FR-034**: 系統必須允許工作人員為會議建立會員報到記錄
- **FR-035**: 系統必須支援報到類型：親自出席、委託出席、列席
- **FR-036**: 系統必須記錄報到時間
- **FR-037**: 系統必須為委託出席記錄受託人資訊
- **FR-038**: 系統必須提供批次報到功能
- **FR-039**: 系統必須計算會議的出席人數、納入計算總人數、列席總人數
- **FR-040**: 系統必須提供即時報到顯示頁面（大螢幕顯示）
- **FR-041**: 系統必須提供報到統計摘要
- **FR-042**: 系統必須允許修改報到狀態

#### 投票議題管理
- **FR-043**: 系統必須允許管理員為會議建立投票議題，包含議題編號、標題、說明
- **FR-044**: 系統必須支援投票方式：簡單多數、絕對多數、三分之二多數、全體一致
- **FR-045**: 系統必須追蹤投票議題狀態：草稿、進行中、已關閉
- **FR-046**: 系統必須允許管理員編輯和刪除投票議題
- **FR-047**: 系統必須允許管理員開啟和關閉投票
- **FR-048**: 系統必須提供投票議題列表檢視功能

#### 投票表決管理
- **FR-049**: 系統必須允許會員對投票議題進行表決，選擇同意、不同意或棄權
- **FR-050**: 系統必須記錄每筆投票的時間和結果
- **FR-051**: 系統必須允許會員在投票關閉前修改投票
- **FR-052**: 系統必須防止未報到的會員投票
- **FR-053**: 系統必須防止會員在投票關閉後投票或修改投票
- **FR-054**: 系統必須提供批次投票功能（一次對多個議題投票）
- **FR-055**: 系統必須根據所有權人的持分比例計算投票權重

#### 投票結果統計
- **FR-056**: 系統必須統計每個投票議題的同意/不同意/棄權票數
- **FR-057**: 系統必須統計每個投票議題的同意/不同意/棄權土地面積
- **FR-058**: 系統必須統計每個投票議題的同意/不同意/棄權建物面積
- **FR-059**: 系統必須根據投票方式和統計結果判斷議題是否通過
- **FR-060**: 系統必須提供投票結果的詳細檢視功能（個別投票記錄）
- **FR-061**: 系統必須提供投票結果匯出功能（Excel 或 PDF 格式）

#### 系統設定管理
- **FR-062**: 系統必須允許管理員設定系統名稱和標誌
- **FR-063**: 系統必須允許管理員設定系統預設值（法定人數、投票方式等）
- **FR-064**: 系統必須提供系統資訊檢視功能（版本號、資料庫狀態、儲存空間）
- **FR-065**: 系統必須提供快取清除功能
- **FR-066**: 系統必須允許管理員設定系統權限和角色

#### 通知系統
- **FR-067**: 系統必須在建立會議時發送通知給相關會員
- **FR-068**: 系統必須在投票開始時發送通知給所有會員
- **FR-069**: 系統必須在投票關閉時發送投票結果通知給所有會員
- **FR-070**: 系統必須提供通知列表檢視功能
- **FR-071**: 系統必須允許使用者標記通知為已讀
- **FR-072**: 系統必須顯示未讀通知計數
- **FR-073**: 系統必須允許管理員設定通知類型的開關

#### 文件管理
- **FR-074**: 系統必須允許管理員上傳與會議相關的文件
- **FR-075**: 系統必須提供文件下載功能
- **FR-076**: 系統必須允許管理員刪除文件
- **FR-077**: 系統必須提供文件列表檢視功能
- **FR-078**: 系統必須提供文件統計資料（總文件數、檔案類型、儲存空間）
- **FR-079**: 系統必須提供批次上傳功能
- **FR-080**: 系統必須限制文件上傳的大小和類型

#### 使用者管理
- **FR-081**: 系統必須允許管理員建立使用者帳號，包含帳號、密碼、姓名、角色
- **FR-082**: 系統必須提供使用者列表檢視功能
- **FR-083**: 系統必須允許管理員編輯使用者資料
- **FR-084**: 系統必須允許管理員停用或啟用使用者帳號
- **FR-085**: 系統必須允許管理員重置使用者的登入嘗試次數
- **FR-086**: 系統必須提供使用者角色統計資料
- **FR-087**: 系統必須允許使用者修改自己的密碼

### Key Entities

- **都市更新會 (Urban Renewals)**: 代表一個都市更新組織，包含名稱、土地面積、所有權人數、理事長資訊等基本資料
- **地籍資料 (Land Plots & Buildings)**: 代表土地和建物資料，包含縣市、鄉鎮市區、地段、地號、面積等資訊，關聯到都市更新會
- **所有權人 (Property Owners)**: 代表都市更新會的所有權人，包含姓名、身分證號、聯絡電話、地址等個人資料
- **所有權關係 (Ownership Relations)**: 代表所有權人與土地/建物之間的持分關係，記錄持分比例
- **會議 (Meetings)**: 代表一場會議，包含會議名稱、類型、日期時間、地點、法定人數設定等資料，關聯到都市更新會
- **會員報到 (Meeting Attendance)**: 代表會員在特定會議的報到記錄，包含報到類型、報到時間、受託人資訊等
- **投票議題 (Voting Topics)**: 代表會議中的投票議題，包含議題編號、標題、說明、投票方式、投票狀態等
- **投票記錄 (Voting Records)**: 代表會員對特定議題的投票記錄，包含投票選擇（同意/不同意/棄權）、投票時間、投票權重等
- **使用者 (Users)**: 代表系統使用者，包含帳號、密碼、姓名、角色、狀態等資料
- **系統設定 (System Settings)**: 代表系統的全域設定，包含設定鍵、設定值、類別、是否公開等資料
- **通知 (Notifications)**: 代表系統通知，包含通知類型、標題、內容、接收者、已讀狀態等資料
- **文件 (Documents)**: 代表上傳的文件，包含檔案名稱、檔案類型、檔案大小、上傳時間、關聯會議等資料

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 使用者能在 3 秒內完成登入並進入系統首頁
- **SC-002**: 管理員能在 2 分鐘內建立一個完整的都市更新會資料（包含基本資料）
- **SC-003**: 管理員能在 5 分鐘內建立一個完整的會議（包含基本資料和法定人數設定）
- **SC-004**: 工作人員能在 10 秒內為一位會員完成報到
- **SC-005**: 會員能在 30 秒內完成對所有投票議題的表決
- **SC-006**: 系統能在 5 秒內計算並顯示投票結果統計
- **SC-007**: 系統支援至少 100 位會員同時投票而不影響效能
- **SC-008**: 所有權人資料匯入功能能在 1 分鐘內完成 1000 筆資料的匯入
- **SC-009**: 報表匯出功能能在 30 秒內完成包含 1000 筆投票記錄的報表
- **SC-010**: 系統在主流瀏覽器（Chrome、Firefox、Safari、Edge）上均能正常運作
- **SC-011**: 90% 的使用者能在首次使用時不需協助即可完成登入和基本操作
- **SC-012**: 系統正常運作時間達到 99% 以上（每月停機時間不超過 7.2 小時）
- **SC-013**: 資料備份每日自動執行且備份檔案能在 15 分鐘內完成還原
- **SC-014**: 系統前端頁面載入時間在 3 秒內（90% 的情況）
- **SC-015**: API 回應時間在 200 毫秒內（95% 的情況）

## Assumptions

- 系統假設所有使用者都有基本的電腦操作能力和瀏覽器使用經驗
- 系統假設使用者在穩定的網路環境下操作
- 系統假設都市更新會的法定人數設定在建立會議時已確定
- 系統假設所有權人的持分資料在投票前已完整建立
- 系統假設會議的出席報到在投票開始前完成
- 系統假設文件上傳的大小限制為 50MB，支援的檔案類型為 PDF、DOC、DOCX、XLS、XLSX、JPG、PNG
- 系統假設使用 MySQL 資料庫，版本 5.7 或以上
- 系統假設後端 PHP 版本為 7.4 或以上
- 系統假設前端支援 Chrome、Firefox、Safari、Edge 的最新兩個主要版本
- 系統假設部署環境為 Linux 作業系統，使用 Docker 容器化部署
- 系統假設資料保留期限為 5 年，超過期限的資料會被封存或刪除
- 系統假設同一時間內單一更新會只會進行一場會議
- 系統假設投票權重計算基於所有權人的持分比例，如果持分資料不完整則該所有權人無投票權
- 系統假設所有日期和時間使用伺服器所在時區（建議為台灣時區 UTC+8）

## Dependencies

- 系統需要 MySQL 資料庫服務
- 系統需要 PHP 執行環境（建議使用 PHP-FPM）
- 系統需要 Web 伺服器（建議使用 Nginx 或 Apache）
- 系統需要 Node.js 環境用於前端建置
- 系統需要 Docker 和 Docker Compose 用於開發和部署
- 系統依賴 CodeIgniter 4 框架及其相關套件
- 系統依賴 Nuxt 3 框架及其相關套件
- 系統需要 Composer 用於後端套件管理
- 系統需要 npm 用於前端套件管理
- 系統可能需要 Redis 服務用於快取和 Session 管理（選用）
- 系統可能需要 SMTP 郵件伺服器用於發送通知郵件（選用）
- 系統需要 SSL 憑證用於 HTTPS 連線（建議）

## Scope

### In Scope

- 使用者身份驗證與授權管理
- 都市更新會完整資料管理（CRUD）
- 地籍資料管理（土地和建物）
- 所有權人資料管理，包含批次匯入匯出
- 所有權關係管理（持分比例）
- 會議完整資料管理（CRUD）
- 會員報到管理，包含親自出席、委託出席、列席
- 投票議題管理，支援多種投票方式
- 投票表決功能，包含批次投票
- 投票結果統計和報表匯出
- 系統設定管理
- 通知系統（站內通知）
- 文件管理（上傳、下載、刪除）
- 使用者管理和角色權限設定
- 報到即時顯示頁面（大螢幕）
- 階層式地區選擇功能（縣市/鄉鎮市區/地段）

### Out of Scope

- 行動裝置原生 App（僅提供響應式網頁）
- 電子郵件通知（第一版僅提供站內通知）
- 簡訊通知
- 多語系支援（僅支援正體中文）
- 線上金流整合
- 區塊鏈投票驗證
- 視訊會議整合
- 即時聊天功能
- 第三方登入整合（Google、Facebook 等）
- 進階報表客製化功能（如圖表拖拉、自訂欄位等）
- 自動化測試框架整合（建議未來版本加入）
- API 版本控制（第一版僅提供單一 API 版本）
- GraphQL API（僅提供 RESTful API）
- 微服務架構（採用單體式架構）
- 離線模式支援

## Non-Functional Requirements

### 效能要求
- 系統必須支援至少 100 位使用者同時在線
- API 回應時間必須在 200 毫秒內（95% 的情況）
- 前端頁面載入時間必須在 3 秒內（90% 的情況）
- 資料庫查詢必須使用適當的索引以提升效能

### 安全性要求
- 系統必須使用 HTTPS 加密通訊（建議）
- 系統必須使用 JWT Token 進行身份驗證
- 系統必須加密儲存使用者密碼（建議使用 bcrypt）
- 系統必須防範 SQL 注入攻擊（使用參數化查詢）
- 系統必須防範 XSS 攻擊（輸入驗證和輸出轉義）
- 系統必須防範 CSRF 攻擊
- 系統必須記錄所有關鍵操作的稽核日誌

### 可用性要求
- 系統正常運作時間必須達到 99% 以上
- 系統必須提供錯誤處理機制，顯示友善的錯誤訊息
- 系統必須支援資料備份和還原
- 系統必須提供系統健康檢查功能

### 可維護性要求
- 程式碼必須遵循 PSR-12 編碼規範（後端）
- 程式碼必須遵循 Vue 3 和 Nuxt 3 的最佳實踐（前端）
- 所有註解必須使用正體中文
- 所有 API 端點必須提供清楚的文件說明
- 資料庫表格和欄位必須有清楚的註解

### 相容性要求
- 前端必須支援 Chrome、Firefox、Safari、Edge 的最新兩個主要版本
- 前端必須支援響應式設計，適應手機、平板、桌面裝置
- 系統必須支援 MySQL 5.7 或以上版本
- 系統必須支援 PHP 7.4 或以上版本

## Technical Notes

### API 設計原則
- 所有 API 端點使用 RESTful 設計原則
- API 路由統一使用 `/api` 前綴
- 使用標準 HTTP 狀態碼（200、201、400、401、403、404、500 等）
- API 回應統一使用 JSON 格式
- 使用 CORS 設定允許前端跨域請求

### 資料庫設計原則
- 所有表格使用 `created_at`、`updated_at`、`deleted_at` 欄位（軟刪除）
- 所有表格使用自動遞增的整數主鍵 `id`
- 外鍵欄位命名格式為 `{table_name}_id`
- 為常用查詢欄位建立索引以提升效能
- 遵循第三正規化（3NF）原則

### 前端開發原則
- 使用 Nuxt UI 元件庫取代 Vuetify
- 使用 Heroicons 作為圖示系統
- 使用 Tailwind CSS 進行樣式設計
- 使用 Vue 3 Composition API（`<script setup>` 語法）
- 使用 Pinia 進行狀態管理（如需要）
- 使用 `$fetch` 或 `useFetch` 進行 API 呼叫

### 開發環境設定
- 使用 Docker Compose 管理開發環境
- 前端開發伺服器使用 Port 4001（避免與常用 Port 衝突）
- 後端 API 服務使用 Port 4002
- MySQL 資料庫使用 Port 4306
- phpMyAdmin 使用 Port 4003

### 部署注意事項
- 建議使用 Nginx 作為反向代理伺服器
- 建議啟用 HTTPS（使用 Let's Encrypt 免費憑證）
- 建議設定定期資料庫備份
- 建議使用 Redis 進行 Session 和快取管理
- 建議設定 Log Rotation 避免日誌檔案過大

## 資料庫結構概覽

### 核心資料表

1. **urban_renewals** - 都市更新會
2. **land_plots** - 土地資料
3. **buildings** - 建物資料
4. **property_owners** - 所有權人
5. **owner_land_ownerships** - 土地持分關係
6. **owner_building_ownerships** - 建物持分關係
7. **meetings** - 會議
8. **meeting_attendance** - 會員報到
9. **meeting_documents** - 會議文件
10. **voting_topics** - 投票議題
11. **voting_records** - 投票記錄
12. **users** - 使用者
13. **user_sessions** - 使用者 Session
14. **system_settings** - 系統設定
15. **notifications** - 通知
16. **counties** - 縣市
17. **districts** - 鄉鎮市區
18. **sections** - 地段

### 關聯關係

- 都市更新會 1:N 土地資料
- 土地資料 1:N 建物資料
- 都市更新會 1:N 所有權人
- 所有權人 N:M 土地資料（透過 owner_land_ownerships）
- 所有權人 N:M 建物資料（透過 owner_building_ownerships）
- 都市更新會 1:N 會議
- 會議 1:N 會員報到
- 會議 1:N 投票議題
- 投票議題 1:N 投票記錄
- 會議 1:N 會議文件

## API 路由概覽

### 身份驗證 API (`/api/auth`)
- `POST /api/auth/login` - 登入
- `POST /api/auth/logout` - 登出
- `POST /api/auth/refresh` - 更新 Token
- `GET /api/auth/me` - 取得當前使用者資訊
- `POST /api/auth/forgot-password` - 忘記密碼
- `POST /api/auth/reset-password` - 重設密碼

### 使用者管理 API (`/api/users`)
- `GET /api/users` - 取得使用者列表
- `GET /api/users/{id}` - 取得單一使用者
- `POST /api/users` - 建立使用者
- `PUT /api/users/{id}` - 更新使用者
- `DELETE /api/users/{id}` - 刪除使用者
- `PATCH /api/users/{id}/toggle-status` - 切換使用者狀態
- `PATCH /api/users/{id}/reset-login-attempts` - 重置登入嘗試次數

### 都市更新會 API (`/api/urban-renewals`)
- `GET /api/urban-renewals` - 取得更新會列表
- `GET /api/urban-renewals/{id}` - 取得單一更新會
- `POST /api/urban-renewals` - 建立更新會
- `PUT /api/urban-renewals/{id}` - 更新更新會
- `DELETE /api/urban-renewals/{id}` - 刪除更新會

### 地籍資料 API (`/api/land-plots`, `/api/urban-renewals/{id}/land-plots`)
- `GET /api/urban-renewals/{id}/land-plots` - 取得更新會的土地列表
- `POST /api/urban-renewals/{id}/land-plots` - 建立土地
- `GET /api/land-plots/{id}` - 取得單一土地
- `PUT /api/land-plots/{id}` - 更新土地
- `DELETE /api/land-plots/{id}` - 刪除土地

### 所有權人 API (`/api/property-owners`, `/api/urban-renewals/{id}/property-owners`)
- `GET /api/urban-renewals/{id}/property-owners` - 取得更新會的所有權人列表
- `POST /api/urban-renewals/{id}/property-owners` - 建立所有權人
- `GET /api/property-owners/{id}` - 取得單一所有權人
- `PUT /api/property-owners/{id}` - 更新所有權人
- `DELETE /api/property-owners/{id}` - 刪除所有權人
- `POST /api/urban-renewals/{id}/property-owners/import` - 批次匯入所有權人
- `GET /api/urban-renewals/{id}/property-owners/export` - 匯出所有權人
- `GET /api/property-owners/template` - 下載匯入範本

### 會議 API (`/api/meetings`)
- `GET /api/meetings` - 取得會議列表
- `GET /api/meetings/{id}` - 取得單一會議
- `POST /api/meetings` - 建立會議
- `PUT /api/meetings/{id}` - 更新會議
- `DELETE /api/meetings/{id}` - 刪除會議
- `PATCH /api/meetings/{id}/status` - 更新會議狀態
- `GET /api/meetings/{id}/statistics` - 取得會議統計

### 會員報到 API (`/api/meeting-attendance`)
- `GET /api/meeting-attendance` - 取得報到列表
- `POST /api/meeting-attendance/check-in` - 單筆報到
- `POST /api/meeting-attendance/batch-check-in` - 批次報到
- `PATCH /api/meeting-attendance/{id}/update-status` - 更新報到狀態
- `GET /api/meeting-attendance/{meetingId}/summary` - 取得報到摘要
- `GET /api/meeting-attendance/{meetingId}/statistics` - 取得報到統計
- `GET /api/meeting-attendance/{meetingId}/export` - 匯出報到資料

### 投票議題 API (`/api/voting-topics`)
- `GET /api/voting-topics` - 取得投票議題列表
- `GET /api/voting-topics/{id}` - 取得單一投票議題
- `POST /api/voting-topics` - 建立投票議題
- `PUT /api/voting-topics/{id}` - 更新投票議題
- `DELETE /api/voting-topics/{id}` - 刪除投票議題
- `PATCH /api/voting-topics/{id}/start-voting` - 開始投票
- `PATCH /api/voting-topics/{id}/close-voting` - 關閉投票

### 投票 API (`/api/voting`)
- `GET /api/voting` - 取得投票記錄列表
- `POST /api/voting/vote` - 單筆投票
- `POST /api/voting/batch-vote` - 批次投票
- `GET /api/voting/my-vote/{topicId}` - 取得我的投票
- `DELETE /api/voting/remove-vote` - 移除投票
- `GET /api/voting/statistics/{topicId}` - 取得投票統計
- `GET /api/voting/detailed/{topicId}` - 取得詳細投票記錄
- `GET /api/voting/export/{topicId}` - 匯出投票結果

### 系統設定 API (`/api/system-settings`)
- `GET /api/system-settings` - 取得所有設定
- `GET /api/system-settings/public` - 取得公開設定
- `GET /api/system-settings/category/{category}` - 取得特定類別設定
- `GET /api/system-settings/get/{key}` - 取得單一設定
- `POST /api/system-settings/set` - 設定單一設定值
- `POST /api/system-settings/batch-set` - 批次設定
- `PATCH /api/system-settings/reset/{key}` - 重置設定
- `DELETE /api/system-settings/clear-cache` - 清除快取

### 通知 API (`/api/notifications`)
- `GET /api/notifications` - 取得通知列表
- `GET /api/notifications/{id}` - 取得單一通知
- `POST /api/notifications` - 建立通知
- `PATCH /api/notifications/{id}/mark-read` - 標記為已讀
- `PATCH /api/notifications/mark-all-read` - 全部標記為已讀
- `DELETE /api/notifications/{id}` - 刪除通知
- `GET /api/notifications/unread-count` - 取得未讀通知數量
- `GET /api/notifications/types` - 取得通知類型

### 文件 API (`/api/documents`)
- `GET /api/documents` - 取得文件列表
- `GET /api/documents/{id}` - 取得單一文件
- `POST /api/documents/upload` - 上傳文件
- `POST /api/documents/batch-upload` - 批次上傳
- `GET /api/documents/download/{id}` - 下載文件
- `PUT /api/documents/{id}` - 更新文件資訊
- `DELETE /api/documents/{id}` - 刪除文件
- `GET /api/documents/statistics` - 取得文件統計

### 地區資料 API (`/api/locations`)
- `GET /api/locations/counties` - 取得縣市列表
- `GET /api/locations/districts/{countyCode}` - 取得鄉鎮市區列表
- `GET /api/locations/sections/{countyCode}/{districtCode}` - 取得地段列表
- `GET /api/locations/hierarchy` - 取得完整階層資料

## 前端頁面結構

### 公開頁面
- `/login` - 登入頁面
- `/signup` - 註冊頁面（保留但可能未實作）

### 主要功能頁面
- `/` - 首頁（功能導覽）
- `/tables/urban-renewal` - 都市更新會管理
- `/tables/urban-renewal/{id}/basic-info` - 更新會基本資料
- `/tables/urban-renewal/{id}/property-owners` - 所有權人管理
- `/tables/urban-renewal/{id}/property-owners/create` - 新增所有權人
- `/tables/urban-renewal/{id}/property-owners/{ownerId}/edit` - 編輯所有權人
- `/tables/meeting` - 會議管理
- `/tables/meeting/{meetingId}/basic-info` - 會議基本資料
- `/tables/meeting/{meetingId}/member-checkin` - 會員報到
- `/tables/meeting/{meetingId}/checkin-display` - 報到顯示（大螢幕）
- `/tables/meeting/{meetingId}/voting-topics` - 投票議題列表
- `/tables/meeting/{meetingId}/voting-topics/new/basic-info` - 新增投票議題
- `/tables/meeting/{meetingId}/voting-topics/{topicId}/basic-info` - 投票議題基本資料
- `/tables/meeting/{meetingId}/voting-topics/{topicId}/voting` - 投票頁面
- `/tables/meeting/{meetingId}/voting-topics/{topicId}/results` - 投票結果
- `/pages/user` - 使用者資料變更

## 專案憲章參照

本專案已建立專案憲章（`.specify/spec.constitution`），定義了以下核心原則和規範：

### 程式碼品質
- 使用 JavaScript ES6+ 標準，不使用 TypeScript
- 變數和函數使用英文命名，註解使用正體中文
- 遵循 ESLint 和 Prettier 規範
- 遵循 DRY 原則和模組化設計

### 文件規範
- 所有專案文件、註解、README 使用正體中文
- API 文件和技術規格使用正體中文
- Git Commit 訊息使用正體中文，遵循 Conventional Commits 格式

### 使用者體驗一致性
- 前端使用 Nuxt UI 元件庫（禁止使用 Vuetify）
- 圖示使用 Heroicons
- 主色為綠色漸層（#2FA633 到 #72BB29）
- 所有使用者介面文字和錯誤訊息使用正體中文

### 效能要求
- 首次內容繪製 (FCP) < 1.8 秒
- 最大內容繪製 (LCP) < 2.5 秒
- API 回應時間 < 200 毫秒（P95）

### 安全性要求
- 使用 JWT Token 進行身份驗證
- 敏感資料加密存儲
- 實作 SQL 注入、XSS、CSRF 防護

詳細規範請參閱 `.specify/spec.constitution` 檔案。
