<?php

return [
    'up' => "
        CREATE TABLE IF NOT EXISTS workflows (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            workflow_type VARCHAR(50) NOT NULL,
            entity_type VARCHAR(50) NOT NULL,
            entity_id INTEGER NOT NULL,
            current_state VARCHAR(50) NOT NULL,
            department_id INTEGER NOT NULL,
            initiated_by INTEGER NOT NULL,
            metadata TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (department_id) REFERENCES departments(id),
            FOREIGN KEY (initiated_by) REFERENCES users(id)
        );
        
        CREATE INDEX idx_workflows_type_state ON workflows(workflow_type, current_state);
        CREATE INDEX idx_workflows_department ON workflows(department_id);
        CREATE INDEX idx_workflows_entity ON workflows(entity_type, entity_id);
        
        CREATE TABLE IF NOT EXISTS workflow_transitions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            workflow_id INTEGER NOT NULL,
            from_state VARCHAR(50),
            to_state VARCHAR(50) NOT NULL,
            performed_by INTEGER NOT NULL,
            action VARCHAR(50) NOT NULL,
            comments TEXT,
            department_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (workflow_id) REFERENCES workflows(id) ON DELETE CASCADE,
            FOREIGN KEY (performed_by) REFERENCES users(id),
            FOREIGN KEY (department_id) REFERENCES departments(id)
        );
        
        CREATE INDEX idx_transitions_workflow ON workflow_transitions(workflow_id);
        CREATE INDEX idx_transitions_performer ON workflow_transitions(performed_by);
        
        CREATE TABLE IF NOT EXISTS workflow_definitions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            workflow_type VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(100) NOT NULL,
            states TEXT NOT NULL,
            transitions TEXT NOT NULL,
            is_active BOOLEAN DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ",
    'down' => "
        DROP TABLE IF EXISTS workflow_transitions;
        DROP TABLE IF EXISTS workflows;
        DROP TABLE IF EXISTS workflow_definitions;
    "
];
