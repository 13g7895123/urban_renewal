<?php

// Create SQLite database manually
$dbPath = __DIR__ . '/writable/database.db';

// Create database if it doesn't exist
$pdo = new PDO('sqlite:' . $dbPath);

// Create urban_renewals table
$createUrbanRenewalsTable = "
CREATE TABLE IF NOT EXISTS urban_renewals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    area DECIMAL(10,2) NOT NULL,
    member_count INTEGER NOT NULL,
    chairman_name VARCHAR(100) NOT NULL,
    chairman_phone VARCHAR(20) NOT NULL,
    address TEXT,
    representative VARCHAR(100),
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME
)";

// Create land_plots table
$createLandPlotsTable = "
CREATE TABLE IF NOT EXISTS land_plots (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    urban_renewal_id INTEGER NOT NULL,
    county VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    section VARCHAR(100) NOT NULL,
    land_number_main VARCHAR(4) NOT NULL,
    land_number_sub VARCHAR(4),
    land_area DECIMAL(10,2) NOT NULL,
    is_representative BOOLEAN DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME,
    FOREIGN KEY (urban_renewal_id) REFERENCES urban_renewals(id)
)";

try {
    $pdo->exec($createUrbanRenewalsTable);
    $pdo->exec($createLandPlotsTable);
    echo "Database and tables created successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>