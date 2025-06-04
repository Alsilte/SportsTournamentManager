// frontend/src/services/api.js
import axios from 'axios'

// Obtener URL desde variables de entorno
const getApiBaseUrl = () => {
  return import.meta.env.VITE_API_URL || 
         import.meta.env.VITE_API_BASE_URL || 
         'https://sportstournamentmanager-production.up.railway.app/api'
}

const API_BASE_URL = getApiBaseUrl()

// Configuración de Axios
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: parseInt(import.meta.env.VITE_API_TIMEOUT) || 15000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: false
})

// Interceptor para logging en desarrollo
if (import.meta.env.DEV) {
  api.interceptors.request.use((config) => {
    console.log('🚀 API Request:', {
      method: config.method?.toUpperCase(),
      url: config.url,
      baseURL: config.baseURL,
      fullURL: `${config.baseURL}${config.url}`
    })
    return config
  })

  api.interceptors.response.use(
    (response) => {
      console.log('✅ API Response:', {
        status: response.status,
        url: response.config.url,
        data: response.data
      })
      return response
    },
    (error) => {
      console.error('❌ API Error:', {
        status: error.response?.status,
        statusText: error.response?.statusText,
        url: error.config?.url,
        method: error.config?.method,
        data: error.response?.data,
        headers: error.response?.headers
      })
      return Promise.reject(error)
    }
  )
}

// Interceptor para token de autenticación
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Interceptor para manejar errores de autenticación
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// Servicios API
export const authAPI = {
  login: (credentials) => api.post('/auth/login', credentials),
  register: (userData) => api.post('/auth/register', userData),
  logout: () => api.post('/auth/logout'),
  profile: () => api.get('/auth/profile'),
    updateProfile: (data) => api.put('/auth/profile', data),   refresh: () => api.post('/auth/refresh'),
}

export const tournamentAPI = {
  getAll: (params) => api.get('/tournaments', { params }),
  getById: (id) => api.get(`/tournaments/${id}`),
  create: (data) => api.post('/tournaments', data),
  update: (id, data) => api.put(`/tournaments/${id}`, data),
  delete: (id) => api.delete(`/tournaments/${id}`),
  registerTeam: (id, data) => api.post(`/tournaments/${id}/register-team`, data),
  standings: (id) => api.get(`/tournaments/${id}/standings`),
  matches: (id) => api.get(`/tournaments/${id}/matches`),
}

export const teamAPI = {
  getAll: (params) => api.get('/teams', { params }),
  getById: (id) => api.get(`/teams/${id}`),
  getRoster: (id) => api.get(`/teams/${id}/roster`),
  getStatistics: (id) => api.get(`/teams/${id}/statistics`),
  create: (data) => api.post('/teams', data),
  update: (id, data) => api.put(`/teams/${id}`, data),
  delete: (id) => api.delete(`/teams/${id}`),
  addPlayer: (id, data) => api.post(`/teams/${id}/players`, data),
  removePlayer: (teamId, playerId) => api.delete(`/teams/${teamId}/players/${playerId}`),
}

export const playerAPI = {
  getAll: (params) => api.get('/players', { params }),
  getById: (id) => api.get(`/players/${id}`),
  getStatistics: (id) => api.get(`/players/${id}/statistics`),
  getTeamHistory: (id) => api.get(`/players/${id}/team-history`),
  update: (id, data) => api.put(`/players/${id}`, data),
  available: (params) => api.get('/players/available', { params }),
}

export const matchAPI = {
  getAll: (params) => api.get('/matches', { params }),
  getById: (id) => api.get(`/matches/${id}`),
  create: (data) => api.post('/matches', data),
  update: (id, data) => api.put(`/matches/${id}`, data),
  addEvent: (id, data) => api.post(`/matches/${id}/events`, data),
  getEvents: (id) => api.get(`/matches/${id}/events`),
}

export const standingAPI = {
  getByTournament: (tournamentId, params) => api.get(`/standings/${tournamentId}`, { params }),
  recalculate: (tournamentId) => api.post(`/standings/${tournamentId}/recalculate`),
  teamStats: (tournamentId, teamId) => api.get(`/standings/${tournamentId}/teams/${teamId}`),
  topScorers: (tournamentId, params) => api.get(`/standings/${tournamentId}/top-scorers`, { params }),
}

// Helper para manejar respuestas de API
export const apiHelpers = {
  isSuccess: (response) => {
    return response?.data?.success === true || response?.status === 200
  },
  
  getData: (response) => {
    return response?.data?.data || response?.data || response
  },
  
  getMessage: (response) => {
    return response?.data?.message || 'Operation completed'
  },
  
  handleError: (error) => {
    if (error.response?.data?.message) {
      return error.response.data.message
    }
    if (error.response?.data?.error) {
      return error.response.data.error
    }
    if (error.message) {
      return error.message
    }
    return 'An unexpected error occurred'
  }
}

export default api