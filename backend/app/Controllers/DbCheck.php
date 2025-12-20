<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\PropertyOwnerModel;

class DbCheck extends Controller
{
    public function index()
    {
        $model = new PropertyOwnerModel();
        $owners = $model->select('id, urban_renewal_id, owner_code')->orderBy('id', 'ASC')->findAll();

        echo "Total Owners: " . count($owners) . "\n";
        echo "ID | RenewalID | OwnerCode\n";
        echo "---|-----------|----------\n";
        foreach ($owners as $owner) {
            echo "{$owner['id']} | {$owner['urban_renewal_id']} | {$owner['owner_code']}\n";
        }
    }
}
