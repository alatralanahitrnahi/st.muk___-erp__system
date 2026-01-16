<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetTestData extends Command
{
    protected $signature = 'test:reset {--fresh : Run fresh migrations}';
    protected $description = 'Reset database and seed with real-world test data';

    public function handle()
    {
        $this->info('🔄 Resetting database with real-world test data...');
        $this->newLine();
        
        if ($this->option('fresh')) {
            $this->warn('⚠️  Running fresh migrations (all data will be lost)');
            if (!$this->confirm('Are you sure?')) {
                $this->error('Cancelled');
                return 1;
            }
            
            Artisan::call('migrate:fresh');
            $this->info('✅ Fresh migrations completed');
        }
        
        $this->info('📊 Seeding real-world test data...');
        $this->newLine();
        
        Artisan::call('db:seed', ['--class' => 'RealWorldTestSeeder']);
        
        $this->newLine();
        $this->info('✅ Test data seeded successfully!');
        $this->newLine();
        
        $this->displayCredentials();
        
        return 0;
    }
    
    private function displayCredentials()
    {
        $this->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin', 'admin@pvgs.edu', 'password123'],
                ['Principal', 'principal@pvgs.edu', 'password123'],
                ['HOD Science', 'hod.science@pvgs.edu', 'password123'],
                ['HOD Commerce', 'hod.commerce@pvgs.edu', 'password123'],
                ['HOD Arts', 'hod.arts@pvgs.edu', 'password123'],
                ['Registrar', 'registrar@pvgs.edu', 'password123'],
                ['Faculty', 'faculty1@pvgs.edu', 'password123'],
                ['Student', 'student1@pvgs.edu', 'password123'],
            ]
        );
        
        $this->newLine();
        $this->info('📈 Data Summary:');
        $this->line('  • 3 Departments (Science, Commerce, Arts)');
        $this->line('  • 9 Programs (3 per department)');
        $this->line('  • 27 Subjects (3 per program)');
        $this->line('  • 15 Faculty (5 per department)');
        $this->line('  • 100 Students (distributed across programs)');
        $this->line('  • 3,000 Attendance records (30 days)');
        $this->line('  • 300 Results (3 subjects per student)');
        $this->line('  • 120+ Workflow transitions');
        $this->newLine();
    }
}
