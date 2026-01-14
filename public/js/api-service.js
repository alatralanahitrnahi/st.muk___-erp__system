// API Service for PVGS ERP System
const API_BASE = window.location.origin;

const ApiService = {
    // Get auth token
    getToken() {
        return localStorage.getItem('session_token');
    },

    // Get current user
    getCurrentUser() {
        const session = localStorage.getItem('secure_session');
        return session ? JSON.parse(session) : null;
    },

    // Generic fetch wrapper
    async fetch(endpoint, options = {}) {
        const token = this.getToken();
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...(token && { 'Authorization': `Bearer ${token}` }),
            ...options.headers
        };

        try {
            const response = await fetch(`${API_BASE}${endpoint}`, {
                ...options,
                headers
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    // Students API
    students: {
        getAll: () => ApiService.fetch('/api/students'),
        getById: (id) => ApiService.fetch(`/api/students/${id}`),
        create: (data) => ApiService.fetch('/api/students', { method: 'POST', body: JSON.stringify(data) }),
        update: (id, data) => ApiService.fetch(`/api/students/${id}`, { method: 'PUT', body: JSON.stringify(data) })
    },

    // Attendance API
    attendance: {
        getAll: () => ApiService.fetch('/api/attendance'),
        mark: (data) => ApiService.fetch('/api/attendance/mark', { method: 'POST', body: JSON.stringify(data) }),
        getReport: (params) => ApiService.fetch(`/api/attendance/report?${new URLSearchParams(params)}`)
    },

    // Results API
    results: {
        getReport: (params) => ApiService.fetch(`/api/results/report?${new URLSearchParams(params)}`),
        getByStudent: (id) => ApiService.fetch(`/api/students/${id}/results`),
        enter: (data) => ApiService.fetch('/api/results/enter', { method: 'POST', body: JSON.stringify(data) })
    },

    // Reports API
    reports: {
        dashboard: () => ApiService.fetch('/api/reports/dashboard'),
        students: () => ApiService.fetch('/api/reports/students'),
        fees: () => ApiService.fetch('/api/reports/fees'),
        attendance: () => ApiService.fetch('/api/reports/attendance'),
        naac: () => ApiService.fetch('/api/reports/naac')
    },

    // Lesson Plans API
    lessonPlans: {
        getAll: (params) => ApiService.fetch(`/api/lesson-plans?${new URLSearchParams(params)}`),
        getById: (id) => ApiService.fetch(`/api/lesson-plans/${id}`),
        create: (data) => ApiService.fetch('/api/lesson-plans', { method: 'POST', body: JSON.stringify(data) }),
        update: (id, data) => ApiService.fetch(`/api/lesson-plans/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
        submit: (id) => ApiService.fetch(`/api/lesson-plans/${id}/submit`, { method: 'POST' }),
        approve: (id) => ApiService.fetch(`/api/lesson-plans/${id}/approve`, { method: 'POST' })
    },

};