export function useDebounce<T = any>(fn: (v: T) => void, delay = 300) {
  let t: any
  return (v: T) => {
    clearTimeout(t)
    t = setTimeout(() => fn(v), delay)
  }
}

