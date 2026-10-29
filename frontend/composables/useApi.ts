import axios from 'axios'

export const useApi = () => {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase || 'http://127.0.0.1:8000/api'

  const getHeaders = () => {
    const headers: Record<string, string> = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    }

    if (import.meta.client) {
      const token = localStorage.getItem('auth_token')
      if (token) {
        headers['Authorization'] = `Bearer ${token}`
      }
    }

    return headers
  }

  const client = axios.create({
    baseURL: apiBase,
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
      if (error.response?.status === 401 && import.meta.client) {
        // Token kadaluarsa atau tidak valid
        if (!window.location.pathname.startsWith('/login') && !window.location.pathname.startsWith('/user')) {
          localStorage.removeItem('auth_token')
          localStorage.removeItem('auth_user')
          window.location.href = '/login'
        }
      }
      return Promise.reject(error)
    }
  )

  return client
}
