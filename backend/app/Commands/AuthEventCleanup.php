<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class AuthEventCleanup extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'auth:cleanup';
    protected $description = '清理舊的認證事件日誌';

    protected $usage = 'auth:cleanup [options]';
    protected $arguments = [];
    protected $options = [
        '--days'     => '保留天數（預設：90 天）',
        '--force'    => '強制執行清理，不詢問確認',
        '--dry-run'  => '模擬執行，不實際刪除',
    ];

    public function run(array $params)
    {
        $days = CLI::getOption('days') ?? 90;
        $force = CLI::getOption('force');
        $dryRun = CLI::getOption('dry-run');

        CLI::write("開始清理 {$days} 天前的認證事件日誌...", 'yellow');

        try {
            $eventModel = model('AuthenticationEventModel');

            // 計算要刪除的記錄數
            $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

            $oldEventsCount = $eventModel
                ->where('created_at <', $cutoffDate)
                ->countAllResults(false);

            if ($oldEventsCount === 0) {
                CLI::write('沒有需要清理的舊記錄', 'green');
                return;
            }

            CLI::write("找到 {$oldEventsCount} 筆超過 {$days} 天的認證事件", 'yellow');

            // 顯示事件類型分佈
            $this->showEventDistribution($cutoffDate);

            // 確認執行
            if (!$force && !$dryRun) {
                $confirm = CLI::prompt('確定要刪除這些事件嗎？', ['y', 'n']);
                if ($confirm !== 'y') {
                    CLI::write('已取消清理', 'yellow');
                    return;
                }
            }

            if ($dryRun) {
                CLI::write("[模擬模式] 將刪除 {$oldEventsCount} 筆記錄", 'cyan');
                CLI::write('[模擬模式] 未執行實際刪除', 'cyan');
                return;
            }

            // 執行清理
            $deleted = $eventModel->deleteOldEvents($days);

            if ($deleted > 0) {
                CLI::write("成功刪除 {$deleted} 筆認證事件記錄", 'green');
                log_message('info', "Auth event cleanup: deleted {$deleted} events older than {$days} days");
            } else {
                CLI::write('沒有記錄被刪除', 'yellow');
            }

            // 顯示清理後統計
            $this->showStats();

        } catch (\Exception $e) {
            CLI::write('清理失敗: ' . $e->getMessage(), 'red');
            log_message('error', 'Auth event cleanup error: ' . $e->getMessage());
        }
    }

    /**
     * 顯示即將刪除的事件類型分佈
     */
    protected function showEventDistribution(string $cutoffDate)
    {
        try {
            $eventModel = model('AuthenticationEventModel');
            $db = \Config\Database::connect();

            $query = $db->table('authentication_events')
                ->select('event_type, COUNT(*) as count')
                ->where('created_at <', $cutoffDate)
                ->groupBy('event_type')
                ->get();

            $results = $query->getResultArray();

            if (!empty($results)) {
                CLI::newLine();
                CLI::write('事件類型分佈：', 'yellow');
                foreach ($results as $row) {
                    CLI::write("  {$row['event_type']}: {$row['count']}", 'white');
                }
                CLI::newLine();
            }

        } catch (\Exception $e) {
            // 忽略錯誤，繼續執行
        }
    }

    /**
     * 顯示認證事件統計資訊
     */
    protected function showStats()
    {
        try {
            $eventModel = model('AuthenticationEventModel');

            $stats = $eventModel->getEventStats(null, 24);

            CLI::newLine();
            CLI::write('過去 24 小時事件統計：', 'yellow');

            if (!empty($stats)) {
                foreach ($stats as $stat) {
                    CLI::write("  {$stat['event_type']}: {$stat['count']}", 'white');
                }
            } else {
                CLI::write('  無事件記錄', 'white');
            }

            // 總記錄數
            $totalCount = $eventModel->countAllResults();
            CLI::write("  總記錄數: {$totalCount}", 'white');

        } catch (\Exception $e) {
            CLI::write('無法取得統計資訊', 'red');
        }
    }
}
