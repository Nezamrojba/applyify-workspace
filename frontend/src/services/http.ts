type HttpMethod = 'GET' | 'POST' | 'PATCH' | 'DELETE'

const base = (import.meta.env?.VITE_API_BASE_URL ?? 'http://localhost:8000').replace(/\/+$/, '')

function token() {
  return localStorage.getItem('token') || ''
}

type AuthStore = { clearAuth: () => void } | null

let authStoreInstance: AuthStore = null

export function setAuthStore(store: AuthStore) {
  authStoreInstance = store
}

function clearAuth() {
  localStorage.removeItem('token')
  authStoreInstance?.clearAuth()
}

type ApiError = {
  message: string
  status?: number
  response?: { status: number }
  networkError?: boolean
}

export async function request<T>(method: HttpMethod, path: string, body?: any): Promise<T> {
  const headers: Record<string, string> = { Accept: 'application/json' }
  if (body && !(body instanceof FormData)) headers['Content-Type'] = 'application/json'
  if (token()) headers['Authorization'] = `Bearer ${token()}`

  const url = `${base}${path}`

  try {
    const res = await fetch(url, {
      method,
      headers,
      body: body instanceof FormData ? body : body ? JSON.stringify(body) : undefined,
      credentials: 'omit'
    })

    if (!res.ok) {
      if (res.status === 404 && path.includes('/api/notifications')) {
        const error: ApiError = { message: 'Not Found', status: 404, response: { status: 404 } }
        throw error
      }

      if (res.status === 401 && path !== '/api/auth/login' && path !== '/api/auth/register') {
        clearAuth()
      }

      let errorData: ApiError
      if (res.status === 413) {
        errorData = {
          message: 'File is too large. Maximum file size is 10MB. Please compress or resize your file and try again.',
          status: 413
        }
      } else {
        const fallbackMessage =
          res.status === 413 ? 'File is too large. Maximum file size is 10MB.' : `Request failed with status ${res.status}`

        errorData = (await res.json().catch(() => ({
          message: fallbackMessage,
          status: res.status
        }))) as ApiError
      }

      if (!errorData.status) errorData.status = res.status
      errorData.response = { status: res.status }
      throw errorData
    }

    const ct = res.headers.get('content-type') || ''
    return (ct.includes('application/json') ? res.json() : (await res.text())) as T
  } catch (error: any) {
    if (error instanceof TypeError) {
      if (error.message.includes('fetch') || error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
        console.error(`[API] Network error connecting to ${url}:`, error.message)
        const networkError: ApiError = {
          message: `Cannot connect to API server. Please check if the backend is running on ${base}`,
          status: 0,
          networkError: true
        }
        throw networkError
      }
    }
    throw error
  }
}

