<?php

namespace App\Traits;

trait HasWorkflow
{
    public function getWorkflowState(): string
    {
        return $this->getAttribute($this->getWorkflowStateColumn());
    }

    public function setWorkflowState(string $state): void
    {
        $this->setAttribute($this->getWorkflowStateColumn(), $state);
    }

    protected function getWorkflowStateColumn(): string
    {
        return property_exists($this, 'workflowStateColumn') 
            ? $this->workflowStateColumn 
            : 'status';
    }

    public function workflowHistory()
    {
        return DB::table('workflow_history')
            ->where('entity_type', get_class($this))
            ->where('entity_id', $this->id)
            ->orderBy('performed_at', 'desc')
            ->get();
    }
}
