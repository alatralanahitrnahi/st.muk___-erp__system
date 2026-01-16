import { useQuery } from '@tanstack/react-query';
import api from '../services/api';
import { useState } from 'react';

export default function Reports({ departmentId }) {
  const [reportType, setReportType] = useState('attendance');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');

  // Attendance Report
  const { data: attendanceReport, isLoading: loadingAttendance } = useQuery({
    queryKey: ['report-attendance', departmentId],
    queryFn: () => api.get('/direct-api.php/api/reports/attendance', { 
      params: { department_id: departmentId }
    }),
    enabled: !!departmentId && reportType === 'attendance',
    select: (response) => response.data.data
  });

  // NAAC Report
  const { data: naacReport, isLoading: loadingNaac } = useQuery({
    queryKey: ['report-naac', departmentId],
    queryFn: () => api.get('/direct-api.php/api/reports/naac', { 
      params: { department_id: departmentId }
    }),
    enabled: !!departmentId && reportType === 'naac',
    select: (response) => response.data.data
  });

  // Financial Report (Fees)
  const { data: financialReport, isLoading: loadingFinancial } = useQuery({
    queryKey: ['report-financial', departmentId],
    queryFn: () => api.get('/direct-api.php/api/fees', { 
      params: { department_id: departmentId }
    }),
    enabled: !!departmentId && reportType === 'financial',
    select: (response) => {
      const fees = response.data.data || [];
      const totalAmount = fees.reduce((sum, f) => sum + parseFloat(f.total_amount || 0), 0);
      const paidAmount = fees.reduce((sum, f) => sum + parseFloat(f.paid_amount || 0), 0);
      const pendingAmount = totalAmount - paidAmount;
      const paidCount = fees.filter(f => f.payment_status === 'paid').length;
      const partialCount = fees.filter(f => f.payment_status === 'partial').length;
      const pendingCount = fees.filter(f => f.payment_status === 'pending').length;
      
      return {
        totalAmount,
        paidAmount,
        pendingAmount,
        paidCount,
        partialCount,
        pendingCount,
        totalStudents: fees.length,
        collectionRate: fees.length > 0 ? ((paidAmount / totalAmount) * 100).toFixed(1) : 0
      };
    }
  });

  const handleExport = () => {
    let data = {};
    let filename = '';

    if (reportType === 'attendance') {
      data = attendanceReport;
      filename = `attendance_report_${departmentId}_${Date.now()}.json`;
    } else if (reportType === 'naac') {
      data = naacReport;
      filename = `naac_report_${departmentId}_${Date.now()}.json`;
    } else if (reportType === 'financial') {
      data = financialReport;
      filename = `financial_report_${departmentId}_${Date.now()}.json`;
    }

    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
  };

  const isLoading = loadingAttendance || loadingNaac || loadingFinancial;

  return (
    <div className="space-y-6">
      {/* Report Type Selector */}
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex justify-between items-center">
          <div>
            <h2 className="text-xl font-bold mb-2">Reports & Analytics</h2>
            <p className="text-sm text-gray-600">Generate and export various reports</p>
          </div>
          <button
            onClick={handleExport}
            disabled={isLoading}
            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            📥 Export Report
          </button>
        </div>

        <div className="flex gap-3 mt-6">
          <button
            onClick={() => setReportType('attendance')}
            className={`px-4 py-2 rounded-lg font-medium transition ${
              reportType === 'attendance'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            📊 Attendance Report
          </button>
          <button
            onClick={() => setReportType('naac')}
            className={`px-4 py-2 rounded-lg font-medium transition ${
              reportType === 'naac'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            📋 NAAC Report
          </button>
          <button
            onClick={() => setReportType('financial')}
            className={`px-4 py-2 rounded-lg font-medium transition ${
              reportType === 'financial'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            💰 Financial Report
          </button>
        </div>
      </div>

      {/* Attendance Report */}
      {reportType === 'attendance' && attendanceReport && (
        <div className="bg-white rounded-lg shadow">
          <div className="p-6 border-b">
            <h3 className="text-lg font-bold">Attendance Summary</h3>
          </div>
          <div className="p-6">
            <div className="grid grid-cols-4 gap-6">
              <div className="bg-blue-50 p-4 rounded-lg">
                <p className="text-sm text-gray-600 mb-1">Total Students</p>
                <p className="text-3xl font-bold text-blue-600">
                  {attendanceReport.total_students || 0}
                </p>
              </div>
              <div className="bg-green-50 p-4 rounded-lg">
                <p className="text-sm text-gray-600 mb-1">Total Present</p>
                <p className="text-3xl font-bold text-green-600">
                  {attendanceReport.total_present || 0}
                </p>
              </div>
              <div className="bg-red-50 p-4 rounded-lg">
                <p className="text-sm text-gray-600 mb-1">Total Absent</p>
                <p className="text-3xl font-bold text-red-600">
                  {attendanceReport.total_absent || 0}
                </p>
              </div>
              <div className="bg-purple-50 p-4 rounded-lg">
                <p className="text-sm text-gray-600 mb-1">Attendance %</p>
                <p className="text-3xl font-bold text-purple-600">
                  {attendanceReport.attendance_percentage || 0}%
                </p>
              </div>
            </div>

            <div className="mt-6 p-4 bg-gray-50 rounded-lg">
              <h4 className="font-semibold mb-2">Analysis</h4>
              <p className="text-sm text-gray-700">
                {parseFloat(attendanceReport.attendance_percentage || 0) >= 75
                  ? '✅ Excellent attendance rate. Department is meeting the 75% requirement.'
                  : '⚠️ Attendance below 75%. Action required to improve student attendance.'}
              </p>
            </div>
          </div>
        </div>
      )}

      {/* NAAC Report */}
      {reportType === 'naac' && naacReport && (
        <div className="bg-white rounded-lg shadow">
          <div className="p-6 border-b">
            <h3 className="text-lg font-bold">NAAC Compliance Report</h3>
            <p className="text-sm text-gray-600 mt-1">National Assessment and Accreditation Council</p>
          </div>
          <div className="p-6">
            <div className="grid grid-cols-3 gap-6">
              <div className="bg-blue-50 p-4 rounded-lg">
                <p className="text-sm text-gray-600 mb-1">Student Enrollment</p>
                <p className="text-3xl font-bold text-blue-600">
                  {naacReport.student_enrollment || 0}
                </p>
              </div>
              <div className="bg-green-50 p-4 rounded-lg">
                <p className="text-sm text-gray-600 mb-1">Attendance Rate</p>
                <p className="text-3xl font-bold text-green-600">
                  {naacReport.attendance_percentage || 0}%
                </p>
              </div>
              <div className="bg-purple-50 p-4 rounded-lg">
                <p className="text-sm text-gray-600 mb-1">Pass Percentage</p>
                <p className="text-3xl font-bold text-purple-600">
                  {naacReport.pass_percentage || 0}%
                </p>
              </div>
            </div>

            <div className="mt-6 space-y-4">
              <div className="p-4 bg-gray-50 rounded-lg">
                <h4 className="font-semibold mb-2">📚 Criterion 1: Curricular Aspects</h4>
                <p className="text-sm text-gray-700">
                  Student Enrollment: {naacReport.student_enrollment || 0} students
                </p>
              </div>
              <div className="p-4 bg-gray-50 rounded-lg">
                <h4 className="font-semibold mb-2">👨‍🏫 Criterion 2: Teaching-Learning and Evaluation</h4>
                <p className="text-sm text-gray-700">
                  Attendance Rate: {naacReport.attendance_percentage || 0}% 
                  {parseFloat(naacReport.attendance_percentage || 0) >= 75 ? ' ✅' : ' ⚠️'}
                </p>
              </div>
              <div className="p-4 bg-gray-50 rounded-lg">
                <h4 className="font-semibold mb-2">🎓 Criterion 3: Student Performance</h4>
                <p className="text-sm text-gray-700">
                  Pass Percentage: {naacReport.pass_percentage || 0}%
                  {parseFloat(naacReport.pass_percentage || 0) >= 60 ? ' ✅' : ' ⚠️'}
                </p>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Financial Report */}
      {reportType === 'financial' && financialReport && (
        <div className="bg-white rounded-lg shadow">
          <div className="p-6 border-b">
            <h3 className="text-lg font-bold">Financial Summary</h3>
            <p className="text-sm text-gray-600 mt-1">Fee collection and payment status</p>
          </div>
          <div className="p-6">
            <div className="grid grid-cols-4 gap-6 mb-6">
              <div className="bg-blue-50 p-4 rounded-lg">
                <p className="text-sm text-gray-600 mb-1">Total Amount</p>
                <p className="text-2xl font-bold text-blue-600">
                  ₹{financialReport.totalAmount?.toLocaleString() || 0}
                </p>
              </div>
              <div className="bg-green-50 p-4 rounded-lg">
                <p className="text-sm text-gray-600 mb-1">Collected</p>
                <p className="text-2xl font-bold text-green-600">
                  ₹{financialReport.paidAmount?.toLocaleString() || 0}
                </p>
              </div>
              <div className="bg-orange-50 p-4 rounded-lg">
                <p className="text-sm text-gray-600 mb-1">Pending</p>
                <p className="text-2xl font-bold text-orange-600">
                  ₹{financialReport.pendingAmount?.toLocaleString() || 0}
                </p>
              </div>
              <div className="bg-purple-50 p-4 rounded-lg">
                <p className="text-sm text-gray-600 mb-1">Collection Rate</p>
                <p className="text-2xl font-bold text-purple-600">
                  {financialReport.collectionRate || 0}%
                </p>
              </div>
            </div>

            <div className="grid grid-cols-3 gap-6">
              <div className="p-4 bg-green-50 rounded-lg text-center">
                <p className="text-3xl font-bold text-green-600 mb-1">
                  {financialReport.paidCount || 0}
                </p>
                <p className="text-sm text-gray-600">Fully Paid</p>
              </div>
              <div className="p-4 bg-yellow-50 rounded-lg text-center">
                <p className="text-3xl font-bold text-yellow-600 mb-1">
                  {financialReport.partialCount || 0}
                </p>
                <p className="text-sm text-gray-600">Partial Payment</p>
              </div>
              <div className="p-4 bg-red-50 rounded-lg text-center">
                <p className="text-3xl font-bold text-red-600 mb-1">
                  {financialReport.pendingCount || 0}
                </p>
                <p className="text-sm text-gray-600">Pending</p>
              </div>
            </div>

            <div className="mt-6 p-4 bg-gray-50 rounded-lg">
              <h4 className="font-semibold mb-2">Financial Health</h4>
              <p className="text-sm text-gray-700">
                {parseFloat(financialReport.collectionRate || 0) >= 80
                  ? '✅ Excellent collection rate. Financial health is strong.'
                  : parseFloat(financialReport.collectionRate || 0) >= 60
                  ? '⚠️ Moderate collection rate. Follow up on pending payments.'
                  : '❌ Low collection rate. Immediate action required for fee collection.'}
              </p>
            </div>
          </div>
        </div>
      )}

      {isLoading && (
        <div className="bg-white rounded-lg shadow p-12 text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Loading report data...</p>
        </div>
      )}
    </div>
  );
}
