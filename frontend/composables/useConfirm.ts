import { ref } from 'vue'

export interface ConfirmOptions {
  title: string
  description: string
  confirmText?: string
  cancelText?: string
  variant?: 'default' | 'destructive'
}

const isOpen = ref(false)
const options = ref<ConfirmOptions>({
  title: '',
  description: '',
  confirmText: 'Confirmer',
  cancelText: 'Annuler',
  variant: 'default',
})

let resolvePromise: ((value: boolean) => void) | null = null

export const useConfirm = () => {
  const confirm = (opts: ConfirmOptions): Promise<boolean> => {
    options.value = {
      confirmText: 'Confirmer',
      cancelText: 'Annuler',
      variant: 'default',
      ...opts,
    }
    isOpen.value = true
    return new Promise<boolean>((resolve) => {
      resolvePromise = resolve
    })
  }

  const handleConfirm = () => {
    isOpen.value = false
    resolvePromise?.(true)
    resolvePromise = null
  }

  const handleCancel = () => {
    isOpen.value = false
    resolvePromise?.(false)
    resolvePromise = null
  }

  return { isOpen, options, confirm, handleConfirm, handleCancel }
}
