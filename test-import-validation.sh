#!/bin/bash

# Test script for property owner import with invalid exclusion_type

echo "=== 測試所有權人匯入功能 - 排除類型驗證 ==="
echo ""

# Get auth token first
echo "1. 登入取得 token..."
LOGIN_RESPONSE=$(curl -s -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}')

TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.data.token')

if [ "$TOKEN" = "null" ] || [ -z "$TOKEN" ]; then
    echo "❌ 登入失敗"
    echo "Response: $LOGIN_RESPONSE"
    exit 1
fi

echo "✓ 登入成功，取得 token"
echo ""

# Create a test Excel file with invalid exclusion_type
echo "2. 建立測試 Excel 檔案（包含無效的排除類型）..."

# Create a temporary PHP script to generate Excel
cat > /tmp/create_test_excel.php << 'PHPEOF'
<?php
require '/var/www/html/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Add headers
$headers = ['所有權人編號', '所有權人名稱', '身分證字號', '電話1', '電話2', '聯絡地址', '戶籍地址', '排除類型', '備註'];
$sheet->fromArray($headers, NULL, 'A1');

// Add test data with VALID exclusion_type
$sheet->fromArray(['TEST001', '測試所有權人1', 'A123456789', '0912345678', '', '台北市', '', '假扣押', ''], NULL, 'A2');

// Add test data with INVALID exclusion_type
$sheet->fromArray(['TEST002', '測試所有權人2', 'B987654321', '0923456789', '', '新北市', '', '無效類型', '應該會失敗'], NULL, 'A3');

// Add test data with another INVALID exclusion_type
$sheet->fromArray(['TEST003', '測試所有權人3', 'C135792468', '0934567890', '', '桃園市', '', '查封中', '也應該失敗'], NULL, 'A4');

// Add test data with VALID exclusion_type
$sheet->fromArray(['TEST004', '測試所有權人4', 'D246813579', '0945678901', '', '台中市', '', '破產登記', ''], NULL, 'A5');

// Add test data with empty exclusion_type (should be valid)
$sheet->fromArray(['TEST005', '測試所有權人5', 'E369258147', '0956789012', '', '高雄市', '', '', '空白也應該有效'], NULL, 'A6');

$writer = new Xlsx($spreadsheet);
$writer->save('/tmp/test_property_owners.xlsx');

echo "Excel 檔案已建立: /tmp/test_property_owners.xlsx\n";
PHPEOF

# Execute the PHP script inside the backend container
docker exec urban_renewal_backend_dev php /tmp/create_test_excel.php
docker cp urban_renewal_backend_dev:/tmp/test_property_owners.xlsx /tmp/test_property_owners.xlsx

echo "✓ 測試 Excel 檔案已建立"
echo ""

# Import the file
echo "3. 匯入測試檔案（urban_renewal_id=1）..."
IMPORT_RESPONSE=$(curl -s -X POST http://localhost:9228/api/urban-renewals/1/property-owners/import \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@/tmp/test_property_owners.xlsx")

echo "$IMPORT_RESPONSE" | jq '.'

echo ""
echo "=== 測試結果分析 ==="
echo ""

SUCCESS_COUNT=$(echo $IMPORT_RESPONSE | jq -r '.data.success_count')
ERROR_COUNT=$(echo $IMPORT_RESPONSE | jq -r '.data.error_count')

echo "成功匯入: $SUCCESS_COUNT 筆"
echo "失敗: $ERROR_COUNT 筆"
echo ""

if [ "$ERROR_COUNT" -gt "0" ]; then
    echo "錯誤詳情:"
    echo "$IMPORT_RESPONSE" | jq -r '.data.errors[]'
    echo ""
fi

# Expected result:
# - Row 2 (TEST001 with 假扣押): SUCCESS
# - Row 3 (TEST002 with 無效類型): FAIL - should show error about invalid exclusion_type
# - Row 4 (TEST003 with 查封中): FAIL - should show error about invalid exclusion_type  
# - Row 5 (TEST004 with 破產登記): SUCCESS
# - Row 6 (TEST005 with empty): SUCCESS

echo "=== 預期結果 ==="
echo "✓ 成功: 3 筆（假扣押、破產登記、空白）"
echo "✗ 失敗: 2 筆（無效類型、查封中）"
echo "✗ 失敗的兩筆應該顯示：'排除類型 XXX 無效，必須是以下其中之一：法院囑託查封、假扣押、假處分、破產登記、未經繼承'"
echo ""

# Clean up
rm -f /tmp/test_property_owners.xlsx
docker exec urban_renewal_backend_dev rm -f /tmp/test_property_owners.xlsx /tmp/create_test_excel.php

echo "=== 測試完成 ==="
