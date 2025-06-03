// ============================================================================
// CONFIGURACIÓN DE API PARA RAILWAY
// ============================================================================

import axios from 'axios'

// ============================================================================
// CONFIGURACIÓN DE URLs SEGÚN EL ENTORNO
// ============================================================================

const getApiBaseUrl = () => {
  // En producción (Railway)
  if (import.meta.env.PROD) {
    // CAMBIA ESTA URL POR TU URL DE RAILWAY
    return 'https://sportstournamentmanager-production.up.railway.app//api'
  }
  
  // En desarrollo local
  if (import.meta.env.DEV) {
    return 'http://127.0.0.1:8000/api'
  }
  
  // Fallback
  return 'http://127.0.0.1:8000/api'
}

// ============================================================================
// INSTANCIA DE AXIOS CONFIGURADA
// ============================================================================

const api = axios.create({
  baseURL: getApiBaseUrl(),
  timeout: 30000, // 30 segundos para Railway
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: false // Para Railway, usualmente false
})

// ============================================================================
// INTERCEPTORS
// ============================================================================

// Request interceptor - agregar token si existe
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    // Log para debugging
    if (import.meta.env.DEV) {
      console.log(`🚀 API Request: ${config.method?.toUpperCase()} ${config.url}`)
      console.log('📍 Base URL:', config.baseURL)
      if (config.data) {
        console.log('📦 Data:', config.data)
      }
    }

    return config
  },
  (error) => {
    console.error('❌ Request Error:', error)
    return Promise.reject(error)
  }
)

// Response interceptor - manejo de errores
api.interceptors.response.use(
  (response) => {
    if (import.meta.env.DEV) {
      console.log(`✅ API Response: ${response.config.method?.toUpperCase()} ${response.config.url}`)
      console.log('📊 Status:', response.status)
    }
    return response
  },
  (error) => {
    // Log detallado del error
    console.error('💥 API Error:', {
      message: error.message,
      status: error.response?.status,
      statusText: error.response?.statusText,
      url: error.config?.url,
      baseURL: error.config?.baseURL,
      fullURL: `${error.config?.baseURL}${error.config?.url}`
    })

    // Manejo específico de errores de red
    if (error.code === 'ERR_NETWORK' || error.message === 'Network Error') {
      console.error('🔥 Network Error: Cannot connect to Laravel server')
      console.error('💡 Current API URL:', getApiBaseUrl())
      console.error('💡 Make sure your Railway backend is running')
      console.error('💡 Check if the URL is correct')
    }

    // Error 401 - token expirado
    if (error.response?.status === 401) {
      console.warn('🔐 Authentication error - removing token')
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user_data')
      
      // Redirigir al login si no estamos ya ahí
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }

    return Promise.reject(error)
  }
)

// ============================================================================
// MÉTODOS DE API
// ============================================================================

export const authAPI = {
  // Registro
  register: (userData) => api.post('/auth/register', userData),
  
  // Login
  login: (credentials) => api.post('/auth/login', credentials),
  
  // Logout
  logout: () => api.post('/auth/logout'),
  
  // Usuario actual
  me: () => api.get('/auth/me'),
  
  // Cambiar contraseña
  changePassword: (passwordData) => api.post('/auth/change-password', passwordData)
}

export const tournamentAPI = {
  // Obtener todos los torneos
  getAll: (params = {}) => {
    console.log('🏆 Fetching tournaments with params:', params)
    return api.get('/torneos', { params })
      .catch(error => {
        console.error('💥 Tournament API Error:', error)
        throw error
      })
  },
  
  // Obtener torneo por ID
  getById: (id) => api.get(`/torneos/${id}`),
  
  // Crear torneo
  create: (data) => api.post('/torneos', data),
  
  // Actualizar torneo
  update: (id, data) => api.put(`/torneos/${id}`, data),
  
  // Eliminar torneo
  delete: (id) => api.delete(`/torneos/${id}`),
  
  // Inscribir equipo
  inscribeTeam: (tournamentId, teamData) => 
    api.post(`/torneos/${tournamentId}/equipos`, teamData),
  
  // Obtener clasificación
  getStandings: (tournamentId) => 
    api.get(`/torneos/${tournamentId}/clasificacion`)
}

export const teamAPI = {
  // Obtener todos los equipos
  getAll: (params = {}) => api.get('/equipos', { params }),
  
  // Obtener equipo por ID
  getById: (id) => api.get(`/equipos/${id}`),
  
  // Crear equipo
  create: (data) => api.post('/equipos', data),
  
  // Actualizar equipo
  update: (id, data) => api.put(`/equipos/${id}`, data),
  
  // Eliminar equipo
  delete: (id) => api.delete(`/equipos/${id}`),
  
  // Obtener jugadores del equipo
  getPlayers: (teamId) => api.get(`/equipos/${teamId}/jugadores`),
  
  // Agregar jugador al equipo
  addPlayer: (teamId, playerData) => 
    api.post(`/equipos/${teamId}/jugadores`, playerData)
}

export const matchAPI = {
  // Obtener todos los partidos
  getAll: (params = {}) => api.get('/partidos', { params }),
  
  // Obtener partido por ID
  getById: (id) => api.get(`/partidos/${id}`),
  
  // Crear partido
  create: (data) => api.post('/partidos', data),
  
  // Actualizar partido
  update: (id, data) => api.put(`/partidos/${id}`, data),
  
  // Iniciar partido
  start: (id) => api.post(`/partidos/${id}/iniciar`),
  
  // Finalizar partido
  finish: (id, data) => api.post(`/partidos/${id}/finalizar`, data),
  
  // Obtener eventos del partido
  getEvents: (matchId) => api.get(`/partidos/${matchId}/eventos`),
  
  // Agregar evento al partido
  addEvent: (matchId, eventData) => 
    api.post(`/partidos/${matchId}/eventos`, eventData)
}

export const dashboardAPI = {
  // Obtener estadísticas del dashboard
  getStats: () => api.get('/dashboard/stats'),
  
  // Obtener actividad reciente
  getActivity: () => api.get('/dashboard/activity')
}

// ============================================================================
// UTILIDADES
// ============================================================================

// Función para verificar conectividad
export const checkConnectivity = async () => {
  try {
    const response = await api.get('/public/app-info', { timeout: 5000 })
    console.log('✅ API connectivity check passed')
    return true
  } catch (error) {
    console.error('❌ API connectivity check failed:', error.message)
    return false
  }
}

// Función para obtener información del servidor
export const getServerInfo = () => api.get('/public/app-info')

// Función para verificar salud del API
export const healthCheck = () => {
  return axios.get(`${getApiBaseUrl().replace('/api', '')}/api/health`, {
    timeout: 5000
  })
}

// Exportar instancia de axios y URL base
export { api as default, getApiBaseUrl }

// ============================================================================
// DEBUG INFO
// ============================================================================

if (import.meta.env.DEV) {
  console.log('🔧 API Configuration:')
  console.log('📍 Base URL:', getApiBaseUrl())
  console.log('🌍 Environment:', import.meta.env.MODE)
  console.log('🏗️ Build:', import.meta.env.PROD ? 'Production' : 'Development')
}