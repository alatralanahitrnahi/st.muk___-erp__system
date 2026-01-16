import { useQuery } from '@tanstack/react-query';
import { students, departments } from '../services/api';
import { useAuthStore } from '../store/auth';
import { useState } from 'react';
import WorkflowApprovals from '../components/WorkflowApprovals';
import Reports from '../components/Reports';

export default function PrincipalDashboard() {
  const { user, department, setDepartment, logout } = useAuthStore();
  const [selectedDept, setSelectedDept] = useState(null);
  const [activeTab, setActiveTab] = useState('approvals');
  
  const { data: depts } = useQuery({
    queryKey: ['departments'],
    queryFn: departments.list
  });
  
  const { data: studentData } = useQuery({
    queryKey: ['students', selectedDept],
    queryFn: () => students.list(selectedDept),
    enabled: !!selectedDept
  });

  const handleDeptChange = (deptId) => {
    const dept = depts?.data.data.find(d => d.id == deptId);
    setSelectedDept(deptId);
    setDepartment(dept);
  };

  return (
    <div className="min-h-screen bg-gray-100">
      <nav className="bg-blue-600 text-white p-4 shadow-lg">
        <div className="flex justify-between items-center max-w-7xl mx-auto">
          <div>
            <h1 className="text-2xl font-bold">Principal Dashboard</h1>
            <p className="text-sm opacity-90">{user?.name}</p>
          </div>
          <div className="flex items-center gap-4">
            <select 
              value={selectedDept || ''} 
              onChange={(e) => handleDeptChange(e.target.value)}
              className="px-4 py-2 rounded bg-white text-gray-800 font-medium"
            >
              <option value="">All Departments</option>
              {depts?.data.data.map(dept => (
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
        <div className="mb-6 border-b">
          <div className="flex gap-4">
            <button
              onClick={() => setActiveTab('approvals')}
              className={`px-4 py-2 font-medium border-b-2 transition ${activeTab === 'approvals' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'}`}
            >
              Workflow Approvals
            </button>
            <button
              onClick={() => setActiveTab('reports')}
              className={`px-4 py-2 font-medium border-b-2 transition ${activeTab === 'reports' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'}`}
            >
              Reports & Analytics
            </button>
            <button
              onClick={() => setActiveTab('overview')}
              className={`px-4 py-2 font-medium border-b-2 transition ${activeTab === 'overview' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'}`}
            >
              Overview
            </button>
          </div>
        </div>

        {/* Approvals Tab */}
        {activeTab === 'approvals' && selectedDept && (
          <WorkflowApprovals departmentId={selectedDept} />
        )}

        {activeTab === 'approvals' && !selectedDept && (
          <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p className="text-yellow-800">Please select a department to view approvals</p>
          </div>
        )}

        {/* Reports Tab */}
        {activeTab === 'reports' && selectedDept && (
          <Reports departmentId={selectedDept} />
        )}

        {activeTab === 'reports' && !selectedDept && (
          <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p className="text-yellow-800">Please select a department to view reports</p>
          </div>
        )}

        {/* Overview Tab */}
        {activeTab === 'overview' && (
          <>
        <div className="grid grid-cols-3 gap-6 mb-6">
          <div className="bg-white p-6 rounded-lg shadow">
            <h3 className="text-gray-600 text-sm font-semibold">Total Departments</h3>
            <p className="text-4xl font-bold text-blue-600">{depts?.data.data.length || 0}</p>
          </div>
          <div className="bg-white p-6 rounded-lg shadow">
            <h3 className="text-gray-600 text-sm font-semibold">Students</h3>
            <p className="text-4xl font-bold text-green-600">{studentData?.data.data.length || 0}</p>
          </div>
          <div className="bg-white p-6 rounded-lg shadow">
            <h3 className="text-gray-600 text-sm font-semibold">Active Users</h3>
            <p className="text-4xl font-bold text-purple-600">51</p>
          </div>
        </div>

        {selectedDept && studentData && (
          <div className="bg-white rounded-lg shadow">
            <div className="p-6 border-b">
              <h2 className="text-xl font-bold">{department?.name} Students</h2>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {studentData.data.data.map(student => (
                    <tr key={student.id} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap">{student.name}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{student.email}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm">{student.program_name}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}
          </>
        )}
      </div>
    </div>
  );
}
