/**
 * CONFIGURACIÓN API PARA RAILWAY
 * 
 * Archivo: frontend/src/services/api.js
 * 
 * Configuración actualizada para conectar con el backend en Railway
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
  // NO usar withCredentials: true para CORS cross-origin
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
    
    // Para Railway, podemos agregar headers adicionales si es necesario
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
  logout: () => api.post('/auth/logout'),
  refresh: () => api.post('/auth/refresh'),
  profile: () => api.get('/auth/me'),
  updateProfile: (data) => api.put('/auth/profile', data),
  changePassword: (data) => api.post('/auth/change-password', data),
  forgotPassword: (email) => api.post('/auth/forgot-password', { email }),
  resetPassword: (data) => api.post('/auth/reset-password', data),
}

// ============================================================================
// SERVICIOS DE TORNEOS
// ============================================================================

export const tournamentAPI = {
  getAll: (params = {}) => api.get('/torneos', { params }),
  getById: (id) => api.get(`/torneos/${id}`),
  create: (data) => api.post('/torneos', data),
  update: (id, data) => api.put(`/torneos/${id}`, data),
  delete: (id) => api.delete(`/torneos/${id}`),
  registerTeam: (id, teamData) => api.post(`/torneos/${id}/equipos`, teamData),
  getStandings: (id) => api.get(`/torneos/${id}/clasificacion`),
  getMatches: (id) => api.get(`/partidos?torneo_id=${id}`)
}

// ============================================================================
// SERVICIOS DE EQUIPOS
// ============================================================================

export const teamAPI = {
  getAll: (params = {}) => api.get('/equipos', { params }),
  getById: (id) => api.get(`/equipos/${id}`),
  create: (data) => api.post('/equipos', data),
  update: (id, data) => api.put(`/equipos/${id}`, data),
  delete: (id) => api.delete(`/equipos/${id}`),
  getRoster: (id) => api.get(`/equipos/${id}/jugadores`),
  addPlayer: (id, playerData) => api.post(`/equipos/${id}/jugadores`, playerData),
  removePlayer: (teamId, playerId) => api.delete(`/equipos/${teamId}/jugadores/${playerId}`),
  getStatistics: (id) => api.get(`/equipos/${id}/estadisticas`)
}

// ============================================================================
// SERVICIOS DE JUGADORES
// ============================================================================

export const playerAPI = {
  getAll: (params = {}) => api.get('/usuarios?tipo_usuario=jugador', { params }),
  getById: (id) => api.get(`/usuarios/${id}`),
  update: (id, data) => api.put(`/usuarios/${id}`, data),
  getStatistics: (id) => api.get(`/jugador/estadisticas`),
  getTeamHistory: (id) => api.get(`/jugador/equipos`),
  getAvailable: (params = {}) => api.get('/usuarios?tipo_usuario=jugador&activo=true', { params })
}

// ============================================================================
// SERVICIOS DE PARTIDOS
// ============================================================================

export const matchAPI = {
  getAll: (params = {}) => api.get('/partidos', { params }),
  getById: (id) => api.get(`/partidos/${id}`),
  create: (data) => api.post('/partidos', data),
  update: (id, data) => api.put(`/partidos/${id}`, data),
  addEvent: (id, eventData) => api.post(`/partidos/${id}/eventos`, eventData),
  getEvents: (id) => api.get(`/partidos/${id}/eventos`),
  start: (id) => api.post(`/partidos/${id}/iniciar`),
  finish: (id, data) => api.post(`/partidos/${id}/finalizar`, data)
}

// ============================================================================
// SERVICIOS DE DASHBOARD
// ============================================================================

export const dashboardAPI = {
  getStats: () => api.get('/dashboard/stats'),
  getActivity: () => api.get('/dashboard/activity')
}

// ============================================================================
// SERVICIOS PÚBLICOS
// ============================================================================

export const publicAPI = {
  getAppInfo: () => api.get('/public/app-info'),
  getDeportes: () => api.get('/public/deportes')
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

export default api