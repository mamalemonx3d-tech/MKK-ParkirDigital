export interface ConfirmDialogOptions {
  title?: string
  message: string
  type?: 'danger' | 'warning' | 'info' | 'success'
  confirmText?: string
  cancelText?: string
  isAlert?: boolean
  animationPath?: string
}

export const useConfirm = () => {
  const isOpen = useState<boolean>('confirm_dialog_open', () => false)
  const options = useState<ConfirmDialogOptions>('confirm_dialog_options', () => ({
    title: '',
    message: '',
    type: 'danger',
    confirmText: 'Lanjutkan',
    cancelText: 'Batal',
    isAlert: false,
    animationPath: '/lottie/error.json',
  }))

  const resolver = useState<((val: boolean) => void) | null>('confirm_dialog_resolver', () => null)

  const askConfirm = (opts: ConfirmDialogOptions): Promise<boolean> => {
    return new Promise((resolve) => {
      let defaultAnim = '/lottie/info.json'
      if (opts.type === 'danger') defaultAnim = '/lottie/error.json'
      else if (opts.type === 'success') defaultAnim = '/lottie/check.json'
      else if (opts.type === 'warning') defaultAnim = '/lottie/info.json'

      options.value = {
        title: opts.title || (opts.type === 'danger' ? 'Konfirmasi Hapus' : 'Konfirmasi Tindakan'),
        message: opts.message,
        type: opts.type || 'danger',
        confirmText: opts.confirmText || (opts.type === 'danger' ? 'Ya, Hapus' : 'Ya, Lanjutkan'),
        cancelText: opts.cancelText || 'Batal',
        isAlert: false,
        animationPath: opts.animationPath || defaultAnim,
      }
      resolver.value = resolve
      isOpen.value = true
    })
  }

  const askAlert = (
    message: string,
    title?: string,
    type: 'danger' | 'warning' | 'info' | 'success' = 'info'
  ): Promise<boolean> => {
    return new Promise((resolve) => {
      let defaultAnim = '/lottie/info.json'
      if (type === 'danger') defaultAnim = '/lottie/error.json'
      else if (type === 'success') defaultAnim = '/lottie/check.json'

      options.value = {
        title: title || (type === 'danger' ? 'Terjadi Kesalahan' : (type === 'success' ? 'Berhasil' : 'Pemberitahuan')),
        message,
        type,
        confirmText: 'Mengerti',
        cancelText: '',
        isAlert: true,
        animationPath: defaultAnim,
      }
      resolver.value = resolve
      isOpen.value = true
    })
  }

  const handleConfirm = () => {
    isOpen.value = false
    if (resolver.value) {
      resolver.value(true)
      resolver.value = null
    }
  }

  const handleCancel = () => {
    isOpen.value = false
    if (resolver.value) {
      resolver.value(false)
      resolver.value = null
    }
  }

  return {
    isOpen,
    options,
    askConfirm,
    askAlert,
    handleConfirm,
    handleCancel,
  }
}
