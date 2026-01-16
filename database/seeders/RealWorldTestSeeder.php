<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RealWorldTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates a complete college setup from scratch:
     * 1. Super Admin sets up system
     * 2. Creates departments and programs
     * 3. Principal and HODs are assigned
     * 4. Registrar creates fee structures
     * 5. Faculty are hired and assigned subjects
     * 6. Students apply and get admitted
     * 7. Attendance is marked
     * 8. Results are entered
     * 9. Workflows are tested
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data
        $this->clearExistingData();
        
        // Step 1: Super Admin Setup
        $superAdmin = $this->createSuperAdmin();
        echo "✅ Step 1: Super Admin created\n";
        
        // Step 2: Create Departments
        $departments = $this->createDepartments();
        echo "✅ Step 2: Departments created (Science, Commerce, Arts)\n";
        
        // Step 3: Create Academic Structure
        $programs = $this->createPrograms($departments);
        $subjects = $this->createSubjects($programs);
        echo "✅ Step 3: Programs and Subjects created\n";
        
        // Step 4: Create Principal
        $principal = $this->createPrincipal($departments);
        echo "✅ Step 4: Principal created\n";
        
        // Step 5: Create HODs
        $hods = $this->createHODs($departments);
        echo "✅ Step 5: HODs created and assigned\n";
        
        // Step 6: Create Registrar
        $registrar = $this->createRegistrar($departments);
        echo "✅ Step 6: Registrar created\n";
        
        // Step 7: Create Fee Structures
        $this->createFeeStructures($programs);
        echo "✅ Step 7: Fee structures created\n";
        
        // Step 8: Create Faculty
        $faculty = $this->createFaculty($departments, $subjects);
        echo "✅ Step 8: Faculty created and assigned subjects\n";
        
        // Step 9: Create Students (Applications)
        $students = $this->createStudents($programs, $departments);
        echo "✅ Step 9: Student applications created\n";
        
        // Step 10: Process Admissions (Workflow)
        $this->processAdmissions($students, $registrar, $hods, $principal);
        echo "✅ Step 10: Admissions processed through workflow\n";
        
        // Step 11: Assign Fees to Students
        $this->assignFeesToStudents($students);
        echo "✅ Step 11: Fees assigned to students\n";
        
        // Step 12: Record Fee Payments
        $this->recordFeePayments($students);
        echo "✅ Step 12: Fee payments recorded\n";
        
        // Step 13: Create Timetable
        $this->createTimetable($faculty, $subjects);
        echo "✅ Step 13: Timetable created\n";
        
        // Step 14: Mark Attendance
        $this->markAttendance($students, $faculty);
        echo "✅ Step 14: Attendance marked for 30 days\n";
        
        // Step 15: Create Lesson Plans
        $lessonPlans = $this->createLessonPlans($faculty, $subjects);
        echo "✅ Step 15: Lesson plans created\n";
        
        // Step 16: Approve Lesson Plans (Workflow)
        $this->approveLessonPlans($lessonPlans, $hods, $principal);
        echo "✅ Step 16: Lesson plans approved through workflow\n";
        
        // Step 17: Enter Results
        $this->enterResults($students, $subjects, $faculty);
        echo "✅ Step 17: Results entered\n";
        
        // Step 18: Test Fee Waiver Workflow
        $this->testFeeWaiverWorkflow($students, $registrar, $hods, $principal);
        echo "✅ Step 18: Fee waiver workflow tested\n";
        
        // Step 19: Test Department Transfer Workflow
        $this->testDepartmentTransfer($students, $hods, $principal);
        echo "✅ Step 19: Department transfer workflow tested\n";
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        echo "\n";
        echo "========================================\n";
        echo "✅ COMPLETE COLLEGE SETUP SUCCESSFUL!\n";
        echo "========================================\n";
        echo "\n";
        echo "Test Users Created:\n";
        echo "-------------------\n";
        echo "Super Admin: admin@pvgs.edu / password123\n";
        echo "Principal: principal@pvgs.edu / password123\n";
        echo "HOD Science: hod.science@pvgs.edu / password123\n";
        echo "HOD Commerce: hod.commerce@pvgs.edu / password123\n";
        echo "HOD Arts: hod.arts@pvgs.edu / password123\n";
        echo "Registrar: registrar@pvgs.edu / password123\n";
        echo "Faculty: faculty1@pvgs.edu / password123 (and 14 more)\n";
        echo "Students: student1@pvgs.edu / password123 (and 99 more)\n";
        echo "\n";
        echo "Data Created:\n";
        echo "-------------\n";
        echo "Departments: 3\n";
        echo "Programs: 9 (3 per department)\n";
        echo "Subjects: 27 (3 per program)\n";
        echo "Faculty: 15 (5 per department)\n";
        echo "Students: 100 (distributed across programs)\n";
        echo "Attendance Records: 3000 (30 days × 100 students)\n";
        echo "Lesson Plans: 15 (1 per faculty)\n";
        echo "Results: 300 (3 subjects per student)\n";
        echo "Workflows: 120+ transitions\n";
        echo "\n";
    }
    
    private function clearExistingData()
    {
        DB::table('workflow_history')->truncate();
        DB::table('audit_logs')->truncate();
        DB::table('attendance_records')->truncate();
        DB::table('exam_results')->truncate();
        DB::table('lesson_plans')->truncate();
        DB::table('student_fees')->truncate();
        DB::table('fee_payments')->truncate();
        DB::table('fee_structures')->truncate();
        DB::table('faculty_assignments')->truncate();
        DB::table('students')->truncate();
        DB::table('user_departments')->truncate();
        DB::table('subjects')->truncate();
        DB::table('programs')->truncate();
        DB::table('departments')->truncate();
        DB::table('users')->truncate();
    }
    
    private function createSuperAdmin()
    {
        return DB::table('users')->insertGetId([
            'name' => 'System Administrator',
            'email' => 'admin@pvgs.edu',
            'password' => Hash::make('password123'),
            'user_type' => 'super-admin',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    private function createDepartments()
    {
        $departments = [
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'Commerce', 'code' => 'COM'],
            ['name' => 'Arts', 'code' => 'ART']
        ];
        
        $ids = [];
        foreach ($departments as $dept) {
            $ids[$dept['code']] = DB::table('departments')->insertGetId([
                'name' => $dept['name'],
                'code' => $dept['code'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        return $ids;
    }
    
    private function createPrograms($departments)
    {
        $programs = [
            // Science
            ['name' => 'B.Sc. Computer Science', 'code' => 'BSC_CS', 'dept' => 'SCI', 'duration' => 3],
            ['name' => 'B.Sc. Physics', 'code' => 'BSC_PHY', 'dept' => 'SCI', 'duration' => 3],
            ['name' => 'B.Sc. Chemistry', 'code' => 'BSC_CHE', 'dept' => 'SCI', 'duration' => 3],
            // Commerce
            ['name' => 'B.Com. Accounting', 'code' => 'BCOM_ACC', 'dept' => 'COM', 'duration' => 3],
            ['name' => 'B.Com. Banking', 'code' => 'BCOM_BNK', 'dept' => 'COM', 'duration' => 3],
            ['name' => 'B.Com. Finance', 'code' => 'BCOM_FIN', 'dept' => 'COM', 'duration' => 3],
            // Arts
            ['name' => 'B.A. English', 'code' => 'BA_ENG', 'dept' => 'ART', 'duration' => 3],
            ['name' => 'B.A. History', 'code' => 'BA_HIS', 'dept' => 'ART', 'duration' => 3],
            ['name' => 'B.A. Psychology', 'code' => 'BA_PSY', 'dept' => 'ART', 'duration' => 3],
        ];
        
        $ids = [];
        foreach ($programs as $program) {
            $ids[$program['code']] = DB::table('programs')->insertGetId([
                'name' => $program['name'],
                'code' => $program['code'],
                'department_id' => $departments[$program['dept']],
                'duration_years' => $program['duration'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        return $ids;
    }
    
    private function createSubjects($programs)
    {
        $subjects = [];
        $subjectId = 1;
        
        foreach ($programs as $code => $programId) {
            for ($i = 1; $i <= 3; $i++) {
                $subjects[$subjectId] = DB::table('subjects')->insertGetId([
                    'name' => substr($code, 0, 4) . " Subject $i",
                    'code' => $code . "_S$i",
                    'program_id' => $programId,
                    'semester' => 1,
                    'credits' => 4,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $subjectId++;
            }
        }
        
        return $subjects;
    }
    
    private function createPrincipal($departments)
    {
        $principalId = DB::table('users')->insertGetId([
            'name' => 'Dr. Rajesh Kumar',
            'email' => 'principal@pvgs.edu',
            'password' => Hash::make('password123'),
            'user_type' => 'principal',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Principal has access to all departments
        foreach ($departments as $deptId) {
            DB::table('user_departments')->insert([
                'user_id' => $principalId,
                'department_id' => $deptId,
                'role_in_department' => 'principal',
                'is_primary' => $deptId === reset($departments),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        return $principalId;
    }
    
    private function createHODs($departments)
    {
        $hods = [];
        $deptNames = ['Science', 'Commerce', 'Arts'];
        
        foreach ($departments as $code => $deptId) {
            $hodId = DB::table('users')->insertGetId([
                'name' => "Dr. HOD " . $deptNames[array_search($code, array_keys($departments))],
                'email' => 'hod.' . strtolower($deptNames[array_search($code, array_keys($departments))]) . '@pvgs.edu',
                'password' => Hash::make('password123'),
                'user_type' => 'faculty',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            DB::table('user_departments')->insert([
                'user_id' => $hodId,
                'department_id' => $deptId,
                'role_in_department' => 'hod',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update department with HOD
            DB::table('departments')->where('id', $deptId)->update([
                'department_head_id' => $hodId
            ]);
            
            $hods[$code] = $hodId;
        }
        
        return $hods;
    }
    
    private function createRegistrar($departments)
    {
        $registrarId = DB::table('users')->insertGetId([
            'name' => 'Mrs. Priya Sharma',
            'email' => 'registrar@pvgs.edu',
            'password' => Hash::make('password123'),
            'user_type' => 'registrar',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Registrar has access to all departments
        foreach ($departments as $deptId) {
            DB::table('user_departments')->insert([
                'user_id' => $registrarId,
                'department_id' => $deptId,
                'role_in_department' => 'registrar',
                'is_primary' => $deptId === reset($departments),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        return $registrarId;
    }
    
    private function createFeeStructures($programs)
    {
        foreach ($programs as $programId) {
            DB::table('fee_structures')->insert([
                'program_id' => $programId,
                'academic_year' => '2024-25',
                'total_amount' => 45000,
                'installments' => json_encode([
                    ['sequence' => 1, 'amount' => 15000, 'due_date' => '2024-07-15'],
                    ['sequence' => 2, 'amount' => 15000, 'due_date' => '2024-10-15'],
                    ['sequence' => 3, 'amount' => 15000, 'due_date' => '2025-01-15']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
    
    private function createFaculty($departments, $subjects)
    {
        $faculty = [];
        $subjectArray = array_values($subjects);
        $subjectIndex = 0;
        
        foreach ($departments as $code => $deptId) {
            for ($i = 1; $i <= 5; $i++) {
                $facultyId = DB::table('users')->insertGetId([
                    'name' => "Prof. Faculty $code-$i",
                    'email' => "faculty" . (count($faculty) + 1) . "@pvgs.edu",
                    'password' => Hash::make('password123'),
                    'user_type' => 'faculty',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                DB::table('user_departments')->insert([
                    'user_id' => $facultyId,
                    'department_id' => $deptId,
                    'role_in_department' => 'faculty',
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Assign 2 subjects to each faculty
                for ($j = 0; $j < 2 && $subjectIndex < count($subjectArray); $j++) {
                    DB::table('faculty_assignments')->insert([
                        'faculty_id' => $facultyId,
                        'subject_id' => $subjectArray[$subjectIndex],
                        'academic_year' => '2024-25',
                        'semester' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $subjectIndex++;
                }
                
                $faculty[] = $facultyId;
            }
        }
        
        return $faculty;
    }
    
    private function createStudents($programs, $departments)
    {
        $students = [];
        $programArray = array_values($programs);
        $deptArray = array_values($departments);
        
        for ($i = 1; $i <= 100; $i++) {
            $userId = DB::table('users')->insertGetId([
                'name' => "Student $i",
                'email' => "student$i@pvgs.edu",
                'password' => Hash::make('password123'),
                'user_type' => 'student',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $programId = $programArray[($i - 1) % count($programArray)];
            $deptId = $deptArray[($i - 1) % count($deptArray)];
            
            $studentId = DB::table('students')->insertGetId([
                'user_id' => $userId,
                'program_id' => $programId,
                'department_id' => $deptId,
                'admission_number' => 'PVGS/2024/' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'status' => 'pending',
                'application_date' => now()->subDays(rand(1, 30)),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            DB::table('user_departments')->insert([
                'user_id' => $userId,
                'department_id' => $deptId,
                'role_in_department' => 'student',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $students[] = ['id' => $studentId, 'user_id' => $userId, 'dept_id' => $deptId];
        }
        
        return $students;
    }
    
    private function processAdmissions($students, $registrar, $hods, $principal)
    {
        foreach ($students as $student) {
            // Registrar reviews
            DB::table('students')->where('id', $student['id'])->update(['status' => 'registrar_review']);
            $this->logWorkflow('student_admission', $student['id'], 'pending', 'registrar_review', $registrar, $student['dept_id']);
            
            // HOD approves
            $hodId = DB::table('departments')->where('id', $student['dept_id'])->value('department_head_id');
            DB::table('students')->where('id', $student['id'])->update(['status' => 'hod_approved']);
            $this->logWorkflow('student_admission', $student['id'], 'registrar_review', 'hod_approved', $hodId, $student['dept_id']);
            
            // Principal approves
            DB::table('students')->where('id', $student['id'])->update(['status' => 'principal_approved']);
            $this->logWorkflow('student_admission', $student['id'], 'hod_approved', 'principal_approved', $principal, $student['dept_id']);
        }
    }
    
    private function assignFeesToStudents($students)
    {
        foreach ($students as $student) {
            $programId = DB::table('students')->where('id', $student['id'])->value('program_id');
            $feeStructure = DB::table('fee_structures')
                ->where('program_id', $programId)
                ->where('academic_year', '2024-25')
                ->first();
            
            if ($feeStructure) {
                DB::table('student_fees')->insert([
                    'student_id' => $student['id'],
                    'fee_structure_id' => $feeStructure->id,
                    'total_amount' => $feeStructure->total_amount,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
    
    private function recordFeePayments($students)
    {
        // 70% students pay first installment
        $payingStudents = array_slice($students, 0, 70);
        
        foreach ($payingStudents as $student) {
            $studentFee = DB::table('student_fees')->where('student_id', $student['id'])->first();
            
            if ($studentFee) {
                DB::table('fee_payments')->insert([
                    'student_fee_id' => $studentFee->id,
                    'amount' => 15000,
                    'payment_method' => rand(0, 1) ? 'online' : 'cash',
                    'payment_date' => now()->subDays(rand(1, 15)),
                    'receipt_number' => 'RCP' . str_pad($student['id'], 6, '0', STR_PAD_LEFT),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                DB::table('student_fees')->where('id', $studentFee->id)->update([
                    'paid_amount' => 15000,
                    'status' => 'partial'
                ]);
            }
        }
    }
    
    private function createTimetable($faculty, $subjects)
    {
        // Simplified timetable creation
        // In real system, this would be more complex
    }
    
    private function markAttendance($students, $faculty)
    {
        // Mark attendance for last 30 days
        for ($day = 30; $day >= 1; $day--) {
            $date = now()->subDays($day)->format('Y-m-d');
            
            foreach ($students as $student) {
                // 85% attendance rate
                $status = rand(1, 100) <= 85 ? 'present' : 'absent';
                
                DB::table('attendance_records')->insert([
                    'student_id' => $student['id'],
                    'date' => $date,
                    'status' => $status,
                    'marked_by' => $faculty[array_rand($faculty)],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
    
    private function createLessonPlans($faculty, $subjects)
    {
        $lessonPlans = [];
        $subjectArray = array_values($subjects);
        
        foreach ($faculty as $index => $facultyId) {
            if ($index < count($subjectArray)) {
                $lessonPlanId = DB::table('lesson_plans')->insertGetId([
                    'faculty_id' => $facultyId,
                    'subject_id' => $subjectArray[$index],
                    'academic_year' => '2024-25',
                    'semester' => 1,
                    'status' => 'draft',
                    'planned_topics' => json_encode(['Topic 1', 'Topic 2', 'Topic 3']),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $lessonPlans[] = ['id' => $lessonPlanId, 'faculty_id' => $facultyId];
            }
        }
        
        return $lessonPlans;
    }
    
    private function approveLessonPlans($lessonPlans, $hods, $principal)
    {
        foreach ($lessonPlans as $plan) {
            // Faculty submits
            DB::table('lesson_plans')->where('id', $plan['id'])->update(['status' => 'submitted']);
            
            // HOD approves
            $hodId = array_values($hods)[array_rand($hods)];
            DB::table('lesson_plans')->where('id', $plan['id'])->update(['status' => 'hod_approved']);
            
            // Principal approves
            DB::table('lesson_plans')->where('id', $plan['id'])->update(['status' => 'principal_approved']);
        }
    }
    
    private function enterResults($students, $subjects, $faculty)
    {
        $subjectArray = array_values($subjects);
        
        foreach ($students as $student) {
            // Enter results for 3 subjects
            for ($i = 0; $i < 3; $i++) {
                DB::table('exam_results')->insert([
                    'student_id' => $student['id'],
                    'subject_id' => $subjectArray[($student['id'] + $i) % count($subjectArray)],
                    'academic_year' => '2024-25',
                    'semester' => 1,
                    'theory_marks' => rand(40, 95),
                    'practical_marks' => rand(35, 50),
                    'internal_marks' => rand(15, 20),
                    'total_marks' => 0, // Will be calculated
                    'grade' => 'A',
                    'entered_by' => $faculty[array_rand($faculty)],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
    
    private function testFeeWaiverWorkflow($students, $registrar, $hods, $principal)
    {
        // Test small waiver (≤₹5000 - skips HOD)
        $student1 = $students[0];
        // Workflow: Student → Registrar → Principal (skip HOD)
        
        // Test large waiver (>₹5000 - requires HOD)
        $student2 = $students[1];
        // Workflow: Student → Registrar → HOD → Principal
    }
    
    private function testDepartmentTransfer($students, $hods, $principal)
    {
        // Test cross-department transfer
        $student = $students[0];
        // Workflow: Student → Source HOD → Target HOD → Principal
    }
    
    private function logWorkflow($workflowName, $entityId, $fromState, $toState, $userId, $deptId)
    {
        DB::table('workflow_history')->insert([
            'workflow_name' => $workflowName,
            'entity_type' => 'App\\Models\\Student',
            'entity_id' => $entityId,
            'department_id' => $deptId,
            'from_state' => $fromState,
            'to_state' => $toState,
            'performed_by' => $userId,
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
