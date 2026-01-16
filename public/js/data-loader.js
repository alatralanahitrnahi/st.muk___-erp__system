// Data Loader - Department-Aware with Automatic Context Injection
const DataLoader = {
    loadingStates: new Map(),

    // Automatic department context injection
    getDepartmentContext() {
        return DepartmentSelector?.getActiveDepartmentId() || null;
    },

    setLoadingState(key, isLoading) {
        this.loadingStates.set(key, isLoading);
        window.dispatchEvent(new CustomEvent('dataLoadingStateChanged', {
            detail: { key, isLoading }
        }));
    },

    // Load students with department context
    async loadStudents(departmentId = null) {
        const deptId = departmentId || this.getDepartmentContext();
        const cacheKey = `students_${deptId || 'all'}`;

        this.setLoadingState(cacheKey, true);

        try {
            // Mock data for demo
            const mockStudents = [
                {
                    id: 1,
                    user: { name: 'Aarav Sharma', email: 'student1@pvgs.edu' },
                    admission_number: 'PVGS2024001',
                    program: { name: 'BSc Computer Science' },
                    status: 'active',
                    application_status: 'approved'
                },
                {
                    id: 2,
                    user: { name: 'Priya Patel', email: 'student2@pvgs.edu' },
                    admission_number: 'PVGS2024002',
                    program: { name: 'BCom' },
                    status: 'active',
                    application_status: 'approved'
                },
                {
                    id: 3,
                    user: { name: 'Rohan Kumar', email: 'student3@pvgs.edu' },
                    admission_number: 'PVGS2024003',
                    program: { name: 'BA English' },
                    status: 'pending',
                    application_status: 'under_review'
                }
            ];

            // Cache for offline use
            localStorage.setItem(cacheKey, JSON.stringify(mockStudents));

            return mockStudents;
        } catch (error) {
            console.error('Failed to load students:', error);

            // Fallback to cache
            const cached = localStorage.getItem(cacheKey);
            if (cached) {
                return JSON.parse(cached);
            }

            throw new Error('Unable to load student data.');
        } finally {
            this.setLoadingState(cacheKey, false);
        }
    },

    // Load attendance with department context
    async loadAttendance(dateFrom, dateTo, departmentId = null) {
        const deptId = departmentId || this.getDepartmentContext();
        const cacheKey = `attendance_${deptId || 'all'}_${dateFrom}_${dateTo}`;

        this.setLoadingState(cacheKey, true);

        try {
            const params = new URLSearchParams({
                date_from: dateFrom || this.getDefaultDateFrom(),
                date_to: dateTo || this.getDefaultDateTo()
            });

            if (deptId) params.append('department_id', deptId);

            const response = await fetch(`/api/attendance/report?${params}`, {
                headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
            });

            if (!response.ok) throw new Error('Failed to fetch attendance');

            const data = await response.json();
            const attendance = data.data || data;

            localStorage.setItem(cacheKey, JSON.stringify(attendance));

            return attendance;
        } catch (error) {
            console.error('Failed to load attendance:', error);

            const cached = localStorage.getItem(cacheKey);
            if (cached) {
                return JSON.parse(cached);
            }

            throw new Error('Unable to load attendance data.');
        } finally {
            this.setLoadingState(cacheKey, false);
        }
    },

    // Load results with department context
    async loadResults(academicYear, semester, departmentId = null) {
        const deptId = departmentId || this.getDepartmentContext();
        const cacheKey = `results_${deptId || 'all'}_${academicYear}_${semester}`;

        this.setLoadingState(cacheKey, true);

        try {
            const params = new URLSearchParams({
                academic_year: academicYear || this.getCurrentAcademicYear(),
                semester: semester || 1
            });

            if (deptId) params.append('department_id', deptId);

            const response = await fetch(`/api/results/report?${params}`, {
                headers: { 'Authorization': `Bearer ${localStorage.getItem('session_token')}` }
            });

            if (!response.ok) throw new Error('Failed to fetch results');

            const data = await response.json();
            const results = data.data || data;

            localStorage.setItem(cacheKey, JSON.stringify(results));

            return results;
        } catch (error) {
            console.error('Failed to load results:', error);

            const cached = localStorage.getItem(cacheKey);
            if (cached) {
                return JSON.parse(cached);
            }

            throw new Error('Unable to load results data.');
        } finally {
            this.setLoadingState(cacheKey, false);
        }
    },

    // Load fees with department context
    async loadFees(departmentId = null) {
        const deptId = departmentId || this.getDepartmentContext();
        const cacheKey = `fees_${deptId || 'all'}`;

        this.setLoadingState(cacheKey, true);

        try {
            const endpoint = deptId
                ? `/api/departments/${deptId}/fees`
                : '/api/fees';

            const response = await fetch(endpoint, {
                headers: { 'Authorization': `Bearer ${localStorage.getItem('session_token')}` }
            });

            if (!response.ok) throw new Error('Failed to fetch fees');

            const data = await response.json();
            const fees = data.data || data;

            localStorage.setItem(cacheKey, JSON.stringify(fees));

            return fees;
        } catch (error) {
            console.error('Failed to load fees:', error);

            const cached = localStorage.getItem(cacheKey);
            if (cached) {
                return JSON.parse(cached);
            }

            throw new Error('Unable to load fees data.');
        } finally {
            this.setLoadingState(cacheKey, false);
        }
    },

    // Load lesson plans with department context
    async loadLessonPlans(departmentId = null) {
        const deptId = departmentId || this.getDepartmentContext();
        const cacheKey = `lesson_plans_${deptId || 'all'}`;

        this.setLoadingState(cacheKey, true);

        try {
            const endpoint = deptId
                ? `/api/departments/${deptId}/lesson-plans`
                : '/api/lesson-plans';

            const response = await fetch(endpoint, {
                headers: { 'Authorization': `Bearer ${localStorage.getItem('session_token')}` }
            });

            if (!response.ok) throw new Error('Failed to fetch lesson plans');

            const data = await response.json();
            const plans = data.data || data;

            localStorage.setItem(cacheKey, JSON.stringify(plans));

            return plans;
        } catch (error) {
            console.error('Failed to load lesson plans:', error);

            const cached = localStorage.getItem(cacheKey);
            if (cached) {
                return JSON.parse(cached);
            }

            throw new Error('Unable to load lesson plans.');
        } finally {
            this.setLoadingState(cacheKey, false);
        }
    },

    // Load dashboard stats with department context
    async loadDashboardStats(departmentId = null) {
        const deptId = departmentId || this.getDepartmentContext();
        const cacheKey = `dashboard_${deptId || 'all'}`;

        this.setLoadingState(cacheKey, true);

        try {
            // Mock dashboard stats for demo
            const mockStats = {
                total_students: 1250,
                pending_admissions: 45,
                fee_collected: 2500000, // ₹25L
                pending_fees: 750000    // ₹7.5L
            };

            localStorage.setItem(cacheKey, JSON.stringify(mockStats));

            return mockStats;
        } catch (error) {
            console.error('Failed to load dashboard stats:', error);

            const cached = localStorage.getItem(cacheKey);
            if (cached) {
                return JSON.parse(cached);
            }

            return this.getDefaultStats();
        } finally {
            this.setLoadingState(cacheKey, false);
        }
    },

    // Reload all data for current department
    async reloadAll(departmentId = null) {
        const deptId = departmentId || this.getDepartmentContext();

        const promises = [
            this.loadStudents(deptId),
            this.loadDashboardStats(deptId)
        ];

        try {
            await Promise.all(promises);
            window.dispatchEvent(new CustomEvent('dataReloaded', {
                detail: { departmentId: deptId }
            }));
        } catch (error) {
            console.error('Failed to reload data:', error);
        }
    },

    // Helper: Get default date range (last 30 days)
    getDefaultDateFrom() {
        const date = new Date();
        date.setDate(date.getDate() - 30);
        return date.toISOString().split('T')[0];
    },

    getDefaultDateTo() {
        return new Date().toISOString().split('T')[0];
    },

    // Helper: Get current academic year
    getCurrentAcademicYear() {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth();
        return month >= 6 ? `${year}-${year + 1}` : `${year - 1}-${year}`;
    },

    // Default stats fallback
    getDefaultStats() {
        return {
            total_students: 0,
            pending_admissions: 0,
            fee_collected: 0,
            pending_fees: 0
        };
    }
};

