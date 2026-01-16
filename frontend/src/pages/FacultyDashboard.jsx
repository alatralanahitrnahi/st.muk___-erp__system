import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { students, attendance, departments } from '../services/api';
import { useAuthStore } from '../store/auth';

export default function FacultyDashboard() {
  const { user, logout } = useAuthStore();
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
  const [deptId, setDeptId] = useState(10);
  const [subjectId, setSubjectId] = useState(1);
  const [attendanceMap, setAttendanceMap] = useState({});
  const [activeTab, setActiveTab] = useState('mark');
  const queryClient = useQueryClient();
  
  const { data: depts } = useQuery({
    queryKey: ['departments'],
    queryFn: departments.list,
    select: (response) => response.data.data
  });

  const { data: studentData } = useQuery({
    queryKey: ['students', deptId],
    queryFn: () => students.list(deptId),
    select: (response) => response.data.data
  });

  const { data: attendanceData } = useQuery({
    queryKey: ['attendance', deptId, selectedDate],
    queryFn: () => attendance.list(deptId, selectedDate, selectedDate),
    select: (response) => response.data.data
  });

  const markAttendance = useMutation({
    mutationFn: attendance.mark,
    onSuccess: () => {
      queryClient.invalidateQueries(['attendance']);
      setAttendanceMap({});
      alert('Attendance marked successfully!');
    },
    onError: (error) => {
      alert('Error: ' + (error.response?.data?.message || error.message));
    }
  });

  const handleToggleAttendance = (studentId) => {
    setAttendanceMap(prev => ({
      ...prev,
      [studentId]: prev[studentId] === 'present' ? 'absent' : 'present'
    }));
  };

  const handleMarkAll = (status) => {
    const newMap = {};
    studentData?.forEach(student => {
      newMap[student.id] = status;
    });
    setAttendanceMap(newMap);
  };

  const handleSubmit = () => {
    const records = Object.entries(attendanceMap).map(([studentId, status]) => ({
      student_id: parseInt(studentId),
      subject_id: subjectId,
      date: selectedDate,
      status
    }));

    if (records.length === 0) {
      alert('Please mark attendance for at least one student');
      return;
    }

    markAttendance.mutate({
      records,
      subject_id: subjectId,
      date: selectedDate
    });
  };

  const getAttendancePercentage = (studentId) => {
    const studentRecords = attendanceData?.filter(r => r.student_id === studentId) || [];
    if (studentRecords.length === 0) return 0;
    const present = studentRecords.filter(r => r.status === 'present').length;
    return ((present / studentRecords.length) * 100).toFixed(1);
  };

  return (
    <div className="min-h-screen bg-gray-100">
      <nav className="bg-green-600 text-white p-4 shadow-lg">
        <div className="flex justify-between items-center max-w-7xl mx-auto">
          <div>
            <h1 className="text-2xl font-bold">Faculty Dashboard</h1>
            <p className="text-sm opacity-90">{user?.name}</p>
          </div>
          <div className="flex items-center gap-4">
            <select 
              value={deptId} 
              onChange={(e) => setDeptId(e.target.value)}
              className="px-4 py-2 rounded bg-white text-gray-800 font-medium"
            >
              {depts?.map(dept => (
                <option key={dept.id} value={dept.id}>{dept.name}</option>
              ))}
            </select>
            <button onClick={logout} className="px-4 py-2 bg-red-500 rounded hover:bg-red-600">
              Logout
            </button>
          </div>
        </div>
      </nav>
      
      <div className="max-w-7xl mx-auto p-6">
        {/* Tabs */}
        <div className="mb-6 border-b bg-white rounded-t-lg">
          <div className="flex gap-4 px-6">
            <button
              onClick={() => setActiveTab('mark')}
              className={`px-4 py-3 font-medium border-b-2 transition ${activeTab === 'mark' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-600 hover:text-gray-800'}`}
            >
              Mark Attendance
            </button>
            <button
              onClick={() => setActiveTab('report')}
              className={`px-4 py-3 font-medium border-b-2 transition ${activeTab === 'report' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-600 hover:text-gray-800'}`}
            >
              Attendance Report
            </button>
          </div>
        </div>

        {/* Mark Attendance Tab */}
        {activeTab === 'mark' && (
          <div className="bg-white rounded-lg shadow">
            <div className="p-6 border-b">
              <div className="flex justify-between items-center">
                <h2 className="text-xl font-bold">Mark Attendance</h2>
                <div className="flex gap-3 items-center">
                  <input 
                    type="date" 
                    value={selectedDate}
                    onChange={(e) => setSelectedDate(e.target.value)}
                    className="px-4 py-2 border rounded-lg"
                  />
                  <button
                    onClick={() => handleMarkAll('present')}
                    className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                  >
                    Mark All Present
                  </button>
                  <button
                    onClick={() => handleMarkAll('absent')}
                    className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                  >
                    Mark All Absent
                  </button>
                </div>
              </div>
            </div>
            
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roll No</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student Name</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program</th>
                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {studentData?.map(student => {
                    const status = attendanceMap[student.id];
                    return (
                      <tr key={student.id} className="hover:bg-gray-50">
                        <td className="px-6 py-4 whitespace-nowrap text-sm">{student.admission_number || student.id}</td>
                        <td className="px-6 py-4 whitespace-nowrap font-medium">{student.student_name || student.name}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{student.program_name}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-center">
                          <button
                            onClick={() => handleToggleAttendance(student.id)}
                            className={`px-6 py-2 rounded-lg font-medium transition ${
                              status === 'present' 
                                ? 'bg-green-600 text-white hover:bg-green-700' 
                                : status === 'absent'
                                ? 'bg-red-600 text-white hover:bg-red-700'
                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                            }`}
                          >
                            {status === 'present' ? '✓ Present' : status === 'absent' ? '✗ Absent' : 'Not Marked'}
                          </button>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>

            <div className="p-6 border-t bg-gray-50">
              <div className="flex justify-between items-center">
                <p className="text-sm text-gray-600">
                  Marked: {Object.keys(attendanceMap).length} / {studentData?.length || 0} students
                </p>
                <button
                  onClick={handleSubmit}
                  disabled={markAttendance.isPending || Object.keys(attendanceMap).length === 0}
                  className="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 font-medium"
                >
                  {markAttendance.isPending ? 'Submitting...' : 'Submit Attendance'}
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Attendance Report Tab */}
        {activeTab === 'report' && (
          <div className="bg-white rounded-lg shadow">
            <div className="p-6 border-b">
              <h2 className="text-xl font-bold">Attendance Report</h2>
              <p className="text-sm text-gray-600 mt-1">View student attendance percentages</p>
            </div>
            
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roll No</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student Name</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program</th>
                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Attendance %</th>
                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {studentData?.map(student => {
                    const percentage = parseFloat(getAttendancePercentage(student.id));
                    const isDefaulter = percentage < 75;
                    return (
                      <tr key={student.id} className={`hover:bg-gray-50 ${isDefaulter ? 'bg-red-50' : ''}`}>
                        <td className="px-6 py-4 whitespace-nowrap text-sm">{student.admission_number || student.id}</td>
                        <td className="px-6 py-4 whitespace-nowrap font-medium">{student.student_name || student.name}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{student.program_name}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-center">
                          <span className={`font-bold ${
                            percentage >= 75 ? 'text-green-600' : 'text-red-600'
                          }`}>
                            {percentage}%
                          </span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-center">
                          {isDefaulter ? (
                            <span className="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                              ⚠️ Defaulter
                            </span>
                          ) : (
                            <span className="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                              ✓ Good
                            </span>
                          )}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
