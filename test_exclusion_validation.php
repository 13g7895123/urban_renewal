<?php
/**
 * 測試排除類型驗證功能
 */

require 'backend/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

echo "=== 測試排除類型驗證功能 ===\n\n";

// 1. 創建包含錯誤排除類型的測試檔案
echo "1. 創建測試檔案...\n";

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 設定標題列
$headers = ['姓名', '身分證字號', '電話', '地址', '土地持份', '是否為實施者', '排除類型', '備註'];
$sheet->fromArray($headers, null, 'A1');

// 添加測試資料（包含錯誤的排除類型）
$testData = [
    ['測試所有權人1', 'A123456789', '0912345678', '台北市信義區', '1/10', '否', '錯誤類型', '測試用'],
    ['測試所有權人2', 'B123456789', '0912345679', '台北市大安區', '2/10', '是', '無', '正常資料'],
    ['測試所有權人3', 'C123456789', '0912345680', '台北市中山區', '1/5', '否', '不明', '另一個錯誤'],
];

$sheet->fromArray($testData, null, 'A2');

$invalidFile = '/tmp/test_invalid_exclusion.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($invalidFile);

echo "✅ 測試檔案創建完成: $invalidFile\n\n";

// 2. 測試匯入驗證
echo "2. 測試匯入驗證（模擬 API 呼叫）...\n";
echo "請使用以下 curl 命令測試：\n\n";

echo "# 先登入獲取 token\n";
echo "TOKEN=\$(curl -s -X POST 'http://localhost:9228/api/auth/login' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\"username\":\"admin\",\"password\":\"password\"}' | \\\n";
echo "  php -r '\$data=json_decode(file_get_contents(\"php://stdin\"),true); echo \$data[\"data\"][\"token\"];')\n\n";

echo "# 測試匯入錯誤的排除類型\n";
echo "curl -X POST 'http://localhost:9228/api/urban-renewals/6/property-owners/import' \\\n";
echo "  -H \"Authorization: Bearer \$TOKEN\" \\\n";
echo "  -F \"file=@$invalidFile\"\n\n";

// 3. 創建包含正確排除類型的測試檔案
echo "3. 創建正確的測試檔案...\n";

$spreadsheet2 = new Spreadsheet();
$sheet2 = $spreadsheet2->getActiveSheet();
$sheet2->fromArray($headers, null, 'A1');

$validData = [
    ['測試所有權人4', 'D123456789', '0912345678', '台北市信義區', '1/10', '否', '無', '測試用'],
    ['測試所有權人5', 'E123456789', '0912345679', '台北市大安區', '2/10', '是', '面積過小', '正常資料'],
    ['測試所有權人6', 'F123456789', '0912345680', '台北市中山區', '1/5', '否', '已出售', '正常資料'],
    ['測試所有權人7', 'G123456789', '0912345681', '台北市松山區', '3/10', '否', '未聯繫', '正常資料'],
];

$sheet2->fromArray($validData, null, 'A2');

$validFile = '/tmp/test_valid_exclusion.xlsx';
$writer2 = new Xlsx($spreadsheet2);
$writer2->save($validFile);

echo "✅ 正確的測試檔案創建完成: $validFile\n\n";

echo "# 測試匯入正確的排除類型\n";
echo "curl -X POST 'http://localhost:9228/api/urban-renewals/6/property-owners/import' \\\n";
echo "  -H \"Authorization: Bearer \$TOKEN\" \\\n";
echo "  -F \"file=@$validFile\"\n\n";

echo "=== 測試檔案準備完成 ===\n";
