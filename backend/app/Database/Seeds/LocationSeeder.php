<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run()
    {
        // Counties data
        $counties = [
            ['code' => 'TPE', 'name' => '臺北市'],
            ['code' => 'KHH', 'name' => '高雄市'],
            ['code' => 'TPH', 'name' => '新北市'],
            ['code' => 'TCH', 'name' => '臺中市'],
            ['code' => 'TNN', 'name' => '臺南市'],
            ['code' => 'TYC', 'name' => '桃園市'],
            ['code' => 'HSC', 'name' => '新竹市'],
            ['code' => 'HSH', 'name' => '新竹縣'],
            ['code' => 'MIA', 'name' => '苗栗縣'],
            ['code' => 'CHA', 'name' => '彰化縣'],
            ['code' => 'NTO', 'name' => '南投縣'],
            ['code' => 'YUN', 'name' => '雲林縣'],
            ['code' => 'CYI', 'name' => '嘉義市'],
            ['code' => 'CYQ', 'name' => '嘉義縣'],
            ['code' => 'PIF', 'name' => '屏東縣'],
            ['code' => 'ILA', 'name' => '宜蘭縣'],
            ['code' => 'HUA', 'name' => '花蓮縣'],
            ['code' => 'TTT', 'name' => '臺東縣'],
            ['code' => 'PEN', 'name' => '澎湖縣'],
            ['code' => 'KIN', 'name' => '金門縣'],
            ['code' => 'LIE', 'name' => '連江縣'],
        ];

        $this->db->table('counties')->insertBatch($counties);

        // Get county IDs for foreign keys
        $taipeiId = $this->db->table('counties')->where('code', 'TPE')->get()->getRow()->id;

        // Districts data for Taipei (sample data - in real implementation, you'd have all districts for all counties)
        $districts = [
            ['county_id' => $taipeiId, 'code' => 'ZS', 'name' => '中山區'],
            ['county_id' => $taipeiId, 'code' => 'DA', 'name' => '大安區'],
            ['county_id' => $taipeiId, 'code' => 'XY', 'name' => '信義區'],
            ['county_id' => $taipeiId, 'code' => 'SS', 'name' => '松山區'],
            ['county_id' => $taipeiId, 'code' => 'WH', 'name' => '萬華區'],
            ['county_id' => $taipeiId, 'code' => 'ZZ', 'name' => '中正區'],
            ['county_id' => $taipeiId, 'code' => 'DT', 'name' => '大同區'],
            ['county_id' => $taipeiId, 'code' => 'SL', 'name' => '士林區'],
            ['county_id' => $taipeiId, 'code' => 'BT', 'name' => '北投區'],
            ['county_id' => $taipeiId, 'code' => 'NH', 'name' => '內湖區'],
            ['county_id' => $taipeiId, 'code' => 'NG', 'name' => '南港區'],
            ['county_id' => $taipeiId, 'code' => 'WS', 'name' => '文山區'],
        ];

        $this->db->table('districts')->insertBatch($districts);

        // Get district ID for sections
        $daanId = $this->db->table('districts')->where('code', 'DA')->where('county_id', $taipeiId)->get()->getRow()->id;

        // Sections data for Da'an District (sample data)
        $sections = [
            ['district_id' => $daanId, 'code' => '001', 'name' => '大安段'],
            ['district_id' => $daanId, 'code' => '002', 'name' => '忠孝段'],
            ['district_id' => $daanId, 'code' => '003', 'name' => '信義段'],
        ];

        $this->db->table('sections')->insertBatch($sections);
    }
}