// Department Theme Application
(function() {
    'use strict';
    
    function applyDepartmentTheme(departmentId) {
        document.body.setAttribute('data-department', departmentId);
        
        // Store for persistence
        localStorage.setItem('active_department_theme', departmentId);
        
        // Announce to screen readers
        const announcement = document.getElementById('theme-announcement');
        if (announcement) {
            const deptNames = { 1: 'Science', 2: 'Commerce', 3: 'Arts' };
            announcement.textContent = `Department theme changed to ${deptNames[departmentId] || 'Unknown'}`;
        }
    }
    
    // Apply theme on page load
    function initTheme() {
        const userData = JSON.parse(localStorage.getItem('user') || '{}');
        const departmentId = localStorage.getItem('active_department_id') || 
                           userData.primary_department_id || 1;
        applyDepartmentTheme(departmentId);
    }
    
    // Listen for department changes
    document.addEventListener('departmentChanged', (e) => {
        applyDepartmentTheme(e.detail.departmentId);
    });
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }
    
    window.applyDepartmentTheme = applyDepartmentTheme;
})();
