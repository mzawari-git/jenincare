import apiClient from './client'

export const authApi = {
  login: (payload) => apiClient.post('/auth/login', payload),
  me: () => apiClient.get('/auth/me'),
  logout: () => apiClient.post('/auth/logout'),
  updateProfile: (payload) => apiClient.put('/auth/profile', payload)
}

export const scansApi = {
  list: (params = {}) => apiClient.get('/scans', { params }),
  detail: (id) => apiClient.get(`/scans/${id}`),
  approve: (id, payload = {}) => apiClient.post(`/scans/${id}/approve`, payload),
  reject: (id, payload = {}) => apiClient.post(`/scans/${id}/reject`, payload),
  generatePin: (id) => apiClient.post(`/scans/${id}/pin`),
  batchApprove: (payload) => apiClient.post('/scans/batch-approve', payload),
  broadcast: (id, payload) => apiClient.post(`/scans/${id}/broadcast`, payload),
  stats: (params = {}) => apiClient.get('/scans/stats', { params }),
  export: (params = {}) => apiClient.get('/scans/export', { params, responseType: 'blob' })
}

export const providersApi = {
  list: () => apiClient.get('/ai-providers'),
  create: (payload) => apiClient.post('/ai-providers', payload),
  detail: (id) => apiClient.get(`/ai-providers/${id}`),
  activate: (id, payload) => apiClient.post(`/ai-providers/${id}/toggle`, payload),
  update: (id, payload) => apiClient.put(`/ai-providers/${id}`, payload),
  testConnection: (id) => apiClient.post(`/ai-providers/${id}/test`),
  quotaStatus: () => apiClient.get('/ai-providers/quota-status')
}

export const promptsApi = {
  list: (params = {}) => apiClient.get('/prompts', { params }),
  detail: (id) => apiClient.get(`/prompts/${id}`),
  create: (payload) => apiClient.post('/prompts', payload),
  update: (id, payload) => apiClient.put(`/prompts/${id}`, payload),
  delete: (id) => apiClient.delete(`/prompts/${id}`),
  history: (id) => apiClient.get(`/prompts/${id}/history`),
  preview: (payload) => apiClient.post('/prompts/preview', payload)
}

export const whiteLabelApi = {
  get: () => apiClient.get('/white-label'),
  update: (payload) => apiClient.put('/white-label', payload),
  uploadLogo: (formData) => apiClient.post('/white-label/logo', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
}

export const dashboardApi = {
  stats: () => apiClient.get('/dashboard/stats'),
  recentScans: (limit = 5) => apiClient.get('/dashboard/recent-scans', { params: { limit } }),
  chartData: (params = {}) => apiClient.get('/dashboard/charts', { params }),
  quotaUsage: () => apiClient.get('/dashboard/quota-usage')
}

export const productsApi = {
  list: (params = {}) => apiClient.get('/products', { params }),
  detail: (id) => apiClient.get(`/products/${id}`),
  create: (payload) => apiClient.post('/products', payload),
  update: (id, payload) => apiClient.put(`/products/${id}`, payload),
  delete: (id) => apiClient.delete(`/products/${id}`),
  linkDefect: (productId, defectType) => apiClient.post(`/products/${productId}/link-defect`, { defect_type: defectType }),
  unlinkDefect: (productId, defectType) => apiClient.delete(`/products/${productId}/link-defect/${defectType}`),
  recommendationRules: () => apiClient.get('/products/recommendation-rules'),
  updateRecommendationRules: (payload) => apiClient.put('/products/recommendation-rules', payload)
}

export default {
  auth: authApi,
  scans: scansApi,
  providers: providersApi,
  prompts: promptsApi,
  whiteLabel: whiteLabelApi,
  dashboard: dashboardApi,
  products: productsApi
}
