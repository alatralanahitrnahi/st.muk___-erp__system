<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LessonPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LessonPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LessonPlan::with(['faculty', 'subject', 'program']);
        
        if ($request->has('faculty_id')) {
            $query->forFaculty($request->faculty_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $lessonPlans = $query->orderBy('lesson_date', 'desc')->paginate(15);
        
        return response()->json($lessonPlans);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'program_id' => 'required|exists:programs,id',
            'semester_id' => 'required|exists:semesters,id',
            'lesson_date' => 'required|date',
            'lesson_number' => 'required|integer|min:1',
            'topic' => 'required|string|max:255',
            'objectives' => 'required|array',
            'materials_required' => 'required|array',
            'teaching_method' => 'required|in:5E_model,gradual_release,gagne_nine_events,lecture,demonstration,group_work,practical,other',
            'lesson_procedure' => 'required|string',
            'assessment_method' => 'required|string',
            'differentiation_notes' => 'nullable|string',
            'planned_duration' => 'required|integer|min:30|max:180'
        ]);

        $validated['faculty_id'] = auth()->id();
        $validated['academic_year_id'] = 1; // Current academic year

        $lessonPlan = LessonPlan::create($validated);

        return response()->json($lessonPlan->load(['subject', 'program']), 201);
    }

    public function show(LessonPlan $lessonPlan): JsonResponse
    {
        return response()->json($lessonPlan->load(['faculty', 'subject', 'program', 'hodApprover', 'principalApprover']));
    }

    public function update(Request $request, LessonPlan $lessonPlan): JsonResponse
    {
        if ($lessonPlan->status !== 'draft') {
            return response()->json(['error' => 'Cannot edit submitted lesson plan'], 403);
        }

        $validated = $request->validate([
            'topic' => 'sometimes|string|max:255',
            'objectives' => 'sometimes|array',
            'materials_required' => 'sometimes|array',
            'teaching_method' => 'sometimes|in:5E_model,gradual_release,gagne_nine_events,lecture,demonstration,group_work,practical,other',
            'lesson_procedure' => 'sometimes|string',
            'assessment_method' => 'sometimes|string',
            'differentiation_notes' => 'nullable|string',
            'planned_duration' => 'sometimes|integer|min:30|max:180'
        ]);

        $lessonPlan->update($validated);

        return response()->json($lessonPlan->load(['subject', 'program']));
    }

    public function submit(LessonPlan $lessonPlan): JsonResponse
    {
        if ($lessonPlan->status !== 'draft') {
            return response()->json(['error' => 'Lesson plan already submitted'], 403);
        }

        $lessonPlan->update(['status' => 'submitted']);

        return response()->json(['message' => 'Lesson plan submitted for approval']);
    }

    public function approve(Request $request, LessonPlan $lessonPlan): JsonResponse
    {
        $user = auth()->user();
        
        if ($user->hasRole('principal')) {
            $lessonPlan->update([
                'status' => 'approved',
                'principal_approved_at' => now(),
                'principal_approved_by' => $user->id
            ]);
        } elseif ($user->hasRole('hod')) {
            $lessonPlan->update([
                'status' => 'hod_approved',
                'hod_approved_at' => now(),
                'hod_approved_by' => $user->id
            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['message' => 'Lesson plan approved']);
    }

    public function addReflection(Request $request, LessonPlan $lessonPlan): JsonResponse
    {
        $validated = $request->validate([
            'reflection_notes' => 'required|string',
            'actual_duration' => 'required|integer|min:1',
            'attendance_count' => 'required|integer|min:0'
        ]);

        $lessonPlan->update($validated);

        return response()->json(['message' => 'Reflection added successfully']);
    }
}