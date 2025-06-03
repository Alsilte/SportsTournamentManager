/**
 * API Service - CORREGIDO para Railway
 * 
 * Archivo: frontend/src/services/api.js
 * 
 * Configuración actualizada para usar las rutas CORRECTAS del backend
 */

import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import router from '@/router'

// ============================================================================
// CONFIGURACIÓN DE LA URL BASE
// ============================================================================

// URL del backend en Railway
const API_BASE_URL = 'https://sportstournamentmanager-production.up.railway.app/api'

// Create axios instance with base configuration
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 15000, // Aumentado para conexiones más lentas
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: false
})

/**
 * Request interceptor
 * Adds authentication token to requests
 */
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    const token = authStore.token
    
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    config.headers['X-Frontend-Origin'] = window.location.origin
    
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

/**
 * Response interceptor
 * Handles authentication errors and token refresh
 */
api.interceptors.response.use(
  (response) => {
    return response
  },
  async (error) => {
    const authStore = useAuthStore()
    
    // Manejar errores específicos de CORS o conexión
    if (!error.response) {
      console.error('Network error or CORS issue:', error.message)
      return Promise.reject(new Error('Error de conexión con el servidor'))
    }
    
    if (error.response?.status === 401) {
      // Unauthorized - clear auth and redirect to login
      authStore.clearAuth()
      if (router.currentRoute.value.name !== 'login') {
        router.push({ name: 'login' })
      }
    } else if (error.response?.status === 403) {
      // Forbidden - user doesn't have permission
      console.error('Access forbidden:', error.response.data.message)
    } else if (error.response?.status >= 500) {
      // Server error
      console.error('Server error:', error.response.data)
    }
    
    return Promise.reject(error)
  }
)

// ============================================================================
// SERVICIOS DE AUTENTICACIÓN
// ============================================================================

export const authAPI = {
  login: (credentials) => api.post('/auth/login', credentials),
  register: (userData) => api.post('/auth/register', userData),
  logout: () => api.post('/auth/logout'), // Ruta protegida SIN v1
  refresh: () => api.post('/auth/refresh'), // Ruta protegida SIN v1
  profile: () => api.get('/auth/profile'), // Ruta protegida SIN v1
  updateProfile: (data) => api.put('/auth/profile', data), // Ruta protegida SIN v1
  logoutAll: () => api.post('/auth/logout-all'), // Nueva ruta disponible
  changePassword: (data) => api.post('/auth/change-password', data),
  forgotPassword: (email) => api.post('/auth/forgot-password', { email }),
  resetPassword: (data) => api.post('/auth/reset-password', data),
}

// ============================================================================
// SERVICIOS DE TORNEOS - RUTAS EN INGLÉS SEGÚN EL BACKEND
// ============================================================================

export const tournamentAPI = {
  // Rutas públicas
  getAll: (params = {}) => api.get('/tournaments', { params }),
  getById: (id) => api.get(`/tournaments/${id}`),
  getStandings: (id) => api.get(`/tournaments/${id}/standings`),
  getMatches: (id) => api.get(`/tournaments/${id}/matches`),
  getTopScorers: (id) => api.get(`/tournaments/${id}/top-scorers`),
  
  // Rutas protegidas (SIN v1 prefix)
  create: (data) => api.post('/tournaments', data),
  update: (id, data) => api.put(`/tournaments/${id}`, data),
  delete: (id) => api.delete(`/tournaments/${id}`),
  registerTeam: (id, teamData) => api.post(`/tournaments/${id}/register-team`, teamData),
  recalculateStandings: (id) => api.post(`/tournaments/${id}/recalculate-standings`)
}

// ============================================================================
// SERVICIOS DE EQUIPOS - RUTAS EN INGLÉS SEGÚN EL BACKEND
// ============================================================================

