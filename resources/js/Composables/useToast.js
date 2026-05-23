import { reactive } from 'vue'

const toasts = reactive([])

export function useToast() {
  function addToast(message, type = 'success') {
    const id = Date.now() + Math.random()
    toasts.push({ id, message, type })
    setTimeout(() => {
      const idx = toasts.findIndex(t => t.id === id)
      if (idx !== -1) toasts.splice(idx, 1)
    }, 4000)
  }

  function removeToast(id) {
    const idx = toasts.findIndex(t => t.id === id)
    if (idx !== -1) toasts.splice(idx, 1)
  }

  return { toasts, addToast, removeToast }
}
