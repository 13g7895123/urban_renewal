<?php
require 'vendor/autoload.php';

$db = \Config\Database::connect();

echo "=== 查詢所有權人 4 的地號關係 ===\n";
$query = $db->query('SELECT * FROM owner_land_ownerships WHERE property_owner_id = 4');
$results = $query->getResultArray();
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n";

echo "=== 查詢所有地號 ===\n";
$query2 = $db->query('SELECT id, land_number_main, land_number_sub FROM land_plots WHERE urban_renewal_id = 6');
$results2 = $query2->getResultArray();
echo json_encode($results2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
