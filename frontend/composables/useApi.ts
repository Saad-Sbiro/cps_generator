export const useApi = () => {
  const config = useRuntimeConfig()
  const baseURL = 'http://127.0.0.1:8000/api'
  const token = useCookie('auth_token')
  let addToast: any = null
  try {
    const toastProps = useToast()
    addToast = toastProps.addToast
  } catch (e) {
  
  }

  const getHeaders = (): Record<string, string> => {
    const headers: Record<string, string> = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    }
    if (token.value) {
      headers['Authorization'] = `Bearer ${token.value}`
    }
    return headers
  }

  const handleResponseError = ({ response }: any) => {
    if (response.status === 403 || response.status === 401) {
      if (import.meta.client && addToast) {
        addToast({ 
          title: 'Accès Refusé', 
          description: response._data?.message || "Vous n'avez pas l'autorisation d'effectuer cette action.", 
          variant: 'destructive' 
        })
      }
    }
  }

  const get = async <T = any>(path: string): Promise<T> => {
    return await $fetch<T>(`${baseURL}${path}`, {
      headers: getHeaders(),
      onResponseError: handleResponseError,
    })
  }

  const post = async <T = any>(path: string, body: any): Promise<T> => {
    return await $fetch<T>(`${baseURL}${path}`, {
      method: 'POST',
      headers: getHeaders(),
      body,
      onResponseError: handleResponseError,
    })
  }

  const put = async <T = any>(path: string, body: any): Promise<T> => {
    return await $fetch<T>(`${baseURL}${path}`, {
      method: 'PUT',
      headers: getHeaders(),
      body,
      onResponseError: handleResponseError,
    })
  }

  const del = async <T = any>(path: string): Promise<T> => {
    return await $fetch<T>(`${baseURL}${path}`, {
      method: 'DELETE',
      headers: getHeaders(),
      onResponseError: handleResponseError,
    })
  }

  return { get, post, put, delete: del }
}
