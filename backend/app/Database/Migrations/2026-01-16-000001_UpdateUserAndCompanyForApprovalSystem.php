<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateUserAndCompanyForApprovalSystem extends Migration
{
    public function up()
    {
        // 1. 修改 users 表
        $userFields = [
            'company_invite_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => '公司邀請編號（個人用戶註冊時輸入）',
                'after'      => 'company_id',
            ],
            'approval_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default'    => 'approved', // 預設為 approved 是為了相容現有已存在的用戶
                'null'       => false,
                'comment'    => '審核狀態：待審核/已通過/已拒絕',
                'after'      => 'company_invite_code',
            ],
            'approved_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => '審核通過時間',
                'after'   => 'approval_status',
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '審核者 ID（公司管理者）',
                'after'      => 'approved_at',
            ],
            'is_substantive' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1, // 預設為 1 是為了相容現有用戶
                'null'       => false,
                'comment'    => '是否為實質性帳號（可被指派管理更新會）',
                'after'      => 'approved_by',
            ],
        ];
        $this->forge->addColumn('users', $userFields);

        // 2. 修改 companies 表
        $companyFields = [
            'invite_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => '公司邀請碼（供個人用戶註冊時使用）',
                'after'      => 'company_phone',
            ],
            'invite_code_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '邀請碼是否啟用',
                'after'      => 'invite_code',
            ],
        ];
        $this->forge->addColumn('companies', $companyFields);

        // 為邀請碼增加唯一索引（MariaDB/MySQL 中 NULL 值不計入唯一性限制，可有多個 NULL）
        $this->db->query("CREATE UNIQUE INDEX idx_company_invite_code ON companies(invite_code)");
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['company_invite_code', 'approval_status', 'approved_at', 'approved_by', 'is_substantive']);
        $this->forge->dropColumn('companies', ['invite_code', 'invite_code_active']);
    }
}
