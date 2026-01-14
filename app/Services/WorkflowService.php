<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class WorkflowService
{
    public function transition($entity, string $action, $user, ?int $departmentId = null, ?string $comment = null, array $metadata = []): bool
    {
        $workflowName = $this->getWorkflowName($entity);
        $config = Config::get("workflows.{$workflowName}");
        
        if (!$config) {
            throw new \Exception("Workflow {$workflowName} not configured");
        }

        $currentState = $entity->getWorkflowState();
        $allowedTransitions = $config['transitions'][$currentState] ?? [];
        
        if (!in_array($action, $allowedTransitions)) {
            throw new \Exception("Invalid transition from {$currentState} to {$action}");
        }

        // Department validation
        if ($config['requires_department'] && !$departmentId) {
            $departmentId = $entity->department_id ?? $user->primary_department_id;
        }

        if ($config['requires_department'] && !$departmentId) {
            throw new \Exception("Department context required for this workflow");
        }

        // Conditional logic evaluation
        $action = $this->evaluateConditionalLogic($config, $action, $entity, $metadata);

        // Department-specific validation
        if (isset($config['department_validation'][$action])) {
            $this->executeDepartmentValidation($config['department_validation'][$action], $entity, $departmentId, $metadata);
        }

        // Approver validation
        if (isset($config['approvers'][$action])) {
            $this->validateApprover($user, $config['approvers'][$action], $departmentId, $entity);
        }

        return DB::transaction(function () use ($entity, $action, $user, $departmentId, $comment, $currentState, $workflowName, $metadata) {
            $entity->setWorkflowState($action);
            $entity->save();

            // Workflow history
            DB::table('workflow_history')->insert([
                'workflow_name' => $workflowName,
                'entity_type' => get_class($entity),
                'entity_id' => $entity->id,
                'department_id' => $departmentId,
                'from_state' => $currentState,
                'to_state' => $action,
                'performed_by' => $user->id,
                'comment' => $comment,
                'metadata' => json_encode($metadata),
                'performed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Audit log with department context
            DB::table('audit_logs')->insert([
                'user_id' => $user->id,
                'department_id' => $departmentId,
                'action' => "workflow.{$workflowName}.{$action}",
                'entity_type' => get_class($entity),
                'entity_id' => $entity->id,
                'old_values' => json_encode(['state' => $currentState]),
                'new_values' => json_encode(['state' => $action]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);

            Log::info('Workflow transition completed', [
                'workflow' => $workflowName,
                'entity_type' => get_class($entity),
                'entity_id' => $entity->id,
                'department_id' => $departmentId,
                'from_state' => $currentState,
                'to_state' => $action,
                'user_id' => $user->id
            ]);

            return true;
        });
    }

    private function evaluateConditionalLogic(array $config, string $action, $entity, array $metadata): string
    {
        if (!isset($config['conditional_logic'][$action])) {
            return $action;
        }

        $logic = $config['conditional_logic'][$action];
        $condition = $logic['condition'] ?? null;

        if (!$condition) {
            return $action;
        }

        // Evaluate condition (simple expression parser)
        if ($this->evaluateCondition($condition, $entity, $metadata)) {
            return $logic['skip_to'] ?? $action;
        }

        return $action;
    }

    private function evaluateCondition(string $condition, $entity, array $metadata): bool
    {
        // Parse simple conditions like "amount <= 5000"
        if (preg_match('/(\w+)\s*([<>=!]+)\s*(\d+)/', $condition, $matches)) {
            $field = $matches[1];
            $operator = $matches[2];
            $value = (float) $matches[3];

            $entityValue = $metadata[$field] ?? $entity->$field ?? 0;

            return match($operator) {
                '<' => $entityValue < $value,
                '<=' => $entityValue <= $value,
                '>' => $entityValue > $value,
                '>=' => $entityValue >= $value,
                '==' => $entityValue == $value,
                '!=' => $entityValue != $value,
                default => false
            };
        }

        return false;
    }

    private function executeDepartmentValidation(string $validationMethod, $entity, int $departmentId, array $metadata): void
    {
        // Placeholder for department-specific validation logic
        // Can be extended with custom validation classes
        Log::info('Department validation executed', [
            'method' => $validationMethod,
            'entity_id' => $entity->id,
            'department_id' => $departmentId
        ]);
    }

    private function validateApprover($user, array $allowedRoles, ?int $departmentId, $entity): void
    {
        if ($user->user_type === 'super-admin') {
            return;
        }

        // For cross-department workflows, validate against target department
        if (method_exists($entity, 'getTargetDepartmentId')) {
            $targetDeptId = $entity->getTargetDepartmentId();
            if ($targetDeptId && $targetDeptId !== $departmentId) {
                $departmentId = $targetDeptId;
            }
        }

        // Check if user is department head for this department
        if (in_array('department_head', $allowedRoles)) {
            $isDeptHead = DB::table('departments')
                ->where('id', $departmentId)
                ->where('department_head_id', $user->id)
                ->exists();

            if ($isDeptHead) {
                return;
            }
        }

        $userRole = $departmentId 
            ? $user->getRoleInDepartment($departmentId) 
            : $user->user_type;

        if (!in_array($userRole, $allowedRoles) && !in_array($user->user_type, $allowedRoles)) {
            throw new \Exception("User not authorized to perform this action. Required roles: " . implode(', ', $allowedRoles));
        }
    }

    public function getWorkflowHistory($entity, ?int $departmentId = null): array
    {
        $query = DB::table('workflow_history')
            ->where('entity_type', get_class($entity))
            ->where('entity_id', $entity->id)
            ->orderBy('performed_at', 'desc');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        return $query->get()->toArray();
    }

    public function getDepartmentWorkflowSummary(int $departmentId, string $workflowName, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = DB::table('workflow_history')
            ->where('workflow_name', $workflowName)
            ->where('department_id', $departmentId);

        if ($dateFrom) {
            $query->where('performed_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('performed_at', '<=', $dateTo);
        }

        return [
            'total_transitions' => $query->count(),
            'by_state' => $query->select('to_state', DB::raw('count(*) as count'))
                ->groupBy('to_state')
                ->get()
                ->toArray(),
            'by_user' => $query->select('performed_by', DB::raw('count(*) as count'))
                ->groupBy('performed_by')
                ->get()
                ->toArray()
        ];
    }

    private function getWorkflowName($entity): string
    {
        $class = class_basename($entity);
        return match($class) {
            'Student' => 'student_admission',
            'StudentFee' => 'fee_payment',
            'FeeWaiver' => 'fee_waiver',
            'LessonPlan' => 'lesson_plan_approval',
            'DepartmentTransfer' => 'department_transfer',
            default => throw new \Exception("No workflow configured for {$class}")
        };
    }
}
