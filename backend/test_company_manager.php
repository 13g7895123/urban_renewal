#!/usr/bin/env php
<?php

// 載入 CodeIgniter 框架
require __DIR__ . '/vendor/autoload.php';

// 初始化 CodeIgniter
$pathsConfig = new \Config\Paths();
$bootstrap = \CodeIgniter\Boot::bootWeb($pathsConfig);
$app = $bootstrap->getApp();

// 取得資料庫連接
$db = \Config\Database::connect();

echo "=== 企業管理者帳號查詢 ===\n\n";

$query = $db->query('SELECT id, username, email, role, is_company_manager, urban_renewal_id FROM users WHERE is_company_manager = 1 LIMIT 5');
$results = $query->getResultArray();

if (empty($results)) {
    echo "找不到企業管理者帳號\n";
} else {
    foreach ($results as $row) {
        echo sprintf(
            "ID: %d | Username: %s | Role: %s | Urban Renewal ID: %s\n",
            $row['id'],
            $row['username'],
            $row['role'],
            $row['urban_renewal_id'] ?? 'NULL'
        );
    }
}

echo "\n=== 更新會列表 ===\n\n";

$query = $db->query('SELECT id, name FROM urban_renewals WHERE deleted_at IS NULL LIMIT 5');
$renewals = $query->getResultArray();

if (empty($renewals)) {
    echo "找不到更新會資料\n";
} else {
    foreach ($renewals as $renewal) {
        echo sprintf("ID: %d | Name: %s\n", $renewal['id'], $renewal['name']);
    }
}
