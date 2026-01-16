@extends('layouts.app')

@section('title', 'Principal Dashboard')

@section('content')
<div class="space-y-6">
    <h1 class="text-3xl font-bold">Principal Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-500 text-sm">Total Students</h3>
            <p class="text-3xl font-bold">{{ $stats['total_students'] ?? 0 }}</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-500 text-sm">Total Faculty</h3>
            <p class="text-3xl font-bold">{{ $stats['total_faculty'] ?? 0 }}</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-500 text-sm">Departments</h3>
            <p class="text-3xl font-bold">{{ $stats['total_departments'] ?? 0 }}</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-500 text-sm">Pending Approvals</h3>
            <p class="text-3xl font-bold text-red-600">{{ $stats['pending_approvals'] ?? 0 }}</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-bold mb-4">Recent Activities</h3>
            <ul class="space-y-2">
                @forelse($recent_activities ?? [] as $activity)
                <li class="border-b pb-2">{{ $activity->description }}</li>
                @empty
                <li class="text-gray-500">No recent activities</li>
                @endforelse
            </ul>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-bold mb-4">Pending Workflows</h3>
            <ul class="space-y-2">
                @forelse($pending_workflows ?? [] as $workflow)
                <li class="border-b pb-2">
                    <a href="{{ route('workflows.show', $workflow->id) }}" class="text-blue-600 hover:underline">
                        {{ $workflow->type }} - {{ $workflow->status }}
                    </a>
                </li>
                @empty
                <li class="text-gray-500">No pending workflows</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
