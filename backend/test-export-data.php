<?php

// 測試會議資料匯出時的資料結構
require_once __DIR__ . '/vendor/autoload.php';

// 載入 CodeIgniter
$app = require_once FCPATH . '../app/Config/Paths.php';

// 初始化
$bootstrap = \CodeIgniter\Config\Services::codeigniter();
$bootstrap->initialize();

// 取得資料庫連接
$db = \Config\Database::connect();

// 測試查詢
$meetingId = 1; // 測試用的會議 ID，請根據實際情況修改

echo "=== 測試會議資料匯出 ===\n\n";

// 1. 測試基本查詢
echo "1. 基本會議查詢:\n";
$result = $db->table('meetings')
    ->select('meetings.*, urban_renewals.name as urban_renewal_name, urban_renewals.chairman_name, urban_renewals.chairman_phone')
    ->join('urban_renewals', 'urban_renewals.id = meetings.urban_renewal_id', 'left')
    ->where('meetings.id', $meetingId)
    ->get()
    ->getRowArray();

if ($result) {
    echo "✓ 找到會議資料\n";
    echo "會議 ID: " . ($result['id'] ?? 'N/A') . "\n";
    echo "會議名稱: " . ($result['meeting_name'] ?? 'N/A') . "\n";
    echo "會議類型: " . ($result['meeting_type'] ?? 'N/A') . "\n";
    echo "會議日期: " . ($result['meeting_date'] ?? 'N/A') . "\n";
    echo "會議時間: " . ($result['meeting_time'] ?? 'N/A') . "\n";
    echo "會議地點: " . ($result['meeting_location'] ?? 'N/A') . "\n";
    echo "更新會 ID: " . ($result['urban_renewal_id'] ?? 'N/A') . "\n";
    echo "更新會名稱: " . ($result['urban_renewal_name'] ?? '[空值]') . "\n";
    echo "理事長姓名: " . ($result['chairman_name'] ?? '[空值]') . "\n";
    echo "理事長電話: " . ($result['chairman_phone'] ?? '[空值]') . "\n";
} else {
    echo "✗ 找不到會議資料 (ID: $meetingId)\n";
}

echo "\n";

// 2. 檢查 urban_renewals 表
echo "2. 檢查 urban_renewals 表:\n";
$urbanRenewals = $db->table('urban_renewals')
    ->select('id, name, chairman_name, chairman_phone')
    ->get()
    ->getResultArray();

echo "總共有 " . count($urbanRenewals) . " 筆更新會資料\n";
foreach ($urbanRenewals as $ur) {
    echo "- ID: " . $ur['id'] . ", 名稱: " . ($ur['name'] ?: '[空值]') . ", 理事長: " . ($ur['chairman_name'] ?: '[空值]') . "\n";
}

echo "\n";

// 3. 檢查 meetings 表
echo "3. 檢查 meetings 表:\n";
$meetings = $db->table('meetings')
    ->select('id, meeting_name, urban_renewal_id')
    ->where('deleted_at', null)
    ->get()
    ->getResultArray();

echo "總共有 " . count($meetings) . " 筆會議資料\n";
foreach ($meetings as $m) {
    echo "- 會議 ID: " . $m['id'] . ", 名稱: " . $m['meeting_name'] . ", 更新會 ID: " . $m['urban_renewal_id'] . "\n";
}

echo "\n";

// 4. 測試 MeetingModel 的方法
echo "4. 測試 MeetingModel::getMeetingWithDetails():\n";
$meetingModel = new \App\Models\MeetingModel();
$meeting = $meetingModel->getMeetingWithDetails($meetingId);

if ($meeting) {
    echo "✓ MeetingModel 回傳資料\n";
    echo "urban_renewal_name: " . ($meeting['urban_renewal_name'] ?? '[空值]') . "\n";
    echo "meeting_name: " . ($meeting['meeting_name'] ?? '[空值]') . "\n";
    echo "meeting_type: " . ($meeting['meeting_type'] ?? '[空值]') . "\n";
    echo "meeting_date: " . ($meeting['meeting_date'] ?? '[空值]') . "\n";
    echo "meeting_time: " . ($meeting['meeting_time'] ?? '[空值]') . "\n";
    echo "meeting_location: " . ($meeting['meeting_location'] ?? '[空值]') . "\n";
    echo "chairman_name: " . ($meeting['chairman_name'] ?? '[空值]') . "\n";
    echo "chairman_phone: " . ($meeting['chairman_phone'] ?? '[空值]') . "\n";

    echo "\n完整資料結構:\n";
    print_r($meeting);
} else {
    echo "✗ MeetingModel 無法取得資料\n";
}

echo "\n=== 測試完成 ===\n";
