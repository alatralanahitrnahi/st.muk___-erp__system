import { useAuthStore } from '../store/auth';
import { useQuery } from '@tanstack/react-query';
import { students, attendance, departments } from '../services/api';
import api from '../services/api';
import { useState } from 'react';

export default function StudentDashboard() {
  const { user, logout } = useAuthStore();
  const [activeTab, setActiveTab] = useState('dashboard');

  // Get student's department
  const { data: depts } = useQuery({
    queryKey: ['departments'],
    queryFn: departments.list,
    select: (response) => response.data.data
  });

  const studentDept = depts?.[0]?.id; // Student's department

  // Get attendance data
  const { data: attendanceData } = useQuery({
    queryKey: ['student-attendance', studentDept],
    queryFn: () => attendance.list(studentDept, null, null),
    enabled: !!studentDept,
    select: (response) => {
      const records = response.data.data || [];
      const studentRecords = records.filter(r => r.student_id === user?.id);
      const total = studentRecords.length;
      const present = studentRecords.filter(r => r.status === 'present').length;
      const percentage = total > 0 ? ((present / total) * 100).toFixed(1) : 0;
      return { records: studentRecords, total, present, percentage };
    }
  });

  // Get fee data
  const { data: feeData } = useQuery({
    queryKey: ['student-fees', studentDept],
    queryFn: () => api.get('/direct-api.php/api/fees', { params: { department_id: studentDept }}),
    enabled: !!studentDept,
    select: (response) => {
      const fees = response.data.data || [];
      const studentFees = fees.filter(f => f.student_id === user?.id);
      const totalAmount = studentFees.reduce((sum, f) => sum + parseFloat(f.total_amount || 0), 0);
      const paidAmount = studentFees.reduce((sum, f) => sum + parseFloat(f.paid_amount || 0), 0);
      const balance = totalAmount - paidAmount;
      return { fees: studentFees, totalAmount, paidAmount, balance };
    }
  });

  // Get results data
  const { data: resultsData } = useQuery({
    queryKey: ['student-results', studentDept],
    queryFn: () => api.get('/direct-api.php/api/results', { params: { department_id: studentDept }}),
    enabled: !!studentDept,
    select: (response) => {
      const results = response.data.data || [];
      return results.filter(r => r.student_id === user?.id);
    }
  });

  return (
    <div className="min-h-screen bg-gray-100">
      <nav className="bg-purple-600 text-white p-4 shadow-lg">
        <div className="flex justify-between items-center max-w-7xl mx-auto">
          <div>
            <h1 className="text-2xl font-bold">Student Portal</h1>
            <p className="text-sm opacity-90">{user?.name}</p>
          </div>
          <button onClick={logout} className="px-4 py-2 bg-red-500 rounded hover:bg-red-600">
            Logout
          </button>
        </div>
      </nav>
      
      <div className="max-w-7xl mx-auto p-6">
        {/* Tabs */}
        <div className="mb-6 border-b bg-white rounded-t-lg">
          <div className="flex gap-4 px-6">
            <button
              onClick={() => setActiveTab('dashboard')}
              className={`px-4 py-3 font-medium border-b-2 transition ${activeTab === 'dashboard' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-600 hover:text-gray-800'}`}
            >
              Dashboard
            </button>
            <button
              onClick={() => setActiveTab('profile')}
              className={`px-4 py-3 font-medium border-b-2 transition ${activeTab === 'profile' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-600 hover:text-gray-800'}`}
            >
              Profile
            </button>
            <button
              onClick={() => setActiveTab('attendance')}
              className={`px-4 py-3 font-medium border-b-2 transition ${activeTab === 'attendance' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-600 hover:text-gray-800'}`}
            >
              Attendance
            </button>
            <button
              onClick={() => setActiveTab('fees')}
              className={`px-4 py-3 font-medium border-b-2 transition ${activeTab === 'fees' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-600 hover:text-gray-800'}`}
            >
              Fees
            </button>
            <button
              onClick={() => setActiveTab('results')}
              className={`px-4 py-3 font-medium border-b-2 transition ${activeTab === 'results' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-600 hover:text-gray-800'}`}
            >
              Results
            </button>
          </div>
        </div>

        {/* Dashboard Tab */}
        {activeTab === 'dashboard' && (
          <>
            <div className="grid grid-cols-3 gap-6 mb-6">
              <div className="bg-white rounded-lg shadow p-6">
                <h3 className="text-gray-600 text-sm font-semibold mb-2">Attendance</h3>
                <p className={`text-4xl font-bold ${parseFloat(attendanceData?.percentage || 0) >= 75 ? 'text-green-600' : 'text-red-600'}`}>
                  {attendanceData?.percentage || 0}%
                </p>
                <p className="text-sm text-gray-500 mt-2">
                  {attendanceData?.present || 0} / {attendanceData?.total || 0} classes
                </p>
              </div>
              
              <div className="bg-white rounded-lg shadow p-6">
                <h3 className="text-gray-600 text-sm font-semibold mb-2">Fee Balance</h3>
                <p className="text-4xl font-bold text-orange-600">
                  ₹{feeData?.balance?.toLocaleString() || 0}
                </p>
                <p className="text-sm text-gray-500 mt-2">
                  Paid: ₹{feeData?.paidAmount?.toLocaleString() || 0}
                </p>
              </div>
              
              <div className="bg-white rounded-lg shadow p-6">
                <h3 className="text-gray-600 text-sm font-semibold mb-2">Results</h3>
                <p className="text-4xl font-bold text-blue-600">
                  {resultsData?.length || 0}
                </p>
                <p className="text-sm text-gray-500 mt-2">Exams completed</p>
              </div>
            </div>

            <div className="bg-white rounded-lg shadow p-6">
              <h2 className="text-xl font-bold mb-4">Quick Actions</h2>
              <div className="grid grid-cols-3 gap-4">
                <button
                  onClick={() => setActiveTab('attendance')}
                  className="p-4 border-2 border-purple-200 rounded-lg hover:border-purple-600 hover:bg-purple-50 transition"
                >
                  <div className="text-3xl mb-2">📊</div>
                  <div className="font-semibold">View Attendance</div>
                  <div className="text-sm text-gray-600">Check your attendance records</div>
                </button>
                <button
                  onClick={() => setActiveTab('fees')}
                  className="p-4 border-2 border-purple-200 rounded-lg hover:border-purple-600 hover:bg-purple-50 transition"
                >
                  <div className="text-3xl mb-2">💰</div>
                  <div className="font-semibold">Fee Payment</div>
                  <div className="text-sm text-gray-600">View fee status & history</div>
                </button>
                <button
                  onClick={() => setActiveTab('results')}
                  className="p-4 border-2 border-purple-200 rounded-lg hover:border-purple-600 hover:bg-purple-50 transition"
                >
                  <div className="text-3xl mb-2">🎓</div>
                  <div className="font-semibold">Exam Results</div>
                  <div className="text-sm text-gray-600">Check your results</div>
                </button>
              </div>
            </div>
          </>
        )}

        {/* Profile Tab */}
        {activeTab === 'profile' && (
          <div className="bg-white rounded-lg shadow">
            <div className="p-6 border-b">
              <h2 className="text-xl font-bold">Profile Information</h2>
            </div>
            <div className="p-6 space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-sm font-semibold text-gray-600">Name</label>
                  <p className="text-lg">{user?.name}</p>
                </div>
                <div>
                  <label className="text-sm font-semibold text-gray-600">Email</label>
                  <p className="text-lg">{user?.email}</p>
                </div>
                <div>
                  <label className="text-sm font-semibold text-gray-600">User Type</label>
                  <p className="text-lg capitalize">{user?.user_type}</p>
                </div>
                <div>
                  <label className="text-sm font-semibold text-gray-600">Department</label>
                  <p className="text-lg">{depts?.[0]?.name || 'N/A'}</p>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Attendance Tab */}
        {activeTab === 'attendance' && (
          <div className="bg-white rounded-lg shadow">
            <div className="p-6 border-b">
              <div className="flex justify-between items-center">
                <div>
                  <h2 className="text-xl font-bold">My Attendance</h2>
                  <p className="text-sm text-gray-600 mt-1">
                    Overall: {attendanceData?.percentage || 0}% 
                    ({attendanceData?.present || 0} present, {(attendanceData?.total || 0) - (attendanceData?.present || 0)} absent)
                  </p>
                </div>
                <div className={`px-4 py-2 rounded-lg font-bold text-lg ${
                  parseFloat(attendanceData?.percentage || 0) >= 75 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-red-100 text-red-800'
                }`}>
                  {parseFloat(attendanceData?.percentage || 0) >= 75 ? '✓ Good Standing' : '⚠️ Below 75%'}
                </div>
              </div>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {attendanceData?.records?.map((record, idx) => (
                    <tr key={idx} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap text-sm">
                        {new Date(record.attendance_date).toLocaleDateString()}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm">
                        Subject {record.subject_id}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-center">
                        <span className={`px-3 py-1 rounded-full text-xs font-medium ${
                          record.status === 'present' 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-red-100 text-red-800'
                        }`}>
                          {record.status === 'present' ? '✓ Present' : '✗ Absent'}
                        </span>
                      </td>
                    </tr>
                  ))}
                  {!attendanceData?.records?.length && (
                    <tr>
                      <td colSpan="3" className="px-6 py-8 text-center text-gray-500">
                        No attendance records found
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* Fees Tab */}
        {activeTab === 'fees' && (
          <div className="bg-white rounded-lg shadow">
            <div className="p-6 border-b">
              <div className="flex justify-between items-center">
                <div>
                  <h2 className="text-xl font-bold">Fee Status</h2>
                  <p className="text-sm text-gray-600 mt-1">
                    Total: ₹{feeData?.totalAmount?.toLocaleString() || 0} | 
                    Paid: ₹{feeData?.paidAmount?.toLocaleString() || 0} | 
                    Balance: ₹{feeData?.balance?.toLocaleString() || 0}
                  </p>
                </div>
                <div className={`px-4 py-2 rounded-lg font-bold ${
                  (feeData?.balance || 0) === 0 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-orange-100 text-orange-800'
                }`}>
                  {(feeData?.balance || 0) === 0 ? '✓ Paid' : '⚠️ Pending'}
                </div>
              </div>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {feeData?.fees?.map((fee, idx) => {
                    const balance = parseFloat(fee.total_amount || 0) - parseFloat(fee.paid_amount || 0);
                    return (
                      <tr key={idx} className="hover:bg-gray-50">
                        <td className="px-6 py-4 whitespace-nowrap font-medium">
                          Fee Category {fee.fee_category_id}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-right">
                          ₹{parseFloat(fee.total_amount || 0).toLocaleString()}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-right text-green-600">
                          ₹{parseFloat(fee.paid_amount || 0).toLocaleString()}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-right text-orange-600 font-semibold">
                          ₹{balance.toLocaleString()}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-center">
                          <span className={`px-3 py-1 rounded-full text-xs font-medium ${
                            fee.payment_status === 'paid' 
                              ? 'bg-green-100 text-green-800' 
                              : fee.payment_status === 'partial'
                              ? 'bg-yellow-100 text-yellow-800'
                              : 'bg-red-100 text-red-800'
                          }`}>
                            {fee.payment_status || 'pending'}
                          </span>
                        </td>
                      </tr>
                    );
                  })}
                  {!feeData?.fees?.length && (
                    <tr>
                      <td colSpan="5" className="px-6 py-8 text-center text-gray-500">
                        No fee records found
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* Results Tab */}
        {activeTab === 'results' && (
          <div className="bg-white rounded-lg shadow">
            <div className="p-6 border-b">
              <h2 className="text-xl font-bold">Exam Results</h2>
              <p className="text-sm text-gray-600 mt-1">{resultsData?.length || 0} results available</p>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Marks Obtained</th>
                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total Marks</th>
                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Percentage</th>
                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Grade</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {resultsData?.map((result, idx) => {
                    const percentage = ((parseFloat(result.marks_obtained || 0) / parseFloat(result.total_marks || 100)) * 100).toFixed(1);
                    return (
                      <tr key={idx} className="hover:bg-gray-50">
                        <td className="px-6 py-4 whitespace-nowrap font-medium">
                          {result.subject_name || `Subject ${result.subject_id}`}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-center font-semibold">
                          {result.marks_obtained || 0}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-center">
                          {result.total_marks || 100}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-center">
                          <span className={`font-bold ${
                            parseFloat(percentage) >= 75 ? 'text-green-600' :
                            parseFloat(percentage) >= 60 ? 'text-blue-600' :
                            parseFloat(percentage) >= 40 ? 'text-yellow-600' :
                            'text-red-600'
                          }`}>
                            {percentage}%
                          </span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-center">
                          <span className={`px-3 py-1 rounded-full text-xs font-medium ${
                            result.grade === 'A+' || result.grade === 'A' ? 'bg-green-100 text-green-800' :
                            result.grade === 'B+' || result.grade === 'B' ? 'bg-blue-100 text-blue-800' :
                            result.grade === 'C' ? 'bg-yellow-100 text-yellow-800' :
                            'bg-red-100 text-red-800'
                          }`}>
                            {result.grade || 'N/A'}
                          </span>
                        </td>
                      </tr>
                    );
                  })}
                  {!resultsData?.length && (
                    <tr>
                      <td colSpan="5" className="px-6 py-8 text-center text-gray-500">
                        No results available yet
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
