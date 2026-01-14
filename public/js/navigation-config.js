// Standardized Navigation Configuration - PVGS ERP System
// All section IDs match HTML templates exactly

const NavigationConfig = {
    // Module definitions with metadata
    modules: {
        'dashboard': { label: 'Dashboard', icon: '📊', category: 'core' },
        'academic-structure': { label: 'Academic Structure', icon: '🏫', category: 'academic' },
        'users': { label: 'User Management', icon: '👥', category: 'system' },
        'settings': { label: 'System Settings', icon: '⚙️', category: 'system' },
        'security': { label: 'Security', icon: '🔐', category: 'system' },
        'logs': { label: 'System Logs', icon: '📋', category: 'system' },
        'roles': { label: 'Roles & Permissions', icon: '🎭', category: 'system' },
        'institutions': { label: 'Institutions', icon: '🏫', category: 'system' },
        'database': { label: 'Database Management', icon: '💾', category: 'system' },
        'analytics': { label: 'Analytics', icon: '📊', category: 'reports' },
        'maintenance': { label: 'Maintenance', icon: '🔧', category: 'system' },
        'overview': { label: 'Overview', icon: '📊', category: 'core' },
        'front-office': { label: 'Front Office', icon: '🏢', category: 'operations' },
        'student-section': { label: 'Student Affairs', icon: '🎓', category: 'operations' },
        'accounts': { label: 'Financial Hub', icon: '💰', category: 'operations' },
        'lab-resources': { label: 'Resources', icon: '🔬', category: 'operations' },
        'faculty': { label: 'Faculty', icon: '👨🏫', category: 'academic' },
        'academics': { label: 'Programs', icon: '📚', category: 'academic' },
        'library': { label: 'Library', icon: '📖', category: 'services' },
        'sports': { label: 'Sports', icon: '⚽', category: 'services' },
        'admissions': { label: 'Admissions', icon: '📝', category: 'student' },
        'students': { label: 'Student Records', icon: '🎓', category: 'student' },
        'documents': { label: 'Document Verification', icon: '📄', category: 'student' },
        'fees': { label: 'Fee Management', icon: '💰', category: 'financial' },
        'payments': { label: 'Payment Processing', icon: '💳', category: 'financial' },
        'attendance': { label: 'Attendance', icon: '📅', category: 'academic' },
        'results': { label: 'Results', icon: '📊', category: 'academic' },
        'reports': { label: 'NAAC Reports', icon: '📋', category: 'reports' },
        'classes': { label: 'My Classes', icon: '📚', category: 'teaching' },
        'timetable': { label: 'Timetable', icon: '🕐', category: 'teaching' },
        'lesson-plans': { label: 'Lesson Plans', icon: '📝', category: 'teaching' },
        'teaching-schedule': { label: 'Teaching Schedule', icon: '📋', category: 'teaching' },
        'assignments': { label: 'Assignments', icon: '📋', category: 'teaching' },
        'mentorship': { label: 'Mentorship', icon: '🤝', category: 'teaching' },
        'leave': { label: 'Leave Management', icon: '🏖️', category: 'personal' },
        'profile': { label: 'Profile', icon: '👤', category: 'personal' },
        'subjects': { label: 'Subjects', icon: '📚', category: 'academic' },
        'applications': { label: 'Applications', icon: '📋', category: 'services' }
    },

    // Role-based navigation structure
    roles: {
        'super-admin': {
            sections: [
                {
                    title: 'System Administration',
                    items: ['dashboard', 'settings', 'security', 'logs']
                },
                {
                    title: 'User Management',
                    items: ['users', 'roles', 'institutions']
                },
                {
                    title: 'System Operations',
                    items: ['database', 'analytics', 'maintenance']
                }
            ]
        },
        'principal': {
            sections: [
                {
                    title: 'Executive',
                    items: ['overview', 'analytics']
                },
                {
                    title: 'Operations',
                    items: ['front-office', 'student-section', 'accounts', 'lab-resources']
                },
                {
                    title: 'Academic',
                    items: ['faculty', 'academics', 'library']
                },
                {
                    title: 'Reports',
                    items: ['reports', 'sports']
                }
            ]
        },
        'registrar': {
            sections: [
                {
                    title: 'System Management',
                    items: ['dashboard', 'academic-structure', 'users']
                },
                {
                    title: 'Student Operations',
                    items: ['admissions', 'students', 'documents']
                },
                {
                    title: 'Financial',
                    items: ['fees', 'payments']
                },
                {
                    title: 'Academic',
                    items: ['attendance', 'results', 'reports']
                }
            ]
        },
        'faculty': {
            sections: [
                {
                    title: 'Teaching',
                    items: ['dashboard', 'classes', 'timetable', 'lesson-plans', 'teaching-schedule']
                },
                {
                    title: 'Assessment',
                    items: ['attendance', 'assignments', 'results']
                },
                {
                    title: 'Students',
                    items: ['students', 'mentorship']
                },
                {
                    title: 'Personal',
                    items: ['leave', 'profile']
                }
            ]
        },
        'student': {
            sections: [
                {
                    title: 'Personal',
                    items: ['dashboard', 'profile', 'documents']
                },
                {
                    title: 'Academic',
                    items: ['attendance', 'results', 'subjects', 'timetable']
                },
                {
                    title: 'Financial',
                    items: ['fees', 'payments']
                },
                {
                    title: 'Services',
                    items: ['applications', 'library']
                }
            ]
        }
    },

    // Generate navigation HTML for a role
    generateNavigation(role, permissions = null) {
        const config = this.roles[role];
        if (!config) return '';

        let html = '';
        config.sections.forEach((section, sectionIndex) => {
            html += `<div class="nav-section">
                <div class="nav-section-title">${section.title}</div>`;
            
            section.items.forEach((moduleKey, itemIndex) => {
                const module = this.modules[moduleKey];
                if (!module) return;

                // Check permissions if provided
                if (permissions && !this.hasAccess(permissions, moduleKey)) return;

                const isFirst = sectionIndex === 0 && itemIndex === 0;
                const activeClass = isFirst ? ' active' : '';
                
                html += `<div class="nav-item${activeClass}" onclick="showSection('${moduleKey}')" data-module="${moduleKey}">
                    <span class="icon">${module.icon}</span> ${module.label}
                </div>`;
            });
            
            html += '</div>';
        });

        return html;
    },

    // Check if user has access to module
    hasAccess(permissions, moduleKey) {
        if (!permissions || !permissions[moduleKey]) return true;
        return permissions[moduleKey].can_view === true;
    },

    // Get all modules for a role
    getModulesForRole(role) {
        const config = this.roles[role];
        if (!config) return [];
        
        const modules = [];
        config.sections.forEach(section => {
            section.items.forEach(moduleKey => {
                if (this.modules[moduleKey]) {
                    modules.push({
                        key: moduleKey,
                        ...this.modules[moduleKey]
                    });
                }
            });
        });
        return modules;
    },

    // Validate navigation consistency
    validateNavigation() {
        const errors = [];
        const warnings = [];

        Object.entries(this.roles).forEach(([role, config]) => {
            config.sections.forEach(section => {
                section.items.forEach(moduleKey => {
                    if (!this.modules[moduleKey]) {
                        errors.push(`Role '${role}' references undefined module '${moduleKey}'`);
                    }
                });
            });
        });

        // Check for unused modules
        const usedModules = new Set();
        Object.values(this.roles).forEach(config => {
            config.sections.forEach(section => {
                section.items.forEach(key => usedModules.add(key));
            });
        });

        Object.keys(this.modules).forEach(key => {
            if (!usedModules.has(key)) {
                warnings.push(`Module '${key}' is defined but not used in any role`);
            }
        });

        return { errors, warnings, valid: errors.length === 0 };
    }
};

// Auto-validate on load
if (typeof console !== 'undefined') {
    const validation = NavigationConfig.validateNavigation();
    if (!validation.valid) {
        console.error('Navigation Configuration Errors:', validation.errors);
    }
    if (validation.warnings.length > 0) {
        console.warn('Navigation Configuration Warnings:', validation.warnings);
    }
}