<?php
// 測試認證和權限
require __DIR__ . '/vendor/autoload.php';

// 模擬登入取得 token
$ch = curl_init('http://localhost:9228/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'username' => 't1101',
    'password' => 't1101'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$loginData = json_decode($response, true);

echo "=== 登入測試 ===\n";
echo "Response: " . print_r($loginData, true) . "\n";

if (isset($loginData['data']['token'])) {
    $token = $loginData['data']['token'];
    echo "\nToken: " . substr($token, 0, 50) . "...\n";
    
    // 測試 /api/auth/me 取得用戶資訊
    echo "\n=== 測試 /api/auth/me ===\n";
    $ch = curl_init('http://localhost:9228/api/auth/me');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token
    ]);
    
    $meResponse = curl_exec($ch);
    $meData = json_decode($meResponse, true);
    echo "User Data: " . print_r($meData, true) . "\n";
    
    // 測試 /api/urban-renewals
    echo "\n=== 測試 /api/urban-renewals ===\n";
    $ch = curl_init('http://localhost:9228/api/urban-renewals');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token
    ]);
    
    $renewalsResponse = curl_exec($ch);
    $renewalsData = json_decode($renewalsResponse, true);
    echo "Renewals Response: " . print_r($renewalsData, true) . "\n";
    
    // 如果有 urban_renewal_id，測試 property-owners
    if (isset($meData['data']['urban_renewal_id']) && $meData['data']['urban_renewal_id']) {
        $urbanRenewalId = $meData['data']['urban_renewal_id'];
        echo "\n=== 測試 /api/urban-renewals/{$urbanRenewalId}/property-owners ===\n";
        $ch = curl_init("http://localhost:9228/api/urban-renewals/{$urbanRenewalId}/property-owners");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        
        $ownersResponse = curl_exec($ch);
        $ownersData = json_decode($ownersResponse, true);
        echo "Property Owners Response: " . print_r($ownersData, true) . "\n";
    }
} else {
    echo "登入失敗\n";
}
