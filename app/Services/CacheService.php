<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheService
{
    const CACHE_TTL = 3600; // 1 hour
    const STATS_CACHE_TTL = 300; // 5 minutes
    
    public function getDashboardStats()
    {
        return Cache::remember('dashboard_stats', self::STATS_CACHE_TTL, function () {
            return [
                'total_students' => DB::table('students')->count(),
                'active_students' => DB::table('students')->where('status', 'active')->count(),
                'pending_admissions' => DB::table('students')->where('application_status', 'pending')->count(),
                'total_fee_collected' => DB::table('fee_payments')->sum('amount'),
                'pending_fees' => DB::table('student_fees')->sum('balance_amount'),
                'attendance_today' => DB::table('attendance_records')->whereDate('attendance_date', today())->count(),
            ];
        });
    }
    
    public function getStudentAttendanceSummary($studentId)
    {
        return Cache::remember("student_attendance_{$studentId}", self::CACHE_TTL, function () use ($studentId) {
            return DB::table('attendance_records')
                ->where('student_id', $studentId)
                ->select('subject_id',
                    DB::raw('COUNT(*) as total_classes'),
                    DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count'),
                    DB::raw('ROUND((SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as percentage'))
                ->groupBy('subject_id')
                ->get();
        });
    }
    
    public function getStudentResults($studentId)
    {
        return Cache::remember("student_results_{$studentId}", self::CACHE_TTL, function () use ($studentId) {
            return DB::table('exam_results')
                ->where('student_id', $studentId)
                ->orderBy('academic_year', 'desc')
                ->orderBy('semester', 'desc')
                ->get();
        });
    }
    
    public function getFeeStructures()
    {
        return Cache::remember('fee_structures', self::CACHE_TTL, function () {
            return DB::table('fee_structures')
                ->join('programs', 'fee_structures.program_id', '=', 'programs.id')
                ->where('fee_structures.is_active', true)
                ->select('fee_structures.*', 'programs.name as program_name')
                ->get();
        });
    }
    
    public function getActivePrograms()
    {
        return Cache::remember('active_programs', self::CACHE_TTL, function () {
            return DB::table('programs')
                ->join('departments', 'programs.department_id', '=', 'departments.id')
                ->where('programs.is_active', true)
                ->select('programs.*', 'departments.name as department_name')
                ->get();
        });
    }
    
    public function clearStudentCache($studentId)
    {
        Cache::forget("student_attendance_{$studentId}");
        Cache::forget("student_results_{$studentId}");
        Cache::forget('dashboard_stats');
    }
    
    public function clearAllCache()
    {
        Cache::flush();
    }
    
    public function warmupCache()
    {
        // Preload frequently accessed data
        $this->getDashboardStats();
        $this->getFeeStructures();
        $this->getActivePrograms();
        
        // Preload top 10 students' data
        $topStudents = DB::table('students')->limit(10)->pluck('id');
        foreach ($topStudents as $studentId) {
            $this->getStudentAttendanceSummary($studentId);
            $this->getStudentResults($studentId);
        }
    }
}