import axios from 'axios'
import Swal from 'sweetalert2'

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api/admin',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Accept-Language': 'ar',
    'X-Locale': 'ar'
  }
})

apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('admin_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    config.headers['X-Locale'] = 'ar'
    return config
  },
  (error) => Promise.reject(error)
)

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('admin_token')
      const loginPath = import.meta.env.VITE_LOGIN_PATH || '/skin-admin/#/login'
      window.location.href = loginPath
    }

    if (error.response?.status === 429) {
      Swal.fire({
        title: 'طلبات كثيرة جداً',
        text: 'الرجاء الانتظار قليلاً قبل المحاولة مرة أخرى',
        icon: 'warning',
        confirmButtonText: 'حسناً',
        confirmButtonColor: '#1a8870'
      })
    }

    return Promise.reject(error)
  }
)

export default apiClient
