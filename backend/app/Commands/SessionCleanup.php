<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SessionCleanup extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'session:cleanup';
    protected $description = '清理過期的使用者會話';

    protected $usage = 'session:cleanup [options]';
    protected $arguments = [];
    protected $options = [
        '--force' => '強制執行清理，不詢問確認',
        '--dry-run' => '模擬執行，不實際刪除',
    ];

    public function run(array $params)
    {
        CLI::write('開始清理過期會話...', 'yellow');

        $force = CLI::getOption('force');
        $dryRun = CLI::getOption('dry-run');

        try {
            $sessionModel = model('UserSessionModel');

            // 取得過期會話數量
            $expiredCount = $sessionModel
                ->where('expires_at <', date('Y-m-d H:i:s'))
                ->where('is_active', 0)
                ->countAllResults(false);

            if ($expiredCount === 0) {
                CLI::write('沒有需要清理的過期會話', 'green');
                return;
            }

            CLI::write("找到 {$expiredCount} 個過期會話", 'yellow');

            // 確認執行
            if (!$force && !$dryRun) {
                $confirm = CLI::prompt('確定要刪除這些會話嗎？', ['y', 'n']);
                if ($confirm !== 'y') {
                    CLI::write('已取消清理', 'yellow');
                    return;
                }
            }

            if ($dryRun) {
                CLI::write('[模擬模式] 將刪除 ' . $expiredCount . ' 個會話', 'cyan');

                // 顯示即將刪除的會話資訊
                $sessions = $sessionModel
                    ->select('id, user_id, expires_at, created_at')
                    ->where('expires_at <', date('Y-m-d H:i:s'))
                    ->where('is_active', 0)
                    ->limit(10)
                    ->findAll();

                if (!empty($sessions)) {
                    CLI::write('範例會話（前 10 筆）：', 'cyan');
                    foreach ($sessions as $session) {
                        CLI::write("  - ID: {$session['id']}, User: {$session['user_id']}, Expired: {$session['expires_at']}", 'white');
                    }
                }

                CLI::write('[模擬模式] 未執行實際刪除', 'cyan');
                return;
            }

            // 執行清理
            $deleted = $sessionModel
                ->where('expires_at <', date('Y-m-d H:i:s'))
                ->where('is_active', 0)
                ->delete();

            if ($deleted) {
                CLI::write("成功刪除 {$expiredCount} 個過期會話", 'green');

                // 記錄到日誌
                log_message('info', "Session cleanup: deleted {$expiredCount} expired sessions");
            } else {
                CLI::write('清理過程中發生錯誤', 'red');
                log_message('error', 'Session cleanup failed');
            }

            // 顯示統計資訊
            $this->showStats();

        } catch (\Exception $e) {
            CLI::write('清理失敗: ' . $e->getMessage(), 'red');
            log_message('error', 'Session cleanup error: ' . $e->getMessage());
        }
    }

    /**
     * 顯示會話統計資訊
     */
    protected function showStats()
    {
        try {
            $sessionModel = model('UserSessionModel');

            $activeCount = $sessionModel
                ->where('is_active', 1)
                ->where('expires_at >', date('Y-m-d H:i:s'))
                ->countAllResults();

            $totalCount = $sessionModel->countAllResults();

            CLI::newLine();
            CLI::write('會話統計：', 'yellow');
            CLI::write("  活動會話: {$activeCount}", 'white');
            CLI::write("  總會話數: {$totalCount}", 'white');

        } catch (\Exception $e) {
            CLI::write('無法取得統計資訊', 'red');
        }
    }
}
