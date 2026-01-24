<?php

namespace Tests\Unit\Models;

use Tests\Support\ApiTestCase;
use App\Models\MeetingModel;
use App\Models\UrbanRenewalModel;

class MeetingModelTest extends ApiTestCase
{
    protected $meetingModel;
    protected $urbanRenewalModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->meetingModel = new MeetingModel();
        $this->urbanRenewalModel = new UrbanRenewalModel();
    }

    public function testCreateMeetingWithValidData()
    {
        $company = $this->createTestCompany();
        $renewalId = $this->urbanRenewalModel->insert([
            'company_id' => $company['id'],
            'name' => 'Test Renewal',
            'area' => 1000.00,
            'address' => 'Test Address'
        ]);

        $meetingData = [
            'urban_renewal_id' => $renewalId,
            'meeting_type' => '會員大會',
            'meeting_date' => date('Y-m-d', strtotime('+7 days')),
            'meeting_time' => '14:00:00',
            'location' => 'Test Location',
            'status' => 'scheduled',
        ];

        $meetingId = $this->meetingModel->insert($meetingData);
        $this->assertIsInt($meetingId);
        $this->assertGreaterThan(0, $meetingId);

        $meeting = $this->meetingModel->find($meetingId);
        $this->assertEquals('會員大會', $meeting['meeting_type']);
        $this->assertEquals('scheduled', $meeting['status']);
    }

    public function testGetUpcomingMeetings()
    {
        $company = $this->createTestCompany();
        $renewalId = $this->urbanRenewalModel->insert([
            'company_id' => $company['id'],
            'name' => 'Test Renewal',
            'area' => 1000.00,
            'address' => 'Test Address'
        ]);

        // 建立未來會議
        $this->meetingModel->insert([
            'urban_renewal_id' => $renewalId,
            'meeting_type' => '會員大會',
            'meeting_date' => date('Y-m-d', strtotime('+7 days')),
            'status' => 'scheduled',
        ]);

        // 建立過去會議
        $this->meetingModel->insert([
            'urban_renewal_id' => $renewalId,
            'meeting_type' => '會員大會',
            'meeting_date' => date('Y-m-d', strtotime('-7 days')),
            'status' => 'completed',
        ]);

        $upcoming = $this->meetingModel
            ->where('meeting_date >=', date('Y-m-d'))
            ->findAll();

        $this->assertGreaterThanOrEqual(1, count($upcoming));
    }

    public function testUpdateMeetingStatus()
    {
        $company = $this->createTestCompany();
        $renewalId = $this->urbanRenewalModel->insert([
            'company_id' => $company['id'],
            'name' => 'Test Renewal',
            'area' => 1000.00,
            'address' => 'Test Address'
        ]);

        $meetingId = $this->meetingModel->insert([
            'urban_renewal_id' => $renewalId,
            'meeting_type' => '會員大會',
            'meeting_date' => date('Y-m-d'),
            'status' => 'scheduled',
        ]);

        $updated = $this->meetingModel->update($meetingId, [
            'status' => 'in_progress'
        ]);

        $this->assertTrue($updated);

        $meeting = $this->meetingModel->find($meetingId);
        $this->assertEquals('in_progress', $meeting['status']);
    }
}
