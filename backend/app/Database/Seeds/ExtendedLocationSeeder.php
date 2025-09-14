<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ExtendedLocationSeeder extends Seeder
{
    public function run()
    {
        // Get county IDs for major cities
        $taipeiId = $this->db->table('counties')->where('code', 'TPE')->get()->getRow()->id;
        $kaohsiungId = $this->db->table('counties')->where('code', 'KHH')->get()->getRow()->id;
        $newTaipeiId = $this->db->table('counties')->where('code', 'TPH')->get()->getRow()->id;
        $taichungId = $this->db->table('counties')->where('code', 'TCH')->get()->getRow()->id;
        $tainanId = $this->db->table('counties')->where('code', 'TNN')->get()->getRow()->id;

        // Add districts for major cities
        $districts = [
            // Kaohsiung districts
            ['county_id' => $kaohsiungId, 'code' => 'XZ', 'name' => '新興區'],
            ['county_id' => $kaohsiungId, 'code' => 'QJ', 'name' => '前金區'],
            ['county_id' => $kaohsiungId, 'code' => 'LY', 'name' => '苓雅區'],
            ['county_id' => $kaohsiungId, 'code' => 'YC', 'name' => '鹽埕區'],
            ['county_id' => $kaohsiungId, 'code' => 'GS', 'name' => '鼓山區'],

            // New Taipei districts (sample)
            ['county_id' => $newTaipeiId, 'code' => 'BL', 'name' => '板橋區'],
            ['county_id' => $newTaipeiId, 'code' => 'SZ', 'name' => '三重區'],
            ['county_id' => $newTaipeiId, 'code' => 'ZH', 'name' => '中和區'],
            ['county_id' => $newTaipeiId, 'code' => 'YH', 'name' => '永和區'],
            ['county_id' => $newTaipeiId, 'code' => 'XD', 'name' => '新店區'],

            // Taichung districts (sample)
            ['county_id' => $taichungId, 'code' => 'ZQ', 'name' => '中區'],
            ['county_id' => $taichungId, 'code' => 'DQ', 'name' => '東區'],
            ['county_id' => $taichungId, 'code' => 'NQ', 'name' => '南區'],
            ['county_id' => $taichungId, 'code' => 'XQ', 'name' => '西區'],
            ['county_id' => $taichungId, 'code' => 'BQ', 'name' => '北區'],

            // Tainan districts (sample)
            ['county_id' => $tainanId, 'code' => 'ZXQ', 'name' => '中西區'],
            ['county_id' => $tainanId, 'code' => 'APN', 'name' => '安平區'],
            ['county_id' => $tainanId, 'code' => 'ANN', 'name' => '安南區'],
            ['county_id' => $tainanId, 'code' => 'DQ2', 'name' => '東區'],
            ['county_id' => $tainanId, 'code' => 'NQ2', 'name' => '南區'],
        ];

        $this->db->table('districts')->insertBatch($districts);

        // Add sections for some districts
        // Get district IDs
        $daanId = $this->db->table('districts')->where('code', 'DA')->where('county_id', $taipeiId)->get()->getRow()->id;
        $zhongshanId = $this->db->table('districts')->where('code', 'ZS')->where('county_id', $taipeiId)->get()->getRow()->id;
        $xinyingId = $this->db->table('districts')->where('code', 'XZ')->where('county_id', $kaohsiungId)->get()->getRow()->id;
        $banliaoId = $this->db->table('districts')->where('code', 'BL')->where('county_id', $newTaipeiId)->get()->getRow()->id;

        // Add more sections
        $sections = [
            // Zhongshan District sections
            ['district_id' => $zhongshanId, 'code' => '001', 'name' => '中山段'],
            ['district_id' => $zhongshanId, 'code' => '002', 'name' => '長安段'],
            ['district_id' => $zhongshanId, 'code' => '003', 'name' => '民權段'],

            // Kaohsiung Xinying District sections
            ['district_id' => $xinyingId, 'code' => '001', 'name' => '新興段'],
            ['district_id' => $xinyingId, 'code' => '002', 'name' => '民生段'],
            ['district_id' => $xinyingId, 'code' => '003', 'name' => '復興段'],

            // New Taipei Banliao District sections
            ['district_id' => $banliaoId, 'code' => '001', 'name' => '板橋段'],
            ['district_id' => $banliaoId, 'code' => '002', 'name' => '埔墘段'],
            ['district_id' => $banliaoId, 'code' => '003', 'name' => '江子翠段'],
        ];

        $this->db->table('sections')->insertBatch($sections);
    }
}