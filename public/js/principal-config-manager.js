// Principal Configuration System - Dynamic UI Manager
class PrincipalConfigManager {
    constructor() {
        this.currentModule = 'students';
        this.permissions = {};
        this.visibilityRules = {};
        this.approvalChains = {};
    }

    // Initialize configuration interface
    async init() {
        await this.loadModuleConfig(this.currentModule);
        this.renderPermissionMatrix();
        this.renderVisibilityRules();
        this.renderApprovalChain();
        this.attachEventListeners();
    }

    // Load module configuration
    async loadModuleConfig(module) {
        try {
            const [perms, rules, chains] = await Promise.all([
                fetch(`/api/principal/config/permissions/${module}`).then(r => r.json()),
                fetch(`/api/principal/config/visibility/${module}`).then(r => r.json()),
                fetch(`/api/principal/config/approval/${module}`).then(r => r.json())
            ]);

            this.permissions = perms.permissions || {};
            this.visibilityRules = rules.rules || {};
            this.approvalChains = chains.chain || [];
        } catch (error) {
            console.error('Failed to load config:', error);
        }
    }

    // Render permission matrix
    renderPermissionMatrix() {
        const roles = ['registrar', 'faculty', 'student'];
        const actions = ['view', 'create', 'edit', 'delete', 'export', 'approve'];
        
        let html = `
            <table class="permission-matrix">
                <thead>
                    <tr>
                        <th>Role</th>
                        ${actions.map(a => `<th>${a}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
        `;

        roles.forEach(role => {
            const perms = this.permissions[role] || {};
            html += `<tr data-role="${role}">
                <td>${role.charAt(0).toUpperCase() + role.slice(1)}</td>
                ${actions.map(action => `
                    <td>
                        <input type="checkbox" 
                               data-role="${role}" 
                               data-action="${action}"
                               ${perms[`can_${action}`] ? 'checked' : ''}
                               onchange="configManager.updatePermission(this)">
                    </td>
                `).join('')}
            </tr>`;
        });

        html += '</tbody></table>';
        document.getElementById('permission-matrix').innerHTML = html;
    }

    // Update permission
    async updatePermission(checkbox) {
        const role = checkbox.dataset.role;
        const action = checkbox.dataset.action;
        const checked = checkbox.checked;

        // Validation warnings
        if (!checked && this.isCriticalPermission(role, action)) {
            if (!confirm(`⚠️ Removing ${action} permission for ${role} may break critical workflows. Continue?`)) {
                checkbox.checked = true;
                return;
            }
        }

        const permissions = {};
        document.querySelectorAll(`input[data-role="${role}"]`).forEach(cb => {
            permissions[`can_${cb.dataset.action}`] = cb.checked;
        });

        await fetch('/api/principal/config/permissions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                module_name: this.currentModule,
                role_name: role,
                permissions
            })
        });

        this.showNotification('Permission updated successfully');
    }

    // Check if permission is critical
    isCriticalPermission(role, action) {
        const critical = {
            'registrar': ['view', 'edit'],
            'faculty': ['view'],
            'student': ['view']
        };
        return critical[role]?.includes(action);
    }

    // Render visibility rules
    renderVisibilityRules() {
        const rules = {
            'faculty': [
                { id: 'assigned_classes', label: 'Students in assigned classes only', checked: true },
                { id: 'assigned_subjects', label: 'Students in assigned subjects', checked: true },
                { id: 'department_all', label: 'All students in their department', checked: false },
                { id: 'unrestricted', label: 'All students (unrestricted)', checked: false }
            ],
            'student': [
                { id: 'own_profile', label: 'Own profile only', checked: true },
                { id: 'classmates', label: 'Classmates (name & roll number only)', checked: true }
            ]
        };

        let html = '<div class="visibility-rules">';
        Object.entries(rules).forEach(([role, ruleList]) => {
            html += `<h4>${role.charAt(0).toUpperCase() + role.slice(1)} can see:</h4>`;
            ruleList.forEach(rule => {
                html += `
                    <label>
                        <input type="checkbox" 
                               data-role="${role}" 
                               data-rule="${rule.id}"
                               ${rule.checked ? 'checked' : ''}
                               onchange="configManager.updateVisibilityRule(this)">
                        ${rule.label}
                    </label><br>
                `;
            });
        });
        html += '</div>';
        document.getElementById('visibility-rules').innerHTML = html;
    }

    // Update visibility rule
    async updateVisibilityRule(checkbox) {
        const role = checkbox.dataset.role;
        const rules = [];
        
        document.querySelectorAll(`input[data-role="${role}"]`).forEach(cb => {
            if (cb.checked) {
                rules.push({
                    type: cb.dataset.rule,
                    config: { enabled: true }
                });
            }
        });

        await fetch('/api/principal/config/visibility', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                module_name: this.currentModule,
                role_name: role,
                rules
            })
        });

        this.showNotification('Visibility rules updated');
    }

    // Render approval chain
    renderApprovalChain() {
        let html = '<div class="approval-chain">';
        this.approvalChains.forEach((step, index) => {
            html += `
                <div class="approval-step">
                    <span>Step ${step.step_order}:</span>
                    <select data-step="${index}" onchange="configManager.updateApprovalStep(this)">
                        <option value="registrar" ${step.approver_role === 'registrar' ? 'selected' : ''}>Registrar</option>
                        <option value="principal" ${step.approver_role === 'principal' ? 'selected' : ''}>Principal</option>
                    </select>
                    <button onclick="configManager.removeApprovalStep(${index})">Remove</button>
                </div>
            `;
        });
        html += '<button onclick="configManager.addApprovalStep()">+ Add Step</button></div>';
        document.getElementById('approval-chain').innerHTML = html;
    }

    // Preview role view
    async previewRoleView(role) {
        const perms = this.permissions[role] || {};
        const preview = document.getElementById('role-preview');
        
        let html = `<h3>Preview: ${role} View</h3><div class="preview-content">`;
        
        if (perms.can_view) {
            html += '<div class="module-section">✓ Can view student list</div>';
        }
        if (perms.can_edit) {
            html += '<div class="module-section">✓ Can edit student profiles</div>';
        }
        if (!perms.can_view && !perms.can_edit) {
            html += '<div class="module-section">✗ No access to this module</div>';
        }
        
        html += '</div>';
        preview.innerHTML = html;
    }

    // Export configuration
    async exportConfig() {
        const modules = ['students', 'attendance', 'fees', 'results'];
        const response = await fetch('/api/principal/config/export', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ modules })
        });
        
        const config = await response.json();
        const blob = new Blob([JSON.stringify(config, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `config_backup_${new Date().toISOString().split('T')[0]}.json`;
        a.click();
    }

    // Import configuration
    async importConfig(file) {
        const config = JSON.parse(await file.text());
        await fetch('/api/principal/config/import', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ config })
        });
        
        this.showNotification('Configuration imported successfully');
        await this.loadModuleConfig(this.currentModule);
        this.renderPermissionMatrix();
    }

    // Show notification
    showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    // Attach event listeners
    attachEventListeners() {
        document.querySelectorAll('.module-tab').forEach(tab => {
            tab.addEventListener('click', async (e) => {
                this.currentModule = e.target.dataset.module;
                await this.loadModuleConfig(this.currentModule);
                this.renderPermissionMatrix();
                this.renderVisibilityRules();
                this.renderApprovalChain();
            });
        });
    }
}

// Initialize on page load
const configManager = new PrincipalConfigManager();
document.addEventListener('DOMContentLoaded', () => configManager.init());
