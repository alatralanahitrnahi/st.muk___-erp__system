import axios from 'axios';

const api = axios.create({
  baseURL: '/direct-api.php',
  headers: { 'Content-Type': 'application/json' }
});

api.interceptors.request.use(config => {
  console.log('API Request:', config.method, config.url);
  const token = localStorage.getItem('token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

api.interceptors.response.use(
  response => {
    console.log('API Response:', response.status, response.data);
    return response;
  },
  error => {
    console.error('API Error:', error.response?.status, error.response?.data || error.message);
    return Promise.reject(error);
  }
);

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
