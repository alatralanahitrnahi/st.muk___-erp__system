<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\WorkflowService;
use App\Models\Student;
use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class DepartmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private $workflowService;
    private $department;
    private $hod;
    private $principal;
    private $registrar;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->workflowService = new WorkflowService();
        
        $this->department = Department::create(['name' => 'Computer Science']);
        
        $this->hod = User::create([
            'name' => 'HOD User',
            'email' => 'hod@test.com',
            'password' => bcrypt('password'),
            'role' => 'faculty'
        ]);
        
        $this->principal = User::create([
            'name' => 'Principal User',
            'email' => 'principal@test.com',
            'password' => bcrypt('password'),
            'role' => 'principal'
        ]);
        
        $this->registrar = User::create([
            'name' => 'Registrar User',
            'email' => 'registrar@test.com',
            'password' => bcrypt('password'),
            'role' => 'registrar'
        ]);

        $this->department->update(['department_head_id' => $this->hod->id]);
        
        DB::table('user_departments')->insert([
            ['user_id' => $this->hod->id, 'department_id' => $this->department->id, 'role_in_department' => 'department_head'],
            ['user_id' => $this->principal->id, 'department_id' => $this->department->id, 'role_in_department' => 'principal'],
            ['user_id' => $this->registrar->id, 'department_id' => $this->department->id, 'role_in_department' => 'registrar']
        ]);
    }

    public function test_student_admission_workflow_complete_chain()
    {
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'program_id' => 1,
            'department_id' => $this->department->id,
            'workflow_state' => 'pending'
        ]);

        $result = $this->workflowService->transition(
            $student,
            'registrar_review',
            $this->registrar,
            $this->department->id,
            'Documents verified'
        );
        $this->assertTrue($result);
        $this->assertEquals('registrar_review', $student->fresh()->workflow_state);

        $result = $this->workflowService->transition(
            $student,
            'hod_approved',
            $this->hod,
            $this->department->id,
            'Department capacity available'
        );
        $this->assertTrue($result);
        $this->assertEquals('hod_approved', $student->fresh()->workflow_state);

        $result = $this->workflowService->transition(
            $student,
            'principal_approved',
            $this->principal,
            $this->department->id,
            'Final approval granted'
        );
        $this->assertTrue($result);
        $this->assertEquals('principal_approved', $student->fresh()->workflow_state);

        $history = DB::table('workflow_history')
            ->where('entity_type', get_class($student))
            ->where('entity_id', $student->id)
            ->where('department_id', $this->department->id)
            ->count();
        $this->assertEquals(3, $history);
    }

    public function test_fee_waiver_conditional_logic_small_amount()
    {
        $feeWaiver = (object)[
            'id' => 1,
            'student_id' => 1,
            'amount' => 3000,
            'department_id' => $this->department->id,
            'workflow_state' => 'requested'
        ];

        $feeWaiver->getWorkflowState = fn() => $feeWaiver->workflow_state;
        $feeWaiver->setWorkflowState = function($state) use ($feeWaiver) {
            $feeWaiver->workflow_state = $state;
        };
        $feeWaiver->save = fn() => true;

        $result = $this->workflowService->transition(
            $feeWaiver,
            'registrar_review',
            $this->registrar,
            $this->department->id,
            'Small waiver approved',
            ['amount' => 3000]
        );

        $this->assertTrue($result);
        $this->assertEquals('principal_approved', $feeWaiver->workflow_state);
    }

    public function test_unauthorized_user_cannot_approve()
    {
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'program_id' => 1,
            'department_id' => $this->department->id,
            'workflow_state' => 'pending'
        ]);

        $unauthorizedUser = User::create([
            'name' => 'Unauthorized',
            'email' => 'unauth@test.com',
            'password' => bcrypt('password'),
            'role' => 'student'
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User not authorized');

        $this->workflowService->transition(
            $student,
            'registrar_review',
            $unauthorizedUser,
            $this->department->id
        );
    }

    public function test_workflow_requires_department_context()
    {
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'program_id' => 1,
            'workflow_state' => 'pending'
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Department context required');

        $this->workflowService->transition(
            $student,
            'registrar_review',
            $this->registrar,
            null
        );
    }

    public function test_audit_trail_includes_department_context()
    {
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'program_id' => 1,
            'department_id' => $this->department->id,
            'workflow_state' => 'pending'
        ]);

        $this->workflowService->transition(
            $student,
            'registrar_review',
            $this->registrar,
            $this->department->id,
            'Test transition'
        );

        $workflowHistory = DB::table('workflow_history')
            ->where('entity_id', $student->id)
            ->first();

        $this->assertNotNull($workflowHistory);
        $this->assertEquals($this->department->id, $workflowHistory->department_id);
        $this->assertEquals($this->registrar->id, $workflowHistory->performed_by);

        $auditLog = DB::table('audit_logs')
            ->where('entity_id', $student->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($this->department->id, $auditLog->department_id);
    }

    public function test_hod_validation_checks_department_head_assignment()
    {
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'program_id' => 1,
            'department_id' => $this->department->id,
            'workflow_state' => 'registrar_review'
        ]);

        $result = $this->workflowService->transition(
            $student,
            'hod_approved',
            $this->hod,
            $this->department->id
        );

        $this->assertTrue($result);

        $otherFaculty = User::create([
            'name' => 'Other Faculty',
            'email' => 'faculty@test.com',
            'password' => bcrypt('password'),
            'role' => 'faculty'
        ]);

        $student2 = Student::create([
            'user_id' => User::factory()->create()->id,
            'program_id' => 1,
            'department_id' => $this->department->id,
            'workflow_state' => 'registrar_review'
        ]);

        $this->expectException(\Exception::class);
        $this->workflowService->transition(
            $student2,
            'hod_approved',
            $otherFaculty,
            $this->department->id
        );
    }

    public function test_principal_can_view_complete_workflow_history()
    {
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'program_id' => 1,
            'department_id' => $this->department->id,
            'workflow_state' => 'pending'
        ]);

        $this->workflowService->transition($student, 'registrar_review', $this->registrar, $this->department->id);
        $this->workflowService->transition($student, 'hod_approved', $this->hod, $this->department->id);
        $this->workflowService->transition($student, 'principal_approved', $this->principal, $this->department->id);

        $history = $this->workflowService->getWorkflowHistory($student, $this->department->id);

        $this->assertCount(3, $history);
        $this->assertEquals('principal_approved', $history[0]->to_state);
        $this->assertEquals('hod_approved', $history[1]->to_state);
        $this->assertEquals('registrar_review', $history[2]->to_state);
    }
}
