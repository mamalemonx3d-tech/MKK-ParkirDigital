export default defineNuxtRouteMiddleware((to) => {
  if (import.meta.server) return

  const token = localStorage.getItem('auth_token')
  const rawUser = localStorage.getItem('auth_user')

  if (!token || !rawUser) {
    return navigateTo('/login')
  }

  try {
    const user = JSON.parse(rawUser)
    const role = user?.role

    // Proteksi rute admin
    if (to.path.startsWith('/admin') && role !== 'super_admin') {
      return navigateTo('/petugas')
    }

    // Petugas tidak dibatasi rute /petugas
  } catch {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
    return navigateTo('/login')
  }
})
