<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 shadow-lg sticky top-0 z-50">
      <div class="container mx-auto">
        <h1 class="text-3xl font-bold mb-2">🧪 系統功能完整測試頁面</h1>
        <p class="text-blue-100">都市更新會管理系統 - 所有功能測試介面</p>
        <div class="mt-4 flex gap-4 items-center flex-wrap">
          <div class="bg-white/20 px-4 py-2 rounded-lg">
            <span class="text-sm">API 基礎位址:</span>
            <input v-model="apiBaseUrl" class="ml-2 bg-white/20 border border-white/30 rounded px-2 py-1 text-sm w-64" />
          </div>
          <div class="bg-white/20 px-4 py-2 rounded-lg">
            <span class="text-sm">當前使用者類型:</span>
            <select v-model="currentUserType" class="ml-2 bg-white/20 border border-white/30 rounded px-2 py-1 text-sm">
              <option value="general">一般使用者</option>
              <option value="enterprise">企業使用者</option>
            </select>
          </div>
          <div class="bg-white/20 px-4 py-2 rounded-lg">
            <span class="text-sm">測試通過: {{ passedTests }}</span>
          </div>
          <div class="bg-white/20 px-4 py-2 rounded-lg">
            <span class="text-sm">測試失敗: {{ failedTests }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto p-6">
      <!-- Filter Bar -->
      <div class="bg-white rounded-lg shadow p-4 mb-6 flex gap-4 items-center flex-wrap">
        <div class="flex-1">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="搜尋功能..." 
            class="w-full border border-gray-300 rounded-lg px-4 py-2"
          />
        </div>
        <button @click="expandAll" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
          全部展開
        </button>
        <button @click="collapseAll" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
          全部收合
        </button>
        <button @click="clearAllResults" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
          清除結果
        </button>
      </div>

      <!-- Feature Modules -->
      <div class="space-y-6">
        <!-- 認證授權模組 -->
        <FeatureModule
          title="🔐 認證授權"
          :expanded="expandedModules.auth"
          @toggle="toggleModule('auth')"
        >
          <FeatureTest
            title="註冊"
            :fields="[
              { name: 'username', label: '使用者名稱', type: 'text', value: 'testuser_' + Date.now(), required: true },
              { name: 'email', label: 'Email', type: 'email', value: 'test_' + Date.now() + '@example.com', required: true },
              { name: 'password', label: '密碼', type: 'password', value: 'password123', required: true },
              { name: 'full_name', label: '真實姓名', type: 'text', value: '測試使用者', required: true },
              { name: 'phone', label: '電話', type: 'text', value: '0912345678', required: false }
            ]"
            method="POST"
            endpoint="/api/auth/register"
            :requires-auth="false"
            @test="executeTest"
          />
          
          <FeatureTest
            title="登入"
            :fields="[
              { name: 'email', label: 'Email', type: 'email', value: 'admin@example.com', required: true },
              { name: 'password', label: '密碼', type: 'password', value: 'password123', required: true }
            ]"
            method="POST"
            endpoint="/api/auth/login"
            :requires-auth="false"
            @test="executeTest"
          />

          <FeatureTest
            title="取得當前使用者資訊"
            :fields="[]"
            method="GET"
            endpoint="/api/auth/me"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="登出"
            :fields="[]"
            method="POST"
            endpoint="/api/auth/logout"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="忘記密碼"
            :fields="[
              { name: 'email', label: 'Email', type: 'email', value: 'admin@example.com', required: true }
            ]"
            method="POST"
            endpoint="/api/auth/forgot-password"
            :requires-auth="false"
            @test="executeTest"
          />

          <FeatureTest
            title="刷新Token"
            :fields="[]"
            method="POST"
            endpoint="/api/auth/refresh"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 更新會管理模組 -->
        <FeatureModule
          v-if="currentUserType === 'enterprise'"
          title="🏢 更新會管理"
          :expanded="expandedModules.urbanRenewal"
          @toggle="toggleModule('urbanRenewal')"
        >
          <FeatureTest
            title="查看更新會列表"
            :fields="[
              { name: 'page', label: '頁碼', type: 'number', value: '1', required: false },
              { name: 'per_page', label: '每頁筆數', type: 'number', value: '10', required: false }
            ]"
            method="GET"
            endpoint="/api/urban-renewals"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="建立更新會"
            :fields="[
              { name: 'name', label: '更新會名稱', type: 'text', value: '測試更新會_' + Date.now(), required: true },
              { name: 'county', label: '縣市', type: 'text', value: '臺北市', required: true },
              { name: 'district', label: '鄉鎮區', type: 'text', value: '中正區', required: true },
              { name: 'section', label: '段', type: 'text', value: '測試段', required: true },
              { name: 'address', label: '地址', type: 'text', value: '台北市中正區測試路123號', required: true },
              { name: 'chairman_name', label: '理事長姓名', type: 'text', value: '王大明', required: true },
              { name: 'chairman_phone', label: '理事長電話', type: 'text', value: '0912345678', required: true },
              { name: 'representative', label: '代表人', type: 'text', value: '王大明', required: false }
            ]"
            method="POST"
            endpoint="/api/urban-renewals"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看更新會詳情"
            :fields="[
              { name: 'id', label: '更新會ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/urban-renewals/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="更新更新會資料"
            :fields="[
              { name: 'id', label: '更新會ID', type: 'number', value: '1', required: true },
              { name: 'name', label: '更新會名稱', type: 'text', value: '更新後的名稱', required: true },
              { name: 'chairman_name', label: '理事長姓名', type: 'text', value: '李小明', required: false }
            ]"
            method="PUT"
            endpoint="/api/urban-renewals/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="刪除更新會"
            :fields="[
              { name: 'id', label: '更新會ID', type: 'number', value: '1', required: true }
            ]"
            method="DELETE"
            endpoint="/api/urban-renewals/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="批次指派更新會"
            :fields="[
              { name: 'renewal_ids', label: '更新會IDs (逗號分隔)', type: 'text', value: '1,2,3', required: true },
              { name: 'admin_id', label: '管理員ID', type: 'number', value: '1', required: true }
            ]"
            method="POST"
            endpoint="/api/urban-renewals/batch-assign"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="取得企業管理者列表"
            :fields="[]"
            method="GET"
            endpoint="/api/urban-renewals/company-managers"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 所有權人管理模組 -->
        <FeatureModule
          v-if="currentUserType === 'enterprise'"
          title="👥 所有權人管理"
          :expanded="expandedModules.propertyOwner"
          @toggle="toggleModule('propertyOwner')"
        >
          <FeatureTest
            title="查看所有權人列表"
            :fields="[
              { name: 'urban_renewal_id', label: '更新會ID', type: 'number', value: '1', required: true },
              { name: 'page', label: '頁碼', type: 'number', value: '1', required: false }
            ]"
            method="GET"
            endpoint="/api/urban-renewals/{urban_renewal_id}/property-owners"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="新增所有權人"
            :fields="[
              { name: 'urban_renewal_id', label: '更新會ID', type: 'number', value: '1', required: true },
              { name: 'name', label: '姓名', type: 'text', value: '張三', required: true },
              { name: 'id_number', label: '身分證字號', type: 'text', value: 'A123456789', required: true },
              { name: 'phone1', label: '電話1', type: 'text', value: '0912345678', required: false },
              { name: 'contact_address', label: '聯絡地址', type: 'text', value: '台北市信義區測試路1號', required: false },
              { name: 'household_address', label: '戶籍地址', type: 'text', value: '台北市信義區測試路1號', required: false },
              { name: 'exclusion_type', label: '排除類型', type: 'select', value: '', options: ['', '法院囑託查封', '假扣押', '假處分', '破產登記', '未經繼承'], required: false }
            ]"
            method="POST"
            endpoint="/api/property-owners"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看所有權人詳情"
            :fields="[
              { name: 'id', label: '所有權人ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/property-owners/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="更新所有權人"
            :fields="[
              { name: 'id', label: '所有權人ID', type: 'number', value: '1', required: true },
              { name: 'name', label: '姓名', type: 'text', value: '張三 (更新)', required: true },
              { name: 'phone1', label: '電話1', type: 'text', value: '0987654321', required: false }
            ]"
            method="PUT"
            endpoint="/api/property-owners/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="刪除所有權人"
            :fields="[
              { name: 'id', label: '所有權人ID', type: 'number', value: '1', required: true }
            ]"
            method="DELETE"
            endpoint="/api/property-owners/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="匯出所有權人"
            :fields="[
              { name: 'urban_renewal_id', label: '更新會ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/urban-renewals/{urban_renewal_id}/property-owners/export"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="下載匯入範本"
            :fields="[]"
            method="GET"
            endpoint="/api/property-owners/template"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看所有建物資料"
            :fields="[
              { name: 'urban_renewal_id', label: '更新會ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/urban-renewals/{urban_renewal_id}/property-owners/all-buildings"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 地號管理模組 -->
        <FeatureModule
          v-if="currentUserType === 'enterprise'"
          title="📍 地號管理"
          :expanded="expandedModules.landPlot"
          @toggle="toggleModule('landPlot')"
        >
          <FeatureTest
            title="查看地號列表"
            :fields="[
              { name: 'urban_renewal_id', label: '更新會ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/urban-renewals/{urban_renewal_id}/land-plots"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="新增地號"
            :fields="[
              { name: 'urban_renewal_id', label: '更新會ID', type: 'number', value: '1', required: true },
              { name: 'county', label: '縣市', type: 'text', value: '臺北市', required: true },
              { name: 'district', label: '鄉鎮區', type: 'text', value: '中正區', required: true },
              { name: 'section', label: '段', type: 'text', value: '測試段', required: true },
              { name: 'land_number_main', label: '地號(母)', type: 'text', value: '123', required: true },
              { name: 'land_number_sub', label: '地號(子)', type: 'text', value: '0', required: false },
              { name: 'land_area', label: '土地面積(平方公尺)', type: 'number', value: '100.50', required: true }
            ]"
            method="POST"
            endpoint="/api/urban-renewals/{urban_renewal_id}/land-plots"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看地號詳情"
            :fields="[
              { name: 'id', label: '地號ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/land-plots/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="更新地號"
            :fields="[
              { name: 'id', label: '地號ID', type: 'number', value: '1', required: true },
              { name: 'land_area', label: '土地面積', type: 'number', value: '150.75', required: true }
            ]"
            method="PUT"
            endpoint="/api/land-plots/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="刪除地號"
            :fields="[
              { name: 'id', label: '地號ID', type: 'number', value: '1', required: true }
            ]"
            method="DELETE"
            endpoint="/api/land-plots/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="設定代表地號"
            :fields="[
              { name: 'id', label: '地號ID', type: 'number', value: '1', required: true },
              { name: 'is_representative', label: '是否為代表地號', type: 'select', value: 'true', options: ['true', 'false'], required: true }
            ]"
            method="PUT"
            endpoint="/api/land-plots/{id}/representative"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 共同區域管理模組 -->
        <FeatureModule
          v-if="currentUserType === 'enterprise'"
          title="🏗️ 共同區域管理"
          :expanded="expandedModules.jointCommonArea"
          @toggle="toggleModule('jointCommonArea')"
        >
          <FeatureTest
            title="查看共同區域列表"
            :fields="[
              { name: 'urban_renewal_id', label: '更新會ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/urban-renewals/{urban_renewal_id}/joint-common-areas"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="新增共同區域"
            :fields="[
              { name: 'urban_renewal_id', label: '更新會ID', type: 'number', value: '1', required: true },
              { name: 'area_name', label: '區域名稱', type: 'text', value: '公共走廊', required: true },
              { name: 'area_size', label: '面積(平方公尺)', type: 'number', value: '50.25', required: true },
              { name: 'area_type', label: '區域類型', type: 'text', value: '走廊', required: false }
            ]"
            method="POST"
            endpoint="/api/urban-renewals/{urban_renewal_id}/joint-common-areas"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看共同區域詳情"
            :fields="[
              { name: 'id', label: '共同區域ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/joint-common-areas/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="更新共同區域"
            :fields="[
              { name: 'id', label: '共同區域ID', type: 'number', value: '1', required: true },
              { name: 'area_name', label: '區域名稱', type: 'text', value: '公共走廊 (更新)', required: true }
            ]"
            method="PUT"
            endpoint="/api/joint-common-areas/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="刪除共同區域"
            :fields="[
              { name: 'id', label: '共同區域ID', type: 'number', value: '1', required: true }
            ]"
            method="DELETE"
            endpoint="/api/joint-common-areas/{id}"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 會議管理模組 -->
        <FeatureModule
          title="📅 會議管理"
          :expanded="expandedModules.meeting"
          @toggle="toggleModule('meeting')"
        >
          <FeatureTest
            title="查看會議列表"
            :fields="[
              { name: 'page', label: '頁碼', type: 'number', value: '1', required: false },
              { name: 'per_page', label: '每頁筆數', type: 'number', value: '10', required: false },
              { name: 'status', label: '狀態', type: 'select', value: '', options: ['', 'draft', 'scheduled', 'in_progress', 'completed', 'cancelled'], required: false }
            ]"
            method="GET"
            endpoint="/api/meetings"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="建立會議"
            :fields="[
              { name: 'urban_renewal_id', label: '更新會ID', type: 'number', value: '1', required: true },
              { name: 'meeting_name', label: '會議名稱', type: 'text', value: '第一次會員大會', required: true },
              { name: 'meeting_type', label: '會議類型', type: 'select', value: '會員大會', options: ['會員大會', '理事會', '監事會', '臨時會議'], required: true },
              { name: 'meeting_date', label: '會議日期', type: 'date', value: '2026-02-01', required: true },
              { name: 'meeting_time', label: '會議時間', type: 'time', value: '14:00', required: true },
              { name: 'meeting_location', label: '會議地點', type: 'text', value: '台北市中正區會議室', required: true },
              { name: 'chairman_name', label: '主席姓名', type: 'text', value: '王大明', required: true },
              { name: 'exclude_owner_from_count', label: '排除所有權人計算', type: 'select', value: 'false', options: ['true', 'false'], required: false }
            ]"
            method="POST"
            endpoint="/api/meetings"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看會議詳情"
            :fields="[
              { name: 'id', label: '會議ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/meetings/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="更新會議"
            :fields="[
              { name: 'id', label: '會議ID', type: 'number', value: '1', required: true },
              { name: 'meeting_name', label: '會議名稱', type: 'text', value: '第一次會員大會 (更新)', required: true }
            ]"
            method="PUT"
            endpoint="/api/meetings/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="刪除會議"
            :fields="[
              { name: 'id', label: '會議ID', type: 'number', value: '1', required: true }
            ]"
            method="DELETE"
            endpoint="/api/meetings/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="更新會議狀態"
            :fields="[
              { name: 'id', label: '會議ID', type: 'number', value: '1', required: true },
              { name: 'status', label: '狀態', type: 'select', value: 'scheduled', options: ['draft', 'scheduled', 'in_progress', 'completed', 'cancelled'], required: true }
            ]"
            method="PATCH"
            endpoint="/api/meetings/{id}/status"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看合格投票人快照"
            :fields="[
              { name: 'id', label: '會議ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/meetings/{id}/eligible-voters"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="重新整理合格投票人快照"
            :fields="[
              { name: 'id', label: '會議ID', type: 'number', value: '1', required: true }
            ]"
            method="POST"
            endpoint="/api/meetings/{id}/eligible-voters/refresh"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="搜尋會議"
            :fields="[
              { name: 'keyword', label: '關鍵字', type: 'text', value: '會員大會', required: false }
            ]"
            method="GET"
            endpoint="/api/meetings/search"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看即將舉行的會議"
            :fields="[]"
            method="GET"
            endpoint="/api/meetings/upcoming"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="會議狀態統計"
            :fields="[]"
            method="GET"
            endpoint="/api/meetings/status-statistics"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="會議統計"
            :fields="[
              { name: 'id', label: '會議ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/meetings/{id}/statistics"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="匯出會議通知書"
            :fields="[
              { name: 'id', label: '會議ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/meetings/{id}/export-notice"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="匯出簽到簿"
            :fields="[
              { name: 'id', label: '會議ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/meetings/{id}/export-signature-book"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 出席管理模組 -->
        <FeatureModule
          title="✅ 出席管理"
          :expanded="expandedModules.attendance"
          @toggle="toggleModule('attendance')"
        >
          <FeatureTest
            title="查看出席紀錄"
            :fields="[
              { name: 'meeting_id', label: '會議ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/meetings/{meeting_id}/attendances"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="會員報到"
            :fields="[
              { name: 'meeting_id', label: '會議ID', type: 'number', value: '1', required: true },
              { name: 'owner_id', label: '所有權人ID', type: 'number', value: '1', required: true },
              { name: 'attendance_type', label: '出席類型', type: 'select', value: 'present', options: ['present', 'proxy', 'absent'], required: true },
              { name: 'proxy_person', label: '代理人姓名', type: 'text', value: '', required: false }
            ]"
            method="POST"
            endpoint="/api/meetings/{meeting_id}/attendances/{owner_id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="更新出席紀錄"
            :fields="[
              { name: 'meeting_id', label: '會議ID', type: 'number', value: '1', required: true },
              { name: 'owner_id', label: '所有權人ID', type: 'number', value: '1', required: true },
              { name: 'attendance_type', label: '出席類型', type: 'select', value: 'present', options: ['present', 'proxy', 'absent'], required: true }
            ]"
            method="PUT"
            endpoint="/api/meetings/{meeting_id}/attendances/{owner_id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="批次報到"
            :fields="[
              { name: 'meeting_id', label: '會議ID', type: 'number', value: '1', required: true },
              { name: 'attendances', label: '報到資料 (JSON)', type: 'textarea', value: '[{&quot;property_owner_id&quot;:1,&quot;attendance_type&quot;:&quot;present&quot;}]', required: true }
            ]"
            method="POST"
            endpoint="/api/meetings/{meeting_id}/attendances/batch"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="出席統計"
            :fields="[
              { name: 'meeting_id', label: '會議ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/meetings/{meeting_id}/attendances/statistics"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="匯出出席紀錄"
            :fields="[
              { name: 'meeting_id', label: '會議ID', type: 'number', value: '1', required: true }
            ]"
            method="POST"
            endpoint="/api/meetings/{meeting_id}/attendances/export"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 投票議題管理模組 -->
        <FeatureModule
          title="🗳️ 投票議題管理"
          :expanded="expandedModules.votingTopic"
          @toggle="toggleModule('votingTopic')"
        >
          <FeatureTest
            title="查看投票議題列表"
            :fields="[
              { name: 'meeting_id', label: '會議ID', type: 'number', value: '1', required: false }
            ]"
            method="GET"
            endpoint="/api/voting-topics"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="建立投票議題"
            :fields="[
              { name: 'meeting_id', label: '會議ID', type: 'number', value: '1', required: true },
              { name: 'topic_number', label: '議題編號', type: 'text', value: '1', required: true },
              { name: 'topic_title', label: '議題標題', type: 'text', value: '是否同意更新計畫', required: true },
              { name: 'topic_description', label: '議題說明', type: 'textarea', value: '本案擬進行都市更新...', required: false },
              { name: 'voting_method', label: '投票方式', type: 'select', value: 'simple_majority', options: ['simple_majority', 'absolute_majority', 'two_thirds_majority', 'unanimous'], required: true },
              { name: 'is_anonymous', label: '是否匿名', type: 'select', value: 'false', options: ['true', 'false'], required: false }
            ]"
            method="POST"
            endpoint="/api/voting-topics"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看投票議題詳情"
            :fields="[
              { name: 'id', label: '議題ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/voting-topics/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="更新投票議題"
            :fields="[
              { name: 'id', label: '議題ID', type: 'number', value: '1', required: true },
              { name: 'topic_title', label: '議題標題', type: 'text', value: '是否同意更新計畫 (修正版)', required: true }
            ]"
            method="PUT"
            endpoint="/api/voting-topics/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="刪除投票議題"
            :fields="[
              { name: 'id', label: '議題ID', type: 'number', value: '1', required: true }
            ]"
            method="DELETE"
            endpoint="/api/voting-topics/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="開始投票"
            :fields="[
              { name: 'id', label: '議題ID', type: 'number', value: '1', required: true }
            ]"
            method="PATCH"
            endpoint="/api/voting-topics/{id}/start-voting"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="結束投票"
            :fields="[
              { name: 'id', label: '議題ID', type: 'number', value: '1', required: true }
            ]"
            method="PATCH"
            endpoint="/api/voting-topics/{id}/close-voting"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="投票議題統計"
            :fields="[]"
            method="GET"
            endpoint="/api/voting-topics/statistics"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="即將進行的投票"
            :fields="[]"
            method="GET"
            endpoint="/api/voting-topics/upcoming"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 投票功能模組 -->
        <FeatureModule
          title="📊 投票功能"
          :expanded="expandedModules.voting"
          @toggle="toggleModule('voting')"
        >
          <FeatureTest
            title="查看投票紀錄"
            :fields="[
              { name: 'topic_id', label: '議題ID', type: 'number', value: '1', required: false }
            ]"
            method="GET"
            endpoint="/api/voting"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="進行投票"
            :fields="[
              { name: 'voting_topic_id', label: '議題ID', type: 'number', value: '1', required: true },
              { name: 'property_owner_id', label: '所有權人ID', type: 'number', value: '1', required: true },
              { name: 'vote_choice', label: '投票選擇', type: 'select', value: 'agree', options: ['agree', 'disagree', 'abstain'], required: true }
            ]"
            method="POST"
            endpoint="/api/voting/vote"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="批次投票"
            :fields="[
              { name: 'votes', label: '投票資料 (JSON)', type: 'textarea', value: '[{&quot;voting_topic_id&quot;:1,&quot;property_owner_id&quot;:1,&quot;vote_choice&quot;:&quot;agree&quot;}]', required: true }
            ]"
            method="POST"
            endpoint="/api/voting/batch-vote"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看我的投票"
            :fields="[
              { name: 'topic_id', label: '議題ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/voting/my-vote/{topic_id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="撤回投票"
            :fields="[
              { name: 'voting_topic_id', label: '議題ID', type: 'number', value: '1', required: true },
              { name: 'property_owner_id', label: '所有權人ID', type: 'number', value: '1', required: true }
            ]"
            method="DELETE"
            endpoint="/api/voting/remove-vote"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="投票統計"
            :fields="[
              { name: 'topic_id', label: '議題ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/voting/statistics/{topic_id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="匯出投票結果"
            :fields="[
              { name: 'topic_id', label: '議題ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/voting/export/{topic_id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看詳細投票紀錄"
            :fields="[
              { name: 'topic_id', label: '議題ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/voting/detailed/{topic_id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="重新計算權重"
            :fields="[
              { name: 'topic_id', label: '議題ID', type: 'number', value: '1', required: true }
            ]"
            method="POST"
            endpoint="/api/voting/recalculate-weights/{topic_id}"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 企業管理模組 -->
        <FeatureModule
          v-if="currentUserType === 'enterprise'"
          title="🏭 企業管理"
          :expanded="expandedModules.company"
          @toggle="toggleModule('company')"
        >
          <FeatureTest
            title="查看企業資料"
            :fields="[]"
            method="GET"
            endpoint="/api/companies/me"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="更新企業資料"
            :fields="[
              { name: 'name', label: '企業名稱', type: 'text', value: '測試企業', required: false },
              { name: 'company_phone', label: '企業電話', type: 'text', value: '02-12345678', required: false }
            ]"
            method="PUT"
            endpoint="/api/companies/me"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看企業管理的更新會"
            :fields="[]"
            method="GET"
            endpoint="/api/companies/me/renewals"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看待審核使用者"
            :fields="[]"
            method="GET"
            endpoint="/api/companies/me/pending-users"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="審核使用者"
            :fields="[
              { name: 'user_id', label: '使用者ID', type: 'number', value: '1', required: true }
            ]"
            method="POST"
            endpoint="/api/companies/me/approve-user/{user_id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="取得邀請碼"
            :fields="[]"
            method="GET"
            endpoint="/api/companies/me/invite-code"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="產生新邀請碼"
            :fields="[]"
            method="POST"
            endpoint="/api/companies/me/generate-invite-code"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看更新會成員"
            :fields="[
              { name: 'renewal_id', label: '更新會ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/companies/me/renewals/{renewal_id}/members"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="指派成員到更新會"
            :fields="[
              { name: 'renewal_id', label: '更新會ID', type: 'number', value: '1', required: true },
              { name: 'user_id', label: '使用者ID', type: 'number', value: '1', required: true },
              { name: 'permissions', label: '權限 (逗號分隔)', type: 'text', value: 'view,edit', required: false }
            ]"
            method="POST"
            endpoint="/api/companies/me/renewals/{renewal_id}/assign"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="取消成員指派"
            :fields="[
              { name: 'renewal_id', label: '更新會ID', type: 'number', value: '1', required: true },
              { name: 'user_id', label: '使用者ID', type: 'number', value: '1', required: true }
            ]"
            method="DELETE"
            endpoint="/api/companies/me/renewals/{renewal_id}/members/{user_id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看可用成員"
            :fields="[]"
            method="GET"
            endpoint="/api/companies/me/available-members"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看企業管理者"
            :fields="[
              { name: 'company_id', label: '企業ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/companies/{company_id}/managers"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看企業使用者"
            :fields="[
              { name: 'company_id', label: '企業ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/companies/{company_id}/users"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 使用者管理模組 (Admin Only) -->
        <FeatureModule
          title="👤 使用者管理 (Admin)"
          :expanded="expandedModules.user"
          @toggle="toggleModule('user')"
        >
          <FeatureTest
            title="查看使用者列表"
            :fields="[
              { name: 'page', label: '頁碼', type: 'number', value: '1', required: false },
              { name: 'role', label: '角色', type: 'select', value: '', options: ['', 'admin', 'chairman', 'member', 'observer'], required: false }
            ]"
            method="GET"
            endpoint="/api/users"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看使用者詳情"
            :fields="[
              { name: 'id', label: '使用者ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/users/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="新增使用者"
            :fields="[
              { name: 'username', label: '使用者名稱', type: 'text', value: 'newuser_' + Date.now(), required: true },
              { name: 'email', label: 'Email', type: 'email', value: 'new_' + Date.now() + '@example.com', required: true },
              { name: 'password', label: '密碼', type: 'password', value: 'password123', required: true },
              { name: 'role', label: '角色', type: 'select', value: 'member', options: ['admin', 'chairman', 'member', 'observer'], required: true }
            ]"
            method="POST"
            endpoint="/api/users"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="更新使用者"
            :fields="[
              { name: 'id', label: '使用者ID', type: 'number', value: '1', required: true },
              { name: 'full_name', label: '真實姓名', type: 'text', value: '更新後的姓名', required: false }
            ]"
            method="PUT"
            endpoint="/api/users/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="刪除使用者"
            :fields="[
              { name: 'id', label: '使用者ID', type: 'number', value: '1', required: true }
            ]"
            method="DELETE"
            endpoint="/api/users/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="切換使用者狀態"
            :fields="[
              { name: 'id', label: '使用者ID', type: 'number', value: '1', required: true },
              { name: 'is_active', label: '啟用狀態', type: 'select', value: 'true', options: ['true', 'false'], required: true }
            ]"
            method="PATCH"
            endpoint="/api/users/{id}/toggle-status"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="重設登入嘗試次數"
            :fields="[
              { name: 'id', label: '使用者ID', type: 'number', value: '1', required: true }
            ]"
            method="PATCH"
            endpoint="/api/users/{id}/reset-login-attempts"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="設定為企業使用者"
            :fields="[
              { name: 'id', label: '使用者ID', type: 'number', value: '1', required: true }
            ]"
            method="POST"
            endpoint="/api/users/{id}/set-as-company-user"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="設定為企業管理者"
            :fields="[
              { name: 'id', label: '使用者ID', type: 'number', value: '1', required: true }
            ]"
            method="POST"
            endpoint="/api/users/{id}/set-as-company-manager"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="搜尋使用者"
            :fields="[
              { name: 'keyword', label: '關鍵字', type: 'text', value: 'admin', required: false }
            ]"
            method="GET"
            endpoint="/api/users/search"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="角色統計"
            :fields="[]"
            method="GET"
            endpoint="/api/users/role-statistics"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="使用者統計"
            :fields="[]"
            method="GET"
            endpoint="/api/users/stats"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看個人資料"
            :fields="[]"
            method="GET"
            endpoint="/api/users/profile"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="更新個人資料"
            :fields="[
              { name: 'full_name', label: '真實姓名', type: 'text', value: '新姓名', required: false },
              { name: 'phone', label: '電話', type: 'text', value: '0912345678', required: false }
            ]"
            method="PUT"
            endpoint="/api/users/profile"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="變更密碼"
            :fields="[
              { name: 'current_password', label: '當前密碼', type: 'password', value: 'password123', required: true },
              { name: 'new_password', label: '新密碼', type: 'password', value: 'newpassword123', required: true },
              { name: 'confirm_password', label: '確認新密碼', type: 'password', value: 'newpassword123', required: true }
            ]"
            method="POST"
            endpoint="/api/users/change-password"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="管理員變更使用者密碼"
            :fields="[
              { name: 'id', label: '使用者ID', type: 'number', value: '1', required: true },
              { name: 'new_password', label: '新密碼', type: 'password', value: 'newpassword123', required: true }
            ]"
            method="PATCH"
            endpoint="/api/users/{id}/password"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 通知管理模組 -->
        <FeatureModule
          title="🔔 通知管理"
          :expanded="expandedModules.notification"
          @toggle="toggleModule('notification')"
        >
          <FeatureTest
            title="查看通知列表"
            :fields="[
              { name: 'page', label: '頁碼', type: 'number', value: '1', required: false },
              { name: 'is_read', label: '是否已讀', type: 'select', value: '', options: ['', 'true', 'false'], required: false }
            ]"
            method="GET"
            endpoint="/api/notifications"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看通知詳情"
            :fields="[
              { name: 'id', label: '通知ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/notifications/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="建立通知"
            :fields="[
              { name: 'title', label: '標題', type: 'text', value: '測試通知', required: true },
              { name: 'content', label: '內容', type: 'textarea', value: '這是一則測試通知', required: true },
              { name: 'type', label: '類型', type: 'select', value: 'info', options: ['info', 'warning', 'success', 'error'], required: true }
            ]"
            method="POST"
            endpoint="/api/notifications"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="標記已讀"
            :fields="[
              { name: 'id', label: '通知ID', type: 'number', value: '1', required: true }
            ]"
            method="PATCH"
            endpoint="/api/notifications/{id}/mark-read"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="批次標記已讀"
            :fields="[
              { name: 'ids', label: '通知IDs (逗號分隔)', type: 'text', value: '1,2,3', required: true }
            ]"
            method="PATCH"
            endpoint="/api/notifications/mark-multiple-read"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="標記全部已讀"
            :fields="[]"
            method="PATCH"
            endpoint="/api/notifications/mark-all-read"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="刪除通知"
            :fields="[
              { name: 'id', label: '通知ID', type: 'number', value: '1', required: true }
            ]"
            method="DELETE"
            endpoint="/api/notifications/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看未讀數量"
            :fields="[]"
            method="GET"
            endpoint="/api/notifications/unread-count"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="通知統計"
            :fields="[]"
            method="GET"
            endpoint="/api/notifications/statistics"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="建立會議通知"
            :fields="[
              { name: 'meeting_id', label: '會議ID', type: 'number', value: '1', required: true }
            ]"
            method="POST"
            endpoint="/api/notifications/create-meeting-notification"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="建立投票通知"
            :fields="[
              { name: 'voting_topic_id', label: '議題ID', type: 'number', value: '1', required: true }
            ]"
            method="POST"
            endpoint="/api/notifications/create-voting-notification"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="清理過期通知"
            :fields="[]"
            method="DELETE"
            endpoint="/api/notifications/clean-expired"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看通知類型"
            :fields="[]"
            method="GET"
            endpoint="/api/notifications/types"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 文件管理模組 -->
        <FeatureModule
          title="📄 文件管理"
          :expanded="expandedModules.document"
          @toggle="toggleModule('document')"
        >
          <FeatureTest
            title="查看文件列表"
            :fields="[
              { name: 'page', label: '頁碼', type: 'number', value: '1', required: false }
            ]"
            method="GET"
            endpoint="/api/documents"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看文件詳情"
            :fields="[
              { name: 'id', label: '文件ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/documents/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="下載文件"
            :fields="[
              { name: 'id', label: '文件ID', type: 'number', value: '1', required: true }
            ]"
            method="GET"
            endpoint="/api/documents/download/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="更新文件資訊"
            :fields="[
              { name: 'id', label: '文件ID', type: 'number', value: '1', required: true },
              { name: 'title', label: '文件標題', type: 'text', value: '更新後的標題', required: false }
            ]"
            method="PUT"
            endpoint="/api/documents/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="刪除文件"
            :fields="[
              { name: 'id', label: '文件ID', type: 'number', value: '1', required: true }
            ]"
            method="DELETE"
            endpoint="/api/documents/{id}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="文件統計"
            :fields="[]"
            method="GET"
            endpoint="/api/documents/statistics"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看最近文件"
            :fields="[]"
            method="GET"
            endpoint="/api/documents/recent"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看文件類型"
            :fields="[]"
            method="GET"
            endpoint="/api/documents/types"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="儲存空間使用量"
            :fields="[]"
            method="GET"
            endpoint="/api/documents/storage-usage"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="清理孤立檔案"
            :fields="[]"
            method="DELETE"
            endpoint="/api/documents/clean-orphan-files"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 系統設定模組 (Admin Only) -->
        <FeatureModule
          title="⚙️ 系統設定 (Admin)"
          :expanded="expandedModules.systemSettings"
          @toggle="toggleModule('systemSettings')"
        >
          <FeatureTest
            title="查看系統設定"
            :fields="[]"
            method="GET"
            endpoint="/api/system-settings"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看公開設定"
            :fields="[]"
            method="GET"
            endpoint="/api/system-settings/public"
            :requires-auth="false"
            @test="executeTest"
          />

          <FeatureTest
            title="依類別查看設定"
            :fields="[
              { name: 'category', label: '類別', type: 'text', value: 'general', required: true }
            ]"
            method="GET"
            endpoint="/api/system-settings/category/{category}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="取得特定設定值"
            :fields="[
              { name: 'key', label: '設定鍵', type: 'text', value: 'site_name', required: true }
            ]"
            method="GET"
            endpoint="/api/system-settings/get/{key}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="設定系統參數"
            :fields="[
              { name: 'key', label: '設定鍵', type: 'text', value: 'site_name', required: true },
              { name: 'value', label: '設定值', type: 'text', value: '都市更新會系統', required: true }
            ]"
            method="POST"
            endpoint="/api/system-settings/set"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="批次設定"
            :fields="[
              { name: 'settings', label: '設定資料 (JSON)', type: 'textarea', value: '{&quot;site_name&quot;:&quot;系統&quot;,&quot;max_upload_size&quot;:&quot;10&quot;}', required: true }
            ]"
            method="POST"
            endpoint="/api/system-settings/batch-set"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="重設設定"
            :fields="[
              { name: 'key', label: '設定鍵', type: 'text', value: 'site_name', required: true }
            ]"
            method="PATCH"
            endpoint="/api/system-settings/reset/{key}"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看設定類別"
            :fields="[]"
            method="GET"
            endpoint="/api/system-settings/categories"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="清除快取"
            :fields="[]"
            method="DELETE"
            endpoint="/api/system-settings/clear-cache"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="驗證設定值"
            :fields="[
              { name: 'key', label: '設定鍵', type: 'text', value: 'site_name', required: true },
              { name: 'value', label: '設定值', type: 'text', value: '測試系統', required: true }
            ]"
            method="POST"
            endpoint="/api/system-settings/validate"
            :requires-auth="true"
            @test="executeTest"
          />

          <FeatureTest
            title="查看系統資訊"
            :fields="[]"
            method="GET"
            endpoint="/api/system-settings/system-info"
            :requires-auth="true"
            @test="executeTest"
          />
        </FeatureModule>

        <!-- 地區資料模組 -->
        <FeatureModule
          title="🗺️ 地區資料"
          :expanded="expandedModules.location"
          @toggle="toggleModule('location')"
        >
          <FeatureTest
            title="查看縣市列表"
            :fields="[]"
            method="GET"
            endpoint="/api/locations/counties"
            :requires-auth="false"
            @test="executeTest"
          />

          <FeatureTest
            title="查看鄉鎮區列表"
            :fields="[
              { name: 'county_code', label: '縣市代碼', type: 'text', value: 'A', required: true }
            ]"
            method="GET"
            endpoint="/api/locations/districts/{county_code}"
            :requires-auth="false"
            @test="executeTest"
          />

          <FeatureTest
            title="查看段列表"
            :fields="[
              { name: 'county_code', label: '縣市代碼', type: 'text', value: 'A', required: true },
              { name: 'district_code', label: '鄉鎮區代碼', type: 'text', value: '01', required: true }
            ]"
            method="GET"
            endpoint="/api/locations/sections/{county_code}/{district_code}"
            :requires-auth="false"
            @test="executeTest"
          />

          <FeatureTest
            title="查看階層資料"
            :fields="[]"
            method="GET"
            endpoint="/api/locations/hierarchy"
            :requires-auth="false"
            @test="executeTest"
          />
        </FeatureModule>
      </div>
    </div>

    <!-- Footer -->
    <div class="bg-gray-800 text-white p-6 mt-12">
      <div class="container mx-auto text-center">
        <p class="text-sm">© 2026 都市更新會管理系統 - 功能測試頁面</p>
        <p class="text-xs text-gray-400 mt-2">此頁面用於測試所有API端點功能</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

