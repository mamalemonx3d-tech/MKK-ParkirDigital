import axios from 'axios'

export const useApi = () => {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase || 'http://127.0.0.1:8000/api'

  const client = axios.create({
    baseURL: apiBase,
    timeout: 5000, // Maksimal 5 detik agar UI tidak hang jika backend mati
  })

  client.interceptors.request.use((req) => {
    if (import.meta.client) {
      const token = localStorage.getItem('auth_token')
      if (token) {
        req.headers['Authorization'] = `Bearer ${token}`
      }
    }
    req.headers['Accept'] = 'application/json'
    return req
  })

  client.interceptors.response.use(
    (response) => response,
    (error) => {
      if (import.meta.client) {
        if (error.response?.status === 401) {
          if (!window.location.pathname.startsWith('/login') && !window.location.pathname.startsWith('/user')) {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('auth_user')
            window.location.href = '/login'
          }
        }

        // Tangani error koneksi backend mati atau timeout
        if (error.code === 'ERR_NETWORK' || error.message?.includes('Network Error')) {
          error.customMessage = 'Server backend belum menyala. Silakan jalankan `php artisan serve` di folder backend (port 8000).'
        } else if (error.code === 'ECONNABORTED') {
          error.customMessage = 'Koneksi ke server memakan waktu terlalu lama (timeout).'
        }
      }
      return Promise.reject(error)
    }
  )

  return client
}
