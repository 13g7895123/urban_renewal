<?php

/**
 * ExcelExportService 使用範例
 *
 * 這個檔案展示如何使用 ExcelExportService 來匯出 Excel 檔案
 */

use App\Services\ExcelExportService;

// ========================================
// 範例 1: 簡單的資料表格匯出
// ========================================
function example1_simpleTable()
{
    $service = new ExcelExportService();

    // 設定文件屬性
    $service->setDocumentProperties(
        '都市更新會管理系統',
        '會員清單',
        '會員清單匯出',
        '會員資料匯出報表'
    );

    // 設定標題
    $service->setTitle('會員清單報表', 'A', 'E', 16);

    // 新增空白列
    $service->addEmptyRows(1);

    // 新增表格標頭
    $service->addTableHeader(['姓名', '電話', '電子郵件', '地址', '備註']);

    // 準備資料
    $members = [
        ['王小明', '0912-345-678', 'wang@example.com', '台北市信義區', ''],
        ['李小華', '0923-456-789', 'lee@example.com', '新北市板橋區', ''],
        ['陳大明', '0934-567-890', 'chen@example.com', '桃園市中壢區', 'VIP會員'],
    ];

    // 新增資料列
    $service->addTableData($members, true, 5);

    // 自動調整欄寬
    $service->autoSizeColumns('A', 'E');

    // 儲存檔案
    $filepath = $service->saveToFile('會員清單_' . date('Ymd') . '.xlsx');

    return $filepath;
}

// ========================================
// 範例 2: 帶統計資訊的複雜報表
// ========================================
function example2_reportWithStatistics()
{
    $service = new ExcelExportService();

    // 設定文件屬性
    $service->setDocumentProperties(
        '都市更新會管理系統',
        '會議簽到結果',
        '會議簽到結果匯出',
        '會議簽到結果匯出報表'
    );

    // 主標題
    $service->setTitle('會議簽到結果', 'A', 'F', 16);
    $service->addEmptyRows(1);

    // 會議資訊
    $service->addInfoRow('會議名稱：', '2024年第一次會員大會', 'A', 'B', 'F');
    $service->addInfoRow('會議日期：', '2024-01-15', 'A', 'B', 'F');
    $service->addInfoRow('會議地點：', '台北市政府會議室', 'A', 'B', 'F');
    $service->addEmptyRows(1);

    // 統計資訊區段
    $service->addSectionTitle('簽到統計', 14);

    // 統計表格標頭
    $service->addTableHeader(['類型', '人數', '比例']);

    // 統計資料
    $statistics = [
        ['親自出席', '25', '50%'],
        ['代理出席', '15', '30%'],
        ['未出席', '10', '20%'],
        ['總計', '50', '100%'],
    ];

    // 最後一列加粗
    foreach ($statistics as $index => $stat) {
        $isTotalRow = ($index === count($statistics) - 1);
        $service->addRow($stat, $isTotalRow, true, 3);
    }

    $service->addEmptyRows(1);

    // 詳細簽到記錄
    $service->addSectionTitle('詳細簽到記錄', 14);

    $service->addTableHeader(['簽到時間', '會員姓名', '身分證字號', '簽到狀態', '代理人', '備註']);

    $attendances = [
        ['2024-01-15 09:05', '王小明', 'A123456789', '親自出席', '', ''],
        ['2024-01-15 09:10', '李小華', 'B234567890', '代理出席', '陳大明', ''],
        ['', '張小英', 'C345678901', '未出席', '', '請假'],
    ];

    $service->addTableData($attendances, true, 6);

    // 自動調整欄寬
    $service->autoSizeColumns('A', 'F');

    // 儲存檔案
    $filepath = $service->saveToFile('會議簽到_' . date('YmdHis') . '.xlsx');

    return $filepath;
}

// ========================================
// 範例 3: 直接下載（用於 Controller）
// ========================================
function example3_directDownload()
{
    $service = new ExcelExportService();

    $service->setDocumentProperties('系統', '報表', '報表匯出', '報表');
    $service->setTitle('測試報表');
    $service->addEmptyRows(1);
    $service->addTableHeader(['欄位1', '欄位2', '欄位3']);
    $service->addTableData([
        ['資料1', '資料2', '資料3'],
        ['資料4', '資料5', '資料6'],
    ]);
    $service->autoSizeColumns('A', 'C');

    // 直接輸出到瀏覽器（會終止程式執行）
    $service->download('測試報表.xlsx');
}

