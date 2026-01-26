<?php
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    echo "OPcache 已啟用\n";
    echo "已緩存的腳本數: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
    echo "記憶體使用: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
    echo "命中率: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
} else {
    echo "OPcache 未啟用\n";
}