definePageMeta({
  layout: false
})

// State
const apiBaseUrl = ref('http://localhost:8080')
const currentUserType = ref('general')
const searchQuery = ref('')
const passedTests = ref(0)
const failedTests = ref(0)

// Expanded modules tracking
const expandedModules = ref({
  auth: false,
  urbanRenewal: false,
  propertyOwner: false,
  landPlot: false,
  jointCommonArea: false,
  meeting: false,
  attendance: false,
  votingTopic: false,
  voting: false,
  company: false,
  user: false,
  notification: false,
  document: false,
  systemSettings: false,
  location: false
})

// Methods
const toggleModule = (module) => {
  expandedModules.value[module] = !expandedModules.value[module]
}

const expandAll = () => {
  Object.keys(expandedModules.value).forEach(key => {
    expandedModules.value[key] = true
  })
}

const collapseAll = () => {
  Object.keys(expandedModules.value).forEach(key => {
    expandedModules.value[key] = false
  })
}

const clearAllResults = () => {
  passedTests.value = 0
  failedTests.value = 0
}

const executeTest = async (testData) => {
  try {
    console.log('Executing test:', testData)
    // This will be implemented in the child component
    passedTests.value++
  } catch (error) {
    console.error('Test failed:', error)
    failedTests.value++
  }
}
</script>

<style scoped>
/* Additional styles if needed */
</style>
