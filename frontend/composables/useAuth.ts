export interface AuthUser {
  id: number
  name: string
  email: string
  no_hp?: string
  role: 'super_admin' | 'petugas'
}

export const useAuth = () => {
  const user = useState<AuthUser | null>('auth_user', () => null)
  const token = useState<string | null>('auth_token', () => null)
  const api = useApi()

  const initAuth = () => {
    if (import.meta.client) {
      const storedToken = localStorage.getItem('auth_token')
      const storedUser = localStorage.getItem('auth_user')

      if (storedToken) {
        token.value = storedToken
      }
      if (storedUser) {
        try {
          user.value = JSON.parse(storedUser)
        } catch {
          user.value = null
        }
      }
    }
  }

  const setAuth = (newToken: string, newUser: AuthUser) => {
    token.value = newToken
    user.value = newUser

    if (import.meta.client) {
      localStorage.setItem('auth_token', newToken)
      localStorage.setItem('auth_user', JSON.stringify(newUser))
    }
  }

  const logout = async () => {
    try {
      await api.post('/logout')
    } catch {
      // Abaikan jika token sudah expired
    } finally {
      token.value = null
      user.value = null

      if (import.meta.client) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
        navigateTo('/login')
      }
    }
  }

  const isAuthenticated = computed(() => !!token.value)
  const isSuperAdmin = computed(() => user.value?.role === 'super_admin')
  const isPetugas = computed(() => user.value?.role === 'petugas')

  return {
    user,
    token,
    initAuth,
    setAuth,
    logout,
    isAuthenticated,
    isSuperAdmin,
    isPetugas,
  }
}
