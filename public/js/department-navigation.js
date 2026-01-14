// Department-Aware Navigation System
const DepartmentNavigation = {
    async generateNavigation(user) {
        const departments = await this.loadUserDepartments(user.id);
        const permissions = await this.loadDepartmentPermissions(user.id);
        
        if (departments.length === 0) {
            return NavigationConfig.generateNavigation(user.user_type);
        }

        let html = '';
        
        if (departments.length > 1) {
            html += this.renderDepartmentSelector(departments);
        }

        const activeDept = this.getActiveDepartment() || departments[0];
        html += this.renderDepartmentNavigation(user, activeDept, permissions);

        return html;
    },

    renderDepartmentSelector(departments) {
        return `
            <div class="department-selector">
                <select id="activeDepartment" onchange="DepartmentNavigation.switchDepartment(this.value)">
                    ${departments.map(d => `
                        <option value="${d.id}" ${d.is_primary ? 'selected' : ''}>
                            ${d.name}
                        </option>
                    `).join('')}
                </select>
            </div>
        `;
    },

    renderDepartmentNavigation(user, department, permissions) {
        const role = user.getRoleInDepartment ? user.getRoleInDepartment(department.id) : user.user_type;
        const config = NavigationConfig.roles[role];
        
        if (!config) return '';

        let html = `<div class="nav-department" data-department="${department.id}">`;
        
        config.sections.forEach(section => {
            html += `<div class="nav-section"><div class="nav-section-title">${section.title}</div>`;
            
            section.items.forEach(moduleKey => {
                const module = NavigationConfig.modules[moduleKey];
                if (!module) return;

                const perm = permissions[department.id]?.[moduleKey];
                if (perm && !perm.can_view) return;

                html += `<div class="nav-item" onclick="showSection('${moduleKey}')" data-module="${moduleKey}">
                    <span class="icon">${module.icon}</span> ${module.label}
                </div>`;
            });
            
            html += '</div>';
        });
        
        html += '</div>';
        return html;
    },

    async switchDepartment(departmentId) {
        localStorage.setItem('active_department_id', departmentId);
        const user = ApiService.getCurrentUser();
        const nav = await this.generateNavigation(user);
        document.getElementById('navigation').innerHTML = nav;
        window.dispatchEvent(new CustomEvent('departmentChanged', { detail: { departmentId } }));
    },

    getActiveDepartment() {
        const stored = localStorage.getItem('active_department_id');
        return stored ? parseInt(stored) : null;
    },

    async loadUserDepartments(userId) {
        try {
            const response = await ApiService.fetch(`/api/users/${userId}/departments`);
            return response.data || response;
        } catch (error) {
            return [];
        }
    },

    async loadDepartmentPermissions(userId) {
        try {
            const response = await ApiService.fetch(`/api/users/${userId}/department-permissions`);
            return response.data || response;
        } catch (error) {
            return {};
        }
    }
};
