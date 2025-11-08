<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap CodeIgniter
$_SERVER['CI_ENVIRONMENT'] = 'development';
$pathsConfig = APPPATH . 'Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;

$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
require realpath($bootstrap) ?: $bootstrap;

$app = Config\Services::codeigniter();
$app->initialize();

echo "========================================\n";
echo "測試 PropertyOwnerModel 刪除功能\n";
echo "========================================\n\n";

// 建立測試資料
$model = new \App\Models\PropertyOwnerModel();
$ownerBuildingModel = new \App\Models\OwnerBuildingOwnershipModel();
$ownerLandModel = new \App\Models\OwnerLandOwnershipModel();

echo "1. 建立測試 property owner...\n";
$testData = [
    'urban_renewal_id' => 6,
    'owner_code' => 'TEST_MODEL_DELETE',
    'name' => '測試Model刪除',
    'id_number' => 'Z666666666',
];

$newId = $model->insert($testData);

if (!$newId) {
    echo "❌ 建立測試資料失敗\n";
    print_r($model->errors());
    exit(1);
}

echo "✓ 測試資料已建立，ID: $newId\n\n";

// 檢查資料存在
echo "2. 驗證資料存在...\n";
$owner = $model->find($newId);
if ($owner) {
    echo "✓ 資料存在: {$owner['name']} (ID: {$owner['id']})\n\n";
} else {
    echo "❌ 找不到測試資料\n";
    exit(1);
}

// 開始刪除測試
echo "3. 測試刪除功能...\n";
$db = \Config\Database::connect();
$db->transStart();

// 刪除關聯的建物和地號
$buildingOwnerships = $ownerBuildingModel->getByPropertyOwnerId($newId);
echo "   找到 " . count($buildingOwnerships) . " 筆建物關聯\n";
foreach ($buildingOwnerships as $ownership) {
    $result = $ownerBuildingModel->delete($ownership['id']);
    echo "   - 刪除建物關聯 ID {$ownership['id']}: " . ($result ? 'success' : 'failed') . "\n";
}

$landOwnerships = $ownerLandModel->getByPropertyOwnerId($newId);
echo "   找到 " . count($landOwnerships) . " 筆地號關聯\n";
foreach ($landOwnerships as $ownership) {
    $result = $ownerLandModel->delete($ownership['id']);
    echo "   - 刪除地號關聯 ID {$ownership['id']}: " . ($result ? 'success' : 'failed') . "\n";
}

// 刪除 property owner
echo "\n   執行刪除 property owner (ID: $newId)...\n";
$deleted = $model->delete($newId);
echo "   刪除結果: " . ($deleted ? 'success' : 'failed') . "\n";
echo "   Deleted variable type: " . gettype($deleted) . "\n";
echo "   Deleted value: ";
var_dump($deleted);

if (!$deleted) {
    echo "\n   Model errors:\n";
    print_r($model->errors());
}

$db->transComplete();

echo "\n   Transaction status: " . ($db->transStatus() === false ? 'FAILED' : 'SUCCESS') . "\n\n";

// 檢查刪除結果
echo "4. 驗證刪除結果...\n";
$checkOwner = $model->find($newId);
if ($checkOwner) {
    echo "❌ 刪除失敗！資料仍然存在\n";
    print_r($checkOwner);
} else {
    echo "✅ 刪除成功！資料已被移除\n";
}

echo "\n========================================\n";
echo "測試完成\n";
echo "========================================\n";
