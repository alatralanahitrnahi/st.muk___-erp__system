// Navigation Configuration - Department-Aware
const NavigationConfig = {
    modules: {
        dashboard: { label: 'Dashboard', icon: '📊', path: '/dashboard' },
        students: { label: 'Students', icon: '👥', path: '/students' },
        admission: { label: 'Admissions', icon: '📝', path: '/admissions' },
        attendance: { label: 'Attendance', icon: '✓', path: '/attendance' },
        results: { label: 'Results', icon: '📈', path: '/results' },
        fees: { label: 'Fees', icon: '💰', path: '/fees' },
        lesson_plans: { label: 'Lesson Plans', icon: '📚', path: '/lesson-plans' },
        reports: { label: 'Reports', icon: '📄', path: '/reports' },
        settings: { label: 'Settings', icon: '⚙️', path: '/settings' }
    },

    roles: {
        'super-admin': {
            sections: [
                { title: 'Overview', items: ['dashboard'] },
                { title: 'Academic', items: ['students', 'admission', 'attendance', 'results'] },
                { title: 'Financial', items: ['fees'] },
                { title: 'Teaching', items: ['lesson_plans'] },
                { title: 'Administration', items: ['reports', 'settings'] }
            ]
        },
        'principal': {
            sections: [
                { title: 'Overview', items: ['dashboard'] },
                { title: 'Academic', items: ['students', 'admission', 'attendance', 'results'] },
                { title: 'Financial', items: ['fees'] },
                { title: 'Teaching', items: ['lesson_plans'] },
                { title: 'Reports', items: ['reports'] }
            ]
        },
        'department_head': {
            sections: [
                { title: 'Department Overview', items: ['dashboard'] },
                { title: 'Students', items: ['students', 'admission', 'attendance', 'results'] },
                { title: 'Teaching', items: ['lesson_plans'] },
                { title: 'Reports', items: ['reports'] }
            ]
        },
        'registrar': {
            sections: [
                { title: 'Overview', items: ['dashboard'] },
                { title: 'Student Management', items: ['students', 'admission'] },
                { title: 'Records', items: ['attendance', 'results', 'fees'] },
                { title: 'Reports', items: ['reports'] }
            ]
        },
        'faculty': {
            sections: [
                { title: 'My Classes', items: ['dashboard'] },
                { title: 'Students', items: ['students', 'attendance', 'results'] },
                { title: 'Teaching', items: ['lesson_plans'] }
            ]
        },
        'student': {
            sections: [
                { title: 'My Dashboard', items: ['dashboard'] },
                { title: 'Academics', items: ['attendance', 'results'] },
                { title: 'Financial', items: ['fees'] }
            ]
        }
    },

    async generate(user, departmentId) {
        const permissions = await this.loadPermissions(user.id, departmentId);
        const role = await this.getUserRole(user.id, departmentId);
        const config = this.roles[role] || this.roles[user.role];

        if (!config) return '';

        let html = '<nav class=\"sidebar-nav\" role=\"navigation\" aria-label=\"Main navigation\">';
        
        config.sections.forEach(section => {
            html += `<div class=\"nav-section\">`;
            html += `<h3 class=\"nav-section__title\">${this.getDepartmentContextTitle(section.title, departmentId)}</h3>`;
            html += `<ul class=\"nav-section__list\" role=\"list\">`;
            
            section.items.forEach(moduleKey => {
                const module = this.modules[moduleKey];
                if (!module) return;

                const perm = permissions[moduleKey];
                if (perm && !perm.can_view) return;

                html += `
                    <li class=\"nav-item\" role=\"listitem\">
                        <a href=\"${module.path}?department_id=${departmentId}\" 
                           class=\"nav-item__link\"
                           data-module=\"${moduleKey}\"
                           aria-label=\"${module.label}\"
                           onclick=\"NavigationHandler.navigate(event, '${moduleKey}', ${departmentId})\">
                            <span class=\"nav-item__icon\" aria-hidden=\"true\">${module.icon}</span>
                            <span class=\"nav-item__label\">${module.label}</span>
                        </a>
                    </li>
                `;
            });
            
            html += `</ul></div>`;
        });
        
        html += '</nav>';
        return html;
    },

    getDepartmentContextTitle(title, departmentId) {
        if (!departmentId) return title;
        const deptName = this.getCachedDepartmentName(departmentId);
        return deptName ? `${title} - ${deptName}` : title;
    },

    getCachedDepartmentName(departmentId) {
        const cached = localStorage.getItem('cached_departments');
        if (!cached) return null;
        const departments = JSON.parse(cached);
        const dept = departments.find(d => d.id == departmentId);
        return dept?.name || null;
    },

    async loadPermissions(userId, departmentId) {
        try {
            const response = await fetch(`/api/users/${userId}/department-permissions?department_id=${departmentId}`, {
                headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            const data = await response.json();
            return data.data || data;
        } catch (error) {
            const cached = localStorage.getItem(`permissions_${userId}_${departmentId}`);
            return cached ? JSON.parse(cached) : {};
        }
    },

    async getUserRole(userId, departmentId) {
        try {
            const response = await fetch(`/api/users/${userId}/departments`, {
                headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            const departments = await response.json();
            const dept = (departments.data || departments).find(d => d.id == departmentId);
            return dept?.role_in_department || dept?.role || 'student';
        } catch (error) {
            return 'student';
        }
    }
};

const NavigationHandler = {
    async navigate(event, moduleKey, departmentId) {
        event.preventDefault();
        
        // Update active state
        document.querySelectorAll('.nav-item__link').forEach(link => {
            link.classList.remove('nav-item__link--active');
        });
        event.currentTarget.classList.add('nav-item__link--active');
        
        // Dispatch navigation event
        window.dispatchEvent(new CustomEvent('moduleNavigate', {
            detail: { module: moduleKey, departmentId }
        }));
        
        // Load module content
        await this.loadModuleContent(moduleKey, departmentId);
    },

    async loadModuleContent(moduleKey, departmentId) {
        const contentArea = document.getElementById('mainContent');
        if (!contentArea) return;

        contentArea.innerHTML = '<div class=\"loading\">Loading...</div>';
        
        try {
            // Trigger data reload for the module
            window.dispatchEvent(new CustomEvent('loadModuleData', {
                detail: { module: moduleKey, departmentId }
            }));
        } catch (error) {
            contentArea.innerHTML = '<div class=\"error\">Failed to load content</div>';
        }
    },

    async refresh(departmentId) {
        const user = await this.getCurrentUser();
        if (!user) return;

        const nav = await NavigationConfig.generate(user, departmentId);
        const navContainer = document.getElementById('sidebarNav');
        if (navContainer) {
            navContainer.innerHTML = nav;
        }
    },

    async getCurrentUser() {
        try {
            const response = await fetch('/api/user', {
                headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });
            return response.ok ? await response.json() : null;
        } catch {
            return JSON.parse(localStorage.getItem('cached_user') || 'null');
        }
    }
};

// Listen for department changes
window.addEventListener('departmentChanged', async (event) => {
    await NavigationHandler.refresh(event.detail.departmentId);
});

// Initialize navigation on page load
document.addEventListener('DOMContentLoaded', async () => {
    const user = await NavigationHandler.getCurrentUser();
    if (!user) return;

    const departmentId = DepartmentSelector.getActiveDepartmentId();
    if (departmentId) {
        await NavigationHandler.refresh(departmentId);
    }
});
