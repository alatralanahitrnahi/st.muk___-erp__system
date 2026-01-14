// Data Loader - Replaces mock data with real API calls
const DataLoader = {
    // Load students with error handling
    async loadStudents() {
        try {
            const response = await ApiService.students.getAll();
            return response.data || response;
        } catch (error) {
            console.error('Failed to load students:', error);
            throw new Error('Unable to load student data. Please check your connection.');
        }
    },

    // Load attendance with date range
    async loadAttendance(dateFrom, dateTo) {
        try {
            const params = {
                date_from: dateFrom || this.getDefaultDateFrom(),
                date_to: dateTo || this.getDefaultDateTo()
            };
            const response = await ApiService.attendance.getReport(params);
            return response.data || response;
        } catch (error) {
            console.error('Failed to load attendance:', error);
            throw new Error('Unable to load attendance data.');
        }
    },

    // Load results
    async loadResults(academicYear, semester) {
        try {
            const params = {
                academic_year: academicYear || this.getCurrentAcademicYear(),
                semester: semester || 1
            };
            const response = await ApiService.results.getReport(params);
            return response.data || response;
        } catch (error) {
            console.error('Failed to load results:', error);
            throw new Error('Unable to load results data.');
        }
    },

    // Load dashboard stats
    async loadDashboardStats() {
        try {
            const response = await ApiService.reports.dashboard();
            return response.data || response;
        } catch (error) {
            console.error('Failed to load dashboard stats:', error);
            return this.getDefaultStats();
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
