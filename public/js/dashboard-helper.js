// Dashboard Title Helper for Frontend
const DashboardTitles = {
    'super-admin': {
        title: '⚡ PVGS Super Admin Dashboard',
        subtitle: 'Complete System Control',
        defaultUser: 'System Administrator'
    },
    'principal': {
        title: '👑 PVGS Principal Dashboard',
        subtitle: 'Executive Overview',
        defaultUser: 'Principal'
    },
    'registrar': {
        title: '📝 PVGS Registrar Dashboard',
        subtitle: 'Student Records & Operations',
        defaultUser: 'Registrar'
    },
    'faculty': {
        title: '👨🏫 PVGS Faculty Portal',
        subtitle: 'Teaching & Assessment',
        defaultUser: 'Faculty Member'
    },
    'student': {
        title: '🎓 PVGS Student Portal',
        subtitle: 'Academic Progress',
        defaultUser: 'Student'
    }
};

function setDashboardTitle(userType) {
    const config = DashboardTitles[userType];
    if (!config) return;
    
    document.title = config.title.replace(/[^\w\s-]/g, '').trim() + ' - PVGS ERP';
    
    const headerTitle = document.querySelector('.header h1');
    if (headerTitle) headerTitle.textContent = config.title;
    
    const mainTitle = document.querySelector('.main-content h2');
    if (mainTitle) mainTitle.textContent = config.subtitle;
}