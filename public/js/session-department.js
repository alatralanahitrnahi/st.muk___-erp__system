// Session Validation & Department Integration Module
// Include this in all dashboard pages

(function() {
    'use strict';
    
    // Session Validation
    function validateSession(requiredRole) {
        const user = JSON.parse(localStorage.getItem('user') || 'null');
        
        if (!user) {
            window.location.href = '/secure_login.html';
            return false;
        }
        
        // Check session expiry
        const sessionExpiry = localStorage.getItem('session_expiry');
        if (sessionExpiry && Date.now() > parseInt(sessionExpiry)) {
            localStorage.clear();
            sessionStorage.clear();
            alert('Session expired. Please login again.');
            window.location.href = '/secure_login.html';
            return false;
        }
        
        // Check role access
        if (requiredRole && user.role !== requiredRole && user.role !== 'super-admin') {
            alert('Access denied. Insufficient permissions.');
            window.location.href = '/secure_login.html';
            return false;
        }
        
        // Extend session (15 minutes)
        localStorage.setItem('session_expiry', Date.now() + (15 * 60 * 1000));
        
        return true;
    }
    
    // Department Selector Integration
    function initDepartmentSelector() {
        const container = document.getElementById('department-selector-container');
        if (!container) return;
        
        fetch('/components/department-selector.html')
            .then(r => r.text())
            .then(html => {
                container.innerHTML = html;
                
                // Initialize with user's primary department
                const userData = JSON.parse(localStorage.getItem('user') || '{}');
                const primaryDept = userData.primary_department_id || 1;
                
                if (window.setActiveDepartment) {
                    window.setActiveDepartment(primaryDept);
                }
                
                // Listen for department changes
                document.addEventListener('departmentChanged', (e) => {
                    const newDeptId = e.detail.departmentId;
                    localStorage.setItem('active_department_id', newDeptId);
                    
                    // Reload dashboard data
                    if (typeof reloadDashboardData === 'function') {
                        reloadDashboardData(newDeptId);
                    }
                });
            })
            .catch(err => console.error('Failed to load department selector:', err));
    }
    
    // API Helper with Department Context
    window.apiCall = async function(endpoint, options = {}) {
        const departmentId = localStorage.getItem('active_department_id') || 
                           JSON.parse(localStorage.getItem('user') || '{}').primary_department_id || 1;
        
        // Add department context to URL if it's a department-scoped endpoint
        let url = endpoint;
        if (endpoint.includes('/api/v1/departments/')) {
            url = endpoint.replace('{departmentId}', departmentId);
        }
        
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                }
            });
            
            if (response.status === 401) {
                localStorage.clear();
                window.location.href = '/secure_login.html';
                throw new Error('Session expired');
            }
            
            if (response.status === 403) {
                throw new Error('Access denied to this department');
            }
            
            if (!response.ok) {
                throw new Error(`API Error: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API call failed:', error);
            
            // Fallback to mock data during transition
            if (typeof getMockData === 'function') {
                console.warn('Using mock data fallback');
                return getMockData(endpoint);
            }
            
            throw error;
        }
    };
    
    // Get active department ID
    window.getActiveDepartment = function() {
        return localStorage.getItem('active_department_id') || 
               JSON.parse(localStorage.getItem('user') || '{}').primary_department_id || 1;
    };
    
    // Export functions
    window.validateSession = validateSession;
    window.initDepartmentSelector = initDepartmentSelector;
    
})();
