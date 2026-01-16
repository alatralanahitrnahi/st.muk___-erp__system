import api from './api';

const workflowApi = {
  list: (filters = {}) => api.get('/workflow-api.php/workflows', { params: filters }),
  get: (id) => api.get(`/workflow-api.php/workflows/${id}`),
  create: (data) => api.post('/workflow-api.php/workflows', data),
  transition: (id, action, comments = '') => 
    api.post(`/workflow-api.php/workflows/${id}/transition`, { action, comments })
};

export default workflowApi;