export const teamAPI = {
  // Rutas públicas
  getAll: (params = {}) => api.get('/teams', { params }),
  getById: (id) => api.get(`/teams/${id}`),
  getRoster: (id) => api.get(`/teams/${id}/roster`),
  getStatistics: (id) => api.get(`/teams/${id}/statistics`),
  
  // Rutas protegidas (SIN v1 prefix)
  create: (data) => api.post('/teams', data),
  update: (id, data) => api.put(`/teams/${id}`, data),
  delete: (id) => api.delete(`/teams/${id}`),
  addPlayer: (id, playerData) => api.post(`/teams/${id}/add-player`, playerData),
  removePlayer: (teamId, playerId) => api.delete(`/teams/${teamId}/remove-player/${playerId}`)
}

// ============================================================================
// SERVICIOS DE JUGADORES - RUTAS EN INGLÉS SEGÚN EL BACKEND
// ============================================================================

export const playerAPI = {
  // Rutas públicas
  getAll: (params = {}) => api.get('/players', { params }),
  getById: (id) => api.get(`/players/${id}`),
  getAvailable: (params = {}) => api.get('/players/available', { params }),
  getStatistics: (id) => api.get(`/players/${id}/statistics`),
  getTeamHistory: (id) => api.get(`/players/${id}/team-history`),
  
  // Rutas protegidas (SIN v1 prefix)
  update: (id, data) => api.put(`/players/${id}`, data)
}

// ============================================================================
// SERVICIOS DE PARTIDOS - RUTAS EN INGLÉS SEGÚN EL BACKEND
// ============================================================================

export const matchAPI = {
  // Rutas públicas
  getAll: (params = {}) => api.get('/matches', { params }),
  getById: (id) => api.get(`/matches/${id}`),
  getEvents: (id) => api.get(`/matches/${id}/events`),
  
  // Rutas protegidas (SIN v1 prefix)
  create: (data) => api.post('/matches', data),
  update: (id, data) => api.put(`/matches/${id}`, data),
  addEvent: (id, eventData) => api.post(`/matches/${id}/add-event`, eventData)
}

// ============================================================================
// SERVICIOS DE CLASIFICACIONES
// ============================================================================

export const standingAPI = {
  getByTournament: (tournamentId, params = {}) => api.get(`/tournaments/${tournamentId}/standings`, { params }),
  getTeamStats: (tournamentId, teamId) => api.get(`/standings/tournament/${tournamentId}/team/${teamId}`),
  recalculate: (tournamentId) => api.post(`/tournaments/${tournamentId}/recalculate-standings`),
  getTopScorers: (tournamentId, params = {}) => api.get(`/tournaments/${tournamentId}/top-scorers`, { params })
}

// ============================================================================
// SERVICIOS DE SALUD Y UTILIDADES
// ============================================================================

export const utilAPI = {
  healthCheck: () => api.get('/health'),
  getAvailableEndpoints: () => api.get('/non-existent-route') // Para obtener la lista de endpoints
}

/**
 * API helper functions
 */
export const apiHelpers = {
  // Handle API errors with user-friendly messages
  handleError: (error) => {
    if (error.response?.data?.message) {
      return error.response.data.message
    } else if (error.response?.data?.errors) {
      // Laravel validation errors
      const errors = error.response.data.errors
      return Object.values(errors).flat().join(', ')
    } else if (error.message) {
      return error.message
    } else {
      return 'An unexpected error occurred'
    }
  },
  
  // Check if request was successful
  isSuccess: (response) => {
    return response.data?.success === true || (response.status >= 200 && response.status < 300)
  },
  
  // Extract data from successful response
  getData: (response) => {
    return response.data?.data || response.data
  }
}

// ============================================================================
// FUNCIONES DE DEBUG PARA DESARROLLO
// ============================================================================

export const debugAPI = {
  testConnection: async () => {
    try {
      const response = await api.get('/health')
      console.log('✅ Conexión exitosa:', response.data)
      return response
    } catch (error) {
      console.error('❌ Error de conexión:', error)
      throw error
    }
  },
  
  testEndpoints: async () => {
    const endpoints = [
      '/health',
      '/tournaments',
      '/teams',
      '/players',
      '/matches'
    ]
    
    for (const endpoint of endpoints) {
      try {
        const response = await api.get(endpoint)
        console.log(`✅ ${endpoint}:`, response.status)
      } catch (error) {
        console.error(`❌ ${endpoint}:`, error.response?.status || 'Network Error')
      }
    }
  }
}

export default api