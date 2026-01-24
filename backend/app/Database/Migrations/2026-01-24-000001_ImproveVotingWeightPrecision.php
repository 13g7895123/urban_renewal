<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 改善投票權重計算精度
 * 
 * 將 land_area_weight 和 building_area_weight 的精度
 * 從 DECIMAL(12,2) 提升到 DECIMAL(20,10)
 * 以支援更精確的面積持分計算
 */
class ImproveVotingWeightPrecision extends Migration
{
    public function up()
    {
        // 修改 voting_records 表的權重欄位精度
        $this->db->query("
            ALTER TABLE voting_records 
            MODIFY COLUMN land_area_weight DECIMAL(20,10) DEFAULT 0.0000000000 COMMENT '土地面積權重',
            MODIFY COLUMN building_area_weight DECIMAL(20,10) DEFAULT 0.0000000000 COMMENT '建物面積權重'
        ");

        // 修改 meeting_eligible_voters 表的權重欄位精度
        $this->db->query("
            ALTER TABLE meeting_eligible_voters 
            MODIFY COLUMN land_area_weight DECIMAL(20,10) DEFAULT 0.0000000000 COMMENT '土地面積權重',
            MODIFY COLUMN building_area_weight DECIMAL(20,10) DEFAULT 0.0000000000 COMMENT '建物面積權重'
        ");

        log_message('info', '投票權重欄位精度已提升至 DECIMAL(20,10)');
    }

    public function down()
    {
        // 還原為原始精度
        $this->db->query("
            ALTER TABLE voting_records 
            MODIFY COLUMN land_area_weight DECIMAL(12,2) DEFAULT 0.00 COMMENT '土地面積權重',
            MODIFY COLUMN building_area_weight DECIMAL(12,2) DEFAULT 0.00 COMMENT '建物面積權重'
        ");

        $this->db->query("
            ALTER TABLE meeting_eligible_voters 
            MODIFY COLUMN land_area_weight DECIMAL(12,2) DEFAULT 0.00 COMMENT '土地面積權重',
            MODIFY COLUMN building_area_weight DECIMAL(12,2) DEFAULT 0.00 COMMENT '建物面積權重'
        ");

        log_message('info', '投票權重欄位精度已還原至 DECIMAL(12,2)');
    }
}
