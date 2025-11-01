<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap CodeIgniter
$app = require_once FCPATH . '../app/Config/Paths.php';
$app = new \CodeIgniter\CodeIgniter($app);
$app->initialize();

// Test UserPermissionModel
$model = model('UserPermissionModel');
echo "Model loaded: " . (is_object($model) ? "YES" : "NO") . "\n";

if ($model) {
    echo "Testing getUserPermissions(18)...\n";
    $permissions = $model->getUserPermissions(18);
    echo "Result: ";
    print_r($permissions);
}
