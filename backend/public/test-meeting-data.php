<?php
/**
 * 測試會議資料匯出時的資料結構
 * 訪問: http://localhost:4002/test-meeting-data.php
 */

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';

// 載入 CodeIgniter
$pathsPath = realpath(FCPATH . '../app/Config/Paths.php');
$paths = require $pathsPath;
$paths = new $paths();

require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

// 初始化 CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();
$context = is_cli() ? 'php-cli' : 'web';
$app->setContext($context);

// 取得資料庫連接
$db = \Config\Database::connect();

echo "=== 測試會議資料匯出 ===\n\n";

// 從 GET 參數取得會議 ID，預設為 1
$meetingId = $_GET['meeting_id'] ?? 1;
echo "測試會議 ID: $meetingId\n\n";

// 1. 測試基本查詢
echo "【1. 直接 SQL 查詢】\n";
echo str_repeat('-', 60) . "\n";

$query = "SELECT
    m.*,
    ur.name as urban_renewal_name,
    ur.chairman_name,
    ur.chairman_phone
FROM meetings m
LEFT JOIN urban_renewals ur ON ur.id = m.urban_renewal_id
WHERE m.id = ? AND m.deleted_at IS NULL";

$result = $db->query($query, [$meetingId])->getRowArray();

if ($result) {
    echo "✓ 找到會議資料\n\n";

    echo "基本資訊:\n";
    echo "  會議 ID: " . ($result['id'] ?? 'N/A') . "\n";
    echo "  會議名稱: " . ($result['meeting_name'] ?? 'N/A') . "\n";
    echo "  會議類型: " . ($result['meeting_type'] ?? 'N/A') . "\n";
    echo "  會議日期: " . ($result['meeting_date'] ?? 'N/A') . "\n";
    echo "  會議時間: " . ($result['meeting_time'] ?? 'N/A') . "\n";
    echo "  會議地點: " . ($result['meeting_location'] ?? 'N/A') . "\n\n";

    echo "更新會資訊:\n";
    echo "  更新會 ID: " . ($result['urban_renewal_id'] ?? 'N/A') . "\n";
    echo "  更新會名稱: " . ($result['urban_renewal_name'] ?: '[空值或 NULL]') . "\n";
    echo "  理事長姓名: " . ($result['chairman_name'] ?: '[空值或 NULL]') . "\n";
    echo "  理事長電話: " . ($result['chairman_phone'] ?: '[空值或 NULL]') . "\n";

    // 檢查是否為空字串
    echo "\n資料類型檢查:\n";
    echo "  urban_renewal_name 是否為 NULL: " . (is_null($result['urban_renewal_name']) ? '是' : '否') . "\n";
    echo "  urban_renewal_name 是否為空字串: " . ($result['urban_renewal_name'] === '' ? '是' : '否') . "\n";
    echo "  urban_renewal_name 長度: " . strlen($result['urban_renewal_name'] ?? '') . "\n";
} else {
    echo "✗ 找不到會議資料 (ID: $meetingId)\n";
}

echo "\n" . str_repeat('=', 60) . "\n\n";

// 2. 測試 MeetingModel 的方法
echo "【2. MeetingModel::getMeetingWithDetails()】\n";
echo str_repeat('-', 60) . "\n";

try {
    $meetingModel = new \App\Models\MeetingModel();
    $meeting = $meetingModel->getMeetingWithDetails($meetingId);

    if ($meeting) {
        echo "✓ MeetingModel 成功回傳資料\n\n";

        echo "關鍵欄位值:\n";
        $fields = [
            'urban_renewal_name' => '更新會名稱',
            'meeting_name' => '會議名稱',
            'meeting_type' => '會議類型',
            'meeting_date' => '會議日期',
            'meeting_time' => '會議時間',
            'meeting_location' => '會議地點',
            'chairman_name' => '理事長姓名',
            'chairman_phone' => '理事長電話',
        ];

        foreach ($fields as $field => $label) {
            $value = $meeting[$field] ?? null;
            $status = '';

            if (is_null($value)) {
                $status = ' [NULL]';
            } elseif ($value === '') {
                $status = ' [空字串]';
            }

            echo "  $label ($field): " . ($value ?: '[無值]') . $status . "\n";
        }

        echo "\n完整資料結構:\n";
        echo str_repeat('-', 60) . "\n";
        foreach ($meeting as $key => $value) {
            if (is_array($value)) {
                echo "$key: [陣列]\n";
            } else {
                $display = is_null($value) ? '[NULL]' : (string)$value;
                if (strlen($display) > 100) {
                    $display = substr($display, 0, 100) . '...';
                }
                echo "$key: $display\n";
            }
        }
    } else {
        echo "✗ MeetingModel 無法取得資料\n";
    }
} catch (\Exception $e) {
    echo "✗ 發生錯誤: " . $e->getMessage() . "\n";
    echo "堆疊追蹤:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat('=', 60) . "\n\n";

// 3. 列出所有會議
echo "【3. 所有會議列表】\n";
echo str_repeat('-', 60) . "\n";

$allMeetings = $db->query("
    SELECT
        m.id,
        m.meeting_name,
        m.urban_renewal_id,
        ur.name as urban_renewal_name
    FROM meetings m
    LEFT JOIN urban_renewals ur ON m.urban_renewal_id = ur.id
    WHERE m.deleted_at IS NULL
    ORDER BY m.id DESC
    LIMIT 10
")->getResultArray();

echo "總共有 " . count($allMeetings) . " 筆會議資料（顯示前 10 筆）:\n\n";

foreach ($allMeetings as $m) {
    $urName = $m['urban_renewal_name'] ?: '[無更新會名稱]';
    echo "  會議 ID: {$m['id']}\n";
    echo "  會議名稱: {$m['meeting_name']}\n";
    echo "  更新會 ID: {$m['urban_renewal_id']}\n";
    echo "  更新會名稱: $urName\n";
    echo "  ---\n";
}

echo "\n" . str_repeat('=', 60) . "\n\n";

// 4. 列出所有更新會
echo "【4. 所有更新會列表】\n";
echo str_repeat('-', 60) . "\n";

$allUrbanRenewals = $db->query("
    SELECT id, name, chairman_name, chairman_phone
    FROM urban_renewals
    WHERE deleted_at IS NULL
    LIMIT 10
")->getResultArray();

echo "總共有 " . count($allUrbanRenewals) . " 筆更新會資料（顯示前 10 筆）:\n\n";

foreach ($allUrbanRenewals as $ur) {
    $name = $ur['name'] ?: '[空值]';
    $chairman = $ur['chairman_name'] ?: '[空值]';
    $phone = $ur['chairman_phone'] ?: '[空值]';

    echo "  更新會 ID: {$ur['id']}\n";
    echo "  名稱: $name\n";
    echo "  理事長: $chairman\n";
    echo "  電話: $phone\n";
    echo "  ---\n";
}

echo "\n=== 測試完成 ===\n";
echo "\n提示: 在 URL 加上 ?meeting_id=X 可以測試不同的會議 ID\n";
echo "例如: http://localhost:4002/test-meeting-data.php?meeting_id=2\n";
