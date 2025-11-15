<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConvertCompanyUrbanRenewalToOneToMany extends Migration
{
    public function up()
    {
        $db = $this->db;

        try {
            // Step 1: Add company_id column to urban_renewals table if not exists
            if (!$db->fieldExists('company_id', 'urban_renewals')) {
                $this->forge->addColumn('urban_renewals', [
                    'company_id' => [
                        'type' => 'INT',
                        'constraint' => 10,
                        'unsigned' => true,
                        'null' => true,
                        'comment' => '所屬企業ID (一對多關係)',
                        'after' => 'id'
                    ]
                ]);
            }

            // Step 2: Migrate data from companies.urban_renewal_id to urban_renewals.company_id
            if ($db->fieldExists('urban_renewal_id', 'companies')) {
                $db->query(<<<SQL
                    UPDATE urban_renewals ur
                    SET ur.company_id = (
                        SELECT c.id 
                        FROM companies c 
                        WHERE c.urban_renewal_id = ur.id 
                        LIMIT 1
                    )
                    WHERE ur.company_id IS NULL AND ur.id IN (
                        SELECT id FROM urban_renewals WHERE company_id IS NULL
                    )
                SQL);

                // Step 3: Try to drop old foreign key (if exists)
                try {
                    $this->forge->dropForeignKey('companies', 'companies_urban_renewal_id_foreign');
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                    log_message('info', 'Foreign key companies_urban_renewal_id_foreign not found, continuing...');
                }

                // Step 4: Drop old constraints and indexes from companies table
                try {
                    $this->forge->dropKey('companies', 'unique_urban_renewal_id');
                } catch (\Exception $e) {
                    log_message('info', 'Unique key not found, continuing...');
                }

                try {
                    $this->forge->dropKey('companies', 'idx_urban_renewal_id');
                } catch (\Exception $e) {
                    log_message('info', 'Index not found, continuing...');
                }

                // Step 5: Drop urban_renewal_id column from companies table
                if ($db->fieldExists('urban_renewal_id', 'companies')) {
                    $this->forge->dropColumn('companies', 'urban_renewal_id');
                }
            }

            // Step 6: Add foreign key constraint to urban_renewals.company_id
            // (Skip index creation via forge, we'll let forge handle it with addForeignKey)

            // Only add foreign key if it doesn't exist already
            try {
                $this->forge->addForeignKey('company_id', 'companies', 'id', 'fk_urban_renewals_company_id', 'SET NULL', 'CASCADE');
            } catch (\Exception $e) {
                log_message('info', 'Foreign key might already exist, continuing...');
            }

        } catch (\Exception $e) {
            log_message('error', 'Migration error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down()
    {
        $db = $this->db;

        try {
            // Reverse: Add urban_renewal_id back to companies table
            if (!$db->fieldExists('urban_renewal_id', 'companies')) {
                $this->forge->addColumn('companies', [
                    'urban_renewal_id' => [
                        'type' => 'INT',
                        'constraint' => 10,
                        'unsigned' => true,
                        'null' => true,
                        'comment' => '對應的更新會ID (一對一關係)',
                        'after' => 'id'
                    ]
                ]);
            }

            // Migrate data back
            $db->query(<<<SQL
                UPDATE companies c
                SET c.urban_renewal_id = (
                    SELECT ur.id
                    FROM urban_renewals ur
                    WHERE ur.company_id = c.id
                    LIMIT 1
                )
                WHERE c.urban_renewal_id IS NULL
            SQL);

            // Re-add indexes
            try {
                $this->forge->addKey('urban_renewal_id', false, false, 'idx_urban_renewal_id');
            } catch (\Exception $e) {
                log_message('info', 'Index might already exist, continuing...');
            }

            try {
                $this->forge->addUniqueKey('urban_renewal_id', 'unique_urban_renewal_id');
            } catch (\Exception $e) {
                log_message('info', 'Unique key might already exist, continuing...');
            }

            // Re-add foreign key
            try {
                $this->forge->addForeignKey('urban_renewal_id', 'urban_renewals', 'id', 'fk_companies_urban_renewal_id', 'CASCADE', 'CASCADE');
            } catch (\Exception $e) {
                log_message('info', 'Foreign key might already exist, continuing...');
            }

            // Drop new constraints from urban_renewals
            try {
                $this->forge->dropForeignKey('urban_renewals', 'fk_urban_renewals_company_id');
            } catch (\Exception $e) {
                log_message('info', 'Foreign key not found, continuing...');
            }

            try {
                $this->forge->dropKey('urban_renewals', 'idx_company_id');
            } catch (\Exception $e) {
                log_message('info', 'Index not found, continuing...');
            }

            // Drop company_id column
            if ($db->fieldExists('company_id', 'urban_renewals')) {
                $this->forge->dropColumn('urban_renewals', 'company_id');
            }

        } catch (\Exception $e) {
            log_message('error', 'Rollback error: ' . $e->getMessage());
            throw $e;
        }
    }
}
