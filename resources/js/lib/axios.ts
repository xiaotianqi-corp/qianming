import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://qianming.test',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
})

let csrfReady = false

api.interceptors.request.use(async (config) => {
  if (!csrfReady && ['post', 'put', 'patch', 'delete'].includes(config.method?.toLowerCase() || '')) {
    try {
      await axios.get('http://qianming.test/sanctum/csrf-cookie', { 
        withCredentials: true 
      })
      csrfReady = true
    } catch (error) {
      console.error('CSRF cookie error:', error)
    }
  }
  return config
}, (error) => {
  return Promise.reject(error)
})

export default api