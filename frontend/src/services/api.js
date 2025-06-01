// src/services/api.js - VERSIÓN CORREGIDA
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import router from '@/router'

// Función para determinar la URL base de la API
const getApiBaseUrl = () => {
  // En desarrollo, usar el proxy de Vite O la URL completa
  if (import.meta.env.DEV) {
    // Opción 1: Usar proxy (recomendado)
    return '/api/v1'  // Esto usará el proxy de Vite
    
    // Opción 2: URL directa (descomenta si el proxy no funciona)
    // return 'http://127.0.0.1:8000/api/v1'
  }
  
  // En producción, usar la URL completa
  return import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
}

// Crear instancia de axios
const api = axios.create({
  baseURL: getApiBaseUrl(),
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: false // Cambiar a false para APIs sin cookies
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    // Solo usar el store si está disponible
    try {
      const authStore = useAuthStore()
      const token = authStore.token
      
      if (token) {
        config.headers.Authorization = `Bearer ${token}`
      }
    } catch (error) {
      // Si no hay store disponible, obtener token de localStorage
      const token = localStorage.getItem('auth_token')
      if (token) {
        config.headers.Authorization = `Bearer ${token}`
      }
    }
    
    // Log de debug en desarrollo
    if (import.meta.env.DEV) {
      console.log('🔄 API Request:', {
        method: config.method?.toUpperCase(),
        url: config.url,
        baseURL: config.baseURL,
        fullUrl: `${config.baseURL}${config.url}`,
        headers: {
          'Content-Type': config.headers['Content-Type'],
          'Accept': config.headers['Accept'],
          'Authorization': config.headers['Authorization'] ? 'Bearer ***' : 'None'
        }
      })
    }
    
    return config
  },
  (error) => {
    console.error('❌ Request Error:', error)
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => {
    if (import.meta.env.DEV) {
      console.log('✅ API Response:', {
        status: response.status,
        statusText: response.statusText,
        url: response.config.url,
        method: response.config.method?.toUpperCase(),
        dataType: Array.isArray(response.data) ? 'Array' : typeof response.data,
        dataLength: Array.isArray(response.data) ? response.data.length : 'N/A',
        data: response.data // Mostrar datos completos para debugging
      })
    }
    return response
  },
  async (error) => {
    // Log detallado del error
    if (import.meta.env.DEV) {
      console.error('❌ API Error:', {
        status: error.response?.status,
        statusText: error.response?.statusText,
        url: error.config?.url,
        method: error.config?.method?.toUpperCase(),
        baseURL: error.config?.baseURL,
        fullUrl: error.config ? `${error.config.baseURL}${error.config.url}` : 'Unknown',
        message: error.message,
        code: error.code,
        response: error.response?.data,
        // Información adicional para debugging
        headers: error.config?.headers,
        timeoutReached: error.code === 'ECONNABORTED'
      })
    }
    
    // Manejo específico de errores
    if (error.code === 'ERR_NETWORK') {
      console.error('🔥 Network Error: Cannot connect to Laravel server')
      console.error('💡 Make sure Laravel is running on http://127.0.0.1:8000')
      console.error('💡 Check if CORS is properly configured in Laravel')
    } else if (error.code === 'ECONNABORTED') {
      console.error('⏰ Request timeout - Server took too long to respond')
    } else if (error.response?.status === 404) {
      console.error('🔍 Endpoint not found:', error.config?.url)
      console.error('💡 Check if the route exists in Laravel routes/api.php')
    } else if (error.response?.status === 401) {
      console.error('🔐 Unauthorized access')
      try {
        const authStore = useAuthStore()
        authStore.logout()
        if (router.currentRoute.value.name !== 'login') {
          router.push({ name: 'login' })
        }
      } catch (storeError) {
        console.error('Error accessing auth store:', storeError)
      }
    } else if (error.response?.status === 403) {
      console.error('🚫 Access forbidden:', error.response.data.message)
    } else if (error.response?.status >= 500) {
      console.error('🔥 Server error:', error.response.data)
    }
    
    return Promise.reject(error)
  }
)

// Función para verificar conectividad con el backend
export const checkServerHealth = async () => {
  try {
    console.log('🏥 Checking server health...')
    
    // Intentar varias URLs de health check
    const healthUrls = [
      'http://127.0.0.1:8000/up',
      'http://127.0.0.1:8000/api/tournaments',
      'http://localhost:8000/up'
    ]
    
    for (const url of healthUrls) {
      try {
        console.log(`🔍 Trying health check: ${url}`)
        const response = await fetch(url, {
          method: 'GET',
          mode: 'cors',
          headers: {
            'Accept': 'application/json'
          }
        })
        
        console.log(`📊 Health check result for ${url}:`, {
          status: response.status,
          ok: response.ok,
          statusText: response.statusText
        })
        
        if (response.ok) {
          console.log('✅ Server is healthy!')
          return true
        }
      } catch (err) {
        console.log(`❌ Health check failed for ${url}:`, err.message)
      }
    }
    
    console.log('❌ All health checks failed')
    return false
  } catch (error) {
    console.error('💥 Server health check failed:', error)
    return false
  }
}

// Tournament API con debugging mejorado
export const tournamentAPI = {
  getAll: async (params = {}) => {
    try {
      console.log('🏆 Fetching tournaments with params:', params)
      const response = await api.get('/tournaments', { params })
      console.log('📊 Tournament API success:', {
        status: response.status,
        dataType: typeof response.data,
        isArray: Array.isArray(response.data),
        hasData: response.data?.data ? 'Yes' : 'No',
        dataLength: response.data?.data?.length || 'N/A'
      })
      return response
    } catch (error) {
      console.error('💥 Tournament API Error:', error)
      throw error
    }
  },
  
  getById: (id) => api.get(`/tournaments/${id}`),
  create: (data) => api.post('/tournaments', data),
  update: (id, data) => api.put(`/tournaments/${id}`, data),
  delete: (id) => api.delete(`/tournaments/${id}`),
  registerTeam: (id, teamData) => api.post(`/tournaments/${id}/register`, teamData),
  getStandings: (id) => api.get(`/tournaments/${id}/standings`),
  getMatches: (id) => api.get(`/tournaments/${id}/matches`)
}

// Mantener las otras APIs igual...
export const authAPI = {
  login: (credentials) => api.post('/auth/login', credentials),
  register: (userData) => api.post('/auth/register', userData),
  logout: () => api.post('/auth/logout'),
  refresh: () => api.post('/auth/refresh'),
  profile: () => api.get('/auth/profile'),
  updateProfile: (data) => api.put('/auth/profile', data)
}

export const teamAPI = {
  getAll: (params = {}) => api.get('/teams', { params }),
  getById: (id) => api.get(`/teams/${id}`),
  create: (data) => api.post('/teams', data),
  update: (id, data) => api.put(`/teams/${id}`, data),
  delete: (id) => api.delete(`/teams/${id}`),
  getRoster: (id) => api.get(`/teams/${id}/roster`),
  addPlayer: (id, playerData) => api.post(`/teams/${id}/players`, playerData),
  removePlayer: (teamId, playerId) => api.delete(`/teams/${teamId}/players/${playerId}`),
  getStatistics: (id) => api.get(`/teams/${id}/statistics`)
}

export const playerAPI = {
  getAll: (params = {}) => api.get('/players', { params }),
  getById: (id) => api.get(`/players/${id}`),
  update: (id, data) => api.put(`/players/${id}`, data),
  getStatistics: (id) => api.get(`/players/${id}/statistics`),
  getTeamHistory: (id) => api.get(`/players/${id}/team-history`),
  getAvailable: (params = {}) => api.get('/players/available', { params })
}

export const matchAPI = {
  getAll: (params = {}) => api.get('/matches', { params }),
  getById: (id) => api.get(`/matches/${id}`),
  create: (data) => api.post('/matches', data),
  update: (id, data) => api.put(`/matches/${id}`, data),
  addEvent: (id, eventData) => api.post(`/matches/${id}/events`, eventData),
  getEvents: (id) => api.get(`/matches/${id}/events`)
}

export const standingAPI = {
  getByTournament: (tournamentId, params = {}) => api.get(`/standings/${tournamentId}`, { params }),
  recalculate: (tournamentId) => api.post(`/standings/${tournamentId}/recalculate`),
  getTeamStats: (tournamentId, teamId) => api.get(`/standings/${tournamentId}/teams/${teamId}`),
  getTopScorers: (tournamentId, params = {}) => api.get(`/standings/${tournamentId}/top-scorers`, { params })
}

// API helpers mejorados
export const apiHelpers = {
  handleError: (error) => {
    if (error.response?.data?.message) {
      return error.response.data.message
    } else if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      return Object.values(errors).flat().join(', ')
    } else if (error.message) {
      return error.message
    } else {
      return 'An unexpected error occurred'
    }
  },
  
  isSuccess: (response) => {
    return response && response.status >= 200 && response.status < 300
  },
  
  getData: (response) => {
    // Manejar diferentes estructuras de respuesta de Laravel
    if (response.data?.data) {
      return response.data.data // Respuesta paginada
    } else if (response.data) {
      return response.data // Respuesta directa
    }
    return null
  },
  
  // Función para extraer datos de respuesta paginada
  getPaginatedData: (response) => {
    const data = apiHelpers.getData(response)
    if (data && typeof data === 'object' && data.data) {
      return {
        data: data.data,
        meta: {
          current_page: data.current_page,
          last_page: data.last_page,
          per_page: data.per_page,
          total: data.total
        }
      }
    }
    return { data: Array.isArray(data) ? data : [], meta: {} }
  }
}

export default api