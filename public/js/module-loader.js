// Module Loader - Department-Aware Content Loading
(function() {
    'use strict';
    
    const ModuleLoader = {
        currentModule: null,
        loadingStates: new Map(),
        
        // Module content templates
        modules: {
            dashboard: {
                title: 'Dashboard',
                load: async (departmentId) => {
                    const stats = await apiCall(`/api/v1/departments/${departmentId}/dashboard`);
                    return `
                        <h2>Dashboard Overview</h2>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-number">${stats.total_students || 0}</div>
                                <div class="stat-label">Total Students</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number">${stats.attendance_rate || 0}%</div>
                                <div class="stat-label">Attendance Rate</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number">₹${stats.fees_collected || 0}</div>
                                <div class="stat-label">Fees Collected</div>
                            </div>
                        </div>
                    `;
                }
            },
            
            students: {
                title: 'Students',
                load: async (departmentId) => {
                    const students = await apiCall(`/api/v1/departments/${departmentId}/students`);
                    return `
                        <h2>Student Management</h2>
                        <div class="module-content">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Program</th>
                                        <th>Year</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${(students.data || []).map(s => `
                                        <tr>
                                            <td>${s.id}</td>
                                            <td>${s.name}</td>
                                            <td>${s.program}</td>
                                            <td>${s.year}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                }
            },
            
            attendance: {
                title: 'Attendance',
                load: async (departmentId) => {
                    const attendance = await apiCall(`/api/v1/departments/${departmentId}/attendance`);
                    return `
                        <h2>Attendance Records</h2>
                        <div class="module-content">
                            <p>Attendance data for department ${departmentId}</p>
                        </div>
                    `;
                }
            },
            
            results: {
                title: 'Results',
                load: async (departmentId) => {
                    return `
                        <h2>Examination Results</h2>
                        <div class="module-content">
                            <p>Results data for department ${departmentId}</p>
                        </div>
                    `;
                }
            },
            
            fees: {
                title: 'Fee Management',
                load: async (departmentId) => {
                    return `
                        <h2>Fee Management</h2>
                        <div class="module-content">
                            <p>Fee data for department ${departmentId}</p>
                        </div>
                    `;
                }
            },
            
            reports: {
                title: 'Reports',
                load: async (departmentId) => {
                    return `
                        <h2>Reports & Analytics</h2>
                        <div class="module-content">
                            <button class="btn" onclick="ModuleLoader.generateReport('students', ${departmentId})">Student Report</button>
                            <button class="btn" onclick="ModuleLoader.generateReport('attendance', ${departmentId})">Attendance Report</button>
                            <button class="btn" onclick="ModuleLoader.generateReport('fees', ${departmentId})">Fee Report</button>
                        </div>
                    `;
                }
            }
        },
        
        async loadModule(moduleKey, departmentId) {
            const container = document.querySelector('.main-content') || document.getElementById('main-content');
            if (!container) {
                console.error('Main content container not found');
                return;
            }
            
            // Show loading state
            container.innerHTML = '<div class="loading-spinner">Loading...</div>';
            this.currentModule = moduleKey;
            
            try {
                const module = this.modules[moduleKey];
                if (!module) {
                    throw new Error(`Module ${moduleKey} not found`);
                }
                
                // Load module content
                const content = await module.load(departmentId);
                
                // Only update if this is still the current module
                if (this.currentModule === moduleKey) {
                    container.innerHTML = content;
                }
                
            } catch (error) {
                console.error(`Failed to load module ${moduleKey}:`, error);
                container.innerHTML = `
                    <div class="error-message">
                        <h3>Unable to load ${moduleKey}</h3>
                        <p>${error.message}</p>
                        <button class="btn" onclick="ModuleLoader.loadModule('${moduleKey}', ${departmentId})">Retry</button>
                    </div>
                `;
            }
        },
        
        async generateReport(type, departmentId) {
            alert(`Generating ${type} report for department ${departmentId}...`);
        }
    };
    
    // Global function for navigation clicks
    window.showSection = function(sectionId) {
        const departmentId = getActiveDepartment();
        ModuleLoader.loadModule(sectionId, departmentId);
        
        // Update active nav item
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
        });
        if (event && event.target) {
            const navItem = event.target.closest('.nav-item');
            if (navItem) navItem.classList.add('active');
        }
    };
    
    // Listen for department changes
    document.addEventListener('departmentChanged', (e) => {
        if (ModuleLoader.currentModule) {
            ModuleLoader.loadModule(ModuleLoader.currentModule, e.detail.departmentId);
        }
    });
    
    // Export
    window.ModuleLoader = ModuleLoader;
    
})();
