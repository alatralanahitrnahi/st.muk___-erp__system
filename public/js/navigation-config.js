const NavigationConfig = {
    roles: {
        'super-admin': {
            sections: [
                {
                    title: 'System Administration',
                    items: [
                        { icon: '📊', label: 'Dashboard', section: 'dashboard' },
                        { icon: '⚙️', label: 'System Settings', section: 'settings' },
                        { icon: '🔐', label: 'Security', section: 'security' },
                        { icon: '📋', label: 'System Logs', section: 'logs' }
                    ]
                },
                {
                    title: 'User Management',
                    items: [
                        { icon: '👥', label: 'All Users', section: 'users' },
                        { icon: '🎭', label: 'Roles & Permissions', section: 'roles' },
                        { icon: '🏫', label: 'Institutions', section: 'institutions' }
                    ]
                },
                {
                    title: 'System Operations',
                    items: [
                        { icon: '💾', label: 'Database Management', section: 'database' },
                        { icon: '📊', label: 'Analytics', section: 'analytics' },
                        { icon: '🔧', label: 'Maintenance', section: 'maintenance' }
                    ]
                }
            ]
        },
        registrar: {
            sections: [
                {
                    title: 'System Management',
                    items: [
                        { icon: '📊', label: 'Dashboard', section: 'dashboard' },
                        { icon: '🏫', label: 'Academic Structure', section: 'academic-structure' },
                        { icon: '👥', label: 'User Management', section: 'users' }
                    ]
                },
                {
                    title: 'Student Operations',
                    items: [
                        { icon: '📝', label: 'Admissions', section: 'admissions' },
                        { icon: '🎓', label: 'Student Records', section: 'students' },
                        { icon: '📄', label: 'Document Verification', section: 'documents' }
                    ]
                },
                {
                    title: 'Financial',
                    items: [
                        { icon: '💰', label: 'Fee Management', section: 'fees' },
                        { icon: '💳', label: 'Payment Processing', section: 'payments' }
                    ]
                },
                {
                    title: 'Academic',
                    items: [
                        { icon: '📅', label: 'Attendance Reports', section: 'attendance' },
                        { icon: '📊', label: 'Results Management', section: 'results' },
                        { icon: '📋', label: 'NAAC Reports', section: 'reports' }
                    ]
                }
            ]
        },
        faculty: {
            sections: [
                {
                    title: 'Teaching',
                    items: [
                        { icon: '📊', label: 'Dashboard', section: 'dashboard' },
                        { icon: '📚', label: 'My Classes', section: 'classes' },
                        { icon: '🕐', label: 'Timetable', section: 'timetable' },
                        { icon: '📝', label: 'Lesson Plans', section: 'lesson-plans' },
                        { icon: '📋', label: 'Teaching Schedule', section: 'teaching-schedule' }
                    ]
                },
                {
                    title: 'Assessment',
                    items: [
                        { icon: '📅', label: 'Mark Attendance', section: 'attendance' },
                        { icon: '📋', label: 'Assignments', section: 'assignments' },
                        { icon: '📊', label: 'Results Entry', section: 'results' }
                    ]
                },
                {
                    title: 'Students',
                    items: [
                        { icon: '👥', label: 'My Students', section: 'students' },
                        { icon: '🤝', label: 'Mentorship', section: 'mentorship' }
                    ]
                },
                {
                    title: 'Personal',
                    items: [
                        { icon: '🏖️', label: 'Leave Management', section: 'leave' },
                        { icon: '👤', label: 'Profile', section: 'profile' }
                    ]
                }
            ]
        },
        principal: {
            sections: [
                {
                    title: 'Executive',
                    items: [
                        { icon: '📊', label: 'Overview', section: 'overview' },
                        { icon: '📈', label: 'Analytics', section: 'analytics' }
                    ]
                },
                {
                    title: 'Operations',
                    items: [
                        { icon: '🏢', label: 'Front Office', section: 'front-office' },
                        { icon: '🎓', label: 'Student Affairs', section: 'student-section' },
                        { icon: '💰', label: 'Financial Hub', section: 'accounts' },
                        { icon: '🔬', label: 'Resources', section: 'lab-resources' }
                    ]
                },
                {
                    title: 'Academic',
                    items: [
                        { icon: '👨🏫', label: 'Faculty', section: 'faculty' },
                        { icon: '📚', label: 'Programs', section: 'academics' },
                        { icon: '📖', label: 'Library', section: 'library' }
                    ]
                },
                {
                    title: 'Reports',
                    items: [
                        { icon: '📋', label: 'NAAC Compliance', section: 'reports' },
                        { icon: '⚽', label: 'Sports', section: 'sports' }
                    ]
                }
            ]
        },
    },
    
    generateNavigation(role) {
        const config = this.roles[role];
        if (!config) return '';
        
        let html = '';
        config.sections.forEach(section => {
            html += `<div class="nav-section"><div class="nav-section-title">${section.title}</div>`;
            section.items.forEach((item, index) => {
                const activeClass = index === 0 && section === config.sections[0] ? ' active' : '';
                html += `<div class="nav-item${activeClass}" onclick="showSection('${item.section}')">
                    <span class="icon">${item.icon}</span> ${item.label}
                </div>`;
            });
            html += '</div>';
        });
        return html;
    }
};