// ========================================
// 範例 4: 進階使用 - 直接操作 Spreadsheet 物件
// ========================================
function example4_advancedUsage()
{
    $service = new ExcelExportService();

    $service->setDocumentProperties('系統', '報表', '報表匯出', '報表');
    $service->setTitle('進階報表');

    // 取得 Spreadsheet 物件進行進階操作
    $spreadsheet = $service->getSpreadsheet();
    $sheet = $service->getSheet();

    // 進行自訂操作
    $sheet->setCellValue('A10', '自訂內容');
    $sheet->getStyle('A10')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0000'));

    // 儲存
    $filepath = $service->saveToFile('進階報表.xlsx');

    return $filepath;
}

// ========================================
// 實際 Controller 整合範例
// ========================================
class ExampleController
{
    public function export()
    {
        try {
            // 準備資料（從資料庫取得）
            $data = [
                'meeting' => [
                    'name' => '2024年第一次會員大會',
                    'date' => '2024-01-15',
                    'location' => '台北市政府會議室'
                ],
                'statistics' => [
                    'present' => 25,
                    'proxy' => 15,
                    'absent' => 10,
                    'total' => 50
                ],
                'attendances' => [
                    [
                        'check_in_time' => '2024-01-15 09:05',
                        'member_name' => '王小明',
                        'id_number' => 'A123456789',
                        'status' => '親自出席',
                        'proxy_name' => '',
                        'notes' => ''
                    ],
                    // ... 更多資料
                ]
            ];

            // 使用服務產生 Excel
            $service = new ExcelExportService();

            $service->setDocumentProperties(
                '都市更新會管理系統',
                '會議簽到結果',
                '會議簽到結果匯出'
            );

            $service->setTitle('會議簽到結果', 'A', 'F', 16);
            $service->addEmptyRows(1);

            // 會議資訊
            $service->addInfoRow('會議名稱：', $data['meeting']['name'], 'A', 'B', 'F');
            $service->addInfoRow('會議日期：', $data['meeting']['date'], 'A', 'B', 'F');
            $service->addInfoRow('會議地點：', $data['meeting']['location'], 'A', 'B', 'F');
            $service->addEmptyRows(1);

            // 統計資訊
            $service->addSectionTitle('簽到統計', 14);
            $service->addTableHeader(['類型', '人數', '比例']);

            $total = $data['statistics']['total'];
            $service->addRow([
                '親自出席',
                $data['statistics']['present'],
                round($data['statistics']['present'] / $total * 100, 1) . '%'
            ], false, true, 3);

            $service->addRow([
                '代理出席',
                $data['statistics']['proxy'],
                round($data['statistics']['proxy'] / $total * 100, 1) . '%'
            ], false, true, 3);

            $service->addRow([
                '未出席',
                $data['statistics']['absent'],
                round($data['statistics']['absent'] / $total * 100, 1) . '%'
            ], false, true, 3);

            $service->addRow([
                '總計',
                $total,
                '100%'
            ], true, true, 3);

            $service->addEmptyRows(1);

            // 詳細記錄
            $service->addSectionTitle('詳細簽到記錄', 14);
            $service->addTableHeader(['簽到時間', '會員姓名', '身分證字號', '簽到狀態', '代理人', '備註']);

            $tableData = [];
            foreach ($data['attendances'] as $attendance) {
                $tableData[] = [
                    $attendance['check_in_time'],
                    $attendance['member_name'],
                    $attendance['id_number'],
                    $attendance['status'],
                    $attendance['proxy_name'],
                    $attendance['notes']
                ];
            }
            $service->addTableData($tableData, true, 6);

            $service->autoSizeColumns('A', 'F');

            // 方法 1: 儲存到檔案後回傳
            $filepath = $service->saveToFile('會議簽到_' . date('YmdHis') . '.xlsx');

            return $this->response
                ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->setHeader('Content-Disposition', 'attachment; filename="' . basename($filepath) . '"')
                ->setBody(file_get_contents($filepath));

            // 方法 2: 直接下載（較簡單，但會 exit）
            // $service->download('會議簽到_' . date('YmdHis') . '.xlsx');

        } catch (\Exception $e) {
            log_message('error', '匯出失敗: ' . $e->getMessage());
            return response_error('匯出失敗', 500);
        }
    }
}
