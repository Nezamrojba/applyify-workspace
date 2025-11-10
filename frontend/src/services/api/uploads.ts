import { request } from '../http'

export const uploadsApi = {
  local: (file: File) => {
    const fd = new FormData()
    fd.append('file', file)
    return request<{ file_url: string; file_type: string; size_bytes: number }>('POST', '/api/uploads/local', fd)
  },
  localWithDocType: (file: File, docType: string) => {
    const fd = new FormData()
    fd.append('file', file)
    fd.append('doc_type', docType)
    return request<{ file_url: string; file_type: string; size_bytes: number }>('POST', '/api/uploads/local', fd)
  }
}

