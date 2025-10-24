<?php

namespace Tests\Support\Fixtures;

use App\Models\UrbanRenewalModel;

/**
 * Urban Renewal Test Fixture
 * 提供測試用更新地區資料
 */
class UrbanRenewalFixture
{
    protected $urbanRenewalModel;
    protected $createdUrbanRenewals = [];

    public function __construct()
    {
        $this->urbanRenewalModel = new UrbanRenewalModel();
    }

    /**
     * 建立基本的更新地區
     */
    public function create(array $overrides = []): array
    {
        $timestamp = time();
        $random = rand(1000, 9999);

        $data = array_merge([
            'area_name' => "測試更新地區_{$timestamp}_{$random}",
            'location' => '台北市中正區',
            'status' => 'active',
            'total_area' => 5000.50,
            'land_count' => 50,
            'building_count' => 30,
            'owner_count' => 80,
            'description' => '這是一個測試用的都市更新地區',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], $overrides);

        $id = $this->urbanRenewalModel->insert($data);
        $urbanRenewal = $this->urbanRenewalModel->find($id);
        $this->createdUrbanRenewals[] = $id;

        return $urbanRenewal;
    }

    /**
     * 建立活動中的更新地區
     */
    public function createActive(array $overrides = []): array
    {
        return $this->create(array_merge([
            'status' => 'active',
            'area_name' => '活動中的更新地區_' . time()
        ], $overrides));
    }

    /**
     * 建立計畫中的更新地區
     */
    public function createPlanning(array $overrides = []): array
    {
        return $this->create(array_merge([
            'status' => 'planning',
            'area_name' => '計畫中的更新地區_' . time()
        ], $overrides));
    }

    /**
     * 建立已完成的更新地區
     */
    public function createCompleted(array $overrides = []): array
    {
        return $this->create(array_merge([
            'status' => 'completed',
            'area_name' => '已完成的更新地區_' . time()
        ], $overrides));
    }

    /**
     * 建立已暫停的更新地區
     */
    public function createSuspended(array $overrides = []): array
    {
        return $this->create(array_merge([
            'status' => 'suspended',
            'area_name' => '已暫停的更新地區_' . time()
        ], $overrides));
    }

    /**
     * 建立多個不同狀態的更新地區
     */
    public function createMultipleWithStatuses(): array
    {
        return [
            'active' => $this->createActive(),
            'planning' => $this->createPlanning(),
            'completed' => $this->createCompleted(),
            'suspended' => $this->createSuspended()
        ];
    }

    /**
     * 建立指定數量的更新地區
     */
    public function createMany(int $count, array $overrides = []): array
    {
        $urbanRenewals = [];

        for ($i = 0; $i < $count; $i++) {
            $urbanRenewals[] = $this->create(array_merge([
                'area_name' => "測試更新地區_{$i}_" . time()
            ], $overrides));
        }

        return $urbanRenewals;
    }

    /**
     * 建立不同地區的更新地區
     */
    public function createInDifferentLocations(): array
    {
        $locations = [
            '台北市中正區',
            '台北市大安區',
            '台北市信義區',
            '新北市板橋區',
            '新北市新店區'
        ];

        $urbanRenewals = [];

        foreach ($locations as $location) {
            $urbanRenewals[$location] = $this->create([
                'location' => $location,
                'area_name' => "{$location}更新地區_" . time()
            ]);
        }

        return $urbanRenewals;
    }

    /**
     * 建立包含詳細資訊的更新地區
     */
    public function createWithDetails(array $overrides = []): array
    {
        return $this->create(array_merge([
            'area_name' => '詳細資訊更新地區_' . time(),
            'location' => '台北市中正區重慶南路一段122號',
            'total_area' => 10500.75,
            'land_count' => 100,
            'building_count' => 65,
            'owner_count' => 150,
            'description' => '這是一個包含完整詳細資訊的都市更新地區，用於詳細測試各項功能。'
        ], $overrides));
    }

    /**
     * 建立測試場景
     * 返回2個不同的更新地區用於測試跨區存取
     */
    public function createCrossAccessScenario(): array
    {
        return [
            'urban_renewal_a' => $this->create([
                'area_name' => '更新地區A_' . time(),
                'location' => '台北市中正區'
            ]),
            'urban_renewal_b' => $this->create([
                'area_name' => '更新地區B_' . time(),
                'location' => '台北市大安區'
            ])
        ];
    }

    /**
     * 取得已建立的更新地區 ID 列表
     */
    public function getCreatedIds(): array
    {
        return $this->createdUrbanRenewals;
    }

    /**
     * 清理所有建立的測試更新地區
     */
    public function cleanup(): void
    {
        foreach ($this->createdUrbanRenewals as $id) {
            $this->urbanRenewalModel->delete($id);
        }
        $this->createdUrbanRenewals = [];
    }

    /**
     * Destructor - 自動清理
     */
    public function __destruct()
    {
        $this->cleanup();
    }
}
