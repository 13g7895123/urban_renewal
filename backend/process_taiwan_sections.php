<?php

/**
 * Process official Taiwan sections CSV data
 * This script processes the official Ministry of Interior section data
 * and generates hierarchical location data for database seeding
 */

$csvFile = 'taiwan_sections_official.csv';
$outputFile = 'processed_taiwan_locations.json';

// Check if CSV file exists
if (!file_exists($csvFile)) {
    die("CSV file not found: $csvFile\n");
}

// Initialize data structures
$counties = [];
$districts = [];
$sections = [];

// County code mapping (based on CSV 所區碼 prefix)
$countyCodeMap = [
    'A' => 'TPE', // 臺北市
    'B' => 'TCH', // 臺中市
    'C' => 'KEE', // 基隆市
    'D' => 'TNN', // 臺南市
    'E' => 'KHH', // 高雄市
    'F' => 'TPH', // 新北市
    'G' => 'ILA', // 宜蘭縣
    'H' => 'TAO', // 桃園市
    'I' => 'CYI', // 嘉義市
    'J' => 'HSZ', // 新竹縣
    'K' => 'MIA', // 苗栗縣
    'M' => 'NAN', // 南投縣
    'N' => 'CHA', // 彰化縣
    'O' => 'HSQ', // 新竹市
    'P' => 'YUN', // 雲林縣
    'Q' => 'CYQ', // 嘉義縣
    'T' => 'PIF', // 屏東縣
    'U' => 'HUA', // 花蓮縣
    'V' => 'TTT', // 臺東縣
    'W' => 'KIN', // 金門縣
    'X' => 'PEN', // 澎湖縣
    'Z' => 'LIE', // 連江縣
];

// Open and parse CSV
$file = fopen($csvFile, 'r');
if (!$file) {
    die("Cannot open CSV file: $csvFile\n");
}

// Skip header
fgetcsv($file);

// Process each line
$lineNumber = 2;
while (($data = fgetcsv($file)) !== FALSE) {
    // Skip if not enough columns
    if (count($data) < 8) {
        echo "Warning: Line $lineNumber has insufficient columns, skipping\n";
        $lineNumber++;
        continue;
    }

    // Extract data
    $sectionName = trim($data[0]);
    $subSection = trim($data[1]);
    $sectionCode = trim($data[2]);
    $remarks = trim($data[3]);
    $officeCode = trim($data[4]);
    $countyName = trim($data[5]);
    $districtName = trim($data[6]);
    $officeName = trim($data[7]);

    // Skip cancelled sections
    if ($remarks === '註銷') {
        $lineNumber++;
        continue;
    }

    // Extract county code from office code (first letter)
    $countyCodeLetter = substr($officeCode, 0, 1);
    $countyCode = $countyCodeMap[$countyCodeLetter] ?? $countyCodeLetter;

    // Generate district code (county + district number)
    $districtNumber = substr($officeCode, 1, 2);
    $districtCode = $countyCode . $districtNumber;

    // Add county if not exists
    if (!isset($counties[$countyCode])) {
        $counties[$countyCode] = [
            'code' => $countyCode,
            'name' => $countyName
        ];
    }

    // Add district if not exists
    if (!isset($districts[$districtCode])) {
        $districts[$districtCode] = [
            'code' => $districtCode,
            'name' => $districtName,
            'county_code' => $countyCode
        ];
    }

    // Create section identifier
    $fullSectionName = $sectionName . ($subSection ? $subSection : '');
    $sectionId = $districtCode . '_' . $sectionCode;

    // Add section
    if (!isset($sections[$sectionId])) {
        $sections[$sectionId] = [
            'code' => $sectionCode,
            'name' => $fullSectionName,
            'district_code' => $districtCode,
            'section_name' => $sectionName,
            'sub_section' => $subSection
        ];
    }

    $lineNumber++;
}

fclose($file);

// Sort arrays
ksort($counties);
ksort($districts);
ksort($sections);

// Convert to indexed arrays
$countiesArray = array_values($counties);
$districtsArray = array_values($districts);
$sectionsArray = array_values($sections);

// Generate statistics
$stats = [
    'total_counties' => count($countiesArray),
    'total_districts' => count($districtsArray),
    'total_sections' => count($sectionsArray),
    'processed_at' => date('Y-m-d H:i:s')
];

// Prepare final data structure
$processedData = [
    'stats' => $stats,
    'counties' => $countiesArray,
    'districts' => $districtsArray,
    'sections' => $sectionsArray
];

// Save processed data to JSON file
file_put_contents($outputFile, json_encode($processedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Display statistics
echo "\n=== Processing Complete ===\n";
echo "Total Counties: " . $stats['total_counties'] . "\n";
echo "Total Districts: " . $stats['total_districts'] . "\n";
echo "Total Sections: " . $stats['total_sections'] . "\n";
echo "Output saved to: $outputFile\n";

// Display some examples
echo "\n=== Sample Counties ===\n";
foreach (array_slice($countiesArray, 0, 5) as $county) {
    echo "- {$county['code']}: {$county['name']}\n";
}

echo "\n=== Sample Districts ===\n";
foreach (array_slice($districtsArray, 0, 10) as $district) {
    echo "- {$district['code']}: {$district['name']} (County: {$district['county_code']})\n";
}

echo "\n=== Sample Sections ===\n";
foreach (array_slice($sectionsArray, 0, 10) as $section) {
    echo "- {$section['code']}: {$section['name']} (District: {$section['district_code']})\n";
}

echo "\nProcessing completed successfully!\n";