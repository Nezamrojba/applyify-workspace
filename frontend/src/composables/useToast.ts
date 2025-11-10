import { useToastStore } from '@/stores/toast'

export function useToast(){
  const t = useToastStore()
  return {
    success: (m: string, d?: number) => t.success(m, d),
    error: (m: string, d?: number) => t.error(m, d),
    info: (m: string, d?: number) => t.info(m, d),
    warning: (m: string, d?: number) => t.warning(m, d)
  }
}

