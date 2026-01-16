import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import workflowApi from '../services/workflow';
import { useState } from 'react';

export default function WorkflowApprovals({ departmentId }) {
  const [selectedWorkflow, setSelectedWorkflow] = useState(null);
  const [comments, setComments] = useState('');
  const queryClient = useQueryClient();

  const { data: workflows, isLoading } = useQuery({
    queryKey: ['workflows', departmentId],
    queryFn: () => workflowApi.list({ department_id: departmentId }),
    select: (response) => response.data.data
  });

  const { data: workflowDetail } = useQuery({
    queryKey: ['workflow', selectedWorkflow],
    queryFn: () => workflowApi.get(selectedWorkflow),
    enabled: !!selectedWorkflow,
    select: (response) => response.data.data
  });

  const transitionMutation = useMutation({
    mutationFn: ({ id, action, comments }) => workflowApi.transition(id, action, comments),
    onSuccess: () => {
      queryClient.invalidateQueries(['workflows']);
      queryClient.invalidateQueries(['workflow']);
      setSelectedWorkflow(null);
      setComments('');
      alert('Action completed successfully!');
    },
    onError: (error) => {
      alert('Error: ' + (error.response?.data?.message || error.message));
    }
  });

  const handleAction = (action) => {
    if (!selectedWorkflow) return;
    if (action === 'reject' && !comments.trim()) {
      alert('Comments required for rejection');
      return;
    }
    transitionMutation.mutate({ id: selectedWorkflow, action, comments });
  };

  const getStatusColor = (state) => {
    if (state.includes('approved')) return 'bg-green-100 text-green-800';
    if (state.includes('rejected')) return 'bg-red-100 text-red-800';
    if (state.includes('pending')) return 'bg-yellow-100 text-yellow-800';
    return 'bg-gray-100 text-gray-800';
  };

  const getWorkflowTypeLabel = (type) => {
    const labels = {
      student_admission: 'Student Admission',
      fee_waiver: 'Fee Waiver',
      lesson_plan: 'Lesson Plan',
      department_transfer: 'Department Transfer'
    };
    return labels[type] || type;
  };

  if (isLoading) return <div className="p-6">Loading workflows...</div>;

  return (
    <div className="grid grid-cols-2 gap-6">
      {/* Workflow List */}
      <div className="bg-white rounded-lg shadow">
        <div className="p-6 border-b">
          <h2 className="text-xl font-bold">Pending Approvals</h2>
          <p className="text-sm text-gray-600 mt-1">
            {workflows?.filter(w => w.current_state.includes('pending')).length || 0} pending
          </p>
        </div>
        <div className="divide-y max-h-[600px] overflow-y-auto">
          {workflows?.map(workflow => (
            <div
              key={workflow.id}
              onClick={() => setSelectedWorkflow(workflow.id)}
              className={`p-4 cursor-pointer hover:bg-gray-50 ${
                selectedWorkflow === workflow.id ? 'bg-blue-50' : ''
              }`}
            >
              <div className="flex justify-between items-start mb-2">
                <div>
                  <h3 className="font-semibold">{getWorkflowTypeLabel(workflow.workflow_type)}</h3>
                  <p className="text-sm text-gray-600">ID: {workflow.entity_id}</p>
                </div>
                <span className={`px-2 py-1 rounded text-xs font-medium ${getStatusColor(workflow.current_state)}`}>
                  {workflow.current_state.replace(/_/g, ' ')}
                </span>
              </div>
              <div className="text-xs text-gray-500">
                <p>Initiated by: {workflow.initiated_by_name}</p>
                <p>Created: {new Date(workflow.created_at).toLocaleDateString()}</p>
              </div>
            </div>
          ))}
          {!workflows?.length && (
            <div className="p-8 text-center text-gray-500">
              No workflows found
            </div>
          )}
        </div>
      </div>

      {/* Workflow Detail */}
      <div className="bg-white rounded-lg shadow">
        {selectedWorkflow && workflowDetail ? (
          <>
            <div className="p-6 border-b">
              <h2 className="text-xl font-bold">{getWorkflowTypeLabel(workflowDetail.workflow_type)}</h2>
              <span className={`inline-block mt-2 px-3 py-1 rounded text-sm font-medium ${getStatusColor(workflowDetail.current_state)}`}>
                {workflowDetail.current_state.replace(/_/g, ' ')}
              </span>
            </div>

            <div className="p-6 space-y-4">
              {/* Metadata */}
              <div>
                <h3 className="font-semibold mb-2">Details</h3>
                <div className="bg-gray-50 p-3 rounded text-sm">
                  <pre className="whitespace-pre-wrap">{JSON.stringify(JSON.parse(workflowDetail.metadata || '{}'), null, 2)}</pre>
                </div>
              </div>

              {/* Transition History */}
              <div>
                <h3 className="font-semibold mb-2">History</h3>
                <div className="space-y-2">
                  {workflowDetail.transitions?.map((transition, idx) => (
                    <div key={idx} className="border-l-2 border-blue-500 pl-3 py-2">
                      <p className="font-medium text-sm">{transition.action.toUpperCase()}</p>
                      <p className="text-xs text-gray-600">
                        {transition.from_state || 'Initial'} → {transition.to_state}
                      </p>
                      <p className="text-xs text-gray-500">
                        By: {transition.performed_by_name} | {new Date(transition.created_at).toLocaleString()}
                      </p>
                      {transition.comments && (
                        <p className="text-xs mt-1 text-gray-700">💬 {transition.comments}</p>
                      )}
                    </div>
                  ))}
                </div>
              </div>

              {/* Actions */}
              {workflowDetail.current_state.includes('pending') && (
                <div>
                  <h3 className="font-semibold mb-2">Take Action</h3>
                  <textarea
                    value={comments}
                    onChange={(e) => setComments(e.target.value)}
                    placeholder="Add comments (required for rejection)"
                    className="w-full px-3 py-2 border rounded-lg mb-3"
                    rows="3"
                  />
                  <div className="flex gap-2">
                    <button
                      onClick={() => handleAction('approve')}
                      disabled={transitionMutation.isPending}
                      className="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                    >
                      Approve
                    </button>
                    <button
                      onClick={() => handleAction('reject')}
                      disabled={transitionMutation.isPending}
                      className="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                    >
                      Reject
                    </button>
                  </div>
                </div>
              )}
            </div>
          </>
        ) : (
          <div className="p-8 text-center text-gray-500">
            Select a workflow to view details
          </div>
        )}
      </div>
    </div>
  );
}
