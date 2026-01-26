<?php
require __DIR__ . '/vendor/autoload.php';

$db = \Config\Database::connect();

// 檢查資料表是否存在
$query = $db->query("SHOW TABLES LIKE 'api_request_logs'");
$result = $query->getResult();

if (count($result) > 0) {
    echo "✓ Table 'api_request_logs' exists\n\n";
    
    // 檢查資料筆數
    $count = $db->table('api_request_logs')->countAllResults();
    echo "Total records: $count\n\n";
    
    // 顯示最近 5 筆
    if ($count > 0) {
        echo "Recent logs:\n";
        $logs = $db->table('api_request_logs')->orderBy('id', 'DESC')->limit(5)->get()->getResultArray();
        foreach ($logs as $log) {
            echo "- [{$log['id']}] {$log['method']} {$log['endpoint']} ({$log['response_status']}) at {$log['created_at']}\n";
        }
    } else {
        echo "No logs found.\n";
    }
} else {
    echo "✗ Table 'api_request_logs' does NOT exist\n";
    echo "Please run: php spark migrate\n";
}
