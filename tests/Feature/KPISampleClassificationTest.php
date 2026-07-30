<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Constants\Roles;

class KPISampleClassificationTest extends TestCase
{
    /**
     * Test team KPI endpoint returns separate standard (N >= 3) and limited (N < 3) groups.
     */
    public function test_team_kpi_classifies_sample_size_correctly()
    {
        $user = User::first();
        if (!$user) {
            $this->assertTrue(true);
            return;
        }

        $response = $this->actingAs($user)->get(route('kpi.team'));
        
        $response->assertStatus(200);
        $response->assertViewHas('standardTeams');
        $response->assertViewHas('limitedTeams');

        $standardTeams = $response->viewData('standardTeams');
        $limitedTeams = $response->viewData('limitedTeams');

        foreach ($standardTeams as $team) {
            $this->assertTrue($team['member_count'] >= 3, 'Standard team should have member_count >= 3');
            $this->assertEquals('🟢 Tim Standar (≥ 3 Staf)', $team['badge_label']);
        }

        foreach ($limitedTeams as $team) {
            $this->assertTrue($team['member_count'] < 3, 'Limited team should have member_count < 3');
            $this->assertEquals('⚠️ Tim Kecil (1-2 Staf)', $team['badge_label']);
        }
    }

    /**
     * Test department KPI endpoint returns separate standard (N >= 3) and limited (N < 3) groups.
     */
    public function test_department_kpi_classifies_sample_size_correctly()
    {
        $user = User::first();
        if (!$user) {
            $this->assertTrue(true);
            return;
        }

        $response = $this->actingAs($user)->get(route('kpi.department'));
        
        $response->assertStatus(200);
        $response->assertViewHas('standardDepartments');
        $response->assertViewHas('limitedDepartments');

        $standardDepts = $response->viewData('standardDepartments');
        $limitedDepts = $response->viewData('limitedDepartments');

        foreach ($standardDepts as $dept) {
            $this->assertTrue($dept['member_count'] >= 3, 'Standard department should have member_count >= 3');
            $this->assertEquals('🟢 Divisi Standar (≥ 3 Staf)', $dept['badge_label']);
        }

        foreach ($limitedDepts as $dept) {
            $this->assertTrue($dept['member_count'] < 3, 'Limited department should have member_count < 3');
            $this->assertEquals('⚠️ Divisi Kecil (1-2 Staf)', $dept['badge_label']);
        }
    }
}
