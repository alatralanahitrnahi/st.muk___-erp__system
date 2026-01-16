import axios from 'axios';

const api = axios.create({
  baseURL: '/direct-api.php',
  headers: { 'Content-Type': 'application/json' }
});

api.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

export const auth = {
  login: (email, password) => api.post('/api/login', { email, password }),
  getUser: () => api.get('/api/user')
};

export const departments = {
  list: () => api.get('/api/departments')
};

export const students = {
  list: (deptId) => api.get('/api/students', { params: { department_id: deptId }})
};

export const attendance = {
  list: (deptId, dateFrom, dateTo) => api.get('/api/attendance', { 
    params: { department_id: deptId, date_from: dateFrom, date_to: dateTo }
  }),
  mark: (data) => api.post('/api/attendance', data)
};

export default api;
