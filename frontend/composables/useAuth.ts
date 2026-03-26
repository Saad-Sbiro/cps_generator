import { ref } from 'vue'

export const useAuth = () => {
  const token = useCookie('auth_token')
  const user = useState('user', () => null)
  const api = useApi()

  const setAuth = (newToken: string, userData: any, remember: boolean = false) => {
    // Dynamically set cookie with appropriate maxAge
    const opts: any = { path: '/' }
    if (remember) {
      opts.maxAge = 60 * 60 * 24 * 30 // 30 days
    } else {
      opts.maxAge = undefined // Session cookie
    }
    const cookie = useCookie('auth_token', opts)
    cookie.value = newToken
    token.value = newToken
    user.value = userData
  }

  const login = async (credentials: any) => {
    const res = await api.post('/auth/login', credentials)
    if (res.token && res.user) {
      setAuth(res.token, res.user, credentials.remember)
      return true
    }
    return false
  }

  const register = async (data: any) => {
    const res = await api.post('/auth/register', data)
    if (res.token && res.user) {
      setAuth(res.token, res.user, data.remember)
      return true
    }
    return false
  }

  const logout = async () => {
    try {
      await api.post('/auth/logout', {})
    } catch (e) {
      // Ignore errors on logout
    }
    token.value = null
    user.value = null
  }

  const fetchUser = async () => {
    if (!token.value) return null
    try {
      const res = await api.get('/auth/me')
      user.value = res
      return res
    } catch (e) {
      token.value = null
      user.value = null
      return null
    }
  }

  const forgotPassword = async (email: string) => {
    return await api.post('/auth/forgot-password', { email })
  }

  const resetPassword = async (data: any) => {
    return await api.post('/auth/reset-password', data)
  }

  return {
    token,
    user,
    login,
    register,
    logout,
    fetchUser,
    forgotPassword,
    resetPassword,
    isAuthenticated: computed(() => !!token.value),
  }
}
