<?php

namespace App\Models;

use CodeIgniter\Model;

class LandPlotModel extends Model
{
    protected $table = 'land_plots';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'urban_renewal_id',
        'county',
        'district',
        'section',
        'land_number_main',
        'land_number_sub',
        'land_area',
        'is_representative'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'urban_renewal_id' => 'required|integer|greater_than[0]',
        'county' => 'required|min_length[1]|max_length[10]',
        'district' => 'required|min_length[1]|max_length[10]',
        'section' => 'required|min_length[1]|max_length[10]',
        'land_number_main' => 'required|exact_length[4]|regex_match[/^\d{4}$/]',
        'land_number_sub' => 'permit_empty|exact_length[4]|regex_match[/^\d{4}$/]',
        'land_area' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'is_representative' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'urban_renewal_id' => [
            'required' => '更新會ID為必填項目',
            'integer' => '更新會ID必須為整數',
            'greater_than' => '更新會ID必須大於0'
        ],
        'county' => [
            'required' => '縣市為必填項目',
            'min_length' => '縣市代碼格式錯誤',
            'max_length' => '縣市代碼格式錯誤'
        ],
        'district' => [
            'required' => '行政區為必填項目',
            'min_length' => '行政區代碼格式錯誤',
            'max_length' => '行政區代碼格式錯誤'
        ],
        'section' => [
            'required' => '段小段為必填項目',
            'min_length' => '段小段代碼格式錯誤',
            'max_length' => '段小段代碼格式錯誤'
        ],
        'land_number_main' => [
            'required' => '地號母號為必填項目',
            'exact_length' => '地號母號必須為4位數字',
            'regex_match' => '地號母號格式錯誤，請輸入4位數字（例：0001）'
        ],
        'land_number_sub' => [
            'exact_length' => '地號子號必須為4位數字',
            'regex_match' => '地號子號格式錯誤，請輸入4位數字（例：0000）'
        ],
        'land_area' => [
            'decimal' => '土地面積必須為數字',
            'greater_than_equal_to' => '土地面積不能為負數'
        ],
        'is_representative' => [
            'in_list' => '代表號設定值錯誤'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Get land plots by urban renewal ID
     */
    public function getLandPlotsByUrbanRenewal($urbanRenewalId)
    {
        return $this->where('urban_renewal_id', $urbanRenewalId)
                   ->orderBy('is_representative', 'DESC')
                   ->orderBy('created_at', 'ASC')
                   ->findAll();
    }

    /**
     * Get single land plot
     */
    public function getLandPlot($id)
    {
        return $this->find($id);
    }

    /**
     * Create new land plot
     */
    public function createLandPlot($data)
    {
        // Check if this is the first plot for this urban renewal
        $existingCount = $this->where('urban_renewal_id', $data['urban_renewal_id'])->countAllResults();

        // If this is the first plot, make it representative
        if ($existingCount === 0) {
            $data['is_representative'] = 1;
        }

        // If setting as representative, remove representative status from others
        if (isset($data['is_representative']) && $data['is_representative'] == 1) {
            $this->clearRepresentativeStatus($data['urban_renewal_id']);
        }

        return $this->insert($data);
    }

    /**
     * Update land plot
     */
    public function updateLandPlot($id, $data)
    {
        $plot = $this->find($id);
        if (!$plot) {
            return false;
        }

        // If setting as representative, remove representative status from others
        if (isset($data['is_representative']) && $data['is_representative'] == 1) {
            $this->clearRepresentativeStatus($plot['urban_renewal_id']);
        }

        return $this->update($id, $data);
    }

    /**
     * Delete land plot (soft delete)
     */
    public function deleteLandPlot($id)
    {
        $plot = $this->find($id);
        if (!$plot) {
            return false;
        }

        $deleted = $this->delete($id);

        // If deleted plot was representative, set another plot as representative
        if ($deleted && $plot['is_representative'] == 1) {
            $this->assignNewRepresentative($plot['urban_renewal_id']);
        }

        return $deleted;
    }

    /**
     * Set land plot as representative
     */
    public function setAsRepresentative($id)
    {
        $plot = $this->find($id);
        if (!$plot) {
            return false;
        }

        // Clear existing representative status
        $this->clearRepresentativeStatus($plot['urban_renewal_id']);

        // Set this plot as representative
        return $this->update($id, ['is_representative' => 1]);
    }

    /**
     * Clear representative status for all plots in urban renewal
     */
    private function clearRepresentativeStatus($urbanRenewalId)
    {
        return $this->where('urban_renewal_id', $urbanRenewalId)
                   ->set(['is_representative' => 0])
                   ->update();
    }

    /**
     * Assign new representative when current one is deleted
     */
    private function assignNewRepresentative($urbanRenewalId)
    {
        $firstPlot = $this->where('urban_renewal_id', $urbanRenewalId)
                          ->orderBy('created_at', 'ASC')
                          ->first();

        if ($firstPlot) {
            $this->update($firstPlot['id'], ['is_representative' => 1]);
        }
    }

    /**
     * Check if land number already exists for urban renewal
     */
    public function landNumberExists($urbanRenewalId, $county, $district, $section, $landNumberMain, $landNumberSub = null, $excludeId = null)
    {
        $builder = $this->where([
            'urban_renewal_id' => $urbanRenewalId,
            'county' => $county,
            'district' => $district,
            'section' => $section,
            'land_number_main' => $landNumberMain,
            'land_number_sub' => $landNumberSub
        ]);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Get representative land plot for urban renewal
     */
    public function getRepresentativePlot($urbanRenewalId)
    {
        return $this->where([
            'urban_renewal_id' => $urbanRenewalId,
            'is_representative' => 1
        ])->first();
    }

    /**
     * Format land number display
     */
    public function formatLandNumber($plot)
    {
        $landNumber = $plot['land_number_main'];
        if (!empty($plot['land_number_sub'])) {
            $landNumber .= '-' . $plot['land_number_sub'];
        }
        return $landNumber;
    }

    /**
     * Get full formatted land number with location
     */
    public function getFullLandNumber($plot)
    {
        // This would need administrative data to format properly
        // For now, return basic format
        $landNumber = $this->formatLandNumber($plot);
        return $plot['county'] . $plot['district'] . $plot['section'] . $landNumber;
    }
}