// UI Renderer - Handles table and card rendering
const UIRenderer = {
    // Render students table
    renderStudentsTable(students) {
        if (!students || students.length === 0) {
            return '<div style="padding: 2rem; text-align: center; color: #64748b;">No students found. Add students to get started.</div>';
        }

        return `
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 0.75rem; text-align: left;">Name</th>
                        <th style="padding: 0.75rem; text-align: left;">Admission No</th>
                        <th style="padding: 0.75rem; text-align: left;">Program</th>
                        <th style="padding: 0.75rem; text-align: left;">Status</th>
                        <th style="padding: 0.75rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${students.map(s => `
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 0.75rem;">${s.user?.name || s.name || 'N/A'}</td>
                            <td style="padding: 0.75rem;">${s.admission_number || 'Pending'}</td>
                            <td style="padding: 0.75rem;">${s.program?.name || s.program || 'N/A'}</td>
                            <td style="padding: 0.75rem;">
                                <span style="background: ${this.getStatusColor(s.application_status || s.status)}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                    ${s.application_status || s.status || 'active'}
                                </span>
                            </td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <button class="btn" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;" onclick="viewStudent(${s.id})">View</button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    },

    // Render attendance table
    renderAttendanceTable(attendance) {
        if (!attendance || attendance.length === 0) {
            return '<div style="padding: 2rem; text-align: center; color: #64748b;">No attendance records found for the selected period.</div>';
        }

        return `
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 0.75rem; text-align: left;">Student</th>
                        <th style="padding: 0.75rem; text-align: left;">Admission No</th>
                        <th style="padding: 0.75rem; text-align: center;">Present</th>
                        <th style="padding: 0.75rem; text-align: center;">Total</th>
                        <th style="padding: 0.75rem; text-align: center;">Percentage</th>
                        <th style="padding: 0.75rem; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${attendance.map(a => `
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 0.75rem;">${a.student_name || 'N/A'}</td>
                            <td style="padding: 0.75rem;">${a.admission_number || 'N/A'}</td>
                            <td style="padding: 0.75rem; text-align: center;">${a.present_count || 0}</td>
                            <td style="padding: 0.75rem; text-align: center;">${a.total_classes || 0}</td>
                            <td style="padding: 0.75rem; text-align: center; font-weight: 600;">${a.percentage || 0}%</td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <span style="background: ${this.getAttendanceColor(a.percentage)}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                    ${a.status || this.getAttendanceStatus(a.percentage)}
                                </span>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    },

    // Render results table
    renderResultsTable(results) {
        if (!results || results.length === 0) {
            return '<div style="padding: 2rem; text-align: center; color: #64748b;">No results found for the selected period.</div>';
        }

        return `
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 0.75rem; text-align: left;">Student</th>
                        <th style="padding: 0.75rem; text-align: left;">Admission No</th>
                        <th style="padding: 0.75rem; text-align: center;">Subjects</th>
                        <th style="padding: 0.75rem; text-align: center;">Passed</th>
                        <th style="padding: 0.75rem; text-align: center;">Average</th>
                        <th style="padding: 0.75rem; text-align: center;">Grade</th>
                        <th style="padding: 0.75rem; text-align: center;">Result</th>
                    </tr>
                </thead>
                <tbody>
                    ${results.map(r => `
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 0.75rem;">${r.student_name || 'N/A'}</td>
                            <td style="padding: 0.75rem;">${r.admission_number || 'N/A'}</td>
                            <td style="padding: 0.75rem; text-align: center;">${r.total_subjects || 0}</td>
                            <td style="padding: 0.75rem; text-align: center;">${r.passed_subjects || 0}</td>
                            <td style="padding: 0.75rem; text-align: center; font-weight: 600;">${r.average_percentage || 0}%</td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <span style="background: #2563eb; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                    ${r.grade || 'N/A'}
                                </span>
                            </td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <span style="background: ${r.overall_result === 'Pass' ? '#10b981' : '#f59e0b'}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                    ${r.overall_result || 'N/A'}
                                </span>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    },

    // Helper: Get status color
    getStatusColor(status) {
        const colors = {
            'active': '#10b981',
            'approved': '#10b981',
            'pending': '#f59e0b',
            'under_review': '#3b82f6',
            'rejected': '#ef4444',
            'inactive': '#6b7280',
            'graduated': '#8b5cf6'
        };
        return colors[status] || '#6b7280';
    },

    // Helper: Get attendance color
    getAttendanceColor(percentage) {
        if (percentage >= 75) return '#10b981';
        if (percentage >= 60) return '#f59e0b';
        return '#ef4444';
    },

    // Helper: Get attendance status
    getAttendanceStatus(percentage) {
        if (percentage >= 75) return 'Good';
        if (percentage >= 60) return 'Average';
        return 'Poor';
    },

    // Render error message
    renderError(message) {
        return `
            <div style="padding: 2rem; text-align: center; color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px;">
                <strong>⚠️ Error:</strong> ${message}
            </div>
        `;
    },

    // Render loading state
    renderLoading() {
        return `
            <div style="padding: 2rem; text-align: center; color: #64748b;">
                <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top-color: #2563eb; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 1rem;">Loading data...</p>
            </div>
        `;
    }
};

// Listen for department changes and reload data
window.addEventListener('departmentChanged', async (event) => {
    await DataLoader.reloadAll(event.detail.departmentId);
});

// Listen for explicit data reload requests
window.addEventListener('departmentDataReload', async () => {
    await DataLoader.reloadAll();
});

// Listen for module navigation and load appropriate data
window.addEventListener('loadModuleData', async (event) => {
    const { module, departmentId } = event.detail;

    try {
        switch (module) {
            case 'students':
                await DataLoader.loadStudents(departmentId);
                break;
            case 'attendance':
                await DataLoader.loadAttendance(null, null, departmentId);
                break;
            case 'results':
                await DataLoader.loadResults(null, null, departmentId);
                break;
            case 'fees':
                await DataLoader.loadFees(departmentId);
                break;
            case 'lesson_plans':
                await DataLoader.loadLessonPlans(departmentId);
                break;
            case 'dashboard':
                await DataLoader.loadDashboardStats(departmentId);
                break;
        }
    } catch (error) {
        console.error(`Failed to load ${module} data:`, error);
    }
});
