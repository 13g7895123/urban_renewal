<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\MeetingModel;

class TestMeetingExportData extends BaseCommand
{
    protected $group       = 'app';
    protected $name        = 'test:meeting-export';
    protected $description = '測試會議資料匯出時的資料結構';

    public function run(array $params)
    {
        $meetingId = $params[0] ?? 1;

        CLI::write('=== 測試會議資料匯出 ===', 'green');
        CLI::newLine();
        CLI::write("測試會議 ID: $meetingId");
        CLI::newLine();

        $db = \Config\Database::connect();

        // 1. 測試直接 SQL 查詢
        CLI::write('【1. 直接 SQL 查詢】', 'yellow');
        CLI::write(str_repeat('-', 60));

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
            CLI::write('✓ 找到會議資料', 'green');
            CLI::newLine();

            CLI::write('基本資訊:', 'cyan');
            CLI::write('  會議 ID: ' . ($result['id'] ?? 'N/A'));
            CLI::write('  會議名稱: ' . ($result['meeting_name'] ?? 'N/A'));
            CLI::write('  會議類型: ' . ($result['meeting_type'] ?? 'N/A'));
            CLI::write('  會議日期: ' . ($result['meeting_date'] ?? 'N/A'));
            CLI::write('  會議時間: ' . ($result['meeting_time'] ?? 'N/A'));
            CLI::write('  會議地點: ' . ($result['meeting_location'] ?? 'N/A'));
            CLI::newLine();

            CLI::write('更新會資訊:', 'cyan');
            CLI::write('  更新會 ID: ' . ($result['urban_renewal_id'] ?? 'N/A'));

            $urName = $result['urban_renewal_name'] ?? null;
            if (is_null($urName)) {
                CLI::write('  更新會名稱: [NULL]', 'red');
            } elseif ($urName === '') {
                CLI::write('  更新會名稱: [空字串]', 'red');
            } else {
                CLI::write('  更新會名稱: ' . $urName, 'green');
            }

            $chairman = $result['chairman_name'] ?? null;
            if (is_null($chairman)) {
                CLI::write('  理事長姓名: [NULL]', 'red');
            } elseif ($chairman === '') {
                CLI::write('  理事長姓名: [空字串]', 'red');
            } else {
                CLI::write('  理事長姓名: ' . $chairman, 'green');
            }

            $phone = $result['chairman_phone'] ?? null;
            if (is_null($phone)) {
                CLI::write('  理事長電話: [NULL]', 'red');
            } elseif ($phone === '') {
                CLI::write('  理事長電話: [空字串]', 'red');
            } else {
                CLI::write('  理事長電話: ' . $phone, 'green');
            }

            CLI::newLine();
            CLI::write('資料類型檢查:', 'cyan');
            CLI::write('  urban_renewal_name 是否為 NULL: ' . (is_null($result['urban_renewal_name']) ? '是' : '否'));
            CLI::write('  urban_renewal_name 是否為空字串: ' . ($result['urban_renewal_name'] === '' ? '是' : '否'));
            CLI::write('  urban_renewal_name 長度: ' . strlen($result['urban_renewal_name'] ?? ''));
        } else {
            CLI::write("✗ 找不到會議資料 (ID: $meetingId)", 'red');
        }

        CLI::newLine();
        CLI::write(str_repeat('=', 60));
        CLI::newLine();

        // 2. 測試 MeetingModel 的方法
        CLI::write('【2. MeetingModel::getMeetingWithDetails()】', 'yellow');
        CLI::write(str_repeat('-', 60));

        try {
            $meetingModel = new MeetingModel();
            $meeting = $meetingModel->getMeetingWithDetails($meetingId);

            if ($meeting) {
                CLI::write('✓ MeetingModel 成功回傳資料', 'green');
                CLI::newLine();

                CLI::write('關鍵欄位值:', 'cyan');
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
                    $color = 'white';

                    if (is_null($value)) {
                        $display = '[NULL]';
                        $color = 'red';
                    } elseif ($value === '') {
                        $display = '[空字串]';
                        $color = 'red';
                    } else {
                        $display = $value;
                        $color = 'green';
                    }

                    CLI::write("  $label ($field): $display", $color);
                }

                CLI::newLine();
                CLI::write('完整資料結構:', 'cyan');
                CLI::write(str_repeat('-', 60));

                foreach ($meeting as $key => $value) {
                    if (is_array($value)) {
                        CLI::write("$key: [陣列]");
                    } else {
                        $display = is_null($value) ? '[NULL]' : (string)$value;
                        if (strlen($display) > 80) {
                            $display = substr($display, 0, 80) . '...';
                        }
                        CLI::write("$key: $display");
                    }
                }
            } else {
                CLI::write('✗ MeetingModel 無法取得資料', 'red');
            }
        } catch (\Exception $e) {
            CLI::write('✗ 發生錯誤: ' . $e->getMessage(), 'red');
            CLI::write('堆疊追蹤:', 'red');
            CLI::write($e->getTraceAsString());
        }

        CLI::newLine();
        CLI::write(str_repeat('=', 60));
        CLI::newLine();

        // 3. 列出所有會議
        CLI::write('【3. 所有會議列表】', 'yellow');
        CLI::write(str_repeat('-', 60));

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

        CLI::write('總共有 ' . count($allMeetings) . ' 筆會議資料（顯示前 10 筆）:', 'cyan');
        CLI::newLine();

        foreach ($allMeetings as $m) {
            $urName = $m['urban_renewal_name'] ?: '[無更新會名稱]';
            CLI::write("  會議 ID: {$m['id']}");
            CLI::write("  會議名稱: {$m['meeting_name']}");
            CLI::write("  更新會 ID: {$m['urban_renewal_id']}");
            CLI::write("  更新會名稱: $urName");
            CLI::write("  ---");
        }

        CLI::newLine();
        CLI::write(str_repeat('=', 60));
        CLI::newLine();

        // 4. 列出所有更新會
        CLI::write('【4. 所有更新會列表】', 'yellow');
        CLI::write(str_repeat('-', 60));

        $allUrbanRenewals = $db->query("
            SELECT id, name, chairman_name, chairman_phone
            FROM urban_renewals
            WHERE deleted_at IS NULL
            LIMIT 10
        ")->getResultArray();

        CLI::write('總共有 ' . count($allUrbanRenewals) . ' 筆更新會資料（顯示前 10 筆）:', 'cyan');
        CLI::newLine();

        foreach ($allUrbanRenewals as $ur) {
            $name = $ur['name'] ?: '[空值]';
            $chairman = $ur['chairman_name'] ?: '[空值]';
            $phone = $ur['chairman_phone'] ?: '[空值]';

            CLI::write("  更新會 ID: {$ur['id']}");
            CLI::write("  名稱: $name");
            CLI::write("  理事長: $chairman");
            CLI::write("  電話: $phone");
            CLI::write("  ---");
        }

        CLI::newLine();
        CLI::write('=== 測試完成 ===', 'green');
        CLI::newLine();
        CLI::write('提示: 執行 php spark test:meeting-export <meeting_id> 可以測試不同的會議 ID', 'light_gray');
    }
}
