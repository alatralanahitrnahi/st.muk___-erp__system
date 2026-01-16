import { useDepartmentStore } from '../store';

const DEPARTMENT_COLORS = {
  1: { bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200' },
  2: { bg: 'bg-green-50', text: 'text-green-700', border: 'border-green-200' },
  3: { bg: 'bg-purple-50', text: 'text-purple-700', border: 'border-purple-200' },
};

export default function DepartmentSelector() {
  const { activeDepartment, departments, setActiveDepartment } = useDepartmentStore();

  if (!departments || departments.length === 0) return null;

  return (
    <div className="relative">
      <label htmlFor="department" className="sr-only">
        Select Department
      </label>
      <select
        id="department"
        value={activeDepartment?.id || ''}
        onChange={(e) => {
          const dept = departments.find(d => d.id === parseInt(e.target.value));
          if (dept) setActiveDepartment(dept);
        }}
        className={`block w-full pl-3 pr-10 py-2 text-base border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
          activeDepartment ? DEPARTMENT_COLORS[activeDepartment.id]?.bg : 'bg-white'
        } ${activeDepartment ? DEPARTMENT_COLORS[activeDepartment.id]?.text : 'text-gray-900'} ${
          activeDepartment ? DEPARTMENT_COLORS[activeDepartment.id]?.border : 'border-gray-300'
        }`}
      >
        {departments.map((dept) => (
          <option key={dept.id} value={dept.id}>
            {dept.name}
          </option>
        ))}
      </select>
    </div>
  );
}
