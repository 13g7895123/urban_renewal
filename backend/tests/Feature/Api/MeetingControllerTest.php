<?php

namespace Tests\Feature\Api;

use Tests\Support\ApiTestCase;
use App\Models\MeetingModel;
use App\Models\UrbanRenewalModel;

class MeetingControllerTest extends ApiTestCase
{
    protected $meetingModel;
    protected $urbanRenewalModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->meetingModel = new MeetingModel();
        $this->urbanRenewalModel = new UrbanRenewalModel();
    }

    public function testAdminCanListAllMeetings()
    {
        $admin = $this->createAdminUser();
        
        $result = $this->authenticatedGet('/api/meetings', $admin);
        
        $result->assertStatus(200);
        $result->assertJSONFragment([
            'success' => true
        ]);
    }

    public function testCompanyManagerCanListTheirMeetings()
    {
        $company = $this->createTestCompany();
        $manager = $this->createCompanyManager($company['id']);
        
        $result = $this->authenticatedGet('/api/meetings', $manager);
        
        $result->assertStatus(200);
    }

    public function testGuestCannotListMeetings()
    {
        $result = $this->get('/api/meetings');
        
        $result->assertStatus(401);
    }

    public function testAdminCanCreateMeeting()
    {
        $admin = $this->createAdminUser();
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

        $result = $this->authenticatedPost('/api/meetings', $meetingData, $admin);
        
        $result->assertStatus(200);
        $result->assertJSONFragment([
            'success' => true
        ]);
    }

    public function testMemberCannotCreateMeeting()
    {
        $member = $this->createTestUser(['role' => 'member']);
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
        ];

        $result = $this->authenticatedPost('/api/meetings', $meetingData, $member);
        
        $result->assertStatus(403);
    }

    public function testAdminCanGetMeetingDetails()
    {
        $admin = $this->createAdminUser();
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

        $result = $this->authenticatedGet("/api/meetings/{$meetingId}", $admin);
        
        $result->assertStatus(200);
        $data = json_decode($result->getJSON(), true);
        $this->assertEquals($meetingId, $data['data']['meeting']['id']);
    }

    public function testAdminCanUpdateMeeting()
    {
        $admin = $this->createAdminUser();
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

        $updateData = [
            'status' => 'in_progress',
            'location' => 'Updated Location'
        ];

        $result = $this->authenticatedPut("/api/meetings/{$meetingId}", $updateData, $admin);
        
        $result->assertStatus(200);
        
        $updated = $this->meetingModel->find($meetingId);
        $this->assertEquals('in_progress', $updated['status']);
    }

    public function testAdminCanDeleteMeeting()
    {
        $admin = $this->createAdminUser();
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

        $result = $this->authenticatedDelete("/api/meetings/{$meetingId}", $admin);
        
        $result->assertStatus(200);
        
        $deleted = $this->meetingModel->find($meetingId);
        $this->assertNull($deleted);
    }
}
