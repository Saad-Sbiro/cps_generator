import { ref } from 'vue'

export interface ToastOptions {
  id?: string
  title: string
  description?: string
  variant?: 'default' | 'success' | 'destructive'
  duration?: number
}

const toasts = ref<ToastOptions[]>([])

export const useToast = () => {
  const addToast = (options: Omit<ToastOptions, 'id'>) => {
    const id = Math.random().toString(36).substring(2, 11)
    toasts.value.push({ ...options, id })
    
    setTimeout(() => {
      removeToast(id)
    }, options.duration || 5000)
  }

  const removeToast = (id: string) => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  return { toasts, addToast, removeToast }
}
