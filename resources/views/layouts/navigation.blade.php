<ul class="space-y-2 px-4">
    @can('view-dashboard')
    <li><a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-blue-50 rounded">Dashboard</a></li>
    @endcan
    
    @can('manage-students')
    <li><a href="{{ route('students.index') }}" class="block px-4 py-2 hover:bg-blue-50 rounded">Students</a></li>
    @endcan
    
    @can('mark-attendance')
    <li><a href="{{ route('attendance.mark') }}" class="block px-4 py-2 hover:bg-blue-50 rounded">Attendance</a></li>
    @endcan
    
    @can('manage-fees')
    <li><a href="{{ route('fees.index') }}" class="block px-4 py-2 hover:bg-blue-50 rounded">Fees</a></li>
    @endcan
    
    @can('manage-results')
    <li><a href="{{ route('results.index') }}" class="block px-4 py-2 hover:bg-blue-50 rounded">Results</a></li>
    @endcan
    
    @can('view-reports')
    <li><a href="{{ route('reports.index') }}" class="block px-4 py-2 hover:bg-blue-50 rounded">Reports</a></li>
    @endcan
</ul>
